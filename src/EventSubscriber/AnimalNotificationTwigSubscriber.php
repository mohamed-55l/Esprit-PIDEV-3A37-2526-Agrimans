<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserNotificationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class AnimalNotificationTwigSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private UserNotificationRepository $notifications,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 5]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $path = $event->getRequest()->getPathInfo();
        if (str_starts_with($path, '/_')) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();
        if (!$user instanceof User) {
            $this->twig->addGlobal('animal_notif_unread', 0);

            return;
        }

        $this->twig->addGlobal(
            'animal_notif_unread',
            $this->notifications->countUnreadForUser((int) $user->getId()),
        );
    }
}
