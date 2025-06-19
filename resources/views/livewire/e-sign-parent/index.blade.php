<div class="h-full p-4">
    <button wire:click="addField" class="bg-blue-500 text-white px-4 py-2 rounded-md">Add Field</button>
    @for ($i = 0; $i < $numberOfFields; $i++)
        <div id="e-sign-content-{{ $i }}" class="h-[50%]">
            <livewire:e-sign :key="'e_sign'.$i" :uniqueId="'e_sign'.$i" />
        </div>
    @endfor
</div>



<script>
    document.addEventListener('livewire:initialized', function () {
        @for ($i = 0; $i < $numberOfFields; $i++)
            document.getElementById('e-sign-content-{{ $i }}').classList.remove('hidden');
        @endfor
    });
    
    // Function to initialize all signature pads
    function initializeAllSignaturePads() {
        const signaturePads = document.querySelectorAll('[id^="signature-pad"]');
        // console.log("all signaturePads", signaturePads);
        signaturePads.forEach(pad => {
            const uniqueId = pad.id.replace('signature-pad', '');
            // console.log("all signaturePads", typeof window[`initializeSignaturePad${uniqueId}`] === 'function');
            // if (typeof window[`initializeSignaturePad${uniqueId}`] === 'function') {
            //     window[`initializeSignaturePad${uniqueId}`]();
            // }
        });
    }

    // Listen for Livewire updates to reinitialize signature pads
    document.addEventListener('e-sign-parent-updated', function() {
        // setTimeout(() => {
        //     // console.log('Livewire update triggered');
        //     initializeAllSignaturePads();
        // }, 500);
    });
    
    // Check for JavaScript errors
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        // console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
        return false;
    };
    
    // Log when the page is fully loaded
    window.addEventListener('load', function() {
        // console.log('Page fully loaded');
        initializeAllSignaturePads();
    });
</script>