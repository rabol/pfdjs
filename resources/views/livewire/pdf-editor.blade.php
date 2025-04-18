<div class="flex h-full flex-1 flex-col">
    <!-- Upload Buttons -->
    <div class="flex flex-col items-center justify-center gap-4 p-2 sm:flex-row">
        <!-- Upload & Display PDF -->
        <div class="flex flex-col items-center">
            <label class="relative inline-flex cursor-pointer items-center rounded bg-blue-600 px-4 py-2 text-sm text-white transition hover:bg-blue-700" wire:loading.class="opacity-50 cursor-not-allowed"
                wire:target="pdfFile">
                <span>Upload & Display PDF</span>
                <input type="file" wire:model="pdfFile" accept="application/pdf" class="absolute inset-0 cursor-pointer opacity-0" wire:loading.attr="disabled" wire:target="pdfFile" />
            </label>
            @error('pdfFile')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Upload & Add Image -->
        <div class="flex flex-col items-center">
            <label class="relative inline-flex cursor-pointer items-center rounded bg-green-600 px-4 py-2 text-sm text-white transition hover:bg-green-700" wire:loading.class="opacity-50 cursor-not-allowed"
                wire:target="pdfFile">
                <span>Upload & Add Image</span>
                <input type="file" wire:model="imageFile" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" wire:loading.attr="disabled" wire:target="pdfFile" />
            </label>
            @error('imageFile')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Viewer + Spinner -->
    <div class="relative h-full min-h-0 flex-1">
        <!-- Spinner -->
        <div wire:loading.delay.long wire:target="pdfFile" class="absolute inset-0 z-50 flex items-center justify-center bg-white bg-opacity-80">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-blue-500 border-t-transparent"></div>
        </div>

        <!-- PDF Viewer -->
        <iframe id="pdfIframe" src="{{ route('pdf.viewer', [], true) }}" class="h-full w-full rounded border border-gray-300" loading="lazy"></iframe>
    </div>

    <script>
        window.addEventListener('pdf-uploaded', event => {
            const url = event.detail[0].url;
            const iframe = document.getElementById('pdfIframe');
            if (!iframe) return;

            const post = () => iframe.contentWindow?.postMessage({
                type: 'load-pdf',
                url
            }, '*');

            if (iframe.contentDocument?.readyState === 'complete') {
                post();
            } else {
                iframe.addEventListener('load', post, {
                    once: true
                });
            }
        });

        window.addEventListener('image-uploaded', event => {
            const url = event.detail[0].url;
            const iframe = document.getElementById('pdfIframe');
            if (!iframe?.contentWindow) return;

            iframe.contentWindow.postMessage({
                type: 'add-image',
                url
            }, '*');
        });
    </script>
</div>
