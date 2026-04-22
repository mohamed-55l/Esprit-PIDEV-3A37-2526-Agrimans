<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository; // 🔴 رجعناها UserRepository
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)] // 🔴 رجعناها UserRepository
#[ORM\Table(name: '`user`')] // 🔴 رجعناها user (بالـ backticks باش ما تعملش مشكل مع الـ SQL)
class User implements UserInterface, PasswordAuthenticatedUserInterface // 🔴 الكلاس ولات User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $full_name = null;

    #[ORM\Column(length: 150, unique: true)] 
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $password_hash = null;

    #[ORM\Column(enumType: UserRole::class)]
    private ?UserRole $role = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    // 🔴 صلحتلك هذي: كانت ناقصة الـ ORM Attribute باش تتصب في الباز
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $faceDescriptor = null;

    // =========================
    // GETTERS / SETTERS face
    // =========================

    public function getFaceDescriptor(): ?array
    {
        return $this->faceDescriptor;
    }

    public function setFaceDescriptor(?array $faceDescriptor): static
    {
        $this->faceDescriptor = $faceDescriptor;
        return $this;
    }

    // =========================
    // GETTERS / SETTERS
    // =========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): static
    {
        $this->full_name = $full_name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password_hash;
    }

    public function setPassword(string $password): static
    {
        $this->password_hash = $password;
        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->password_hash;
    }

    public function setPasswordHash(string $password_hash): static
    {
        $this->password_hash = $password_hash;
        return $this;
    }

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    // =========================
    // SYMFONY SECURITY
    // =========================

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . $this->role->value];
    }

    public function eraseCredentials(): void {}
}