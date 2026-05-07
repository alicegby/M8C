<?php

namespace App\Entity;

use App\Repository\PromoCodeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromoCodeRepository::class)]
#[ORM\Table(name: 'promo_codes')]
#[ORM\HasLifecycleCallbacks]
class PromoCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $code;

    #[ORM\Column(length: 20)]
    private string $discountType; // 'percentage', 'fixed'

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $discountValue;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validFrom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validUntil = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxUses = null;

    #[ORM\Column]
    private int $currentUses = 0;

    #[ORM\Column]
    private bool $isActive = false;

    #[ORM\Column]
    private bool $isWelcomeCode = false;

    #[ORM\Column(length: 20)]
    private string $applicableTo = 'both'; // 'mp', 'pack', 'both'

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }
    public function getDiscountType(): string { return $this->discountType; }
    public function setDiscountType(string $discountType): static { $this->discountType = $discountType; return $this; }
    public function getDiscountValue(): string { return $this->discountValue; }
    public function setDiscountValue(string $discountValue): static { $this->discountValue = $discountValue; return $this; }
    public function getValidFrom(): ?\DateTimeInterface { return $this->validFrom; }
    public function setValidFrom(?\DateTimeInterface $validFrom): static { $this->validFrom = $validFrom; return $this; }
    public function getValidUntil(): ?\DateTimeInterface { return $this->validUntil; }
    public function setValidUntil(?\DateTimeInterface $validUntil): static { $this->validUntil = $validUntil; return $this; }
    public function getMaxUses(): ?int { return $this->maxUses; }
    public function setMaxUses(?int $maxUses): static { $this->maxUses = $maxUses; return $this; }
    public function getCurrentUses(): int { return $this->currentUses; }
    public function setCurrentUses(int $currentUses): static { $this->currentUses = $currentUses; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
    public function isWelcomeCode(): bool { return $this->isWelcomeCode; }
    public function setIsWelcomeCode(bool $isWelcomeCode): static { $this->isWelcomeCode = $isWelcomeCode; return $this; }
    public function getApplicableTo(): string { return $this->applicableTo; }
    public function setApplicableTo(string $applicableTo): static { $this->applicableTo = $applicableTo; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
}