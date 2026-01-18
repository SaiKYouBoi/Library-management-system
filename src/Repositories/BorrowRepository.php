<?php
// src/Repositories/BorrowRepository.php

namespace LibraryManagement\Repositories;

use LibraryManagement\Models\BorrowRecord;
use PDO;
use DateTime;

class BorrowRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function save(BorrowRecord $record): int
    {
        $sql = "INSERT INTO borrow_records (member_id, book_id, branch_id, borrow_date, due_date, late_fee, is_renewed) 
                VALUES (:member_id, :book_id, :branch_id, :borrow_date, :due_date, :late_fee, :is_renewed)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'member_id' => $record->getMemberId(),
            'book_id' => $record->getBookId(),
            'branch_id' => $record->getBranchId(),
            'borrow_date' => $record->getBorrowDate()->format('Y-m-d'),
            'due_date' => $record->getDueDate()->format('Y-m-d'),
            'late_fee' => $record->getLateFee(),
            'is_renewed' => $record->isRenewed() ? 1 : 0
        ]);

        $recordId = (int)$this->db->lastInsertId();
        $record->setId($recordId);
        return $recordId;
    }

    public function update(BorrowRecord $record): bool
    {
        $sql = "UPDATE borrow_records 
                SET return_date = :return_date, late_fee = :late_fee, is_renewed = :is_renewed, due_date = :due_date
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'return_date' => $record->getReturnDate()?->format('Y-m-d'),
            'late_fee' => $record->getLateFee(),
            'is_renewed' => $record->isRenewed() ? 1 : 0,
            'due_date' => $record->getDueDate()->format('Y-m-d'),
            'id' => $record->getId()
        ]);
    }

    public function findById(int $id): ?BorrowRecord
    {
        $sql = "SELECT * FROM borrow_records WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return $this->createRecordFromData($data);
    }

    public function findActiveBorrowByMemberAndBook(int $memberId, int $bookId): ?BorrowRecord
    {
        $sql = "SELECT * FROM borrow_records 
                WHERE member_id = :member_id AND book_id = :book_id AND return_date IS NULL
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['member_id' => $memberId, 'book_id' => $bookId]);
        
        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return $this->createRecordFromData($data);
    }

    public function getActiveBorrowsByMember(int $memberId): array
    {
        $sql = "SELECT br.*, b.title, b.isbn 
                FROM borrow_records br
                INNER JOIN books b ON br.book_id = b.id
                WHERE br.member_id = :member_id AND br.return_date IS NULL
                ORDER BY br.due_date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        
        $records = [];
        while ($data = $stmt->fetch()) {
            $records[] = $this->createRecordFromData($data);
        }
        
        return $records;
    }

    public function getBorrowHistory(int $memberId, int $limit = 10): array
    {
        $sql = "SELECT br.*, b.title, b.isbn 
                FROM borrow_records br
                INNER JOIN books b ON br.book_id = b.id
                WHERE br.member_id = :member_id
                ORDER BY br.borrow_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('member_id', $memberId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $records = [];
        while ($data = $stmt->fetch()) {
            $records[] = $this->createRecordFromData($data);
        }
        
        return $records;
    }

    public function getOverdueBooks(): array
    {
        $today = (new DateTime())->format('Y-m-d');
        
        $sql = "SELECT br.*, b.title, b.isbn, m.full_name, m.email 
                FROM borrow_records br
                INNER JOIN books b ON br.book_id = b.id
                INNER JOIN members m ON br.member_id = m.id
                WHERE br.return_date IS NULL AND br.due_date < :today
                ORDER BY br.due_date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['today' => $today]);
        
        return $stmt->fetchAll();
    }

    private function createRecordFromData(array $data): BorrowRecord
    {
        $record = new BorrowRecord(
            (int)$data['member_id'],
            (int)$data['book_id'],
            (int)$data['branch_id'],
            new DateTime($data['borrow_date']),
            new DateTime($data['due_date'])
        );

        $record->setId((int)$data['id']);
        
        if ($data['return_date']) {
            $record->setReturnDate(new DateTime($data['return_date']));
        }
        
        $record->setLateFee((float)$data['late_fee']);
        $record->setIsRenewed((bool)$data['is_renewed']);

        return $record;
    }
}