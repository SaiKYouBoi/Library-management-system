<?php

namespace LibraryManagement\Models;

class LibraryBranch
{
    private ?int $id = null;
    private string $name;
    private string $location;
    private ?string $operatingHours;
    private ?string $contactPhone;
    private ?string $contactEmail;

    public function __construct(
        string $name,
        string $location,
        ?string $operatingHours = null,
        ?string $contactPhone = null,
        ?string $contactEmail = null
    ) {
        $this->name = $name;
        $this->location = $location;
        $this->operatingHours = $operatingHours;
        $this->contactPhone = $contactPhone;
        $this->contactEmail = $contactEmail;
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

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getOperatingHours(): ?string
    {
        return $this->operatingHours;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }
}