<?php

namespace LibraryManagement\Models;

use DateTime;

class FacultyMember extends Member
{
    private const BORROW_LIMIT = 10;
    private const LOAN_PERIOD_DAYS = 30;
    private const LATE_FEE_PER_DAY = 0.25;

    public function getBorrowLimit(): int
    {
        return self::BORROW_LIMIT;
    }

    public function getLoanPeriodDays(): int
    {
        return self::LOAN_PERIOD_DAYS;
    }

    public function getLateFeePerDay(): float
    {
        return self::LATE_FEE_PER_DAY;
    }

    public function getMemberType(): string
    {
        return 'Faculty';
    }
}