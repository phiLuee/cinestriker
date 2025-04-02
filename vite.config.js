import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),

        tailwindcss(),
    ],
    server: {
        hmr: {
            host: "localhost",
            port: 5174,
        },
        watch: {
            usePolling: true,
        },
        host: true,
        port: 5174,
        strictPort: true,
    },
});
