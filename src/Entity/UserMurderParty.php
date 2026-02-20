<?php

namespace App\Entity;

use App\Repository\UserMurderPartyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserMurderPartyRepository::class)]
#[ORM\Table(name: 'user_murder_parties')]
#[ORM\UniqueConstraint(name: 'unique_user_murder_party', columns: ['user_id', 'murder_party_id'])]
class UserMurderParty
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userMurderParties')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: MurderParty::class, inversedBy: 'userMurderParties')]
    #[ORM\JoinColumn(nullable: false)]
    private MurderParty $murderParty;

    #[ORM\ManyToOne(targetEntity: Purchase::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Purchase $purchase = null;

    #[ORM\Column]
    private bool $isPlayed = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $unlockedAt;

    public function __construct()
    {
        $this->unlockedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function setUser(User $user): static { $this->user = $user; return $this; }
    public function getMurderParty(): MurderParty { return $this->murderParty; }
    public function setMurderParty(MurderParty $murderParty): static { $this->murderParty = $murderParty; return $this; }
    public function getPurchase(): ?Purchase { return $this->purchase; }
    public function setPurchase(?Purchase $purchase): static { $this->purchase = $purchase; return $this; }
    public function isPlayed(): bool { return $this->isPlayed; }
    public function setIsPlayed(bool $isPlayed): static { $this->isPlayed = $isPlayed; return $this; }
    public function getUnlockedAt(): \DateTimeInterface { return $this->unlockedAt; }
    public function setUnlockedAt(\DateTimeInterface $unlockedAt): static { $this->unlockedAt = $unlockedAt; return $this; }
}