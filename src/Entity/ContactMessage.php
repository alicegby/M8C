<?php

namespace App\Entity;

use App\Repository\ContactMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactMessageRepository::class)]
#[ORM\Table(name: 'contact_messages')]
class ContactMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 100)]
    private string $prenom;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 50)]
    private string $subject; // 'information', 'custom_mp', 'devis', 'autre'

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column]
    private bool $isRead = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $sentAt;

    public function __construct()
    {
        $this->sentAt = new \DateTime();
    }

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $subject): static { $this->subject = $subject; return $this; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): static { $this->message = $message; return $this; }
    public function isRead(): bool { return $this->isRead; }
    public function setIsRead(bool $isRead): static { $this->isRead = $isRead; return $this; }
    public function getSentAt(): \DateTimeInterface { return $this->sentAt; }
    public function setSentAt(\DateTimeInterface $sentAt): static { $this->sentAt = $sentAt; return $this; }
}