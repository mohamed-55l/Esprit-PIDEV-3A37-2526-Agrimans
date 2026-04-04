<?php

namespace App\Modules\Equipement\Entity;

use App\Modules\Equipement\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'review')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

        #[ORM\Column(type: Types::TEXT, nullable: true)]
        #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide.")]
        #[Assert\Length(min: 10, minMessage: "Votre commentaire doit faire au moins {{ limit }} caractères.")]
        private ?string $commentaire = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotNull(message: "Veuillez attribuer une note.")]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: "La note doit être comprise entre 1 et 5.")]
    private ?int $note = null;

    #[ORM\Column(name: 'date_review', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReview = null;

    #[ORM\ManyToOne(targetEntity: Equipement::class)]
    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Veuillez sélectionner un équipement.")]
    private ?Equipement $equipement = null;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: true)]
    private ?int $userId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getDateReview(): ?\DateTimeInterface
    {
        return $this->dateReview;
    }

    public function setDateReview(?\DateTimeInterface $dateReview): self
    {
        $this->dateReview = $dateReview;
        return $this;
    }

    public function getEquipement(): ?Equipement
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipement $equipement): self
    {
        $this->equipement = $equipement;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }
}