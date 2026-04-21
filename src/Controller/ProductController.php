<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Rating;
use App\Entity\ProductBundle;
use App\Form\ProductType;
use App\Form\RatingType;
use App\Repository\ProductRepository;
use App\Repository\ProductBundleRepository;
use App\Repository\RatingRepository;
use App\Service\CartService;
use App\Service\SessionCartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/marketplace')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_marketplace_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, ProductBundleRepository $bundleRepository, SessionCartService $sessionCartService, \App\Service\ExchangeRateService $exchangeRateService): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 12; // Items per page
        $search = $request->query->get('search', '');
        $category = $request->query->get('category', '');

        if ($search || $category) {
            $products = $productRepository->search($search, $category ?: null);
            $totalProducts = count($products);
            $products = array_slice($products, ($page - 1) * $limit, $limit);
        } else {
            // Use paginated query
            $query = $productRepository->createQueryBuilder('p')
                ->orderBy('p.id', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery();
            $products = $query->getResult();
            
            // Get total count
            $totalProducts = $productRepository->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }

        $totalPages = ceil($totalProducts / $limit);
        $bundles = $bundleRepository->findActiveBundlesPaginated(1, 6);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'bundles' => $bundles,
            'search' => $search,
            'category' => $category,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'limit' => $limit,
            'cartCount' => $sessionCartService->getCartCount(),
            'rates' => $exchangeRateService->getRates(),
            'currency' => $request->getSession()->get('currency', 'TND'),
        ]);
    }

    #[Route('/set-currency/{currency}', name: 'app_marketplace_set_currency', methods: ['GET'])]
    public function setCurrency(string $currency, Request $request): Response
    {
        if (in_array($currency, ['TND', 'EUR', 'USD'])) {
            $request->getSession()->set('currency', $currency);
        }
        return $this->redirect($request->headers->get('referer', '/marketplace'));
    }

    #[Route('/mes-produits', name: 'app_marketplace_my_products', methods: ['GET'])]
    public function myProducts(ProductRepository $productRepository): Response
    {
        // sellerId=1 par défaut (sans authentification)
        $products = $productRepository->findBySellerId(1);

        return $this->render('product/my_products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'app_marketplace_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $product, $slugger);
            $product->setUser($entityManager->getReference(\App\Entity\User::class, 1)); // userId par dÃ©faut
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('app_marketplace_my_products', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_marketplace_product_show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function show(Request $request, Product $product, EntityManagerInterface $entityManager, RatingRepository $ratingRepository): Response
    {
        // Rating form
        $rating = new Rating();
        $ratingForm = $this->createForm(RatingType::class, $rating);
        $ratingForm->handleRequest($request);

        if ($ratingForm->isSubmitted() && $ratingForm->isValid()) {
            $rating->setProduct($product);
            $user = $this->getUser();
            if ($user instanceof User) {
                $rating->setUser($user);
                $rating->setUserId($user->getId());
            } else {
                $rating->setUserId(1);
            }
            $rating->setCreatedAt(new \DateTime());
            $entityManager->persist($rating);
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a été enregistré !');
            return $this->redirectToRoute('app_marketplace_product_show', ['id' => $product->getId()]);
        }

        $avgRating = $ratingRepository->getAverageRating($product->getId());
        $priceAnalysis = $ratingRepository->getPriceCategoryAnalysis($product->getId());

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'ratingForm' => $ratingForm->createView(),
            'avgRating' => $avgRating,
            'priceAnalysis' => $priceAnalysis,
        ]);
    }

    #[Route('/rating/{id}/delete', name: 'app_marketplace_rating_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteRating(Rating $rating, Request $request, EntityManagerInterface $entityManager): Response
    {
        $productId = $rating->getProduct()->getId();

        // Check CSRF token
        if (!$this->isCsrfTokenValid('delete' . $rating->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide');
            return $this->redirectToRoute('app_marketplace_product_show', ['id' => $productId]);
        }

        // Check if user owns this rating
        if ($rating->getUser() !== $this->getUser() && $rating->getUserId() !== $this->getUser()?->getId()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cet avis');
            return $this->redirectToRoute('app_marketplace_product_show', ['id' => $productId]);
        }

        $entityManager->remove($rating);
        $entityManager->flush();

        $this->addFlash('success', 'Votre avis a été supprimé');
        return $this->redirectToRoute('app_marketplace_product_show', ['id' => $productId]);
    }

    #[Route('/{id}/edit', name: 'app_marketplace_product_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $product, $slugger);
            $entityManager->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès !');
            return $this->redirectToRoute('app_marketplace_my_products', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_marketplace_product_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token') ?? $request->getPayload()->getString('_token'))) {
            // Supprimer l'image du disque
            if ($product->getImage()) {
                $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/products/' . $product->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé avec succès !');
        }

        return $this->redirectToRoute('app_marketplace_my_products', [], Response::HTTP_SEE_OTHER);
    }

    private function handleImageUpload($form, Product $product, SluggerInterface $slugger): void
    {
        $imageFile = $form->get('imageFile')->getData();
        if (!$imageFile) {
            return;
        }

        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';

        // Supprimer l'ancienne image si elle existe
        if ($product->getImage()) {
            $oldPath = $uploadDir . '/' . $product->getImage();
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        try {
            $imageFile->move($uploadDir, $newFilename);
            $product->setImage($newFilename);
        } catch (FileException $e) {
            $this->addFlash('error', "Erreur lors de l'upload de l'image.");
        }
    }

    // ===== BUNDLE ROUTES (handled by BundleController) =====

    // ===== SESSION CART ROUTES =====

    #[Route('/session-cart/add/{id}', name: 'app_marketplace_session_cart_add', methods: ['POST'])]
    public function addToSessionCart(Request $request, Product $product, SessionCartService $sessionCartService): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity <= 0 || $product->getQuantity() < $quantity) {
            $this->addFlash('error', 'Quantité invalide ou stock insuffisant');
        } else {
            $sessionCartService->addToCart($product->getId(), $product->getPrice(), $product->getName(), $quantity);
            $this->addFlash('success', "{$quantity} kg de '{$product->getName()}' ajoutés au panier!");
        }

        return $this->redirectToRoute('app_marketplace_index');
    }

    #[Route('/session-cart', name: 'app_marketplace_session_cart', methods: ['GET'])]
    public function sessionCart(SessionCartService $sessionCartService): Response
    {
        $cart = $sessionCartService->getCart();
        $total = $sessionCartService->getCartTotal();

        return $this->render('cart/session_cart.html.twig', [
            'cart' => $cart,
            'total' => $total,
            'cartCount' => $sessionCartService->getCartCount(),
        ]);
    }

    #[Route('/session-cart/remove/{id}', name: 'app_marketplace_session_cart_remove', methods: ['POST'])]
    public function removeFromSessionCart(Request $request, int $id, SessionCartService $sessionCartService): Response
    {
        if ($this->isCsrfTokenValid('remove' . $id, $request->request->get('_token'))) {
            $sessionCartService->removeFromCart($id);
            $this->addFlash('success', 'Article supprimé du panier');
        }

        return $this->redirectToRoute('app_marketplace_session_cart');
    }

    #[Route('/session-cart/update/{id}', name: 'app_marketplace_session_cart_update', methods: ['POST'])]
    public function updateSessionCart(Request $request, int $id, SessionCartService $sessionCartService): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $sessionCartService->updateQuantity($id, $quantity);
        
        return $this->redirectToRoute('app_marketplace_session_cart');
    }

    #[Route('/session-cart/add-bundle/{id}', name: 'app_marketplace_session_cart_add_bundle', methods: ['POST'])]
    public function addBundleToSessionCart(Request $request, ProductBundle $bundle, SessionCartService $sessionCartService): Response
    {
        $productIds = $bundle->getProducts()->map(fn($p) => $p->getId())->toArray();
        $sessionCartService->addBundleToCart(
            $bundle->getId(),
            $bundle->getName(),
            $bundle->getBundlePrice(),
            $productIds
        );
        $this->addFlash('success', "Bundle '{$bundle->getName()}' ajouté au panier!");

        return $this->redirectToRoute('app_marketplace_index');
    }

    #[Route('/session-cart/clear', name: 'app_marketplace_session_cart_clear', methods: ['POST'])]
    public function clearSessionCart(Request $request, SessionCartService $sessionCartService): Response
    {
        if ($this->isCsrfTokenValid('clear_cart', $request->request->get('_token'))) {
            $sessionCartService->clearCart();
            $this->addFlash('success', 'Panier vidé');
        }

        return $this->redirectToRoute('app_marketplace_session_cart');
    }
}

