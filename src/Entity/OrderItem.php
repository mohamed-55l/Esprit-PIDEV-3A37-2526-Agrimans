<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\OrderItemRepository;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_item')]
class OrderItem
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $order_id = null;

    public function getOrder_id(): ?int
    {
        return $this->order_id;
    }

    public function setOrder_id(int $order_id): self
    {
        $this->order_id = $order_id;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $product_id = null;

    public function getProduct_id(): ?int
    {
        return $this->product_id;
    }

    public function setProduct_id(int $product_id): self
    {
        $this->product_id = $product_id;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $quantity = null;

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    #[ORM\Column(type: 'decimal', precision: 10, scale: 3, nullable: false)]
    private ?string $price_at_purchase = null;

    public function getPrice_at_purchase(): ?string
    {
        return $this->price_at_purchase;
    }

    public function setPrice_at_purchase(string $price_at_purchase): self
    {
        $this->price_at_purchase = $price_at_purchase;
        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setOrderId(int $order_id): static
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(int $product_id): static
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getPriceAtPurchase(): ?string
    {
        return $this->price_at_purchase;
    }

    public function setPriceAtPurchase(string $price_at_purchase): static
    {
        $this->price_at_purchase = $price_at_purchase;

        return $this;
    }

}
