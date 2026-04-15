<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\UserOtpRepository;

#[ORM\Entity(repositoryClass: UserOtpRepository::class)]
#[ORM\Table(name: 'user_otp')]
class UserOtp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $user_id = null;

    public function getUser_id(): ?int
    {
        return $this->user_id;
    }

    public function setUser_id(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $otp_code = null;

    public function getOtp_code(): ?string
    {
        return $this->otp_code;
    }

    public function setOtp_code(string $otp_code): self
    {
        $this->otp_code = $otp_code;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $expires_at = null;

    public function getExpires_at(): ?\DateTimeInterface
    {
        return $this->expires_at;
    }

    public function setExpires_at(\DateTimeInterface $expires_at): self
    {
        $this->expires_at = $expires_at;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $attempts = null;

    public function getAttempts(): ?int
    {
        return $this->attempts;
    }

    public function setAttempts(?int $attempts): self
    {
        $this->attempts = $attempts;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getOtpCode(): ?string
    {
        return $this->otp_code;
    }

    public function setOtpCode(string $otp_code): static
    {
        $this->otp_code = $otp_code;

        return $this;
    }

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expires_at;
    }

    public function setExpiresAt(\DateTime $expires_at): static
    {
        $this->expires_at = $expires_at;

        return $this;
    }

}
