<?php

namespace App\Service;

use App\Repository\ParcelleRepository;
use App\Repository\CultureRepository;

/**
 * Service métier agricole : rendement, compatibilité sol-culture, statistiques.
 */
class AgricultureService
{
    // ──────────────────────────────────────────────────────────────
    // RÉFÉRENTIELS AGRONOMIQUES
    // ──────────────────────────────────────────────────────────────

    /** Coefficients de rendement selon le type de sol (base 1.0) */
    private const COEFFICIENTS_SOL = [
        'argileux'   => 1.2,
        'limoneux'   => 1.0,
        'sableux'    => 0.7,
        'calcaire'   => 0.8,
        'humifère'   => 1.3,
        'humifere'   => 1.3,
        'tourbeux'   => 0.6,
        'autre'      => 0.9,
    ];

    /** Rendement de base en t/ha selon le type de culture */
    private const RENDEMENT_BASE = [
        'céréales'      => 5.0,
        'cereales'      => 5.0,
        'blé'           => 5.5,
        'ble'           => 5.5,
        'orge'          => 4.5,
        'maïs'          => 9.0,
        'mais'          => 9.0,
        'légumes'       => 20.0,
        'legumes'       => 20.0,
        'fruits'        => 15.0,
        'oléagineux'    => 3.5,
        'oleagineux'    => 3.5,
        'légumineuses'  => 3.0,
        'legumineuses'  => 3.0,
        'fourrage'      => 8.0,
        'vigne'         => 7.0,
        'pomme de terre'=> 35.0,
        'autre'         => 5.0,
    ];

    /** Matrice de compatibilité sol → [compatible[], neutre[], deconseille[]] */
    private const COMPATIBILITE = [
        'argileux' => [
            'compatible'   => ['Blé', 'Maïs', 'Colza', 'Tournesol', 'Betterave'],
            'neutre'       => ['Orge', 'Soja', 'Légumes', 'Pomme de terre'],
            'deconseille'  => ['Carotte', 'Vigne', 'Lavande', 'Arachide'],
        ],
        'limoneux' => [
            'compatible'   => ['Blé', 'Orge', 'Maïs', 'Légumes', 'Tournesol', 'Betterave'],
            'neutre'       => ['Vigne', 'Colza', 'Soja'],
            'deconseille'  => ['Lavande', 'Arachide'],
        ],
        'sableux' => [
            'compatible'   => ['Carotte', 'Arachide', 'Vigne', 'Asperge', 'Lavande'],
            'neutre'       => ['Maïs', 'Pomme de terre', 'Soja'],
            'deconseille'  => ['Blé', 'Betterave', 'Colza', 'Légumes feuilles'],
        ],
        'calcaire' => [
            'compatible'   => ['Blé', 'Orge', 'Vigne', 'Lavande', 'Colza'],
            'neutre'       => ['Tournesol', 'Légumes', 'Soja'],
            'deconseille'  => ['Maïs', 'Pomme de terre', 'Betterave', 'Arachide'],
        ],
        'humifère' => [
            'compatible'   => ['Légumes', 'Fruits', 'Maraîchage', 'Pomme de terre', 'Soja'],
            'neutre'       => ['Maïs', 'Blé', 'Orge'],
            'deconseille'  => ['Lavande', 'Vigne'],
        ],
        'humifere' => [
            'compatible'   => ['Légumes', 'Fruits', 'Maraîchage', 'Pomme de terre', 'Soja'],
            'neutre'       => ['Maïs', 'Blé', 'Orge'],
            'deconseille'  => ['Lavande', 'Vigne'],
        ],
        'tourbeux' => [
            'compatible'   => ['Canneberge', 'Myrtille', 'Joncs'],
            'neutre'       => ['Légumes', 'Pomme de terre'],
            'deconseille'  => ['Blé', 'Maïs', 'Orge', 'Colza', 'Vigne'],
        ],
    ];

    // ──────────────────────────────────────────────────────────────
    // CALCUL DE RENDEMENT
    // ──────────────────────────────────────────────────────────────

    /**
     * Calcule le rendement estimé et retourne un tableau détaillé.
     */
    public function calculerRendement(float $superficie, string $typeSol, string $typeCulture): array
    {
        $solKey     = mb_strtolower(trim($typeSol));
        $cultureKey = mb_strtolower(trim($typeCulture));

        $coeffSol      = self::COEFFICIENTS_SOL[$solKey]     ?? self::COEFFICIENTS_SOL['autre'];
        $rendementBase = self::RENDEMENT_BASE[$cultureKey]   ?? self::RENDEMENT_BASE['autre'];

        $rendementHa    = round($rendementBase * $coeffSol, 2);
        $rendementTotal = round($rendementHa * $superficie, 2);

        // Indice de qualité (1-5 étoiles)
        $qualite = match (true) {
            $coeffSol >= 1.2 => 5,
            $coeffSol >= 1.0 => 4,
            $coeffSol >= 0.85 => 3,
            $coeffSol >= 0.7 => 2,
            default => 1,
        };

        // Conseil agronomique
        $conseil = $this->genererConseil($solKey, $cultureKey, $coeffSol);

        return [
            'superficie'     => $superficie,
            'type_sol'       => $typeSol,
            'type_culture'   => $typeCulture,
            'coeff_sol'      => $coeffSol,
            'rendement_base' => $rendementBase,
            'rendement_ha'   => $rendementHa,
            'rendement_total'=> $rendementTotal,
            'qualite'        => $qualite,
            'conseil'        => $conseil,
        ];
    }

    private function genererConseil(string $solKey, string $cultureKey, float $coeff): string
    {
        if ($coeff >= 1.2) {
            return "Excellent ! Ce type de sol est idéal pour cette culture. Vous pouvez espérer un rendement supérieur à la moyenne nationale.";
        } elseif ($coeff >= 1.0) {
            return "Bon potentiel. Le sol est bien adapté à cette culture. Assurez-vous d'un apport hydrique suffisant.";
        } elseif ($coeff >= 0.8) {
            return "Rendement correct. Le sol présente quelques limites. Envisagez un amendement organique pour améliorer les résultats.";
        } else {
            return "Rendement réduit. Ce type de sol n'est pas optimal pour cette culture. Pensez à diversifier ou à amender significativement votre terrain.";
        }
    }

    // ──────────────────────────────────────────────────────────────
    // COMPATIBILITÉ SOL-CULTURE
    // ──────────────────────────────────────────────────────────────

    public function getCompatibiliteSol(?string $typeSol = null): array
    {
        if ($typeSol !== null) {
            $key = mb_strtolower(trim($typeSol));
            return self::COMPATIBILITE[$key] ?? [
                'compatible'  => [],
                'neutre'      => [],
                'deconseille' => [],
            ];
        }
        return self::COMPATIBILITE;
    }

    public function getTypesSolDisponibles(): array
    {
        return array_keys(self::COMPATIBILITE);
    }

    // ──────────────────────────────────────────────────────────────
    // STATISTIQUES
    // ──────────────────────────────────────────────────────────────

    public function getStatistiques(ParcelleRepository $pr, CultureRepository $cr): array
    {
        $parcelles = $pr->findAll();
        $cultures  = $cr->findAll();
        $now       = new \DateTime();

        // KPIs
        $nbParcelles    = count($parcelles);
        $superficieTotal = array_sum(array_map(fn($p) => $p->getSuperficie() ?? 0, $parcelles));
        $nbCultures      = count($cultures);

        $recoltesProches = 0;
        foreach ($cultures as $c) {
            $dr = $c->getDate_recolte_prevue();
            if ($dr && $dr >= $now && $dr <= (clone $now)->modify('+30 days')) {
                $recoltesProches++;
            }
        }

        // Parcelles par type de sol
        $parTypeSol = [];
        foreach ($parcelles as $p) {
            $sol = $p->getType_sol() ?: 'Non défini';
            $parTypeSol[$sol] = ($parTypeSol[$sol] ?? 0) + 1;
        }

        // Cultures par état
        $parEtat = [];
        foreach ($cultures as $c) {
            $etat = $c->getEtat_culture() ?: 'Non défini';
            $parEtat[$etat] = ($parEtat[$etat] ?? 0) + 1;
        }

        // Cultures plantées par mois (12 derniers mois)
        $parMois = array_fill(1, 12, 0);
        foreach ($cultures as $c) {
            $dp = $c->getDate_plantation();
            if ($dp) {
                $m = (int) $dp->format('n');
                $parMois[$m] = ($parMois[$m] ?? 0) + 1;
            }
        }

        // Superficie par type de sol
        $superficieParSol = [];
        foreach ($parcelles as $p) {
            $sol = $p->getType_sol() ?: 'Non défini';
            $superficieParSol[$sol] = ($superficieParSol[$sol] ?? 0) + ($p->getSuperficie() ?? 0);
        }

        return [
            'kpi' => [
                'nb_parcelles'      => $nbParcelles,
                'superficie_totale' => round($superficieTotal, 2),
                'nb_cultures'       => $nbCultures,
                'recoltes_proches'  => $recoltesProches,
            ],
            'par_type_sol'        => $parTypeSol,
            'par_etat'            => $parEtat,
            'par_mois'            => $parMois,
            'superficie_par_sol'  => $superficieParSol,
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // CALENDRIER
    // ──────────────────────────────────────────────────────────────

    /**
     * Retourne les événements au format FullCalendar.
     */
    public function getEvenementCalendrier(CultureRepository $cr): array
    {
        $cultures = $cr->findAll();
        $events   = [];

        foreach ($cultures as $culture) {
            $nom = $culture->getNom() ?: 'Culture #' . $culture->getId();

            // Événement plantation
            if ($dp = $culture->getDate_plantation()) {
                $events[] = [
                    'id'              => 'plant_' . $culture->getId(),
                    'title'           => '🌱 Plantation : ' . $nom,
                    'start'           => $dp->format('Y-m-d'),
                    'backgroundColor' => '#198B61',
                    'borderColor'     => '#145c41',
                    'textColor'       => '#ffffff',
                    'extendedProps'   => [
                        'type'        => 'plantation',
                        'culture'     => $nom,
                        'etat'        => $culture->getEtat_culture(),
                        'parcelle'    => $culture->getParcelle()?->getNom(),
                    ],
                ];
            }

            // Événement récolte prévue
            if ($dr = $culture->getDate_recolte_prevue()) {
                $events[] = [
                    'id'              => 'recolte_' . $culture->getId(),
                    'title'           => '🌾 Récolte : ' . $nom,
                    'start'           => $dr->format('Y-m-d'),
                    'backgroundColor' => '#e67e22',
                    'borderColor'     => '#ca6f1e',
                    'textColor'       => '#ffffff',
                    'extendedProps'   => [
                        'type'        => 'recolte',
                        'culture'     => $nom,
                        'etat'        => $culture->getEtat_culture(),
                        'parcelle'    => $culture->getParcelle()?->getNom(),
                    ],
                ];
            }
        }

        return $events;
    }
}
