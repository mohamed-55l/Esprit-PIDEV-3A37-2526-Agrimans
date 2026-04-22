<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoginController extends AbstractController
{
    #[Route('/', name: 'app_login')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        HttpClientInterface $client
    ): Response {
        $error = null;

        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');
            $password = $request->request->get('password');

            // ✅ reCAPTCHA token
            $recaptchaResponse = $request->request->get('g-recaptcha-response');

            if (!$recaptchaResponse) {
                $error = "Veuillez valider le reCAPTCHA.";
            } else {

                // 🔐 Vérification Google
                $response = $client->request(
                    'POST',
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'body' => [
                            'secret' => $_ENV['RECAPTCHA_SECRET_KEY'],
                            'response' => $recaptchaResponse,
                        ]
                    ]
                );

                $data = $response->toArray();

                if (!$data['success']) {
                    $error = "reCAPTCHA invalide.";
                } else {

                    // 🔍 chercher user
                    $user = $em->getRepository(User::class)->findOneBy([
                        'email' => $email
                    ]);

                    if (!$user) {
                        $error = "Email introuvable";
                    } else {

                        // 🔐 vérifier password
                        if (!password_verify($password, $user->getPasswordHash())) {
                            $error = "Mot de passe incorrect";
                        } else {

                            // ✅ SESSION
                            $session->set('user_id', $user->getId());
                            $session->set('user_name', $user->getFullName());
                            $session->set('user_role', $user->getRole()->value);

                            // 🚀 REDIRECTION
                            if ($user->getRole() === UserRole::ADMIN) {
                                return $this->redirectToRoute('admin_dashboard');
                            } else {
                                return $this->redirectToRoute('app_home');
                            }
                        }
                    }
                }
            }
        }

        return $this->render('login/Login.html.twig', [
            'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
            'error' => $error,
        ]);
    }

    #[Route('/home', name: 'app_home')]
    public function home(SessionInterface $session): Response
    {
        if (!$session->get('user_id')) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/Home.html.twig', [
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ]);
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function adminDashboard(SessionInterface $session, EntityManagerInterface $em): Response
    {
        if (!$session->get('user_id')) {
            return $this->redirectToRoute('app_login');
        }

        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé");
        }

        $users = $em->getRepository(User::class)->findAll();

        return $this->render('samir/admin/dashboard.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->invalidate();
        return $this->redirectToRoute('app_login');
    }
}