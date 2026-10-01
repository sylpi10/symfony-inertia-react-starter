# Symfony + React SPA powered by Inertia.js, with SSR

A **Symfony 8.1** starter with a **React 19 + TypeScript** front end, glued together by **Inertia.js v3**, bundled with **Vite**, styled with **Sass**, and **server-side rendered** through Node. Authentication is included: login, registration, password reset, email verification, CSRF protection.

> Inertia lets you build a React SPA **without writing an API**: Symfony controllers stay regular controllers (routing, security, validation…), but instead of rendering a Twig template they return **a React component name + props**.

---

## Table of contents

- [Stack](#stack)
- [Requirements](#requirements)
- [Getting started](#getting-started)
- [Running in development](#running-in-development)
- [Production build](#production-build)
- [How it works](#how-it-works)
- [Pages and components](#pages-and-components)
- [Adding a page](#adding-a-page)
- [Layout and navigation](#layout-and-navigation)
- [Forms and validation](#forms-and-validation)
- [Authentication](#authentication)
- [Shared props and flash messages](#shared-props-and-flash-messages)
- [CSRF protection](#csrf-protection)
- [Error pages](#error-pages)
- [Emails](#emails)
- [Styles (Sass)](#styles-sass)
- [SSR](#ssr)
- [Project structure](#project-structure)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

## Stack

| Side  | Tool                                                         | Purpose                                                    |
| ----- | ------------------------------------------------------------ | ---------------------------------------------------------- |
| Back  | `symfony/framework-bundle` 8.1                               | Framework                                                  |
| Back  | `symfony/twig-bundle`                                        | A single root HTML view (`base.html.twig`) + email templates |
| Back  | `nytodev/inertia-bundle`                                     | Inertia v3 server adapter (`$inertia->render()`)           |
| Back  | `pentatrion/vite-bundle`                                     | Symfony ↔ Vite integration (`vite_entry_*_tags`)           |
| Back  | `symfony/http-client`                                        | Symfony → Node SSR server calls                            |
| Back  | Doctrine ORM + Migrations                                    | Persistence (SQLite by default)                            |
| Back  | `symfony/security-bundle`, `symfony/rate-limiter`            | Authentication, login throttling                           |
| Back  | `symfony/validator`, `symfony/serializer`                    | Input DTOs with `#[MapRequestPayload]`                     |
| Back  | `symfony/mailer`                                             | Password reset and email verification emails               |
| Back  | `symfonycasts/reset-password-bundle`, `verify-email-bundle`  | Password reset tokens, signed verification links           |
| Front | `react` / `react-dom` 19                                     | UI                                                         |
| Front | `@inertiajs/react` 3                                         | Inertia client adapter                                     |
| Front | `typescript`                                                 | Type checking only (Vite does the compiling)               |
| Front | `vite` + `vite-plugin-symfony` + `@vitejs/plugin-react`      | Build & dev server                                         |
| Front | `sass-embedded`                                              | Sass compiler                                              |
| Dev   | `symfony/debug-pack`, `maker-bundle`, `test-pack`            | Profiler, makers, PHPUnit                                  |

## Requirements

- PHP ≥ 8.4 with `pdo_sqlite`
- Composer
- Node.js (tested with v24) + npm
- [Symfony CLI](https://symfony.com/download) (recommended)

## Getting started

This repository is a **GitHub template**: click **Use this template → Create a new repository**, then:

```bash
git clone git@github.com:<you>/<your-project>.git
cd <your-project>

composer install
npm install

# Create the SQLite database (var/data_dev.db) and its tables
symfony console doctrine:migrations:migrate --no-interaction
```

Generate your own dev secret and paste it as `APP_SECRET` in `.env.dev` (it signs sessions, remember-me cookies and verification links):

```bash
php -r 'echo bin2hex(random_bytes(16)), PHP_EOL;'
```

Then create a first account at **http://localhost:8000/register** (see [Running in development](#running-in-development)).

**Using PostgreSQL instead of SQLite**: set `DATABASE_URL` in `.env.local`. Migrations contain SQL specific to the platform they were generated on, so regenerate them for PostgreSQL (`symfony console make:migration`) in a new project.

## Running in development

Two processes, two terminals:

```bash
# 1. PHP server
symfony serve -d

# 2. Vite dev server (on-the-fly JS/CSS + hot reload)
npm run dev
```

Then open **http://localhost:8000**.

> ⚠️ `http://localhost:5173` is **not** the app: it's Vite's asset server. Always browse the app through Symfony (port 8000).

**SSR is off by default in development.** To check server-side rendering locally:

| You want to…        | `.env.local`                                        | Terminals                                                                   |
| ------------------- | --------------------------------------------------- | --------------------------------------------------------------------------- |
| Develop as usual    | nothing (or `INERTIA_SSR=0`)                        | `symfony serve` + `npm run dev`                                             |
| Check SSR           | `INERTIA_SSR=1`, then `symfony console cache:clear` | the same + `npm run build:ssr`, then `symfony console inertia:start-ssr`    |

`INERTIA_SSR=1` only tells Symfony to **call** the Node server; it doesn't start it. When the Node server is down, Inertia silently falls back to client-side rendering.

## Production build

```bash
npm run build
```

This script runs two builds:

| Script              | Config               | Output                 | Contents                            |
| ------------------- | -------------------- | ---------------------- | ----------------------------------- |
| `vite build`        | `vite.config.js`     | `public/build/`        | Client JS + CSS, `entrypoints.json` |
| `npm run build:ssr` | `vite.ssr.config.js` | `bootstrap/ssr/ssr.js` | Node bundle for SSR                 |

In production, set these in the server's environment (`.env.local` or real env vars), never in a committed file:

| Variable          | Example                                                    |
| ----------------- | ---------------------------------------------------------- |
| `APP_ENV`         | `prod`                                                     |
| `APP_SECRET`      | a unique random value                                      |
| `DATABASE_URL`    | your production database                                   |
| `MAILER_DSN`      | your SMTP server (see [Emails](#emails))                   |
| `INERTIA_SSR`     | `1` to enable SSR                                          |
| `INERTIA_SSR_URL` | URL of the Node SSR server                                 |

Run the SSR server under a process manager (systemd, Supervisor…) with `node bootstrap/ssr/ssr.js`, and restart it after every deploy.

---

## How it works

### First visit (full page load)

```
Browser ──GET /──▶ Symfony (HomeController)
                      │  $inertia->render('Home', ['message' => ...])
                      │
                      ├──POST /render──▶ Node SSR server (port 13714)
                      │◀── <Home /> HTML ──┘
                      ▼
                base.html.twig
                ├─ {{ inertiaHead(page) }}   → <head> tags from SSR
                ├─ {{ vite_entry_*_tags }}   → CSS + JS
                └─ {{ inertia(page) }}       → pre-rendered HTML + page JSON
                      │
Browser ◀─────────────┘
   └─ React hydrates the existing HTML (hydrateRoot)
```

### Subsequent navigation

Inertia links (`<Link>`) send an XHR request with an `X-Inertia: true` header. The **same controller** answers, but the bundle returns **JSON only** (`{ component, props, url, version }`), and React swaps the page component without a full reload.

|                  | Plain `<a href>`       | Inertia `<Link href>`                |
| ---------------- | ---------------------- | ------------------------------------ |
| Request          | Full HTML page         | XHR with `X-Inertia: true`           |
| Symfony response | Full HTML (Twig + SSR) | JSON `{ component, props, url }`     |
| Browser          | Full reload            | React swaps the page, URL is updated |

There is **no client-side router**: routes live exclusively in Symfony (`#[Route]`).

## Pages and components

Everything is a React component. A **page** is simply the root component a controller renders by name.

The project follows the standard Inertia convention from the official docs.

**Resolution rule** (see `assets/pages.ts`): the name passed to `render()` is the path under `assets/Pages/`, without the extension.

```
$inertia->render('Home')        →  assets/Pages/Home.tsx
$inertia->render('Auth/Login')  →  assets/Pages/Auth/Login.tsx
$inertia->render('Users/Index') →  assets/Pages/Users/Index.tsx
```

`assets/pages.ts` exports `resolvePage` and `resolveLayout`, shared by the client (`app.tsx`) and the SSR entry (`ssr.tsx`), so both always resolve pages and layouts the same way. Pages are lazy-loaded: each one becomes its own JS chunk, fetched on first visit.

The bundle's `pages.ensure_pages_exist` option is enabled: `render('Abuot')` throws `Inertia page component [Abuot] not found.` on the PHP side, instead of failing in the browser.

| Folder               | Contents                                                       |
| -------------------- | -------------------------------------------------------------- |
| `assets/Pages/`      | One file = one component rendered by `$inertia->render()`      |
| `assets/Layouts/`    | Persistent layouts (`AppLayout`, `GuestLayout`)                |
| `assets/Components/` | Shared components (imported normally, no naming constraint)    |
| `assets/types/`      | TypeScript declarations (shared props, flash data)             |

## Adding a page

**1. The controller**

```php
// src/Controller/ContactController.php
namespace App\Controller;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Inertia $inertia): Response
    {
        return $inertia->render('Contact', [
            'title' => 'Contact us',
        ]);
    }
}
```

> With `make:controller`, pass `--no-template`: Inertia controllers never render a Twig template.

**2. The page component**

```tsx
// assets/Pages/Contact.tsx
type Props = { title: string };

export default function Contact({ title }: Props) {
    return <h1>{title}</h1>;
}
```

That's it: no front-end route, no API call.

> ⚠️ PHP array keys become React prop names **exactly** (case-sensitive), and TypeScript can't check what PHP actually sends. When a prop is `undefined`, inspect the JSON response in DevTools → Network first.

> ⚠️ Never pass a Doctrine entity to `render()`: every public getter would end up in the page JSON (including password hashes). Pass a DTO or an array with only what the page needs.

> If the SSR server is running, run `npm run build:ssr` and restart it so it knows about the new page.

## Layout and navigation

Two layouts live in `assets/Layouts/`:

| Layout        | Used by                         | Contents                                                       |
| ------------- | ------------------------------- | -------------------------------------------------------------- |
| `AppLayout`   | Every page (default)            | Centered menu, user area (email + logout, or login/register), verify-email banner, flash messages |
| `GuestLayout` | `Auth/*` pages (login, signup…) | Minimal wrapper + flash messages, no app menu                  |

The choice is made by name in `resolveLayout()` (`assets/pages.ts`), passed to `createInertiaApp` in **both** `app.tsx` and `ssr.tsx`:

```tsx
createInertiaApp({
    resolve: resolvePage,
    layout: resolveLayout,
});
```

These are **persistent layouts**: on navigation only the page is swapped, the layout isn't re-mounted, so its state survives. A page can still force a different layout with `MyPage.layout = OtherLayout`.

The active link is detected with `usePage().url`. The menu is centered with a `1fr auto 1fr` CSS grid, so the user area on the right doesn't push it off-center.

## Forms and validation

Every form follows the same pattern: **an input DTO + `#[MapRequestPayload]` on the PHP side, `useForm` on the React side.**

```php
// src/Dto/RegisterUserInput.php
final readonly class RegisterUserInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email = '',
        // ...
    ) {
    }
}

// Controller: when this method runs, the data is already valid
#[Route('/register', name: 'app_register_submit', methods: ['POST'])]
public function submit(#[MapRequestPayload] RegisterUserInput $input): Response
```

```tsx
const form = useForm({ email: "", password: "", passwordConfirmation: "" });
form.post("/register");
// form.errors.email, form.processing, form.reset(...)
```

When validation fails, the bundle's `InertiaValidationListener` stores the first message of each field and redirects back (303). `useForm` receives them in `form.errors`, under the field with the **same name as the DTO property**. No `if ($form->isValid())`, no Symfony Form component.

After a successful submit, set a flash message and redirect with a **303** (POST → redirect → GET), so F5 never re-submits the form.

## Authentication

| Feature                  | Routes                                                     | Pages (`assets/Pages/Auth/`)               |
| ------------------------ | ---------------------------------------------------------- | ------------------------------------------ |
| Login                    | `GET` / `POST /login`                                      | `Login.tsx`                                |
| Logout                   | `POST /logout` (a `GET` returns 405)                       | button in `AppLayout`                      |
| Registration             | `GET` / `POST /register`                                   | `Register.tsx`                             |
| Password reset           | `/reset-password`, `/reset-password/check-email`, `/reset-password/reset/{token}` | `ForgotPassword.tsx`, `CheckEmail.tsx`, `ResetPassword.tsx` |
| Email verification       | `GET /verify/email` (signed link), `POST /verify/resend`   | `VerifyEmailBanner` component              |

**Login** uses Symfony's standard `form_login` (`config/packages/security.yaml`). The React page posts with `useForm` and **`forceFormData: true`**: Inertia sends JSON by default, which `form_login` can't read. On failure, the firewall redirects to `/login` and `SecurityController` passes `AuthenticationUtils::getLastAuthenticationError()` as `errors.email`. Field names are `email`, `password` and `remember` (`username_parameter`, `password_parameter`, `remember_me_parameter`).

**Remember me**: a signed `REMEMBERME` cookie valid for one week, set only when the checkbox is ticked.

**Login throttling**: 5 failed attempts per 15 minutes (per email + IP). To unblock yourself in dev:

```bash
symfony console cache:pool:clear cache.rate_limiter
```

**Logout** is POST-only (`<Link method="post" as="button">`): a GET logout could be triggered by any site with a simple `<img src="…/logout">`.

**Registration** creates the user, hashes the password, logs the user in (`Security::login()`) and sends a verification email.

**Password reset** (`symfonycasts/reset-password-bundle`): the controller generated by `make:reset-password` was rewritten to render Inertia pages with input DTOs. It never reveals whether an account exists (same response for unknown emails), removes the token from the URL as soon as the link is opened, and allows one request per user per hour (`throttle_limit`). To retest in dev:

```bash
symfony console dbal:run-sql 'DELETE FROM reset_password_request'
```

**Email verification** (`symfonycasts/verify-email-bundle`): a signed link (user id + email + expiration + `APP_SECRET`) valid for one hour. Unverified users **can** log in and see a banner with a "resend" button. To block them instead, add a `UserChecker` that throws a `CustomUserMessageAccountStatusException` in `checkPreAuth()`, and declare it as `user_checker` on the firewall.

**Authorization** stays classic Symfony: `#[IsGranted('ROLE_ADMIN')]` on actions, `access_control` in `security.yaml`. An anonymous visitor is redirected to `/login`; a logged-in user without the right role gets the 403 error page.

**Creating a user without the registration form** (e.g. for tests):

```bash
symfony console security:hash-password 'your-password' 'App\Entity\User'
sqlite3 var/data_dev.db
```
```sql
INSERT INTO "user" (email, roles, password, is_verified) VALUES ('you@example.com', '[]', 'PASTE_THE_HASH', 1);
```

## Shared props and flash messages

`src/EventListener/InertiaShareListener.php` adds props to **every** Inertia response:

```ts
usePage().props.auth.user   // User | null
```

- `auth.user` is a **`UserDto`** (`id`, `email`, `roles`, `isVerified`), **never the entity**: the entity would leak the password hash into every page's JSON.
- The value is a closure, evaluated only at render time (after the firewall). The listener runs with **priority 64**, before the router (32), so shared props also exist on 404 pages.

Shared props and flash data are typed once for the whole app in `assets/types/inertia.d.ts`, by augmenting `@inertiajs/core`'s `InertiaConfig` (`sharedPageProps`, `flashDataType`).

**Flash messages** use Inertia v3's native `flash`:

```php
$inertia->flash('success', 'Your password has been reset.');
return $this->redirectToRoute('app_login', status: Response::HTTP_SEE_OTHER);
```

They're displayed by `Components/FlashMessages.tsx`, present in both layouts (`success` and `error` keys).

## CSRF protection

`src/EventListener/InertiaCsrfListener.php` implements the mechanism recommended by the Inertia docs:

1. On every response, it sets an `XSRF-TOKEN` cookie (readable by JS, `SameSite=Lax`) holding a token from Symfony's `CsrfTokenManager`.
2. Inertia's HTTP client automatically sends it back in an `X-XSRF-TOKEN` header.
3. On every `POST`, `PUT`, `PATCH` and `DELETE`, the listener checks the header. It runs with **priority 10**, before the firewall (8), so `/login` and `/logout` are protected too.

On an invalid token, the user is redirected back (same origin only) with a "This page has expired" flash message, instead of an error modal. All unsafe requests are checked, not only Inertia ones: a plain HTML form posted from another site is rejected too.

The session cookie is `SameSite=Lax` (Symfony's default). Since the token is stored in the session, every visitor gets a session.

## Error pages

`src/EventListener/InertiaErrorPageListener.php` renders `assets/Pages/Error.tsx` instead of Symfony's error page or Inertia's error modal:

| Error                                        | Development                          | Production       |
| -------------------------------------------- | ------------------------------------ | ---------------- |
| 403, 404 and other 4xx                       | `Pages/Error.tsx`                    | `Pages/Error.tsx` |
| 500                                          | Symfony's debug page (stack trace)   | `Pages/Error.tsx` |
| Anonymous user on a protected page           | redirect to `/login`                 | same             |

It runs with **priority -1**, after Symfony's exception logger (so errors are still logged) and after Inertia's validation listener. Only the status code is sent to the front, never the exception message.

> On a 404 thrown by the router, the menu shows "Login" even for a logged-in user: the request stops before the firewall runs. This is a Symfony limitation.

## Emails

Email templates stay in **Twig** (`templates/registration/`, `templates/reset_password/`): that's what Twig is still for in an Inertia app.

| Environment | `MAILER_DSN`                                         | Where to read emails                                  |
| ----------- | ---------------------------------------------------- | ----------------------------------------------------- |
| Development | `null://null` (default in `.env`)                    | Profiler (`/_profiler`) → the **POST** request → **E-mails** tab |
| Production  | your SMTP server, in `.env.local`                     | the real inbox                                        |

```dotenv
# .env.local — never commit SMTP credentials
MAILER_DSN="smtp://user%40your-domain.com:PASSWORD@mail.your-domain.com:465?encryption=ssl"
```

- Encode `@` as `%40` in the username (and special characters in the password).
- The sender is set in `ResetPasswordController` and `Security/EmailVerifier.php` (`no-reply@example.com`). **Change it to an address on your own domain**: mail providers reject or spam emails whose sender domain doesn't match the sending server (SPF/DMARC).
- `symfony console mailer:test you@example.com` sends a test email outside the app.

## Styles (Sass)

- Entry point: `assets/styles/app.scss`, declared as a **separate Vite entry** (`styles`) and loaded with `{{ vite_entry_link_tags('styles') }}` in `<head>`.
- Partials: `_variables.scss`, `_nav.scss`, `_form.scss` (loaded with `@use`).
- Use `@use`, not `@import` (deprecated in Sass).

**Why a separate entry instead of `import "./app.scss"` in `app.tsx`?** In dev, Vite injects JS-imported CSS _after_ the JS has loaded. With SSR the HTML shows up first → flash of unstyled content (FOUC). As a separate entry, the CSS is served through a real `<link>` in `<head>`, even in dev.

For component-scoped styles, use CSS Modules (`Component.module.scss`), supported natively by Vite.

## SSR

| File                           | Purpose                                                                                                      |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------ |
| `assets/ssr.tsx`               | Node entry point: `createServer()` + `renderToString`                                                        |
| `vite.ssr.config.js`           | Dedicated Vite config (no `vite-plugin-symfony`, `publicDir: false`, `ssr.noExternal: ['@inertiajs/react']`) |
| `config/packages/inertia.php`  | `ssr_enabled`, `ssr_url`, `ssr_bundle`, `pages`                                                              |
| `.env` / `.env.local`          | `INERTIA_SSR` (on/off), `INERTIA_SSR_URL`                                                                    |

**Why is the Inertia config in PHP rather than YAML?** The bundle reads `ssr_enabled` when the container is compiled, to decide whether to wire the SSR HTTP client. A `%env()%` placeholder is only resolved at runtime, too late. The PHP config reads `$_SERVER['INERTIA_SSR']` at compile time instead, which is why changing `INERTIA_SSR` requires a `symfony console cache:clear`. `ssr_url` is only used at runtime, so it stays a regular `%env(INERTIA_SSR_URL)%`.

Commands:

```bash
symfony console inertia:start-ssr   # start the Node server (blocks the terminal)
symfony console inertia:check-ssr   # check it responds
symfony console inertia:stop-ssr    # stop it
```

To check SSR is working, view the page source (Ctrl+U): the component's HTML must be there, not just the JSON.

**SSR golden rule**: never touch `window`, `document`, `localStorage`… in a component's body (it crashes on Node). Put that code in a `useEffect`, which only runs in the browser.

---

## Project structure

```
assets/
├── app.tsx                    # Client entry: createInertiaApp()
├── ssr.tsx                    # SSR entry: createServer()
├── pages.ts                   # resolvePage + resolveLayout (shared by client & SSR)
├── Pages/                     # One file = one page rendered by $inertia->render()
│   ├── Home.tsx
│   ├── About.tsx
│   ├── Error.tsx              # 4xx/5xx error page
│   └── Auth/                  # Login, Register, ForgotPassword, CheckEmail, ResetPassword
├── Layouts/
│   ├── AppLayout.tsx          # Default persistent layout: menu, user area, banner, flashes
│   └── GuestLayout.tsx        # Layout for Auth/* pages
├── Components/                # Shared components
│   ├── FlashMessages.tsx
│   ├── VerifyEmailBanner.tsx
│   └── Icons/UserIcon.tsx
├── types/
│   └── inertia.d.ts           # Shared props + flash data typing
└── styles/                    # app.scss (entry) + partials
bootstrap/ssr/                 # Generated SSR bundle (git-ignored)
config/packages/
├── inertia.php                # Inertia + SSR config
└── security.yaml              # Firewall: form_login, logout, remember_me, login_throttling
migrations/                    # Doctrine migrations (SQLite)
public/build/                  # Generated client assets (git-ignored)
src/
├── Controller/                # Controllers → $inertia->render()
├── Dto/                       # Input DTOs (#[MapRequestPayload]) and output DTOs (UserDto)
├── Entity/                    # User, ResetPasswordRequest
├── EventListener/
│   ├── InertiaShareListener.php      # Shared props (auth.user)
│   ├── InertiaCsrfListener.php       # XSRF-TOKEN cookie + X-XSRF-TOKEN check
│   └── InertiaErrorPageListener.php  # Pages/Error.tsx for errors
├── Repository/
└── Security/EmailVerifier.php # Sends signed verification emails
templates/
├── base.html.twig             # The Inertia root view
├── registration/              # Verification email
└── reset_password/            # Password reset email
var/data_dev.db                # SQLite database (git-ignored)
tsconfig.json
vite.config.js                 # Client build
vite.ssr.config.js             # SSR build
```

## Troubleshooting

| Symptom                                               | Cause                                                                                        | Fix                                                                                                                                         |
| ----------------------------------------------------- | -------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| `@vitejs/plugin-react can't detect preamble`          | The React Refresh script isn't injected (the page is served by Symfony, not Vite)            | `vite_entry_script_tags('app', { dependency: 'react' })` in `base.html.twig`                                                                |
| `ERR_CONNECTION_REFUSED` on `:5173`, styles gone      | `npm run dev` stopped, but `public/build/.vite/entrypoints.json` still points to Vite        | Restart `npm run dev`, or run `npm run build` for static assets                                                                             |
| Unstyled flash on load (dev)                          | JS-imported CSS + SSR                                                                        | CSS as a separate Vite entry (already set up)                                                                                               |
| `Cannot read properties of undefined (reading 'map')` | Hot reload re-rendered a component with stale props, or the controller sends the wrong props | Hit F5; then check the controller's `render()` name and prop keys in the JSON response                                                      |
| Hydration mismatch warning                            | SSR bundle is outdated, or client/SSR config differ (e.g. `layout` set in only one entry)    | `npm run build:ssr` + restart SSR; keep `app.tsx` and `ssr.tsx` in sync                                                                     |
| `SSR bundle not found`                                | Bundle not built, or wrong path                                                              | `npm run build:ssr`. The bundle auto-detects `bootstrap/ssr/ssr.mjs` but Vite outputs `ssr.js`, hence `ssr_bundle` is set in `inertia.php`  |
| `Inertia page component [X] not found`                | `render()` name doesn't match a file in `assets/Pages/` (case-sensitive)                     | Fix the name, or create `assets/Pages/X.tsx`                                                                                                |
| `INERTIA_SSR` change has no effect                    | The value is read when the container is compiled                                             | `symfony console cache:clear`                                                                                                               |
| `INERTIA_SSR=1` but no HTML in the page source        | The Node SSR server isn't running (Symfony silently falls back to client rendering)          | `npm run build:ssr`, then `symfony console inertia:start-ssr`                                                                               |
| SSR shows an old version                              | The Node server loads the bundle once, at startup                                            | `npm run build:ssr`, then restart `inertia:start-ssr`                                                                                       |
| New Vite entry ignored                                | `entrypoints.json` is written when Vite starts                                               | Restart `npm run dev`                                                                                                                       |
| `Variable "page" does not exist` in `base.html.twig`  | A controller renders a Twig template that extends `base.html.twig` (e.g. maker-generated)    | Render an Inertia page with `$inertia->render()` instead; keep Twig for emails only                                                         |
| "This page has expired. Please try again."            | Missing or invalid `X-XSRF-TOKEN` (cookie deleted, session expired, cross-site request)     | Retry: the response sets a fresh cookie. In tests, GET a page first and send the cookie value in the header                                |
| `props.auth` is undefined                             | A shared-props listener ran too late (e.g. after the router on a 404)                        | Keep `InertiaShareListener` at priority 64; check with `symfony console debug:event-dispatcher kernel.request`                              |
| "Too many failed login attempts"                      | Login throttling (5 attempts / 15 min)                                                       | Wait, or `symfony console cache:pool:clear cache.rate_limiter` in dev                                                                       |
| No password reset email                               | Unknown email (silent by design) or one request per hour already made                        | Check the email in the profiler's Doctrine tab; `DELETE FROM reset_password_request` in dev                                                 |
| Emails sent but never received                        | Sender domain doesn't match the SMTP server (SPF/DMARC)                                      | Use a sender address on your own domain; check the spam folder                                                                              |

## License

[MIT](LICENSE)

## Resources

- [Inertia.js](https://inertiajs.com/)
- [nytodev/inertia-bundle](https://github.com/nytodev/inertia-bundle)
- [Pentatrion Vite bundle](https://symfony-vite.pentatrion.com/)
- [SymfonyCasts reset-password-bundle](https://github.com/SymfonyCasts/reset-password-bundle) / [verify-email-bundle](https://github.com/SymfonyCasts/verify-email-bundle)
- [Vite](https://vite.dev/)
- [Symfony](https://symfony.com/doc/current/)
