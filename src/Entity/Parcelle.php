<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\ParcelleRepository;
use App\Entity\User;
use App\Entity\Culture;

use App\Validator\ValidCoordinates;

use App\Validator\CulturesSuperficieValid;

#[ORM\Entity(repositoryClass: ParcelleRepository::class)]
#[ORM\Table(name: 'parcelle')]
#[ValidCoordinates]
#[CulturesSuperficieValid]
class Parcelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_parcelle', type: 'integer')]
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
    #[Assert\NotBlank(message: 'Le nom de la parcelle est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom doit contenir au plus {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{Lu}][\p{L}\p{M}\'’\-]*.*$/u',
        message: 'Le premier mot du nom doit commencer par une majuscule.'
    )]
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

    #[ORM\Column(type: 'float', nullable: false)]
    #[Assert\NotBlank(message: 'La superficie est obligatoire.')]
    #[Assert\Positive(message: 'La superficie doit être un nombre positif.')]
    #[Assert\Range(
        min: 0.01,
        max: 1000,
        notInRangeMessage: 'La superficie doit être entre {{ min }} et {{ max }} hectares.'
    )]
    private ?float $superficie = null;

    public function getSuperficie(): ?float
    {
        return $this->superficie;
    }

    public function setSuperficie(float $superficie): self
    {
        $this->superficie = $superficie;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $localisation = null;

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): self
    {
        $this->localisation = $localisation;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Le type de sol ne peut pas dépasser {{ limit }} caractères.'
    )]
    public ?string $type_sol = null;

    public function getType_sol(): ?string
    {
        return $this->type_sol;
    }

    public function setType_sol(?string $type_sol): self
    {
        $this->type_sol = $type_sol;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'parcelles')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
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

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\Range(
        min: -90,
        max: 90,
        notInRangeMessage: 'La latitude doit être entre {{ min }} et {{ max }} degrés.'
    )]
    private ?float $latitude = null;

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\Range(
        min: -180,
        max: 180,
        notInRangeMessage: 'La longitude doit être entre {{ min }} et {{ max }} degrés.'
    )]
    private ?float $longitude = null;

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: 'App\Entity\Culture', mappedBy: 'parcelle')]
    private Collection $cultures;

    public function __construct()
    {
        $this->cultures = new ArrayCollection();
    }

    /**
     * @return Collection<int, Culture>
     */
    public function getCultures(): Collection
    {
        return $this->cultures;
    }

    public function addCulture(Culture $culture): self
    {
        if (!$this->getCultures()->contains($culture)) {
            $this->getCultures()->add($culture);
        }
        return $this;
    }

    public function removeCulture(Culture $culture): self
    {
        $this->getCultures()->removeElement($culture);
        return $this;
    }

}
