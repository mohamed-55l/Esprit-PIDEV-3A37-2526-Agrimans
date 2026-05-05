<?php

namespace App\Tests\Service;

use App\Entity\Equipement;
use App\Entity\Review;
use App\Service\ReviewManager;
use PHPUnit\Framework\TestCase;

class ReviewManagerTest extends TestCase
{
    private ReviewManager $manager;

    protected function setUp(): void
    {
        $this->manager = new ReviewManager();
    }

    /**
     * Test: Valider une revue valide
     * Scénario: Tous les champs obligatoires sont remplis correctement
     * Résultat attendu: La validation retourne true
     */
    public function testValidReview(): void
    {
        $equipement = new Equipement();
        $equipement->setNom('Tracteur');

        $review = new Review();
        $review->setNote(4);
        $review->setCommentaire('Excellent équipement, très satisfait!');
        $review->setEquipement($equipement);

        $this->assertTrue($this->manager->validate($review));
    }

    /**
     * Test: Rejeter une revue avec note invalide (< 1)
     * Scénario: La note est inférieure à 1
     * Résultat attendu: Une exception est levée
     */
    public function testReviewWithNoteToLow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit être entre 1 et 5');

        $equipement = new Equipement();
        $review = new Review();
        $review->setNote(0); // Note invalide
        $review->setCommentaire('Commentaire');
        $review->setEquipement($equipement);

        $this->manager->validate($review);
    }

    /**
     * Test: Rejeter une revue avec note invalide (> 5)
     * Scénario: La note est supérieure à 5
     * Résultat attendu: Une exception est levée
     */
    public function testReviewWithNoteTooHigh(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit être entre 1 et 5');

        $equipement = new Equipement();
        $review = new Review();
        $review->setNote(6); // Note invalide
        $review->setCommentaire('Commentaire');
        $review->setEquipement($equipement);

        $this->manager->validate($review);
    }

    /**
     * Test: Rejeter une revue avec note null
     * Scénario: La note n'est pas définie
     * Résultat attendu: Une exception est levée
     */
    public function testReviewWithoutNote(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit être entre 1 et 5');

        $equipement = new Equipement();
        $review = new Review();
        // Pas d'appel à setNote
        $review->setCommentaire('Commentaire');
        $review->setEquipement($equipement);

        $this->manager->validate($review);
    }

    /**
     * Test: Rejeter une revue avec commentaire vide
     * Scénario: Le commentaire est vide
     * Résultat attendu: Une exception est levée
     */
    public function testReviewWithoutCommentaire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire est obligatoire');

        $equipement = new Equipement();
        $review = new Review();
        $review->setNote(4);
        $review->setCommentaire(''); // Commentaire vide
        $review->setEquipement($equipement);

        $this->manager->validate($review);
    }

    /**
     * Test: Rejeter une revue avec commentaire null
     * Scénario: Le commentaire n'est pas défini
     * Résultat attendu: Une exception est levée
     */
    public function testReviewWithNullCommentaire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire est obligatoire');

        $equipement = new Equipement();
        $review = new Review();
        $review->setNote(4);
        // Pas d'appel à setCommentaire
        $review->setEquipement($equipement);

        $this->manager->validate($review);
    }

    /**
     * Test: Rejeter une revue sans équipement associé
     * Scénario: L'équipement est null
     * Résultat attendu: Une exception est levée
     */
    public function testReviewWithoutEquipement(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Une revue doit être associée à un équipement');

        $review = new Review();
        $review->setNote(4);
        $review->setCommentaire('Commentaire');
        // Pas d'appel à setEquipement

        $this->manager->validate($review);
    }

    /**
     * Test: Calculer la moyenne des notes
     * Scénario: Plusieurs revues avec des notes différentes
     * Résultat attendu: La moyenne est correctement calculée
     */
    public function testCalculateAverageRating(): void
    {
        $reviews = [];

        for ($i = 0; $i < 3; $i++) {
            $review = new Review();
            $review->setNote(4);
            $reviews[] = $review;
        }

        // 3 notes de 4 = moyenne de 4
        $average = $this->manager->calculateAverageRating($reviews);
        $this->assertEquals(4.0, $average);
    }

    /**
     * Test: Calculer la moyenne avec des notes variées
     * Scénario: 5 revues avec notes 5, 4, 3, 2, 1
     * Résultat attendu: La moyenne est 3.0
     */
    public function testCalculateAverageRatingVaried(): void
    {
        $reviews = [];
        $notes = [5, 4, 3, 2, 1];

        foreach ($notes as $note) {
            $review = new Review();
            $review->setNote($note);
            $reviews[] = $review;
        }

        // Moyenne = (5+4+3+2+1)/5 = 15/5 = 3.0
        $average = $this->manager->calculateAverageRating($reviews);
        $this->assertEquals(3.0, $average);
    }

    /**
     * Test: Calculer la moyenne d'un tableau vide
     * Scénario: Aucune revue disponible
     * Résultat attendu: La moyenne est 0
     */
    public function testCalculateAverageRatingEmpty(): void
    {
        $average = $this->manager->calculateAverageRating([]);
        $this->assertEquals(0, $average);
    }

    /**
     * Test: Identifier une revue positive
     * Scénario: Une revue avec une note > 3
     * Résultat attendu: isPositiveReview retourne true
     */
    public function testIsPositiveReview(): void
    {
        $review = new Review();
        $review->setNote(4);

        $this->assertTrue($this->manager->isPositiveReview($review));
    }

    /**
     * Test: Identifier une revue négative
     * Scénario: Une revue avec une note <= 3
     * Résultat attendu: isPositiveReview retourne false
     */
    public function testIsNegativeReview(): void
    {
        $review = new Review();
        $review->setNote(3);

        $this->assertFalse($this->manager->isPositiveReview($review));
    }

    /**
     * Test: Identifier une revue note 5 comme positive
     * Scénario: Une revue avec une note excellente (5)
     * Résultat attendu: isPositiveReview retourne true
     */
    public function testIsExcellentReview(): void
    {
        $review = new Review();
        $review->setNote(5);

        $this->assertTrue($this->manager->isPositiveReview($review));
    }
}
