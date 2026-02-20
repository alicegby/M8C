<?php

namespace App\Entity;

use App\Repository\PackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackRepository::class)]
#[ORM\Table(name: 'packs')]
#[ORM\HasLifecycleCallbacks]
class Pack
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    #[ORM\ManyToMany(targetEntity: MurderParty::class, inversedBy: 'packs')]
    #[ORM\JoinTable(name: 'pack_murder_parties')]
    private Collection $murderParties;

    public function __construct()
    {
        $this->murderParties = new ArrayCollection();
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
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getPrice(): string { return $this->price; }
    public function setPrice(string $price): static { $this->price = $price; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function getMurderParties(): Collection { return $this->murderParties; }
    public function addMurderParty(MurderParty $murderParty): static
    {
        if (!$this->murderParties->contains($murderParty)) {
            $this->murderParties->add($murderParty);
        }
        return $this;
    }
    public function removeMurderParty(MurderParty $murderParty): static
    {
        $this->murderParties->removeElement($murderParty);
        return $this;
    }
}