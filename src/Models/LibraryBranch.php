<?php
class LibraryBranch
{
    public function __construct(
        private int $id,
        private string $name,
        private string $location
    ) {}

    public function getId(): int
    {
        return $this->id;
    }
}
