<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <title>PDF Viewer</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="flex min-h-screen flex-col bg-gray-100">
        <div class="flex flex-1 items-center justify-center">
            <div class="mx-auto h-[90vh] w-full rounded-xl border border-gray-300 bg-white p-6 px-4 shadow-md sm:max-w-md md:max-w-lg lg:max-w-3xl xl:max-w-5xl 2xl:max-w-7xl">
                <!-- <button class="mb-4 rounded bg-blue-500 px-4 py-2 text-white" id="load-pdf-editor">Load PDF Editor</button> -->
                <div class="h-full" id="pdf-editor-container">
                    <div class="flex min-h-[85vh] items-center justify-center rounded bg-white shadow" id="pdf-editor-placeholder">
                        <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-500 border-t-transparent"></div>
                    </div>
                    <div class="hidden h-full" id="pdf-editor-content">
                        @livewire('pdf-editor', [
                            'pdfPath' => '/storage/uploads/HqeJTrVHtPsi7pfVLzxehpIYpGCPcGMpeNL280zk.pdf',
                            'overlays' => [
                                [
                                    'pageNumber' => 1,
                                    'top' => 49.1284,
                                    'left' => 66.8722,
                                    'width' => 20,
                                    'height' => 20,
                                    'src' => 'https://pdfjs.test/storage/uploads/CU1ryBQPyfqz4sLIdSjARDraorqmrtV59XGx3xRc.png',
                                ],
                            ],
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed bottom-4 left-4 z-50 hidden max-h-[60vh] w-[320px] overflow-y-auto rounded-lg bg-white p-4 text-sm text-gray-800 shadow-xl ring-1 ring-gray-300" id="overlay-info-box">
            <button class="absolute right-2 top-2 text-gray-400 hover:text-red-600" onclick="document.getElementById('overlay-info-box').classList.add('hidden')" title="Close" type="button">✕</button>
            <div class="mt-4 whitespace-pre-wrap break-words text-xs" id="overlay-info-content"></div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>

</html>
