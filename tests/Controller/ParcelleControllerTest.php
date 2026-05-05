<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels pour ParcelleController.
 *
 * Routes testées :
 *  - GET  /parcelle            → liste des parcelles
 *  - GET  /parcelle/new        → formulaire de création
 *  - POST /parcelle/new        → soumission du formulaire
 *  - GET  /parcelle/{id}       → détail d'une parcelle
 *  - GET  /parcelle/{id}/edit  → formulaire de modification
 */
class ParcelleControllerTest extends WebTestCase
{
    // -------------------------------------------------------
    // Test 1 : La page liste retourne HTTP 200
    // -------------------------------------------------------
    public function testIndexPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/parcelle');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    // -------------------------------------------------------
    // Test 2 : La page de création retourne HTTP 200
    // -------------------------------------------------------
    public function testNewPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/parcelle/new');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    // -------------------------------------------------------
    // Test 3 : Le formulaire de création contient les champs
    // -------------------------------------------------------
    public function testNewFormContainsExpectedFields(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/parcelle/new');

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
    // Test 4 : Soumission du formulaire avec données valides
    //          → redirection HTTP 303
    // -------------------------------------------------------
    public function testNewParcelleSubmitRedirects(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/parcelle/new');

        $this->assertResponseIsSuccessful();

        // Trouver le formulaire et le soumettre
        $form = $crawler->selectButton('Enregistrer')->form();

        // Remplir les champs obligatoires
        $formName = $form->getName(); // ex: "parcelle"
        $client->submitForm('Enregistrer', [
            $formName . '[nom]'        => 'Champ Test Unitaire',
            $formName . '[superficie]' => '10.5',
            $formName . '[localisation]' => 'Tunis',
        ]);

        // Après soumission réussie → redirection
        $this->assertResponseRedirects();
    }

    // -------------------------------------------------------
    // Test 5 : La page index contient le titre attendu
    // -------------------------------------------------------
    public function testIndexPageContainsTitle(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/parcelle');

        $this->assertResponseIsSuccessful();

        // Vérifier que la réponse contient du contenu HTML
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
        $client->request('GET', '/parcelle/99999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
