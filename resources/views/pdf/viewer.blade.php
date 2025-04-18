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
            pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdfjs/build/pdf.worker.mjs';

            const container = document.getElementById('pdf-container');

            window.addEventListener("message", async function(event) {
                const {
                    type,
                    url
                } = event.data;
                console.log("iframe received message", event.data);

                if (type === 'load-pdf' && url) {
                    try {
                        container.innerHTML = '';
                        const loadingTask = pdfjsLib.getDocument(url);
                        const pdf = await loadingTask.promise;
                        const page = await pdf.getPage(1);
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

                if (type === 'add-image' && url) {
                    const img = new Image();
                    img.src = url;
                    img.className = 'image-overlay';
                    img.style.top = '100px';
                    img.style.left = '100px';
                    img.style.width = '150px';
                    img.draggable = false;
                    container.appendChild(img);

                    makeDraggable(img);
                }
            }, false);

            function makeDraggable(el) {
                let offsetX = 0;
                let offsetY = 0;
                let isDragging = false;

                el.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    offsetX = e.clientX - el.offsetLeft;
                    offsetY = e.clientY - el.offsetTop;
                    el.style.zIndex = 1000;
                    e.preventDefault();
                });

                document.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    el.style.left = (e.clientX - offsetX) + 'px';
                    el.style.top = (e.clientY - offsetY) + 'px';
                });

                document.addEventListener('mouseup', () => {
                    isDragging = false;
                    el.style.zIndex = 10;
                });
            }
        </script>
    </body>

</html>
