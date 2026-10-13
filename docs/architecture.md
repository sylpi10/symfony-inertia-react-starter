# Architecture

← [Back to README](../README.md)

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

See the step-by-step example in the [README](../README.md#adding-a-page) and the [types guide](types.md).

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

## Styles (Sass)

- Entry point: `assets/styles/app.scss`, declared as a **separate Vite entry** (`styles`) and loaded with `{{ vite_entry_link_tags('styles') }}` in `<head>`.
- Partials: `_variables.scss`, `_nav.scss`, `_form.scss` (loaded with `@use`).
- Use `@use`, not `@import` (deprecated in Sass).

**Why a separate entry instead of `import "./app.scss"` in `app.tsx`?** In dev, Vite injects JS-imported CSS _after_ the JS has loaded. With SSR the HTML shows up first → flash of unstyled content (FOUC). As a separate entry, the CSS is served through a real `<link>` in `<head>`, even in dev.

For component-scoped styles, use CSS Modules (`Component.module.scss`), supported natively by Vite.

## Images

| Kind of image                        | Folder                    | How to use it                                         |
| ------------------------------------ | ------------------------- | ----------------------------------------------------- |
| UI (logos, illustrations)            | `assets/images/`          | `import logo from "../images/logo.webp"`              |
| Fixed URL (favicon, og:image, PDF)   | `public/`                 | `"/images/og-image.webp"` or a meta in `base.html.twig` |
| Content stored in the database       | `public/images/<type>/` (or `public/uploads/`, git-ignored) | the entity stores a file name, the DTO sends the full URL |

Imported images get a hashed file name (cache busting), and a missing file fails the build. `vite.ssr.config.js` sets `base: "/build/"` so the SSR build produces the same image URLs as the client build.

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
├── images/                    # UI images, imported by components
├── types/
│   ├── generated.ts           # Generated from PHP DTOs (composer types) — never edit by hand
│   └── inertia.d.ts           # Shared props + flash data typing
└── styles/                    # app.scss (entry) + partials
bootstrap/ssr/                 # Generated SSR bundle (git-ignored)
config/packages/
├── inertia.php                # Inertia + SSR config
└── security.yaml              # Firewall: form_login, logout, remember_me, login_throttling
migrations/                    # Doctrine migrations (SQLite)
public/build/                  # Generated client assets (git-ignored)
src/
├── Command/                   # app:typescript:generate (dev only)
├── Controller/                # Controllers → $inertia->render()
├── DataFixtures/              # Demo data: technos + test@example.com
├── Dto/                       # Input DTOs, output DTOs (UserDto, TechnoDto), Page/ props DTOs
├── Entity/                    # User, Techno, ResetPasswordRequest
├── EventListener/
│   ├── InertiaShareListener.php      # Shared props (auth.user)
│   ├── InertiaCsrfListener.php       # XSRF-TOKEN cookie + X-XSRF-TOKEN check
│   └── InertiaErrorPageListener.php  # Pages/Error.tsx for errors
├── Repository/
└── Security/EmailVerifier.php # Sends signed verification emails
templates/
├── base.html.twig             # The Inertia root view
├── _seo.html.twig             # Head tags when SSR is off
├── seo/                       # sitemap.xml and robots.txt
├── registration/              # Verification email
└── reset_password/            # Password reset email
var/data_dev.db                # SQLite database (git-ignored)
tsconfig.json
vite.config.js                 # Client build
vite.ssr.config.js             # SSR build
```
