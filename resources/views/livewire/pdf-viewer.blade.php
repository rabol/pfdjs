<div class="relative h-full w-full" x-data="() => window.pdfViewer ? window.pdfViewer() : {}" x-init="initPdfViewer('{{ $pdfPath }}')">

    <div class="m-0 h-[90vh] w-full overflow-auto bg-gray-100 p-0" id="viewerContainer">
        <div class="m-0 p-0" id="pdfPages"></div>
    </div>

    <script>
        window.initialOverlays = @json($overlays);
    </script>
</div>
@push('scripts')
    @vite('resources/js/pdfviewer/main.js')
@endpush
