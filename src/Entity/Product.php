<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProductRepository;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product
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
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $price = null;

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $quantity = null;

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image = null;

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'seller_id', referencedColumnName: 'id')]
    private ?Users $user = null;

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $category = null;

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getCategoryIcon(): string
    {
        $cat = strtolower($this->category ?? '');
        return match (true) {
            str_contains($cat, 'fruit') => 'fa-apple-whole',
            str_contains($cat, 'légume') || str_contains($cat, 'legume') => 'fa-carrot',
            str_contains($cat, 'céréale') || str_contains($cat, 'cereale') => 'fa-wheat-awn',
            str_contains($cat, 'laitier') => 'fa-cheese',
            str_contains($cat, 'viande') => 'fa-drumstick-bite',
            str_contains($cat, 'semence') => 'fa-seedling',
            str_contains($cat, 'engrais') => 'fa-leaf',
            str_contains($cat, 'équipement') || str_contains($cat, 'equipement') => 'fa-tractor',
            default => 'fa-box',
        };
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $supplier = null;

    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    public function setSupplier(?string $supplier): self
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function getAverageRating(): float
    {
        if ($this->ratings->isEmpty()) {
            return 0.0;
        }

        $sum = 0;
        foreach ($this->ratings as $rating) {
            $sum += $rating->getRating();
        }

        return round($sum / $this->ratings->count(), 1);
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $expiry_date = null;

    public function getExpiry_date(): ?\DateTimeInterface
    {
        return $this->expiry_date;
    }

    public function setExpiry_date(?\DateTimeInterface $expiry_date): self
    {
        $this->expiry_date = $expiry_date;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'product')]
    private Collection $ratings;

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        if (!$this->ratings instanceof Collection) {
            $this->ratings = new ArrayCollection();
        }
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->getRatings()->contains($rating)) {
            $this->getRatings()->add($rating);
        }
        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        $this->getRatings()->removeElement($rating);
        return $this;
    }
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'product', cascade: ['persist', 'remove'])]
    private Collection $cartItems;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setProduct($this);
        }
        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->removeElement($cartItem)) {
            if ($cartItem->getProduct() === $this) {
                $cartItem->setProduct(null);
            }
        }
        return $this;
    }

    public function getExpiryDate(): ?\DateTime
    {
        return $this->expiry_date;
    }

    public function setExpiryDate(?\DateTime $expiry_date): static
    {
        $this->expiry_date = $expiry_date;

        return $this;
    }

}
