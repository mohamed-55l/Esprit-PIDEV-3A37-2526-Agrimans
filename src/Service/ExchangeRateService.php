<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExchangeRateService
{
    private const BASE_CURRENCY = 'TND';
    private const SUPPORTED_CURRENCIES = ['TND', 'EUR', 'USD'];

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private ParameterBagInterface $params
    ) {}

    /**
     * Get exchange rates relative to base currency (TND).
     * Caches results for 1 hour to avoid hitting API limits.
     */
    public function getRates(): array
    {
        return $this->cache->get('exchange_rates_tnd', function (ItemInterface $item) {
            $item->expiresAfter(3600); // Cache for 1 hour

            $apiKey = $this->params->get('exchange_rate_api_key');
            if (empty($apiKey)) {
                // Fallback hardcoded rates if API key is missing
                return ['TND' => 1, 'EUR' => 0.29, 'USD' => 0.32];
            }

            try {
                // Using ExchangeRate-API standard format
                $response = $this->httpClient->request(
                    'GET',
                    "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/" . self::BASE_CURRENCY
                );

                $data = $response->toArray();
                
                if ($data['result'] === 'success') {
                    $rates = [];
                    foreach (self::SUPPORTED_CURRENCIES as $currency) {
                        if (isset($data['conversion_rates'][$currency])) {
                            $rates[$currency] = $data['conversion_rates'][$currency];
                        }
                    }
                    return $rates;
                }
            } catch (\Exception $e) {
                // Ignore error and fallback to hardcoded rates
            }

            return ['TND' => 1, 'EUR' => 0.29, 'USD' => 0.32];
        });
    }

    public function convert(float $amount, string $targetCurrency): float
    {
        $rates = $this->getRates();
        
        if (!isset($rates[$targetCurrency])) {
            return $amount; // Return original if rate not found
        }

        // Formula: amount * (target_rate / base_rate(which is 1))
        return round($amount * $rates[$targetCurrency], 2);
    }
    
    public function getSupportedCurrencies(): array
    {
        return self::SUPPORTED_CURRENCIES;
    }
}
