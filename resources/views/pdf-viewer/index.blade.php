<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <title>PDF Viewer</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style>
            html,
            body {
                margin: 0;
                height: 100%;
                overflow: hidden;
            }

            #pdf-container {
                width: 100%;
                height: 100%;
                background: #f9fafb;
                overflow-y: auto;
                padding: 1rem 0;
                box-sizing: border-box;
            }

            .page-wrapper {
                position: relative;
                width: fit-content;
                margin: 0 auto 2rem auto;
            }

            canvas {
                display: block;
                max-width: 100%;
                height: auto;
            }

            .overlay-layer {
                position: absolute;
                top: 0;
                left: 0;
                transform-origin: top left;
                pointer-events: none;
                z-index: 10;
            }

            .image-overlay {
                position: absolute;
                z-index: 20;
                cursor: move;
                touch-action: none;
                transform-origin: center center;
                pointer-events: auto;
            }

            .image-overlay.active {
                outline: 2px solid #2563eb;
            }

            .image-delete {
                position: absolute;
                top: -14px;
                right: -14px;
                background: #ef4444;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                font-size: 14px;
                font-weight: bold;
                text-align: center;
                line-height: 20px;
                cursor: pointer;
                z-index: 30;
            }

            .resize-handle {
                position: absolute;
                width: 8px;
                height: 8px;
                background: #1d4ed8;
                border: 2px solid white;
                border-radius: 2px;
                z-index: 30;
                touch-action: none;
            }

            .resize-handle.br {
                right: -6px;
                bottom: -6px;
                cursor: nwse-resize;
            }

            .resize-handle.bl {
                left: -6px;
                bottom: -6px;
                cursor: nesw-resize;
            }

            .resize-handle.tr {
                right: -6px;
                top: -6px;
                cursor: nesw-resize;
            }

            .resize-handle.tl {
                left: -6px;
                top: -6px;
                cursor: nwse-resize;
            }

            .resize-handle.t {
                top: -6px;
                left: 50%;
                transform: translateX(-50%);
                cursor: ns-resize;
            }

            .resize-handle.b {
                bottom: -6px;
                left: 50%;
                transform: translateX(-50%);
                cursor: ns-resize;
            }

            .resize-handle.l {
                left: -6px;
                top: 50%;
                transform: translateY(-50%);
                cursor: ew-resize;
            }

            .resize-handle.r {
                right: -6px;
                top: 50%;
                transform: translateY(-50%);
                cursor: ew-resize;
            }

            #overlay-info {
                position: fixed;
                bottom: 1rem;
                right: 1rem;
                background: #2563eb;
                color: white;
                padding: 0.75rem 1rem;
                border-radius: 6px;
                font-size: 0.875rem;
                z-index: 100;
                display: none;
            }

            #overlay-info button {
                background: white;
                color: #2563eb;
                font-weight: bold;
                border: none;
                padding: 2px 6px;
                margin-left: 1rem;
                cursor: pointer;
                border-radius: 4px;
            }
        </style>
    </head>

    <body>
        <div id="pdf-container"></div>
        <div id="overlay-info"></div>

        <script type="module">
            import * as pdfjsLib from '/js/pdfjs/build/pdf.mjs';
            pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdfjs/build/pdf.worker.mjs';

            const container = document.getElementById('pdf-container');
            const overlayInfo = document.getElementById('overlay-info');

            const scaleOverlayLayer = () => {
                const wrappers = document.querySelectorAll('.page-wrapper');
                wrappers.forEach(wrapper => {
                    const canvas = wrapper.querySelector('canvas');
                    const overlayLayer = wrapper.querySelector('.overlay-layer');

                    const canvasRect = canvas.getBoundingClientRect();
                    const originalWidth = canvas.width;
                    const renderedWidth = canvasRect.width;

                    const scaleFactor = renderedWidth / originalWidth;
                    overlayLayer.style.transform = `scale(${scaleFactor})`;
                });
            };

            new ResizeObserver(scaleOverlayLayer).observe(container);
            window.addEventListener('resize', scaleOverlayLayer);

            window.addEventListener("message", async function(event) {
                const {
                    type,
                    url
                } = event.data;

                if (type === 'load-pdf' && url) {
                    container.innerHTML = '';
                    const loadingTask = pdfjsLib.getDocument(url);
                    const pdf = await loadingTask.promise;

                    for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                        const page = await pdf.getPage(pageNumber);
                        const viewport = page.getViewport({
                            scale: 1.5
                        });

                        const wrapper = document.createElement("div");
                        wrapper.className = "page-wrapper";

                        const canvas = document.createElement("canvas");
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        const overlayLayer = document.createElement("div");
                        overlayLayer.className = "overlay-layer";
                        overlayLayer.style.width = `${canvas.width}px`;
                        overlayLayer.style.height = `${canvas.height}px`;

                        wrapper.appendChild(canvas);
                        wrapper.appendChild(overlayLayer);
                        container.appendChild(wrapper);

                        const context = canvas.getContext('2d');
                        await page.render({
                            canvasContext: context,
                            viewport
                        }).promise;
                    }

                    scaleOverlayLayer();
                }

                if (type === 'add-image' && url) {
                    const wrapper = getClosestPage();
                    if (!wrapper) return;

                    const overlayLayer = wrapper.querySelector('.overlay-layer');
                    const img = new Image();
                    img.src = url;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.pointerEvents = 'none';

                    const overlay = document.createElement('div');
                    overlay.className = 'image-overlay';
                    overlay.style.width = '20%';
                    overlay.style.height = '20%';
                    overlay.style.left = '40%';
                    overlay.style.top = '40%';

                    overlay.appendChild(img);

                    const delBtn = document.createElement('div');
                    delBtn.className = 'image-delete';
                    delBtn.textContent = '×';
                    delBtn.onclick = () => {
                        overlay.remove();
                        overlayInfo.style.display = 'none';
                    };
                    overlay.appendChild(delBtn);

                    addResizeHandles(overlay, overlayLayer);
                    addScrollWheelScaling(overlay);
                    overlay.addEventListener('mousedown', (e) => {
                        e.stopPropagation();
                        document.querySelectorAll('.image-overlay').forEach(el => el.classList.remove('active'));
                        overlay.classList.add('active');
                        showOverlayInfo(overlay);
                    });

                    overlayLayer.appendChild(overlay);
                    makeDraggable(overlay, overlayLayer);
                    showOverlayInfo(overlay);
                }

                if (type === 'save-overlays') {
                    const overlays = document.querySelectorAll('.image-overlay');
                    const data = [];

                    overlays.forEach(overlay => {
                        data.push({
                            top: parseFloat(overlay.style.top),
                            left: parseFloat(overlay.style.left),
                            width: parseFloat(overlay.style.width),
                            height: parseFloat(overlay.style.height)
                        });
                    });

                    window.parent.postMessage({
                        type: 'overlays-saved',
                        overlays: data
                    }, '*');
                }
            });

            function getClosestPage() {
                const pages = Array.from(document.querySelectorAll('.page-wrapper'));
                const centerY = container.scrollTop + container.clientHeight / 2;

                return pages.reduce((closest, page) => {
                    const offset = page.offsetTop + page.offsetHeight / 2;
                    const distance = Math.abs(offset - centerY);
                    return distance < closest.distance ? {
                        el: page,
                        distance
                    } : closest;
                }, {
                    el: null,
                    distance: Infinity
                }).el;
            }

            function showOverlayInfo(el) {
                overlayInfo.innerHTML = `
            Top: ${parseFloat(el.style.top).toFixed(2)}% |
            Left: ${parseFloat(el.style.left).toFixed(2)}% |
            Width: ${parseFloat(el.style.width).toFixed(2)}% |
            Height: ${parseFloat(el.style.height).toFixed(2)}%
            <button onclick="document.getElementById('overlay-info').style.display='none'">Close</button>
        `;
                overlayInfo.style.display = 'block';
            }

            function makeDraggable(el, container) {
                let offsetX = 0,
                    offsetY = 0,
                    isDragging = false;

                el.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    offsetX = e.offsetX;
                    offsetY = e.offsetY;
                    e.preventDefault();
                });

                document.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    const bounds = container.getBoundingClientRect();
                    const left = ((e.clientX - bounds.left - offsetX) / bounds.width) * 100;
                    const top = ((e.clientY - bounds.top - offsetY) / bounds.height) * 100;

                    el.style.left = `${Math.min(Math.max(left, 0), 100 - parseFloat(el.style.width))}%`;
                    el.style.top = `${Math.min(Math.max(top, 0), 100 - parseFloat(el.style.height))}%`;
                    showOverlayInfo(el);
                });

                document.addEventListener('mouseup', () => {
                    isDragging = false;
                });
            }

            function addResizeHandles(el, container) {
                const positions = ['tl', 'tr', 'bl', 'br', 't', 'b', 'l', 'r'];
                positions.forEach(pos => {
                    const handle = document.createElement('div');
                    handle.className = `resize-handle ${pos}`;
                    el.appendChild(handle);

                    let startX, startY, startW, startH, startL, startT;

                    const onMouseMove = (e) => {
                        const bounds = container.getBoundingClientRect();
                        const dx = (e.clientX - startX) / bounds.width * 100;
                        const dy = (e.clientY - startY) / bounds.height * 100;

                        let newW = startW;
                        let newH = startH;
                        let newL = startL;
                        let newT = startT;

                        if (pos.includes('r')) newW = Math.max(5, startW + dx);
                        if (pos.includes('l')) {
                            newW = Math.max(5, startW - dx);
                            newL = Math.max(0, startL + dx);
                        }
                        if (pos.includes('b')) newH = Math.max(5, startH + dy);
                        if (pos.includes('t')) {
                            newH = Math.max(5, startH - dy);
                            newT = Math.max(0, startT + dy);
                        }

                        newW = Math.min(newW, 100 - newL);
                        newH = Math.min(newH, 100 - newT);

                        el.style.width = `${newW}%`;
                        el.style.height = `${newH}%`;
                        el.style.left = `${newL}%`;
                        el.style.top = `${newT}%`;
                        showOverlayInfo(el);
                    };

                    handle.addEventListener('mousedown', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        startX = e.clientX;
                        startY = e.clientY;
                        startW = parseFloat(el.style.width);
                        startH = parseFloat(el.style.height);
                        startL = parseFloat(el.style.left);
                        startT = parseFloat(el.style.top);

                        document.addEventListener('mousemove', onMouseMove);
                        document.addEventListener('mouseup', () => {
                            document.removeEventListener('mousemove', onMouseMove);
                        }, {
                            once: true
                        });
                    });
                });
            }

            function addScrollWheelScaling(el) {
                el.addEventListener('wheel', (e) => {
                    e.preventDefault();
                    const factor = e.deltaY < 0 ? 1.05 : 0.95;
                    const w = Math.max(5, parseFloat(el.style.width) * factor);
                    const h = Math.max(5, parseFloat(el.style.height) * factor);
                    el.style.width = `${w}%`;
                    el.style.height = `${h}%`;
                    showOverlayInfo(el);
                });
            }
        </script>
    </body>

</html>
