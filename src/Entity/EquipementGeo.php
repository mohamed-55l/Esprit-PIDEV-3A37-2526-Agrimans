<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EquipementGeoRepository;

#[ORM\Entity(repositoryClass: EquipementGeoRepository::class)]
#[ORM\Table(name: 'equipement_geo')]
class EquipementGeo
{
    #[ORM\ManyToOne(targetEntity: Equipement::class, inversedBy: 'equipementGeos')]
    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id')]
    private ?Equipement $equipement = null;

    public function getEquipement(): ?Equipement
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipement $equipement): self
    {
        $this->equipement = $equipement;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Garage::class, inversedBy: 'equipementGeos')]
    #[ORM\JoinColumn(name: 'garage_id', referencedColumnName: 'id')]
    private ?Garage $garage = null;

    public function getGarage(): ?Garage
    {
        return $this->garage;
    }

    public function setGarage(?Garage $garage): self
    {
        $this->garage = $garage;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $position_gps = null;

    public function getPosition_gps(): ?string
    {
        return $this->position_gps;
    }

    public function setPosition_gps(?string $position_gps): self
    {
        $this->position_gps = $position_gps;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut_garage = null;

    public function getStatut_garage(): ?string
    {
        return $this->statut_garage;
    }

    public function setStatut_garage(?string $statut_garage): self
    {
        $this->statut_garage = $statut_garage;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $derniere_localisation = null;

    public function getDerniere_localisation(): ?\DateTimeInterface
    {
        return $this->derniere_localisation;
    }

    public function setDerniere_localisation(?\DateTimeInterface $derniere_localisation): self
    {
        $this->derniere_localisation = $derniere_localisation;
        return $this;
    }

}
