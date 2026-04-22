<?php

namespace App\Controller;

use App\Entity\ProductBundle;
use App\Repository\ProductBundleRepository;
use App\Service\SessionCartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketplace/bundles')]
final class BundleController extends AbstractController
{
    #[Route(name: 'app_marketplace_bundles', methods: ['GET'])]
    public function index(Request $request, ProductBundleRepository $bundleRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 6; // Bundles per page
        
        $bundles = $bundleRepository->findActiveBundlesPaginated($page, $limit);
        $totalBundles = $bundleRepository->countActiveBundles();
        $totalPages = ceil($totalBundles / $limit);

        return $this->render('bundle/index.html.twig', [
            'bundles' => $bundles,
            'totalBundles' => $totalBundles,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pageSize' => $limit,
        ]);
    }

    #[Route('/{id}', name: 'app_marketplace_bundle_show', methods: ['GET'])]
    public function show(ProductBundle $bundle): Response
    {
        return $this->render('bundle/show.html.twig', [
            'bundle' => $bundle,
        ]);
    }

    #[Route('/{id}/add', name: 'app_marketplace_bundle_add', methods: ['POST'])]
    public function addToCart(
        Request $request,
        ProductBundle $bundle,
        SessionCartService $sessionCartService
    ): Response {
        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity <= 0) {
            $this->addFlash('error', 'La quantité doit être supérieure à 0');
            return $this->redirectToRoute('app_marketplace_bundles');
        }

        if (!$bundle->isActive()) {
            $this->addFlash('error', 'Ce pack n\'est plus disponible');
            return $this->redirectToRoute('app_marketplace_bundles');
        }

        // Add bundle to cart as special item
        $sessionCartService->addToCart(
            $bundle->getId(),
            $bundle->getBundlePrice(),
            $bundle->getName(),
            $quantity,
            true // isBundle
        );

        $savings = $bundle->getSavings() * $quantity;
        $this->addFlash('success', sprintf(
            "%d %s '%s' ajouté(s) au panier ! Économie : %.2f TND",
            $quantity,
            $quantity > 1 ? 'packs de' : 'pack de',
            $bundle->getName(),
            $savings
        ));

        return $this->redirectToRoute('app_marketplace_index');
    }
}
