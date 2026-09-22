// import React from "react";
import react from "@vitejs/plugin-react";
import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

/* if you're using React */
// import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [react(), symfonyPlugin()],
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.tsx",
                styles: "./assets/styles/app.scss",
            },
        },
    },
});
