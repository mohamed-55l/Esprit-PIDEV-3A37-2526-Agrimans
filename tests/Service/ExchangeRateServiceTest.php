<?php

namespace App\Tests\Service;

use App\Service\ExchangeRateService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Tests unitaires pour ExchangeRateService.
 *
 * Règles métier testées :
 *  1. Le taux de base (TND) est toujours 1.
 *  2. La conversion multiplie correctement le montant par le taux.
 *  3. Si la devise cible n'est pas supportée, le montant original est retourné.
 *  4. Les devises supportées sont exactement ['TND', 'EUR', 'USD'].
 *  5. Sans clé API, les taux de repli (fallback) sont utilisés.
 *
 * Aucune base de données ni appel HTTP réel n'est effectué :
 * tous les collaborateurs sont des mocks PHPUnit.
 */
class ExchangeRateServiceTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Construit un ExchangeRateService dont le cache renvoie toujours
     * les taux passés en paramètre (simule un cache déjà chaud).
     *
     * @param array $rates   Taux à retourner, ex. ['TND'=>1,'EUR'=>0.29,'USD'=>0.32]
     * @param string $apiKey Clé API simulée (vide = pas de clé)
     */
    private function buildService(array $rates, string $apiKey = ''): ExchangeRateService
    {
        // Mock du cache : on simule get() qui exécute immédiatement le callback
        // et retourne ce que le callback retourne.
        // Pour simplifier, on force le cache à retourner directement $rates.
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')
              ->willReturnCallback(function (string $key, callable $callback) use ($rates) {
                  // On ne veut pas vraiment exécuter le callback réseau :
                  // on retourne directement les taux prédéfinis.
                  return $rates;
              });

        // Mock du client HTTP (jamais appelé ici car le cache court-circuite)
        $httpClient = $this->createMock(HttpClientInterface::class);

        // Mock du ParameterBag pour la clé API
        $params = $this->createMock(ParameterBagInterface::class);
        $params->method('get')
               ->with('exchange_rate_api_key')
               ->willReturn($apiKey);

        return new ExchangeRateService($httpClient, $cache, $params);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 1 : Taux de base TND = 1
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : La devise de base est le TND.
     * Son taux doit toujours valoir 1 (pas de conversion).
     */
    public function testTndRateIsAlwaysOne(): void
    {
        // Arrange
        $service = $this->buildService(['TND' => 1.0, 'EUR' => 0.29, 'USD' => 0.32]);

        // Act
        $rates = $service->getRates();

        // Assert
        $this->assertArrayHasKey('TND', $rates, 'Le taux TND doit exister dans les résultats.');
        $this->assertSame(1.0, (float) $rates['TND'], 'Le taux TND doit être exactement 1.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2 : Conversion TND → EUR correcte
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : La conversion doit multiplier le montant par le taux
     * et arrondir à 2 décimales.
     *
     * Exemple : 100 TND × 0.29 = 29.00 EUR
     */
    public function testConvertTndToEurIsCorrect(): void
    {
        // Arrange
        $service = $this->buildService(['TND' => 1.0, 'EUR' => 0.29, 'USD' => 0.32]);

        // Act
        $result = $service->convert(100.0, 'EUR');

        // Assert
        $this->assertSame(29.0, $result,
            'La conversion de 100 TND en EUR doit donner 29.00 EUR (100 × 0.29).');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 3 : Conversion TND → USD correcte
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Conversion TND → USD avec le taux de repli 0.32.
     *
     * Exemple : 50 TND × 0.32 = 16.00 USD
     */
    public function testConvertTndToUsdIsCorrect(): void
    {
        // Arrange
        $service = $this->buildService(['TND' => 1.0, 'EUR' => 0.29, 'USD' => 0.32]);

        // Act
        $result = $service->convert(50.0, 'USD');

        // Assert
        $this->assertSame(16.0, $result,
            'La conversion de 50 TND en USD doit donner 16.00 USD (50 × 0.32).');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 4 : Devise non supportée → retourne le montant original
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Si la devise demandée n'existe pas dans les taux,
     * le service retourne le montant original sans modification.
     */
    public function testConvertWithUnsupportedCurrencyReturnsOriginalAmount(): void
    {
        // Arrange
        $service = $this->buildService(['TND' => 1.0, 'EUR' => 0.29, 'USD' => 0.32]);

        // Act — "GBP" n'est pas dans la liste supportée
        $result = $service->convert(75.0, 'GBP');

        // Assert
        $this->assertSame(75.0, $result,
            'Une devise non supportée doit retourner le montant original.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 5 : Les devises supportées sont TND, EUR, USD
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Le service ne supporte que TND, EUR et USD.
     * Cette liste doit être stable et complète.
     */
    public function testSupportedCurrenciesAreExactlyTndEurUsd(): void
    {
        // Arrange
        $service = $this->buildService(['TND' => 1.0, 'EUR' => 0.29, 'USD' => 0.32]);

        // Act
        $currencies = $service->getSupportedCurrencies();

        // Assert
        $this->assertCount(3, $currencies,
            'Il doit y avoir exactement 3 devises supportées.');
        $this->assertContains('TND', $currencies, 'TND doit être supportée.');
        $this->assertContains('EUR', $currencies, 'EUR doit être supportée.');
        $this->assertContains('USD', $currencies, 'USD doit être supportée.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 6 : Taux de repli (fallback) utilisés sans clé API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : En l'absence de clé API, le service utilise des taux
     * de secours codés en dur (TND=1, EUR=0.29, USD=0.32).
     * Ces taux permettent à l'application de fonctionner hors ligne.
     *
     * Ici on teste le callback du cache pour simuler l'absence de clé.
     */
    public function testFallbackRatesUsedWhenApiKeyIsMissing(): void
    {
        // Arrange — on fait vraiment exécuter le callback du cache
        // pour tester la logique interne "sans clé API"
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')
              ->willReturnCallback(function (string $key, callable $callback) {
                  // On exécute le vrai callback avec un ItemInterface fictif
                  $item = $this->createMock(ItemInterface::class);
                  // expiresAfter() doit retourner $item (type: static)
                  $item->method('expiresAfter')->willReturnSelf();
                  return $callback($item);
              });

        $httpClient = $this->createMock(HttpClientInterface::class);
        // httpClient->request ne doit PAS être appelé (pas de clé API)
        $httpClient->expects($this->never())->method('request');

        $params = $this->createMock(ParameterBagInterface::class);
        $params->method('get')
               ->with('exchange_rate_api_key')
               ->willReturn(''); // clé vide = pas de clé API

        $service = new ExchangeRateService($httpClient, $cache, $params);

        // Act
        $rates = $service->getRates();

        // Assert — taux de repli
        $this->assertSame(1.0,  (float) $rates['TND'], 'Taux de repli TND doit être 1.');
        $this->assertSame(0.29, (float) $rates['EUR'], 'Taux de repli EUR doit être 0.29.');
        $this->assertSame(0.32, (float) $rates['USD'], 'Taux de repli USD doit être 0.32.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 7 : Conversion avec montant nul
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Règle métier : Convertir 0 TND doit toujours donner 0,
     * quelle que soit la devise cible.
     */
    public function testConvertZeroAmountAlwaysReturnsZero(): void
    {
        // Arrange
        $service = $this->buildService(['TND' => 1.0, 'EUR' => 0.29, 'USD' => 0.32]);

        // Act & Assert
        $this->assertSame(0.0, $service->convert(0.0, 'EUR'),
            'Convertir 0 TND en EUR doit donner 0.');
        $this->assertSame(0.0, $service->convert(0.0, 'USD'),
            'Convertir 0 TND en USD doit donner 0.');
    }
}
