<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>PDF Viewer</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>


    <body class="flex min-h-screen flex-col bg-gray-100">
        <div class="flex flex-1 items-center justify-center">
            <div class="mx-auto h-[90vh] w-full rounded-xl border border-gray-300 bg-white p-6 px-4 shadow-md sm:max-w-md md:max-w-lg lg:max-w-3xl xl:max-w-5xl 2xl:max-w-7xl">
                @livewire('pdf-editor')
            </div>
        </div>
        @livewireScripts
    </body>

</html>
