<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository; 
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)] 
#[ORM\Table(name: '`user`')] 
class User implements UserInterface, PasswordAuthenticatedUserInterface 
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

<<<<<<< HEAD
    public function setRole(UserRole $role): static
=======
    public function setRole(?string $role): self
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e
    {
        $this->role = $role;
        return $this;
    }

<<<<<<< HEAD
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
=======
    #[ORM\Column(name: 'ferme_id', type: 'integer', nullable: true)]
    private ?int $ferme_id = null;

    public function getFermeId(): ?int
    {
        return $this->ferme_id;
    }

    public function setFermeId(?int $ferme_id): self
    {
        $this->ferme_id = $ferme_id;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Cart::class, mappedBy: 'user')]
    private Collection $carts;

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        if (!$this->carts instanceof Collection) {
            $this->carts = new ArrayCollection();
        }
        return $this->carts;
    }

    public function addCart(Cart $cart): self
    {
        if (!$this->getCarts()->contains($cart)) {
            $this->getCarts()->add($cart);
        }
        return $this;
    }

    public function removeCart(Cart $cart): self
    {
        $this->getCarts()->removeElement($cart);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'user')]
    private Collection $products;

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        if (!$this->products instanceof Collection) {
            $this->products = new ArrayCollection();
        }
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->getProducts()->contains($product)) {
            $this->getProducts()->add($product);
        }
        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->getProducts()->removeElement($product);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'user')]
    private Collection $reviews;

    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        if (!$this->reviews instanceof Collection) {
            $this->reviews = new ArrayCollection();
        }
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->getReviews()->contains($review)) {
            $this->getReviews()->add($review);
        }
        return $this;
    }

    public function removeReview(Review $review): self
    {
        $this->getReviews()->removeElement($review);
        return $this;
    }

    public function getFullName(): ?string
    {
        if ($this->prenom && $this->nom) {
            return $this->prenom . ' ' . $this->nom;
        }
        return $this->prenom ?? $this->nom;
    }

    public function setFullName(string $full_name): static
    {
        // Split full name into first and last name
        $parts = explode(' ', $full_name, 2);
        $this->prenom = $parts[0] ?? null;
        $this->nom = $parts[1] ?? null;

        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->password;
    }

    public function setPasswordHash(string $password_hash): static
    {
        $this->password = $password_hash;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        // No created_at in the user table, return null
        return null;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        // No created_at in the user table, do nothing
        return $this;
    }

    // ===== MÉTHODES REQUISES PAR SYMFONY =====
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

<<<<<<< HEAD
    public function getRoles(): array
    {
        return ['ROLE_' . $this->role->value];
    }

    public function eraseCredentials(): void {}
}
=======
    public function getUsername(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $currentRole = $this->role ?? 'USER';
        if (strpos($currentRole, 'ROLE_') !== 0) {
            $currentRole = 'ROLE_' . $currentRole;
        }
        return [$currentRole];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // Rien à faire ici
    }

}
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e
