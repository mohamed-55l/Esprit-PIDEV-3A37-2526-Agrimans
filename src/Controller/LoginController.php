<?php

namespace App\Controller;

use App\Entity\Users;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        HttpClientInterface $client,
        TokenStorageInterface $tokenStorage
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
                    $user = $em->getRepository(Users::class)->findOneBy([
                        'email' => $email
                    ]);

                    if (!$user) {
                        $error = "Email introuvable";
                    } else {

                        // 🔐 vérifier password
                        if (!password_verify($password, $user->getPasswordHash())) {
                            $error = "Mot de passe incorrect";
                        } else {

                            // Handle role properly whether it's an Enum or a string
                            $role = $user->getRole();
                            $roleValue = $role instanceof \App\Enum\UserRole ? $role->value : $role;

                            // ✅ SESSION
                            $session->set('user_id', $user->getId());
                            $session->set('user_name', $user->getFullName());
                            $session->set('user_role', $roleValue);

                            // Authentification Symfony (pour rendre #[IsGranted] et $this->getUser() fonctionnels)
                            $token = new PostAuthenticationToken($user, 'main', $user->getRoles());
                            $tokenStorage->setToken($token);
                            $session->set('_security_main', serialize($token));

                            // 🚀 REDIRECTION
                            if ($roleValue === 'ADMIN') {
                                return $this->redirectToRoute('admin_dashboard');
                            } else {
                                return $this->redirectToRoute('app_user_dashboard');
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

    #[Route('/', name: 'app_home')]
    public function home(SessionInterface $session): Response
    {
        // On permet l'accès à la page d'accueil sans être connecté


        return $this->render('home/index.html.twig', [
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session, TokenStorageInterface $tokenStorage): Response
    {
        $tokenStorage->setToken(null);
        $session->invalidate();
        return $this->redirectToRoute('app_login');
    }
}