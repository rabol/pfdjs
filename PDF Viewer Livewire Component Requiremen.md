PDF Viewer Livewire Component Requirements:

Display PDF inside an div
The pdf to display should be given as parameter - als overlay information.
it should be possible to upload a image as a overlay that can be moved and scaled, each each overlay should have have handles in all cornes and on the center of each side, and include a 'x' to remove the image.
The viewer should load all pages in the pdf and make it scrollable
should work on mobile
When user uploads image add it centered at 40% left, 40% top
Show java script errors in the console, laravel errors in the log

Technologies:
TailwindCSS
Alpine.js when appropriate 
Livewire v3.x
Laravel v12.x
Vite v6.x
Vite static plugin to copy assets (PDF.JS)
use strict code - declare(strict_types=1)
use Livewire as much as possible and try to avoid custom event listeners for communication, but use Livewire to it's full potential
The component should follow the Laravel Livewire structure for filename and locations
Use the PDF.JS (https://github.com/mozilla/pdf.js) to display the pdf
The PDF.JS part should have configuration object that should be initialized before the pdf is rendered
Create bespoke js for sizing and handling images on a given page
use @asset inside the livewire component to load bespoke javascript  (global javascript functions)
use @script inside the component to execute any javascript that needs to be executed before the pdf is shown after the page is loaded, but before Livewire 
store uploaded files in the 'uploads' folder on the public disk.
User uploaded pictures should just be inserted as is e.g. centered on the page - after the user can resize and more as needed
