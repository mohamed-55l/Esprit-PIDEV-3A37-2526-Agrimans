<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReviewRepository;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'review')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $note = null;

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_review = null;

    public function getDate_review(): ?\DateTimeInterface
    {
        return $this->date_review;
    }

    public function setDate_review(?\DateTimeInterface $date_review): self
    {
        $this->date_review = $date_review;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Equipement::class, inversedBy: 'reviews')]
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

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?Users $user = null;

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user ? $this->user->getId() : null;
    }

    public function getDateReview(): ?\DateTime
    {
        return $this->date_review;
    }

    public function setDateReview(?\DateTime $date_review): static
    {
        $this->date_review = $date_review;

        return $this;
    }

}
