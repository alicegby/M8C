<?php

namespace App\Entity;

use App\Repository\GameSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameSessionRepository::class)]
#[ORM\Table(name: 'game_sessions')]
class GameSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: MurderParty::class)]
    #[ORM\JoinColumn(nullable: false)]
    private MurderParty $murderParty;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $hostUser;

    #[ORM\Column(length: 8, unique: true)]
    private string $joinCode;

    #[ORM\Column(length: 30)]
    private string $status = 'waiting';
    // 'waiting', 'reading_scenario', 'reading_characters', 'playing', 'voting', 'finished'

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $timerEndsAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $votingEndsAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'gameSession', targetEntity: GamePlayer::class, cascade: ['persist', 'remove'])]
    private Collection $gamePlayers;

    #[ORM\OneToOne(mappedBy: 'gameSession', targetEntity: GameResult::class, cascade: ['persist', 'remove'])]
    private ?GameResult $gameResult = null;

    public function __construct()
    {
        $this->gamePlayers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getMurderParty(): MurderParty { return $this->murderParty; }
    public function setMurderParty(MurderParty $murderParty): static { $this->murderParty = $murderParty; return $this; }
    public function getHostUser(): User { return $this->hostUser; }
    public function setHostUser(User $hostUser): static { $this->hostUser = $hostUser; return $this; }
    public function getJoinCode(): string { return $this->joinCode; }
    public function setJoinCode(string $joinCode): static { $this->joinCode = $joinCode; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getStartedAt(): ?\DateTimeInterface { return $this->startedAt; }
    public function setStartedAt(?\DateTimeInterface $startedAt): static { $this->startedAt = $startedAt; return $this; }
    public function getTimerEndsAt(): ?\DateTimeInterface { return $this->timerEndsAt; }
    public function setTimerEndsAt(?\DateTimeInterface $timerEndsAt): static { $this->timerEndsAt = $timerEndsAt; return $this; }
    public function getVotingEndsAt(): ?\DateTimeInterface { return $this->votingEndsAt; }
    public function setVotingEndsAt(?\DateTimeInterface $votingEndsAt): static { $this->votingEndsAt = $votingEndsAt; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getGamePlayers(): Collection { return $this->gamePlayers; }
    public function getGameResult(): ?GameResult { return $this->gameResult; }
}