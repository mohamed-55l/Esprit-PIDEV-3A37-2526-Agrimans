<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EquipementControllerTest extends WebTestCase
{
    public function testEquipementIndexDisplaysSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/equipement');

        $this->assertResponseIsSuccessful();
    }

    public function testEquipementIndexWithSearchQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/equipement?q=tracteur');

        $this->assertResponseIsSuccessful();
    }

    public function testEquipementIndexWithSortParameters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/equipement?sort=prix&order=DESC');

        $this->assertResponseIsSuccessful();
    }

    public function testEquipementNewFormDisplays(): void
    {
        $client = static::createClient();
        $client->request('GET', '/equipement/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEquipementShowWith404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/equipement/999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
