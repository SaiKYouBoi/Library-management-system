<?php
namespace LibraryManagement\Exceptions;

use Exception;

class MembershipExpiredException extends Exception
{
    public function __construct(string $message = "Member's membership has expired")
    {
        parent::__construct($message);
    }
}