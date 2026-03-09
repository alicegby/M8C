<?php

namespace App\Entity;

use App\Repository\MurderPartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MurderPartyRepository::class)]
#[ORM\Table(name: 'murder_parties')]
#[ORM\HasLifecycleCallbacks]
class MurderParty
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT)]
    private string $synopsis;

    #[ORM\Column(type: Types::TEXT)]
    private string $scenario;

    #[ORM\Column(type: Types::TEXT)]
    private string $epilogue;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImageUrl = null;

    #[ORM\Column]
    private int $duree;

    #[ORM\Column]
    private int $nbPlayers;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column]
    private bool $isFree = false;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private string $averageRating = '0.00';

    #[ORM\Column]
    private int $ratingsCount = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    // Relations
    #[ORM\OneToMany(mappedBy: 'murderParty', targetEntity: Character::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $characters;

    #[ORM\OneToMany(mappedBy: 'murderParty', targetEntity: Clue::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $clues;

    #[ORM\ManyToMany(targetEntity: Pack::class, mappedBy: 'murderParties')]
    private Collection $packs;

    #[ORM\OneToMany(mappedBy: 'murderParty', targetEntity: UserMurderParty::class)]
    private Collection $userMurderParties;

    public function __construct()
    {
        $this->characters = new ArrayCollection();
        $this->clues = new ArrayCollection();
        $this->packs = new ArrayCollection();
        $this->userMurderParties = new ArrayCollection();
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
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }
    public function getSynopsis(): string { return $this->synopsis; }
    public function setSynopsis(string $synopsis): static { $this->synopsis = $synopsis; return $this; }
    public function getScenario(): string { return $this->scenario; }
    public function setScenario(string $scenario): static { $this->scenario = $scenario; return $this; }
    public function getEpilogue(): string { return $this->epilogue; }
    public function setEpilogue(string $epilogue): static { $this->epilogue = $epilogue; return $this; }
    public function getCoverImageUrl(): ?string { return $this->coverImageUrl; }
    public function setCoverImageUrl(?string $coverImageUrl): static { $this->coverImageUrl = $coverImageUrl; return $this; }
    public function getDuree(): int { return $this->duree; }
    public function setDuree(int $duree): static { $this->duree = $duree; return $this; }
    public function getNbPlayers(): int { return $this->nbPlayers; }
    public function setNbPlayers(int $nbPlayers): static { $this->nbPlayers = $nbPlayers; return $this; }
    public function getPrice(): string { return $this->price; }
    public function setPrice(string $price): static { $this->price = $price; return $this; }
    public function isFree(): bool { return $this->isFree; }
    public function setIsFree(bool $isFree): static { $this->isFree = $isFree; return $this; }
    public function isPublished(): bool { return $this->isPublished; }
    public function setIsPublished(bool $isPublished): static { $this->isPublished = $isPublished; return $this; }
    public function getAverageRating(): string { return $this->averageRating; }
    public function setAverageRating(string $averageRating): static { $this->averageRating = $averageRating; return $this; }
    public function getRatingsCount(): int { return $this->ratingsCount; }
    public function setRatingsCount(int $ratingsCount): static { $this->ratingsCount = $ratingsCount; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function getCharacters(): Collection { return $this->characters; }
    public function getClues(): Collection { return $this->clues; }
    public function getPacks(): Collection { return $this->packs; }
    public function getUserMurderParties(): Collection { return $this->userMurderParties; }

    // Logiques métiers
    public function addCharacter(Character $character): static
    {
        if (!$this->characters->contains($character)) {
            $this->characters->add($character);
            $character->setMurderParty($this);
        }
        return $this;
    }

    public function removeCharacter(Character $character): static
    {
        if ($this->characters->removeElement($character)) {
            // Si la FK ne peut pas être null, on ne touche pas à murderParty
        }
        return $this;
    }

    public function addClue(Clue $clue): static
    {
        if (!$this->clues->contains($clue)) {
            $this->clues->add($clue);
            $clue->setMurderParty($this);
        }
        return $this;
    }

    public function removeClue(Clue $clue): static
    {
        $this->clues->removeElement($clue);
        return $this;
    }
}