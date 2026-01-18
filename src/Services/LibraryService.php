<?php

namespace LibraryManagement\Services;

use LibraryManagement\Models\Member;
use LibraryManagement\Models\Book;
use LibraryManagement\Models\BorrowRecord;
use LibraryManagement\Repositories\MemberRepository;
use LibraryManagement\Repositories\BookRepository;
use LibraryManagement\Repositories\BorrowRepository;
use LibraryManagement\Repositories\DatabaseConnection;
use LibraryManagement\Exceptions\BookUnavailableException;
use LibraryManagement\Exceptions\MemberLimitExceededException;
use LibraryManagement\Exceptions\LateFeeException;
use LibraryManagement\Exceptions\MembershipExpiredException;
use DateTime;
use PDO;
use Exception;

class LibraryService
{
    private MemberRepository $memberRepo;
    private BookRepository $bookRepo;
    private BorrowRepository $borrowRepo;
    private PDO $db;

    public function __construct()
    {
        $this->memberRepo = new MemberRepository();
        $this->bookRepo = new BookRepository();
        $this->borrowRepo = new BorrowRepository();
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function borrowBook(int $memberId, int $bookId, int $branchId): BorrowRecord
    {
        $this->db->beginTransaction();

        try {
           
            $member = $this->memberRepo->findById($memberId);
            if (!$member) {
                throw new Exception("Member not found");
            }

          
            if (!$member->isActive()) {
                throw new MembershipExpiredException("Your membership has expired. Please renew.");
            }

            $currentBorrowed = $this->memberRepo->getCurrentBorrowedCount($memberId);
            if (!$member->canBorrow($currentBorrowed)) {
                throw new MemberLimitExceededException(
                    "You have reached your borrowing limit of {$member->getBorrowLimit()} books"
                );
            }

          
            $unpaidFees = $this->memberRepo->getTotalUnpaidFees($memberId);
            if ($unpaidFees > 10.00) {
                throw new LateFeeException(
                    "You have unpaid late fees of $" . number_format($unpaidFees, 2) . 
                    ". Please pay your fees before borrowing."
                );
            }

            $availableCopies = $this->bookRepo->getAvailableCopies($bookId, $branchId);
            if ($availableCopies <= 0) {
                throw new BookUnavailableException("This book is not available at the selected branch");
            }

            
            $borrowDate = new DateTime();
            $dueDate = clone $borrowDate;
            $dueDate->modify("+{$member->getLoanPeriodDays()} days");

            $borrowRecord = new BorrowRecord($memberId, $bookId, $branchId, $borrowDate, $dueDate);
            $this->borrowRepo->save($borrowRecord);

           
            $this->bookRepo->updateInventory($bookId, $branchId, -1);

          
            $member->incrementTotalBorrowed();
            $this->memberRepo->update($member);

            $this->db->commit();

            return $borrowRecord;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function returnBook(int $memberId, int $bookId): array
    {
        $this->db->beginTransaction();

        try {

            $borrowRecord = $this->borrowRepo->findActiveBorrowByMemberAndBook($memberId, $bookId);
            if (!$borrowRecord) {
                throw new Exception("No active borrow record found for this book");
            }

            $member = $this->memberRepo->findById($memberId);
            

            $returnDate = new DateTime();
            $borrowRecord->setReturnDate($returnDate);

            if ($borrowRecord->isOverdue()) {
                $lateFee = $borrowRecord->calculateLateFee($member->getLateFeePerDay());
                $borrowRecord->setLateFee($lateFee);
            }

            $this->borrowRepo->update($borrowRecord);

            $this->bookRepo->updateInventory(
                $borrowRecord->getBookId(),
                $borrowRecord->getBranchId(),
                1
            );

            $this->db->commit();

            return [
                'success' => true,
                'late_fee' => $borrowRecord->getLateFee(),
                'is_overdue' => $borrowRecord->isOverdue(),
                'return_date' => $returnDate
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function renewBook(int $memberId, int $bookId): BorrowRecord
    {
        $this->db->beginTransaction();

        try {
            
            $borrowRecord = $this->borrowRepo->findActiveBorrowByMemberAndBook($memberId, $bookId);
            if (!$borrowRecord) {
                throw new Exception("No active borrow record found");
            }

          
            if ($borrowRecord->isRenewed()) {
                throw new Exception("This book has already been renewed once");
            }

            
            $member = $this->memberRepo->findById($memberId);

            
            $newDueDate = clone $borrowRecord->getDueDate();
            $newDueDate->modify("+{$member->getLoanPeriodDays()} days");
            
            $borrowRecord->setDueDate($newDueDate);
            $borrowRecord->setIsRenewed(true);

           
            $this->borrowRepo->update($borrowRecord);

            $this->db->commit();

            return $borrowRecord;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function searchBooks(string $query, string $searchType = 'title'): array
    {
        return match($searchType) {
            'title' => $this->bookRepo->searchByTitle($query),
            'author' => $this->bookRepo->searchByAuthor($query),
            'isbn' => [$this->bookRepo->findByIsbn($query)],
            default => []
        };
    }

    public function getMemberHistory(int $memberId, int $limit = 10): array
    {
        return $this->borrowRepo->getBorrowHistory($memberId, $limit);
    }

    public function getOverdueBooks(): array
    {
        return $this->borrowRepo->getOverdueBooks();
    }


    public function checkBookAvailability(int $bookId): array
    {
        return $this->bookRepo->getBranchesWithBook($bookId);
    }
}