import { createInertiaApp } from "@inertiajs/react";
import createServer from "@inertiajs/react/server";
import { renderToString } from "react-dom/server";
import { resolveLayout, resolvePage } from "./pages";

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: resolvePage,
        layout: resolveLayout,
        setup: ({ App, props }) => <App {...props} />,
    }),
);
