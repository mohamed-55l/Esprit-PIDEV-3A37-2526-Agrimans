<?php

namespace App\Modules\Parcelle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Modules\Parcelle\Repository\ParcelleRepository;
use App\Entity\User;
use App\Entity\Culture;

#[ORM\Entity(repositoryClass: ParcelleRepository::class)]
#[ORM\Table(name: 'parcelle')]
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

    #[ORM\Column(type: 'decimal', nullable: false)]
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
    private ?string $type_sol = null;

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

    #[ORM\Column(type: 'decimal', nullable: true)]
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

    #[ORM\Column(type: 'decimal', nullable: true)]
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

    #[ORM\OneToMany(targetEntity: Culture::class, mappedBy: 'parcelle')]
    private Collection $cultures;

    /**
     * @return Collection<int, Culture>
     */
    public function getCultures(): Collection
    {
        if (!$this->cultures instanceof Collection) {
            $this->cultures = new ArrayCollection();
        }
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
