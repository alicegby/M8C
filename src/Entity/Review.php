<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'reviews')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private string $authorPrenom;

    #[ORM\Column(length: 100)]
    private string $authorNom;

    #[ORM\Column(length: 180)]
    private string $authorEmail;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(length: 20)]
    private string $status = 'pending'; // 'pending', 'approved', 'rejected'

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $reviewedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
    public function getAuthorPrenom(): string { return $this->authorPrenom; }
    public function setAuthorPrenom(string $authorPrenom): static { $this->authorPrenom = $authorPrenom; return $this; }
    public function getAuthorNom(): string { return $this->authorNom; }
    public function setAuthorNom(string $authorNom): static { $this->authorNom = $authorNom; return $this; }
    public function getAuthorEmail(): string { return $this->authorEmail; }
    public function setAuthorEmail(string $authorEmail): static { $this->authorEmail = $authorEmail; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getReviewedAt(): ?\DateTimeInterface { return $this->reviewedAt; }
    public function setReviewedAt(?\DateTimeInterface $reviewedAt): static { $this->reviewedAt = $reviewedAt; return $this; }
}