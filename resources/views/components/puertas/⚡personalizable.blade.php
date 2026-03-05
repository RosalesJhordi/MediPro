<?php

use Livewire\Component;

new class extends Component
{
    public array $panels = [];

    public int $cols = 3; // columnas del diseño

    public function addPanel()
    {
        $this->panels[] = [
            'id' => uniqid(),
        ];
    }

    public function removePanel($id)
    {
        $this->panels = array_values(
            array_filter($this->panels, fn($p) => $p['id'] !== $id)
        );
    }
};
?>

<div class="flex gap-10 p-10 select-none">

    <!-- 🧩 Botón agregar -->
    <div>
        <button wire:click="addPanel"
                class="px-4 py-2 bg-blue-500 text-white rounded">
            Agregar Cuadro
        </button>
    </div>

    <!-- 🚪 Puerta -->
    <div
        class="w-80 h-[500px] bg-gray-200 border-4 border-black p-[3px]">

        <div class="grid h-full"
             style="grid-template-columns: repeat({{ $cols }}, 1fr); gap: 3px;">

            @foreach($panels as $panel)
                <div
                    wire:key="{{ $panel['id'] }}"
                    class="bg-green-500 aspect-square cursor-pointer"
                    ondblclick="$wire.removePanel('{{ $panel['id'] }}')"
                ></div>
            @endforeach

        </div>
    </div>
</div>

@script
<script>
const GRID = 20;

function snap(value) {
    return Math.round(value / GRID) * GRID;
}

function initDesigner() {

    const canvas = document.getElementById('doorCanvas');
    const items = document.querySelectorAll('.drag-item');
    const panels = document.querySelectorAll('.panel');

    // 🔹 DRAG DESDE PALETA
    items.forEach(item => {
        item.addEventListener('dragstart', e => {
            e.dataTransfer.setData('type', 'new');
        });
    });

    canvas.addEventListener('dragover', e => e.preventDefault());

    canvas.addEventListener('drop', e => {
        e.preventDefault();

        const rect = canvas.getBoundingClientRect();
        let x = snap(e.clientX - rect.left);
        let y = snap(e.clientY - rect.top);

        $wire.dispatch('add-panel', { x, y });
    });

    // 🔹 MOVER PANELES YA COLOCADOS
    panels.forEach(panel => {

        panel.addEventListener('mousedown', function(e) {

            const id = panel.dataset.id;
            const rect = canvas.getBoundingClientRect();

            function onMouseMove(e) {
                let x = snap(e.clientX - rect.left - 40);
                let y = snap(e.clientY - rect.top - 40);

                panel.style.left = x + 'px';
                panel.style.top = y + 'px';
            }

            function onMouseUp(e) {
                let x = snap(e.clientX - rect.left - 40);
                let y = snap(e.clientY - rect.top - 40);

                $wire.dispatch('move-panel', { id, x, y });

                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    });
}

document.addEventListener('livewire:navigated', initDesigner);
document.addEventListener('DOMContentLoaded', initDesigner);
</script>
@endscript