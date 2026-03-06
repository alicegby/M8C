<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'purchase_stats')]
class PurchaseStat
{
    #[ODM\Id]
    private ?string $id = null;

    // Murder Party ou Pack
    #[ODM\Field(type: 'string')]
    private ?string $murderPartyId = null;

    #[ODM\Field(type: 'string')]
    private ?string $murderPartyTitle = null;

    #[ODM\Field(type: 'string')]
    private ?string $packId = null;

    #[ODM\Field(type: 'string')]
    private ?string $packName = null;

    #[ODM\Field(type: 'string')]
    private string $purchaseType; // 'single', 'pack'

    #[ODM\Field(type: 'string')]
    private string $source = 'web'; // 'web', 'app'

    // Montants
    #[ODM\Field(type: 'float')]
    private float $amountPaid;

    #[ODM\Field(type: 'float')]
    private float $discountApplied = 0.0;

    // Promo
    #[ODM\Field(type: 'string')]
    private ?string $promoCodeId = null;

    #[ODM\Field(type: 'string')]
    private ?string $promoCode = null;

    // Paiement
    #[ODM\Field(type: 'string')]
    private string $paymentMethod;

    // User
    #[ODM\Field(type: 'string')]
    private string $userId;

    #[ODM\Field(type: 'date')]
    private \DateTimeInterface $purchasedAt;

    public function __construct()
    {
        $this->purchasedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }

    public function getMurderPartyId(): ?string { return $this->murderPartyId; }
    public function setMurderPartyId(?string $v): static { $this->murderPartyId = $v; return $this; }

    public function getMurderPartyTitle(): ?string { return $this->murderPartyTitle; }
    public function setMurderPartyTitle(?string $v): static { $this->murderPartyTitle = $v; return $this; }

    public function getPackId(): ?string { return $this->packId; }
    public function setPackId(?string $v): static { $this->packId = $v; return $this; }

    public function getPackName(): ?string { return $this->packName; }
    public function setPackName(?string $v): static { $this->packName = $v; return $this; }

    public function getPurchaseType(): string { return $this->purchaseType; }
    public function setPurchaseType(string $v): static { $this->purchaseType = $v; return $this; }

    public function getSource(): string { return $this->source; }
    public function setSource(string $v): static { $this->source = $v; return $this; }

    public function getAmountPaid(): float { return $this->amountPaid; }
    public function setAmountPaid(float $v): static { $this->amountPaid = $v; return $this; }

    public function getDiscountApplied(): float { return $this->discountApplied; }
    public function setDiscountApplied(float $v): static { $this->discountApplied = $v; return $this; }

    public function getPromoCodeId(): ?string { return $this->promoCodeId; }
    public function setPromoCodeId(?string $v): static { $this->promoCodeId = $v; return $this; }

    public function getPromoCode(): ?string { return $this->promoCode; }
    public function setPromoCode(?string $v): static { $this->promoCode = $v; return $this; }

    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function setPaymentMethod(string $v): static { $this->paymentMethod = $v; return $this; }

    public function getUserId(): string { return $this->userId; }
    public function setUserId(string $v): static { $this->userId = $v; return $this; }

    public function getPurchasedAt(): \DateTimeInterface { return $this->purchasedAt; }
    public function setPurchasedAt(\DateTimeInterface $v): static { $this->purchasedAt = $v; return $this; }
}