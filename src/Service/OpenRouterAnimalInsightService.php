<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenRouterAnimalInsightService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(OPENROUTER_API_KEY)%')]
        private string $apiKey,
        #[Autowire('%env(OPENROUTER_MODEL)%')]
        private string $model,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $stats
     */
    public function generateInsight(array $stats, string $locale = 'fr'): string
    {
        if ($this->apiKey === '') {
            return 'Ajoutez la variable d’environnement OPENROUTER_API_KEY pour activer l’analyse IA du cheptel.';
        }

        $payload = json_encode($stats, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $prompt = <<<PROMPT
Tu es un conseiller agricole pour un éleveur. Analyse ces statistiques d'élevage (JSON) et réponds en {$locale}.
Donne 3 à 5 phrases concises : points forts, risques (santé, effectifs), et une action prioritaire cette semaine.
Ne répète pas le JSON brut.

Données:
{$payload}
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'timeout' => 45,
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);
            $data = $response->toArray(false);
            $content = $data['choices'][0]['message']['content'] ?? null;
            if (!\is_string($content) || $content === '') {
                return 'Réponse IA vide ou invalide. Réessayez plus tard.';
            }

            return trim($content);
        } catch (\Throwable $e) {
            $this->logger->warning('OpenRouter insight failed: '.$e->getMessage());

            return 'Impossible de joindre le service d’analyse pour le moment. Vérifiez la clé API et la connexion réseau.';
        }
    }
}
