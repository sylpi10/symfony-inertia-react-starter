import { createInertiaApp } from "@inertiajs/react";
import createServer from "@inertiajs/react/server";
import { renderToString } from "react-dom/server";
import { resolvePage } from "./resolvePage";
import Layout from "./components/Layout";

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: resolvePage,
        layout: () => Layout,
        setup: ({ App, props }) => <App {...props} />,
    }),
);
