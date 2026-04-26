<?php

namespace App\Controller;

use App\Entity\ProductBundle;
use App\Form\BundleType;
use App\Repository\ProductBundleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/bundles')]
#[IsGranted('ROLE_ADMIN')]
final class AdminBundleController extends AbstractController
{
    #[Route('/', name: 'app_admin_bundle_index', methods: ['GET'])]
    public function index(ProductBundleRepository $productBundleRepository): Response
    {
        return $this->render('admin_bundle/index.html.twig', [
            'product_bundles' => $productBundleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_bundle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $productBundle = new ProductBundle();
        $form = $this->createForm(BundleType::class, $productBundle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productBundle->calculateDiscount();
            $entityManager->persist($productBundle);
            $entityManager->flush();

            $this->addFlash('success', 'Pack créé avec succès.');
            return $this->redirectToRoute('app_admin_bundle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_bundle/new.html.twig', [
            'product_bundle' => $productBundle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_bundle_show', methods: ['GET'])]
    public function show(ProductBundle $productBundle): Response
    {
        return $this->render('admin_bundle/show.html.twig', [
            'product_bundle' => $productBundle,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_bundle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductBundle $productBundle, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BundleType::class, $productBundle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productBundle->calculateDiscount();
            $entityManager->flush();

            $this->addFlash('success', 'Pack mis à jour avec succès.');
            return $this->redirectToRoute('app_admin_bundle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_bundle/edit.html.twig', [
            'product_bundle' => $productBundle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_bundle_delete', methods: ['POST'])]
    public function delete(Request $request, ProductBundle $productBundle, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productBundle->getId(), $request->request->get('_token'))) {
            $entityManager->remove($productBundle);
            $entityManager->flush();
            $this->addFlash('success', 'Pack supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_bundle_index', [], Response::HTTP_SEE_OTHER);
    }
}
