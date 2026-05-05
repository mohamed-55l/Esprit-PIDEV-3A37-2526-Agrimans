<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReviewControllerTest extends WebTestCase
{
    public function testReviewIndexDisplaysSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/');

        $this->assertResponseIsSuccessful();
    }

    public function testReviewIndexWithSearchQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/?q=excellent');

        $this->assertResponseIsSuccessful();
    }

    public function testReviewIndexWithSortByDate(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/?sort=date_review&order=DESC');

        $this->assertResponseIsSuccessful();
    }

    public function testReviewIndexWithSortByNote(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/?sort=note&order=DESC');

        $this->assertResponseIsSuccessful();
    }

    public function testReviewNewFormDisplays(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testReviewShowWith404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
