# TypeScript types generated from PHP

← [Back to README](../README.md)

PHP DTOs are the single source of truth for the data sent to React. Their TypeScript types are **generated**, never written by hand, with [`spatie/typescript-transformer`](https://github.com/spatie/typescript-transformer).

```
PHP : #[TypeScript] final readonly class TechnoDto { int $id; string $name; ?string $url; }
                │
                │  composer types
                ▼
TS  : assets/types/generated.ts → export type Techno = { readonly id: number; readonly name: string; readonly url: string | null }
```

## Rules

- **Never pass a Doctrine entity to `render()`**: pass a DTO. An entity would expose every getter (including password hashes) in the page JSON.
- **Mark DTOs with `#[TypeScript]`** to include them in `generated.ts`. Only marked classes are exported.
- **Never edit `assets/types/generated.ts` by hand**: change the PHP DTO, then run `composer types`.
- **Commit `generated.ts`**: the front end must compile without running PHP, and CI checks the file is up to date.

## Output DTOs: one per page

```php
// src/Dto/Page/HomePageProps.php
#[TypeScript]
final readonly class HomePageProps
{
    /**
     * @param list<TechnoDto> $technos
     */
    public function __construct(
        public string $title,
        public string $para,
        public array $technos,
    ) {
    }
}

// Controller
return $this->inertia->render('Home', get_object_vars(new HomePageProps(
    title: 'Home Page',
    para: '...',
    technos: array_map(TechnoDto::fromEntity(...), $this->technoRepository->findAllOrdered()),
)));
```

```tsx
// assets/Pages/Home.tsx
import type { HomePageProps } from "../types/generated";

export default function Home({ title, para, technos }: HomePageProps) {
```

`get_object_vars()` turns the DTO into the array `render()` expects. Entity → DTO conversion lives in a static `fromEntity()` method.

## How PHP types are converted

| PHP                                  | TypeScript                    |
| ------------------------------------ | ----------------------------- |
| `string`, `int`, `float`, `bool`     | `string`, `number`, `number`, `boolean` |
| `?string`                            | `string \| null`              |
| `array` + `@param list<string>`      | `string[]`                    |
| `array` + `@param list<TechnoDto>`   | `Techno[]`                    |
| backed `enum`                        | union: `'active' \| 'banned'` |
| `readonly` class                     | `readonly` properties         |

Without a PHPDoc type, an `array` property can't be typed precisely: always annotate arrays.

`#[TypeScript(name: 'User')]` renames the exported type (`UserDto` → `User`).

## Shared props

Shared props and flash data are typed once in `assets/types/inertia.d.ts`, using the generated types:

```ts
import "@inertiajs/core";
import type { User } from "./generated";

declare module "@inertiajs/core" {
    export interface InertiaConfig {
        sharedPageProps: { auth: { user: User | null } };
        flashDataType: { success?: string; error?: string };
    }
}
```

`usePage().props.auth.user` is then typed `User | null` everywhere.

## How it's wired

- `src/Command/GenerateTypeScriptCommand.php` (`app:typescript:generate`, dev environment only) scans `src/`, converts classes marked `#[TypeScript]` and backed enums, and writes `assets/types/generated.ts`.
- The file is formatted with Prettier (`.prettierrc`) right after generation, so regenerating an unchanged DTO produces an identical file. This keeps the CI drift check (`composer types && git diff --exit-code`) reliable even if your editor formats on save.
- `composer types` is a shortcut for the command.
