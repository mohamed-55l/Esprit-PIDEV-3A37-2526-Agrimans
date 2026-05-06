<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\NourritureRepository;

#[ORM\Entity(repositoryClass: NourritureRepository::class)]
#[ORM\Table(name: 'nourriture')]
class Nourriture
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $unit = null;

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nutritional_value = null;

    public function getNutritional_value(): ?string
    {
        return $this->nutritional_value;
    }

    public function setNutritional_value(?string $nutritional_value): self
    {
        $this->nutritional_value = $nutritional_value;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
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

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $cost = null;

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(?string $cost): self
    {
        $this->cost = $cost;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_added = null;

    public function getDate_added(): ?\DateTimeInterface
    {
        return $this->date_added;
    }

    public function setDate_added(?\DateTimeInterface $date_added): self
    {
        $this->date_added = $date_added;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: false)]
    private ?bool $is_active = null;

    public function is_active(): ?bool
    {
        return $this->is_active;
    }

    public function setIs_active(bool $is_active): self
    {
        $this->is_active = $is_active;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: AnimalNourriture::class, mappedBy: 'nourriture')]
    private Collection $animalNourritures;

    public function __construct()
    {
        $this->animalNourritures = new ArrayCollection();
    }

    /**
     * @return Collection<int, AnimalNourriture>
     */
    public function getAnimalNourritures(): Collection
    {
        if (!$this->animalNourritures instanceof Collection) {
            $this->animalNourritures = new ArrayCollection();
        }
        return $this->animalNourritures;
    }

    public function addAnimalNourriture(AnimalNourriture $animalNourriture): self
    {
        if (!$this->getAnimalNourritures()->contains($animalNourriture)) {
            $this->getAnimalNourritures()->add($animalNourriture);
        }
        return $this;
    }

    public function removeAnimalNourriture(AnimalNourriture $animalNourriture): self
    {
        $this->getAnimalNourritures()->removeElement($animalNourriture);
        return $this;
    }

    public function getNutritionalValue(): ?string
    {
        return $this->nutritional_value;
    }

    public function setNutritionalValue(?string $nutritional_value): static
    {
        $this->nutritional_value = $nutritional_value;

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

    public function getDateAdded(): ?\DateTime
    {
        return $this->date_added;
    }

    public function setDateAdded(?\DateTime $date_added): static
    {
        $this->date_added = $date_added;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

}
