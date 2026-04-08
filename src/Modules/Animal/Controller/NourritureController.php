<?php

namespace App\Modules\Animal\Controller;

use App\Modules\Animal\Entity\Nourriture;
use App\Modules\Animal\Form\NourritureType;
use App\Modules\Animal\Repository\NourritureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/waad/nourriture')]
class NourritureController extends AbstractController
{
    #[Route('', name: 'waad_nourriture_index', methods: ['GET'])]
    public function index(NourritureRepository $repo): Response
    {
        $nourritures = $repo->findAll();
        $form = $this->createForm(NourritureType::class, new Nourriture(), [
            'action' => $this->generateUrl('waad_nourriture_new'),
            'method' => 'POST',
        ]);

        $editForms = [];
        foreach ($nourritures as $nourriture) {
            $editForms[$nourriture->getId()] = $this->createForm(NourritureType::class, $nourriture, [
                'action' => $this->generateUrl('waad_nourriture_edit', ['id' => $nourriture->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('Animal/nourriture/index.html.twig', [
            'nourritures' => $nourritures,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);
    }

    #[Route('/new', name: 'waad_nourriture_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $nourriture = new Nourriture();
        $form = $this->createForm(NourritureType::class, $nourriture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($nourriture);
            $em->flush();
            $this->addFlash('success', 'Food added successfully.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_nourriture_index');
    }

    #[Route('/{id}/edit', name: 'waad_nourriture_edit', methods: ['POST'])]
    public function edit(Request $request, Nourriture $nourriture, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(NourritureType::class, $nourriture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Food updated successfully.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_nourriture_index');
    }

    #[Route('/{id}/delete', name: 'waad_nourriture_delete', methods: ['POST'])]
    public function delete(Nourriture $nourriture, EntityManagerInterface $em): Response
    {
        $em->remove($nourriture);
        $em->flush();
        $this->addFlash('success', 'Food deleted.');

        return $this->redirectToRoute('waad_nourriture_index');
    }
}
