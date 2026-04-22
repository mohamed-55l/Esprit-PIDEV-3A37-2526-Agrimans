<?php

namespace App\Controller;

use App\Entity\User; 
use App\Entity\EmailOtp;
use App\Enum\UserRole;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        EmailService $emailService,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        $errors = [];
        // نبعثو الـ data ديما باش الـ Inputs ما يفرغوش
        $data = [
            'name' => $request->request->get('name', ''),
            'email' => $request->request->get('email', ''),
            'phone' => $request->request->get('phone', ''),
            'password' => $request->request->get('password', ''), // نرجعو الـ password
            'confirm' => $request->request->get('confirm', '')
        ];

        if ($request->isMethod('GET')) {
            $session->remove('pending_user');
        }

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $otpCode = $request->request->get('otp');
            $faceDescriptorRaw = $request->request->get('face_descriptor');

            // 1️⃣ مرحلة إرسال الـ OTP (مع الـ Validation المتقدمة)
            if ($action === 'send_otp') {
                // Email Validation
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "L'adresse email n'est pas valide.";
                }
                // Password Strength: 8 chars, 1 Uppercase, 1 Number
                if (strlen($data['password']) < 8 || !preg_match('/[A-Z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
                    $errors[] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.";
                }
                if ($data['password'] !== $data['confirm']) {
                    $errors[] = "Les mots de passe ne correspondent pas.";
                }
                // Tunisian Phone: Starts with 2,4,5,9 and has 8 digits
                if (!preg_match('/^[2459][0-9]{7}$/', $data['phone'])) {
                    $errors[] = "Numéro de téléphone tunisien invalide (8 chiffres requis).";
                }

                if (empty($errors)) {
                    $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]); 
                    if ($existingUser) {
                        $errors[] = "Cet email est déjà utilisé.";
                    } else {
                        $code = (string)random_int(100000, 999999);
                        $otp = new EmailOtp();
                        $otp->setEmail($data['email']);
                        $otp->setCode($code);
                        $otp->setExpiry(new \DateTime('+5 minutes'));

                        $em->persist($otp);
                        $em->flush();

                        $emailService->sendOtp($data['email'], $code);
                        $this->addFlash('success', 'Code OTP envoyé.');

                        // نسجلو المعطيات في الـ Session باش ما يضيعوش في الـ Reload الجاي
                        $session->set('pending_user', [
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'phone' => $data['phone'],
                            'password' => $data['password']
                        ]);
                    }
                }
            }

            // 2️⃣ مرحلة التسجيل النهائي
            if ($action === 'register') {
                $pending = $session->get('pending_user');

                if (!$pending) {
                    $errors[] = "Session expirée. Veuillez renvoyer le code OTP.";
                } elseif (empty($faceDescriptorRaw)) {
                    $errors[] = "Veuillez scanner votre visage avant de valider.";
                } else {
                    $otp = $em->getRepository(EmailOtp::class)->findOneBy([
                        'email' => $pending['email'],
                        'code' => $otpCode
                    ]);

                    if (!$otp || $otp->getExpiry() < new \DateTime()) {
                        $errors[] = "Code OTP invalide ou expiré.";
                    } else {
                        $user = new User(); 
                        $user->setFullName($pending['name']);
                        $user->setEmail($pending['email']);
                        $user->setPhone($pending['phone']);
                        $user->setPasswordHash($passwordHasher->hashPassword($user, $pending['password']));
                        $user->setFaceDescriptor(json_decode($faceDescriptorRaw, true));
                        $user->setRole(UserRole::USER);
                        $user->setCreatedAt(new \DateTimeImmutable());

                        $em->persist($user);
                        $em->remove($otp);
                        $em->flush();

                        $session->remove('pending_user');
                        $this->addFlash('success', 'Inscription réussie !');
                        return $this->redirectToRoute('app_login');
                    }
                }
            }
        }

        return $this->render('register/Register.html.twig', [
            'errors' => $errors,
            'data' => $data
        ]);
    }
}