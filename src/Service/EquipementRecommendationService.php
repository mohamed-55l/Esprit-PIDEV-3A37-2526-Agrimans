<?php

namespace App\Service;

use App\Dto\EquipementMatchingResult;
use App\Entity\Equipement;
use App\Entity\Parcelle;
use App\Repository\EquipementRepository;

/**
 * Service métier avancé : Matching Intelligent & Recommandation par Rendement
 * 
 * Analyse la surface du terrain et la capacité de l'équipement pour recommander
 * la machine la plus rentable. Cela assiste l'agriculteur dans sa prise de décision
 * économique en évitant les mismatches (machine trop grosse ou trop petite).
 */
class EquipementRecommendationService
{
    private EquipementRepository $equipementRepository;

    /**
     * Seuils de matching
     * Score >= 85% = Recommandé (vert)
     * Score 65-84% = Acceptable (jaune)
     * Score < 65% = Non recommandé (rouge)
     */
    private const SCORE_RECOMMENDED = 85;
    private const SCORE_ACCEPTABLE = 65;

    /**
     * Facteur de tolerance sur la capacité
     * Si un équipement peut faire le travail en 1.5x le temps optimal, c'est acceptable
     */
    private const MAX_TIME_TOLERANCE_FACTOR = 1.5;

    /**
     * Facteur minimum pour considérer une machine trop grande
     * Si elle finit en moins de 0.3x du temps, c'est surpuissant (mauvaise rentabilité)
     */
    private const MIN_TIME_EFFICIENCY = 0.3;

    public function __construct(EquipementRepository $equipementRepository)
    {
        $this->equipementRepository = $equipementRepository;
    }

    /**
     * Évalue le matching d'UN équipement pour une parcelle donnée
     * 
     * @param Equipement $equipement L'équipement à évaluer
     * @param Parcelle $parcelle La parcelle pour laquelle on cherche un équipement
     * @return EquipementMatchingResult Le résultat du matching
     */
    public function evaluateEquipmentForParcelle(Equipement $equipement, Parcelle $parcelle): EquipementMatchingResult
    {
        $terrainArea = $parcelle->getSuperficie();
        $capaciteRendement = $equipement->getCapaciteRendement();

        // Validation de base
        if ($capaciteRendement === null || $capaciteRendement <= 0) {
            return $this->createUnratedResult($equipement, $terrainArea, "Capacité de rendement non définie");
        }

        if ($terrainArea <= 0) {
            return $this->createUnratedResult($equipement, $terrainArea, "Surface du terrain invalide");
        }

        // Calcul du temps estimé
        $estimatedTimeHours = $terrainArea / $capaciteRendement;

        // Calcul du score de matching
        $matchScore = $this->calculateMatchScore($terrainArea, $capaciteRendement, $estimatedTimeHours);

        // Détermination de la recommandation et des alertes
        [$recommendation, $badgeColor, $reason, $alertMessage] = $this->determineRecommendation(
            $matchScore,
            $equipement,
            $terrainArea,
            $estimatedTimeHours,
            $capaciteRendement
        );

        return new EquipementMatchingResult(
            $equipement,
            $terrainArea,
            $matchScore,
            $estimatedTimeHours,
            $recommendation,
            $badgeColor,
            $reason,
            $alertMessage
        );
    }

    /**
     * Recommande les MEILLEURS équipements pour une parcelle (top 3)
     * 
     * @param Parcelle $parcelle
     * @return EquipementMatchingResult[] Liste triée des meilleurs matches
     */
    public function recommendEquipmentsForParcelle(Parcelle $parcelle): array
    {
        $allEquipements = $this->equipementRepository->findAll();
        $results = [];

        foreach ($allEquipements as $equipement) {
            $result = $this->evaluateEquipmentForParcelle($equipement, $parcelle);
            $results[] = $result;
        }

        // Tri par score décroissant
        usort($results, function (EquipementMatchingResult $a, EquipementMatchingResult $b) {
            return $b->getMatchScore() <=> $a->getMatchScore();
        });

        // Retour des 3 meilleurs
        return array_slice($results, 0, 3);
    }

    /**
     * Calcule le score de matching (0-100)
     * 
     * Logique :
     * - Score optimal = 100 quand le temps estimé est entre 2 et 8 heures
     * - Moins de 2h = machine surpuissante (mauvaise rentabilité)
     * - Plus de 8h = machine sous-dimensionnée (trop long)
     */
    private function calculateMatchScore(float $terrainArea, float $capaciteRendement, float $estimatedTimeHours): float
    {
        // Plage optimale : 2 à 8 heures
        $optimalMinHours = 2.0;
        $optimalMaxHours = 8.0;

        // Si dans la plage optimale
        if ($estimatedTimeHours >= $optimalMinHours && $estimatedTimeHours <= $optimalMaxHours) {
            return 100.0;
        }

        // Si trop court (machine surpuissante)
        if ($estimatedTimeHours < $optimalMinHours) {
            // Pénalité progressive : de 100 à 50
            $ratio = $estimatedTimeHours / $optimalMinHours;
            return 50 + ($ratio * 50); // Entre 50 et 100
        }

        // Si trop long (machine sous-dimensionnée)
        if ($estimatedTimeHours > $optimalMaxHours) {
            // Pénalité progressive : de 100 à 20
            $ratio = $optimalMaxHours / $estimatedTimeHours;
            return 20 + ($ratio * 80); // Entre 20 et 100
        }

        return 0;
    }

    /**
     * Détermine la recommandation, la couleur du badge et le message d'alerte
     */
    private function determineRecommendation(
        float $matchScore,
        Equipement $equipement,
        float $terrainArea,
        float $estimatedTimeHours,
        float $capaciteRendement
    ): array {
        $recommendation = 'NOT_RECOMMENDED';
        $badgeColor = 'red';
        $reason = '';
        $alertMessage = null;

        if ($matchScore >= self::SCORE_RECOMMENDED) {
            $recommendation = 'RECOMMENDED';
            $badgeColor = 'green';
            $reason = sprintf(
                '✓ Excellent match ! %s réalisera le travail en %.1f heures avec un rendement optimal.',
                $equipement->getNom(),
                $estimatedTimeHours
            );
        } elseif ($matchScore >= self::SCORE_ACCEPTABLE) {
            $recommendation = 'ACCEPTABLE';
            $badgeColor = 'yellow';
            $reason = sprintf(
                '⚠ Match acceptable. %s réalisera le travail en %.1f heures.',
                $equipement->getNom(),
                $estimatedTimeHours
            );

            if ($estimatedTimeHours < 2) {
                $alertMessage = "⚠️ Machine surpuissante : consommation de carburant excessive.";
            } else {
                $alertMessage = "⚠️ Temps un peu long pour cette tâche.";
            }
        } else {
            $recommendation = 'NOT_RECOMMENDED';
            $badgeColor = 'red';

            if ($estimatedTimeHours < 1.5) {
                $reason = sprintf(
                    '✗ Pas recommandé. %s est trop puissant pour cette surface (%.1f Ha). Travail en %.1f h.',
                    $equipement->getNom(),
                    $terrainArea,
                    $estimatedTimeHours
                );
                $alertMessage = "🔴 Machine trop grande : surcoûts énergétiques, faible rentabilité.";
            } else {
                $reason = sprintf(
                    '✗ Pas recommandé. %s est sous-dimensionné pour cette surface (%.1f Ha). Travail en %.1f h.',
                    $equipement->getNom(),
                    $terrainArea,
                    $estimatedTimeHours
                );
                $alertMessage = "🔴 Machine insuffisante : temps trop long, faible productivité.";
            }
        }

        return [$recommendation, $badgeColor, $reason, $alertMessage];
    }

    /**
     * Crée un résultat non noté (quand les données manquent)
     */
    private function createUnratedResult(Equipement $equipement, float $terrainArea, string $reason): EquipementMatchingResult
    {
        return new EquipementMatchingResult(
            $equipement,
            $terrainArea,
            0,
            0,
            'UNRATED',
            'gray',
            $reason,
            "⚠️ Données manquantes pour évaluer cet équipement."
        );
    }

    /**
     * Calcule les statistiques d'efficacité pour un équipement donné
     * Utile pour afficher des dashboards
     */
    public function getEquipementEfficiencyStats(Equipement $equipement): array
    {
        $parcelles = []; // À intégrer avec un repository de parcelles
        $totalMatches = 0;
        $recommendedCount = 0;

        // Cette méthode serait à étendre selon tes besoins
        return [
            'equipement_id' => $equipement->getId(),
            'equipement_nom' => $equipement->getNom(),
            'capacite' => $equipement->getCapaciteRendement(),
            'total_evaluations' => $totalMatches,
            'recommended_count' => $recommendedCount,
            'recommendation_rate' => $totalMatches > 0 ? ($recommendedCount / $totalMatches) * 100 : 0,
        ];
    }
}
