<?php

namespace App\Service;

use App\Entity\Animal;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class AnimalListPdfExporter
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    /**
     * @param iterable<Animal> $animals
     */
    public function export(iterable $animals): string
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);

        $html = $this->twig->render('pdf/animal_list.html.twig', [
            'animals' => $animals,
            'generatedAt' => new \DateTimeImmutable(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
