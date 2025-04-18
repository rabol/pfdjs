<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>PDF Viewer</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style>
            html,
            body {
                margin: 0;
                padding: 0;
                height: 100%;
                overflow: hidden;
            }

            #pdf-container {
                position: relative;
                width: 100%;
                height: 100%;
                background: #f9fafb;
                overflow-y: auto;
                padding: 1rem 0;
                box-sizing: border-box;
            }

            canvas {
                display: block;
                margin: 0 auto 2rem auto;
                max-width: 100%;
                height: auto;
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

                        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                            const page = await pdf.getPage(pageNumber);
                            const viewport = page.getViewport({
                                scale: 1.5
                            });

                            const canvas = document.createElement("canvas");
                            canvas.width = viewport.width;
                            canvas.height = viewport.height;
                            canvas.style.position = 'relative';
                            container.appendChild(canvas);

                            const context = canvas.getContext('2d');
                            await page.render({
                                canvasContext: context,
                                viewport
                            }).promise;
                        }
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

                // Mouse Events
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

                // Touch Events
                el.addEventListener('touchstart', (e) => {
                    isDragging = true;
                    const touch = e.touches[0];
                    offsetX = touch.clientX - el.offsetLeft;
                    offsetY = touch.clientY - el.offsetTop;
                    el.style.zIndex = 1000;
                    e.preventDefault();
                });

                document.addEventListener('touchmove', (e) => {
                    if (!isDragging) return;
                    const touch = e.touches[0];
                    el.style.left = (touch.clientX - offsetX) + 'px';
                    el.style.top = (touch.clientY - offsetY) + 'px';
                });

                document.addEventListener('touchend', () => {
                    isDragging = false;
                    el.style.zIndex = 10;
                });
            }
        </script>
    </body>

</html>
