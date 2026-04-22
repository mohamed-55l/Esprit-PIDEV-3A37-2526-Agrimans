<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ParcelleRepository;

#[ORM\Entity(repositoryClass: ParcelleRepository::class)]
#[ORM\Table(name: 'parcelle')]
class Parcelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_parcelle = null;

    public function getId_parcelle(): ?int
    {
        return $this->id_parcelle;
    }

    public function getId(): ?int
    {
        return $this->id_parcelle;
    }

    public function setId_parcelle(int $id_parcelle): self
    {
        $this->id_parcelle = $id_parcelle;
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

    #[ORM\Column(type: 'float', nullable: false)]
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

  
    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: true)]
    private ?Users $user = null; 

    public function getUsers(): ?Users // 
    {
        return $this->user;
    }

    public function setUser(?Users $user): self 
    {
        $this->user = $user;
        return $this;
    }

    public function getUtilisateur_id(): ?int
    {
        return $this->user ? $this->user->getId() : null;
    }

    public function setUtilisateur_id(?int $utilisateur_id): self
    {
        // Deprecated simple setter
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: true)]
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

    public function getIdParcelle(): ?int
    {
        return $this->id_parcelle;
    }

    public function getTypeSol(): ?string
    {
        return $this->type_sol;
    }

    public function setTypeSol(?string $type_sol): static
    {
        $this->type_sol = $type_sol;

        return $this;
    }

    public function getUtilisateurId(): ?int
    {
        return $this->user ? $this->user->getId() : null;
    }

    public function setUtilisateurId(?int $utilisateur_id): static
    {
        return $this;
    }

}