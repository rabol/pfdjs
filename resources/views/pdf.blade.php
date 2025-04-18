<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>PDF Viewer</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="flex min-h-screen flex-col items-center justify-start bg-gray-100 py-8">
        <div class="w-full max-w-5xl space-y-6 px-4">
            <h1 class="text-center text-3xl font-semibold text-gray-800">Welcome to PDF Viewer</h1>

            <div class="rounded-xl bg-white p-6 shadow-md">
                @livewire('pdf-editor')
            </div>
        </div>

        @livewireScripts
    </body>

</html>
