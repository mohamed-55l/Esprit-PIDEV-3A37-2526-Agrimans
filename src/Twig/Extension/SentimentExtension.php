<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SentimentExtension extends AbstractExtension
{
    private array $positiveWords = [
        'excellent', 'bon', 'bien', 'super', 'fantastique', 'génial', 'parfait', 
        'satisfait', 'recommandé', 'rapide', 'efficace', 'propre', 'magnifique', 
        'utile', 'agreable', 'top', 'mlih', 'behy', 'tayara', 'merci', 'bravo',
        'fiable', 'solide', 'pratique', 'géniale', 'parfaite'
    ];

    private array $negativeWords = [
        'mauvais', 'nul', 'pire', 'horrible', 'déçu', 'lent', 'cassé', 'sale', 
        'inutile', 'défectueux', 'problème', 'décevant', 'khayeb', 'catastrophe',
        'panne', 'arnaque', 'lent', 'retard', 'mauvaise'
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
        if (!$text) {
            return 'Neutre';
        }

        // Convertir en minuscules et enlever la ponctuation basique
        $text = strtolower($text);
        // Supprimer les caractères spéciaux pour ne garder que les mots
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        
        $words = explode(' ', $text);
        
        $score = 0;

        foreach ($words as $word) {
            if (in_array($word, $this->positiveWords)) {
                $score++;
            } elseif (in_array($word, $this->negativeWords)) {
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
            return '<span style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><i class="fa-regular fa-face-smile"></i> Positif</span>';
        } elseif ($sentiment === 'Négatif') {
            return '<span style="background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><i class="fa-regular fa-face-frown"></i> Négatif</span>';
        }

        return '<span style="background: rgba(149, 165, 166, 0.2); color: #95a5a6; border: 1px solid rgba(149, 165, 166, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><i class="fa-regular fa-face-meh"></i> Neutre</span>';
    }
}
