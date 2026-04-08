<?php

namespace App\Modules\Animal\Entity;

use App\Modules\Animal\Repository\AnimalRepository;
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

    #[ORM\Column(name: 'nom', length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit comporter au moins {{ limit }} caractères.", maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $name = null;

    #[ORM\Column(name: 'espece', length: 100)]
    #[Assert\NotBlank(message: "Le type est obligatoire.")]
    #[Assert\Length(max: 100, maxMessage: "Le type ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $type = null;

    #[ORM\Column(name: 'race', length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: "La race ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $breed = null;

    /** Stored as string to preserve DECIMAL precision; cast to float when needed. */
    #[ORM\Column(name: 'poids', type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: "Le poids doit être un nombre positif ou zéro.")]
    private ?string $weight = null;

    #[ORM\Column(name: 'etatSante', length: 50, nullable: true)]
    #[Assert\Choice(choices: ['Sain', 'Malade', 'En récupération', 'Gestation'], message: "Statut de santé invalide.")]
    private ?string $healthStatus = null;

    #[ORM\Column(name: 'userId', nullable: true)]
    private ?int $userId = null;

    #[ORM\OneToMany(mappedBy: 'animal', targetEntity: AnimalNourriture::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $animalNourritures;

    public function __construct()
    {
        $this->animalNourritures = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getBreed(): ?string { return $this->breed; }
    public function setBreed(?string $breed): static { $this->breed = $breed; return $this; }

    public function getWeight(): ?string { return $this->weight; }
    public function setWeight(?string $weight): static { $this->weight = $weight; return $this; }

    public function getHealthStatus(): ?string { return $this->healthStatus; }
    public function setHealthStatus(?string $healthStatus): static { $this->healthStatus = $healthStatus; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): static { $this->userId = $userId; return $this; }

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
