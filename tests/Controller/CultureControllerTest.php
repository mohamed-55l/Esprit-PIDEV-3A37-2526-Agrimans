<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels pour CultureController.
 *
 * Routes testées :
 *  - GET  /culture            → liste des cultures
 *  - GET  /culture/new        → formulaire de création
 *  - POST /culture/new        → soumission du formulaire
 *  - GET  /culture/{id}       → détail d'une culture
 *  - GET  /culture/{id}/edit  → formulaire de modification
 */
class CultureControllerTest extends WebTestCase
{
    // -------------------------------------------------------
    // Test 1 : La page liste retourne HTTP 200
    // -------------------------------------------------------
    public function testIndexPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/culture');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    // -------------------------------------------------------
    // Test 2 : La page de création retourne HTTP 200
    // -------------------------------------------------------
    public function testNewPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/culture/new');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    // -------------------------------------------------------
    // Test 3 : Le formulaire de création contient les champs
    // -------------------------------------------------------
    public function testNewFormContainsExpectedFields(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/culture/new');

        $this->assertResponseIsSuccessful();

        // Vérifier que le formulaire existe
        $this->assertGreaterThan(
            0,
            $crawler->filter('form')->count(),
            'La page doit contenir un formulaire.'
        );

        // Vérifier la présence du champ nom
        $this->assertGreaterThan(
            0,
            $crawler->filter('input[name*="nom"], input[id*="nom"]')->count(),
            'Le formulaire doit contenir un champ nom.'
        );
    }

    // -------------------------------------------------------
    // Test 4 : Soumission avec données valides → redirection
    // -------------------------------------------------------
    public function testNewCultureSubmitRedirects(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/culture/new');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $formName = $form->getName();

        $client->submitForm('Save', [
            $formName . '[nom]'          => 'Blé Test Unitaire',
            $formName . '[type_culture]' => 'Céréale',
            $formName . '[etat_culture]' => 'En cours',
        ]);

        // Après soumission réussie → redirection
        $this->assertResponseRedirects();
    }

    // -------------------------------------------------------
    // Test 5 : La page index contient du contenu HTML
    // -------------------------------------------------------
    public function testIndexPageContainsHtmlBody(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/culture');

        $this->assertResponseIsSuccessful();

        $this->assertGreaterThan(
            0,
            $crawler->filter('body')->count(),
            'La page doit avoir un body HTML.'
        );
    }

    // -------------------------------------------------------
    // Test 6 : Route inexistante → HTTP 404
    // -------------------------------------------------------
    public function testNotFoundRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/culture/99999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
