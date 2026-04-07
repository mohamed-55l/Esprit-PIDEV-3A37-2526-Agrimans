<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    // Add other fields as needed

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    // Add getter/setter for parcelles if needed
    #[ORM\OneToMany(targetEntity: 'App\Modules\Parcelle\Entity\Parcelle', mappedBy: 'user')]
    private Collection $parcelles;

    public function __construct()
    {
        $this->parcelles = new ArrayCollection();
    }

    /**
     * @return Collection<int, Parcelle>
     */
    public function getParcelles(): Collection
    {
        return $this->parcelles;
    }

    public function addParcelle($parcelle): self
    {
        if (!$this->parcelles->contains($parcelle)) {
            $this->parcelles->add($parcelle);
            $parcelle->setUser($this);
        }

        return $this;
    }

    public function removeParcelle($parcelle): self
    {
        if ($this->parcelles->removeElement($parcelle)) {
            // set the owning side to null (unless already changed)
            if ($parcelle->getUser() === $this) {
                $parcelle->setUser(null);
            }
        }

        return $this;
    }
}