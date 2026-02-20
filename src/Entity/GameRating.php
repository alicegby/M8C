<?php

namespace App\Entity;

use App\Repository\GameRatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRatingRepository::class)]
#[ORM\Table(name: 'game_ratings')]
#[ORM\UniqueConstraint(name: 'unique_rating_per_player', columns: ['game_session_id', 'game_player_id'])]
class GameRating
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: GameSession::class)]
    #[ORM\JoinColumn(nullable: false)]
    private GameSession $gameSession;

    #[ORM\ManyToOne(targetEntity: GamePlayer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private GamePlayer $gamePlayer;

    #[ORM\ManyToOne(targetEntity: MurderParty::class)]
    #[ORM\JoinColumn(nullable: false)]
    private MurderParty $murderParty;

    #[ORM\Column]
    private int $rating; // 1 à 5

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $ratedAt;

    public function __construct()
    {
        $this->ratedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getGameSession(): GameSession { return $this->gameSession; }
    public function setGameSession(GameSession $gameSession): static { $this->gameSession = $gameSession; return $this; }
    public function getGamePlayer(): GamePlayer { return $this->gamePlayer; }
    public function setGamePlayer(GamePlayer $gamePlayer): static { $this->gamePlayer = $gamePlayer; return $this; }
    public function getMurderParty(): MurderParty { return $this->murderParty; }
    public function setMurderParty(MurderParty $murderParty): static { $this->murderParty = $murderParty; return $this; }
    public function getRating(): int { return $this->rating; }
    public function setRating(int $rating): static { $this->rating = $rating; return $this; }
    public function getRatedAt(): \DateTimeInterface { return $this->ratedAt; }
    public function setRatedAt(\DateTimeInterface $ratedAt): static { $this->ratedAt = $ratedAt; return $this; }
}