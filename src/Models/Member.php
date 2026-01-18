<?php


namespace LibraryManagement\Models;

use DateTime;

abstract class Member
{
    protected ?int $id = null;
    protected string $fullName;
    protected string $email;
    protected ?string $phone;
    protected DateTime $membershipStart;
    protected DateTime $membershipEnd;
    protected int $totalBorrowed = 0;

    public function __construct(
        string $fullName,
        string $email,
        ?string $phone,
        DateTime $membershipStart,
        DateTime $membershipEnd
    ) {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->phone = $phone;
        $this->membershipStart = $membershipStart;
        $this->membershipEnd = $membershipEnd;
    }

    abstract public function getBorrowLimit(): int;
    abstract public function getLoanPeriodDays(): int;
    abstract public function getLateFeePerDay(): float;
    abstract public function getMemberType(): string;

    public function isActive(): bool
    {
        $today = new DateTime();
        return $this->membershipEnd >= $today;
    }

    public function canBorrow(int $currentBorrowedCount): bool
    {
        return $this->isActive() && $currentBorrowedCount < $this->getBorrowLimit();
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

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getMembershipStart(): DateTime
    {
        return $this->membershipStart;
    }

    public function getMembershipEnd(): DateTime
    {
        return $this->membershipEnd;
    }

    public function setMembershipEnd(DateTime $membershipEnd): void
    {
        $this->membershipEnd = $membershipEnd;
    }

    public function getTotalBorrowed(): int
    {
        return $this->totalBorrowed;
    }

    public function setTotalBorrowed(int $totalBorrowed): void
    {
        $this->totalBorrowed = $totalBorrowed;
    }

    public function incrementTotalBorrowed(): void
    {
        $this->totalBorrowed++;
    }
}