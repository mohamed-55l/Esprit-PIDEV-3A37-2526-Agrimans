<?php

namespace App\Modules\Equipement\Entity;

use App\Modules\Equipement\Repository\EquipementGeoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipementGeoRepository::class)]
#[ORM\Table(name: 'equipement_geo')]
class EquipementGeo
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Equipement::class)]
    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Equipement $equipement = null;

    #[ORM\Column(name: 'garage_id', type: 'integer', nullable: true)]
    private ?int $garageId = null;

    #[ORM\Column(name: 'position_gps', type: 'string', length: 50, nullable: true)]
    private ?string $positionGps = null;

    #[ORM\Column(name: 'statut_garage', type: 'string', length: 20, nullable: true, options: ['default' => 'DANS_GARAGE'])]
    private ?string $statutGarage = 'DANS_GARAGE';

    #[ORM\Column(name: 'derniere_localisation', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $derniereLocalisation = null;

    public function getEquipement(): ?Equipement
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipement $equipement): self
    {
        $this->equipement = $equipement;
        return $this;
    }

    public function getGarageId(): ?int
    {
        return $this->garageId;
    }

    public function setGarageId(?int $garageId): self
    {
        $this->garageId = $garageId;
        return $this;
    }

    public function getPositionGps(): ?string
    {
        return $this->positionGps;
    }

    public function setPositionGps(?string $positionGps): self
    {
        $this->positionGps = $positionGps;
        return $this;
    }

    public function getStatutGarage(): ?string
    {
        return $this->statutGarage;
    }

    public function setStatutGarage(?string $statutGarage): self
    {
        $this->statutGarage = $statutGarage;
        return $this;
    }

    public function getDerniereLocalisation(): ?\DateTimeInterface
    {
        return $this->derniereLocalisation;
    }

    public function setDerniereLocalisation(?\DateTimeInterface $derniereLocalisation): self
    {
        $this->derniereLocalisation = $derniereLocalisation;
        return $this;
    }
}