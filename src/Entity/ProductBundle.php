<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ProductBundleRepository;

#[ORM\Entity(repositoryClass: ProductBundleRepository::class)]
#[ORM\Table(name: 'product_bundles')]
class ProductBundle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 3, nullable: false)]
    private ?string $originalPrice = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 3, nullable: false)]
    private ?string $bundlePrice = null;

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $discountPercentage = 0.0;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[ORM\JoinTable(name: 'bundle_products')]
    #[ORM\JoinColumn(name: 'bundle_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'product_id', referencedColumnName: 'id')]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->calculateDiscount();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
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

    public function getOriginalPrice(): ?string
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(string $originalPrice): self
    {
        $this->originalPrice = $originalPrice;
        $this->calculateDiscount();
        return $this;
    }

    public function getBundlePrice(): ?string
    {
        return $this->bundlePrice;
    }

    public function setBundlePrice(string $bundlePrice): self
    {
        $this->bundlePrice = $bundlePrice;
        $this->calculateDiscount();
        return $this;
    }

    public function getDiscountPercentage(): ?float
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(float $discountPercentage): self
    {
        $this->discountPercentage = $discountPercentage;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }
        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);
        return $this;
    }

    public function calculateDiscount(): self
    {
        if ($this->originalPrice && $this->bundlePrice) {
            $discount = (($this->originalPrice - $this->bundlePrice) / $this->originalPrice) * 100;
            $this->discountPercentage = round($discount, 2);
        }
        return $this;
    }

    public function getSavings(): float
    {
        return round($this->originalPrice - $this->bundlePrice, 2);
    }
}
