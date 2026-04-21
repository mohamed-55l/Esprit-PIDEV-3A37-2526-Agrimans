<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AnimalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserDashboardController extends AbstractController
{
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(AnimalRepository $animalRepository): Response
    {
        $user = $this->getUser();
        $uid = $user instanceof User ? $user->getId() : null;

        $stats = [
            'total_parcelles' => 0,
            'total_cultures' => 0,
            'total_animaux' => $animalRepository->countActive($uid),
            'commandes_en_cours' => 0,
            'alertes' => 0,
        ];

        return $this->render('user/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }
}
