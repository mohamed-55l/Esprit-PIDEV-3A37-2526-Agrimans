<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EquipementRepository;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
#[ORM\Table(name: 'equipement')]
class Equipement
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prix = null;

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $disponibilite = null;

    public function getDisponibilite(): ?string
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(?string $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'equipements')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
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

    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'equipement')]
    private Collection $demandes;

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        if (!$this->demandes instanceof Collection) {
            $this->demandes = new ArrayCollection();
        }
        return $this->demandes;
    }

    public function addDemande(Demande $demande): self
    {
        if (!$this->getDemandes()->contains($demande)) {
            $this->getDemandes()->add($demande);
        }
        return $this;
    }

    public function removeDemande(Demande $demande): self
    {
        $this->getDemandes()->removeElement($demande);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: EquipementGeo::class, mappedBy: 'equipement')]
    private Collection $equipementGeos;

    /**
     * @return Collection<int, EquipementGeo>
     */
    public function getEquipementGeos(): Collection
    {
        if (!$this->equipementGeos instanceof Collection) {
            $this->equipementGeos = new ArrayCollection();
        }
        return $this->equipementGeos;
    }

    public function addEquipementGeo(EquipementGeo $equipementGeo): self
    {
        if (!$this->getEquipementGeos()->contains($equipementGeo)) {
            $this->getEquipementGeos()->add($equipementGeo);
        }
        return $this;
    }

    public function removeEquipementGeo(EquipementGeo $equipementGeo): self
    {
        $this->getEquipementGeos()->removeElement($equipementGeo);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'equipement')]
    private Collection $reviews;

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        if (!$this->reviews instanceof Collection) {
            $this->reviews = new ArrayCollection();
        }
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->getReviews()->contains($review)) {
            $this->getReviews()->add($review);
        }
        return $this;
    }

    public function removeReview(Review $review): self
    {
        $this->getReviews()->removeElement($review);
        return $this;
    }

}
