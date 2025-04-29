import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import 'pdfjs-dist/web/pdf_viewer.css';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
  'pdfjs-dist/build/pdf.worker.min.mjs',
  import.meta.url
).toString();

const overlaysData = [];

window.pdfViewer = function () {
  return {
    async initPdfViewer(pdfPath) {
      const container = document.getElementById('viewerContainer');
      const pagesContainer = document.getElementById('pdfPages');
      container.scrollTop = 0;
      pagesContainer.innerHTML = '';

      const loadingTask = pdfjsLib.getDocument({
        url: pdfPath,
        enableXfa: true,
        cMapUrl: '/js/pdfjs/cmaps/',
        cMapPacked: true,
      });

      const pdf = await loadingTask.promise;

      for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
        const page = await pdf.getPage(pageNumber);
        const viewport = page.getViewport({ scale: 1.5 });

        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;

        canvas.style.display = 'block';
        canvas.style.width = '100%';
        canvas.style.height = 'auto';
        canvas.style.boxSizing = 'border-box';
        canvas.style.margin = '0';
        canvas.style.padding = '0';
        canvas.style.border = 'none';

        const wrapper = document.createElement('div');
        wrapper.className = 'page-wrapper bg-white shadow overflow-hidden';
        wrapper.style.position = 'relative';
        wrapper.style.margin = '0';
        wrapper.style.padding = '0';
        wrapper.style.width = '100%';
        wrapper.style.maxWidth = '100%';
        wrapper.style.display = 'block';

        const overlayLayer = document.createElement('div');
        overlayLayer.className = 'overlay-layer';
        overlayLayer.style.position = 'absolute';
        overlayLayer.style.top = '0';
        overlayLayer.style.left = '0';
        overlayLayer.style.width = `${canvas.width}px`;
        overlayLayer.style.height = `${canvas.height}px`;
        overlayLayer.dataset.pageNumber = pageNumber;

        wrapper.appendChild(canvas);
        wrapper.appendChild(overlayLayer);
        pagesContainer.appendChild(wrapper);

        const context = canvas.getContext('2d');
        await page.render({ canvasContext: context, viewport }).promise;
      }

      scaleOverlayLayers();
      new ResizeObserver(scaleOverlayLayers).observe(container);
      window.addEventListener('resize', scaleOverlayLayers);

      restoreOverlays();
    }
  };
};

function scaleOverlayLayers() {
  const wrappers = document.querySelectorAll('.page-wrapper');
  wrappers.forEach(wrapper => {
    const canvas = wrapper.querySelector('canvas');
    const overlayLayer = wrapper.querySelector('.overlay-layer');

    const canvasRect = canvas.getBoundingClientRect();
    const originalWidth = canvas.width;
    const renderedWidth = canvasRect.width;

    const scaleFactor = renderedWidth / originalWidth;
    overlayLayer.style.transformOrigin = 'top left';
    overlayLayer.style.transform = `scale(${scaleFactor})`;
  });
}

window.addEventListener('overlayUploaded', e => {
  const imagePath = e.detail.path;
  const wrapper = getClosestPage();
  if (!wrapper) return;

  const overlayLayer = wrapper.querySelector('.overlay-layer');

  const overlayInfo = {
    pageNumber: parseInt(overlayLayer.dataset.pageNumber, 10),
    left: 40,
    top: 40,
    width: 20,
    height: 20,
    src: imagePath,
  };

  overlaysData.push(overlayInfo);
  insertOverlay(overlayInfo);
});

function insertOverlay(overlayInfo) {
  const overlayLayer = getOverlayLayerByPageNumber(overlayInfo.pageNumber);
  if (!overlayLayer) return;

  const wrapper = document.createElement('div');
  wrapper.className = 'overlay-wrapper';
  wrapper.style.position = 'absolute';
  wrapper.style.left = `${overlayInfo.left}%`;
  wrapper.style.top = `${overlayInfo.top}%`;
  wrapper.style.width = `${overlayInfo.width}%`;
  wrapper.style.height = `${overlayInfo.height}%`;
  wrapper.style.border = '2px dashed red';
  wrapper.style.zIndex = '1000';
  wrapper.style.cursor = 'move';
  wrapper.style.pointerEvents = 'auto';
  wrapper.style.background = '#f9f9f9';

  const img = new Image();
  img.src = overlayInfo.src;
  img.style.width = '100%';
  img.style.height = '100%';
  img.style.pointerEvents = 'none';

  const deleteButton = document.createElement('button');
  deleteButton.innerText = '✖';
  deleteButton.style.position = 'absolute';
  deleteButton.style.top = '0';
  deleteButton.style.right = '0';
  deleteButton.style.background = 'red';
  deleteButton.style.color = 'white';
  deleteButton.style.border = 'none';
  deleteButton.style.padding = '2px 5px';
  deleteButton.style.cursor = 'pointer';
  deleteButton.style.zIndex = '1001';
  deleteButton.style.fontSize = '14px';

  deleteButton.addEventListener('click', (event) => {
    event.stopPropagation();
    const index = overlaysData.indexOf(overlayInfo);
    if (index !== -1) overlaysData.splice(index, 1);
    wrapper.remove();
  });

  wrapper.appendChild(deleteButton);
  wrapper.appendChild(img);
  overlayLayer.appendChild(wrapper);

  makeDraggable(wrapper, overlayInfo, overlayLayer);
  addResizeHandles(wrapper, overlayInfo, overlayLayer);
}

function restoreOverlays() {
  overlaysData.forEach(insertOverlay);
}

function getClosestPage() {
  const container = document.getElementById('viewerContainer');
  const pages = Array.from(container.querySelectorAll('.page-wrapper'));
  const containerTop = container.scrollTop;
  const containerCenter = containerTop + container.clientHeight / 2;

  return pages.reduce((closest, page) => {
    const pageTop = page.offsetTop;
    const pageHeight = page.offsetHeight;
    const pageCenter = pageTop + pageHeight / 2;
    const distance = Math.abs(pageCenter - containerCenter);
    return distance < closest.distance ? { el: page, distance } : closest;
  }, { el: null, distance: Infinity }).el;
}

function getOverlayLayerByPageNumber(pageNumber) {
  return document.querySelector(`.overlay-layer[data-page-number="${pageNumber}"]`);
}

function makeDraggable(wrapper, overlayInfo, container) {
  wrapper.addEventListener('mousedown', (e) => {
    if (e.target.classList.contains('resize-handle')) return;
    e.preventDefault();
    const startX = e.clientX;
    const startY = e.clientY;
    const startLeft = parseFloat(wrapper.style.left);
    const startTop = parseFloat(wrapper.style.top);
    const bounds = container.getBoundingClientRect();

    function onMouseMove(e) {
      const dx = e.clientX - startX;
      const dy = e.clientY - startY;
      const dxPercent = (dx / bounds.width) * 100;
      const dyPercent = (dy / bounds.height) * 100;

      let newLeft = startLeft + dxPercent;
      let newTop = startTop + dyPercent;
      newLeft = Math.min(Math.max(newLeft, 0), 100 - parseFloat(wrapper.style.width));
      newTop = Math.min(Math.max(newTop, 0), 100 - parseFloat(wrapper.style.height));

      wrapper.style.left = `${newLeft}%`;
      wrapper.style.top = `${newTop}%`;
      overlayInfo.left = newLeft;
      overlayInfo.top = newTop;
    }

    function onMouseUp() {
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', onMouseUp);
    }

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
  });
}

function addResizeHandles(wrapper, overlayInfo, container) {
  const positions = ['tl', 'tr', 'bl', 'br', 't', 'b', 'l', 'r'];
  const cursors = {
    tl: 'nwse-resize',
    tr: 'nesw-resize',
    bl: 'nesw-resize',
    br: 'nwse-resize',
    t: 'ns-resize',
    b: 'ns-resize',
    l: 'ew-resize',
    r: 'ew-resize'
  };

  positions.forEach(pos => {
    const handle = document.createElement('div');
    handle.className = `resize-handle ${pos}`;
    handle.style.position = 'absolute';
    handle.style.width = '12px';
    handle.style.height = '12px';
    handle.style.background = 'rgba(255,255,255,0.9)';
    handle.style.border = '1px solid #333';
    handle.style.borderRadius = '9999px';
    handle.style.boxShadow = '0 0 2px rgba(0,0,0,0.5)';
    handle.style.zIndex = '1002';
    handle.style.cursor = cursors[pos];

    if (pos === 'tl') {
      handle.style.top = '0';
      handle.style.left = '0';
    } else if (pos === 'tr') {
      handle.style.top = '0';
      handle.style.right = '0';
    } else if (pos === 'bl') {
      handle.style.bottom = '0';
      handle.style.left = '0';
    } else if (pos === 'br') {
      handle.style.bottom = '0';
      handle.style.right = '0';
    } else if (pos === 't') {
      handle.style.top = '0';
      handle.style.left = '50%';
      handle.style.transform = 'translateX(-50%)';
    } else if (pos === 'b') {
      handle.style.bottom = '0';
      handle.style.left = '50%';
      handle.style.transform = 'translateX(-50%)';
    } else if (pos === 'l') {
      handle.style.left = '0';
      handle.style.top = '50%';
      handle.style.transform = 'translateY(-50%)';
    } else if (pos === 'r') {
      handle.style.right = '0';
      handle.style.top = '50%';
      handle.style.transform = 'translateY(-50%)';
    }

    wrapper.appendChild(handle);

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

      wrapper.style.width = `${newW}%`;
      wrapper.style.height = `${newH}%`;
      wrapper.style.left = `${newL}%`;
      wrapper.style.top = `${newT}%`;

      overlayInfo.width = newW;
      overlayInfo.height = newH;
      overlayInfo.left = newL;
      overlayInfo.top = newT;
    };

    handle.addEventListener('mousedown', (e) => {
      e.stopPropagation();
      e.preventDefault();
      startX = e.clientX;
      startY = e.clientY;
      startW = parseFloat(wrapper.style.width);
      startH = parseFloat(wrapper.style.height);
      startL = parseFloat(wrapper.style.left);
      startT = parseFloat(wrapper.style.top);

      document.addEventListener('mousemove', onMouseMove);
      document.addEventListener('mouseup', () => {
        document.removeEventListener('mousemove', onMouseMove);
      }, { once: true });
    });
  });
}