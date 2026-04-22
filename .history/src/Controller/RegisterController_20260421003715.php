<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $errors = [];

        // ✅ garder valeurs
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

            // 🔐 CSRF
            if (!$this->isCsrfTokenValid('register', $request->request->get('_token'))) {
                $errors['csrf'] = "Requête invalide";
            }

            // ================= VALIDATION =================

            // NOM
            if (empty($data['name'])) {
                $errors['name'] = "Nom requis";
            } elseif (strlen($data['name']) < 3) {
                $errors['name'] = "Minimum 3 caractères";
            }

            // EMAIL
            if (empty($data['email'])) {
                $errors['email'] = "Email requis";
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Email invalide";
            }

            // PHONE
            if (!empty($data['phone']) && !preg_match("/^[0-9]{8}$/", $data['phone'])) {
                $errors['phone'] = "Numéro invalide (8 chiffres)";
            }

            // PASSWORD (🔥 détaillé)
            $passwordErrors = [];

            if (empty($password)) {
                $passwordErrors[] = "Mot de passe requis";
            } else {

                if (strlen($password) < 8) {
                    $passwordErrors[] = "Minimum 8 caractères";
                }

                if (!preg_match('/[A-Z]/', $password)) {
                    $passwordErrors[] = "Ajouter une majuscule";
                }

                if (!preg_match('/[a-z]/', $password)) {
                    $passwordErrors[] = "Ajouter une minuscule";
                }

                if (!preg_match('/[0-9]/', $password)) {
                    $passwordErrors[] = "Ajouter un chiffre";
                }

                if (!preg_match('/[\W]/', $password)) {
                    $passwordErrors[] = "Ajouter un symbole (!@#$...)";
                }
            }

            if (!empty($passwordErrors)) {
                $errors['password'] = $passwordErrors;
            }

            // CONFIRM
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

            // ================= INSERT =================

            if (empty($errors)) {

                $user = new User();
                $user->setFullName($data['name']);
                $user->setEmail($data['email']);
                $user->setPhone($data['phone']);
                $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
                $user->setRole(UserRole::USER);
                $user->setCreatedAt(new \DateTimeImmutable());

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Compte créé avec succès');

                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('samir/register/Register.html.twig', [
            'errors' => $errors,
            'data' => $data
        ]);
    }
}