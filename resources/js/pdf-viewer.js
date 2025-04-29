
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

        const wrapper = document.createElement('div');
        wrapper.className = 'page-wrapper bg-white shadow rounded-md overflow-hidden';
        wrapper.style.position = 'relative';

        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;

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
        await page.render({
          canvasContext: context,
          viewport,
        }).promise;
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
