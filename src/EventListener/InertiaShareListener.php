<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Dto\UserDto;
use App\Entity\User;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 64)]
final class InertiaShareListener
{
    public function __construct(
        private Inertia $inertia,
        private Security $security,
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->inertia->share("auth", function (): array {
            $user = $this->security->getUser();

            return [
                "user" =>
                    $user instanceof User ? UserDto::fromEntity($user) : null,
            ];
        });
    }
}
