<?php

namespace App\Livewire;

use Livewire\Component;

class ESign extends Component
{
    public $value;

    public function mount($value = null)
    {
        if (!$value) {
            $value = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
        }
        $this->value = $value;
    }

    public function render()
    {
        return view('livewire.e-sign.index');
    }
}
