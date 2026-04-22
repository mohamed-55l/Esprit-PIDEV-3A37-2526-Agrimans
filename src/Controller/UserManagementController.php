<?php

namespace App\Controller;

use App\Entity\Users;
use App\Enum\UserRole;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────────────
    // LISTE DES UTILISATEURS
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/', name: 'app_user_management', methods: ['GET'])]
    public function index(UsersRepository $usersRepository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $usersRepository->findAll(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOGGLE RÔLE ADMIN / USER
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/{id}/toggle-role', name: 'app_user_toggle_role', methods: ['POST'])]
    public function toggleRole(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(Users::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_user_management');
        }

        if (!$this->isCsrfTokenValid('toggle_role_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_user_management');
        }

        // Ne pas modifier son propre rôle
        if ($this->getUser() && $this->getUser()->getUserIdentifier() === $user->getUserIdentifier()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier votre propre rôle.');
            return $this->redirectToRoute('app_user_management');
        }

        if ($user->getRole() === UserRole::ADMIN) {
            $user->setRole(UserRole::USER);
            $this->addFlash('success', $user->getFullName() . ' est maintenant Utilisateur.');
        } else {
            $user->setRole(UserRole::ADMIN);
            $this->addFlash('success', $user->getFullName() . ' est maintenant Administrateur.');
        }

        $em->flush();

        return $this->redirectToRoute('app_user_management');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CRÉER UN ADMINISTRATEUR
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/create-admin', name: 'app_create_admin', methods: ['GET', 'POST'])]
    public function createAdmin(Request $request, EntityManagerInterface $em, UsersRepository $usersRepository): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $fullName = trim($request->request->get('full_name', ''));
            $email    = trim($request->request->get('email', ''));
            $phone    = trim($request->request->get('phone', ''));
            $password = $request->request->get('password', '');
            $confirm  = $request->request->get('confirm', '');

            if (empty($fullName)) $errors['full_name'] = 'Le nom complet est requis.';
            if (empty($email))    $errors['email']     = 'L\'email est requis.';
            if (empty($password)) $errors['password']  = 'Le mot de passe est requis.';
            if ($password !== $confirm) $errors['confirm'] = 'Les mots de passe ne correspondent pas.';

            if (empty($errors)) {
                if ($usersRepository->findOneBy(['email' => $email])) {
                    $errors['email'] = 'Un compte avec cet email existe déjà.';
                } else {
                    $user = new Users();
                    $user->setFullName($fullName);
                    $user->setEmail($email);
                    $user->setPhone($phone ?: '00000000');
                    $user->setRole(UserRole::ADMIN);
                    $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
                    $user->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Administrateur ' . $fullName . ' créé avec succès.');
                    return $this->redirectToRoute('app_user_management');
                }
            }
        }

        return $this->render('admin/users/create_admin.html.twig', ['errors' => $errors]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CRÉER UN UTILISATEUR
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/create-user', name: 'app_create_user', methods: ['GET', 'POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, UsersRepository $usersRepository): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $fullName = trim($request->request->get('full_name', ''));
            $email    = trim($request->request->get('email', ''));
            $phone    = trim($request->request->get('phone', ''));
            $password = $request->request->get('password', '');
            $confirm  = $request->request->get('confirm', '');

            if (empty($fullName)) $errors['full_name'] = 'Le nom complet est requis.';
            if (empty($email))    $errors['email']     = 'L\'email est requis.';
            if (empty($password)) $errors['password']  = 'Le mot de passe est requis.';
            if ($password !== $confirm) $errors['confirm'] = 'Les mots de passe ne correspondent pas.';

            if (empty($errors)) {
                if ($usersRepository->findOneBy(['email' => $email])) {
                    $errors['email'] = 'Un compte avec cet email existe déjà.';
                } else {
                    $user = new Users();
                    $user->setFullName($fullName);
                    $user->setEmail($email);
                    $user->setPhone($phone ?: '00000000');
                    $user->setRole(UserRole::USER);
                    $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
                    $user->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Utilisateur ' . $fullName . ' créé avec succès.');
                    return $this->redirectToRoute('app_user_management');
                }
            }
        }

        return $this->render('admin/users/create_user.html.twig', ['errors' => $errors]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SUPPRIMER UN UTILISATEUR
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(Users::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_user_management');
        }

        // Ne pas supprimer son propre compte
        if ($this->getUser() && $this->getUser()->getUserIdentifier() === $user->getUserIdentifier()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_user_management');
        }

        if ($this->isCsrfTokenValid('delete_user_' . $id, $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_user_management');
    }
}
