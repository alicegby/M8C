<?php

namespace App\Entity;

use App\Repository\GameResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameResultRepository::class)]
#[ORM\Table(name: 'game_results')]
class GameResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\OneToOne(inversedBy: 'gameResult', targetEntity: GameSession::class)]
    #[ORM\JoinColumn(nullable: false)]
    private GameSession $gameSession;

    #[ORM\Column]
    private bool $success;

    #[ORM\Column]
    private int $correctVotesCount;

    #[ORM\Column]
    private int $totalVotesCount;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $completedAt;

    public function __construct()
    {
        $this->completedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getGameSession(): GameSession { return $this->gameSession; }
    public function setGameSession(GameSession $gameSession): static { $this->gameSession = $gameSession; return $this; }
    public function isSuccess(): bool { return $this->success; }
    public function setSuccess(bool $success): static { $this->success = $success; return $this; }
    public function getCorrectVotesCount(): int { return $this->correctVotesCount; }
    public function setCorrectVotesCount(int $correctVotesCount): static { $this->correctVotesCount = $correctVotesCount; return $this; }
    public function getTotalVotesCount(): int { return $this->totalVotesCount; }
    public function setTotalVotesCount(int $totalVotesCount): static { $this->totalVotesCount = $totalVotesCount; return $this; }
    public function getCompletedAt(): \DateTimeInterface { return $this->completedAt; }
    public function setCompletedAt(\DateTimeInterface $completedAt): static { $this->completedAt = $completedAt; return $this; }
}