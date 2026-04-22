<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

use App\Repository\CultureRepository;

#[ORM\Entity(repositoryClass: CultureRepository::class)]
#[ORM\Table(name: 'culture')]
#[Vich\Uploadable]
class Culture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_culture = null;

    public function getId_culture(): ?int
    {
        return $this->id_culture;
    }

    public function getId(): ?int
    {
        return $this->id_culture;
    }

    public function setId_culture(int $id_culture): self
    {
        $this->id_culture = $id_culture;
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

    #[ORM\ManyToOne(targetEntity: Parcelle::class)]
    #[ORM\JoinColumn(name: 'parcelle_id', referencedColumnName: 'id_parcelle', nullable: true)]
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

    public function getIdCulture(): ?int
    {
        return $this->id_culture;
    }

    public function getTypeCulture(): ?string
    {
        return $this->type_culture;
    }

    public function setTypeCulture(?string $type_culture): static
    {
        $this->type_culture = $type_culture;

        return $this;
    }

    public function getDatePlantation(): ?\DateTime
    {
        return $this->date_plantation;
    }

    public function setDatePlantation(?\DateTime $date_plantation): static
    {
        $this->date_plantation = $date_plantation;

        return $this;
    }

    public function getDateRecoltePrevue(): ?\DateTime
    {
        return $this->date_recolte_prevue;
    }

    public function setDateRecoltePrevue(?\DateTime $date_recolte_prevue): static
    {
        $this->date_recolte_prevue = $date_recolte_prevue;

        return $this;
    }

    public function getEtatCulture(): ?string
    {
        return $this->etat_culture;
    }

    public function setEtatCulture(?string $etat_culture): static
    {
        $this->etat_culture = $etat_culture;

        return $this;
    }

    public function getParcelleId(): ?int
    {
        return $this->parcelle ? $this->parcelle->getId() : null;
    }

    public function setParcelleId(?int $parcelle_id): static
    {
        // This setter is deprecated. Use setParcelle() with the object instead.
        // It remains empty to avoid breaking legacy forms that just map parcelle_id without joining.
        return $this;
    }

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $infoFileName = null;

    #[Vich\UploadableField(mapping: 'culture_files', fileNameProperty: 'infoFileName')]
    private ?File $infoFile = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function setInfoFile(?File $infoFile = null): void
    {
        $this->infoFile = $infoFile;

        if (null !== $infoFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getInfoFile(): ?File
    {
        return $this->infoFile;
    }

    public function setInfoFileName(?string $infoFileName): void
    {
        $this->infoFileName = $infoFileName;
    }

    public function getInfoFileName(): ?string
    {
        return $this->infoFileName;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

}
