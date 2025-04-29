<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class PdfOverlayUploader extends Component
{
    use WithFileUploads;

    public $overlayImage;

    public function uploadOverlay()
    {
        $this->validate([
            'overlayImage' => 'required|file|image|max:10240',
        ]);

        $path = $this->overlayImage->store('uploads', 'public');

        $this->dispatch('overlayUploaded', path: asset("storage/{$path}"));
    }

    public function render()
    {
        return view('livewire.pdf-overlay-uploader');
    }
}
