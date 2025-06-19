<div id="signature-pad{{$uniqueId}}" class="signature-pad" x-init="

    function initializeSignaturePad{{$uniqueId}}() {
        // console.log('initializeSignaturePad{{$uniqueId}}');
        const wrapper{{$uniqueId}} = document.getElementById('signature-pad{{$uniqueId}}');
        if (!wrapper{{$uniqueId}}) return;

        const canvasWrapper{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector('#canvas-wrapper');
        const clearButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector('[data-action=clear]');
        const undoButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector('[data-action=undo]');
        const redoButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector('[data-action=redo]');
        const savePNGButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector('[data-action=save-png]');
        const saveToServerButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector('[data-action=save-to-server]');
        let undoData{{$uniqueId}} = [];
        const canvas{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector('canvas');
        const signaturePad{{$uniqueId}} = new SignaturePad(canvas{{$uniqueId}}, {
            backgroundColor: 'rgb(255, 255, 255)'
        });

        function resizeCanvas{{$uniqueId}}() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas{{$uniqueId}}.width = canvas{{$uniqueId}}.offsetWidth * ratio;
            canvas{{$uniqueId}}.height = canvas{{$uniqueId}}.offsetHeight * ratio;
            canvas{{$uniqueId}}.getContext('2d').scale(ratio, ratio);
            signaturePad{{$uniqueId}}.fromData(signaturePad{{$uniqueId}}.toData());
        }

        window.addEventListener('resize', resizeCanvas{{$uniqueId}});
        setTimeout(resizeCanvas{{$uniqueId}}, 100);

        clearButton{{$uniqueId}}.addEventListener('click', () => {
            signaturePad{{$uniqueId}}.clear();
        });

        undoButton{{$uniqueId}}.addEventListener('click', () => {
            const data = signaturePad{{$uniqueId}}.toData();
            if (data && data.length > 0) {
                const removed = data.pop();
                undoData{{$uniqueId}}.push(removed);
                signaturePad{{$uniqueId}}.fromData(data);
            }
        });

        redoButton{{$uniqueId}}.addEventListener('click', () => {
            if (undoData{{$uniqueId}}.length > 0) {
                const data = signaturePad{{$uniqueId}}.toData();
                data.push(undoData{{$uniqueId}}.pop());
                signaturePad{{$uniqueId}}.fromData(data);
            }
        });

        savePNGButton{{$uniqueId}}.addEventListener('click', () => {
            if (signaturePad{{$uniqueId}}.isEmpty()) {
                alert('Please provide a signature first.');
            } else {
                const dataURL = signaturePad{{$uniqueId}}.toDataURL();
                download{{$uniqueId}}(dataURL, 'signature.png');
            }
        });

        saveToServerButton{{$uniqueId}}.addEventListener('click', () => {
            if (signaturePad{{$uniqueId}}.isEmpty()) {
                alert('Please provide a signature first.');
            } else {
                const dataURL = signaturePad{{$uniqueId}}.toDataURL();
                @this.saveSignature(dataURL)
                    .then(response => {
                        alert('Signature saved successfully!');
                        console.log('Signature URL:', response);
                    })
                    .catch(error => {
                        console.error('Error saving signature:', error);
                        alert('Error saving signature. Please try again.');
                    });
            }
        });
    }

    function download{{$uniqueId}}(dataURL, filename) {
        const blob = dataURLToBlob{{$uniqueId}}(dataURL);
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style = 'display: none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    }

    function dataURLToBlob{{$uniqueId}}(dataURL) {
        const parts = dataURL.split(';base64,');
        const contentType = parts[0].split(':')[1];
        const raw = window.atob(parts[1]);
        const rawLength = raw.length;
        const uInt8Array = new Uint8Array(rawLength);
        for (let i = 0; i < rawLength; ++i) {
            uInt8Array[i] = raw.charCodeAt(i);
        }
        return new Blob([uInt8Array], { type: contentType });
    }

    // Initialize on first load
    document.addEventListener('livewire:initialized', function() {
        console.log('livewire:initialized');
        if(typeof initializeSignaturePad{{$uniqueId}} === 'function') {
            initializeSignaturePad{{$uniqueId}}();
        }
    });
    
    // Initialize on subsequent updates
    document.addEventListener('livewire:update', function() {
        setTimeout(initializeSignaturePad{{$uniqueId}}, 0);
    });
">
    <div id="canvas-wrapper" class="signature-pad--body">
        <canvas></canvas>
    </div>
    <div class="signature-pad--footer">
        <div class="description">Sign above</div>

        <div class="signature-pad--actions">
            <div class="flex flex-row w-full">
                <button type="button" class="grow button clear" data-action="clear">Clear</button>
                <button type="button" class="grow button" data-action="undo" title="Ctrl-Z">Undo</button>
                <button type="button" class="grow button" data-action="redo" title="Ctrl-Y">Redo</button>
                <button type="button" class="grow button save" data-action="save-png">Save as PNG</button>
                <button type="button" class="grow button save" data-action="save-to-server">Save to Server</button>
            </div>
        </div>
    </div>
</div>
@assets
<script src="{{ asset('js/signature_pad/dist/signature_pad.umd.min.js') }}"></script>
<script>
</script>
@endassets

@script
<script>
    console.log('here is the script');
</script>
@endscript