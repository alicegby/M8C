<?php

namespace App\Entity;

use App\Repository\GamePlayerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GamePlayerRepository::class)]
#[ORM\Table(name: 'game_players')]
class GamePlayer
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: GameSession::class, inversedBy: 'gamePlayers')]
    #[ORM\JoinColumn(nullable: false)]
    private GameSession $gameSession;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Character::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Character $character = null;

    #[ORM\Column(length: 100, nullable: true)]
    private string $pseudoInGame;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarInGame = null;

    #[ORM\Column(options: ["default" => false])]
    private bool $isHost = false;

    #[ORM\Column(options: ["default" => false])]
    private bool $isReady = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => "now()"])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->pseudoInGame = '';
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getGameSession(): GameSession { return $this->gameSession; }
    public function setGameSession(GameSession $gameSession): static { $this->gameSession = $gameSession; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
    public function getCharacter(): ?Character { return $this->character; }
    public function setCharacter(?Character $character): static { $this->character = $character; return $this; }
    public function getPseudoInGame(): string { return $this->pseudoInGame; }
    public function setPseudoInGame(string $pseudoInGame): static { $this->pseudoInGame = $pseudoInGame; return $this; }
    public function getAvatarInGame(): ?string { return $this->avatarInGame; }
    public function setAvatarInGame(?string $avatarInGame): static { $this->avatarInGame = $avatarInGame; return $this; }
    public function isHost(): bool { return $this->isHost; }
    public function setIsHost(bool $isHost): static { $this->isHost = $isHost; return $this; }
    public function isReady(): bool { return $this->isReady; }
    public function setIsReady(bool $isReady): static { $this->isReady = $isReady; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}