<?php

namespace App\Tests\Entity;

use App\Entity\Animal;
use App\Entity\AnimalNourriture;
use App\Entity\Nourriture;
use PHPUnit\Framework\TestCase;

class AnimalTest extends TestCase
{
    public function testCreateAnimalWithCoreFields(): void
    {
        $a = new Animal();
        $a->setNom('Bella')
          ->setEspece('Vache')
          ->setRace('Holstein')
          ->setPoids(420.5)
          ->setEtatSante('Bonne');

        $this->assertSame('Bella', $a->getNom());
        $this->assertSame('Vache', $a->getEspece());
        $this->assertSame('Holstein', $a->getRace());
        $this->assertSame(420.5, $a->getPoids());
        $this->assertSame('Bonne', $a->getEtatSante());
    }

    public function testFrenchAndEnglishApiAreAliases(): void
    {
        $a = new Animal();
        $a->setName('Rex')->setType('Chien')->setBreed('Berger')->setWeight(30.0)->setHealthStatus('OK');

        $this->assertSame('Rex', $a->getNom());
        $this->assertSame('Chien', $a->getEspece());
        $this->assertSame('Berger', $a->getRace());
        $this->assertSame(30.0, $a->getPoids());
        $this->assertSame('OK', $a->getEtatSante());
    }

    public function testNewAnimalIsNotArchived(): void
    {
        $a = new Animal();
        $this->assertFalse($a->isArchived());
        $this->assertNull($a->getDeletedAt());
    }

    public function testSoftDeleteMarksArchived(): void
    {
        $a = new Animal();
        $a->setDeletedAt(new \DateTimeImmutable('2026-05-01'));
        $this->assertTrue($a->isArchived());
    }

    public function testRestoreFromArchive(): void
    {
        $a = new Animal();
        $a->setDeletedAt(new \DateTimeImmutable());
        $a->setDeletedAt(null);
        $this->assertFalse($a->isArchived());
    }

    public function testAnimalNourrituresCollectionStartsEmpty(): void
    {
        $a = new Animal();
        $this->assertCount(0, $a->getAnimalNourritures());
    }

    public function testAddAnimalNourritureIsIdempotent(): void
    {
        $a = new Animal();
        $link = new AnimalNourriture();

        $a->addAnimalNourriture($link);
        $a->addAnimalNourriture($link);

        $this->assertCount(1, $a->getAnimalNourritures());
    }

    public function testRemoveAnimalNourriture(): void
    {
        $a = new Animal();
        $link = new AnimalNourriture();
        $a->addAnimalNourriture($link);
        $a->removeAnimalNourriture($link);

        $this->assertCount(0, $a->getAnimalNourritures());
    }

    public function testDateNaissanceIsImmutable(): void
    {
        $a = new Animal();
        $birth = new \DateTimeImmutable('2024-03-15');
        $a->setDateNaissance($birth);
        $this->assertSame($birth, $a->getDateNaissance());
    }

    public function testUserIdOwnership(): void
    {
        $a = new Animal();
        $a->setUserId(42);
        $this->assertSame(42, $a->getUserId());
    }
}
