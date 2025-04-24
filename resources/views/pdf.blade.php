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
                <!-- <button id="load-pdf-editor" class="mb-4 rounded bg-blue-500 px-4 py-2 text-white">Load PDF Editor</button> -->
                <div id="pdf-editor-container" class="h-full">
                    <div id="pdf-editor-placeholder" class="flex min-h-[85vh] items-center justify-center rounded bg-white shadow">
                        <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-500 border-t-transparent"></div>
                    </div>
                    <div id="pdf-editor-content" class="hidden h-full">
                        @livewire('pdf-editor')
                    </div>
                </div>
            </div>
        </div>

        <div id="overlay-info-box" class="fixed bottom-4 left-4 z-50 hidden max-h-[60vh] w-[320px] overflow-y-auto rounded-lg bg-white p-4 text-sm text-gray-800 shadow-xl ring-1 ring-gray-300">
            <button type="button" class="absolute right-2 top-2 text-gray-400 hover:text-red-600" onclick="document.getElementById('overlay-info-box').classList.add('hidden')" title="Close">✕</button>
            <div id="overlay-info-content" class="mt-4 whitespace-pre-wrap break-words text-xs"></div>
        </div>

        @livewireScripts
        
        <script>
            // document.getElementById('load-pdf-editor').addEventListener('click', function() {
            //     // Show the PDF editor content and hide the placeholder
            //     document.getElementById('pdf-editor-placeholder').classList.add('hidden');
            //     document.getElementById('pdf-editor-content').classList.remove('hidden');
                
            //     // Force reload the component
            //     Livewire.dispatch('$refresh');
            //     console.log('Manually triggering PDF editor load');
            // });
            window.onload = function() {
                // Show the PDF editor content and hide the placeholder
                document.getElementById('pdf-editor-placeholder').classList.add('hidden');
                document.getElementById('pdf-editor-content').classList.remove('hidden');

                // Force reload the component
                Livewire.dispatch('$refresh');
                console.log('Manually triggering PDF editor load');
            
            };
            
            // Check for JavaScript errors
            window.onerror = function(msg, url, lineNo, columnNo, error) {
                console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
                return false;
            };
            
            // Log when the page is fully loaded
            window.addEventListener('load', function() {
                console.log('Page fully loaded');
                console.log('PDF Editor container:', document.getElementById('pdf-editor-container'));
            });
        </script>
    </body>

</html>
