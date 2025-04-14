<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('js/pdfjs/web/pdf_viewer.css') }}">

    <script src="{{ asset('js/pdfjs/build/pdf.mjs') }}" type="module" mime_tyep="application/javascript"></script>
    <script src="{{ asset('js/pdfjs/web/pdf_viewer.mjs') }}" type="module"></script>


</head>

<body>

    <div class="max-w-7xl mx-auto py-12 border-amber-500 border-2">
        <div class="w-full mx-auto text-center overflow-auto">
            <div id="pageContainer"
                class="pdfViewer absolute overflow-auto max-h-9/12 bg-amber-700 mx-auto max-w-10/12 px-0">
                <div id="viewer" class="m-3">
                </div>
            </div>
        </div>

    </div>
    @if (Route::has('login'))
        <div class="h-14.5 hidden lg:block"></div>
    @endif

    <script src="{{ asset('js/pdfjs/pageviewer.mjs') }}" type="module"></script>
</body>

</html>
