<?php

use Livewire\Component;

new class extends Component {
    public $count = 0;
    public function increment()
    {
        $this->count++;
    }
};
?>

<div class="flex gap-10 p-10 select-none">
    <h1>contador</h1>
    <p wire:poll='increment'>{{ $count }}</p>
</div>
