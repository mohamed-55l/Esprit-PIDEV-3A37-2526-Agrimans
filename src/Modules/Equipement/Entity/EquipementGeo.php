<?php
namespace App\Modules\Equipement\Entity;
use App\Modules\Equipement\Repository\EquipementGeoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipementGeoRepository::class)]
#[ORM\Table(name: 'equipement_geo')]
class EquipementGeo
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\OneToOne(targetEntity: Equipement::class)]
    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id')]
    private ?Equipement \ = null;

    #[ORM\Column(name: 'garage_id', type: 'integer')]
    private ?int \ = null;

    #[ORM\Column(name: 'position_gps', type: 'string', length: 50)]
    private ?string \ = null;

    #[ORM\Column(name: 'statut_garage', type: 'string', length: 20)]
    private ?string \ = null;

    #[ORM\Column(name: 'derniere_localisation', type: 'datetime')]
    private ?\DateTimeInterface \ = null;
}