<?php

namespace App\Livewire;

use Livewire\Component;

class ESign extends Component
{
    public $uniqueId;

    public function mount($uniqueId = null)
    {
        // this is for unique id
        if (!$uniqueId) {
            $uniqueId = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
        }
        $this->uniqueId = $uniqueId;
    }

    public function render()
    {
        return view('livewire.e-sign.index');
    }

    public function saveSignature($signatureData)
    {
        // Remove the data URL prefix to get just the base64 data
        $image_parts = explode(";base64,", $signatureData);
        $image_base64 = base64_decode($image_parts[1]);
        
        // Generate a unique filename
        $filename = 'signatures/' . uniqid() . '.png';
        
        // Store the file
        \Storage::disk('public')->put($filename, $image_base64);
        
        // Return the public URL
        return \Storage::url($filename);
    }
}
