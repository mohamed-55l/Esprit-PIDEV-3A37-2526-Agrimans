<?php

namespace App\Tests\Service;

use App\Entity\Culture;
use App\Service\CultureManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le service CultureManager.
 *
 * Règles métier testées :
 *  1. Le nom de la culture est obligatoire.
 *  2. La date de récolte prévue doit être postérieure à la date de plantation.
 */
class CultureManagerTest extends TestCase
{
    // -------------------------------------------------------
    // Test 1 : culture valide (nom + dates cohérentes)
    // -------------------------------------------------------
    public function testCultureValide(): void
    {
        $culture = new Culture();
        $culture->setNom('Blé dur');
        $culture->setDate_plantation(new \DateTime('2025-01-01'));
        $culture->setDate_recolte_prevue(new \DateTime('2025-06-01'));

        $manager = new CultureManager();

        $this->assertTrue($manager->validate($culture));
    }

    // -------------------------------------------------------
    // Test 2 : culture valide sans dates (dates optionnelles)
    // -------------------------------------------------------
    public function testCultureValideSansDates(): void
    {
        $culture = new Culture();
        $culture->setNom('Tomates');

        $manager = new CultureManager();

        $this->assertTrue($manager->validate($culture));
    }

    // -------------------------------------------------------
    // Test 3 : nom vide → exception attendue
    // -------------------------------------------------------
    public function testCultureNomVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom de la culture est obligatoire.');

        $culture = new Culture();
        $culture->setNom('');

        $manager = new CultureManager();
        $manager->validate($culture);
    }

    // -------------------------------------------------------
    // Test 4 : date récolte = date plantation → exception
    // -------------------------------------------------------
    public function testCultureDateRecolteEgalePlantation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de récolte prévue doit être postérieure à la date de plantation.');

        $culture = new Culture();
        $culture->setNom('Maïs');
        $culture->setDate_plantation(new \DateTime('2025-03-01'));
        $culture->setDate_recolte_prevue(new \DateTime('2025-03-01'));

        $manager = new CultureManager();
        $manager->validate($culture);
    }

    // -------------------------------------------------------
    // Test 5 : date récolte AVANT plantation → exception
    // -------------------------------------------------------
    public function testCultureDateRecolteAvantPlantation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de récolte prévue doit être postérieure à la date de plantation.');

        $culture = new Culture();
        $culture->setNom('Orge');
        $culture->setDate_plantation(new \DateTime('2025-05-01'));
        $culture->setDate_recolte_prevue(new \DateTime('2025-01-01'));

        $manager = new CultureManager();
        $manager->validate($culture);
    }
}
