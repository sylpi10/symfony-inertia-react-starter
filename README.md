# Symfony + React SPA powered by Inertia.js, with SSR

A **Symfony 8.1** starter with a **React 19 + TypeScript** front end, glued together by **Inertia.js v3**: you build a single-page app **without writing an API**. Controllers stay regular Symfony controllers, but instead of rendering Twig templates they return **a React page + its props**.

Included: Vite, Sass, server-side rendering, authentication (login, registration, password reset, email verification), CSRF protection, SEO (head tags per page, sitemap, `robots.txt`), an example admin CRUD, TypeScript types generated from PHP DTOs, functional tests, a Docker dev stack and a GitHub Actions CI.

## Quick start

```bash
composer create-project sylpi10/symfony-inertia-react-starter my-app
cd my-app
npm install
make dev
```

Open **http://localhost:8000** and sign in with `admin@example.com` / `admin1234`.

Needs PHP ≥ 8.4, Composer, Node 24 and the [Symfony CLI](https://symfony.com/download). No PHP or Node on your machine? Use [Docker](#running-with-docker) instead. Details in [Getting started](#getting-started).

## Requirements

- PHP ≥ 8.4 with `pdo_sqlite`
- Composer, Node.js (v24) + npm
- [Symfony CLI](https://symfony.com/download) (recommended, required by `make dev`)
- `make` (optional)

Or only Docker: see [Running with Docker](#running-with-docker).

## Getting started

```bash
composer create-project sylpi10/symfony-inertia-react-starter my-app
cd my-app
npm install
```

`create-project` installs the PHP dependencies, then runs `composer setup`, which:

1. generates a fresh `APP_SECRET` in `.env.dev` (so that no two projects share the same one);
2. runs the migrations, creating `var/data_dev.db` (SQLite);
3. loads the fixtures: demo data + demo accounts;
4. prepares the test database (`var/data_test.db`) the same way, so that `php bin/phpunit` works right away.

Then make the project yours: replace `name` and `description` in `composer.json` (they still read `sylpi10/symfony-inertia-react-starter`), and run `composer update --lock` to keep `composer.lock` in sync.

Prefer GitHub's **Use this template** button? Clone your new repository, then run the same steps by hand:

```bash
composer install
composer setup
npm install
```

> ⚠️ `composer setup` is meant to run once: it resets both databases (dev and test) and replaces `APP_SECRET`.

## Running in development

```bash
make dev
```

This starts the Symfony server in the background, along with the Vite dev server (hot reload), declared as a worker in `.symfony.local.yaml`.

| Command      | What it does                                                        |
| ------------ | ------------------------------------------------------------------- |
| `make dev`   | Starts the Symfony server + Vite in the background                  |
| `make stop`  | Stops the server and its workers                                    |
| `make logs`  | Tails the server and Vite logs                                      |
| `make build` | Builds the client and SSR bundles (`public/build`, `bootstrap/ssr`) |
| `make test`  | Prepares the test database, then runs PHPUnit                       |
| `make test-ssr` | Builds the assets, then checks the home page is server-rendered |

Without `make`: `symfony serve -d` does the same as `make dev`. Without the Symfony CLI, run your PHP server and `npm run dev` in two terminals.

Open **http://localhost:8000** and sign in with one of the demo accounts:

| Account             | Password    | Role                                      |
| ------------------- | ----------- | ----------------------------------------- |
| `test@example.com`  | `test1234`  | Regular user                              |
| `admin@example.com` | `admin1234` | `ROLE_ADMIN`: gets the **Admin** nav link |

> ⚠️ `localhost:5173` is Vite's asset server, not the app. Always browse through Symfony (port 8000).

Useful commands:

| Command                                     | What it does                                            |
| ------------------------------------------- | ------------------------------------------------------- |
| `composer types`                            | Regenerates `assets/types/generated.ts` from PHP DTOs   |
| `npm run typecheck`                         | Type-checks the front end (`tsc --noEmit`)              |
| `npm run lint`                              | Checks the formatting of `assets/` with Prettier        |
| `symfony console doctrine:fixtures:load -n` | Resets the database with demo data (deletes all users!) |
| `/_profiler`                                | Symfony profiler: requests, SQL, **emails sent in dev** |

SSR is **off in development** (no hot reload on the Node side). To check it: see [SSR and production](docs/ssr-and-production.md).

## Running with Docker

A development stack that needs neither PHP nor Node on your machine: [FrankenPHP](https://frankenphp.dev) for Symfony, and a Node container for Vite (hot reload included).

```bash
make docker-up          # first start takes a minute or two (composer install, npm ci)
make docker-fixtures    # demo data + demo accounts
```

Then open **http://localhost:8000**, as without Docker.

| Command                | What it does                              |
| ---------------------- | ----------------------------------------- |
| `make docker-up`       | Builds and starts the containers          |
| `make docker-down`     | Stops them (volumes are kept)             |
| `make docker-logs`     | Follows the logs of both containers       |
| `make docker-fixtures` | Loads the fixtures in the Docker database |
| `make docker-test`     | Runs PHPUnit in the `php` container       |

Good to know:

- The `php` container runs `composer install` and the migrations each time it starts.
- The SQLite database lives in a Docker volume, separate from `var/data_dev.db`. `docker compose down -v` deletes it, along with the container's `node_modules`.
- Containers run as your user, so `vendor/` and `var/` stay yours.
- Ports 8000 and 5173 are the same as `make dev`: stop one before starting the other, or change them with `APP_PORT=8080 VITE_PORT=5174 make docker-up`.
- This stack is for development only. For production, see [SSR and production](docs/ssr-and-production.md).

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

## Example admin CRUD

`/admin/technos` manages the technos listed on the home page. It is a reference for the patterns you will reuse in your own CRUDs:

| Pattern                 | Where to look                                                                                              |
| ----------------------- | ---------------------------------------------------------------------------------------------------------- |
| Access control          | `#[IsGranted("ROLE_ADMIN")]` on `Admin/TechnoController`: anonymous → `/login`, non-admin → 403 error page |
| One input DTO, two uses | `TechnoInput` with `#[MapRequestPayload]`, shared by create (`POST`) and update (`PUT`)                    |
| One form page, two uses | `Pages/Admin/Technos/Form.tsx`: `form.post()` or `form.put()` depending on the `techno` prop               |
| Delete                  | `router.delete()` after a `confirm()`, answered by a **303** redirect                                      |
| Pagination              | Doctrine `Paginator` in `TechnoRepository`, `?page=N` bound with `#[MapQueryParameter]`                    |
| Optimistic update       | Reordering with `router.optimistic(...).patch(...)`: the row moves at once, Inertia rolls back on failure  |
| Enum shared with TS     | `MoveDirection` (`up` / `down`), exported to `generated.ts`                                                |

## Conventions

- **Never pass a Doctrine entity to `render()`**: always a DTO (an entity would leak every getter, including password hashes).
- **Forms**: an input DTO with `#[MapRequestPayload]` + Validator constraints on the PHP side, `useForm` on the React side. Validation errors come back automatically in `form.errors`.
- **After a successful POST, PUT, PATCH or DELETE**: flash message + redirect with a **303** (with a 302, the browser would replay the redirect with the same method).
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
├── Controller/     # Controllers → $inertia->render(), Admin/ for the CRUD
├── Dto/            # Input DTOs, output DTOs, Page/ props
├── Enum/           # Enums, exported to TypeScript
├── Entity/, Repository/, DataFixtures/
└── EventListener/  # Shared props, CSRF, error pages
tests/              # Functional tests (InertiaWebTestCase + Controller/)
```

## Testing

```bash
make test                               # migrations + fixtures on var/data_test.db, then PHPUnit
php bin/phpunit --filter testCreate     # a single test, once the test database exists
```

The test database is created by `composer setup` (or `make test`). If PHPUnit fails with `no such table`, it is missing: run `make test`. Run it again after adding a migration or changing the fixtures.

Functional tests live in `tests/Controller/` and extend `tests/InertiaWebTestCase.php`, which talks to the app the way the Inertia client does:

- `visit()` sends the `X-Inertia` header and copies the `XSRF-TOKEN` cookie into `X-XSRF-TOKEN` (without it, every POST is rejected). Responses are JSON, so tests need neither Vite nor a build.
- `page()` returns an `AssertableInertiaPage` to assert the component and its props; `flash()` reads flash messages.
- Fixtures are loaded once; [dama/doctrine-test-bundle](https://github.com/dmaicher/doctrine-test-bundle) rolls back the database after each test.

Covered: pages and 404, SEO props and head tags, sitemap and `robots.txt`, CSRF, registration, email verification, login (case-insensitive email, throttling), password reset, and the admin CRUD including access control.

### Continuous integration

`.github/workflows/ci.yaml` runs on every push to `master` and on pull requests, without Docker:

- **PHP**: `composer validate`, `lint:container`, migrations + `doctrine:schema:validate`, PHPUnit, and a check that `composer types` leaves `assets/types` unchanged (a DTO changed without regenerating the types fails the build).
- **Front**: `npm run typecheck`, `npm run lint`, and the client + SSR builds.

## Documentation

| Guide                                                        | Contents                                                                                                         |
| ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------- |
| [Architecture](docs/architecture.md)                         | How Inertia works, page resolution, layouts, styles, images, full project structure                              |
| [TypeScript types](docs/types.md)                            | DTOs, `#[TypeScript]`, `composer types`, shared props typing                                                     |
| [Authentication, forms and security](docs/authentication.md) | Login, registration, password reset, email verification, shared props, flash messages, CSRF, error pages, emails |
| [SSR and production](docs/ssr-and-production.md)             | Enabling SSR, Node server, production build, asset versioning, deploy order, known limitations |
| [SEO](docs/seo.md) | Head tags per page (`SeoDto`), indexing rules, share image, sitemap and `robots.txt` |
| [Troubleshooting](docs/troubleshooting.md)                   | Common errors and their fixes                                                                                    |

## License

[MIT](LICENSE)
