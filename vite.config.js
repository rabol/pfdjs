import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import { viteStaticCopy } from 'vite-plugin-static-copy';


export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css", 
                "resources/js/app.js",
                'resources/js/pdf-viewer.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
        viteStaticCopy({
            targets: [
                {
                    src: 'node_modules/pdfjs-dist/cmaps',
                    dest: 'js/pdfjs'
                }
            ]
        })
    ],
});
