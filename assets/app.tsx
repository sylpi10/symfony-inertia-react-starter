import { createInertiaApp } from "@inertiajs/react";
import { resolvePage } from "./resolvePage";

createInertiaApp({
    resolve: resolvePage,
});
