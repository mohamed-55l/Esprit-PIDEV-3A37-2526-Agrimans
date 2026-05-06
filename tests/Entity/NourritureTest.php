<?php

namespace App\Tests\Entity;

use App\Entity\Nourriture;
use PHPUnit\Framework\TestCase;

class NourritureTest extends TestCase
{
    public function testCoreFields(): void
    {
        $n = new Nourriture();
        $n->setName('Foin')
          ->setType('Fourrage')
          ->setQuantity(50.0)
          ->setUnit('kg')
          ->setSupplier('CoopAgri');

        $this->assertSame('Foin', $n->getName());
        $this->assertSame('Fourrage', $n->getType());
        $this->assertSame(50.0, $n->getQuantity());
        $this->assertSame('kg', $n->getUnit());
        $this->assertSame('CoopAgri', $n->getSupplier());
    }

    public function testCostIsStoredAsStringDecimal(): void
    {
        $n = new Nourriture();
        $n->setCost('199.99');

        $this->assertIsString($n->getCost(), 'Cost must be string (decimal) — not float (money rule)');
        $this->assertSame('199.99', $n->getCost());
    }

    public function testCostKeepsExactPrecision(): void
    {
        $n = new Nourriture();
        $n->setCost('0.10');

        $this->assertSame('0.10', $n->getCost(), 'Decimal must not lose trailing zero like float would');
    }

    public function testCostIsNullable(): void
    {
        $n = new Nourriture();
        $this->assertNull($n->getCost());

        $n->setCost(null);
        $this->assertNull($n->getCost());
    }

    public function testCostCanRepresentLargeAmount(): void
    {
        $n = new Nourriture();
        $n->setCost('9999999999.99');

        $this->assertSame('9999999999.99', $n->getCost());
    }

    public function testExpiryDate(): void
    {
        $n = new Nourriture();
        $date = new \DateTime('2026-12-31');
        $n->setExpiry_date($date);

        $this->assertSame($date, $n->getExpiry_date());
    }

    public function testNutritionalValueIsOptional(): void
    {
        $n = new Nourriture();
        $this->assertNull($n->getNutritional_value());

        $n->setNutritional_value('Protéines 18%');
        $this->assertSame('Protéines 18%', $n->getNutritional_value());
    }
}
