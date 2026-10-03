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
| `config/packages/inertia.php`  | `ssr_enabled`, `ssr_url`, `ssr_bundle`, `pages`                                                              |
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
