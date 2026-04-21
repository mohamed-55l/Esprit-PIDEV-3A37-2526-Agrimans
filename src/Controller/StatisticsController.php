<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CartItemRepository;
use App\Repository\RatingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/statistics')]
final class StatisticsController extends AbstractController
{
    #[Route('', name: 'app_statistics_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        // Total products
        $totalProducts = count($productRepository->findAll());
        
        // Products by category
        $productsByCategory = $this->getProductsByCategory($productRepository);
        
        // Top rated products
        $topRatedProducts = $this->getTopRatedProducts($productRepository);
        
        // Low stock products
        $lowStockProducts = $this->getLowStockProducts($productRepository);
        
        // Average price by category
        $avgPriceByCategory = $this->getAveragePriceByCategory($productRepository);
        
        // Total value in stock
        $totalStockValue = $this->getTotalStockValue($productRepository);
        
        // Category statistics
        $categoryStats = $this->getCategoryStatistics($productRepository);

        return $this->render('statistics/index.html.twig', [
            'totalProducts' => $totalProducts,
            'productsByCategory' => $productsByCategory,
            'topRatedProducts' => $topRatedProducts,
            'lowStockProducts' => $lowStockProducts,
            'avgPriceByCategory' => $avgPriceByCategory,
            'totalStockValue' => $totalStockValue,
            'categoryStats' => $categoryStats,
        ]);
    }

    private function getProductsByCategory(ProductRepository $productRepository): array
    {
        $products = $productRepository->findAll();
        $categories = [];
        
        foreach ($products as $product) {
            $category = $product->getCategory() ?? 'Non catégorisé';
            $categories[$category] = ($categories[$category] ?? 0) + 1;
        }
        
        arsort($categories);
        return $categories;
    }

    private function getTopRatedProducts(ProductRepository $productRepository): array
    {
        $products = $productRepository->findAll();
        $ratedProducts = [];
        
        foreach ($products as $product) {
            if ($product->getAverageRating() > 0) {
                $ratedProducts[] = [
                    'product' => $product,
                    'rating' => $product->getAverageRating(),
                    'ratingCount' => count($product->getRatings()),
                ];
            }
        }
        
        usort($ratedProducts, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });
        
        return array_slice($ratedProducts, 0, 5);
    }

    private function getLowStockProducts(ProductRepository $productRepository): array
    {
        $products = $productRepository->findAll();
        $lowStock = [];
        
        foreach ($products as $product) {
            if ($product->getQuantity() < 50) { // Less than 50kg
                $lowStock[] = [
                    'product' => $product,
                    'quantity' => $product->getQuantity(),
                    'status' => $product->getQuantity() < 10 ? 'critical' : 'low',
                ];
            }
        }
        
        usort($lowStock, function($a, $b) {
            return $a['quantity'] <=> $b['quantity'];
        });
        
        return $lowStock;
    }

    private function getAveragePriceByCategory(ProductRepository $productRepository): array
    {
        $products = $productRepository->findAll();
        $categoryPrices = [];
        
        foreach ($products as $product) {
            $category = $product->getCategory() ?? 'Non catégorisé';
            if (!isset($categoryPrices[$category])) {
                $categoryPrices[$category] = ['sum' => 0, 'count' => 0];
            }
            $categoryPrices[$category]['sum'] += $product->getPrice();
            $categoryPrices[$category]['count']++;
        }
        
        $result = [];
        foreach ($categoryPrices as $category => $data) {
            if ($data['count'] > 0) {
                $result[$category] = round($data['sum'] / $data['count'], 2);
            }
        }
        
        arsort($result);
        return $result;
    }

    private function getTotalStockValue(ProductRepository $productRepository): float
    {
        $products = $productRepository->findAll();
        $total = 0;
        
        foreach ($products as $product) {
            $total += ($product->getPrice() * $product->getQuantity());
        }
        
        return round($total, 2);
    }

    private function getCategoryStatistics(ProductRepository $productRepository): array
    {
        $products = $productRepository->findAll();
        $stats = [];
        
        foreach ($products as $product) {
            $category = $product->getCategory() ?? 'Non catégorisé';
            if (!isset($stats[$category])) {
                $stats[$category] = [
                    'count' => 0,
                    'avgPrice' => 0,
                    'totalStock' => 0,
                    'avgRating' => 0,
                    'totalValue' => 0,
                ];
            }
            
            $stats[$category]['count']++;
            $stats[$category]['totalStock'] += $product->getQuantity();
            $stats[$category]['avgRating'] += $product->getAverageRating();
            $stats[$category]['totalValue'] += ($product->getPrice() * $product->getQuantity());
        }
        
        // Calculate averages
        foreach ($stats as $category => &$data) {
            $data['avgPrice'] = $data['count'] > 0 
                ? round($data['totalValue'] / $data['totalStock'], 2)
                : 0;
            $data['avgRating'] = $data['count'] > 0
                ? round($data['avgRating'] / $data['count'], 1)
                : 0;
            $data['totalValue'] = round($data['totalValue'], 2);
        }
        
        return $stats;
    }
}
