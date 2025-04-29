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
            <div class="relative mx-auto h-[90vh] w-full rounded-xl border border-gray-300 bg-white p-6 px-4 shadow-md sm:max-w-md md:max-w-lg lg:max-w-3xl xl:max-w-5xl 2xl:max-w-7xl">
                {{-- content start here --}}
                <livewire:pdf-viewer pdfPath="/storage/uploads/HqeJTrVHtPsi7pfVLzxehpIYpGCPcGMpeNL280zk.pdf" />
            </div>
        </div>


        @livewireScripts
        @stack('scripts')
    </body>

</html>
