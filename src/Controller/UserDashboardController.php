<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\EquipementRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserDashboardController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD PRINCIPAL
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(): Response
    {
        $stats = [
            'total_parcelles'   => 0,
            'total_cultures'    => 0,
            'total_animaux'     => 0,
            'commandes_en_cours'=> 0,
            'alertes'           => 0,
        ];

        return $this->render('user/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MES ÉQUIPEMENTS (équipements assignés à l'utilisateur connecté)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/user/equipements', name: 'user_equipement_index', methods: ['GET'])]
    public function mesEquipements(EquipementRepository $equipementRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $equipements = $equipementRepository->findByUser($user);

        return $this->render('user/equipements/index.html.twig', [
            'equipements' => $equipements,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MES REVIEWS
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/user/reviews', name: 'user_review_index', methods: ['GET'])]
    public function mesReviews(ReviewRepository $reviewRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $reviews = $reviewRepository->findByUser($user);

        return $this->render('user/reviews/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/user/reviews/new', name: 'user_review_new', methods: ['GET', 'POST'])]
    public function newReview(
        Request $request,
        EntityManagerInterface $em,
        EquipementRepository $equipementRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $review = new Review();
        $review->setUser($user);
        $review->setDateReview(new \DateTime());

        // Pré-sélectionner l'équipement si eq_id est passé en paramètre
        $eqId = $request->query->get('eq_id');
        $equipement = null;
        if ($eqId) {
            $equipement = $equipementRepository->find($eqId);
            // Sécurité : l'équipement doit appartenir à l'utilisateur connecté
            if ($equipement && $equipement->getUser() !== $user) {
                throw $this->createAccessDeniedException('Cet équipement ne vous appartient pas.');
            }
            if ($equipement) {
                $review->setEquipement($equipement);
            }
        }

        $form = $this->createForm(ReviewType::class, $review, [
            'user_equipements' => $equipementRepository->findByUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();
            $this->addFlash('success', 'Votre avis a été enregistré.');
            return $this->redirectToRoute('user_review_index');
        }

        return $this->render('user/reviews/new.html.twig', [
            'form'        => $form->createView(),
            'equipement'  => $equipement,
        ]);
    }

    #[Route('/user/reviews/{id}/edit', name: 'user_review_edit', methods: ['GET', 'POST'])]
    public function editReview(
        Request $request,
        Review $review,
        EntityManagerInterface $em,
        EquipementRepository $equipementRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Sécurité : seul l'auteur peut modifier
        if ($review->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres avis.');
        }

        $form = $this->createForm(ReviewType::class, $review, [
            'user_equipements' => $equipementRepository->findByUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Votre avis a été modifié.');
            return $this->redirectToRoute('user_review_index');
        }

        return $this->render('user/reviews/edit.html.twig', [
            'form'   => $form->createView(),
            'review' => $review,
        ]);
    }

    #[Route('/user/reviews/{id}/delete', name: 'user_review_delete', methods: ['POST'])]
    public function deleteReview(
        Request $request,
        Review $review,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Sécurité : seul l'auteur peut supprimer
        if ($review->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres avis.');
        }

        if ($this->isCsrfTokenValid('delete_rev_' . $review->getId(), $request->request->get('_token'))) {
            $em->remove($review);
            $em->flush();
            $this->addFlash('success', 'Votre avis a été supprimé.');
        }

        return $this->redirectToRoute('user_review_index');
    }
}
