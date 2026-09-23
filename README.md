# Symfony + React SPA powered by Inertia.js, with SSR

A **Symfony 8.1** starter with a **React 19 + TypeScript** front end, glued together by **Inertia.js v3**, bundled with **Vite**, styled with **Sass**, and **server-side rendered** through Node.

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
- [Styles (Sass)](#styles-sass)
- [SSR](#ssr)
- [Project structure](#project-structure)
- [Troubleshooting](#troubleshooting)

---

## Stack

| Side  | Tool                                                    | Purpose                                          |
| ----- | ------------------------------------------------------- | ------------------------------------------------ |
| Back  | `symfony/framework-bundle` 8.1                          | Framework                                        |
| Back  | `symfony/twig-bundle`                                   | A single root HTML view (`base.html.twig`)       |
| Back  | `nytodev/inertia-bundle`                                | Inertia v3 server adapter (`$inertia->render()`) |
| Back  | `pentatrion/vite-bundle`                                | Symfony ↔ Vite integration (`vite_entry_*_tags`) |
| Back  | `symfony/http-client`                                   | Symfony → Node SSR server calls                  |
| Front | `react` / `react-dom` 19                                | UI                                               |
| Front | `@inertiajs/react` 3                                    | Inertia client adapter                           |
| Front | `typescript`                                            | Type checking only (Vite does the compiling)     |
| Front | `vite` + `vite-plugin-symfony` + `@vitejs/plugin-react` | Build & dev server                               |
| Front | `sass-embedded`                                         | Sass compiler                                    |
| Dev   | `symfony/debug-pack`, `maker-bundle`, `test-pack`       | Profiler, makers, PHPUnit                        |

## Requirements

- PHP ≥ 8.4
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
```

Then generate your own dev secret and paste it as `APP_SECRET` in `.env.dev`:

```bash
php -r 'echo bin2hex(random_bytes(16)), PHP_EOL;'
```

## Running in development

Three processes, three terminals:

```bash
# 1. PHP server
symfony serve -d

# 2. Vite dev server (on-the-fly JS/CSS + hot reload)
npm run dev

# 3. (optional) SSR server
npm run build:ssr
symfony console inertia:start-ssr
```

Then open **http://localhost:8000**.

> ⚠️ `http://localhost:5173` is **not** the app: it's Vite's asset server. Always browse the app through Symfony (port 8000).

The SSR server is optional in development: when it's down, Inertia silently falls back to client-side rendering.

## Production build

```bash
npm run build
```

This script runs two builds:

| Script              | Config               | Output                 | Contents                            |
| ------------------- | -------------------- | ---------------------- | ----------------------------------- |
| `vite build`        | `vite.config.js`     | `public/build/`        | Client JS + CSS, `entrypoints.json` |
| `npm run build:ssr` | `vite.ssr.config.js` | `bootstrap/ssr/ssr.js` | Node bundle for SSR                 |

In production, run the SSR server under a process manager (systemd, Supervisor…) with `node bootstrap/ssr/ssr.js`, and restart it after every deploy.

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

**Resolution rule** (see `assets/resolvePage.tsx`):

```
$inertia->render('Home')  →  assets/HomePage/Home.tsx
$inertia->render('About') →  assets/AboutPage/About.tsx
```

The same resolver is shared by the client (`app.tsx`) and the SSR entry (`ssr.tsx`). Pages are lazy-loaded: each one becomes its own JS chunk, fetched on first visit.

Other components live on two levels:

| Level      | Location               | When                      |
| ---------- | ---------------------- | ------------------------- |
| **Local**  | Inside the page folder | Only used by that page    |
| **Shared** | `assets/components/`   | Used by two or more pages |

Rule of thumb: start local, move to `components/` when a second page needs it.

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

**2. The page component**

```tsx
// assets/ContactPage/Contact.tsx
type Props = { title: string };

export default function Contact({ title }: Props) {
    return <h1>{title}</h1>;
}
```

That's it: no front-end route, no API call.

> ⚠️ PHP array keys become React prop names **exactly** (case-sensitive), and TypeScript can't check what PHP actually sends. When a prop is `undefined`, inspect the JSON response in DevTools → Network first.

> If the SSR server is running, run `npm run build:ssr` and restart it so it knows about the new page.

## Layout and navigation

`assets/components/Layout.tsx` holds the main menu and wraps every page. It's registered as the default layout in **both** `app.tsx` and `ssr.tsx`:

```tsx
createInertiaApp({
    resolve: resolvePage,
    layout: () => Layout,
});
```

It's a **persistent layout**: on navigation only the page is swapped, the layout isn't re-mounted, so its state survives. A page can opt for a different layout with `MyPage.layout = OtherLayout`.

The active link is detected with `usePage().url`.

## Styles (Sass)

- Entry point: `assets/styles/app.scss`, declared as a **separate Vite entry** (`styles`) and loaded with `{{ vite_entry_link_tags('styles') }}` in `<head>`.
- Variables: `assets/styles/_variables.scss` (a partial, loaded with `@use "variables" as *;`).
- Use `@use`, not `@import` (deprecated in Sass).

**Why a separate entry instead of `import "./app.scss"` in `app.tsx`?** In dev, Vite injects JS-imported CSS _after_ the JS has loaded. With SSR the HTML shows up first → flash of unstyled content (FOUC). As a separate entry, the CSS is served through a real `<link>` in `<head>`, even in dev.

For component-scoped styles, use CSS Modules (`Component.module.scss`), supported natively by Vite.

## SSR

| File                           | Purpose                                                                                                      |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------ |
| `assets/ssr.tsx`               | Node entry point: `createServer()` + `renderToString`                                                        |
| `vite.ssr.config.js`           | Dedicated Vite config (no `vite-plugin-symfony`, `publicDir: false`, `ssr.noExternal: ['@inertiajs/react']`) |
| `config/packages/inertia.yaml` | `ssr_enabled`, `ssr_url`, `ssr_bundle`                                                                       |

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
├── app.tsx               # Client entry: createInertiaApp()
├── ssr.tsx               # SSR entry: createServer()
├── resolvePage.tsx       # Page name → component (shared by client & SSR)
├── components/           # Shared components
│   └── Layout.tsx        # Default persistent layout + menu
├── HomePage/
│   ├── Home.tsx          # render('Home')
│   └── TechnoStack.tsx    # Local component
├── AboutPage/
│   └── About.tsx         # render('About')
└── styles/
    ├── app.scss          # Sass entry
    └── _variables.scss
bootstrap/ssr/            # Generated SSR bundle (git-ignored)
config/packages/
└── inertia.yaml          # Inertia + SSR config
public/build/             # Generated client assets (git-ignored)
src/Controller/           # Controllers → $inertia->render()
templates/
└── base.html.twig        # The one and only Twig view (Inertia root view)
tsconfig.json
vite.config.js            # Client build
vite.ssr.config.js        # SSR build
```

## Troubleshooting

| Symptom                                               | Cause                                                                                        | Fix                                                                                                                                         |
| ----------------------------------------------------- | -------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| `@vitejs/plugin-react can't detect preamble`          | The React Refresh script isn't injected (the page is served by Symfony, not Vite)            | `vite_entry_script_tags('app', { dependency: 'react' })` in `base.html.twig`                                                                |
| `ERR_CONNECTION_REFUSED` on `:5173`, styles gone      | `npm run dev` stopped, but `public/build/.vite/entrypoints.json` still points to Vite        | Restart `npm run dev`, or run `npm run build` for static assets                                                                             |
| Unstyled flash on load (dev)                          | JS-imported CSS + SSR                                                                        | CSS as a separate Vite entry (already set up)                                                                                               |
| `Cannot read properties of undefined (reading 'map')` | Hot reload re-rendered a component with stale props, or the controller sends the wrong props | Hit F5; then check the controller's `render()` name and prop keys in the JSON response                                                      |
| Hydration mismatch warning                            | SSR bundle is outdated, or client/SSR config differ (e.g. `layout` set in only one entry)    | `npm run build:ssr` + restart SSR; keep `app.tsx` and `ssr.tsx` in sync                                                                     |
| `SSR bundle not found`                                | Bundle not built, or wrong path                                                              | `npm run build:ssr`. The bundle auto-detects `bootstrap/ssr/ssr.mjs` but Vite outputs `ssr.js`, hence `ssr_bundle` is set in `inertia.yaml` |
| SSR shows an old version                              | The Node server loads the bundle once, at startup                                            | `npm run build:ssr`, then restart `inertia:start-ssr`                                                                                       |
| New Vite entry ignored                                | `entrypoints.json` is written when Vite starts                                               | Restart `npm run dev`                                                                                                                       |

## Resources

- [Inertia.js](https://inertiajs.com/)
- [nytodev/inertia-bundle](https://github.com/nytodev/inertia-bundle)
- [Pentatrion Vite bundle](https://symfony-vite.pentatrion.com/)
- [Vite](https://vite.dev/)
- [Symfony](https://symfony.com/doc/current/)
