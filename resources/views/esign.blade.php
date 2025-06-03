<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>PDF Viewer</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>


    <body class="flex min-h-screen flex-col bg-gray-100"  onselectstart="return false">
        <div class="flex flex-1 items-center justify-center">
            <div class="mx-auto h-[90vh] w-full rounded-xl border border-gray-300 bg-white p-6 px-4 shadow-md sm:max-w-md md:max-w-lg lg:max-w-3xl xl:max-w-5xl 2xl:max-w-7xl">
                <!-- <button id="load-pdf-editor" class="mb-4 rounded bg-blue-500 px-4 py-2 text-white">Load PDF Editor</button> -->
                <div id="e-sign-container" class="h-full">
                    <div id="e-sign-placeholder" class="flex min-h-[85vh] items-center justify-center rounded bg-white shadow">
                        <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-500 border-t-transparent"></div>
                    </div>
                    <div id="e-sign-content" class="hidden h-[50%]">
                        @livewire('e-sign')
                    </div>

                    <div id="e-sign-content2" class="hidden h-[50%]">
                        @livewire('e-sign')
                    </div>
                </div>
            </div>
        </div>

        @livewireScripts
        
        <script>
            window.onload = function() {
                // Show the PDF editor content and hide the placeholder
                document.getElementById('e-sign-placeholder').classList.add('hidden');
                document.getElementById('e-sign-content').classList.remove('hidden');
                document.getElementById('e-sign-content2').classList.remove('hidden');

                // Force reload the component
                Livewire.dispatch('$refresh');
                console.log('Manually triggering e-sign load');
            
            };
            
            // Check for JavaScript errors
            window.onerror = function(msg, url, lineNo, columnNo, error) {
                console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
                return false;
            };
            
            // Log when the page is fully loaded
            window.addEventListener('load', function() {
                console.log('Page fully loaded');
                console.log('e-sign container:', document.getElementById('e-sign-container'));
            });
        </script>
    </body>

</html>
