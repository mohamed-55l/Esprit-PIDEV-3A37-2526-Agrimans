<?php

namespace App\Service;

use App\Entity\Review;

class ReviewManager
{
    /**
     * Valide une revue selon les règles métier
     * 
     * Règles métier:
     * 1. La note doit être entre 1 et 5
     * 2. Le commentaire est obligatoire
     * 3. L'équipement doit être associé à la revue
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(Review $review): bool
    {
        // Règle 1: La note doit être entre 1 et 5
        $note = $review->getNote();
        if ($note === null || $note < 1 || $note > 5) {
            throw new \InvalidArgumentException('La note doit être entre 1 et 5');
        }

        // Règle 2: Le commentaire est obligatoire et non vide
        if (empty($review->getCommentaire())) {
            throw new \InvalidArgumentException('Le commentaire est obligatoire');
        }

        // Règle 3: L'équipement doit être associé à la revue
        if ($review->getEquipement() === null) {
            throw new \InvalidArgumentException('Une revue doit être associée à un équipement');
        }

        return true;
    }

    /**
     * Calcule la moyenne des notes pour un équipement
     */
    public function calculateAverageRating(array $reviews): float
    {
        if (empty($reviews)) {
            return 0;
        }

        $sum = 0;
        foreach ($reviews as $review) {
            $sum += $review->getNote();
        }

        return $sum / count($reviews);
    }

    /**
     * Vérifie si une note est positive (> 3)
     */
    public function isPositiveReview(Review $review): bool
    {
        return $review->getNote() > 3;
    }
}
