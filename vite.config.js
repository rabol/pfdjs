import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import { viteStaticCopy } from "vite-plugin-static-copy";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
        viteStaticCopy({
            targets: [
                {
                    src: "./node_modules/pdfjs-dist/build",
                    dest: "../js/pdfjs",
                },
                {
                    src: "./node_modules/pdfjs-dist/cmaps",
                    dest: "../js/pdfjs/cmaps",
                },
                {
                    src: "./node_modules/pdfjs-dist/web",
                    dest: "../js/pdfjs/",
                },
                {
                    src: "resources/js/pageviewer.mjs",
                    dest: "../js/pdfjs",
                },
                {
                    src: "resources/js/simpleviewer.mjs",
                    dest: "../js/pdfjs",
                },
            ],
        }),
    ],
});
