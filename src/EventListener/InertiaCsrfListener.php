<?php

declare(strict_types=1);

namespace App\EventListener;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class InertiaCsrfListener
{
    private const TOKEN_ID = "inertia";
    private const COOKIE_NAME = "XSRF-TOKEN";
    private const HEADER_NAME = "X-XSRF-TOKEN";

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

        $token = new CsrfToken(
            self::TOKEN_ID,
            (string) $request->headers->get(self::HEADER_NAME),
        );
        if ($this->csrfTokenManager->isTokenValid($token)) {
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

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function setTokenCookie(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $event->getResponse()->headers->setCookie(
            Cookie::create(self::COOKIE_NAME)
                ->withValue(
                    $this->csrfTokenManager
                        ->getToken(self::TOKEN_ID)
                        ->getValue(),
                )
                ->withSecure($event->getRequest()->isSecure())
                ->withHttpOnly(false)
                ->withSameSite(Cookie::SAMESITE_LAX),
        );
    }
}
