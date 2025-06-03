import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import * as pdfjsViewer from 'pdfjs-dist/web/pdf_viewer.mjs';
import 'pdfjs-dist/web/pdf_viewer.css';

// Setup PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
  'pdfjs-dist/build/pdf.worker.min.mjs',
  import.meta.url
).toString();

window.pdfViewer = function () {
  return {
    async initPdfViewer(pdfPath) {


      console.log('Initializing PDF Viewer with path:', pdfPath);
      const container = document.getElementById('viewerContainer');
      console.log('Container dimensions:', container.offsetWidth, container.offsetHeight);

      const viewer = container.querySelector('.pdfViewer');
      const eventBus = new pdfjsViewer.EventBus();

      const pdfViewer = new pdfjsViewer.PDFViewer({
        container,
        viewer,
        eventBus,
      });

      const loadingTask = pdfjsLib.getDocument({
        url: pdfPath,
        enableXfa: true,
        cMapUrl: '/js/pdfjs/cmaps/',
        cMapPacked: true,
      });

      const pdfDocument = await loadingTask.promise;
      pdfViewer.setDocument(pdfDocument);

      // Responsive scaling
      pdfViewer.currentScaleValue = 'page-width';
      window.addEventListener('resize', () => {
        pdfViewer.currentScaleValue = 'page-width';
      });

      // Add overlay layers to all pages
      eventBus.on('pagesinit', () => {
        pdfViewer.currentScaleValue = 'page-width';

        pdfViewer._pages.forEach((pageView, index) => {
          const pageDiv = pageView.div;

          const overlay = document.createElement('div');
          overlay.className = 'overlay-layer';
          overlay.style.position = 'absolute';
          overlay.style.top = '0';
          overlay.style.left = '0';
          overlay.style.width = '100%';
          overlay.style.height = '100%';
          overlay.style.pointerEvents = 'none'; // let children interact
          overlay.dataset.pageIndex = index + 1;

          pageDiv.style.position = 'relative';
          pageDiv.appendChild(overlay);
        });
      });

      // Inject image overlay when uploaded
      window.addEventListener('overlayUploaded', e => {
        const imagePath = e.detail.path;

        const overlay = document.querySelector('.overlay-layer');
        if (!overlay) return;

        const wrapper = document.createElement('div');
        wrapper.style.position = 'absolute';
        wrapper.style.left = '100px';
        wrapper.style.top = '100px';
        wrapper.style.width = '150px';
        wrapper.style.height = '150px';
        wrapper.style.border = '2px dashed red';
        wrapper.style.zIndex = '1000';
        wrapper.style.cursor = 'move';
        wrapper.style.background = '#f9f9f9';
        wrapper.style.pointerEvents = 'auto'; // allow interaction

        const img = new Image();
        img.src = imagePath;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.display = 'block';
        img.style.pointerEvents = 'none'; // let wrapper handle drag

        wrapper.appendChild(img);
        overlay.appendChild(wrapper);

        // Drag behavior
        wrapper.addEventListener('mousedown', function (e) {
          e.preventDefault();
          const startX = e.clientX;
          const startY = e.clientY;
          const origLeft = wrapper.offsetLeft;
          const origTop = wrapper.offsetTop;

          function onMouseMove(e) {
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            wrapper.style.left = `${origLeft + dx}px`;
            wrapper.style.top = `${origTop + dy}px`;
          }

          function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
          }

          document.addEventListener('mousemove', onMouseMove);
          document.addEventListener('mouseup', onMouseUp);
        });
      });
    }
  };
};