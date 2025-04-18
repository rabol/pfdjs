<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>PDF Viewer</title>
        <style>
            html,
            body {
                margin: 0;
                height: 100%;
                overflow: hidden;
            }

            #pdf-container {
                position: relative;
                width: 100%;
                height: 100%;
                background: #f9fafb;
            }

            canvas {
                position: absolute;
                top: 0;
                left: 0;
            }

            .image-overlay {
                position: absolute;
                z-index: 10;
                cursor: move;
            }
        </style>
    </head>

    <body>
        <div id="pdf-container"></div>

        <script type="module">
            import * as pdfjsLib from '/js/pdfjs/build/pdf.mjs';

            // Set worker path
            pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdfjs/build/pdf.worker.mjs';

            const container = document.getElementById('pdf-container');

            window.addEventListener("message", async function(event) {
                const {
                    type,
                    url
                } = event.data;

                // Load the PDF
                if (type === 'load-pdf' && url) {
                    try {
                        container.innerHTML = ''; // Clear previous content

                        const loadingTask = pdfjsLib.getDocument(url);
                        const pdf = await loadingTask.promise;
                        const page = await pdf.getPage(1); // Load first page

                        const viewport = page.getViewport({
                            scale: 1.5
                        });
                        const canvas = document.createElement("canvas");
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        container.appendChild(canvas);

                        const context = canvas.getContext('2d');
                        await page.render({
                            canvasContext: context,
                            viewport
                        }).promise;
                    } catch (error) {
                        console.error('Error loading PDF:', error);
                    }
                }

                // Add image overlay
                if (type === 'add-image' && url) {
                    const img = new Image();
                    img.src = url;
                    img.className = 'image-overlay';
                    img.style.top = '100px';
                    img.style.left = '100px';
                    img.style.width = '150px';
                    container.appendChild(img);
                }
            }, false);
        </script>
    </body>

</html>
