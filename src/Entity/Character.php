<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: 'characters')]
#[ORM\HasLifecycleCallbacks]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: MurderParty::class, inversedBy: 'characters')]
    #[ORM\JoinColumn(nullable: false)]
    private MurderParty $murderParty;

    #[ORM\Column(length: 100)]
    private string $prenom;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $job = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $histoire;

    #[ORM\Column(type: Types::TEXT)]
    private string $mobile;

    #[ORM\Column(type: Types::TEXT)]
    private string $alibi;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $extraInfo = null;

    #[ORM\Column]
    private bool $isGuilty = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    #[ORM\OneToMany(mappedBy: 'character', targetEntity: GamePlayer::class)]
    private Collection $gamePlayers;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->gamePlayers = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getMurderParty(): MurderParty { return $this->murderParty; }
    public function setMurderParty(MurderParty $murderParty): static { $this->murderParty = $murderParty; return $this; }
    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }
    public function getAge(): ?int { return $this->age; }
    public function setAge(?int $age): static { $this->age = $age; return $this; }
    public function getJob(): ?string { return $this->job; }
    public function setJob(?string $job): static { $this->job = $job; return $this; }
    public function getHistoire(): string { return $this->histoire; }
    public function setHistoire(string $histoire): static { $this->histoire = $histoire; return $this; }
    public function getMobile(): string { return $this->mobile; }
    public function setMobile(string $mobile): static { $this->mobile = $mobile; return $this; }
    public function getAlibi(): string { return $this->alibi; }
    public function setAlibi(string $alibi): static { $this->alibi = $alibi; return $this; }
    public function getExtraInfo(): ?string { return $this->extraInfo; }
    public function setExtraInfo(?string $extraInfo): static { $this->extraInfo = $extraInfo; return $this; }
    public function isGuilty(): bool { return $this->isGuilty; }
    public function setIsGuilty(bool $isGuilty): static { $this->isGuilty = $isGuilty; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function getGamePlayers(): Collection { return $this->gamePlayers; }
}