<?php

namespace App\Controller;

use App\Service\SessionCartService;
use App\Repository\ProductRepository;
use App\Repository\ProductBundleRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
        
        if (empty($sessionItems)) {
            $this->addFlash('error', 'Votre panier est vide. Impossible de générer une facture.');
            return $this->redirectToRoute('app_marketplace_cart');
        }

        $enrichedItems = [];
        foreach ($sessionItems as $key => $item) {
            if (isset($item['isBundle']) && $item['isBundle']) {
                $bundle = $bundleRepository->find($item['id']);
                if ($bundle) {
                    $enrichedItems[] = [
                        'name' => $bundle->getName(),
                        'type' => 'Pack Promo',
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'lineTotal' => $item['price'] * $item['quantity']
                    ];
                }
            } else {
                $product = $productRepository->find($item['id']);
                if ($product) {
                    $enrichedItems[] = [
                        'name' => $product->getName(),
                        'type' => $product->getCategory(),
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'lineTotal' => $item['price'] * $item['quantity']
                    ];
                }
            }
        }

        $total = $sessionCartService->getCartTotal();

        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('invoice/invoice.html.twig', [
            'items' => $enrichedItems,
            'total' => $total,
            'date' => new \DateTime(),
            'invoiceNumber' => 'INV-' . strtoupper(uniqid())
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("facture-agrimans.pdf", [
            "Attachment" => true
        ]);
        
        return new Response();
    }
}
