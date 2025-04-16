<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .pdf-container {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            margin-top: 20px;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .controls {
            margin-bottom: 15px;
        }
        .btn {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .file-input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PDF Viewer</h1>
        
        <div class="controls">
            <input type="file" id="pdfFile" class="file-input" accept="application/pdf">
            <button id="loadPdfBtn" class="btn">Load PDF</button>
            <button id="openInNewTabBtn" class="btn">Open in New Tab</button>
        </div>
        
        <div class="pdf-container">
            <iframe id="pdfViewer" src="/test-pdf.html"></iframe>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pdfFileInput = document.getElementById('pdfFile');
            const loadPdfBtn = document.getElementById('loadPdfBtn');
            const openInNewTabBtn = document.getElementById('openInNewTabBtn');
            const pdfViewer = document.getElementById('pdfViewer');
            
            // Function to load a PDF file
            function loadPdfFile(file) {
                if (!file) return;
                
                // Create a URL for the file
                const fileURL = URL.createObjectURL(file);
                
                // Update the iframe source with the file URL
                pdfViewer.src = `/test-pdf.html?file=${encodeURIComponent(fileURL)}`;
            }
            
            // Load PDF button click event
            loadPdfBtn.addEventListener('click', function() {
                const file = pdfFileInput.files[0];
                loadPdfFile(file);
            });
            
            // Open in new tab button click event
            openInNewTabBtn.addEventListener('click', function() {
                const file = pdfFileInput.files[0];
                if (!file) return;
                
                const fileURL = URL.createObjectURL(file);
                window.open(`/test-pdf.html?file=${encodeURIComponent(fileURL)}`, '_blank');
            });
        });
    </script>
</body>
</html> 