<?php

declare(strict_types=1);

namespace App\EventListener;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Priority -1: after Symfony's exception logger (0), so errors are still logged,
 * and after Inertia's validation listener (16) and the security firewall (1).
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: -1)]
final class InertiaErrorPageListener
{
    public function __construct(
        private Inertia $inertia,
        #[Autowire("%kernel.debug%")] private bool $debug,
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $throwable = $event->getThrowable();
        $status =
            $throwable instanceof HttpExceptionInterface
                ? $throwable->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;

        // In debug mode, keep Symfony's exception page for crashes: it shows the stack trace.
        if ($this->debug && $status >= 500) {
            return;
        }

        $response = $this->inertia->render("Error", ["status" => $status]);
        $response->setStatusCode($status);

        $event->setResponse($response);
        $event->allowCustomResponseCode();
    }
}
