<?php

namespace App\Entity;

use App\Repository\GameVoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameVoteRepository::class)]
#[ORM\Table(name: 'game_votes')]
#[ORM\UniqueConstraint(name: 'unique_vote_per_player', columns: ['game_session_id', 'voter_game_player_id'])]
class GameVote
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
    #[ORM\JoinColumn(name: 'voter_game_player_id', nullable: false)]
    private GamePlayer $voter;

    #[ORM\ManyToOne(targetEntity: Character::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Character $votedCharacter;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $votedAt;

    public function __construct()
    {
        $this->votedAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getGameSession(): GameSession { return $this->gameSession; }
    public function setGameSession(GameSession $gameSession): static { $this->gameSession = $gameSession; return $this; }
    public function getVoter(): GamePlayer { return $this->voter; }
    public function setVoter(GamePlayer $voter): static { $this->voter = $voter; return $this; }
    public function getVotedCharacter(): Character { return $this->votedCharacter; }
    public function setVotedCharacter(Character $votedCharacter): static { $this->votedCharacter = $votedCharacter; return $this; }
    public function getVotedAt(): \DateTimeInterface { return $this->votedAt; }
    public function setVotedAt(\DateTimeInterface $votedAt): static { $this->votedAt = $votedAt; return $this; }
}