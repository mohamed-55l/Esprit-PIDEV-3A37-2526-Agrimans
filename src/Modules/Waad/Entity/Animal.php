<?php

namespace App\Modules\Waad\Entity;

use App\Modules\Waad\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
#[ORM\Table(name: 'animal')]
class Animal
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

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: "La race ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $breed = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "L'âge doit être un nombre positif ou zéro.")]
    #[Assert\LessThanOrEqual(value: 100, message: "L'âge ne peut pas dépasser 100 ans.")]
    private ?int $age = null;

    /** Stored as string to preserve DECIMAL precision; cast to float when needed. */
    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: "Le poids doit être un nombre positif ou zéro.")]
    private ?string $weight = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(choices: ['healthy', 'sick', 'recovering'], message: "Statut de santé invalide.")]
    private ?string $healthStatus = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAdded = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(choices: ['milk', 'meat', 'eggs', 'wool'], message: "Type de production invalide.")]
    private ?string $productionType = null;

    #[ORM\OneToMany(mappedBy: 'animal', targetEntity: AnimalNourriture::class, cascade: ['remove'], orphanRemoval: true)]
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

    public function getBreed(): ?string { return $this->breed; }
    public function setBreed(?string $breed): static { $this->breed = $breed; return $this; }

    public function getAge(): ?int { return $this->age; }
    public function setAge(?int $age): static { $this->age = $age; return $this; }

    public function getWeight(): ?string { return $this->weight; }
    public function setWeight(?string $weight): static { $this->weight = $weight; return $this; }

    public function getHealthStatus(): ?string { return $this->healthStatus; }
    public function setHealthStatus(?string $healthStatus): static { $this->healthStatus = $healthStatus; return $this; }

    public function getDateAdded(): ?\DateTimeInterface { return $this->dateAdded; }
    public function setDateAdded(?\DateTimeInterface $dateAdded): static { $this->dateAdded = $dateAdded; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getProductionType(): ?string { return $this->productionType; }
    public function setProductionType(?string $productionType): static { $this->productionType = $productionType; return $this; }

    public function getAnimalNourritures(): Collection { return $this->animalNourritures; }

    public function addAnimalNourriture(AnimalNourriture $animalNourriture): static
    {
        if (!$this->animalNourritures->contains($animalNourriture)) {
            $this->animalNourritures->add($animalNourriture);
            $animalNourriture->setAnimal($this);
        }
        return $this;
    }

    public function removeAnimalNourriture(AnimalNourriture $animalNourriture): static
    {
        if ($this->animalNourritures->removeElement($animalNourriture)) {
            if ($animalNourriture->getAnimal() === $this) {
                $animalNourriture->setAnimal(null);
            }
        }
        return $this;
    }
}
