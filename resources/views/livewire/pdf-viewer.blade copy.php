<div class="flex min-h-screen w-full flex-col items-center justify-start bg-gray-100 p-4" x-data="() => window.pdfViewer ? window.pdfViewer() : {}" x-init="initPdfViewer('{{ $pdfPath }}')">

    <!-- Upload Controls -->

    <div class="mb-4 w-full max-w-5xl">
        <input accept="image/*" class="block" type="file" wire:model.defer="overlayImage" />
        <button class="mt-2 rounded bg-blue-600 px-3 py-1 text-white" type="button" wire:click="uploadOverlay">
            Add Overlay
        </button>
        @error('overlayImage')
            <span class="text-sm text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <!-- PDF Card Container (fully locked layout) -->
    <div class="relative mx-auto h-[90vh] w-full max-w-5xl overflow-hidden rounded bg-white shadow">
        <div class="absolute inset-0 overflow-auto bg-white" id="viewerContainer" wire:ignore>
            <div class="pdfViewer"></div>
        </div>
    </div>

</div>
@vite('resources/js/pdf-viewer.js')
