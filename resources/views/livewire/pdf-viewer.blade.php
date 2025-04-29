<div class="relative h-[90vh] w-full" x-data="() => window.pdfViewer ? window.pdfViewer() : {}" x-init="initPdfViewer('{{ $pdfPath }}')">

    <div class="h-full overflow-auto bg-gray-100 p-4" id="viewerContainer" wire:ignore>

        <div class="flex flex-col items-center space-y-6" id="pdfPages">
            <!-- JS will inject pages here -->
        </div>
    </div>
</div>

@vite('resources/js/pdf-viewer.js')
