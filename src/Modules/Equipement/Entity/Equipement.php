<?php
namespace App\Modules\Equipement\Entity;
use App\Modules\Equipement\Repository\EquipementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
#[ORM\Table(name: 'equipement')]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int \ = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string \ = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string \ = null;

    #[ORM\Column(type: 'float')]
    private ?float \ = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string \ = null;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private ?int \ = null;

    // Add getters/setters via your IDE
}