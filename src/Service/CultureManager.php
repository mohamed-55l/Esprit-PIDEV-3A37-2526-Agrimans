<?php

namespace App\Service;

use App\Entity\Culture;

/**
 * Service métier pour la gestion des cultures.
 *
 * Règles métier :
 *  1. Le nom de la culture est obligatoire.
 *  2. La date de récolte prévue doit être postérieure à la date de plantation.
 */
class CultureManager
{
    /**
     * Valide les règles métier d'une culture.
     *
     * @throws \InvalidArgumentException si une règle est violée
     */
    public function validate(Culture $culture): bool
    {
        // Règle 1 : Le nom est obligatoire
        if (empty($culture->getNom())) {
            throw new \InvalidArgumentException('Le nom de la culture est obligatoire.');
        }

        // Règle 2 : La date de récolte doit être postérieure à la date de plantation
        if (
            $culture->getDate_plantation() !== null
            && $culture->getDate_recolte_prevue() !== null
            && $culture->getDate_recolte_prevue() <= $culture->getDate_plantation()
        ) {
            throw new \InvalidArgumentException(
                'La date de récolte prévue doit être postérieure à la date de plantation.'
            );
        }

        return true;
    }
}
