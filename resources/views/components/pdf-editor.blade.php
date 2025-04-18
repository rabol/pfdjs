<div {{ $attributes->merge(['class' => 'relative w-full h-auto space-y-4']) }}>
    <form wire:submit.prevent="uploadPdf" class="flex items-center gap-4">
        <input type="file" wire:model="pdfFile" accept="application/pdf" />
        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Upload PDF</button>
    </form>

    @if ($pdfUrl)
        <iframe src="{{ route('pdf.viewer', ['pdf' => $pdfUrl]) }}" class="h-[800px] w-full border" loading="lazy"></iframe>
    @endif

    @livewire('pdf-overlay-manager', ['userDocId' => $userDocId])
</div>
