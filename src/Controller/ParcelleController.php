<?php

namespace App\Controller;

use App\Form\ParcelleType;
use App\Entity\Parcelle;
use App\Repository\ParcelleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/parcelle')]
final class ParcelleController extends AbstractController
{
    #[Route(name: 'app_parcelle_index', methods: ['GET'])]
    public function index(Request $request, ParcelleRepository $parcelleRepository, \Knp\Component\Pager\PaginatorInterface $paginator): Response
    {
        $search = trim((string) $request->query->get('search', ''));

        // Admin voit toutes les parcelles, user voit uniquement les siennes
        if ($this->isGranted('ROLE_ADMIN')) {
            $query = $search !== ''
                ? $parcelleRepository->findBySearchTerm($search)
                : $parcelleRepository->findAllOrderBySuperficieDesc();
        } else {
            $currentUser = $this->getUser();
            $query = $search !== ''
                ? $parcelleRepository->findBySearchTermAndUser($search, $currentUser)
                : $parcelleRepository->findByUser($currentUser);
        }

        $parcelles = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

        return $this->render('parcelle/index.html.twig', [
            'parcelles' => $parcelles,
            'search'    => $search,
        ]);
    }

    #[Route('/new', name: 'app_parcelle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcelle    = new Parcelle();
        $isAdmin     = $this->isGranted('ROLE_ADMIN');

        // Admin peut choisir l'utilisateur, user connecté est auto-assigné
        $form = $this->createForm(ParcelleType::class, $parcelle, [
            'show_user_field' => $isAdmin,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si c'est un user connecté (non-admin), on assigne automatiquement
            if (!$isAdmin) {
                $parcelle->setUser($this->getUser());
            }

            $entityManager->persist($parcelle);
            $entityManager->flush();

            return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcelle/new.html.twig', [
            'parcelle' => $parcelle,
            'form'     => $form->createView(),
            'is_admin' => $isAdmin,
        ]);
    }

    #[Route('/{id}', name: 'app_parcelle_show', methods: ['GET'])]
    public function show(Parcelle $parcelle): Response
    {
        return $this->render('parcelle/show.html.twig', [
            'parcelle' => $parcelle,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_parcelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Parcelle $parcelle, EntityManagerInterface $entityManager): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $form = $this->createForm(ParcelleType::class, $parcelle, [
            'show_user_field' => $isAdmin,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si user non-admin, on ne change pas l'owner
            if (!$isAdmin && $parcelle->getUser() === null) {
                $parcelle->setUser($this->getUser());
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcelle/edit.html.twig', [
            'parcelle' => $parcelle,
            'form'     => $form->createView(),
            'is_admin' => $isAdmin,
        ]);
    }

    #[Route('/{id}', name: 'app_parcelle_delete', methods: ['POST'])]
    public function delete(Request $request, Parcelle $parcelle, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$parcelle->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($parcelle);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
    }
}

