<?php

namespace App\Service;

use App\Entity\Equipement;

class SmartMatchingService
{
    private const PRIX_LITRE_CARBURANT = 2.10;   // TND/litre
    private const SALAIRE_CHAUFFEUR_HEURE = 15.0; // TND/heure
    private const HEURES_JOURNEE = 8;

    /**
     * Calcule le score de compatibilité (0-100) entre une surface et un équipement,
     * et retourne un diagnostic complet pour la présentation au jury.
     */
    public function analyserCompatibilite(float $surfaceHectares, Equipement $equipement): array
    {
        $capacite     = $equipement->getCapaciteRendement() ?? 1.0; // Ha/heure
        $consommation = 10.0; // L/heure (valeur par défaut TCO)
        $prixAchat    = $equipement->getPrix() ?? 0.0;

        // ── Calculs de base ──────────────────────────────────────────────────
        $tempsHeures   = $surfaceHectares / $capacite;
        $joursNecessaires = ceil($tempsHeures / self::HEURES_JOURNEE);

        // ── Score de matching (logique métier) ───────────────────────────────
        $score  = 100;
        $raison = 'Parfait : l\'équipement est bien dimensionné pour cette surface.';

        if ($tempsHeures > 10) {
            // Sous-dimensionné : la tâche dépasse 1 journée + 2h
            $score  = max(0, 100 - (int)(($tempsHeures - 8) * 6));
            $raison = "⚠️ Sous-dimensionné : ce travail nécessitera {$joursNecessaires} jours. Préférez un équipement plus puissant.";
        } elseif ($tempsHeures < 0.5 && $capacite > 3) {
            // Sur-dimensionné : gaspillage de carburant
            $score  = 60;
            $raison = '⚠️ Sur-dimensionné : trop puissant pour cette surface. Coût carburant élevé inutilement.';
        }

        $score = max(0, min(100, $score));

        // ── TCO : Coût Total de Possession ────────────────────────────────────
        $coutCarburant    = $tempsHeures * $consommation * self::PRIX_LITRE_CARBURANT;
        $coutMainDoeuvre  = $tempsHeures * self::SALAIRE_CHAUFFEUR_HEURE;
        $coutTotal        = $coutCarburant + $coutMainDoeuvre;
        $coutParHectare   = $surfaceHectares > 0 ? $coutTotal / $surfaceHectares : 0;

        // ── Badge visuel ──────────────────────────────────────────────────────
        $badge  = 'danger';
        $label  = 'Non recommandé';
        if ($score >= 80) { $badge = 'success'; $label = 'Recommandé ✅'; }
        elseif ($score >= 55) { $badge = 'warning'; $label = 'Acceptable ⚠️'; }

        return [
            'score'            => $score,
            'badge'            => $badge,
            'label'            => $label,
            'raison'           => $raison,
            'temps_heures'     => round($tempsHeures, 1),
            'jours'            => $joursNecessaires,
            'cout_carburant'   => round($coutCarburant, 2),
            'cout_main_oeuvre' => round($coutMainDoeuvre, 2),
            'cout_total'       => round($coutTotal, 2),
            'cout_par_hectare' => round($coutParHectare, 2),
        ];
    }

    /**
     * Classe tous les équipements disponibles et retourne les résultats triés
     * par score décroissant → le meilleur choix en premier.
     */
    public function classerEquipements(float $surface, array $equipements): array
    {
        $resultats = [];
        foreach ($equipements as $eq) {
            $resultats[] = [
                'equipement' => $eq,
                'analyse'    => $this->analyserCompatibilite($surface, $eq),
            ];
        }

        usort($resultats, fn($a, $b) => $b['analyse']['score'] <=> $a['analyse']['score']);

        return $resultats;
    }
}
