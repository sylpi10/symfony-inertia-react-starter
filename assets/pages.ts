import type { ComponentType, ReactNode } from "react";
import AppLayout from "./Layouts/AppLayout";
import GuestLayout from "./Layouts/GuestLayout";

type PageModule = { default: ComponentType<any> };

const pages = import.meta.glob<PageModule>("./Pages/**/*.tsx");

export function resolvePage(name: string) {
    const page = pages[`./Pages/${name}.tsx`];
    if (!page)
        throw new Error(
            `Inertia page not found: ${name} (expected assets/Pages/${name}.tsx)`,
        );
    return page().then((module) => module.default);
}

export function resolveLayout(
    name: string,
): ComponentType<{ children: ReactNode }> {
    return name.startsWith("Auth/") ? GuestLayout : AppLayout;
}
