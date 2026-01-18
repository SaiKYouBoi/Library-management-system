<?php
namespace LibraryManagement\Exceptions;

use Exception;

class LateFeeException extends Exception
{
    public function __construct(string $message = "Member has unpaid late fees exceeding limit")
    {
        parent::__construct($message);
    }
}