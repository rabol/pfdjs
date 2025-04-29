<div class="relative h-full w-full" x-data="() => window.pdfViewer ? window.pdfViewer() : {}" x-init="initPdfViewer('{{ $pdfPath }}')">
    <!-- This container fills the white box -->
    <div class="absolute inset-0 overflow-auto bg-white" id="viewerContainer">
        <div class="pdfViewer"></div>
    </div>
</div>

@vite('resources/js/pdf-viewer.js')
