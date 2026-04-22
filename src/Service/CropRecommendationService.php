<?php

namespace App\Service;

use App\Entity\Parcelle;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CropRecommendationService
{
    private HttpClientInterface $httpClient;
    private string $openaiApiKey;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->openaiApiKey = $_ENV['OPENAI_API_KEY'] ?? '';
    }

    public function getRecommendation(Parcelle $parcelle): array
    {
        $typeSol = $parcelle->getTypeSol() ?? 'Inconnu';
        $localisation = $parcelle->getLocalisation() ?? 'Tunisie';
        
        $prompt = "Tu es un expert agricole. On a une parcelle située en '$localisation' avec un type de sol '$typeSol'. "
                . "Recommande la meilleure culture agricole adaptée. "
                . "Donne aussi une estimation moyenne du rendement en tonnes par hectare pour cette culture. "
                . "Réponds UNIQUEMENT au format JSON strict avec les clés suivantes : "
                . "'recommended_crop' (string, le nom de la culture), "
                . "'reasoning' (string, explication brève de pourquoi c'est adapté au sol), "
                . "'estimated_yield_per_ha' (float, juste le nombre de tonnes par hectare).";

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu es un expert agronome.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.7,
                ],
            ]);

            $content = $response->toArray();
            $message = $content['choices'][0]['message']['content'];
            
            // OpenAI sometimes wraps JSON in markdown blocks like ```json ... ```
            $message = trim($message);
            if (str_starts_with($message, '```json')) {
                $message = substr($message, 7);
                if (str_ends_with($message, '```')) {
                    $message = substr($message, 0, -3);
                }
            } elseif (str_starts_with($message, '```')) {
                $message = substr($message, 3);
                if (str_ends_with($message, '```')) {
                    $message = substr($message, 0, -3);
                }
            }

            $data = json_decode($message, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($data['recommended_crop'])) {
                $data['total_estimated_yield'] = $this->calculateTotalYield($data['estimated_yield_per_ha'], $parcelle->getSuperficie());
                return $data;
            }

            throw new \Exception("Le format JSON renvoyé par l'IA est invalide.");
        } catch (\Exception $e) {
            // Fallback en cas d'erreur ou de clé expirée
            return $this->getFallbackRecommendation($parcelle);
        }
    }

    private function calculateTotalYield(float $yieldPerHa, float $area): float
    {
        return round($yieldPerHa * $area, 2);
    }

    private function getFallbackRecommendation(Parcelle $parcelle): array
    {
        $typeSol = strtolower($parcelle->getTypeSol() ?? '');
        
        $crop = 'Blé';
        $reasoning = 'Le blé est une culture tolérante adaptée à de nombreux types de sols par défaut.';
        $yieldPerHa = 3.5; // Tonnes par hectare

        if (str_contains($typeSol, 'sableux')) {
            $crop = 'Pomme de terre';
            $reasoning = 'Les sols sableux drainent bien l\'eau, idéaux pour les tubercules comme la pomme de terre.';
            $yieldPerHa = 30.0;
        } elseif (str_contains($typeSol, 'argileux')) {
            $crop = 'Tournesol';
            $reasoning = 'Les sols argileux retiennent l\'eau, ce qui convient aux cultures avec un système racinaire puissant.';
            $yieldPerHa = 2.5;
        } elseif (str_contains($typeSol, 'limoneux')) {
            $crop = 'Maïs';
            $reasoning = 'Les sols limoneux sont très fertiles et excellents pour les cultures exigeantes comme le maïs.';
            $yieldPerHa = 9.0;
        }

        return [
            'recommended_crop' => $crop,
            'reasoning' => $reasoning . " (Ceci est une recommandation de secours car l'API OpenAI est indisponible).",
            'estimated_yield_per_ha' => $yieldPerHa,
            'total_estimated_yield' => $this->calculateTotalYield($yieldPerHa, $parcelle->getSuperficie())
        ];
    }
}
