<?php

namespace App\Livewire;

use Livewire\Component;

class ESignParent extends Component
{
    public $numberOfFields = 1;

    public function mount()
    {
        $this->numberOfFields = 1;
    }

    public function addField()
    {
        $this->dispatch('e-sign-parent-updated');
        $this->numberOfFields++;
    }
    
    public function render()
    {
        return view('livewire.e-sign-parent.index');
    }
}
