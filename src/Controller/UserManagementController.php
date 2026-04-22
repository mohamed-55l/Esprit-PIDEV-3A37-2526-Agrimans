<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    #[Route('/', name: 'app_user_management', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}/toggle-role', name: 'app_user_toggle_role', methods: ['POST'])]
    public function toggleRole(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            $user->setRole('USER');
        } else {
            $user->setRole('ADMIN');
        }

        $entityManager->flush();

        $this->addFlash('success', 'Rôle de l\'utilisateur mis à jour.');

        return $this->redirectToRoute('app_user_management');
    }

    #[Route('/create-admin', name: 'app_create_admin', methods: ['GET', 'POST'])]
    public function createAdmin(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $email    = $request->request->get('email');
            $password = $request->request->get('password');
            $fullName = $request->request->get('full_name', 'Admin');

            if ($email && $password) {
                $existingUser = $userRepository->findOneBy(['email' => $email]);
                if ($existingUser) {
                    $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
                    return $this->redirectToRoute('app_create_admin');
                }

                $user = new User();
                $user->setEmail($email);
                $user->setFullName($fullName);
                $user->setRole('ADMIN');
                $user->setCreatedAt(new \DateTime());
                $user->setPasswordHash($passwordHasher->hashPassword($user, $password));

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Administrateur créé avec succès.');
                return $this->redirectToRoute('app_user_management');
            }
        }

        return $this->render('admin/users/create_admin.html.twig');
    }

    #[Route('/create-user', name: 'app_create_user', methods: ['GET', 'POST'])]
    public function createUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $email    = $request->request->get('email');
            $password = $request->request->get('password');
            $fullName = $request->request->get('full_name', 'Utilisateur');
            $phone    = $request->request->get('phone', '');

            if ($email && $password) {
                $existingUser = $userRepository->findOneBy(['email' => $email]);
                if ($existingUser) {
                    $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
                    return $this->redirectToRoute('app_create_user');
                }

                $user = new User();
                $user->setEmail($email);
                $user->setFullName($fullName);
                $user->setRole('USER');
                $user->setCreatedAt(new \DateTime());
                if ($phone) {
                    $user->setPhone($phone);
                }
                $user->setPasswordHash($passwordHasher->hashPassword($user, $password));

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès.');
                return $this->redirectToRoute('app_user_management');
            }
        }

        return $this->render('admin/users/create_user.html.twig');
    }

    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimÃ©.');
        }

        return $this->redirectToRoute('app_user_management');
    }
}
