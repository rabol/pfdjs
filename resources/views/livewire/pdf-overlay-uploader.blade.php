<div class="mx-auto mb-4 w-full max-w-5xl">
    <input accept="image/*" class="block w-full" type="file" wire:model.defer="overlayImage" />
    <button class="mt-2 rounded bg-blue-600 px-4 py-2 text-white" type="button" wire:click="uploadOverlay">
        Add Overlay
    </button>
    @error('overlayImage')
        <span class="text-sm text-red-600">{{ $message }}</span>
    @enderror
</div>
