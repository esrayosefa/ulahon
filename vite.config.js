import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js", // ← jalur entry SPA kamu
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
        },
    },
    server: {
        host: "localhost",
        port: 5173,
        proxy: {
            "/login": "http://127.0.0.1:8000",
            "/logout": "http://127.0.0.1:8000",
            "/sanctum": "http://127.0.0.1:8000",
            "/api": "http://127.0.0.1:8000",
        },
    },
});
