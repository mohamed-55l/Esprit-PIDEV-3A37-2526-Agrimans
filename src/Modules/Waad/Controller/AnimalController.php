<?php

namespace App\Modules\Waad\Controller;

use App\Modules\Waad\Entity\Animal;
use App\Modules\Waad\Form\AnimalType;
use App\Modules\Waad\Form\AnimalNourritureType;
use App\Modules\Waad\Entity\AnimalNourriture;
use App\Modules\Waad\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/waad/animal')]
class AnimalController extends AbstractController
{
    #[Route('', name: 'waad_animal_index', methods: ['GET'])]
    public function index(AnimalRepository $repo): Response
    {
        $animals = $repo->findAll();
        $form = $this->createForm(AnimalType::class, new Animal(), [
            'action' => $this->generateUrl('waad_animal_new'),
            'method' => 'POST',
        ]);

        $editForms = [];
        foreach ($animals as $animal) {
            $editForms[$animal->getId()] = $this->createForm(AnimalType::class, $animal, [
                'action' => $this->generateUrl('waad_animal_edit', ['id' => $animal->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('Waad/animal/index.html.twig', [
            'animals' => $animals,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);
    }

    #[Route('/new', name: 'waad_animal_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($animal);
            $em->flush();
            $this->addFlash('success', 'Animal added successfully.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_animal_index');
    }

    #[Route('/{id}', name: 'waad_animal_show', methods: ['GET'])]
    public function show(Animal $animal): Response
    {
        $editForm = $this->createForm(AnimalType::class, $animal, [
            'action' => $this->generateUrl('waad_animal_edit', ['id' => $animal->getId()]),
            'method' => 'POST',
        ]);

        $addFeedingForm = $this->createForm(AnimalNourritureType::class, new AnimalNourriture(), [
            'action' => $this->generateUrl('waad_feeding_new', ['animalId' => $animal->getId()]),
            'method' => 'POST',
        ]);

        $editFeedingForms = [];
        foreach ($animal->getAnimalNourritures() as $feeding) {
            $editFeedingForms[$feeding->getId()] = $this->createForm(AnimalNourritureType::class, $feeding, [
                'action' => $this->generateUrl('waad_feeding_edit', ['animalId' => $animal->getId(), 'id' => $feeding->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('Waad/animal/show.html.twig', [
            'animal' => $animal,
            'editForm' => $editForm->createView(),
            'addFeedingForm' => $addFeedingForm->createView(),
            'editFeedingForms' => $editFeedingForms,
        ]);
    }

    #[Route('/{id}/edit', name: 'waad_animal_edit', methods: ['POST'])]
    public function edit(Request $request, Animal $animal, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Animal updated successfully.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_animal_show', ['id' => $animal->getId()]);
    }

    #[Route('/{id}/delete', name: 'waad_animal_delete', methods: ['POST'])]
    public function delete(Animal $animal, EntityManagerInterface $em): Response
    {
        $em->remove($animal);
        $em->flush();
        $this->addFlash('success', 'Animal deleted.');

        return $this->redirectToRoute('waad_animal_index');
    }
}
