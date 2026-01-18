<?php

namespace LibraryManagement\Exceptions;

use Exception;

class BookUnavailableException extends Exception
{
    public function __construct(string $message = "Book is not available for borrowing")
    {
        parent::__construct($message);
    }
}