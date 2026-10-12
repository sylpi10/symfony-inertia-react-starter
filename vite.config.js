// import React from "react";
import react from "@vitejs/plugin-react";
import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

/* if you're using React */
// import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        react(),
        // VITE_ORIGIN and VITE_HOST are only set by compose.yaml (Docker)
        symfonyPlugin({ originOverride: process.env.VITE_ORIGIN }),
    ],
    server: {
        host: process.env.VITE_HOST,
    },

    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.tsx",
                styles: "./assets/styles/app.scss",
            },
        },
    },
});
