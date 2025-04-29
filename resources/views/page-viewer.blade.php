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

        <div class="flex flex-1 items-center justify-center p-4">
            <div class="w-full max-w-7xl">

                <!-- Upload Component (Separate) -->
                <livewire:pdf-overlay-uploader />

                <!-- PDF Viewer Card -->
                <div class="relative mx-auto h-[90vh] w-full max-w-5xl overflow-hidden rounded bg-white shadow">
                    <livewire:pdf-viewer pdfPath="/storage/uploads/HqeJTrVHtPsi7pfVLzxehpIYpGCPcGMpeNL280zk.pdf" />
                </div>

            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>

</html>
