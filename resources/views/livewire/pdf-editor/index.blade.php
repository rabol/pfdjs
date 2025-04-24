<div class="flex h-full flex-col">
    <div class="flex flex-col items-center justify-center gap-2 sm:flex-row sm:justify-between">
        <label class="relative inline-flex cursor-pointer items-center rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
            <span>Upload & Display PDF</span>
            <input type="file" wire:model="pdfFile" accept="application/pdf" class="absolute inset-0 cursor-pointer opacity-0" />
        </label>

        <label class="relative inline-flex cursor-pointer items-center rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
            <span>Upload & Add Image</span>
            <input type="file" wire:model="imageFile" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" />
        </label>

        <button type="button" class="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700" onclick="document.getElementById('pdfIframe')?.contentWindow?.postMessage({ type: 'export-overlays' }, '*')">
            Save Overlays
        </button>
    </div>

    <div class="mt-4 flex-1 overflow-hidden">
        <iframe id="pdfIframe" src="{{ route('pdf.viewer', [], true) }}" class="h-full w-full rounded border border-gray-300" loading="lazy"></iframe>
    </div>

    <script>
        function hideOverlayInfoBox() {
            const box = document.getElementById('overlay-info-box');
            if (box) box.classList.add('hidden');
        }

        window.addEventListener('pdf-uploaded', event => {
            console.log("pdf-uploaded", event);
            hideOverlayInfoBox(); // hide info when uploading new PDF
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
            hideOverlayInfoBox(); // hide info when uploading image
            const url = event.detail[0].url;
            const iframe = document.getElementById('pdfIframe');
            if (!iframe?.contentWindow) return;

            iframe.contentWindow.postMessage({
                type: 'add-image',
                url
            }, '*');
        });

        window.addEventListener('message', event => {
            console.log("event", event);
            if (event.data.type === 'overlays-exported') {
                const overlays = event.data.data;
                console.log("overlays", event.data);
                Livewire.dispatch('save-overlays', {
                    overlays
                });

                const box = document.getElementById('overlay-info-box');
                const content = document.getElementById('overlay-info-content');

                if (box && content) {
                    content.innerText = JSON.stringify(overlays, null, 2);
                    box.classList.remove('hidden');
                }
            }
        });
    </script>
</div>
