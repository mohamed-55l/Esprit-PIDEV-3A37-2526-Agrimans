<?php

namespace App\Service;

use App\Entity\Equipement;

class EquipementManager
{
    /**
     * Valide un équipement selon les règles métier
     * 
     * Règles métier:
     * 1. Le nom est obligatoire
     * 2. Le prix doit être supérieur à zéro
     * 3. Le type est obligatoire
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(Equipement $equipement): bool
    {
        // Règle 1: Le nom est obligatoire
        if (empty($equipement->getNom())) {
            throw new \InvalidArgumentException('Le nom de l\'équipement est obligatoire');
        }

        // Règle 2: Le prix doit être supérieur à zéro
        $prix = $equipement->getPrix();
        if ($prix === null || $prix <= 0) {
            throw new \InvalidArgumentException('Le prix doit être supérieur à zéro');
        }

        // Règle 3: Le type est obligatoire
        if (empty($equipement->getType())) {
            throw new \InvalidArgumentException('Le type de l\'équipement est obligatoire');
        }

        return true;
    }

    /**
     * Calcule une remise si applicable
     * Règle métier: Une remise de 10% s'applique si le prix dépasse 500
     */
    public function calculateDiscountedPrice(Equipement $equipement): float
    {
        $prix = $equipement->getPrix();

        if ($prix >= 500) {
            return $prix * 0.90; // 10% de remise
        }

        return $prix;
    }

    /**
     * Vérifie si l'équipement est disponible
     */
    public function isAvailable(Equipement $equipement): bool
    {
        return $equipement->getDisponibilite() === 'disponible';
    }
}
