<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class PdfEditor extends Component
{
    use WithFileUploads;

    public $pdfFile;

    public $imageFile;

    public array $overlays = [];

    public ?string $pdfPath = null;

    public function mount(?string $pdfPath = null, array $overlays = []): void
    {
        $this->pdfPath = $pdfPath;
        $this->overlays = $overlays;
    }

    #[On('save-overlays')]
    public function saveOverlays(array $overlays): void
    {
        $this->overlays = $overlays;

        foreach ($overlays as $overlay) {
            \App\Models\OverlayPosition::create([
                'user_doc_id' => 0, // Example, adjust as needed
                'page' => $overlay['pageNumber'],
                'top' => $overlay['top'],
                'left' => $overlay['left'],
                'width' => $overlay['width'],
                'height' => $overlay['height'],
                'image_url' => $overlay['src'],
            ]);
        }

        session()->flash('message', 'Overlay positions saved.');
    }

    public function updatedPdfFile()
    {
        $this->validate([
            'pdfFile' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $this->pdfFile->store('uploads', 'public');
        $url = \Storage::url($path);

        $this->dispatch('pdf-uploaded', ['url' => $url]);
    }

    public function updatedImageFile()
    {
        $this->validate([
            'imageFile' => 'required|image|max:10240',
        ]);

        $path = $this->imageFile->store('uploads', 'public');
        $url = \Storage::url($path);

        $this->dispatch('image-uploaded', ['url' => $url]);
    }

    public function placeholder()
    {
        return view('livewire.pdf-editor-loading');
    }

    public function render()
    {
        return view('livewire.pdf-editor');
    }
}
