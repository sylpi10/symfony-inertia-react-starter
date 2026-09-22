import type { ComponentType } from "react";

type PageModule = { default: ComponentType<any> };

const pages = import.meta.glob<PageModule>("./*Page/*.tsx");

export function resolvePage(name: string) {
    const page = pages[`./${name}Page/${name}.tsx`];
    if (!page)
        throw new Error(
            `Page Inertia introuvable : ${name} (attendu : assets/${name}Page/${name}.tsx)`,
        );
    return page().then((module) => module.default);
}
