<?php
class Author
{
    public function __construct(
        private int $id,
        private string $name,
        private string $nationality,
        private string $primaryGenre
    ) {}

    public function getId(): int
    {
        return $this->id;
    }
}
