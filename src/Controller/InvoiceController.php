<?php

namespace App\Controller;

use App\Service\SessionCartService;
use App\Repository\ProductRepository;
use App\Repository\ProductBundleRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketplace/invoice')]
final class InvoiceController extends AbstractController
{
    #[Route('/download', name: 'app_marketplace_invoice_download')]
    public function downloadInvoice(
        SessionCartService $sessionCartService,
        ProductRepository $productRepository,
        ProductBundleRepository $bundleRepository
    ): Response {
        $sessionItems = $sessionCartService->getCart();

        $enrichedItems = [];

        if (!empty($sessionItems)) {
            foreach ($sessionItems as $key => $item) {
                if (isset($item['isBundle']) && $item['isBundle']) {
                    $bundle = $bundleRepository->find($item['id']);
                    if ($bundle) {
                        $enrichedItems[] = [
                            'name'      => $bundle->getName(),
                            'type'      => 'Pack Promo',
                            'quantity'  => $item['quantity'],
                            'unit'      => 'pack(s)',
                            'price'     => $item['price'],
                            'lineTotal' => $item['price'] * $item['quantity'],
                        ];
                    }
                } else {
                    $product = $productRepository->find($item['id']);
                    if ($product) {
                        $enrichedItems[] = [
                            'name'      => $product->getName(),
                            'type'      => $product->getCategory(),
                            'quantity'  => $item['quantity'],
                            'unit'      => 'kg',
                            'price'     => $item['price'],
                            'lineTotal' => $item['price'] * $item['quantity'],
                        ];
                    }
                }
            }
        }

        // Allow empty cart (show blank invoice with message)
        $total = array_sum(array_column($enrichedItems, 'lineTotal'));
        $invoiceNumber = 'INV-' . strtoupper(uniqid());
        $date = new \DateTime();

        // Build PDF HTML inline (no Twig render to avoid headers-already-sent)
        $html = $this->buildInvoiceHtml($enrichedItems, $total, $invoiceNumber, $date);

        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'DejaVu Sans');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'facture-agrimans.pdf')
        );
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function buildInvoiceHtml(array $items, float $total, string $invoiceNumber, \DateTime $date): string
    {
        $dateStr  = $date->format('d/m/Y H:i');
        $rows     = '';

        if (empty($items)) {
            $rows = '<tr><td colspan="5" style="text-align:center; color:#888; padding: 20px;">Panier vide – aucun article à facturer.</td></tr>';
        } else {
            foreach ($items as $item) {
                $lineTotal = number_format($item['lineTotal'], 2, ',', ' ');
                $price     = number_format($item['price'], 2, ',', ' ');
                $rows .= "
                <tr>
                    <td style='padding:8px; border-bottom:1px solid #eee;'>{$item['name']}</td>
                    <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$item['type']}</td>
                    <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$item['quantity']} {$item['unit']}</td>
                    <td style='padding:8px; border-bottom:1px solid #eee; text-align:right;'>{$price} DT</td>
                    <td style='padding:8px; border-bottom:1px solid #eee; text-align:right; font-weight:bold; color:#198B61;'>{$lineTotal} DT</td>
                </tr>";
            }
        }

        $totalFormatted = number_format($total, 2, ',', ' ');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facture {$invoiceNumber}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; }
        .header { background: #198B61; color: white; padding: 30px; }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 2px; }
        .header p { margin: 4px 0 0; opacity: 0.85; }
        .meta { padding: 20px 30px; display: flex; justify-content: space-between; border-bottom: 2px solid #198B61; }
        .meta-left, .meta-right { font-size: 12px; line-height: 1.7; }
        .meta-right { text-align: right; }
        .invoice-label { font-size: 20px; font-weight: bold; color: #198B61; }
        table { width: 100%; border-collapse: collapse; margin: 0 30px; width: calc(100% - 60px); }
        thead tr { background: #198B61; color: white; }
        thead th { padding: 10px 8px; text-align: left; font-size: 12px; }
        tbody tr:nth-child(even) { background: #f9f9f9; }
        .total-section { text-align: right; padding: 15px 30px; border-top: 2px solid #198B61; }
        .total-section .total-amount { font-size: 22px; font-weight: bold; color: #198B61; }
        .footer { background: #f5f5f5; padding: 15px 30px; text-align: center; font-size: 11px; color: #888; margin-top: 30px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>&#127807; AGRIMANS</h1>
        <p>Marché Frais en Ligne — facture officielle</p>
    </div>

    <div class="meta">
        <div class="meta-left">
            <strong>Émetteur :</strong> Agrimans Platform<br>
            <strong>Date :</strong> {$dateStr}<br>
        </div>
        <div class="meta-right">
            <div class="invoice-label">FACTURE</div>
            <strong>{$invoiceNumber}</strong>
        </div>
    </div>

    <br>
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th style="text-align:center;">Catégorie</th>
                <th style="text-align:center;">Quantité</th>
                <th style="text-align:right;">Prix unitaire</th>
                <th style="text-align:right;">Sous-total</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
        </tbody>
    </table>

    <div class="total-section">
        <div>TOTAL À PAYER</div>
        <div class="total-amount">{$totalFormatted} DT</div>
    </div>

    <div class="footer">
        Merci pour votre confiance · Agrimans &copy; {$date->format('Y')} · Ce document est généré automatiquement.
    </div>
</body>
</html>
HTML;
    }
}
