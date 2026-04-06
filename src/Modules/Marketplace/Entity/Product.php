<?php

namespace App\Modules\Marketplace\Entity;

use App\Modules\Marketplace\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le nom doit comporter au moins {{ limit }} caractères.")]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    private ?string $description = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être supérieur à 0.")]
    private ?float $price = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\Positive(message: "La quantité doit être supérieure à 0.")]
    private ?int $quantity = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(name: 'seller_id', type: 'integer', nullable: true)]
    private ?int $sellerId = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    #[Assert\Choice(choices: ['VEGETABLES', 'FRUITS', 'GRAINS', 'HAY'], message: "Catégorie invalide.")]
    private ?string $category = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $supplier = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\GreaterThanOrEqual('today', message: "La date d'expiration ne peut pas être dans le passé.")]
    private ?\DateTimeInterface $expiryDate = null;

    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $ratings;

    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $cartItems;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getSellerId(): ?int
    {
        return $this->sellerId;
    }

    public function setSellerId(?int $sellerId): self
    {
        $this->sellerId = $sellerId;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    public function setSupplier(?string $supplier): self
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(?\DateTimeInterface $expiryDate): self
    {
        $this->expiryDate = $expiryDate;
        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setProduct($this);
        }
        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            if ($rating->getProduct() === $this) {
                $rating->setProduct(null);
            }
        }
        return $this;
    }

    public function getAverageRating(): float
    {
        if ($this->ratings->isEmpty()) {
            return 0;
        }
        $sum = 0;
        foreach ($this->ratings as $rating) {
            $sum += $rating->getRating();
        }
        return round($sum / $this->ratings->count(), 1);
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function getCategoryIcon(): string
    {
        return match ($this->category) {
            'VEGETABLES' => 'fa-carrot',
            'FRUITS' => 'fa-apple-whole',
            'GRAINS' => 'fa-wheat-awn',
            'HAY' => 'fa-seedling',
            default => 'fa-box',
        };
    }
}
