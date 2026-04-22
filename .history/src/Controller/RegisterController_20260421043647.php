<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\EmailOtp;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {

        $errors = [];

        $data = [
            'name' => '',
            'email' => '',
            'phone' => ''
        ];

        // ⚠️ important
        $otpSent = $session->get('otp_sent', false);

        if ($request->isMethod('POST')) {

            $action = $request->request->get('action');

            // =========================
            // 1. ENVOYER OTP
            // =========================
            if ($action === 'send_otp') {

                $data['name'] = trim($request->request->get('name'));
                $data['email'] = trim($request->request->get('email'));
                $data['phone'] = trim($request->request->get('phone'));
                $password = $request->request->get('password');
                $confirm = $request->request->get('confirm');

                if (!$this->isCsrfTokenValid('register', $request->request->get('_token'))) {
                    $errors['csrf'] = "Requête invalide";
                }

                if (empty($data['name'])) $errors['name'] = "Nom requis";
                if (empty($data['email'])) $errors['email'] = "Email requis";
                if (empty($data['phone'])) $errors['phone'] = "Téléphone requis";

                if ($password !== $confirm) {
                    $errors['confirm'] = "Mots de passe différents";
                }

                $existing = $em->getRepository(User::class)->findOneBy([
                    'email' => $data['email']
                ]);

                if ($existing) {
                    $errors['email'] = "Email déjà utilisé";
                }

                if (empty($errors)) {

                    // save session
                    $session->set('pending_user', [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'password' => password_hash($password, PASSWORD_BCRYPT)
                    ]);

                    // OTP
                    $code = random_int(100000, 999999);

                    $otp = new EmailOtp();
                    $otp->setEmail($data['email']);
                    $otp->setCode((string)$code);
                    $otp->setExpiry(new \DateTime('+5 minutes'));

                    $em->persist($otp);
                    $em->flush();

                    // ⚠️ IMPORTANT
                    $session->set('otp_sent', true);
                    $otpSent = true; // 🔥 correction clé

                    dump("OTP: " . $code);
                }
            }

            // =========================
            // 2. VERIFY OTP
            // =========================
            if ($action === 'verify_otp') {

                $code = $request->request->get('otp');
                $pending = $session->get('pending_user');

                if (!$pending) {
                    return new Response("Session expirée");
                }

                $otp = $em->getRepository(EmailOtp::class)->findOneBy([
                    'email' => $pending['email'],
                    'code' => $code
                ]);

                if ($otp && $otp->getExpiry() > new \DateTimeImmutable()) {

                    $user = new User();
                    $user->setFullName($pending['name']);
                    $user->setEmail($pending['email']);
                    $user->setPhone($pending['phone']);
                    $user->setPasswordHash($pending['password']);
                    $user->setRole(UserRole::USER);
                    $user->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($user);
                    $em->remove($otp);
                    $em->flush();

                    $session->remove('pending_user');
                    $session->remove('otp_sent');

                    return $this->redirectToRoute('app_login');
                }

                $errors['otp'] = "Code OTP invalide ou expiré";
                $otpSent = true; // 🔥 garder affichage OTP si erreur
            }
        }

        return $this->render('register/Register.html.twig', [
            'errors' => $errors,
            'data' => $data,
            'otpSent' => $otpSent
        ]);
    }
}