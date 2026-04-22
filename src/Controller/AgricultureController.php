<?php

namespace App\Controller;

use App\Repository\ParcelleRepository;
use App\Repository\CultureRepository;
use App\Service\AgricultureService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agriculture')]
final class AgricultureController extends AbstractController
{
    public function __construct(private AgricultureService $agriService) {}

    // ──────────────────────────────────────────────────────────────
    // 1. CALCUL RENDEMENT
    // ──────────────────────────────────────────────────────────────
    #[Route('/rendement', name: 'app_agriculture_rendement', methods: ['GET', 'POST'])]
    public function rendement(Request $request, ParcelleRepository $parcelleRepository): Response
    {
        $parcelles = $parcelleRepository->findAll();
        $resultat  = null;
        $erreur    = null;

        if ($request->isMethod('POST')) {
            $superficie  = (float) $request->request->get('superficie', 0);
            $typeSol     = trim((string) $request->request->get('type_sol', ''));
            $typeCulture = trim((string) $request->request->get('type_culture', ''));

            // Si l'utilisateur a choisi une parcelle existante
            $parcelleId = $request->request->get('parcelle_id');
            if ($parcelleId) {
                $parcelle = $parcelleRepository->find($parcelleId);
                if ($parcelle) {
                    $superficie = $parcelle->getSuperficie() ?? $superficie;
                    $typeSol    = $parcelle->getType_sol() ?? $typeSol;
                }
            }

            if ($superficie <= 0) {
                $erreur = 'La superficie doit être supérieure à 0.';
            } elseif (empty($typeSol)) {
                $erreur = 'Veuillez sélectionner ou saisir un type de sol.';
            } elseif (empty($typeCulture)) {
                $erreur = 'Veuillez saisir un type de culture.';
            } else {
                $resultat = $this->agriService->calculerRendement($superficie, $typeSol, $typeCulture);
            }
        }

        return $this->render('agriculture/rendement.html.twig', [
            'parcelles'      => $parcelles,
            'resultat'       => $resultat,
            'erreur'         => $erreur,
            'types_sol'      => ['Argileux', 'Limoneux', 'Sableux', 'Calcaire', 'Humifère', 'Tourbeux', 'Autre'],
            'types_culture'  => ['Céréales', 'Blé', 'Orge', 'Maïs', 'Légumes', 'Fruits', 'Oléagineux', 'Légumineuses', 'Fourrage', 'Vigne', 'Pomme de terre', 'Autre'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 2. STATISTIQUES
    // ──────────────────────────────────────────────────────────────
    #[Route('/statistiques', name: 'app_agriculture_statistiques', methods: ['GET'])]
    public function statistiques(ParcelleRepository $parcelleRepository, CultureRepository $cultureRepository): Response
    {
        $stats = $this->agriService->getStatistiques($parcelleRepository, $cultureRepository);

        return $this->render('agriculture/statistiques.html.twig', [
            'stats' => $stats,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 3. COMPATIBILITÉ SOL-CULTURE
    // ──────────────────────────────────────────────────────────────
    #[Route('/compatibilite', name: 'app_agriculture_compatibilite', methods: ['GET'])]
    public function compatibilite(): Response
    {
        $typesDisponibles  = $this->agriService->getTypesSolDisponibles();
        $matrice           = $this->agriService->getCompatibiliteSol();

        return $this->render('agriculture/compatibilite.html.twig', [
            'types_sol' => $typesDisponibles,
            'matrice'   => $matrice,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 4. CALENDRIER CULTURAL
    // ──────────────────────────────────────────────────────────────
    #[Route('/calendrier', name: 'app_agriculture_calendrier', methods: ['GET'])]
    public function calendrier(): Response
    {
        return $this->render('agriculture/calendrier.html.twig');
    }

    #[Route('/calendrier/events', name: 'app_agriculture_calendrier_events', methods: ['GET'])]
    public function calendrierEvents(CultureRepository $cultureRepository): JsonResponse
    {
        $events = $this->agriService->getEvenementCalendrier($cultureRepository);
        return new JsonResponse($events);
    }
}
