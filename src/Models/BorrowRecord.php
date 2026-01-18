<?php

namespace LibraryManagement\Models;

use DateTime;

class BorrowRecord
{
    private ?int $id = null;
    private int $memberId;
    private int $bookId;
    private int $branchId;
    private DateTime $borrowDate;
    private DateTime $dueDate;
    private ?DateTime $returnDate = null;
    private float $lateFee = 0.00;
    private bool $isRenewed = false;

    public function __construct(
        int $memberId,
        int $bookId,
        int $branchId,
        DateTime $borrowDate,
        DateTime $dueDate
    ) {
        $this->memberId = $memberId;
        $this->bookId = $bookId;
        $this->branchId = $branchId;
        $this->borrowDate = $borrowDate;
        $this->dueDate = $dueDate;
    }

    public function isOverdue(): bool
    {
        if ($this->returnDate !== null) {
            return false;
        }
        $today = new DateTime();
        return $today > $this->dueDate;
    }

    public function calculateLateFee(float $feePerDay): float
    {
        if (!$this->isOverdue()) {
            return 0.00;
        }

        $today = new DateTime();
        $interval = $this->dueDate->diff($today);
        $daysLate = $interval->days;

        return $daysLate * $feePerDay;
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMemberId(): int
    {
        return $this->memberId;
    }

    public function getBookId(): int
    {
        return $this->bookId;
    }

    public function getBranchId(): int
    {
        return $this->branchId;
    }

    public function getBorrowDate(): DateTime
    {
        return $this->borrowDate;
    }

    public function getDueDate(): DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(DateTime $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function getReturnDate(): ?DateTime
    {
        return $this->returnDate;
    }

    public function setReturnDate(DateTime $returnDate): void
    {
        $this->returnDate = $returnDate;
    }

    public function getLateFee(): float
    {
        return $this->lateFee;
    }

    public function setLateFee(float $lateFee): void
    {
        $this->lateFee = $lateFee;
    }

    public function isRenewed(): bool
    {
        return $this->isRenewed;
    }

    public function setIsRenewed(bool $isRenewed): void
    {
        $this->isRenewed = $isRenewed;
    }
}