<?php

namespace App\Entity;

use App\Repository\PromoCodeUsageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromoCodeUsageRepository::class)]
#[ORM\Table(name: 'promo_code_usages')]
#[ORM\UniqueConstraint(name: 'unique_user_promo', columns: ['user_id', 'promo_code_id'])]
class PromoCodeUsage
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: PromoCode::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PromoCode $promoCode;

    #[ORM\ManyToOne(targetEntity: Purchase::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Purchase $purchase = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $usedAt;

    public function __construct()
    {
        $this->usedAt = new \DateTime();
    }

    public function getId(): ?string { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function setUser(User $user): static { $this->user = $user; return $this; }
    public function getPromoCode(): PromoCode { return $this->promoCode; }
    public function setPromoCode(PromoCode $promoCode): static { $this->promoCode = $promoCode; return $this; }
    public function getPurchase(): ?Purchase { return $this->purchase; }
    public function setPurchase(?Purchase $purchase): static { $this->purchase = $purchase; return $this; }
    public function getUsedAt(): \DateTimeInterface { return $this->usedAt; }
    public function setUsedAt(\DateTimeInterface $usedAt): static { $this->usedAt = $usedAt; return $this; }
}