<?php

namespace App\Tests\Entity;

use App\Entity\Animal;
use App\Entity\AnimalNourriture;
use App\Entity\Nourriture;
use PHPUnit\Framework\TestCase;

class AnimalNourritureTest extends TestCase
{
    public function testFeedingRecordLinksAnimalAndNourriture(): void
    {
        $animal = (new Animal())->setNom('Bella')->setEspece('Vache');
        $food   = (new Nourriture())->setName('Foin')->setType('Fourrage')->setQuantity(10.0);

        $link = new AnimalNourriture();
        $link->setAnimal($animal)
             ->setNourriture($food)
             ->setQuantity_fed(5.5)
             ->setNotes('Repas du matin');

        $this->assertSame($animal, $link->getAnimal());
        $this->assertSame($food, $link->getNourriture());
        $this->assertSame(5.5, $link->getQuantity_fed());
        $this->assertSame('Repas du matin', $link->getNotes());
    }

    public function testFeedingDateIsRecorded(): void
    {
        $link = new AnimalNourriture();
        $when = new \DateTime('2026-05-06 07:30:00');
        $link->setFeeding_date($when);

        $this->assertSame($when, $link->getFeeding_date());
    }

    public function testBidirectionalRegistration(): void
    {
        $animal = new Animal();
        $link   = new AnimalNourriture();

        $animal->addAnimalNourriture($link);
        $link->setAnimal($animal);

        $this->assertCount(1, $animal->getAnimalNourritures());
        $this->assertSame($animal, $link->getAnimal());
    }
}
