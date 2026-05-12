<?php

namespace App\Tests\Service;

use App\Entity\Equipement;
use App\Service\EquipementManager;
use PHPUnit\Framework\TestCase;

class EquipementManagerTest extends TestCase
{
    private EquipementManager $manager;

    protected function setUp(): void
    {
        $this->manager = new EquipementManager();
    }

    /**
     * Test: Valider un équipement valide
     * Scénario: Tous les champs obligatoires sont remplis correctement
     * Résultat attendu: La validation retourne true
     */
    public function testValidEquipement(): void
    {
        $equipement = new Equipement();
        $equipement->setNom('Tracteur John Deere');
        $equipement->setType('Tracteur');
        $equipement->setPrix(15000.00);
        $equipement->setDisponibilite('disponible');

        $this->assertTrue($this->manager->validate($equipement));
    }

    /**
     * Test: Rejeter un équipement sans nom
     * Scénario: Le nom est vide
     * Résultat attendu: Une exception est levée
     */
    public function testEquipementWithoutName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom de l\'équipement est obligatoire');

        $equipement = new Equipement();
        $equipement->setNom(''); // Nom vide
        $equipement->setType('Tracteur');
        $equipement->setPrix(15000.00);

        $this->manager->validate($equipement);
    }

    /**
     * Test: Rejeter un équipement avec nom null
     * Scénario: Le nom n'est pas défini
     * Résultat attendu: Une exception est levée
     */
    public function testEquipementWithNullName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom de l\'équipement est obligatoire');

        $equipement = new Equipement();
        // Pas d'appel à setNom
        $equipement->setType('Tracteur');
        $equipement->setPrix(15000.00);

        $this->manager->validate($equipement);
    }

    /**
     * Test: Rejeter un équipement avec prix invalide (négatif)
     * Scénario: Le prix est négatif
     * Résultat attendu: Une exception est levée
     */
    public function testEquipementWithNegativePrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prix doit être supérieur à zéro');

        $equipement = new Equipement();
        $equipement->setNom('Tracteur');
        $equipement->setType('Tracteur');
        $equipement->setPrix(-100.00); // Prix négatif

        $this->manager->validate($equipement);
    }

    /**
     * Test: Rejeter un équipement avec prix égal à zéro
     * Scénario: Le prix est zéro
     * Résultat attendu: Une exception est levée
     */
    public function testEquipementWithZeroPrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prix doit être supérieur à zéro');

        $equipement = new Equipement();
        $equipement->setNom('Tracteur');
        $equipement->setType('Tracteur');
        $equipement->setPrix(0.00); // Prix zéro

        $this->manager->validate($equipement);
    }

    /**
     * Test: Rejeter un équipement sans type
     * Scénario: Le type est vide
     * Résultat attendu: Une exception est levée
     */
    public function testEquipementWithoutType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le type de l\'équipement est obligatoire');

        $equipement = new Equipement();
        $equipement->setNom('Tracteur John Deere');
        $equipement->setType(''); // Type vide
        $equipement->setPrix(15000.00);

        $this->manager->validate($equipement);
    }

    /**
     * Test: Calculer le prix remisé pour un équipement coûteux
     * Scénario: Le prix dépasse 500 (10% de remise applicable)
     * Résultat attendu: Le prix remisé est 90% du prix original
     */
    public function testCalculateDiscountedPriceAbove500(): void
    {
        $equipement = new Equipement();
        $equipement->setPrix(1000.00);

        $discountedPrice = $this->manager->calculateDiscountedPrice($equipement);

        $this->assertEquals(900.00, $discountedPrice);
    }

    /**
     * Test: Pas de remise pour les équipements moins chers
     * Scénario: Le prix ne dépasse pas 500
     * Résultat attendu: Le prix reste inchangé
     */
    public function testCalculateDiscountedPriceBelow500(): void
    {
        $equipement = new Equipement();
        $equipement->setPrix(300.00);

        $discountedPrice = $this->manager->calculateDiscountedPrice($equipement);

        $this->assertEquals(300.00, $discountedPrice);
    }

    /**
     * Test: Vérifier la disponibilité d'un équipement
     * Scénario: L'équipement a une disponibilité 'disponible'
     * Résultat attendu: isAvailable retourne true
     */
    public function testIsAvailableEquipement(): void
    {
        $equipement = new Equipement();
        $equipement->setDisponibilite('disponible');

        $this->assertTrue($this->manager->isAvailable($equipement));
    }

    /**
     * Test: Vérifier l'indisponibilité d'un équipement
     * Scénario: L'équipement n'a pas une disponibilité 'disponible'
     * Résultat attendu: isAvailable retourne false
     */
    public function testIsNotAvailableEquipement(): void
    {
        $equipement = new Equipement();
        $equipement->setDisponibilite('indisponible');

        $this->assertFalse($this->manager->isAvailable($equipement));
    }
}
