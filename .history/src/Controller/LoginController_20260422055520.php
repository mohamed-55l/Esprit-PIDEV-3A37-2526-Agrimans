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

        // 📝 معالجة الدخول الكلاسيكي (POST)
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $recaptchaResponse = $request->request->get('g-recaptcha-response');

            if (!$recaptchaResponse) {
                $error = "Veuillez valider le reCAPTCHA.";
            } else {
                // Verify reCAPTCHA with Google API
                $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                    'body' => [
                        'secret' => $_ENV['RECAPTCHA_SECRET_KEY'],
                        'response' => $recaptchaResponse,
                    ]
                ]);

                if (!$response->toArray()['success']) {
                    $error = "reCAPTCHA invalide.";
                } else {
                    $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

                    if (!$user || !password_verify($password, $user->getPasswordHash())) {
                        $error = "Email ou mot de passe incorrect.";
                    } else {
                        // ✅ النجاح: فتح الجلسة والتحويل
                        $this->setupUserSession($session, $user);
                        return $this->redirectByUserRole($user->getRole()->value);
                    }
                }
            }
        }

        return $this->render('login/Login.html.twig', [
            'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
            'error' => $error,
        ]);
    }

    /**
     * 🚀 Professional Face Login Logic
     */
    #[Route('/login-face', name: 'app_login_face', methods: ['POST'])]
    public function loginFace(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $data = json_decode($request->getContent(), true);
        $currentDescriptor = $data['descriptor'] ?? null;

        if (!$currentDescriptor) {
            return $this->json(['success' => false, 'message' => 'Flux vidéo corrompu ou visage non détecté']);
        }

        // لوج على المستخدمين اللي عندهم بصمة وجه مسجلة
        $users = $em->getRepository(User::class)->findAll();
        $bestMatch = null;
        
        // 💡 Threshold Pro: 0.6 هو الرقم العالمي المثالي لموديل SSD Mobilenet V1
        // إذا كان الرقم أقل من 0.6، يعني الشخص هو بيدو بنسبة كبيرة
        $threshold = 0.6; 
        $minDistance = 1.0;

        foreach ($users as $user) {
            $savedDescriptor = $user->getFaceDescriptor();
            if (!$savedDescriptor) continue;

            // حساب المسافة الإقليدية (Euclidean Distance)
            $distance = $this->calculateEuclideanDistance($currentDescriptor, $savedDescriptor);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $bestMatch = $user;
            }
        }

        // ✅ التثبت النهائي من المطابقة
        if ($bestMatch && $minDistance < $threshold) {
            $this->setupUserSession($session, $bestMatch);
            
            // نرجعو الـ Redirect URL كـ JSON باش الـ JavaScript يعمل الـ التحويل
            return $this->json([
                'success' => true,
                'message' => 'Identité confirmée ✅',
                'redirect' => $this->redirectByUserRole($bestMatch->getRole()->value)->getTargetUrl()
            ]);
        }

        return $this->json(['success' => false, 'message' => 'Utilisateur non reconnu.']);
    }

    private function setupUserSession(SessionInterface $session, User $user): void
    {
        $session->set('user_id', $user->getId());
        $session->set('user_name', $user->getFullName());
        $session->set('user_role', $user->getRole()->value);
    }

    private function redirectByUserRole(string $role): Response
    {
        return $role === 'ADMIN' ? $this->redirectToRoute('admin_dashboard') : $this->redirectToRoute('app_home');
    }

    /**
     * 📐 Math: $d = \sqrt{\sum (x_i - y_i)^2}$
     */
    private function calculateEuclideanDistance(array $arr1, array $arr2): float
    {
        $sum = 0;
        for ($i = 0; $i < count($arr1); $i++) {
            $sum += pow($arr1[$i] - $arr2[$i], 2);
        }
        return sqrt($sum);
    }

    #[Route('/home', name: 'app_home')]
    public function home(SessionInterface $session): Response {
        if (!$session->get('user_id')) return $this->redirectToRoute('app_login');
        return $this->render('home/index.html.twig', [
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response {
        $session->invalidate();
        return $this->redirectToRoute('app_login');
    }
}