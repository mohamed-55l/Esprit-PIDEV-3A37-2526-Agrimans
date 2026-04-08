<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();

        // Si l'utilisateur est connecté
        if ($user) {
            // Si c'est un admin, rediriger vers le dashboard
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('app_admin');
            }

            // Si c'est un utilisateur régulier, rediriger vers les gestions (animaux)
            return $this->redirectToRoute('waad_animal_index');
        }

        // Si pas connecté, afficher la page d'accueil avec les informations du site
        return $this->render('home/index.html.twig');
    }
}
