<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stats')]
class Stat
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $type;

    #[ORM\Column(type: Types::JSON)]
    private array $data = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'now()'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getData(): array { return $this->data; }
    public function setData(array $data): static { $this->data = $data; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}