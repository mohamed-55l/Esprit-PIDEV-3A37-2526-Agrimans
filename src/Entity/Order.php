<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\OrderRepository;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'order')]
class Order
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $total_amount = null;

    public function getTotal_amount(): ?float
    {
        return $this->total_amount;
    }

    public function setTotal_amount(float $total_amount): self
    {
        $this->total_amount = $total_amount;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $order_date = null;

    public function getOrder_date(): ?\DateTimeInterface
    {
        return $this->order_date;
    }

    public function setOrder_date(\DateTimeInterface $order_date): self
    {
        $this->order_date = $order_date;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order')]
    private Collection $orderItems;

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        if (!$this->orderItems instanceof Collection) {
            $this->orderItems = new ArrayCollection();
        }
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->getOrderItems()->contains($orderItem)) {
            $this->getOrderItems()->add($orderItem);
        }
        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        $this->getOrderItems()->removeElement($orderItem);
        return $this;
    }

}
