<?php

namespace App\Entity;

use App\Repository\EmailOtpRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailOtpRepository::class)]
class EmailOtp
{
     #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $expiry = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getExpiry(): ?\DateTime
    {
        return $this->expiry;
    }

    public function setExpiry(\DateTime $expiry): static
    {
        $this->expiry = $expiry;

        return $this;
    }
}
