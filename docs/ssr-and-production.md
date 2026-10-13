# SSR and production

← [Back to README](../README.md)

## Enabling SSR in development

SSR is **off by default in development**: the Node server doesn't hot-reload, so every component change would require a rebuild.

| You want to…        | `.env.local`                                        | Terminals                                                                   |
| ------------------- | --------------------------------------------------- | --------------------------------------------------------------------------- |
| Develop as usual    | nothing (or `INERTIA_SSR=0`)                        | `symfony serve` + `npm run dev`                                             |
| Check SSR           | `INERTIA_SSR=1`, then `symfony console cache:clear` | the same + `npm run build:ssr`, then `symfony console inertia:start-ssr`    |

`INERTIA_SSR=1` only tells Symfony to **call** the Node server; it doesn't start it. When the Node server is down, Inertia silently falls back to client-side rendering.

## SSR files and configuration

| File                           | Purpose                                                                                                      |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------ |
| `assets/ssr.tsx`               | Node entry point: `createServer()` + `renderToString`                                                        |
| `vite.ssr.config.js`           | Dedicated Vite config (no `vite-plugin-symfony`, `publicDir: false`, `ssr.noExternal: ['@inertiajs/react']`) |
| `config/packages/inertia.php`  | `ssr_enabled`, `ssr_url`, `ssr_bundle`, `pages`, `version`                                                   |
| `.env` / `.env.local`          | `INERTIA_SSR` (on/off), `INERTIA_SSR_URL`                                                                    |

**Why is the Inertia config in PHP rather than YAML?** The bundle reads `ssr_enabled` when the container is compiled, to decide whether to wire the SSR HTTP client. A `%env()%` placeholder is only resolved at runtime, too late. The PHP config reads `$_SERVER['INERTIA_SSR']` at compile time instead, which is why changing `INERTIA_SSR` requires a `symfony console cache:clear`. `INERTIA_SSR_URL` is only referenced when SSR is enabled: an env var referenced but never used makes Symfony refuse to compile the container.

Commands:

```bash
symfony console inertia:start-ssr   # start the Node server (blocks the terminal)
symfony console inertia:check-ssr   # check it responds
symfony console inertia:stop-ssr    # stop it
```

To check SSR is working, view the page source (Ctrl+U): the component's HTML must be there, not just the JSON.

**SSR golden rule**: never touch `window`, `document`, `localStorage`… in a component's body (it crashes on Node). Put that code in a `useEffect`, which only runs in the browser.

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

## Asset versioning

After a deploy, a visitor whose tab stayed open still runs the **old** JavaScript against the **new** server. Inertia's asset versioning fixes that: every Inertia request carries the version the page was loaded with, and when it differs from the server's, the server answers `409 Conflict` and the client does a full page reload.

The version is set in `config/packages/inertia.php`: a hash of Vite's build manifest (`public/build/.vite/manifest.json`), so it changes exactly when the built assets change. With the Vite dev server there is no manifest, hence no versioning.

The hash is computed **when the container is compiled**, which dictates the order of a deploy:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build                       # 1. build the assets (writes the manifest)
php bin/console cache:clear                   # 2. compile the container (reads the manifest)
php bin/console doctrine:migrations:migrate --no-interaction
# 3. restart the SSR server, so it loads the new bundle
```

Clearing the cache before the build would keep the previous version, and open tabs would never reload.

## Known limitations

- **SSR is off in development.** The Node server loads the bundle once at startup and doesn't hot-reload. Check SSR with a build, as described above.
- **The SSR server is a separate Node process** that you run and restart yourself. When it is down, pages still work: Inertia falls back to client-side rendering, without the SEO and first-paint benefits. The same happens if a component crashes during SSR (`ssr_throw_on_error` is off).
- **Menu on router 404s.** On a URL that matches no route, the menu shows "Login" even for a logged-in user: the request stops before the firewall runs, so the user is unknown. See [Error pages](authentication.md#error-pages).
- **SQLite by default.** Migrations are SQLite-specific. Switching to PostgreSQL or MySQL means regenerating them, and on PostgreSQL also renaming the `user` table (a reserved word), for instance with `#[ORM\Table(name: "users")]`.
- **The Docker stack is for development only**: no production image is provided.
- **Supported versions**: PHP ≥ 8.4, Symfony 8.1, Node 24, Inertia.js v3, React 19. CI runs on PHP 8.4 and Node 24.
