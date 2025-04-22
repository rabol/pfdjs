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

        .image-overlay {
            position: absolute;
            z-index: 10;
            cursor: move;
            touch-action: none;
            transform-origin: center center;
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
            z-index: 20;
        }

        .resize-handle {
            position: absolute;
            width: 8px;
            height: 8px;
            background: #1d4ed8;
            border: 2px solid white;
            border-radius: 2px;
            z-index: 20;
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

        window.addEventListener("message", async function(event) {
            const { type, url } = event.data;

            if (type === 'load-pdf' && url) {
                container.innerHTML = '';
                const loadingTask = pdfjsLib.getDocument(url);
                const pdf = await loadingTask.promise;

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);
                    const viewport = page.getViewport({ scale: 1.5 });

                    const wrapper = document.createElement("div");
                    wrapper.className = "page-wrapper";

                    const canvas = document.createElement("canvas");
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    wrapper.appendChild(canvas);
                    container.appendChild(wrapper);

                    const context = canvas.getContext('2d');
                    await page.render({ canvasContext: context, viewport }).promise;
                }
            }

            if (type === 'add-image' && url) {
                const img = new Image();
                img.src = url;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.pointerEvents = 'none';

                const overlay = document.createElement('div');
                overlay.className = 'image-overlay';
                overlay.style.width = '20%';
                overlay.style.height = '20%';
                overlay.style.top = '40%';
                overlay.style.left = '40%';

                overlay.appendChild(img);

                const delBtn = document.createElement('div');
                delBtn.className = 'image-delete';
                delBtn.textContent = '×';
                delBtn.onclick = () => {
                    overlay.remove();
                    overlayInfo.style.display = 'none';
                };
                overlay.appendChild(delBtn);

                addResizeHandles(overlay);
                addScrollWheelScaling(overlay);

                overlay.addEventListener('mousedown', (e) => {
                    e.stopPropagation();
                    document.querySelectorAll('.image-overlay').forEach(el => el.classList.remove('active'));
                    overlay.classList.add('active');
                    showOverlayInfo(overlay);
                });

                const closestPage = getClosestPage();
                if (!closestPage) return;
                closestPage.appendChild(overlay);
                makeDraggable(overlay);
                showOverlayInfo(overlay);
            }

            if (type === 'save-overlays') {
                const overlays = document.querySelectorAll('.image-overlay');
                const data = [];

                overlays.forEach(overlay => {
                    const parent = overlay.parentElement.getBoundingClientRect();
                    data.push({
                        pageIndex: [...overlay.parentElement.parentElement.children].indexOf(overlay.parentElement),
                        top: parseFloat(overlay.style.top),
                        left: parseFloat(overlay.style.left),
                        width: parseFloat(overlay.style.width),
                        height: parseFloat(overlay.style.height),
                    });
                });

                window.parent.postMessage({ type: 'overlays-saved', overlays: data }, '*');
            }
        });

        function getClosestPage() {
            const pages = Array.from(document.querySelectorAll('.page-wrapper'));
            const centerY = container.scrollTop + container.clientHeight / 2;

            return pages.reduce((closest, page) => {
                const offset = page.offsetTop + page.offsetHeight / 2;
                const distance = Math.abs(offset - centerY);
                return distance < closest.distance ? { el: page, distance } : closest;
            }, { el: null, distance: Infinity }).el;
        }

        function showOverlayInfo(el) {
            overlayInfo.innerHTML = `Top: ${el.style.top} | Left: ${el.style.left} | Width: ${el.style.width} | Height: ${el.style.height} <button onclick="document.getElementById('overlay-info').style.display='none'">Close</button>`;
            overlayInfo.style.display = 'block';
        }

        function makeDraggable(el) {
            let offsetX = 0, offsetY = 0, isDragging = false;
            el.addEventListener('mousedown', (e) => {
                isDragging = true;
                offsetX = e.offsetX;
                offsetY = e.offsetY;
                e.preventDefault();
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                const parent = el.parentElement.getBoundingClientRect();
                const left = ((e.clientX - parent.left - offsetX) / parent.width) * 100;
                const top = ((e.clientY - parent.top - offsetY) / parent.height) * 100;
                el.style.left = `${left}%`;
                el.style.top = `${top}%`;
                showOverlayInfo(el);
            });

            document.addEventListener('mouseup', () => {
                isDragging = false;
            });
        }

        function addResizeHandles(el) {
            const positions = ['tl', 'tr', 'bl', 'br', 't', 'b', 'l', 'r'];
            positions.forEach(pos => {
                const handle = document.createElement('div');
                handle.className = `resize-handle ${pos}`;
                el.appendChild(handle);

                let startX, startY, startW, startH;

                const onMouseMove = (e) => {
                    const parent = el.parentElement.getBoundingClientRect();
                    const dx = (e.clientX - startX) / parent.width * 100;
                    const dy = (e.clientY - startY) / parent.height * 100;

                    if (pos.includes('r')) el.style.width = `${startW + dx}%`;
                    if (pos.includes('l')) {
                        el.style.width = `${startW - dx}%`;
                        el.style.left = `${parseFloat(el.style.left) + dx}%`;
                    }
                    if (pos.includes('b')) el.style.height = `${startH + dy}%`;
                    if (pos.includes('t')) {
                        el.style.height = `${startH - dy}%`;
                        el.style.top = `${parseFloat(el.style.top) + dy}%`;
                    }

                    showOverlayInfo(el);
                };

                handle.addEventListener('mousedown', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    const bounds = el.parentElement.getBoundingClientRect();
                    startX = e.clientX;
                    startY = e.clientY;
                    startW = parseFloat(el.style.width);
                    startH = parseFloat(el.style.height);

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', () => {
                        document.removeEventListener('mousemove', onMouseMove);
                    }, { once: true });
                });
            });
        }

        function addScrollWheelScaling(el) {
            el.addEventListener('wheel', (e) => {
                e.preventDefault();
                const factor = e.deltaY < 0 ? 1.05 : 0.95;
                const w = parseFloat(el.style.width) * factor;
                const h = parseFloat(el.style.height) * factor;
                el.style.width = `${w}%`;
                el.style.height = `${h}%`;
                showOverlayInfo(el);
            });
        }
    </script>
</body>

</html>