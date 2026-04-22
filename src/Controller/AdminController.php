<?php

namespace App\Controller;

use App\Entity\Equipement;
use App\Entity\Garage;
use App\Entity\Review;
use App\Form\AssignEquipementType;
use App\Form\EquipementType;
use App\Form\GarageType;
use App\Repository\EquipementRepository;
use App\Repository\GarageRepository;
use App\Repository\ReviewRepository;
use App\Repository\UsersRepository;
use App\Entity\Users;
use App\Enum\UserRole;
use App\Service\StockAlertService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CultureRepository;
use App\Repository\ParcelleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

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
        UsersRepository $userRepository,
        ChartBuilderInterface $chartBuilder
    ): Response {
        $users = $userRepository->findAll();
        
        $stats = [
            'total_equipements' => $equipementRepository->count([]),
            'total_reviews'     => $reviewRepository->count([]),
            'total_users'       => count($users),
        ];

        // 📊 Graphique 1 : Répartition des Équipements par Disponibilité
        $equipements = $equipementRepository->findAll();
        $dispoCount = ['Disponible' => 0, 'Indisponible' => 0, 'En Panne' => 0];
        foreach ($equipements as $eq) {
            $dispo = ucfirst(strtolower(trim($eq->getDisponibilite() ?? '')));
            if (isset($dispoCount[$dispo])) {
                $dispoCount[$dispo]++;
            } else {
                if (!isset($dispoCount['Autre'])) $dispoCount['Autre'] = 0;
                $dispoCount['Autre']++;
            }
        }

        $chartEquipement = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chartEquipement->setData([
            'labels' => array_keys($dispoCount),
            'datasets' => [
                [
                    'label' => 'Équipements',
                    'backgroundColor' => ['#2ecc71', '#e74c3c', '#f1c40f', '#95a5a6'],
                    'borderColor' => '#1e2529',
                    'data' => array_values($dispoCount),
                ],
            ],
        ]);
        $chartEquipement->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['color' => '#fff']]]
        ]);

        // 📊 Graphique 2 : Notes des Avis (Étoiles)
        $reviews = $reviewRepository->findAll();
        $notesCount = ['1 Étoile' => 0, '2 Étoiles' => 0, '3 Étoiles' => 0, '4 Étoiles' => 0, '5 Étoiles' => 0];
        foreach ($reviews as $rev) {
            $note = $rev->getNote();
            if ($note >= 1 && $note <= 5) {
                $notesCount[$note . ' Étoile' . ($note > 1 ? 's' : '')]++;
            }
        }

        $chartReview = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartReview->setData([
            'labels' => array_keys($notesCount),
            'datasets' => [
                [
                    'label' => 'Nombre d\'Avis',
                    'backgroundColor' => '#f1c40f',
                    'data' => array_values($notesCount),
                ],
            ],
        ]);
        $chartReview->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['color' => '#fff']],
                'x' => ['ticks' => ['color' => '#fff']]
            ],
            'plugins' => ['legend' => ['display' => false]]
        ]);

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'users' => $users,
            'chartEquipement' => $chartEquipement,
            'chartReview' => $chartReview,
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
    public function equipementEdit(Request $request, Equipement $equipement, EntityManagerInterface $em, StockAlertService $stockAlert): Response
    {
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $stockAlert->checkAndSendAlert();
            $this->addFlash('success', 'Équipement modifié avec succès.');
            return $this->redirectToRoute('admin_equipement_index');
        }

        return $this->render('admin/equipements/edit.html.twig', [
            'equipement' => $equipement,
            'form'       => $form->createView(),
        ]);
    }

    #[Route('/equipements/{id}/delete', name: 'admin_equipement_delete', methods: ['POST'])]
    public function equipementDelete(Request $request, Equipement $equipement, EntityManagerInterface $em, StockAlertService $stockAlert): Response
    {
        if ($this->isCsrfTokenValid('delete_eq_' . $equipement->getId(), $request->request->get('_token'))) {
            $em->remove($equipement);
            $em->flush();
            $stockAlert->checkAndSendAlert();
            $this->addFlash('success', 'Équipement supprimé.');
        }

        return $this->redirectToRoute('admin_equipement_index');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ASSIGNATION D'UN ÉQUIPEMENT À UN USER
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/equipements/{id}/assign', name: 'admin_equipement_assign', methods: ['GET', 'POST'])]
    public function equipementAssign(Request $request, Equipement $equipement, EntityManagerInterface $em, StockAlertService $stockAlert): Response
    {
        $form = $this->createForm(AssignEquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $stockAlert->checkAndSendAlert();
            $this->addFlash('success', 'Équipement assigné avec succès.');
            return $this->redirectToRoute('admin_equipement_index');
        }

        return $this->render('admin/equipements/assign.html.twig', [
            'equipement' => $equipement,
            'form'       => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GESTION DES GARAGES (Admin)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/garages', name: 'admin_garage_index', methods: ['GET'])]
    public function garageIndex(GarageRepository $garageRepository): Response
    {
        return $this->render('admin/garages/index.html.twig', [
            'garages' => $garageRepository->findAll(),
        ]);
    }

    #[Route('/garages/new', name: 'admin_garage_new', methods: ['GET', 'POST'])]
    public function garageNew(Request $request, EntityManagerInterface $em): Response
    {
        $garage = new Garage();
        $garage->setDateCreation(new \DateTime());
        $form = $this->createForm(GarageType::class, $garage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($garage);
            $em->flush();
            $this->addFlash('success', 'Garage créé avec succès.');
            return $this->redirectToRoute('admin_garage_index');
        }

        return $this->render('admin/garages/new.html.twig', [
            'garage' => $garage,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/garages/{id}/edit', name: 'admin_garage_edit', methods: ['GET', 'POST'])]
    public function garageEdit(Request $request, Garage $garage, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GarageType::class, $garage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Garage modifié avec succès.');
            return $this->redirectToRoute('admin_garage_index');
        }

        return $this->render('admin/garages/edit.html.twig', [
            'garage' => $garage,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/garages/{id}/delete', name: 'admin_garage_delete', methods: ['POST'])]
    public function garageDelete(Request $request, Garage $garage, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_gar_' . $garage->getId(), $request->request->get('_token'))) {
            $em->remove($garage);
            $em->flush();
            $this->addFlash('success', 'Garage supprimé.');
        }

        return $this->redirectToRoute('admin_garage_index');
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
