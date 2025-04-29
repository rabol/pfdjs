<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

class PdfViewer extends Component
{
    public string $pdfPath;

    public function mount(string $pdfPath): void
    {
        // when calling like this:  <livewire:pdf-viewer pdfPath="/storage/uploads/HqeJTrVHtPsi7pfVLzxehpIYpGCPcGMpeNL280zk.pdf" />
        // it becomes https://pdfjs.test/storage/uploads/HqeJTrVHtPsi7pfVLzxehpIYpGCPcGMpeNL280zk.pdf
        // and the url exits
        $this->pdfPath = asset($pdfPath);

    }

    public function render()
    {
        return view('livewire.pdf-viewer');
    }
}
