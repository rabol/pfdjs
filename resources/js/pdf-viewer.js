import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import * as pdfjsViewer from 'pdfjs-dist/web/pdf_viewer.mjs';
import 'pdfjs-dist/web/pdf_viewer.css';

// Setup PDF.js worker for Vite
pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
  'pdfjs-dist/build/pdf.worker.min.mjs',
  import.meta.url
).toString();

// Alpine component
window.pdfViewer = function () {
  return {
    async initPdfViewer(pdfPath) {
      const container = document.getElementById('viewerContainer');
      const viewer = container.querySelector('.pdfViewer');
      const eventBus = new pdfjsViewer.EventBus();

      // Initialize the viewer
      const pdfViewer = new pdfjsViewer.PDFViewer({
        container,
        viewer,
        eventBus,
      });

      // Load the document
      const loadingTask = pdfjsLib.getDocument({
        url: pdfPath,
        enableXfa: true,
        cMapUrl: '/js/pdfjs/cmaps/',
        cMapPacked: true,
      });

      const pdfDocument = await loadingTask.promise;
      pdfViewer.setDocument(pdfDocument);

      // Auto scale to fit container width
      pdfViewer.currentScaleValue = 'page-width';

      // Re-scale on window resize
      window.addEventListener('resize', () => {
        pdfViewer.currentScaleValue = 'page-width';
      });
    }
  };
};