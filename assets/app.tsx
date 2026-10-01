import { createInertiaApp } from "@inertiajs/react";
import { resolveLayout, resolvePage } from "./pages";

createInertiaApp({
    resolve: resolvePage,
    layout: resolveLayout,
});
