<?php

namespace App\Service;

use App\Entity\Parcelle;

/**
 * Service métier pour la gestion des parcelles.
 *
 * Règles métier :
 *  1. Le nom de la parcelle est obligatoire.
 *  2. La superficie doit être strictement supérieure à zéro.
 */
class ParcelleManager
{
    /**
     * Valide les règles métier d'une parcelle.
     *
     * @throws \InvalidArgumentException si une règle est violée
     */
    public function validate(Parcelle $parcelle): bool
    {
        // Règle 1 : Le nom est obligatoire
        if (empty($parcelle->getNom())) {
            throw new \InvalidArgumentException('Le nom de la parcelle est obligatoire.');
        }

        // Règle 2 : La superficie doit être supérieure à zéro
        if ($parcelle->getSuperficie() === null || $parcelle->getSuperficie() <= 0) {
            throw new \InvalidArgumentException('La superficie doit être supérieure à zéro.');
        }

        return true;
    }
}
