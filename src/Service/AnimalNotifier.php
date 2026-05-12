<?php

namespace App\Service;

use App\Entity\Animal;
use App\Entity\UserNotification;
use Doctrine\ORM\EntityManagerInterface;

class AnimalNotifier
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function notifyUser(int $userId, string $title, string $message, ?string $link = null): void
    {
        $n = new UserNotification();
        $n->setUserId($userId);
        $n->setTitle($title);
        $n->setMessage($message);
        $n->setLink($link);
        $n->setContext('animal');
        $this->em->persist($n);
    }

    public function notifyAnimalCreated(Animal $animal, int $actorUserId): void
    {
        $this->notifyUser(
            $actorUserId,
            'Animal ajouté',
            sprintf('L’animal « %s » (%s) a été enregistré.', $animal->getNom() ?? '', $animal->getEspece() ?? ''),
            $animal->getId() ? '/waad/animal/'.$animal->getId() : null,
        );
    }

    public function notifyAnimalUpdated(Animal $animal, int $actorUserId): void
    {
        $this->notifyUser(
            $actorUserId,
            'Animal modifié',
            sprintf('Les informations de « %s » ont été mises à jour.', $animal->getNom() ?? ''),
            $animal->getId() ? '/waad/animal/'.$animal->getId() : null,
        );
    }

    public function notifyAnimalArchived(Animal $animal, int $actorUserId): void
    {
        $this->notifyUser(
            $actorUserId,
            'Animal archivé',
            sprintf('« %s » a été retiré du cheptel actif (archivage). Consultez l’historique ou les archives.', $animal->getNom() ?? ''),
            '/waad/animal/archive',
        );
    }

    public function notifyFeedingChanged(string $label, Animal $animal, int $actorUserId): void
    {
        $this->notifyUser(
            $actorUserId,
            'Suivi alimentaire',
            sprintf('%s — animal « %s ».', $label, $animal->getNom() ?? ''),
            $animal->getId() ? '/waad/animal/'.$animal->getId() : null,
        );
    }
}
