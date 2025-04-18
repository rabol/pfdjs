<div class="space-y-6">
    <!-- Upload PDF -->
    <form wire:submit.prevent="uploadPdf" class="space-y-2">
        <div class="flex flex-col items-center gap-4 md:flex-row">
            <label class="relative inline-flex cursor-pointer items-center rounded bg-blue-600 px-6 py-2 text-white hover:bg-blue-700">
                <span>Select PDF</span>
                <input type="file" wire:model="pdfFile" accept="application/pdf" class="absolute inset-0 cursor-pointer opacity-0" />
            </label>

            <button type="submit" class="flex items-center gap-2 rounded bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50" wire:loading.attr="disabled" wire:target="uploadPdf,pdfFile">
                <svg wire:loading wire:target="uploadPdf" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                </svg>
                <span>Load PDF</span>
            </button>
        </div>

        @if ($pdfFile)
            <p class="text-sm italic text-gray-600">📄 {{ $pdfFile->getClientOriginalName() }}</p>
        @endif

        @error('pdfFile')
            <p class="text-sm text-red-500">{{ $message }}</p>
        @enderror
    </form>

    <!-- Upload Image -->
    <form wire:submit.prevent="uploadImage" class="space-y-2">
        <div class="flex flex-col items-center gap-4 md:flex-row">
            <label class="relative inline-flex cursor-pointer items-center rounded bg-green-600 px-6 py-2 text-white hover:bg-green-700">
                <span>Select Image</span>
                <input type="file" wire:model="imageFile" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" />
            </label>

            <button type="submit" class="flex items-center gap-2 rounded bg-green-600 px-6 py-2 text-white hover:bg-green-700 disabled:opacity-50" wire:loading.attr="disabled" wire:target="uploadImage,imageFile">
                <svg wire:loading wire:target="uploadImage" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                </svg>
                <span>Add Image</span>
            </button>
        </div>

        @if ($imageFile)
            <p class="text-sm italic text-gray-600">🖼️ {{ $imageFile->getClientOriginalName() }}</p>
        @endif

        @error('imageFile')
            <p class="text-sm text-red-500">{{ $message }}</p>
        @enderror
    </form>

    <!-- Always visible iframe -->
    <div>
        <iframe id="pdfIframe" src="{{ route('pdf.viewer') }}" class="h-[80vh] w-full rounded border border-gray-300" loading="lazy"></iframe>
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
                url: url
            }, '*');
        });
    </script>
</div>
