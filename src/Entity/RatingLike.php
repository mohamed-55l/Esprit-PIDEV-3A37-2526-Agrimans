<?php

namespace App\Entity;

use App\Repository\RatingLikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingLikeRepository::class)]
#[ORM\Table(name: 'rating_likes')]
class RatingLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Rating::class, inversedBy: 'likes')]
    #[ORM\JoinColumn(name: 'rating_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Rating $rating = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isLike = true; // true = Like, false = Dislike

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?Rating
    {
        return $this->rating;
    }

    public function setRating(?Rating $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function isLike(): bool
    {
        return $this->isLike;
    }

    public function setIsLike(bool $isLike): self
    {
        $this->isLike = $isLike;
        return $this;
    }
}
