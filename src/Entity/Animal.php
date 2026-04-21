<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
#[ORM\Table(name: 'animal')]
#[Vich\Uploadable]
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

    /** Colonne SQL `name` (schéma d’origine). API PHP inchangée : getNom() / setNom(). */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
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

    public function setName(string $name): self
    {
        $this->nom = $name;

        return $this;
    }

    #[ORM\Column(name: 'type', type: 'string', length: 100, nullable: false)]
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

    public function setType(string $type): self
    {
        $this->espece = $type;

        return $this;
    }

    #[ORM\Column(name: 'breed', type: 'string', length: 100, nullable: true)]
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

    public function setBreed(?string $breed): self
    {
        $this->race = $breed;

        return $this;
    }

    #[ORM\Column(name: 'weight', type: 'float', nullable: true)]
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

    public function getWeight(): ?float
    {
        return $this->poids;
    }

    public function setWeight(?float $weight): self
    {
        $this->poids = $weight;

        return $this;
    }

    #[ORM\Column(name: 'health_status', type: 'string', length: 50, nullable: true)]
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

    public function getHealthStatus(): ?string
    {
        return $this->etatSante;
    }

    public function setHealthStatus(?string $healthStatus): self
    {
        $this->etatSante = $healthStatus;

        return $this;
    }

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: true)]
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

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateNaissance = null;

    public function getDateNaissance(): ?\DateTimeImmutable
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeImmutable $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function isArchived(): bool
    {
        return $this->deletedAt !== null;
    }

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    #[Vich\UploadableField(mapping: 'animal_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $externalImageUrl = null;

    public function getExternalImageUrl(): ?string
    {
        return $this->externalImageUrl;
    }

    public function setExternalImageUrl(?string $externalImageUrl): self
    {
        $this->externalImageUrl = $externalImageUrl;

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
