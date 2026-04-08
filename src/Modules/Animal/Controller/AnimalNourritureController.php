<?php

namespace App\Modules\Animal\Controller;

use App\Modules\Animal\Entity\Animal;
use App\Modules\Animal\Entity\AnimalNourriture;
use App\Modules\Animal\Form\AnimalNourritureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/waad/animal/{animalId}/feeding')]
class AnimalNourritureController extends AbstractController
{
    #[Route('/new', name: 'waad_feeding_new', methods: ['POST'])]
    public function new(Request $request, int $animalId, EntityManagerInterface $em): Response
    {
        $animal = $em->getRepository(Animal::class)->find($animalId);
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
            $this->addFlash('success', 'Feeding record added.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_animal_show', ['id' => $animalId]);
    }

    #[Route('/{id}/edit', name: 'waad_feeding_edit', methods: ['POST'])]
    public function edit(Request $request, int $animalId, AnimalNourriture $feeding, EntityManagerInterface $em): Response
    {
        if ($feeding->getAnimal()?->getId() !== $animalId) {
            throw $this->createNotFoundException('Feeding record not found for this animal.');
        }

        $form = $this->createForm(AnimalNourritureType::class, $feeding);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Feeding record updated.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_animal_show', ['id' => $animalId]);
    }

    #[Route('/{id}/delete', name: 'waad_feeding_delete', methods: ['POST'])]
    public function delete(int $animalId, AnimalNourriture $feeding, EntityManagerInterface $em): Response
    {
        if ($feeding->getAnimal()?->getId() !== $animalId) {
            throw $this->createNotFoundException('Feeding record not found for this animal.');
        }

        $em->remove($feeding);
        $em->flush();
        $this->addFlash('success', 'Feeding record deleted.');

        return $this->redirectToRoute('waad_animal_show', ['id' => $animalId]);
    }
}
