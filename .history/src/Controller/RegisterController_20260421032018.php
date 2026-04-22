<?php

namespace App\Controller;

use App\Entity\EmailOtp;
use App\Entity\User;
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

        if ($request->isMethod('POST')) {

            $data['name'] = trim($request->request->get('name'));
            $data['email'] = trim($request->request->get('email'));
            $data['phone'] = trim($request->request->get('phone'));

            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');

            // CSRF
            if (!$this->isCsrfTokenValid('register', $request->request->get('_token'))) {
                $errors['csrf'] = "Requête invalide";
            }

            // ================= VALIDATION =================

            if (empty($data['name'])) {
                $errors['name'] = "Nom requis";
            } elseif (strlen($data['name']) < 3) {
                $errors['name'] = "Minimum 3 caractères";
            }

            if (empty($data['email'])) {
                $errors['email'] = "Email requis";
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Email invalide";
            }

            if (!empty($data['phone']) && !preg_match("/^[0-9]{8}$/", $data['phone'])) {
                $errors['phone'] = "Numéro invalide (8 chiffres)";
            }

            // PASSWORD
            $passwordErrors = [];

            if (empty($password)) {
                $passwordErrors[] = "Mot de passe requis";
            } else {
                if (strlen($password) < 8) $passwordErrors[] = "Minimum 8 caractères";
                if (!preg_match('/[A-Z]/', $password)) $passwordErrors[] = "Ajouter une majuscule";
                if (!preg_match('/[a-z]/', $password)) $passwordErrors[] = "Ajouter une minuscule";
                if (!preg_match('/[0-9]/', $password)) $passwordErrors[] = "Ajouter un chiffre";
                if (!preg_match('/[\W]/', $password)) $passwordErrors[] = "Ajouter un symbole";
            }

            if (!empty($passwordErrors)) {
                $errors['password'] = $passwordErrors;
            }

            if ($password !== $confirm) {
                $errors['confirm'] = "Mot de passe incorrect";
            }

            // EMAIL UNIQUE
            $existingUser = $em->getRepository(User::class)->findOneBy([
                'email' => $data['email']
            ]);

            if ($existingUser) {
                $errors['email'] = "Email déjà utilisé";
            }

            // ================= OTP FLOW =================

            if (empty($errors)) {

                // 🔐 hash password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // 💾 stocker temporairement en session
                $session->set('pending_user', [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => $hashedPassword
                ]);

                // 🔢 générer OTP
                $code = rand(100000, 999999);

                $otp = new EmailOtp();
                $otp->setEmail($data['email']);
                $otp->setCode($code);
                $otp->setExpiry((new \DateTime())->modify('+5 minutes'));

                $em->persist($otp);
                $em->flush();

                // ⚠️ ici tu peux envoyer email (Mailer)
                dump("OTP CODE: " . $code); // debug temporaire

                return $this->redirectToRoute('app_verify_otp');
            }
        }

        return $this->render('register/Register.html.twig', [
            'errors' => $errors,
            'data' => $data
        ]);
    }
}