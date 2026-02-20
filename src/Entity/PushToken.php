<?php

namespace App\Entity;

use App\Repository\PushTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PushTokenRepository::class)]
#[ORM\Table(name: 'push_tokens')]
#[ORM\UniqueConstraint(name: 'unique_user_token', columns: ['user_id', 'token'])]
class PushToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'pushTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(length: 255)]
    private string $token;

    #[ORM\Column(length: 10)]
    private string $platform; // 'ios', 'android'

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function setUser(User $user): static { $this->user = $user; return $this; }
    public function getToken(): string { return $this->token; }
    public function setToken(string $token): static { $this->token = $token; return $this; }
    public function getPlatform(): string { return $this->platform; }
    public function setPlatform(string $platform): static { $this->platform = $platform; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}