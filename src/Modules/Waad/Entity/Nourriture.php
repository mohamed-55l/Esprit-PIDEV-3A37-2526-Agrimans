<?php

namespace App\Modules\Waad\Entity;

use App\Modules\Waad\Repository\NourritureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NourritureRepository::class)]
#[ORM\Table(name: 'nourriture')]
class Nourriture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit comporter au moins {{ limit }} caractères.", maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le type est obligatoire.")]
    #[Assert\Length(max: 100, maxMessage: "Le type ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $type = null;

    /** Stored as string to preserve DECIMAL precision; cast to float when needed. */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\Positive(message: "La quantité doit être un nombre positif.")]
    private ?string $quantity = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50, maxMessage: "L'unité ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $unit = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "La valeur nutritive ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $nutritionalValue = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThan("today", message: "La date d'expiration doit être dans le futur.")]
    private ?\DateTimeInterface $expiryDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le fournisseur ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $supplier = null;

    /** Stored as string to preserve DECIMAL precision; cast to float when needed. */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: "Le coût doit être un nombre positif ou zéro.")]
    private ?string $cost = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAdded = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\OneToMany(mappedBy: 'nourriture', targetEntity: AnimalNourriture::class)]
    private Collection $animalNourritures;

    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->animalNourritures = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getQuantity(): ?string { return $this->quantity; }
    public function setQuantity(string $quantity): static { $this->quantity = $quantity; return $this; }

    public function getUnit(): ?string { return $this->unit; }
    public function setUnit(?string $unit): static { $this->unit = $unit; return $this; }

    public function getNutritionalValue(): ?string { return $this->nutritionalValue; }
    public function setNutritionalValue(?string $nutritionalValue): static { $this->nutritionalValue = $nutritionalValue; return $this; }

    public function getExpiryDate(): ?\DateTimeInterface { return $this->expiryDate; }
    public function setExpiryDate(?\DateTimeInterface $expiryDate): static { $this->expiryDate = $expiryDate; return $this; }

    public function getSupplier(): ?string { return $this->supplier; }
    public function setSupplier(?string $supplier): static { $this->supplier = $supplier; return $this; }

    public function getCost(): ?string { return $this->cost; }
    public function setCost(?string $cost): static { $this->cost = $cost; return $this; }

    public function getDateAdded(): ?\DateTimeInterface { return $this->dateAdded; }
    public function setDateAdded(?\DateTimeInterface $dateAdded): static { $this->dateAdded = $dateAdded; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getAnimalNourritures(): Collection { return $this->animalNourritures; }
}
