<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'bloc_notes')]
class BlocNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // ── Relation avec le joueur ──
    #[ORM\ManyToOne(targetEntity: GamePlayer::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private GamePlayer $gamePlayer;

    // ── Relation avec la session ──
    #[ORM\ManyToOne(targetEntity: GameSession::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private GameSession $gameSession;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "now()"])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ── Getters & Setters ──
    public function getId(): ?int { return $this->id; }

    public function getGamePlayer(): GamePlayer { return $this->gamePlayer; }
    public function setGamePlayer(GamePlayer $gamePlayer): static { $this->gamePlayer = $gamePlayer; return $this; }

    public function getGameSession(): GameSession { return $this->gameSession; }
    public function setGameSession(GameSession $gameSession): static { $this->gameSession = $gameSession; return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): static { $this->content = $content; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}