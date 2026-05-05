<?php

namespace App\Tests\Service;

use App\Entity\Parcelle;
use App\Service\ParcelleManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le service ParcelleManager.
 *
 * Règles métier testées :
 *  1. Le nom de la parcelle est obligatoire.
 *  2. La superficie doit être supérieure à zéro.
 */
class ParcelleManagerTest extends TestCase
{
    // -------------------------------------------------------
    // Test 1 : parcelle valide (nom + superficie > 0)
    // -------------------------------------------------------
    public function testParcellValide(): void
    {
        $parcelle = new Parcelle();
        $parcelle->setNom('Champ Nord');
        $parcelle->setSuperficie(12.5);

        $manager = new ParcelleManager();

        $this->assertTrue($manager->validate($parcelle));
    }

    // -------------------------------------------------------
    // Test 2 : nom vide → exception attendue
    // -------------------------------------------------------
    public function testParcelleNomVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom de la parcelle est obligatoire.');

        $parcelle = new Parcelle();
        $parcelle->setNom('');
        $parcelle->setSuperficie(5.0);

        $manager = new ParcelleManager();
        $manager->validate($parcelle);
    }

    // -------------------------------------------------------
    // Test 3 : superficie = 0 → exception attendue
    // -------------------------------------------------------
    public function testParcelleSuperficieNulle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La superficie doit être supérieure à zéro.');

        $parcelle = new Parcelle();
        $parcelle->setNom('Champ Sud');
        $parcelle->setSuperficie(0);

        $manager = new ParcelleManager();
        $manager->validate($parcelle);
    }

    // -------------------------------------------------------
    // Test 4 : superficie négative → exception attendue
    // -------------------------------------------------------
    public function testParcelleSuperficieNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La superficie doit être supérieure à zéro.');

        $parcelle = new Parcelle();
        $parcelle->setNom('Champ Est');
        $parcelle->setSuperficie(-3.0);

        $manager = new ParcelleManager();
        $manager->validate($parcelle);
    }
}
