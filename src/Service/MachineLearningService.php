<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MachineLearningService
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    /**
     * Analyse le sentiment d'un texte en appelant le modèle Machine Learning Python
     */
    public function analyzeSentiment(string $text): string
    {
        if (empty(trim($text))) {
            return 'Neutre';
        }

        // Chemin vers le script python
        $scriptPath = $this->projectDir . '/machine_learning/sentiment.py';

        // Creation du processus: python sentiment.py "le texte"
        $process = new Process(['python', $scriptPath, $text]);
        $process->setTimeout(10); // 10 secondes max

        try {
            $process->mustRun();
            
            $output = $process->getOutput();
            $data = json_decode($output, true);

            if (isset($data['sentiment'])) {
                return $data['sentiment']; // 'Positif', 'Négatif', ou 'Neutre'
            }

        } catch (ProcessFailedException $exception) {
            // En cas d'erreur (python non installé, lib manquante), on retourne Neutre par défaut
            // pour ne pas crasher l'application
        }

        return 'Neutre';
    }
}
