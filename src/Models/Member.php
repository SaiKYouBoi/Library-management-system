<?php
abstract class Member
{
    public function __construct(
        protected int $id,
        protected string $fullName,
        protected string $email,
        protected string $phone,
        protected DateTime $membershipExpiry,
        protected float $unpaidFees
    ) {}

    public function canBorrow(): bool
    {
        return $this->membershipExpiry >= new DateTime()
            && $this->unpaidFees <= 10;
    }

    abstract public function getBorrowLimit(): int;
    abstract public function getLoanDuration(): int;
    abstract public function getLateFeePerDay(): float;

    public function getId(): int
    {
        return $this->id;
    }
}
