/* Copyright 2014 Mozilla Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// Import required modules
import * as pdfjsLib from './build/pdf.mjs';
import * as pdfjsViewer from './web/pdf_viewer.mjs';

// Check if required libraries are available
if (!pdfjsLib.getDocument || !pdfjsViewer.PDFPageView) {
    // eslint-disable-next-line no-alert
    alert("Please build the pdfjs-dist library using\n  `gulp dist-install`");
}

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = "/js/pdfjs/build/pdf.worker.mjs";

// Get configuration from window object or use defaults
const config = window.pdfViewerConfig || {};

// Some PDFs need external cmaps.
// Ensure CMAP_URL always has a trailing slash
const CMAP_URL = config.cmapUrl || "/js/pdfjs/cmaps/";
const CMAP_PACKED = config.cmapPacked !== undefined ? config.cmapPacked : true;

// Ensure the URL has a trailing slash
const ensureTrailingSlash = (url) => {
    return url.endsWith('/') ? url : url + '/';
};

const DEFAULT_URL = config.defaultUrl || "/storage/test.pdf";
const PAGE_TO_VIEW = config.pageToView || 1;
const SCALE = config.scale || 1.0;

const ENABLE_XFA = config.enableXfa !== undefined ? config.enableXfa : true;

// Initialize PDF viewer
export async function initPdfViewer() {
    const container = document.getElementById("pageContainer");
    const viewer = document.getElementById("viewer");
    
    if (!container || !viewer) {
        console.error("Required container elements not found");
        return null;
    }
    
    console.log("Initializing PDF viewer with container:", container);
    console.log("Viewer element:", viewer);
    
    // Clear any existing content
    viewer.innerHTML = '';
    
    const eventBus = new pdfjsViewer.EventBus();

    try {
        // Loading document.
        console.log("Loading PDF from URL:", DEFAULT_URL);
        const loadingTask = pdfjsLib.getDocument({
            url: DEFAULT_URL,
            cMapUrl: ensureTrailingSlash(CMAP_URL),
            cMapPacked: CMAP_PACKED,
            enableXfa: ENABLE_XFA,
        });

        const pdfDocument = await loadingTask.promise;
        console.log("PDF document loaded:", pdfDocument);
        
        // Document loaded, retrieving the page.
        const pdfPage = await pdfDocument.getPage(PAGE_TO_VIEW);
        console.log("PDF page loaded:", pdfPage);

        // Creating the page view with default parameters.
        // The PDFViewer constructor expects specific parameters
        const pdfPageView = new pdfjsViewer.PDFViewer({
            container: container,
            viewer: viewer,
            eventBus: eventBus,
            textLayerMode: 2,
            renderInteractiveForms: true,
            linkService: new pdfjsViewer.PDFLinkService({
                eventBus: eventBus,
            }),
            findController: new pdfjsViewer.PDFFindController({
                eventBus: eventBus,
                linkService: new pdfjsViewer.PDFLinkService({
                    eventBus: eventBus,
                }),
            }),
        });
        
        // Associate the actual page with the view, and draw it.
        pdfPageView.setDocument(pdfDocument);
        
        // Force a redraw
        setTimeout(() => {
            console.log("Forcing redraw of PDF viewer");
            pdfPageView.update();
            
            // Add a class to the container to indicate it's loaded
            container.classList.add('loaded');
        }, 500);
        
        return {
            pdfDocument,
            pdfPage,
            pdfPageView,
            eventBus
        };
    } catch (error) {
        console.error("Error in PDF viewer initialization:", error);
        viewer.innerHTML = `<div class="p-4 text-red-500">Error loading PDF: ${error.message}</div>`;
        throw error;
    }
}

// Don't automatically initialize the viewer when the module is loaded
// Let the page handle initialization
// initPdfViewer().catch(error => {
//     console.error('Error initializing PDF viewer:', error);
// });

// Export configuration for external use
export const pdfConfig = {
    defaultUrl: DEFAULT_URL,
    pageToView: PAGE_TO_VIEW,
    scale: SCALE,
    enableXfa: ENABLE_XFA,
    cmapUrl: CMAP_URL,
    cmapPacked: CMAP_PACKED
};
