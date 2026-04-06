<?php

namespace App\Modules\Marketplace\Controller;

use App\Modules\Marketplace\Entity\Product;
use App\Modules\Marketplace\Entity\Rating;
use App\Modules\Marketplace\Form\ProductType;
use App\Modules\Marketplace\Form\RatingType;
use App\Modules\Marketplace\Repository\ProductRepository;
use App\Modules\Marketplace\Repository\RatingRepository;
use App\Modules\Marketplace\Service\CartService;
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
    public function index(Request $request, ProductRepository $productRepository, CartService $cartService): Response
    {
        $search = $request->query->get('search', '');
        $category = $request->query->get('category', '');

        if ($search || $category) {
            $products = $productRepository->search($search, $category ?: null);
        } else {
            $products = $productRepository->findBy([], ['id' => 'DESC']);
        }

        return $this->render('Marketplace/product/index.html.twig', [
            'products' => $products,
            'search' => $search,
            'category' => $category,
            'cartCount' => $cartService->getCartItemCount(1), // userId=1 par défaut
        ]);
    }

    #[Route('/mes-produits', name: 'app_marketplace_my_products', methods: ['GET'])]
    public function myProducts(ProductRepository $productRepository): Response
    {
        // sellerId=1 par défaut (sans authentification)
        $products = $productRepository->findBySellerId(1);

        return $this->render('Marketplace/product/my_products.html.twig', [
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
            $product->setSellerId(1); // userId par défaut
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('app_marketplace_my_products', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Marketplace/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_marketplace_product_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Product $product, EntityManagerInterface $entityManager, RatingRepository $ratingRepository): Response
    {
        // Rating form
        $rating = new Rating();
        $ratingForm = $this->createForm(RatingType::class, $rating);
        $ratingForm->handleRequest($request);

        if ($ratingForm->isSubmitted() && $ratingForm->isValid()) {
            $rating->setProduct($product);
            $rating->setUserId(1); // userId par défaut
            $rating->setCreatedAt(new \DateTime());
            $entityManager->persist($rating);
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a été enregistré !');
            return $this->redirectToRoute('app_marketplace_product_show', ['id' => $product->getId()]);
        }

        $avgRating = $ratingRepository->getAverageRating($product->getId());
        $priceAnalysis = $ratingRepository->getPriceCategoryAnalysis($product->getId());

        return $this->render('Marketplace/product/show.html.twig', [
            'product' => $product,
            'ratingForm' => $ratingForm->createView(),
            'avgRating' => $avgRating,
            'priceAnalysis' => $priceAnalysis,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_marketplace_product_edit', methods: ['GET', 'POST'])]
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

        return $this->render('Marketplace/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_marketplace_product_delete', methods: ['POST'])]
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
}
