<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenWeatherFarmService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(OPENWEATHER_API_KEY)%')]
        private string $apiKey,
        #[Autowire('%env(OPENWEATHER_CITY)%')]
        private string $city,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCurrentForFarm(): ?array
    {
        if ($this->apiKey === '') {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q' => $this->city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ],
                'timeout' => 12,
            ]);

            $data = $response->toArray(false);

            return [
                'city' => $data['name'] ?? $this->city,
                'description' => $data['weather'][0]['description'] ?? '',
                'temp' => $data['main']['temp'] ?? null,
                'humidity' => $data['main']['humidity'] ?? null,
                'wind' => $data['wind']['speed'] ?? null,
            ];
        } catch (\Throwable $e) {
            $this->logger->warning('OpenWeather failed: '.$e->getMessage());

            return null;
        }
    }
}
