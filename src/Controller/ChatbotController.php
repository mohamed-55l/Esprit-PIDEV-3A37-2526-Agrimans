<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketplace/chatbot')]
final class ChatbotController extends AbstractController
{
    #[Route('', name: 'app_chatbot_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }

    #[Route('/message', name: 'app_chatbot_message', methods: ['POST'])]
    public function sendMessage(Request $request, ChatbotService $chatbotService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            return $this->json([
                'success' => false,
                'error' => 'Message vide'
            ]);
        }

        // Limit message length
        if (strlen($message) > 200) {
            $message = substr($message, 0, 200);
        }

        try {
            $result = $chatbotService->processQuery($message);

            return $this->json([
                'success' => true,
                'response' => $result['response'],
                'products' => array_map(fn($p) => [
                    'id' => $p->getId(),
                    'name' => $p->getName(),
                    'price' => $p->getPrice(),
                    'category' => $p->getCategory(),
                    'quantity' => $p->getQuantity(),
                    'supplier' => $p->getSupplier(),
                    'rating' => $p->getAverageRating(),
                ], $result['products']),
                'type' => $result['type']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur du serveur'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
