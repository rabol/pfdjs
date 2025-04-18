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

    public function updatedPdfFile()
    {
        $this->uploadPdf();
    }

    public function updatedImageFile()
    {
        $this->uploadImage();
    }

    public function uploadPdf()
    {
        $this->validate([
            'pdfFile' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $this->pdfFile->store('uploads', 'public');
        $this->pdfUrl = Storage::url($path);

        $this->dispatch('pdf-uploaded', ['url' => $this->pdfUrl]);
        $this->reset('pdfFile');
    }

    public function uploadImage()
    {
        $this->validate([
            'imageFile' => 'required|image|max:2048',
        ]);

        $path = $this->imageFile->store('uploads', 'public');
        $imageUrl = Storage::url($path);

        $this->dispatch('image-uploaded', ['url' => $imageUrl]);
        $this->reset('imageFile');
    }

    public function render()
    {
        return view('livewire.pdf-editor');
    }
}
