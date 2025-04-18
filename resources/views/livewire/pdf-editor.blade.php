<div class="space-y-6">
    <!-- Upload PDF -->
    <form wire:submit.prevent="uploadPdf" class="flex flex-col items-center gap-4 md:flex-row">
        <label class="relative inline-flex cursor-pointer items-center rounded bg-blue-600 px-6 py-2 text-white hover:bg-blue-700">
            <span>Choose PDF</span>
            <input type="file" wire:model="pdfFile" accept="application/pdf" class="absolute inset-0 cursor-pointer opacity-0" />
        </label>

        <button type="submit" class="rounded bg-blue-600 px-6 py-2 text-white hover:bg-blue-700">
            Load PDF
        </button>
    </form>

    @error('pdfFile')
        <span class="text-sm text-red-500">{{ $message }}</span>
    @enderror
    @if ($pdfFile)
        <p class="text-sm italic text-gray-600">Selected PDF: {{ $pdfFile->getClientOriginalName() }}</p>
    @endif

    <!-- Upload Image -->
    <form wire:submit.prevent="uploadImage" class="flex flex-col items-center gap-4 md:flex-row">
        <label class="relative inline-flex cursor-pointer items-center rounded bg-green-600 px-6 py-2 text-white hover:bg-green-700">
            <span>Choose Image</span>
            <input type="file" wire:model="imageFile" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" />
        </label>

        <button type="submit" class="rounded bg-green-600 px-6 py-2 text-white hover:bg-green-700">
            Add Image
        </button>
    </form>

    @error('imageFile')
        <span class="text-sm text-red-500">{{ $message }}</span>
    @enderror
    @if ($imageFile)
        <p class="text-sm italic text-gray-600">Selected Image: {{ $imageFile->getClientOriginalName() }}</p>
    @endif

    <!-- Viewer iframe -->
    @if ($pdfUrl)
        <div class="h-[80vh] w-full overflow-hidden rounded border border-gray-300">
            <iframe id="pdfIframe" src="{{ route('pdf.viewer') }}" class="h-full w-full" loading="lazy"></iframe>
        </div>
    @else
        <div class="pt-6 text-center italic text-gray-500">No PDF loaded.</div>
    @endif

    <!-- JS Events -->
    <script>
        window.addEventListener('pdf-uploaded', event => {
            const iframe = document.getElementById('pdfIframe');
            if (!iframe) return;

            iframe.addEventListener('load', () => {
                iframe.contentWindow.postMessage({
                    type: 'load-pdf',
                    url: event.detail.url
                }, '*');
            }, {
                once: true
            });
        });

        window.addEventListener('image-uploaded', event => {
            const iframe = document.getElementById('pdfIframe');
            if (!iframe) return;

            iframe.contentWindow.postMessage({
                type: 'add-image',
                url: event.detail.url
            }, '*');
        });
    </script>
</div>
