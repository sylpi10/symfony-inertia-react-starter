import { createInertiaApp } from "@inertiajs/react";
import { resolvePage } from "./resolvePage";
import Layout from "./components/Layout";

createInertiaApp({
    resolve: resolvePage,
    layout: () => Layout,
});
