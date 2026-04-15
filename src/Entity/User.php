<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $full_name = null;

    public function getFull_name(): ?string
    {
        return $this->full_name;
    }

    public function setFull_name(string $full_name): self
    {
        $this->full_name = $full_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $phone = null;

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $password_hash = null;

    public function getPassword_hash(): ?string
    {
        return $this->password_hash;
    }

    public function setPassword_hash(string $password_hash): self
    {
        $this->password_hash = $password_hash;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
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
        return $this->full_name;
    }

    public function setFullName(string $full_name): static
    {
        $this->full_name = $full_name;

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

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    // ===== MÉTHODES REQUISES PAR SYMFONY =====

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

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

    public function getPassword(): ?string
    {
        return $this->password_hash;
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
