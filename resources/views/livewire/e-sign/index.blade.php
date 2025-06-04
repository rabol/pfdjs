<div id="signature-pad{{$uniqueId}}" class="signature-pad">
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
@endassets
<script>
    const wrapper{{$uniqueId}} = document.getElementById("signature-pad{{$uniqueId}}");
    const canvasWrapper{{$uniqueId}} = document.getElementById("canvas-wrapper");
    const clearButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=clear]");
    const changeBackgroundColorButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=change-background-color]");
    const changeColorButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=change-color]");
    const changeWidthButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=change-width]");
    const undoButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=undo]");
    const redoButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=redo]");
    const savePNGButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=save-png]");
    const saveJPGButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=save-jpg]");
    const saveSVGButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=save-svg]");
    const saveSVGWithBackgroundButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=save-svg-with-background]");
    const openInWindowButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=open-in-window]");
    let undoData{{$uniqueId}} = [];
    const canvas{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("canvas");
    const signaturePad{{$uniqueId}} = new SignaturePad(canvas{{$uniqueId}}, {
        // It's Necessary to use an opaque color when saving image as JPEG;
        // this option can be omitted if only saving as PNG or SVG
        backgroundColor: 'rgb(255, 255, 255)'
    });

    function randomColor{{$uniqueId}}() {
        const r = Math.round(Math.random() * 255);
        const g = Math.round(Math.random() * 255);
        const b = Math.round(Math.random() * 255);
        return `rgb(${r},${g},${b})`;
    }

    // Adjust canvas coordinate space taking into account pixel ratio,
    // to make it look crisp on mobile devices.
    // This also causes canvas to be cleared.
    function resizeCanvas{{$uniqueId}}() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);

        // This part causes the canvas to be cleared
        canvas{{$uniqueId}}.width = canvas{{$uniqueId}}.offsetWidth * ratio;
        canvas{{$uniqueId}}.height = canvas{{$uniqueId}}.offsetHeight * ratio;
        canvas{{$uniqueId}}.getContext("2d").scale(ratio, ratio);

        // This library does not listen for canvas changes, so after the canvas is automatically
        // cleared by the browser, SignaturePad#isEmpty might still return false, even though the
        // canvas looks empty, because the internal data of this library wasn't cleared. To make sure
        // that the state of this library is consistent with visual state of the canvas, you
        // have to clear it manually.
        //signaturePad.clear();

        // If you want to keep the drawing on resize instead of clearing it you can reset the data.
        signaturePad{{$uniqueId}}.fromData(signaturePad{{$uniqueId}}.toData());
    }

    // On mobile devices it might make more sense to listen to orientation change,
    // rather than window resize events.
    window.onresize = resizeCanvas{{$uniqueId}};
    setTimeout(() => {
      resizeCanvas{{$uniqueId}}();
    }, 700);

    window.addEventListener("keydown", (event) => {
        switch (true) {
            case event.key === "z" && event.ctrlKey:
                undoButton{{$uniqueId}}.click();
                break;
            case event.key === "y" && event.ctrlKey:
                redoButton{{$uniqueId}}.click();
                break;
        }
    });

    function download{{$uniqueId}}(dataURL, filename) {
        const blob = dataURLToBlob{{$uniqueId}}(dataURL);
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement("a");
        a.style = "display: none";
        a.href = url;
        a.download = filename;

        document.body.appendChild(a);
        a.click();

        window.URL.revokeObjectURL(url);
    }

    // One could simply use Canvas#toBlob method instead, but it's just to show
    // that it can be done using result of SignaturePad#toDataURL.
    function dataURLToBlob{{$uniqueId}}(dataURL) {
        // Code taken from https://github.com/ebidel/filer.js
        const parts = dataURL.split(';base64,');
        const contentType = parts[0].split(":")[1];
        const raw = window.atob(parts[1]);
        const rawLength = raw.length;
        const uInt8Array = new Uint8Array(rawLength);

        for (let i = 0; i < rawLength; ++i) {
            uInt8Array[i] = raw.charCodeAt(i);
        }

        return new Blob([uInt8Array], {
            type: contentType
        });
    }

    signaturePad{{$uniqueId}}.addEventListener("endStroke", () => {
        // clear undoData when new data is added
        undoData{{$uniqueId}} = [];
    });

    clearButton{{$uniqueId}}.addEventListener("click", () => {
        signaturePad{{$uniqueId}}.clear();
    });

    undoButton{{$uniqueId}}.addEventListener("click", () => {
        const data = signaturePad{{$uniqueId}}.toData();

        if (data && data.length > 0) {
            // remove the last dot or line
            const removed = data.pop();
            undoData{{$uniqueId}}.push(removed);
            signaturePad{{$uniqueId}}.fromData(data);
        }
    });

    redoButton{{$uniqueId}}.addEventListener("click", () => {
        if (undoData{{$uniqueId}}.length > 0) {
            const data = signaturePad{{$uniqueId}}.toData();
            data.push(undoData{{$uniqueId}}.pop());
            signaturePad{{$uniqueId}}.fromData(data);
        }
    });

    savePNGButton{{$uniqueId}}.addEventListener("click", () => {
        if (signaturePad{{$uniqueId}}.isEmpty()) {
            alert("Please provide a signature first.");
        } else {
            const dataURL = signaturePad{{$uniqueId}}.toDataURL();
            download{{$uniqueId}}(dataURL, "signature.png");
        }
    });

    // Add event listener for saving to server
    const saveToServerButton{{$uniqueId}} = wrapper{{$uniqueId}}.querySelector("[data-action=save-to-server]");
    saveToServerButton{{$uniqueId}}.addEventListener("click", () => {
        if (signaturePad{{$uniqueId}}.isEmpty()) {
            alert("Please provide a signature first.");
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
</script>
