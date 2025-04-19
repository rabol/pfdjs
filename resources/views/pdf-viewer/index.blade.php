<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <title>PDF Viewer</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
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
                cursor: grab;
                touch-action: none;
                user-select: none;
            }

            .resize-handle,
            .image-delete {
                display: none;
            }

            .image-overlay.active .resize-handle,
            .image-overlay.active .image-delete {
                display: block;
            }

            .resize-handle {
                position: absolute;
                width: 6px;
                height: 6px;
                background: #1d4ed8;
                border: 2px solid white;
                border-radius: 2px;
                z-index: 20;
                touch-action: none;
            }

            .handle-tl {
                top: -4px;
                left: -4px;
                cursor: nwse-resize;
            }

            .handle-tr {
                top: -4px;
                right: -4px;
                cursor: nesw-resize;
            }

            .handle-bl {
                bottom: -4px;
                left: -4px;
                cursor: nesw-resize;
            }

            .handle-br {
                bottom: -4px;
                right: -4px;
                cursor: nwse-resize;
            }

            .handle-tc {
                top: -4px;
                left: 50%;
                transform: translateX(-50%);
                cursor: ns-resize;
            }

            .handle-bc {
                bottom: -4px;
                left: 50%;
                transform: translateX(-50%);
                cursor: ns-resize;
            }

            .handle-lc {
                left: -4px;
                top: 50%;
                transform: translateY(-50%);
                cursor: ew-resize;
            }

            .handle-rc {
                right: -4px;
                top: 50%;
                transform: translateY(-50%);
                cursor: ew-resize;
            }

            .image-delete {
                position: absolute;
                top: -20px;
                right: -20px;
                width: 20px;
                height: 20px;
                background: #ef4444;
                color: white;
                font-size: 14px;
                font-weight: bold;
                line-height: 20px;
                text-align: center;
                border-radius: 9999px;
                cursor: pointer;
                z-index: 30;
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
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.pointerEvents = 'none';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'image-overlay';
                    wrapper.style.width = '150px';
                    wrapper.style.height = '150px';

                    const canvases = Array.from(document.querySelectorAll('canvas'));
                    const containerScrollTop = container.scrollTop;
                    const containerCenter = containerScrollTop + container.clientHeight / 2;

                    let closestCanvas = null;
                    let closestDistance = Infinity;

                    canvases.forEach(canvas => {
                        const canvasCenter = canvas.offsetTop + canvas.clientHeight / 2;
                        const distance = Math.abs(canvasCenter - containerCenter);
                        if (distance < closestDistance) {
                            closestDistance = distance;
                            closestCanvas = canvas;
                        }
                    });

                    if (!closestCanvas) return;

                    // Place in the vertical center of the visible page
                    const canvasTop = closestCanvas.offsetTop;
                    const canvasLeft = closestCanvas.offsetLeft;
                    const canvasHeight = closestCanvas.clientHeight;
                    const canvasWidth = closestCanvas.clientWidth;

                    // Centered within canvas
                    const top = canvasTop + canvasHeight / 2 - 75; // 75 = half of 150px image
                    const left = canvasLeft + canvasWidth / 2 - 75;

                    wrapper.style.top = `${top}px`;
                    wrapper.style.left = `${left}px`;

                    wrapper.appendChild(img);

                    const delBtn = document.createElement('div');
                    delBtn.className = 'image-delete';
                    delBtn.textContent = '×';
                    delBtn.title = 'Delete image';
                    delBtn.onclick = () => wrapper.remove();
                    wrapper.appendChild(delBtn);

                    addResizeHandles(wrapper);
                    addScrollWheelScaling(wrapper);

                    wrapper.addEventListener('mousedown', (e) => {
                        e.stopPropagation();
                        document.querySelectorAll('.image-overlay').forEach(el => el.classList.remove('active'));
                        wrapper.classList.add('active');
                    });

                    container.appendChild(wrapper);
                    makeDraggable(wrapper);
                }

                if (type === 'export-overlays') {
                    const overlays = Array.from(document.querySelectorAll('.image-overlay')).map(el => ({
                        top: parseInt(el.style.top || '0', 10),
                        left: parseInt(el.style.left || '0', 10),
                        width: parseInt(el.style.width || '0', 10),
                        height: parseInt(el.style.height || '0', 10),
                        src: el.querySelector('img')?.src || null
                    }));

                    parent.postMessage({
                        type: 'overlays-exported',
                        data: overlays
                    }, '*');
                }
            });

            document.addEventListener('mousedown', () => {
                document.querySelectorAll('.image-overlay').forEach(el => el.classList.remove('active'));
            });

            function makeDraggable(el) {
                let offsetX = 0,
                    offsetY = 0;
                let isDragging = false;

                el.addEventListener('mousedown', (e) => {
                    if (e.target.classList.contains('resize-handle') || e.target.classList.contains('image-delete')) return;
                    isDragging = true;
                    offsetX = e.clientX - el.offsetLeft;
                    offsetY = e.clientY - el.offsetTop;
                    e.preventDefault();
                });

                document.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    el.style.left = (e.clientX - offsetX) + 'px';
                    el.style.top = (e.clientY - offsetY) + 'px';
                });

                document.addEventListener('mouseup', () => {
                    isDragging = false;
                });
            }

            function addResizeHandles(el) {
                const handles = ['tl', 'tr', 'bl', 'br', 'tc', 'bc', 'lc', 'rc'];
                handles.forEach(pos => {
                    const h = document.createElement('div');
                    h.className = `resize-handle handle-${pos}`;
                    el.appendChild(h);
                    addResizeEvents(el, h, pos);
                });
            }

            function addResizeEvents(el, handle, position) {
                let isResizing = false;
                let startX = 0,
                    startY = 0;
                let startWidth = 0,
                    startHeight = 0;
                let startTop = 0,
                    startLeft = 0;

                handle.addEventListener('mousedown', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    isResizing = true;
                    startX = e.clientX;
                    startY = e.clientY;
                    startWidth = el.offsetWidth;
                    startHeight = el.offsetHeight;
                    startTop = el.offsetTop;
                    startLeft = el.offsetLeft;
                    document.body.style.cursor = handle.style.cursor;
                });

                document.addEventListener('mousemove', (e) => {
                    if (!isResizing) return;
                    let dx = e.clientX - startX;
                    let dy = e.clientY - startY;

                    let newWidth = startWidth;
                    let newHeight = startHeight;
                    let newTop = startTop;
                    let newLeft = startLeft;

                    if (position === 'br') {
                        newWidth = startWidth + dx;
                        newHeight = startHeight + dy;
                    } else if (position === 'tr') {
                        newWidth = startWidth + dx;
                        newHeight = startHeight - dy;
                        newTop = startTop + dy;
                    } else if (position === 'bl') {
                        newWidth = startWidth - dx;
                        newHeight = startHeight + dy;
                        newLeft = startLeft + dx;
                    } else if (position === 'tl') {
                        newWidth = startWidth - dx;
                        newHeight = startHeight - dy;
                        newLeft = startLeft + dx;
                        newTop = startTop + dy;
                    } else if (position === 'tc') {
                        newHeight = startHeight - dy;
                        newTop = startTop + dy;
                    } else if (position === 'bc') {
                        newHeight = startHeight + dy;
                    } else if (position === 'lc') {
                        newWidth = startWidth - dx;
                        newLeft = startLeft + dx;
                    } else if (position === 'rc') {
                        newWidth = startWidth + dx;
                    }

                    el.style.width = `${Math.max(30, newWidth)}px`;
                    el.style.height = `${Math.max(30, newHeight)}px`;
                    el.style.top = `${newTop}px`;
                    el.style.left = `${newLeft}px`;
                });

                document.addEventListener('mouseup', () => {
                    isResizing = false;
                    document.body.style.cursor = '';
                });
            }

            function addScrollWheelScaling(el) {
                let scale = 1;
                el.addEventListener('wheel', (e) => {
                    e.preventDefault();
                    const direction = Math.sign(e.deltaY);
                    scale *= direction < 0 ? 1.1 : 0.9;
                    scale = Math.max(0.3, Math.min(scale, 5));
                    el.style.width = `${150 * scale}px`;
                    el.style.height = `${150 * scale}px`;
                });
            }
        </script>
    </body>

</html>
