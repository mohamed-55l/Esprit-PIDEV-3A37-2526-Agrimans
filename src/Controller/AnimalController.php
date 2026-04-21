<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\AnimalHistory;
use App\Entity\AnimalNourriture;
use App\Entity\User;
use App\Entity\UserNotification;
use App\Form\AnimalNourritureType;
use App\Form\AnimalType;
use App\Repository\AnimalHistoryRepository;
use App\Repository\AnimalRepository;
use App\Repository\UserNotificationRepository;
use App\Service\AnimalActivityLogger;
use App\Service\AnimalListPdfExporter;
use App\Service\AnimalNotifier;
use App\Service\OpenRouterAnimalInsightService;
use App\Service\OpenWeatherFarmService;
use App\Service\PollinationsImageService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/waad/animal')]
#[IsGranted('ROLE_USER')]
class AnimalController extends AbstractController
{
    private function currentUserId(): ?int
    {
        $u = $this->getUser();

        return $u instanceof User ? $u->getId() : null;
    }

    #[Route('/stats', name: 'waad_animal_stats', methods: ['GET'])]
    public function stats(
        AnimalRepository $animalRepo,
        OpenRouterAnimalInsightService $openRouter,
        OpenWeatherFarmService $weather,
        Request $request,
    ): Response {
        $uid = $this->currentUserId();
        $parRace = $animalRepo->countActiveGroupedByBreed($uid);
        arsort($parRace);
        $parRaceTop = self::limitGroupedSeries($parRace, 8);

        $statsPayload = [
            'total_actifs' => $animalRepo->countActive($uid),
            'total_archives' => $animalRepo->countArchived($uid),
            'par_espece' => $animalRepo->countActiveGroupedBySpecies($uid),
            'par_sante' => $animalRepo->countActiveGroupedByHealth($uid),
            'par_race' => $parRaceTop,
        ];

        $insight = null;
        if ($request->query->getBoolean('analyse_ia')) {
            $insight = $openRouter->generateInsight($statsPayload);
        }

        return $this->render('animal/stats.html.twig', [
            'stats' => $statsPayload,
            'insight' => $insight,
            'weather' => $weather->getCurrentForFarm(),
        ]);
    }

    #[Route('/historique', name: 'waad_animal_historique', methods: ['GET'])]
    public function historique(AnimalHistoryRepository $historyRepository): Response
    {
        return $this->render('animal/historique.html.twig', [
            'entries' => $historyRepository->findRecentForUser($this->currentUserId()),
        ]);
    }

    #[Route('/archive', name: 'waad_animal_archive', methods: ['GET'])]
    public function archive(AnimalRepository $animalRepo): Response
    {
        return $this->render('animal/archive.html.twig', [
            'animals' => $animalRepo->findAllArchived($this->currentUserId()),
        ]);
    }

    #[Route('/export/pdf', name: 'waad_animal_export_pdf', methods: ['GET'])]
    public function exportPdf(AnimalRepository $animalRepo, AnimalListPdfExporter $exporter): Response
    {
        $animals = $animalRepo->createActiveQueryBuilder($this->currentUserId())->getQuery()->getResult();
        $pdf = $exporter->export($animals);

        $filename = 'animaux-'.(new \DateTimeImmutable())->format('Y-m-d').'.pdf';

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    #[Route('/notifications', name: 'waad_animal_notifications', methods: ['GET'])]
    public function notifications(UserNotificationRepository $repo): Response
    {
        $uid = $this->currentUserId();
        if ($uid === null) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('animal/notifications.html.twig', [
            'notifications' => $repo->findForUser($uid),
        ]);
    }

    /**
     * Polling pour notifications « bureau » (Web Notifications API). since=0 → pas d’items, seulement maxId (ligne de base).
     */
    #[Route('/notifications/api/poll', name: 'waad_animal_notifications_poll', methods: ['GET'])]
    public function notificationsPoll(Request $request, UserNotificationRepository $repo): JsonResponse
    {
        $uid = $this->currentUserId();
        if ($uid === null) {
            return new JsonResponse(['error' => 'unauthorized'], 401);
        }

        $since = max(0, (int) $request->query->get('since', 0));
        $maxId = $repo->findMaxIdForUser($uid, 'animal');

        if ($since === 0) {
            return new JsonResponse([
                'items' => [],
                'maxId' => $maxId,
            ]);
        }

        $rows = $repo->findAnimalNotificationsWithIdGreaterThan($uid, $since, 'animal');
        $items = [];
        foreach ($rows as $n) {
            $items[] = [
                'id' => $n->getId(),
                'title' => $n->getTitle(),
                'message' => $n->getMessage(),
                'link' => $n->getLink(),
            ];
        }

        return new JsonResponse([
            'items' => $items,
            'maxId' => $maxId,
        ]);
    }

    #[Route('/notifications/lues', name: 'waad_animal_notifications_read_all', methods: ['POST'])]
    public function markAllNotificationsRead(
        Request $request,
        UserNotificationRepository $repo,
    ): Response {
        if (!$this->isCsrfTokenValid('animal_notif_read_all', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $uid = $this->currentUserId();
        if ($uid === null) {
            throw $this->createAccessDeniedException();
        }
        $repo->markAllReadForUser($uid);
        $this->addFlash('success', 'Toutes les notifications animaux sont marquées comme lues.');

        return $this->redirectToRoute('waad_animal_notifications');
    }

    #[Route('/notifications/{id}/lue', name: 'waad_animal_notification_read', methods: ['POST'])]
    public function markOneNotificationRead(
        Request $request,
        UserNotification $notification,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid('animal_notif_read_one', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $uid = $this->currentUserId();
        if ($uid === null || $notification->getUserId() !== $uid) {
            throw $this->createAccessDeniedException();
        }
        $notification->setReadAt(new \DateTimeImmutable());
        $em->flush();

        return $this->redirectToRoute('waad_animal_notifications');
    }

    #[Route('', name: 'waad_animal_index', methods: ['GET'])]
    public function index(
        Request $request,
        AnimalRepository $repo,
        PaginatorInterface $paginator,
    ): Response {
        $qb = $repo->createActiveQueryBuilder($this->currentUserId());
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10,
        );

        $form = $this->createForm(AnimalType::class, new Animal(), [
            'action' => $this->generateUrl('waad_animal_new'),
            'method' => 'POST',
        ]);

        $editForms = [];
        foreach ($pagination as $animal) {
            \assert($animal instanceof Animal);
            $editForms[$animal->getId()] = $this->createForm(AnimalType::class, $animal, [
                'action' => $this->generateUrl('waad_animal_edit', ['id' => $animal->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('animal/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);
    }

    #[Route('/new', name: 'waad_animal_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        AnimalActivityLogger $logger,
        AnimalNotifier $notifier,
    ): Response {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uid = $this->currentUserId();
            if ($uid !== null) {
                $animal->setUserId($uid);
            }
            $em->persist($animal);
            $em->flush();
            $logger->log(AnimalHistory::ACTION_CREATED, $animal, $uid, $logger->snapshotAnimal($animal));
            if ($uid !== null) {
                $notifier->notifyAnimalCreated($animal, $uid);
            }
            $em->flush();
            $this->addFlash('success', 'Animal added successfully.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_animal_index');
    }

    #[Route('/{id}/restore', name: 'waad_animal_restore', methods: ['POST'])]
    public function restore(
        Request $request,
        int $id,
        AnimalRepository $animalRepo,
        EntityManagerInterface $em,
        AnimalActivityLogger $activityLogger,
        AnimalNotifier $notifier,
    ): Response {
        if (!$this->isCsrfTokenValid('restore_animal', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $animal = $animalRepo->findOneArchivedById($id, $this->currentUserId());
        if (!$animal) {
            throw $this->createNotFoundException();
        }
        $animal->setDeletedAt(null);
        $uid = $this->currentUserId();
        $activityLogger->log(AnimalHistory::ACTION_RESTORED, $animal, $uid, $activityLogger->snapshotAnimal($animal));
        if ($uid !== null) {
            $notifier->notifyUser($uid, 'Animal restauré', sprintf('« %s » est de nouveau actif dans le cheptel.', $animal->getNom() ?? ''), '/waad/animal/'.$animal->getId());
        }
        $em->flush();
        $this->addFlash('success', 'Animal restauré dans le cheptel actif.');

        return $this->redirectToRoute('waad_animal_index');
    }

    #[Route('/{id}/image-ia', name: 'waad_animal_pollinations', methods: ['POST'])]
    public function generateImage(
        Request $request,
        int $id,
        AnimalRepository $animalRepo,
        EntityManagerInterface $em,
        PollinationsImageService $pollinations,
    ): Response {
        if (!$this->isCsrfTokenValid('animal_pollinations', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $animal = $animalRepo->findOneActiveById($id, $this->currentUserId());
        if (!$animal) {
            throw $this->createNotFoundException();
        }
        $prompt = trim($request->request->getString('prompt'));
        if ($prompt === '') {
            $prompt = sprintf('Healthy farm animal %s named %s, pastoral photorealistic', $animal->getEspece() ?? 'livestock', $animal->getNom() ?? '');
        }
        $animal->setExternalImageUrl($pollinations->imageUrlForPrompt($prompt));
        $em->flush();
        $this->addFlash('success', 'Image générée (aperçu via URL Pollinations).');

        return $this->redirectToRoute('waad_animal_show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'waad_animal_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, AnimalRepository $animalRepo): Response
    {
        $animal = $animalRepo->findOneActiveById($id, $this->currentUserId());
        if (!$animal) {
            throw $this->createNotFoundException();
        }

        $editForm = $this->createForm(AnimalType::class, $animal, [
            'action' => $this->generateUrl('waad_animal_edit', ['id' => $animal->getId()]),
            'method' => 'POST',
        ]);

        $addFeedingForm = $this->createForm(AnimalNourritureType::class, new AnimalNourriture(), [
            'action' => $this->generateUrl('waad_feeding_new', ['animalId' => $animal->getId()]),
            'method' => 'POST',
        ]);

        $editFeedingForms = [];
        foreach ($animal->getAnimalNourritures() as $feeding) {
            $editFeedingForms[$feeding->getId()] = $this->createForm(AnimalNourritureType::class, $feeding, [
                'action' => $this->generateUrl('waad_feeding_edit', ['animalId' => $animal->getId(), 'id' => $feeding->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
            'editForm' => $editForm->createView(),
            'addFeedingForm' => $addFeedingForm->createView(),
            'editFeedingForms' => $editFeedingForms,
        ]);
    }

    #[Route('/{id}/edit', name: 'waad_animal_edit', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request,
        int $id,
        AnimalRepository $animalRepo,
        EntityManagerInterface $em,
        AnimalActivityLogger $logger,
        AnimalNotifier $notifier,
    ): Response {
        $animal = $animalRepo->findOneActiveById($id, $this->currentUserId());
        if (!$animal) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uid = $this->currentUserId();
            $logger->log(AnimalHistory::ACTION_UPDATED, $animal, $uid, $logger->snapshotAnimal($animal));
            if ($uid !== null) {
                $notifier->notifyAnimalUpdated($animal, $uid);
            }
            $em->flush();
            $this->addFlash('success', 'Animal updated successfully.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Please correct the errors in the form.');
        }

        return $this->redirectToRoute('waad_animal_show', ['id' => $animal->getId()]);
    }

    #[Route('/{id}/delete', name: 'waad_animal_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        Request $request,
        int $id,
        AnimalRepository $animalRepo,
        EntityManagerInterface $em,
        AnimalActivityLogger $logger,
        AnimalNotifier $notifier,
    ): Response {
        if (!$this->isCsrfTokenValid('delete_animal', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $animal = $animalRepo->findOneActiveById($id, $this->currentUserId());
        if (!$animal) {
            throw $this->createNotFoundException();
        }

        $animal->setDeletedAt(new \DateTimeImmutable());
        $uid = $this->currentUserId();
        $logger->log(AnimalHistory::ACTION_ARCHIVED, $animal, $uid, $logger->snapshotAnimal($animal), 'Archivage (suppression logique)');
        if ($uid !== null) {
            $notifier->notifyAnimalArchived($animal, $uid);
        }
        $em->flush();
        $this->addFlash('success', 'Animal archivé (retiré du cheptel actif).');

        return $this->redirectToRoute('waad_animal_index');
    }

    /**
     * @param array<string, int> $series
     *
     * @return array<string, int>
     */
    private static function limitGroupedSeries(array $series, int $maxLabels): array
    {
        if ($series === []) {
            return [];
        }
        if (\count($series) <= $maxLabels) {
            return $series;
        }
        $items = \array_slice($series, 0, $maxLabels, true);
        $restKeys = \array_slice(\array_keys($series), $maxLabels);
        $other = 0;
        foreach ($restKeys as $k) {
            $other += $series[$k] ?? 0;
        }
        if ($other > 0) {
            $items['Autres'] = $other;
        }

        return $items;
    }
}
