<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'user_registration_stats')]
class UserRegistrationStat
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    private string $userId;

    #[ODM\Field(type: 'string')]
    private string $authProvider; // 'email', 'google', etc.

    #[ODM\Field(type: 'date')]
    private \DateTimeInterface $registeredAt;

    public function __construct()
    {
        $this->registeredAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }

    public function getUserId(): string { return $this->userId; }
    public function setUserId(string $v): static { $this->userId = $v; return $this; }

    public function getAuthProvider(): string { return $this->authProvider; }
    public function setAuthProvider(string $v): static { $this->authProvider = $v; return $this; }

    public function getRegisteredAt(): \DateTimeInterface { return $this->registeredAt; }
    public function setRegisteredAt(\DateTimeInterface $v): static { $this->registeredAt = $v; return $this; }
}