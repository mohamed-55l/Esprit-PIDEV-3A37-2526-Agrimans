<?php

namespace App\Service;

use App\Entity\Animal;
use App\Entity\AnimalHistory;
use App\Entity\AnimalNourriture;
use Doctrine\ORM\EntityManagerInterface;

class AnimalActivityLogger
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function log(string $action, ?Animal $animal, ?int $userId, ?array $snapshot = null, ?string $detail = null): void
    {
        $row = new AnimalHistory();
        $row->setAction($action);
        $row->setAnimalId($animal?->getId());
        $row->setSnapshot($snapshot);
        $row->setDetail($detail);
        $row->setUserId($userId);
        $this->em->persist($row);
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshotAnimal(Animal $animal): array
    {
        return [
            'nom' => $animal->getNom(),
            'espece' => $animal->getEspece(),
            'race' => $animal->getRace(),
            'poids' => $animal->getPoids(),
            'etatSante' => $animal->getEtatSante(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshotFeeding(AnimalNourriture $feeding): array
    {
        return [
            'feeding_id' => $feeding->getId(),
            'animal_id' => $feeding->getAnimal()?->getId(),
            'quantity' => $feeding->getQuantity_fed(),
            'date' => $feeding->getFeeding_date()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
