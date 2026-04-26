<?php

namespace App\Controller;

use App\Entity\User; // ✅ 
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CultureRepository;
use App\Repository\ParcelleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            'stats' => $stats,
            'users' => $users,
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

    #[Route('/user/update/{id}', name: 'user_update')]
<<<<<<< HEAD
    public function update($id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé", 403);
        }

        $user = $em->getRepository(User::class)->find($id); // ✅ User بالمفرد
=======
    public function updateUser($id, Request $request, EntityManagerInterface $em)
    {
        $user = $em->getRepository(Users::class)->find($id);
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e

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

<<<<<<< HEAD
            // Validation 
=======
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e
            if (empty($name)) $errors['full_name'] = "Nom requis";
            if (empty($email)) $errors['email'] = "Email requis";

            if (!empty($password) && $password !== $confirm) {
                $errors['confirm'] = "Les mots de passe لا تتطابق";
            }

            if (empty($errors)) {
                $user->setFullName($name);
                $user->setEmail($email);
                $user->setPhone($phone);

<<<<<<< HEAD
                //   Role
                $user->setRole($role === 'ADMIN' ? UserRole::ADMIN : UserRole::USER);
=======
                if ($role === 'ADMIN') {
                    $user->setRole(UserRole::ADMIN);
                } else {
                    $user->setRole(UserRole::USER);
                }
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e

                if (!empty($password)) {
                    $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
                }

                $em->flush();
<<<<<<< HEAD
                $this->addFlash('success', 'Utilisateur mis à jour ✅');

=======
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e
                return $this->redirectToRoute('admin_dashboard');
            }
        }

        return $this->render('Update/updateuser.html.twig', [
            'user' => $user,
            'errors' => $errors
        ]);
    }
<<<<<<< HEAD
}
=======

    #[Route('/view-parcelles-cultures', name: 'app_admin_view_parcelles_cultures')]
    public function viewParcellesCultures(Request $request, ParcelleRepository $parcelleRepository, CultureRepository $cultureRepository, \Knp\Component\Pager\PaginatorInterface $paginator): Response
    {
        $parcellesQuery = $parcelleRepository->findAll();
        $culturesQuery = $cultureRepository->findAll();

        $parcelles = $paginator->paginate(
            $parcellesQuery,
            $request->query->getInt('page_p', 1),
            5,
            ['pageParameterName' => 'page_p']
        );

        $cultures = $paginator->paginate(
            $culturesQuery,
            $request->query->getInt('page_c', 1),
            5,
            ['pageParameterName' => 'page_c']
        );

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'view_mode' => 'parcelles_cultures',
            'parcelles' => $parcelles,
            'cultures' => $cultures,
        ]);
    }
}
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e
