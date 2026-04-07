<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
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

    #[ORM\OneToMany(targetEntity: Animal::class, mappedBy: 'user')]
    private Collection $animals;

    /**
     * @return Collection<int, Animal>
     */
    public function getAnimals(): Collection
    {
        if (!$this->animals instanceof Collection) {
            $this->animals = new ArrayCollection();
        }
        return $this->animals;
    }

    public function addAnimal(Animal $animal): self
    {
        if (!$this->getAnimals()->contains($animal)) {
            $this->getAnimals()->add($animal);
        }
        return $this;
    }

    public function removeAnimal(Animal $animal): self
    {
        $this->getAnimals()->removeElement($animal);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'user')]
    private Collection $cartItems;

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        if (!$this->cartItems instanceof Collection) {
            $this->cartItems = new ArrayCollection();
        }
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->getCartItems()->contains($cartItem)) {
            $this->getCartItems()->add($cartItem);
        }
        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        $this->getCartItems()->removeElement($cartItem);
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

    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'user')]
    private Collection $demandes;

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        if (!$this->demandes instanceof Collection) {
            $this->demandes = new ArrayCollection();
        }
        return $this->demandes;
    }

    public function addDemande(Demande $demande): self
    {
        if (!$this->getDemandes()->contains($demande)) {
            $this->getDemandes()->add($demande);
        }
        return $this;
    }

    public function removeDemande(Demande $demande): self
    {
        $this->getDemandes()->removeElement($demande);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        if (!$this->orders instanceof Collection) {
            $this->orders = new ArrayCollection();
        }
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->getOrders()->contains($order)) {
            $this->getOrders()->add($order);
        }
        return $this;
    }

    public function removeOrder(Order $order): self
    {
        $this->getOrders()->removeElement($order);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Parcelle::class, mappedBy: 'user')]
    private Collection $parcelles;

    /**
     * @return Collection<int, Parcelle>
     */
    public function getParcelles(): Collection
    {
        if (!$this->parcelles instanceof Collection) {
            $this->parcelles = new ArrayCollection();
        }
        return $this->parcelles;
    }

    public function addParcelle(Parcelle $parcelle): self
    {
        if (!$this->getParcelles()->contains($parcelle)) {
            $this->getParcelles()->add($parcelle);
        }
        return $this;
    }

    public function removeParcelle(Parcelle $parcelle): self
    {
        $this->getParcelles()->removeElement($parcelle);
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

    #[ORM\OneToMany(targetEntity: UserOtp::class, mappedBy: 'user')]
    private Collection $userOtps;

    /**
     * @return Collection<int, UserOtp>
     */
    public function getUserOtps(): Collection
    {
        if (!$this->userOtps instanceof Collection) {
            $this->userOtps = new ArrayCollection();
        }
        return $this->userOtps;
    }

    public function addUserOtp(UserOtp $userOtp): self
    {
        if (!$this->getUserOtps()->contains($userOtp)) {
            $this->getUserOtps()->add($userOtp);
        }
        return $this;
    }

    public function removeUserOtp(UserOtp $userOtp): self
    {
        $this->getUserOtps()->removeElement($userOtp);
        return $this;
    }

}
