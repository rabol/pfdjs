<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

class PdfViewer extends Component
{
    public string $pdfPath;

    public array $overlays = [];

    public function mount(string $pdfPath, array $overlays = []): void
    {
        // when calling like this:  <livewire:pdf-viewer pdfPath="/storage/uploads/HqeJTrVHtPsi7pfVLzxehpIYpGCPcGMpeNL280zk.pdf" />
        // it becomes https://pdfjs.test/storage/uploads/HqeJTrVHtPsi7pfVLzxehpIYpGCPcGMpeNL280zk.pdf
        // and the url exits
        $this->pdfPath = asset($pdfPath);
        $this->overlays = $overlays;
    }

    public function render()
    {
        return view('livewire.pdf-viewer');
    }
}
