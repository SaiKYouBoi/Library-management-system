<?php
class Book
{
    public function __construct(
        private string $isbn,
        private string $title,
        private int $publicationYear,
        private string $status
    ) {}

    public function getIsbn(): string
    {
        return $this->isbn;
    }
}
