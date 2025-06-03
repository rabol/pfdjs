import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import 'pdfjs-dist/web/pdf_viewer.css';
import { scaleOverlayLayers } from './utils';
import { overlaysData, insertOverlay, restoreOverlays } from './overlay-manager';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
  'pdfjs-dist/build/pdf.worker.min.mjs',
  import.meta.url
).toString();

export async function initPdfViewer(pdfPath) {
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

    Object.assign(canvas.style, {
      display: 'block',
      width: '100%',
      height: 'auto',
      boxSizing: 'border-box',
      margin: '0',
      padding: '0',
      border: 'none'
    });

    const wrapper = document.createElement('div');
    wrapper.className = 'page-wrapper bg-white shadow overflow-hidden';
    wrapper.style.position = 'relative';
    wrapper.style.margin = '0 auto 20px auto';
    wrapper.style.padding = '0';
    wrapper.style.width = '100%';
    wrapper.style.maxWidth = '100%';
    wrapper.style.display = 'block';

    const overlayLayer = document.createElement('div');
    overlayLayer.className = 'overlay-layer';
    Object.assign(overlayLayer.style, {
      position: 'absolute',
      top: '0',
      left: '0',
      width: `${canvas.width}px`,
      height: `${canvas.height}px`
    });
    overlayLayer.dataset.pageNumber = pageNumber;

    wrapper.appendChild(canvas);
    wrapper.appendChild(overlayLayer);
    // Enable drag and drop for image overlays
    overlayLayer.addEventListener('dragover', (e) => {
      e.preventDefault();
    });

    overlayLayer.addEventListener('drop', (e) => {
      e.preventDefault();
      const files = e.dataTransfer.files;
      if (!files || !files.length) return;

      const file = files[0];
      if (!file.type.startsWith('image/')) return;

      const reader = new FileReader();
      reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
          const rect = overlayLayer.getBoundingClientRect();
          const dropX = e.clientX - rect.left;
          const dropY = e.clientY - rect.top;

          const leftPercent = (dropX / rect.width) * 100;
          const topPercent = (dropY / rect.height) * 100;

          const widthPercent = (img.width / rect.width) * 100;
          const heightPercent = (img.height / rect.height) * 100;

          const overlayInfo = {
            pageNumber,
            left: leftPercent,
            top: topPercent,
            width: widthPercent,
            height: heightPercent,
            src: event.target.result,
          };

          overlaysData.push(overlayInfo);
          insertOverlay(overlayInfo);
        };
        img.src = event.target.result;
      };
      reader.readAsDataURL(file);
    });

    pagesContainer.appendChild(wrapper);

    const context = canvas.getContext('2d');
    await page.render({ canvasContext: context, viewport }).promise;
  }

  scaleOverlayLayers();
  new ResizeObserver(scaleOverlayLayers).observe(container);
  window.addEventListener('resize', scaleOverlayLayers);
  restoreOverlays();
}
