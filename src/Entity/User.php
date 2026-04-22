<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
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

    #[ORM\Column(name: 'nom', type: 'string', nullable: true)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(name: 'prenom', type: 'string', nullable: true)]
    private ?string $prenom = null;

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    #[ORM\Column(name: 'email', type: 'string', nullable: true)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(name: 'password', type: 'string', nullable: true)]
    private ?string $password = null;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    #[ORM\Column(name: 'role', type: 'string', nullable: true)]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;
        return $this;
    }

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

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // Rien à faire ici
    }

}
