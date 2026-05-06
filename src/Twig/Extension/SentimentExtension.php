<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SentimentExtension extends AbstractExtension
{
    private array $positiveWords = [
        // Français
        'excellent',
        'bon',
        'bien',
        'super',
        'fantastique',
        'génial',
        'parfait',
        'satisfait',
        'recommandé',
        'rapide',
        'efficace',
        'propre',
        'magnifique',
        'utile',
        'agreable',
        'top',
        'merci',
        'bravo',
        'fiable',
        'solide',
        'pratique',
        'géniale',
        'parfaite',
        'incroyable',
        'adore',
        'adoré',
        'géniaux',
        // Anglais
        'good',
        'great',
        'excellent',
        'amazing',
        'perfect',
        'awesome',
        'nice',
        'best',
        'love',
        'fantastic',
        'wonderful',
        'brilliant',
        'outstanding',
        'helpful',
        'fast',
        'reliable',
        'loved',
        'beautiful',
        // Tunisien
        'mlih',
        'behi',
        'tayara',
        'yesser',
        'haja waw',
    ];

    private array $negativeWords = [
        // Français
        'mauvais',
        'nul',
        'pire',
        'horrible',
        'déçu',
        'lent',
        'cassé',
        'sale',
        'inutile',
        'défectueux',
        'problème',
        'décevant',
        'catastrophe',
        'panne',
        'arnaque',
        'retard',
        'mauvaise',
        'médiocre',
        'détesté',
        'fui',
        // Anglais
        'bad',
        'terrible',
        'awful',
        'worst',
        'poor',
        'hate',
        'broken',
        'slow',
        'useless',
        'disgusting',
        'garbage',
        'trash',
        'horrendous',
        'disappointed',
        'scam',
        'problem',
        'fail',
        'failed',
        'defective',
        // Tunisien
        'khayeb',
        'zbala',
        'karez',
        'mgata'
    ];

    private $mlService;

    public function __construct(\App\Service\MachineLearningService $mlService)
    {
        $this->mlService = $mlService;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sentiment', [$this, 'analyzeSentiment']),
            new TwigFilter('sentiment_badge', [$this, 'getSentimentBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function analyzeSentiment(?string $text): string
    {
        // On utilise le modèle Machine Learning (Python)
        return $this->mlService->analyzeSentiment($text ?? '');
    }

    public function getSentimentBadge(?string $text): string
    {
        $sentiment = $this->analyzeSentiment($text);

        if ($sentiment === 'Positif') {
            return '<span style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><i class="fa-regular fa-face-smile"></i> Positif / Positive</span>';
        } elseif ($sentiment === 'Négatif') {
            return '<span style="background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><i class="fa-regular fa-face-frown"></i> Négatif / Negative</span>';
        }

        return '<span style="background: rgba(149, 165, 166, 0.2); color: #95a5a6; border: 1px solid rgba(149, 165, 166, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><i class="fa-regular fa-face-meh"></i> Neutre / Neutral</span>';
    }
}
