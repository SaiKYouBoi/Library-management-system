<?php
namespace LibraryManagement\Exceptions;

use Exception;

class MemberLimitExceededException extends Exception
{
    public function __construct(string $message = "Member has reached borrowing limit")
    {
        parent::__construct($message);
    }
}