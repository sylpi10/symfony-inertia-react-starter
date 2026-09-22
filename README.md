# Symfony + Inertia + React Starter

Base de projet **Symfony 8.1** avec un front **React 19 + TypeScript**, reliés par **Inertia.js v3**, compilés avec **Vite**, stylés en **Sass**, et rendus côté serveur (**SSR**) via Node.

> Inertia permet de construire une SPA React **sans écrire d'API** : les contrôleurs Symfony restent des contrôleurs classiques (routing, sécurité, validation…), mais au lieu de rendre un template Twig, ils renvoient **un nom de composant React + des props**.

---

## Sommaire

- [Stack](#stack)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Lancer le projet en dev](#lancer-le-projet-en-dev)
- [Build de production](#build-de-production)
- [Comment ça marche](#comment-ça-marche)
- [Créer une nouvelle page](#créer-une-nouvelle-page)
- [Styles (Sass)](#styles-sass)
- [SSR](#ssr)
- [Structure du projet](#structure-du-projet)
- [Pièges connus](#pièges-connus)

---

## Stack

| Côté | Outil | Rôle |
|---|---|---|
| Back | `symfony/framework-bundle` 8.1 | Framework |
| Back | `symfony/twig-bundle` | Une seule vue HTML racine (`base.html.twig`) |
| Back | `nytodev/inertia-bundle` | Adaptateur serveur Inertia v3 (`$inertia->render()`) |
| Back | `pentatrion/vite-bundle` | Lien Symfony ↔ Vite (`vite_entry_*_tags`) |
| Back | `symfony/http-client` | Appels Symfony → serveur SSR Node |
| Front | `react` / `react-dom` 19 | UI |
| Front | `@inertiajs/react` 3 | Adaptateur client Inertia |
| Front | `typescript` | Typage (vérification uniquement, Vite compile) |
| Front | `vite` + `vite-plugin-symfony` + `@vitejs/plugin-react` | Build & serveur de dev |
| Front | `sass-embedded` | Compilation Sass |
| Dev | `symfony/debug-pack`, `maker-bundle`, `test-pack` | Profiler, makers, PHPUnit |

## Prérequis

- PHP ≥ 8.4
- Composer
- Node.js (testé avec v24) + npm
- [Symfony CLI](https://symfony.com/download) (recommandé)

## Installation

À partir de ce dépôt utilisé comme modèle :

```bash
git clone git@github.com:sylpi10/symfony-inertia-react-starter.git mon-projet
cd mon-projet
rm -rf .git && git init

composer install
npm install
```

## Lancer le projet en dev

Trois processus, trois terminaux :

```bash
# 1. Serveur PHP
symfony serve -d

# 2. Serveur Vite (JS/CSS à la volée + hot reload)
npm run dev

# 3. (optionnel) Serveur SSR
npm run build:ssr
symfony console inertia:start-ssr
```

Puis ouvrir **http://localhost:8000**.

> ⚠️ `http://localhost:5173` n'est **pas** l'application : c'est le serveur d'assets de Vite. L'app se consulte toujours via Symfony (port 8000).

Le serveur SSR est optionnel en dev : s'il est arrêté, Inertia bascule silencieusement en rendu côté client.

## Build de production

```bash
npm run build
```

Ce script enchaîne deux builds :

| Script | Config | Sortie | Contenu |
|---|---|---|---|
| `vite build` | `vite.config.js` | `public/build/` | JS + CSS client, `entrypoints.json` |
| `npm run build:ssr` | `vite.ssr.config.js` | `bootstrap/ssr/ssr.js` | Bundle Node pour le SSR |

En production, le serveur SSR doit tourner sous un gestionnaire de processus (systemd, Supervisor…) avec `node bootstrap/ssr/ssr.js`, et être redémarré après chaque déploiement.

---

## Comment ça marche

### Première visite (chargement complet)

```
Navigateur ──GET /──▶ Symfony (HomeController)
                         │  $inertia->render('Home', ['message' => ...])
                         │
                         ├──POST /render──▶ Serveur SSR Node (port 13714)
                         │◀── HTML de <Home /> ──┘
                         ▼
                   base.html.twig
                   ├─ {{ inertiaHead(page) }}   → balises <head> issues du SSR
                   ├─ {{ vite_entry_*_tags }}   → CSS + JS
                   └─ {{ inertia(page) }}       → HTML pré-rendu + JSON de la page
                         │
Navigateur ◀─────────────┘
   └─ React "hydrate" le HTML existant (hydrateRoot)
```

### Navigations suivantes

Les liens Inertia (`<Link>`) font une requête XHR avec l'en-tête `X-Inertia`. Symfony répond alors **uniquement en JSON** (`{ component, props, url, version }`) et React remplace le composant, sans rechargement de page.

### Résolution des composants

`$inertia->render('Home')` → `assets/Pages/Home.tsx`, via `import.meta.glob` dans `assets/app.tsx` (client) et `assets/ssr.tsx` (serveur).

## Créer une nouvelle page

**1. Le contrôleur**

```php
// src/Controller/AboutController.php
namespace App\Controller;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route('/about', name: 'about')]
    public function index(Inertia $inertia): Response
    {
        return $inertia->render('About', [
            'title' => 'À propos',
        ]);
    }
}
```

**2. Le composant**

```tsx
// assets/Pages/About.tsx
type Props = { title: string };

export default function About({ title }: Props) {
    return <h1>{title}</h1>;
}
```

C'est tout : pas de route front, pas d'appel API. Les sous-dossiers fonctionnent aussi (`render('Users/Index')` → `assets/Pages/Users/Index.tsx`).

> Si le SSR tourne, pense à `npm run build:ssr` + redémarrage du serveur SSR pour qu'il connaisse la nouvelle page.

## Styles (Sass)

- Point d'entrée : `assets/styles/app.scss`, déclaré comme **entrée Vite séparée** (`styles`) et chargé par `{{ vite_entry_link_tags('styles') }}` dans le `<head>`.
- Variables : `assets/styles/_variables.scss` (partial, importé via `@use "variables" as *;`).
- Utiliser `@use`, pas `@import` (déprécié par Sass).

**Pourquoi une entrée séparée plutôt que `import "./app.scss"` dans `app.tsx` ?** En dev, Vite injecte le CSS importé depuis le JS *après* le chargement du JS. Avec le SSR, le HTML s'affiche avant → flash de contenu non stylé (FOUC). En entrée séparée, le CSS arrive via un vrai `<link>` dans le `<head>`, même en dev.

Pour du style par composant : CSS Modules (`Composant.module.scss`), gérés nativement par Vite.

## SSR

| Fichier | Rôle |
|---|---|
| `assets/ssr.tsx` | Point d'entrée Node : `createServer()` + `renderToString` |
| `vite.ssr.config.js` | Config Vite dédiée (sans `vite-plugin-symfony`, avec `ssr.noExternal: ['@inertiajs/react']`) |
| `config/packages/inertia.yaml` | `ssr_enabled`, `ssr_url`, `ssr_bundle` |

Commandes :

```bash
symfony console inertia:start-ssr   # démarre le serveur Node (bloque le terminal)
symfony console inertia:check-ssr   # vérifie qu'il répond
symfony console inertia:stop-ssr    # l'arrête
```

Vérifier que le SSR fonctionne : afficher le code source de la page (Ctrl+U) → le HTML du composant doit être présent, pas seulement le JSON.

**Règle d'or SSR** : pas d'accès à `window`, `document`, `localStorage`… dans le corps d'un composant (ça plante côté Node). Les mettre dans un `useEffect`, qui ne s'exécute que dans le navigateur.

---

## Structure du projet

```
assets/
├── app.tsx              # Entrée client : createInertiaApp()
├── ssr.tsx              # Entrée SSR : createServer()
├── Pages/               # Un fichier = une page Inertia
│   └── Home.tsx
└── styles/
    ├── app.scss         # Entrée Sass
    └── _variables.scss
bootstrap/ssr/           # Bundle SSR généré (git-ignoré)
config/packages/
└── inertia.yaml         # Config Inertia + SSR
public/build/            # Assets client générés (git-ignoré)
src/Controller/          # Contrôleurs → $inertia->render()
templates/
└── base.html.twig       # L'unique vue Twig (vue racine Inertia)
tsconfig.json
vite.config.js           # Build client
vite.ssr.config.js       # Build SSR
```

## Pièges connus

| Symptôme | Cause | Solution |
|---|---|---|
| `@vitejs/plugin-react can't detect preamble` | Le script React Refresh n'est pas injecté (la page est servie par Symfony, pas Vite) | `vite_entry_script_tags('app', { dependency: 'react' })` dans `base.html.twig` |
| `ERR_CONNECTION_REFUSED` sur `:5173`, plus de style | `npm run dev` arrêté, mais `public/build/.vite/entrypoints.json` pointe encore vers le serveur Vite | Relancer `npm run dev`, ou `npm run build` pour générer les assets statiques |
| Flash sans style au chargement (dev) | CSS importé depuis le JS + SSR | CSS en entrée Vite séparée (déjà en place) |
| `SSR bundle not found` | Bundle non construit, ou mauvais chemin | `npm run build:ssr` ; le bundle auto-détecte `bootstrap/ssr/ssr.mjs` mais Vite produit `ssr.js` → `ssr_bundle` est défini explicitement dans `inertia.yaml` |
| Le SSR affiche une ancienne version | Le serveur Node charge le bundle une fois au démarrage | `npm run build:ssr` puis redémarrer `inertia:start-ssr` |
| Nouvelle entrée Vite ignorée | `entrypoints.json` écrit au démarrage de Vite | Redémarrer `npm run dev` |

## Ressources

- [Inertia.js](https://inertiajs.com/)
- [nytodev/inertia-bundle](https://github.com/nytodev/inertia-bundle)
- [Pentatrion Vite bundle](https://symfony-vite.pentatrion.com/)
- [Vite](https://vite.dev/)
- [Symfony](https://symfony.com/doc/current/)
