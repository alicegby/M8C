<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $passwordHash = null;

    #[ORM\Column(length: 20)]
    private string $authProvider = 'email';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $authProviderId = null;

    #[ORM\Column(length: 100)]
    private string $prenom;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarUrl = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dob = null;

    #[ORM\Column]
    private bool $notifications = true;

    #[ORM\Column]
    private bool $newsletter = false;

    #[ORM\Column(length: 20)]
    private string $role = 'user';

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\Column]
    private bool $isDeleted = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Purchase::class)]
    private Collection $purchases;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserMurderParty::class)]
    private Collection $userMurderParties;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PushToken::class)]
    private Collection $pushTokens;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiresAt = null;

    public function __construct()
    {
        $this->purchases        = new ArrayCollection();
        $this->userMurderParties = new ArrayCollection();
        $this->pushTokens       = new ArrayCollection();
        $this->createdAt        = new \DateTimeImmutable();
        $this->updatedAt        = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getUserIdentifier(): string { return $this->email; }

    public function getRoles(): array
    {
        return [$this->role === 'admin' ? 'ROLE_ADMIN' : 'ROLE_USER'];
    }

    public function getPassword(): ?string { return $this->passwordHash; }
    public function eraseCredentials(): void {}

    // Getters & Setters
    public function getId(): ?string { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function setPasswordHash(?string $h): static { $this->passwordHash = $h; return $this; }
    public function getAuthProvider(): string { return $this->authProvider; }
    public function setAuthProvider(string $v): static { $this->authProvider = $v; return $this; }
    public function getAuthProviderId(): ?string { return $this->authProviderId; }
    public function setAuthProviderId(?string $v): static { $this->authProviderId = $v; return $this; }
    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $v): static { $this->prenom = $v; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $v): static { $this->nom = $v; return $this; }
    public function getPseudo(): ?string { return $this->pseudo; }
    public function setPseudo(?string $v): static { $this->pseudo = $v; return $this; }
    public function getAvatarUrl(): ?string { return $this->avatarUrl; }
    public function setAvatarUrl(?string $v): static { $this->avatarUrl = $v; return $this; }
    public function getDob(): ?\DateTimeInterface { return $this->dob; }
    public function setDob(?\DateTimeInterface $v): static { $this->dob = $v; return $this; }
    public function isNotifications(): bool { return $this->notifications; }
    public function setNotifications(bool $v): static { $this->notifications = $v; return $this; }
    public function isNewsletter(): bool { return $this->newsletter; }
    public function setNewsletter(bool $v): static { $this->newsletter = $v; return $this; }
    public function getRole(): string { return $this->role; }
    public function setRole(string $v): static { $this->role = $v; return $this; }
    public function isVerified(): bool { return $this->isVerified; }
    public function setIsVerified(bool $v): static { $this->isVerified = $v; return $this; }
    public function getEmailVerificationToken(): ?string { return $this->emailVerificationToken; }
    public function setEmailVerificationToken(?string $v): static { $this->emailVerificationToken = $v; return $this; }
    public function isDeleted(): bool { return $this->isDeleted; }
    public function setIsDeleted(bool $v): static { $this->isDeleted = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function getPurchases(): Collection { return $this->purchases; }
    public function getUserMurderParties(): Collection { return $this->userMurderParties; }
    public function getPushTokens(): Collection { return $this->pushTokens; }
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeInterface $resetTokenExpiresAt): static
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
        return $this;
    }
    public function getPlayedMurderParties(): array
    {
        return $this->userMurderParties->filter(fn($ump) => $ump->isPlayed())->toArray();
    }
}