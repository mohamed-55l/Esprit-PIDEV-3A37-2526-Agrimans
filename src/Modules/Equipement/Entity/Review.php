<?php
namespace App\Modules\Equipement\Entity;
use App\Modules\Equipement\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'review')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int \ = null;

    #[ORM\Column(type: 'text')]
    private ?string \ = null;

    #[ORM\Column(type: 'integer')]
    private ?int \ = null;

    #[ORM\Column(name: 'date_review', type: 'date')]
    private ?\DateTimeInterface \ = null;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private ?int \ = null;

    #[ORM\ManyToOne(targetEntity: Equipement::class)]
    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id')]
    private ?Equipement \ = null;
}