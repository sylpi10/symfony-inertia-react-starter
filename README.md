# Symfony + React SPA powered by Inertia.js, with SSR

A **Symfony 8.1** starter with a **React 19 + TypeScript** front end, glued together by **Inertia.js v3**: you build a single-page app **without writing an API**. Controllers stay regular Symfony controllers, but instead of rendering Twig templates they return **a React page + its props**.

Included: Vite, Sass, server-side rendering, authentication (login, registration, password reset, email verification), CSRF protection, and TypeScript types generated from PHP DTOs.

## Requirements

- PHP ≥ 8.4 with `pdo_sqlite`
- Composer, Node.js (v24) + npm
- [Symfony CLI](https://symfony.com/download) (recommended)

## Getting started

Click **Use this template** on GitHub, clone your new repository, then:

```bash
composer install
npm install

symfony console doctrine:migrations:migrate --no-interaction   # creates var/data_dev.db (SQLite)
symfony console doctrine:fixtures:load --no-interaction        # demo data + test account
```

Generate your own `APP_SECRET` and paste it in `.env.dev`:

```bash
php -r 'echo bin2hex(random_bytes(16)), PHP_EOL;'
```

## Running in development

Two terminals:

```bash
symfony serve -d     # PHP server
npm run dev          # Vite dev server (hot reload)
```

Open **http://localhost:8000** and sign in with **`test@example.com` / `test1234`**.

> ⚠️ `localhost:5173` is Vite's asset server, not the app. Always browse through Symfony (port 8000).

Useful commands:

| Command                                          | What it does                                             |
| ------------------------------------------------ | -------------------------------------------------------- |
| `composer types`                                 | Regenerates `assets/types/generated.ts` from PHP DTOs    |
| `npx tsc --noEmit`                               | Type-checks the front end                                |
| `symfony console doctrine:fixtures:load -n`      | Resets the database with demo data (deletes all users!)  |
| `/_profiler`                                     | Symfony profiler: requests, SQL, **emails sent in dev**  |

SSR is **off in development** (no hot reload on the Node side). To check it: see [SSR and production](docs/ssr-and-production.md).

## Adding a page

**1. A props DTO**, marked `#[TypeScript]`:

```php
// src/Dto/Page/ContactPageProps.php
#[TypeScript]
final readonly class ContactPageProps
{
    public function __construct(
        public string $email,
    ) {
    }
}
```

**2. The controller** returns a page name + the DTO:

```php
// src/Controller/ContactController.php
#[Route('/contact', name: 'app_contact')]
public function index(Inertia $inertia): Response
{
    return $inertia->render('Contact', get_object_vars(new ContactPageProps(
        email: 'hello@example.com',
    )));
}
```

**3. Generate the types**: `composer types`

**4. The page component**, in `assets/Pages/` (the name passed to `render()` is the file path):

```tsx
// assets/Pages/Contact.tsx
import type { ContactPageProps } from "../types/generated";

export default function Contact({ email }: ContactPageProps) {
    return <h1>Write to {email}</h1>;
}
```

No front-end route, no API call. Links between pages use `<Link href="/contact">` from `@inertiajs/react`.

## Conventions

- **Never pass a Doctrine entity to `render()`**: always a DTO (an entity would leak every getter, including password hashes).
- **Forms**: an input DTO with `#[MapRequestPayload]` + Validator constraints on the PHP side, `useForm` on the React side. Validation errors come back automatically in `form.errors`.
- **After a successful POST**: flash message + redirect with a **303**.
- **Static UI text** goes in the component; **props** are for data only the server knows (database, user, computed values).
- **Twig** is only used for the root view (`templates/base.html.twig`) and emails.

## Project layout (short)

```
assets/
├── Pages/          # One file = one page rendered by $inertia->render()
├── Layouts/        # AppLayout (default), GuestLayout (Auth/* pages)
├── Components/     # Shared components
├── images/         # UI images, imported by components
├── types/          # generated.ts (never edit) + inertia.d.ts
└── styles/         # Sass
src/
├── Controller/     # Controllers → $inertia->render()
├── Dto/            # Input DTOs, output DTOs, Page/ props
├── Entity/, Repository/, DataFixtures/
└── EventListener/  # Shared props, CSRF, error pages
```

## Documentation

| Guide                                                    | Contents                                                              |
| -------------------------------------------------------- | --------------------------------------------------------------------- |
| [Architecture](docs/architecture.md)                     | How Inertia works, page resolution, layouts, styles, images, full project structure |
| [TypeScript types](docs/types.md)                        | DTOs, `#[TypeScript]`, `composer types`, shared props typing          |
| [Authentication, forms and security](docs/authentication.md) | Login, registration, password reset, email verification, shared props, flash messages, CSRF, error pages, emails |
| [SSR and production](docs/ssr-and-production.md)         | Enabling SSR, Node server, production build and environment variables |
| [Troubleshooting](docs/troubleshooting.md)               | Common errors and their fixes                                         |

## License

[MIT](LICENSE)
