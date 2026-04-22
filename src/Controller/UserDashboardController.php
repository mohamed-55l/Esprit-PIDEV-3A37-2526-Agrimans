<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\EquipementRepository;
use App\Repository\GarageRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[IsGranted('ROLE_USER')]
class UserDashboardController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD PRINCIPAL
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(): Response
    {
        $stats = [
            'total_parcelles' => 0,
            'total_cultures' => 0,
            'total_animaux' => 0,
            'commandes_en_cours' => 0,
            'alertes' => 0,
        ];

        return $this->render('user/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MES ÉQUIPEMENTS (équipements assignés à l'utilisateur connecté)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/user/equipements', name: 'user_equipement_index', methods: ['GET'])]
    public function mesEquipements(Request $request, EquipementRepository $equipementRepository, GarageRepository $garageRepository, ChartBuilderInterface $chartBuilder): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        $query = $request->query->get('q', '');
        $equipements = $equipementRepository->findByUser($user, $query);

        // Chart Equipements
        $dispoCount = ['Disponible' => 0, 'Indisponible' => 0, 'En Panne' => 0];
        foreach ($equipements as $eq) {
            $dispo = ucfirst(strtolower(trim($eq->getDisponibilite() ?? '')));
            if (isset($dispoCount[$dispo])) {
                $dispoCount[$dispo]++;
            } else {
                if (!isset($dispoCount['Autre'])) $dispoCount['Autre'] = 0;
                $dispoCount['Autre']++;
            }
        }

        $chartEquipement = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chartEquipement->setData([
            'labels' => array_keys($dispoCount),
            'datasets' => [
                [
                    'label' => 'Mes Équipements',
                    'backgroundColor' => ['#2ecc71', '#e74c3c', '#f1c40f', '#95a5a6'],
                    'borderColor' => '#1e2529',
                    'data' => array_values($dispoCount),
                ],
            ],
        ]);
        $chartEquipement->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['color' => '#fff']]]
        ]);

        return $this->render('user/equipements/index.html.twig', [
            'equipements' => $equipements,
            'currentQuery' => $query,
            'chartEquipement' => $chartEquipement,
            'garages' => $garageRepository->findAll(),
        ]);
    }

    #[Route('/user/equipements/request', name: 'user_equipement_request', methods: ['GET', 'POST'])]
    public function requestEquipement(Request $request, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $equipementName = $request->request->get('equipement_name');
            $messageContent = $request->request->get('message');
            /** @var \App\Entity\Users $user */
            $user = $this->getUser();

            if ($equipementName && $messageContent) {
                $email = (new Email())
                    ->from('zidisamir993@gmail.com')
                    ->to('d.moham2004@gmail.com') // Email de l'admin demandé
                    ->subject('Demande d\'équipement : ' . $equipementName)
                    ->html(
                        "<h2>Nouvelle demande d'équipement</h2>" .
                        "<p><strong>Utilisateur :</strong> " . $user->getFullName() . "</p>" .
                        "<p><strong>Téléphone :</strong> " . $user->getPhone() . "</p>" .
                        "<p><strong>Email :</strong> " . $user->getEmail() . "</p>" .
                        "<p><strong>Équipement demandé :</strong> " . htmlspecialchars($equipementName) . "</p>" .
                        "<p><strong>Message :</strong></p>" .
                        "<blockquote>" . nl2br(htmlspecialchars($messageContent)) . "</blockquote>"
                    );

                $mailer->send($email);

                $this->addFlash('success', 'Votre demande a bien été envoyée à l\'administrateur.');
                return $this->redirectToRoute('user_equipement_index');
            } else {
                $this->addFlash('danger', 'Veuillez remplir tous les champs.');
            }
        }

        return $this->render('user/equipements/request.html.twig');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CHATBOT ASSISTANT AGRICOLE (AJAX)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/user/chatbot/ask', name: 'user_chatbot_ask', methods: ['POST'])]
    public function chatbotAsk(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = strtolower($data['message'] ?? '');

        // Logique de réponse basée sur des mots-clés (Rule-based AI)
        $response = "Désolé, je n'ai pas bien compris votre question. Je suis un assistant spécialisé dans les équipements agricoles et les capteurs. Posez-moi une question sur un tracteur, un drone, l'irrigation ou les capteurs IoT !";

        if (str_contains($message, 'bonjour') || str_contains($message, 'salut') || str_contains($message, 'hello')) {
            $response = "Bonjour ! 👋 Je suis votre assistant virtuel Agrimans. Comment puis-je vous aider aujourd'hui avec vos équipements agricoles ?";
        } elseif (str_contains($message, 'tracteur')) {
            $response = "🚜 **Tracteur Agricole** : C'est le véhicule central de l'exploitation. Il sert à tirer des outils agricoles (charrues, semoirs). L'entretien régulier (huile, filtres, pneus) est crucial. Pour un tracteur moderne, on utilise souvent le guidage GPS pour une précision optimale.";
        } elseif (str_contains($message, 'moissonneuse') || str_contains($message, 'batteuse')) {
            $response = "🌾 **Moissonneuse-batteuse** : Une machine complexe utilisée pour la récolte des céréales (blé, orge, maïs). Elle fauche, bat les épis et nettoie le grain en une seule opération. Assurez-vous de bien nettoyer la trémie après chaque utilisation.";
        } elseif (str_contains($message, 'capteur') || str_contains($message, 'iot') || str_contains($message, 'humidité')) {
            $response = "📡 **Capteurs IoT (Internet des Objets)** : Dans l'agriculture de précision, les capteurs connectés mesurent l'humidité du sol, la température ou la météo en temps réel. Ces données aident à optimiser l'irrigation et à prévenir les maladies des plantes.";
        } elseif (str_contains($message, 'irrigation') || str_contains($message, 'eau') || str_contains($message, 'arrosage')) {
            $response = "💧 **Systèmes d'irrigation** : Les systèmes modernes (goutte-à-goutte ou pivots) permettent d'économiser l'eau. Couplés à des capteurs d'humidité, ils s'activent uniquement quand la plante en a besoin, ce qui réduit le gaspillage et augmente le rendement.";
        } elseif (str_contains($message, 'drone')) {
            $response = "🚁 **Drones Agricoles** : Les drones sont utilisés pour survoler les parcelles. Équipés de caméras multispectrales, ils détectent le stress hydrique des plantes, la présence de mauvaises herbes, ou estiment les rendements sans avoir à marcher dans le champ.";
        } elseif (str_contains($message, 'panne') || str_contains($message, 'réparation')) {
            $response = "🔧 **Gestion des Pannes** : Si un de vos équipements est en panne, n'hésitez pas à utiliser le bouton 'Demander un équipement' pour signaler le problème à l'administration, ou vérifiez s'il y a un garage partenaire sur notre plateforme.";
        } elseif (str_contains($message, 'merci')) {
            $response = "Avec grand plaisir ! N'hésitez pas si vous avez d'autres questions. 😊";
        }

        // Simuler un temps de réflexion (pour faire plus "humain")
        usleep(500000); // 0.5 secondes

        return $this->json(['reply' => $response]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MES REVIEWS
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/user/reviews', name: 'user_review_index', methods: ['GET'])]
    public function mesReviews(Request $request, ReviewRepository $reviewRepository, ChartBuilderInterface $chartBuilder): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        $query = $request->query->get('q', '');
        $reviews = $reviewRepository->findByUser($user, $query);

        // Chart Reviews
        $notesCount = ['1 Étoile' => 0, '2 Étoiles' => 0, '3 Étoiles' => 0, '4 Étoiles' => 0, '5 Étoiles' => 0];
        foreach ($reviews as $rev) {
            $note = $rev->getNote();
            if ($note >= 1 && $note <= 5) {
                $notesCount[$note . ' Étoile' . ($note > 1 ? 's' : '')]++;
            }
        }

        $chartReview = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartReview->setData([
            'labels' => array_keys($notesCount),
            'datasets' => [
                [
                    'label' => 'Mes Avis',
                    'backgroundColor' => '#f1c40f',
                    'data' => array_values($notesCount),
                ],
            ],
        ]);
        $chartReview->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['color' => '#fff']],
                'x' => ['ticks' => ['color' => '#fff']]
            ],
            'plugins' => ['legend' => ['display' => false]]
        ]);

        return $this->render('user/reviews/index.html.twig', [
            'reviews' => $reviews,
            'currentQuery' => $query,
            'chartReview' => $chartReview,
        ]);
    }

    #[Route('/user/reviews/new', name: 'user_review_new', methods: ['GET', 'POST'])]
    public function newReview(
        Request $request,
        EntityManagerInterface $em,
        EquipementRepository $equipementRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $review = new Review();
        $review->setUser($user);
        $review->setDateReview(new \DateTime());

        // Pré-sélectionner l'équipement si eq_id est passé en paramètre
        $eqId = $request->query->get('eq_id');
        $equipement = null;
        if ($eqId) {
            $equipement = $equipementRepository->find($eqId);
            // Sécurité : l'équipement doit appartenir à l'utilisateur connecté
            if ($equipement && $equipement->getUser() !== $user) {
                throw $this->createAccessDeniedException('Cet équipement ne vous appartient pas.');
            }
            if ($equipement) {
                $review->setEquipement($equipement);
            }
        }

        $form = $this->createForm(ReviewType::class, $review, [
            'user_equipements' => $equipementRepository->findByUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();
            $this->addFlash('success', 'Votre avis a été enregistré.');
            return $this->redirectToRoute('user_review_index');
        }

        return $this->render('user/reviews/new.html.twig', [
            'form' => $form->createView(),
            'equipement' => $equipement,
        ]);
    }

    #[Route('/user/reviews/{id}/edit', name: 'user_review_edit', methods: ['GET', 'POST'])]
    public function editReview(
        Request $request,
        Review $review,
        EntityManagerInterface $em,
        EquipementRepository $equipementRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Sécurité : seul l'auteur peut modifier
        if ($review->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres avis.');
        }

        $form = $this->createForm(ReviewType::class, $review, [
            'user_equipements' => $equipementRepository->findByUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Votre avis a été modifié.');
            return $this->redirectToRoute('user_review_index');
        }

        return $this->render('user/reviews/edit.html.twig', [
            'form' => $form->createView(),
            'review' => $review,
        ]);
    }

    #[Route('/user/reviews/{id}/delete', name: 'user_review_delete', methods: ['POST'])]
    public function deleteReview(
        Request $request,
        Review $review,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Sécurité : seul l'auteur peut supprimer
        if ($review->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres avis.');
        }

        if ($this->isCsrfTokenValid('delete_rev_' . $review->getId(), $request->request->get('_token'))) {
            $em->remove($review);
            $em->flush();
            $this->addFlash('success', 'Votre avis a été supprimé.');
        }

        return $this->redirectToRoute('user_review_index');
    }
}
