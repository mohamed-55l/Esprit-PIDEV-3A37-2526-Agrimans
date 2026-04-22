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
        $data = ['name' => '', 'email' => '', 'phone' => ''];

        if ($request->isMethod('GET')) {
            $session->remove('pending_user');
        }

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $data['name'] = trim($request->request->get('name'));
            $data['email'] = trim($request->request->get('email'));
            $data['phone'] = trim($request->request->get('phone'));
            
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');
            $otpCode = $request->request->get('otp');
            // Get the JSON descriptor from the hidden input
            $faceDescriptorRaw = $request->request->get('face_descriptor');

            if ($action === 'send_otp') {
                if (empty($data['email'])) {
                    $errors['email'] = "L'email est requis.";
                } else {
                    $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]); 
                    if ($existingUser) {
                        $errors['email'] = "Cet email est déjà utilisé.";
                    }
                }

                if ($password !== $confirm) {
                    $errors['confirm'] = "Les mots de passe ne correspondent pas.";
                }

                if (empty($errors)) {
                    $code = random_int(100000, 999999);
                    $otp = new EmailOtp();
                    $otp->setEmail($data['email']);
                    $otp->setCode((string)$code);
                    $otp->setExpiry(new \DateTime('+5 minutes'));

                    $em->persist($otp);
                    $em->flush();

                    $emailService->sendOtp($data['email'], (string)$code);
                    $this->addFlash('success', 'Code OTP envoyé.');

                    $session->set('pending_user', [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'password' => $password
                    ]);
                }
            }

            if ($action === 'register') {
                $pending = $session->get('pending_user');

                if (!$pending) {
                    $errors['otp'] = "Veuillez d'abord demander un code OTP.";
                } elseif (empty($faceDescriptorRaw)) {
                    // Force the user to scan their face
                    $errors['face'] = "La reconnaissance faciale est obligatoire pour s'inscrire.";
                } else {
                    $otp = $em->getRepository(EmailOtp::class)->findOneBy([
                        'email' => $pending['email'],
                        'code' => (string)$otpCode
                    ]);

                    if (!$otp || $otp->getExpiry() < new \DateTime()) {
                        $errors['otp'] = "Code OTP invalide ou expiré.";
                    } else {
                        $user = new User(); 
                        $user->setFullName($pending['name']);
                        $user->setEmail($pending['email']);
                        $user->setPhone($pending['phone']);
                        
                        $hashedPassword = $passwordHasher->hashPassword($user, $pending['password']);
                        $user->setPasswordHash($hashedPassword);
                        
                        // Save face descriptor as array
                        $user->setFaceDescriptor(json_decode($faceDescriptorRaw, true));
                        
                        $user->setRole(UserRole::USER);
                        $user->setCreatedAt(new \DateTimeImmutable());

                        $em->persist($user);
                        $em->remove($otp);
                        $em->flush();

                        $session->remove('pending_user');
                        $this->addFlash('success', 'Inscription réussie avec Face ID !');
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