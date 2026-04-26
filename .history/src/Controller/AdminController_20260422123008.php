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
<<<<<<< HEAD
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
=======
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD PRINCIPAL
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('', name: 'admin_dashboard')]
    public function index(
        EquipementRepository $equipementRepository,
        ReviewRepository $reviewRepository,
        UsersRepository $userRepository
    ): Response {
        $users = $userRepository->findAll();

        $stats = [
            'total_equipements' => $equipementRepository->count([]),
            'total_reviews'     => $reviewRepository->count([]),
            'total_users'       => count($users),
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e
        ];

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'users' => $users,
        ]);
    }

<<<<<<< HEAD
    // ✅ DELETE USER
    #[Route('/user/delete/{id}', name: 'user_delete')]
    public function delete($id, EntityManagerInterface $em, SessionInterface $session): Response
    {
        if ($session->get('user_role') !== 'ADMIN') {
            return new Response("Accès refusé", 403);
        }

        $user = $em->getRepository(User::class)->find($id); // ✅ User بالمفرد
=======
    // ─────────────────────────────────────────────────────────────────────────
    // CRUD ÉQUIPEMENTS (Admin)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/equipements', name: 'admin_equipement_index', methods: ['GET'])]
    public function equipementIndex(EquipementRepository $equipementRepository): Response
    {
        return $this->render('admin/equipements/index.html.twig', [
            'equipements' => $equipementRepository->findAllWithUser(),
            'statistics'  => $equipementRepository->getStatistics(),
        ]);
    }

    #[Route('/equipements/new', name: 'admin_equipement_new', methods: ['GET', 'POST'])]
    public function equipementNew(Request $request, EntityManagerInterface $em): Response
    {
        $equipement = new Equipement();
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($equipement);
            $em->flush();
            $this->addFlash('success', 'Équipement créé avec succès.');
            return $this->redirectToRoute('admin_equipement_index');
        }

        return $this->render('admin/equipements/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/equipements/{id}/edit', name: 'admin_equipement_edit', methods: ['GET', 'POST'])]
    public function equipementEdit(Request $request, Equipement $equipement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Équipement modifié avec succès.');
            return $this->redirectToRoute('admin_equipement_index');
        }

        return $this->render('admin/equipements/edit.html.twig', [
            'equipement' => $equipement,
            'form'       => $form->createView(),
        ]);
    }

    #[Route('/equipements/{id}/delete', name: 'admin_equipement_delete', methods: ['POST'])]
    public function equipementDelete(Request $request, Equipement $equipement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_eq_' . $equipement->getId(), $request->request->get('_token'))) {
            $em->remove($equipement);
            $em->flush();
            $this->addFlash('success', 'Équipement supprimé.');
        }

        return $this->redirectToRoute('admin_equipement_index');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ASSIGNATION D'UN ÉQUIPEMENT À UN USER
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/equipements/{id}/assign', name: 'admin_equipement_assign', methods: ['GET', 'POST'])]
    public function equipementAssign(Request $request, Equipement $equipement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AssignEquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Équipement assigné avec succès.');
            return $this->redirectToRoute('admin_equipement_index');
        }

        return $this->render('admin/equipements/assign.html.twig', [
            'equipement' => $equipement,
            'form'       => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GESTION DES REVIEWS (Admin)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/reviews', name: 'admin_review_index', methods: ['GET'])]
    public function reviewIndex(ReviewRepository $reviewRepository): Response
    {
        return $this->render('admin/reviews/index.html.twig', [
            'reviews'    => $reviewRepository->findBy([], ['date_review' => 'DESC']),
            'statistics' => $reviewRepository->getStatistics(),
        ]);
    }

    #[Route('/reviews/{id}/delete', name: 'admin_review_delete', methods: ['POST'])]
    public function reviewDelete(Request $request, Review $review, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_rev_' . $review->getId(), $request->request->get('_token'))) {
            $em->remove($review);
            $em->flush();
            $this->addFlash('success', 'Avis supprimé.');
        }

        return $this->redirectToRoute('admin_review_index');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GESTION DES UTILISATEURS (Admin)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/user/delete/{id}', name: 'user_delete')]
    public function deleteUser($id, EntityManagerInterface $em)
    {
        $user = $em->getRepository(Users::class)->find($id);
>>>>>>> 45843e398e8d6f4eeb7979c39a74bfa3f8a8ef4e

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
