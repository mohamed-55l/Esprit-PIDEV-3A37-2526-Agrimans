<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\AnimalRepository;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
#[ORM\Table(name: 'animal')]
class Animal
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
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->nom;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $espece = null;

    public function getEspece(): ?string
    {
        return $this->espece;
    }

    public function setEspece(string $espece): self
    {
        $this->espece = $espece;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->espece;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $race = null;

    public function getRace(): ?string
    {
        return $this->race;
    }

    public function setRace(?string $race): self
    {
        $this->race = $race;
        return $this;
    }

    public function getBreed(): ?string
    {
        return $this->race;
    }

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $poids = null;

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(?float $poids): self
    {
        $this->poids = $poids;
        return $this;
    }

    #[ORM\Column(name: 'etatSante', type: 'string', nullable: true)]
    private ?string $etatSante = null;

    public function getEtatSante(): ?string
    {
        return $this->etatSante;
    }

    public function setEtatSante(?string $etatSante): self
    {
        $this->etatSante = $etatSante;
        return $this;
    }

    #[ORM\Column(name: 'userId', type: 'integer', nullable: true)]
    private ?int $userId = null;

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: AnimalNourriture::class, mappedBy: 'animal')]
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

}
