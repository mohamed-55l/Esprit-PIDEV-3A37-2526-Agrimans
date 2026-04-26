<?php

namespace App\Controller;

use App\Entity\User; // ✅ 
use App\Enum\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(SessionInterface $session, EntityManagerInterface $em): Response
    {
        // 1. التثبت من الـ Session
        if (!$session->get('user_id')) {
            return $this->redirectToRoute('app_login');
        }

        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé", 403);
        }

        $users = $em->getRepository(User::class)->findAll(); 

        $stats = [
            'total_users' => count($users),
            'total_equipements' => 0, 
            'total_reviews' => 0,
            'total_products' => 0,
        ];

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'stats' => $stats
        ]);
    }

    // ✅ DELETE USER
    #[Route('/user/delete/{id}', name: 'user_delete')]
    public function delete($id, EntityManagerInterface $em, SessionInterface $session): Response
    {
        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé", 403);
        }

        $user = $em->getRepository(User::class)->find($id); // ✅ User بالمفرد

        if ($user) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    // ✅ UPDATE USER
    #[Route('/user/update/{id}', name: 'user_update')]
    public function update($id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé", 403);
        }

        $user = $em->getRepository(User::class)->find($id); // ✅ User بالمفرد

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur non trouvé");
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            $name = $request->request->get('full_name');
            $email = $request->request->get('email');
            $phone = $request->request->get('phone');
            $role = $request->request->get('role');
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');

            // Validation 
            if (empty($name)) $errors['full_name'] = "Nom requis";
            if (empty($email)) $errors['email'] = "Email requis";

            if (!empty($password) && $password !== $confirm) {
                $errors['confirm'] = "Les mots de passe لا تتطابق";
            }

            if (empty($errors)) {
                $user->setFullName($name);
                $user->setEmail($email);
                $user->setPhone($phone);

                //  الـ Role
                $user->setRole($role === 'ADMIN' ? UserRole::ADMIN : UserRole::USER);

                // تغيير كلمة السر إذا تم إدخالها
                if (!empty($password)) {
                    $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
                }

                $em->flush();
                $this->addFlash('success', 'Utilisateur mis à jour ✅');

                return $this->redirectToRoute('admin_dashboard');
            }
        }

        return $this->render('Update/updateuser.html.twig', [
            'user' => $user,
            'errors' => $errors
        ]);
    }
}