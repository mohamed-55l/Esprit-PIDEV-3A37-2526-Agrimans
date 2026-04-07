<?php

namespace App\Modules\Waad\Entity;

use App\Modules\Waad\Repository\AnimalNourritureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalNourritureRepository::class)]
#[ORM\Table(name: 'animal_nourriture')]
class AnimalNourriture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'animalNourritures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animal $animal = null;

    #[ORM\ManyToOne(inversedBy: 'animalNourritures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Nourriture $nourriture = null;

    /** Stored as string to preserve DECIMAL precision; cast to float when needed. */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $quantityFed = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $feedingDate = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->feedingDate = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getAnimal(): ?Animal { return $this->animal; }
    public function setAnimal(?Animal $animal): static { $this->animal = $animal; return $this; }

    public function getNourriture(): ?Nourriture { return $this->nourriture; }
    public function setNourriture(?Nourriture $nourriture): static { $this->nourriture = $nourriture; return $this; }

    public function getQuantityFed(): ?string { return $this->quantityFed; }
    public function setQuantityFed(string $quantityFed): static { $this->quantityFed = $quantityFed; return $this; }

    public function getFeedingDate(): ?\DateTimeInterface { return $this->feedingDate; }
    public function setFeedingDate(?\DateTimeInterface $feedingDate): static { $this->feedingDate = $feedingDate; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
}
