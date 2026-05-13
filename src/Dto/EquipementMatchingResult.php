<?php

namespace App\Dto;

use App\Entity\Equipement;

/**
 * DTO pour représenter le résultat du matching intelligente d'équipement
 * Contient le score, les recommandations et les alertes
 */
class EquipementMatchingResult
{
    /**
     * Score de matching (0-100)
     * 100 = parfait match
     * 60-80 = acceptable
     * < 60 = mauvais match (alerte)
     */
    private float $matchScore;

    /**
     * Temps estimé en heures pour réaliser la tâche
     */
    private float $estimatedTimeHours;

    /**
     * L'équipement évalué
     */
    private Equipement $equipement;

    /**
     * La surface du terrain (hectares)
     */
    private float $terrainArea;

    /**
     * Statut de recommandation
     * 'RECOMMENDED', 'ACCEPTABLE', 'NOT_RECOMMENDED'
     */
    private string $recommendation;

    /**
     * Message d'alerte si applicable
     */
    private ?string $alertMessage = null;

    /**
     * Couleur du badge pour affichage
     * 'green' = recommended, 'yellow' = acceptable, 'red' = not recommended
     */
    private string $badgeColor;

    /**
     * Raison du matching (pour explication au jury)
     */
    private string $reason;

    public function __construct(
        Equipement $equipement,
        float $terrainArea,
        float $matchScore,
        float $estimatedTimeHours,
        string $recommendation,
        string $badgeColor,
        string $reason,
        ?string $alertMessage = null
    ) {
        $this->equipement = $equipement;
        $this->terrainArea = $terrainArea;
        $this->matchScore = $matchScore;
        $this->estimatedTimeHours = $estimatedTimeHours;
        $this->recommendation = $recommendation;
        $this->badgeColor = $badgeColor;
        $this->reason = $reason;
        $this->alertMessage = $alertMessage;
    }

    // Getters
    public function getMatchScore(): float
    {
        return $this->matchScore;
    }

    public function getEstimatedTimeHours(): float
    {
        return $this->estimatedTimeHours;
    }

    public function getEquipement(): Equipement
    {
        return $this->equipement;
    }

    public function getTerrainArea(): float
    {
        return $this->terrainArea;
    }

    public function getRecommendation(): string
    {
        return $this->recommendation;
    }

    public function getAlertMessage(): ?string
    {
        return $this->alertMessage;
    }

    public function getBadgeColor(): string
    {
        return $this->badgeColor;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Retourne un tableau pour sérialisation JSON
     */
    public function toArray(): array
    {
        return [
            'equipement_id' => $this->equipement->getId(),
            'equipement_nom' => $this->equipement->getNom(),
            'equipement_type' => $this->equipement->getType(),
            'match_score' => round($this->matchScore, 2),
            'estimated_time_hours' => round($this->estimatedTimeHours, 2),
            'recommendation' => $this->recommendation,
            'badge_color' => $this->badgeColor,
            'reason' => $this->reason,
            'alert_message' => $this->alertMessage,
            'terrain_area' => $this->terrainArea,
            'equipement_capacite' => $this->equipement->getCapaciteRendement(),
        ];
    }
}
