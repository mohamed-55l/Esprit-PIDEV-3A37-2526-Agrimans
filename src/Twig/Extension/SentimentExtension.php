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

    public function getFilters(): array
    {
        return [
            new TwigFilter('sentiment', [$this, 'analyzeSentiment']),
            new TwigFilter('sentiment_badge', [$this, 'getSentimentBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function analyzeSentiment(?string $text): string
    {
        if (empty(trim((string) $text))) {
            return 'Neutre';
        }

        // Utiliser mb_strtolower pour préserver les accents en UTF-8
        $text = mb_strtolower($text, 'UTF-8');

        // Supprimer la ponctuation pour isoler les mots
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $score = 0;

        foreach ($words as $word) {
            if (in_array($word, $this->positiveWords, true)) {
                $score++;
            } elseif (in_array($word, $this->negativeWords, true)) {
                $score--;
            }
        }

        if ($score > 0) {
            return 'Positif';
        } elseif ($score < 0) {
            return 'Négatif';
        }

        return 'Neutre';
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
