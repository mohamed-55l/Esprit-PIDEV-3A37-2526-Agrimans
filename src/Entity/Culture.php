<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\CultureRepository;

#[ORM\Entity(repositoryClass: CultureRepository::class)]
#[ORM\Table(name: 'culture')]
class Culture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_culture', type: 'integer')]
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
    #[Assert\NotBlank(message: 'Le nom de la culture est obligatoire.')]
    #[Assert\Length(
        min: 2,
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

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le type de culture ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $type_culture = null;

    public function getTypeCulture(): ?string
    {
        return $this->type_culture;
    }

    public function setTypeCulture(?string $type_culture): self
    {
        $this->type_culture = $type_culture;
        return $this;
    }

    public function getType_culture(): ?string
    {
        return $this->getTypeCulture();
    }

    public function setType_culture(?string $type_culture): self
    {
        return $this->setTypeCulture($type_culture);
    }

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'La date de plantation ne peut pas être dans le futur.'
    )]
    private ?\DateTimeInterface $date_plantation = null;

    public function getDatePlantation(): ?\DateTimeInterface
    {
        return $this->date_plantation;
    }

    public function setDatePlantation(?\DateTimeInterface $date_plantation): self
    {
        $this->date_plantation = $date_plantation;
        return $this;
    }

    public function getDate_plantation(): ?\DateTimeInterface
    {
        return $this->getDatePlantation();
    }

    public function setDate_plantation(?\DateTimeInterface $date_plantation): self
    {
        return $this->setDatePlantation($date_plantation);
    }

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\GreaterThan(
        propertyPath: 'date_plantation',
        message: 'La date de récolte prévue doit être après la date de plantation.'
    )]
    private ?\DateTimeInterface $date_recolte_prevue = null;

    public function getDateRecoltePrevue(): ?\DateTimeInterface
    {
        return $this->date_recolte_prevue;
    }

    public function setDateRecoltePrevue(?\DateTimeInterface $date_recolte_prevue): self
    {
        $this->date_recolte_prevue = $date_recolte_prevue;
        return $this;
    }

    public function getDate_recolte_prevue(): ?\DateTimeInterface
    {
        return $this->getDateRecoltePrevue();
    }

    public function setDate_recolte_prevue(?\DateTimeInterface $date_recolte_prevue): self
    {
        return $this->setDateRecoltePrevue($date_recolte_prevue);
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'L\'état de la culture ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $etat_culture = null;

    public function getEtatCulture(): ?string
    {
        return $this->etat_culture;
    }

    public function setEtatCulture(?string $etat_culture): self
    {
        $this->etat_culture = $etat_culture;
        return $this;
    }

    public function getEtat_culture(): ?string
    {
        return $this->getEtatCulture();
    }

    public function setEtat_culture(?string $etat_culture): self
    {
        return $this->setEtatCulture($etat_culture);
    }

    #[ORM\ManyToOne(targetEntity: Parcelle::class, inversedBy: 'cultures')]
    #[ORM\JoinColumn(name: 'parcelle_id', referencedColumnName: 'id_parcelle')]
    #[Assert\NotNull(message: 'La parcelle est obligatoire.')]
    private ?Parcelle $parcelle = null;

    public function getParcelle(): ?Parcelle
    {
        return $this->parcelle;
    }

    public function setParcelle(?Parcelle $parcelle): self
    {
        $this->parcelle = $parcelle;
        return $this;
    }

}
