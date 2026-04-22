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
            $recaptchaResponse = $request->request->get('g-recaptcha-response');

            // Logic classique (Fallback)
            if (!$recaptchaResponse) {
                $error = "Veuillez valider le reCAPTCHA.";
            } else {
                $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                if (!$user || !password_verify($password, $user->getPasswordHash())) {
                    $error = "Identifiants incorrects";
                } else {
                    $this->setupSession($session, $user);
                    return $this->redirectByUserRole($user);
                }
            }
        }

        return $this->render('login/Login.html.twig', [
            'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
            'error' => $error,
        ]);
    }

    #[Route('/login-face', name: 'app_login_face', methods: ['POST'])]
    public function loginFace(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $data = json_decode($request->getContent(), true);
        $currentDescriptor = $data['descriptor'] ?? null;

        if (!$currentDescriptor) {
            return $this->json(['success' => false, 'message' => 'Aucun visage détecté.']);
        }

        // 1. نجيبو المستخدمين الكل اللي عندهم بصمة وجه مسجلة
        $users = $em->getRepository(User::class)->findAll();
        $bestMatch = null;
        $threshold = 0.6; // دقة المقارنة: كل ما كان أصغر كل ما كان أصعب (أكثر دقة)
        $minDistance = 1.0;

        foreach ($users as $user) {
            $savedDescriptor = $user->getFaceDescriptor();
            if (!$savedDescriptor) continue;

            // حساب المسافة الإقليدية (Euclidean Distance)
            $distance = $this->euclideanDistance($currentDescriptor, $savedDescriptor);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $bestMatch = $user;
            }
        }

        // 2. إذا لقينا مستخدم المسافة بين وجهو ووجه الـ Scan أقل من 0.6
        if ($bestMatch && $minDistance < $threshold) {
            $this->setupSession($session, $bestMatch);
            return $this->json([
                'success' => true, 
                'redirect' => $this->redirectByUserRole($bestMatch)->getTargetUrl()
            ]);
        }

        return $this->json(['success' => false, 'message' => 'Visage non reconnu.']);
    }

    private function euclideanDistance(array $arr1, array $arr2): float
    {
        $sum = 0;
        for ($i = 0; $i < count($arr1); $i++) {
            $sum += pow($arr1[$i] - $arr2[$i], 2);
        }
        return sqrt($sum);
    }

    private function setupSession(SessionInterface $session, User $user): void
    {
        $session->set('user_id', $user->getId());
        $session->set('user_name', $user->getFullName());
        $session->set('user_role', $user->getRole()->value);
    }

    private function redirectByUserRole(User $user): Response
    {
        if ($user->getRole() === UserRole::ADMIN) {
            return $this->redirectToRoute('admin_dashboard');
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->invalidate();
        return $this->redirectToRoute('app_login');
    }
}