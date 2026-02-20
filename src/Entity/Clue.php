<?php

namespace App\Entity;

use App\Repository\ClueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClueRepository::class)]
#[ORM\Table(name: 'clues')]
#[ORM\HasLifecycleCallbacks]
class Clue
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: MurderParty::class, inversedBy: 'clues')]
    #[ORM\JoinColumn(nullable: false)]
    private MurderParty $murderParty;

    #[ORM\ManyToOne(targetEntity: Character::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Character $character = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column]
    private int $triggerMinutes;

    #[ORM\Column]
    private bool $isPublic = true;

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
    public function getMurderParty(): MurderParty { return $this->murderParty; }
    public function setMurderParty(MurderParty $murderParty): static { $this->murderParty = $murderParty; return $this; }
    public function getCharacter(): ?Character { return $this->character; }
    public function setCharacter(?Character $character): static { $this->character = $character; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }
    public function getTriggerMinutes(): int { return $this->triggerMinutes; }
    public function setTriggerMinutes(int $triggerMinutes): static { $this->triggerMinutes = $triggerMinutes; return $this; }
    public function isPublic(): bool { return $this->isPublic; }
    public function setIsPublic(bool $isPublic): static { $this->isPublic = $isPublic; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
}