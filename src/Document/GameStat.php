<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'game_stats')]
class GameStat
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    private string $murderPartyId;

    #[ODM\Field(type: 'string')]
    private string $murderPartyTitle;

    #[ODM\Field(type: 'string')]
    private string $gameSessionId;

    #[ODM\Field(type: 'int')]
    private int $playerCount;

    // Résultat
    #[ODM\Field(type: 'bool')]
    private bool $success; // true = coupable trouvé

    #[ODM\Field(type: 'int')]
    private int $correctVotes;

    #[ODM\Field(type: 'int')]
    private int $totalVotes;

    // Durée en secondes (startedAt -> completedAt)
    #[ODM\Field(type: 'int')]
    private ?int $durationSeconds = null;

    #[ODM\Field(type: 'date')]
    private \DateTimeInterface $playedAt;

    public function __construct()
    {
        $this->playedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }

    public function getMurderPartyId(): string { return $this->murderPartyId; }
    public function setMurderPartyId(string $v): static { $this->murderPartyId = $v; return $this; }

    public function getMurderPartyTitle(): string { return $this->murderPartyTitle; }
    public function setMurderPartyTitle(string $v): static { $this->murderPartyTitle = $v; return $this; }

    public function getGameSessionId(): string { return $this->gameSessionId; }
    public function setGameSessionId(string $v): static { $this->gameSessionId = $v; return $this; }

    public function getPlayerCount(): int { return $this->playerCount; }
    public function setPlayerCount(int $v): static { $this->playerCount = $v; return $this; }

    public function isSuccess(): bool { return $this->success; }
    public function setSuccess(bool $v): static { $this->success = $v; return $this; }

    public function getCorrectVotes(): int { return $this->correctVotes; }
    public function setCorrectVotes(int $v): static { $this->correctVotes = $v; return $this; }

    public function getTotalVotes(): int { return $this->totalVotes; }
    public function setTotalVotes(int $v): static { $this->totalVotes = $v; return $this; }

    public function getDurationSeconds(): ?int { return $this->durationSeconds; }
    public function setDurationSeconds(?int $v): static { $this->durationSeconds = $v; return $this; }

    public function getPlayedAt(): \DateTimeInterface { return $this->playedAt; }
    public function setPlayedAt(\DateTimeInterface $v): static { $this->playedAt = $v; return $this; }
}