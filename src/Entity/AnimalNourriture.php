<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\AnimalNourritureRepository;

#[ORM\Entity(repositoryClass: AnimalNourritureRepository::class)]
#[ORM\Table(name: 'animal_nourriture')]
class AnimalNourriture
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

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $quantity_fed = null;

    public function getQuantity_fed(): ?float
    {
        return $this->quantity_fed;
    }

    public function setQuantity_fed(float $quantity_fed): self
    {
        $this->quantity_fed = $quantity_fed;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $feeding_date = null;

    public function getFeeding_date(): ?\DateTimeInterface
    {
        return $this->feeding_date;
    }

    public function setFeeding_date(?\DateTimeInterface $feeding_date): self
    {
        $this->feeding_date = $feeding_date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $notes = null;

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Animal::class, inversedBy: 'animalNourritures')]
    #[ORM\JoinColumn(name: 'animal_id', referencedColumnName: 'id')]
    private ?Animal $animal = null;

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): self
    {
        $this->animal = $animal;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Nourriture::class, inversedBy: 'animalNourritures')]
    #[ORM\JoinColumn(name: 'nourriture_id', referencedColumnName: 'id')]
    private ?Nourriture $nourriture = null;

    public function getNourriture(): ?Nourriture
    {
        return $this->nourriture;
    }

    public function setNourriture(?Nourriture $nourriture): self
    {
        $this->nourriture = $nourriture;
        return $this;
    }

    public function getQuantityFed(): ?string
    {
        return $this->quantity_fed;
    }

    public function setQuantityFed(string $quantity_fed): static
    {
        $this->quantity_fed = $quantity_fed;

        return $this;
    }

    public function getFeedingDate(): ?\DateTime
    {
        return $this->feeding_date;
    }

    public function setFeedingDate(?\DateTime $feeding_date): static
    {
        $this->feeding_date = $feeding_date;

        return $this;
    }

}
