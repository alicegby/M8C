<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
#[ORM\Table(name: 'purchases')]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: MurderParty::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?MurderParty $murderParty = null;

    #[ORM\ManyToOne(targetEntity: Pack::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pack $pack = null;

    #[ORM\Column(length: 20)]
    private string $purchaseType; // 'single', 'pack'

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $amountPaid;

    #[ORM\ManyToOne(targetEntity: PromoCode::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PromoCode $promoCode = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $discountApplied = '0.00';

    #[ORM\Column(length: 20)]
    private string $paymentMethod; // 'card', 'paypal', 'apple_pay', 'google_pay'

    #[ORM\Column(length: 255)]
    private string $stripePaymentId;

    #[ORM\Column(length: 20)]
    private string $status = 'pending'; // 'pending', 'completed', 'refunded'

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $purchasedAt;

    public function __construct()
    {
        $this->purchasedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function setUser(User $user): static { $this->user = $user; return $this; }
    public function getMurderParty(): ?MurderParty { return $this->murderParty; }
    public function setMurderParty(?MurderParty $murderParty): static { $this->murderParty = $murderParty; return $this; }
    public function getPack(): ?Pack { return $this->pack; }
    public function setPack(?Pack $pack): static { $this->pack = $pack; return $this; }
    public function getPurchaseType(): string { return $this->purchaseType; }
    public function setPurchaseType(string $purchaseType): static { $this->purchaseType = $purchaseType; return $this; }
    public function getAmountPaid(): string { return $this->amountPaid; }
    public function setAmountPaid(string $amountPaid): static { $this->amountPaid = $amountPaid; return $this; }
    public function getPromoCode(): ?PromoCode { return $this->promoCode; }
    public function setPromoCode(?PromoCode $promoCode): static { $this->promoCode = $promoCode; return $this; }
    public function getDiscountApplied(): string { return $this->discountApplied; }
    public function setDiscountApplied(string $discountApplied): static { $this->discountApplied = $discountApplied; return $this; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function setPaymentMethod(string $paymentMethod): static { $this->paymentMethod = $paymentMethod; return $this; }
    public function getStripePaymentId(): string { return $this->stripePaymentId; }
    public function setStripePaymentId(string $stripePaymentId): static { $this->stripePaymentId = $stripePaymentId; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getPurchasedAt(): \DateTimeInterface { return $this->purchasedAt; }
    public function setPurchasedAt(\DateTimeInterface $purchasedAt): static { $this->purchasedAt = $purchasedAt; return $this; }
}