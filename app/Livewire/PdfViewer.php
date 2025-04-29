<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class PdfViewer extends Component
{
    use WithFileUploads;

    public string $pdfPath;

    public $overlayImage;

    public function mount(string $pdfPath): void
    {

        // https://pdfjs.test/storage/uploads/HqeJTrVHtPsi7pfVLzxehpIYpGCPcGMpeNL280zk.pdf

        $this->pdfPath = asset($pdfPath);
    }

    public function uploadOverlay(): void
    {
        $this->validate([
            'overlayImage' => 'image|max:5120',
        ]);

        $path = $this->overlayImage->store('uploads', 'public');

        $this->dispatch('overlayUploaded', path: asset("storage/{$path}"));
    }

    public function render()
    {
        return view('livewire.pdf-viewer');
    }
}
