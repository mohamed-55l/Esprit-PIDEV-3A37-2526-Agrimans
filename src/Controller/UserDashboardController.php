<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserDashboardController extends AbstractController
{
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(): Response
    {
        // Dans le futur, nous pourrons injecter les Repositories ici 
        // pour rÃ©cupÃ©rer les vraies statistiques (par ex: nb de parcelles, nb d'animaux)
        
        // Statistiques globales fictives / initialisation
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
}
