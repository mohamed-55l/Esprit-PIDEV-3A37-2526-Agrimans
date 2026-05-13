<?php

namespace App\Controller;

use App\Form\EquipementType;
use App\Entity\Equipement;
use App\Entity\Parcelle;
use App\Repository\EquipementRepository;
use App\Repository\ParcelleRepository;
use App\Service\EquipementRecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/equipement')]
final class EquipementController extends AbstractController
{
    #[Route(name: 'app_equipement_index', methods: ['GET'])]
    public function index(Request $request, EquipementRepository $equipementRepository): Response
    {
        $query = $request->query->get('q', '');
        $sortBy = $request->query->get('sort', 'nom');
        $sortOrder = $request->query->get('order', 'ASC');

        $equipements = $equipementRepository->searchAndSort($query, $sortBy, $sortOrder);
        $statistics = $equipementRepository->getStatistics();

        return $this->render('equipement/index.html.twig', [
            'equipements' => $equipements,
            'statistics' => $statistics,
            'currentQuery' => $query,
            'currentSort' => $sortBy,
            'currentOrder' => $sortOrder
        ]);
    }

    #[Route('/new', name: 'app_equipement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipement = new Equipement();
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipement);
            $entityManager->flush();

            return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipement/new.html.twig', [
            'equipement' => $equipement,
            'form' => $form->createView(),
        ]);
    }

    // (CRUD routes moved to the end of the file)

    /**
     * Matching Intelligent : Évalue un équipement pour une parcelle
     * Route : /equipement/{equipementId}/match/{parcelleId}
     */
    #[Route('/{equipementId}/match/{parcelleId}', name: 'app_equipement_match', methods: ['GET'])]
    public function matchEquipmentForParcelle(
        int $equipementId,
        int $parcelleId,
        EquipementRepository $equipementRepository,
        ParcelleRepository $parcelleRepository,
        EquipementRecommendationService $recommendationService
    ): Response {
        $equipement = $equipementRepository->find($equipementId);
        $parcelle = $parcelleRepository->find($parcelleId);

        if (!$equipement || !$parcelle) {
            throw $this->createNotFoundException('Équipement ou parcelle non trouvée');
        }

        $matchResult = $recommendationService->evaluateEquipmentForParcelle($equipement, $parcelle);

        return $this->render('equipement/match.html.twig', [
            'equipement' => $equipement,
            'parcelle' => $parcelle,
            'matchResult' => $matchResult,
        ]);
    }

    /**
     * Recommandations Intelligentes : Affiche les top 3 équipements pour une parcelle
     * Route : /equipement/recommend/{parcelleId}
     */
    #[Route('/recommend/{parcelleId}', name: 'app_equipement_recommend', methods: ['GET'])]
    public function recommendEquipmentsForParcelle(
        int $parcelleId,
        ParcelleRepository $parcelleRepository,
        EquipementRecommendationService $recommendationService
    ): Response {
        $parcelle = $parcelleRepository->find($parcelleId);

        if (!$parcelle) {
            throw $this->createNotFoundException('Parcelle non trouvée');
        }

        $recommendations = $recommendationService->recommendEquipmentsForParcelle($parcelle);

        return $this->render('equipement/recommend.html.twig', [
            'parcelle' => $parcelle,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * API JSON : Recommandations pour intégration frontend
     * Route : /equipement/api/recommend/{parcelleId}
     */
    #[Route('/api/recommend/{parcelleId}', name: 'app_equipement_api_recommend', methods: ['GET'])]
    public function apiRecommendEquipments(
        int $parcelleId,
        ParcelleRepository $parcelleRepository,
        EquipementRecommendationService $recommendationService
    ): JsonResponse {
        $parcelle = $parcelleRepository->find($parcelleId);

        if (!$parcelle) {
            return new JsonResponse(['error' => 'Parcelle non trouvée'], 404);
        }

        $recommendations = $recommendationService->recommendEquipmentsForParcelle($parcelle);
        $data = array_map(fn($rec) => $rec->toArray(), $recommendations);

        return new JsonResponse([
            'parcelle_id' => $parcelle->getId(),
            'parcelle_nom' => $parcelle->getNom(),
            'terrain_area' => $parcelle->getSuperficie(),
            'recommendations' => $data,
        ]);
    }

    /**
     * Page de démonstration : Sélectionner une parcelle et voir les recommandations
     * Route : /equipement/demo
     */
    #[Route('/demo', name: 'app_equipement_demo', methods: ['GET'])]
    public function demonstrationPage(
        ParcelleRepository $parcelleRepository,
        EquipementRepository $equipementRepository
    ): Response {
        $parcelles = $parcelleRepository->findAll();
        $equipements = $equipementRepository->findAll();

        return $this->render('equipement/demo.html.twig', [
            'parcelles' => $parcelles,
            'equipements' => $equipements,
            'totalParcelles' => count($parcelles),
            'totalEquipements' => count($equipements),
        ]);
    }

    /**
     * AJAX : Charger les recommandations pour une parcelle
     * Route : /equipement/ajax/recommendations/{parcelleId}
     */
    #[Route('/ajax/recommendations/{parcelleId}', name: 'app_equipement_ajax_recommendations', methods: ['GET'])]
    public function ajaxGetRecommendations(
        int $parcelleId,
        ParcelleRepository $parcelleRepository,
        EquipementRecommendationService $recommendationService
    ): JsonResponse {
        $parcelle = $parcelleRepository->find($parcelleId);

        if (!$parcelle) {
            return new JsonResponse(['error' => 'Parcelle non trouvée'], 404);
        }

        $recommendations = $recommendationService->recommendEquipmentsForParcelle($parcelle);
        $data = array_map(fn($rec) => $rec->toArray(), $recommendations);

        return new JsonResponse([
            'success' => true,
            'parcelle' => [
                'id' => $parcelle->getId(),
                'nom' => $parcelle->getNom(),
                'superficie' => $parcelle->getSuperficie(),
                'localisation' => $parcelle->getLocalisation(),
            ],
            'recommendations' => $data,
        ]);
    }

    /**
     * AJAX : Charger l'analyse détaillée d'un equipement pour une parcelle
     * Route : /equipement/ajax/match/{equipementId}/{parcelleId}
     */
    #[Route('/ajax/match/{equipementId}/{parcelleId}', name: 'app_equipement_ajax_match', methods: ['GET'])]
    public function ajaxGetMatch(
        int $equipementId,
        int $parcelleId,
        EquipementRepository $equipementRepository,
        ParcelleRepository $parcelleRepository,
        EquipementRecommendationService $recommendationService
    ): JsonResponse {
        $equipement = $equipementRepository->find($equipementId);
        $parcelle = $parcelleRepository->find($parcelleId);

        if (!$equipement || !$parcelle) {
            return new JsonResponse(['error' => 'Données non trouvées'], 404);
        }

        $matchResult = $recommendationService->evaluateEquipmentForParcelle($equipement, $parcelle);

        return new JsonResponse([
            'success' => true,
            'match' => $matchResult->toArray(),
        ]);
    }

    // --- CRUD Routes ---
    // (Moved to the bottom so they don't override static routes like /demo)

    #[Route('/{id}', name: 'app_equipement_show', methods: ['GET'])]
    public function show(Equipement $equipement): Response
    {
        return $this->render('equipement/show.html.twig', [
            'equipement' => $equipement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_equipement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipement $equipement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipement/edit.html.twig', [
            'equipement' => $equipement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_equipement_delete', methods: ['POST'])]
    public function delete(Request $request, Equipement $equipement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $equipement->getId(), $request->request->get('_token') ?? $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
    }
}
