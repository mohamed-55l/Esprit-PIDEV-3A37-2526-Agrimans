<?php

namespace App\Controller;

use App\Entity\Equipement;
use App\Entity\Review;
use App\Form\AssignEquipementType;
use App\Form\EquipementType;
use App\Repository\EquipementRepository;
use App\Repository\ReviewRepository;
use App\Repository\UsersRepository;
use App\Entity\Users;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CultureRepository;
use App\Repository\ParcelleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
        ];

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'users' => $users,
        ]);
    }

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

        if ($user) {
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/user/update/{id}', name: 'user_update')]
    public function updateUser($id, Request $request, EntityManagerInterface $em)
    {
        $user = $em->getRepository(Users::class)->find($id);

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

            if (empty($name)) $errors['full_name'] = "Nom requis";
            if (empty($email)) $errors['email'] = "Email requis";

            if (!empty($password) && $password !== $confirm) {
                $errors['confirm'] = "Mot de passe incorrect";
            }

            if (empty($errors)) {
                $user->setFullName($name);
                $user->setEmail($email);
                $user->setPhone($phone);

                if ($role === 'ADMIN') {
                    $user->setRole(UserRole::ADMIN);
                } else {
                    $user->setRole(UserRole::USER);
                }

                if (!empty($password)) {
                    $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
                }

                $em->flush();
                return $this->redirectToRoute('admin_dashboard');
            }
        }

        return $this->render('Update/updateuser.html.twig', [
            'user' => $user,
            'errors' => $errors
        ]);
    }

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
