# SEO

← [Back to README](../README.md)

## Head tags, page by page

Every page receives a `seo` prop, an `App\Dto\SeoDto`:

| Field         | Tag                                           |
| ------------- | --------------------------------------------- |
| `title`       | `<title>`, `og:title`                         |
| `description` | `<meta name="description">`, `og:description` |
| `canonical`   | `<link rel="canonical">`, `og:url`            |
| `image`       | `og:image` (and a large Twitter card)         |
| `noindex`     | `<meta name="robots" content="noindex">`      |

Controllers build it with `App\Seo\SeoFactory`, which has two methods:

```php
// A public page, meant to be indexed: title, description and the route used for the canonical URL
seo: $this->seo->page("About", "What this starter includes…", "app_about"),

// A page search engines must ignore (authentication, admin, errors)
"seo" => $this->seo->noindex("Sign in"),
```

- The factory appends the site name to every title (`About · Symfony Inertia Starter`). Change it in `config/services.yaml` (`app.site_name`).
- `canonical` and `image` are **absolute URLs**, as crawlers require. They are built from the current request, so make sure Symfony knows its public host behind a reverse proxy ([trusted proxies](https://symfony.com/doc/current/deployment/proxies.html)).
- The share image is `public/og-image.png` (1200×630): replace it with yours. It lives in `public/` because Vite hashes the file names of `assets/`, so PHP cannot know their URL.

**Indexing is opt-in.** A page that passes no `seo` prop gets a default one, shared by `InertiaShareListener`: the site name as title, and `noindex`. Forgetting the prop on a new admin page therefore never exposes it to search engines; a public page must call `page()` explicitly.

## Where the tags are rendered

The layouts (`AppLayout`, `GuestLayout`) render `assets/Components/Seo.tsx`, which writes the tags with Inertia's `<Head>`. On client-side navigation, the tags follow the page.

On the first load, they must be in the HTML sent by the server, because social networks' crawlers don't run JavaScript:

| SSR          | Who writes the tags in the HTML                                 |
| ------------ | --------------------------------------------------------------- |
| On           | React, through the Node SSR server                              |
| Off, or down | Twig, with `templates/_seo.html.twig`, from the same `seo` prop |

So titles, descriptions and Open Graph tags are correct **even without SSR**. The Twig tags carry a `data-inertia` attribute, which lets the React component take them over once the page is loaded.

> `Seo.tsx` and `_seo.html.twig` list the same tags: when you add one, add it to both.

What SSR still brings is the **content** of the page in the HTML, for crawlers that don't run JavaScript. See [SSR and production](ssr-and-production.md).

`<html lang>` is the request locale (`en` by default, `framework.default_locale`).

## Sitemap and robots.txt

| URL            | Source                                                                      |
| -------------- | --------------------------------------------------------------------------- |
| `/sitemap.xml` | `SitemapController::ROUTES`, the list of public routes: **add yours there** |
| `/robots.txt`  | `templates/seo/robots.txt.twig`: disallows `/admin`, points to the sitemap  |

For pages coming from the database (articles, products…), query them in `SitemapController` and add their URLs to the template.

Authentication pages are not disallowed in `robots.txt` on purpose: a crawler must be able to fetch them to see their `noindex` tag.

## Tests

- `tests/Controller/SeoTest.php`: the `seo` prop of public, authentication, admin and error pages; the head tags in the HTML without SSR; the sitemap and `robots.txt`.
- `make test-ssr` (`tests/ssr-smoke-test.sh`): builds the assets, starts the Node SSR server and checks that the home page comes back server-rendered, with a single `<title>`, its description and its canonical link. It replaces the files of the Vite dev server: restart `make dev` afterwards.
