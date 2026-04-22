<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
Doctrine\ORM\EntityManagerInterface
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(SessionInterface $session, EntityManagerInterface $em): Response
    {
        if (!$session->get('user_id')) {
            return $this->redirectToRoute('app_login');
        }

        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé");
        }

        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users
        ]);
    }

    // ✅ DELETE USER
    #[Route('/user/delete/{id}', name: 'user_delete')]
    public function delete($id, EntityManagerInterface $em, SessionInterface $session)
    {
        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé");
        }

        $user = $em->getRepository(User::class)->find($id);

        if ($user) {
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    // ✅ UPDATE USER
    #[Route('/user/update/{id}', name: 'user_update')]
    public function update($id, Request $request, EntityManagerInterface $em, SessionInterface $session)
    {
        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé");
        }

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new Response("Utilisateur non trouvé");
        }

        $errors = [];

        if ($request->isMethod('POST')) {

            $name = $request->request->get('full_name');
            $email = $request->request->get('email');
            $phone = $request->request->get('phone');
            $role = $request->request->get('role');
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');

            // validation
            if (empty($name)) $errors['full_name'] = "Nom requis";
            if (empty($email)) $errors['email'] = "Email requis";

            if (!empty($password) && $password !== $confirm) {
                $errors['confirm'] = "Mot de passe incorrect";
            }

            if (empty($errors)) {

                $user->setFullName($name);
                $user->setEmail($email);
                $user->setPhone($phone);

                // ROLE
                if ($role === 'ADMIN') {
                    $user->setRole(UserRole::ADMIN);
                } else {
                    $user->setRole(UserRole::USER);
                }

                // PASSWORD (optionnel)
                if (!empty($password)) {
                    $user->setPasswordHash(
                        password_hash($password, PASSWORD_BCRYPT)
                    );
                }

                $em->flush();

                return $this->redirectToRoute('admin_dashboard');
            }
        }

        return $this->render('Samir/Update/updateuser.html.twig', [
            'user' => $user,
            'errors' => $errors
        ]);
    }
}
