<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\AnimalHistory;
use App\Entity\AnimalNourriture;
use App\Entity\User;
use App\Form\AnimalNourritureType;
use App\Repository\AnimalRepository;
use App\Service\AnimalActivityLogger;
use App\Service\AnimalNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/waad/animal/{animalId}/feeding')]
#[IsGranted('ROLE_USER')]
class AnimalNourritureController extends AbstractController
{
    private function actorUserId(): ?int
    {
        $u = $this->getUser();

        return $u instanceof User ? $u->getId() : null;
    }

    #[Route('/new', name: 'waad_feeding_new', methods: ['POST'])]
    public function new(
        Request $request,
        int $animalId,
        EntityManagerInterface $em,
        AnimalRepository $animalRepo,
        AnimalActivityLogger $logger,
        AnimalNotifier $notifier,
    ): Response {
        $animal = $animalRepo->findOneActiveById($animalId, $this->actorUserId());
        if (!$animal) {
            throw $this->createNotFoundException('Animal not found.');
        }

        $feeding = new AnimalNourriture();
        $feeding->setAnimal($animal);

        $form = $this->createForm(AnimalNourritureType::class, $feeding);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($feeding);
            $em->flush();
            $uid = $this->actorUserId();
            $logger->log(AnimalHistory::ACTION_FEEDING_CREATED, $animal, $uid, $logger->snapshotFeeding($feeding));
            if ($uid !== null) {
                $notifier->notifyFeedingChanged('Nouvel enregistrement de repas', $animal, $uid);
            }
            $em->flush();
            $this->addFlash('success', 'Feeding record added.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_animal_show', ['id' => $animalId]);
    }

    #[Route('/{id}/edit', name: 'waad_feeding_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        int $animalId,
        AnimalNourriture $feeding,
        EntityManagerInterface $em,
        AnimalRepository $animalRepo,
        AnimalActivityLogger $logger,
        AnimalNotifier $notifier,
    ): Response {
        $animal = $animalRepo->findOneActiveById($animalId, $this->actorUserId());
        if (!$animal || $feeding->getAnimal()?->getId() !== $animalId) {
            throw $this->createNotFoundException('Feeding record not found for this animal.');
        }

        $form = $this->createForm(AnimalNourritureType::class, $feeding);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uid = $this->actorUserId();
            $logger->log(AnimalHistory::ACTION_FEEDING_UPDATED, $animal, $uid, $logger->snapshotFeeding($feeding));
            if ($uid !== null) {
                $notifier->notifyFeedingChanged('Repas modifié', $animal, $uid);
            }
            $em->flush();
            $this->addFlash('success', 'Feeding record updated.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_animal_show', ['id' => $animalId]);
    }

    #[Route('/{id}/delete', name: 'waad_feeding_delete', methods: ['POST'])]
    public function delete(
        int $animalId,
        AnimalNourriture $feeding,
        EntityManagerInterface $em,
        AnimalRepository $animalRepo,
        AnimalActivityLogger $logger,
        AnimalNotifier $notifier,
    ): Response {
        $animal = $animalRepo->findOneActiveById($animalId, $this->actorUserId());
        if (!$animal || $feeding->getAnimal()?->getId() !== $animalId) {
            throw $this->createNotFoundException('Feeding record not found for this animal.');
        }

        $uid = $this->actorUserId();
        $logger->log(AnimalHistory::ACTION_FEEDING_DELETED, $animal, $uid, $logger->snapshotFeeding($feeding));
        if ($uid !== null) {
            $notifier->notifyFeedingChanged('Repas supprimé', $animal, $uid);
        }
        $em->remove($feeding);
        $em->flush();
        $this->addFlash('success', 'Feeding record deleted.');

        return $this->redirectToRoute('waad_animal_show', ['id' => $animalId]);
    }
}
