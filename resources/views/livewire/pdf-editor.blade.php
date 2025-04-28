<div class="flex h-full flex-col">
    <div class="flex flex-col items-center justify-center gap-2 sm:flex-row sm:justify-between">
        <label class="relative inline-flex cursor-pointer items-center rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
            <span>Upload & Display PDF</span>
            <input accept="application/pdf" class="absolute inset-0 cursor-pointer opacity-0" type="file" wire:model="pdfFile" />
        </label>

        <label class="relative inline-flex cursor-pointer items-center rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
            <span>Upload & Add Image</span>
            <input accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" type="file" wire:model="imageFile" />
        </label>

        <button class="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700" onclick="document.getElementById('pdfIframe')?.contentWindow?.postMessage({ type: 'export-overlays' }, '*')" type="button">
            Save Overlays
        </button>
    </div>

    <div class="mt-4 flex-1 overflow-hidden">
        <iframe class="h-full w-full rounded border border-gray-300" id="pdfIframe" loading="lazy" src="{{ route('pdf-viewer', [], true) }}"></iframe>
    </div>

    @push('scripts')
        <script>
            window.onload = function() {
                // Show the PDF editor content and hide the placeholder
                document.getElementById('pdf-editor-placeholder').classList.add('hidden');
                document.getElementById('pdf-editor-content').classList.remove('hidden');

                // Force reload the component
                // Try to detect if Livewire component has pdfPath
                const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                if (component) {
                    const pdfPath = component.get('pdfPath');

                    if (!pdfPath) {
                        Livewire.dispatch('$refresh');
                    }
                }

            };

            // Check for JavaScript errors
            window.onerror = function(msg, url, lineNo, columnNo, error) {
                console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
                return false;
            };

            // Log when the page is fully loaded
            window.addEventListener('load', function() {

            });
        </script>
    @endpush
    <script>
        function hideOverlayInfoBox() {
            const box = document.getElementById('overlay-info-box');
            if (box) box.classList.add('hidden');
        }

        window.addEventListener('pdf-uploaded', event => {
            hideOverlayInfoBox();
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
            hideOverlayInfoBox();
            const url = event.detail[0].url;
            const iframe = document.getElementById('pdfIframe');
            if (!iframe?.contentWindow) return;

            iframe.contentWindow.postMessage({
                type: 'add-image',
                url
            }, '*');
        });

        window.addEventListener('message', event => {
            if (event.data.type === 'overlays-exported') {
                const overlays = event.data.data;
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

    @if ($pdfPath)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const iframe = document.getElementById('pdfIframe');
                if (!iframe) return;

                function postLoadEverything() {
                    const tryPost = () => {
                        if (iframe.contentWindow?.isViewerReady) {
                            iframe.contentWindow.postMessage({
                                type: 'load-pdf',
                                url: '{{ $pdfPath }}'
                            }, '*');

                            const overlaysData = {!! $jsonOverlays !!};

                            if (overlaysData.length > 0) {
                                setTimeout(() => {
                                    iframe.contentWindow.postMessage({
                                        type: 'load-overlays',
                                        overlays: overlaysData
                                    }, '*');
                                }, 500);
                            }

                        } else {
                            setTimeout(tryPost, 100);
                        }
                    };
                    tryPost();
                }

                if (iframe.contentDocument?.readyState === 'complete') {
                    postLoadEverything();
                } else {
                    iframe.addEventListener('load', postLoadEverything, {
                        once: true
                    });
                }
            });
        </script>
    @endif

</div>
