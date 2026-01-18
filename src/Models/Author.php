<?php

namespace LibraryManagement\Models;

use DateTime;

class Author
{
    private ?int $id = null;
    private string $name;
    private ?string $biography;
    private ?string $nationality;
    private ?DateTime $birthDate;
    private ?DateTime $deathDate;
    private ?string $primaryGenre;

    public function __construct(
        string $name,
        ?string $biography = null,
        ?string $nationality = null,
        ?DateTime $birthDate = null,
        ?DateTime $deathDate = null,
        ?string $primaryGenre = null
    ) {
        $this->name = $name;
        $this->biography = $biography;
        $this->nationality = $nationality;
        $this->birthDate = $birthDate;
        $this->deathDate = $deathDate;
        $this->primaryGenre = $primaryGenre;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function getBirthDate(): ?DateTime
    {
        return $this->birthDate;
    }

    public function getDeathDate(): ?DateTime
    {
        return $this->deathDate;
    }

    public function getPrimaryGenre(): ?string
    {
        return $this->primaryGenre;
    }
}