<?php

namespace App\Modules\Parcelle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Modules\Parcelle\Repository\CultureRepository;

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
    private ?string $type_culture = null;

    public function getType_culture(): ?string
    {
        return $this->type_culture;
    }

    public function setType_culture(?string $type_culture): self
    {
        $this->type_culture = $type_culture;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_plantation = null;

    public function getDate_plantation(): ?\DateTimeInterface
    {
        return $this->date_plantation;
    }

    public function setDate_plantation(?\DateTimeInterface $date_plantation): self
    {
        $this->date_plantation = $date_plantation;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_recolte_prevue = null;

    public function getDate_recolte_prevue(): ?\DateTimeInterface
    {
        return $this->date_recolte_prevue;
    }

    public function setDate_recolte_prevue(?\DateTimeInterface $date_recolte_prevue): self
    {
        $this->date_recolte_prevue = $date_recolte_prevue;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $etat_culture = null;

    public function getEtat_culture(): ?string
    {
        return $this->etat_culture;
    }

    public function setEtat_culture(?string $etat_culture): self
    {
        $this->etat_culture = $etat_culture;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Parcelle::class, inversedBy: 'cultures')]
    #[ORM\JoinColumn(name: 'parcelle_id', referencedColumnName: 'id_parcelle')]
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
