<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PdfEditor extends Component
{
    use WithFileUploads;

    public $pdfFile;

    public $imageFile;

    public $pdfUrl;

    public function uploadPdf()
    {

        $this->validate([
            'pdfFile' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $this->pdfFile->store('public/uploads');
        $this->pdfUrl = Storage::url($path);

        $this->dispatch('pdf-uploaded', ['url' => $this->pdfUrl]);
    }

    public function uploadImage()
    {
        $this->validate([
            'imageFile' => 'required|image|max:2048',
        ]);

        $path = $this->imageFile->store('public/uploads');
        $imageUrl = Storage::url($path);

        $this->dispatch('image-uploaded', ['url' => $imageUrl]);
    }

    public function render()
    {
        return view('livewire.pdf-editor');
    }
}
