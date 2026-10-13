<?php

declare(strict_types=1);

namespace App\EventListener;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Rejects every unsafe request (POST, PUT, PATCH, DELETE) that doesn't come from the app itself.
 *
 * The "inertia" token is stateless (config/packages/csrf.yaml): Symfony validates it from
 * the Sec-Fetch-Site, Origin or Referer header that browsers attach to the request, so
 * no token has to be stored in the session or sent in a cookie.
 */
final class InertiaCsrfListener
{
    private const TOKEN_ID = "inertia";

    public function __construct(
        private CsrfTokenManagerInterface $csrfTokenManager,
        private Inertia $inertia,
    ) {}

    /**
     * Priority 10: runs before the security firewall (8), so the login POST is protected too.
     */
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 10)]
    public function checkToken(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || $request->isMethodSafe()) {
            return;
        }

        // For a stateless id, getToken() returns a constant: validity comes from the request's origin
        if ($this->csrfTokenManager->isTokenValid($this->csrfTokenManager->getToken(self::TOKEN_ID))) {
            return;
        }

        $this->inertia->flash(
            "error",
            "This page has expired. Please try again.",
        );

        $referer = $request->headers->get("referer");
        $sameHost =
            null !== $referer &&
            parse_url($referer, \PHP_URL_HOST) === $request->getHost();

        $event->setResponse(
            new RedirectResponse(
                $sameHost ? $referer : "/",
                Response::HTTP_SEE_OTHER,
            ),
        );
    }
}
