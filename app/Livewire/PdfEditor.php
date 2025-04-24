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

    #[On('save-overlays')]
    public function saveOverlays(array $overlays): void
    {
        $this->overlays = $overlays;

        // Example persistence (disabled by default)
        foreach ($overlays as $overlay) {
            // dd($overlay);
            \App\Models\OverlayPosition::create([
                // user_doc_id => $this->userDoc->id,
                'user_doc_id' => 0,
                'page_number' => $overlay['pageNumber'],
                'top' => $overlay['top'],
                'left' => $overlay['left'],
                'width' => $overlay['width'],
                'height' => $overlay['height'],
                'image_url' => $overlay['src'],
            ]);
        }

        logger()->info('Overlay coordinates saved:', $overlays);
        session()->flash('message', 'Overlay positions saved.');
    }

    public function updatedPdfFile()
    {
        $this->validate(['pdfFile' => 'required|file|mimes:pdf|max:10240']);
        $path = $this->pdfFile->store('uploads', 'public');
        $url = \Storage::url($path);
        $this->dispatch('pdf-uploaded', ['url' => $url]);
    }

    public function updatedImageFile()
    {
        $this->validate(['imageFile' => 'required|image|max:10240']);
        $path = $this->imageFile->store('uploads', 'public');
        $url = \Storage::url($path);
        $this->dispatch('image-uploaded', ['url' => $url]);
    }

    public function render()
    {
        \Log::info('PdfEditor component is rendering');
        return view('livewire.pdf-editor.index');
    }

    public function placeholder()
    {
        return view('livewire.pdf-editor.loading');
    }
}
