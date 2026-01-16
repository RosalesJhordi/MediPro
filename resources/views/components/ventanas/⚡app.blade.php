<?php

use Livewire\Component;

new class extends Component
{
    public $sistemaSeleccionado = null;


    public function mount()
    {
        $this->sistemaSeleccionado = session('sistemaSeleccionado');
    }

    public function seleccionar($sistema)
    {
        logger('CLICK OK: ' . $sistema); // o dump($sistema)
        $this->sistemaSeleccionado = $sistema;

        session(['sistemaSeleccionado' => $sistema]);
    }

    public function resetear()
    {
        $this->sistemaSeleccionado = null;
        session()->forget('sistemaSeleccionado');
    }
};
?>

<div class="p-2 md:p-5 text-center max-w-7xl mx-auto">

    {{-- HEADER --}}
    <header class="mb-4 transition-all duration-500">
        <h1 class="text-2xl md:text-4xl font-black text-gray-800 tracking-tight">
            @if (!$sistemaSeleccionado)
                Configuración
            @else
                <div class="flex flex-col items-center gap-6 mt-4 animate-fade-in">

                    <span class="text-2xl md:text-4xl font-black text-blue-600 tracking-tighter drop-shadow-sm">
                        {{ $sistemaSeleccionado }}
                    </span>

                    <button wire:click="resetear"
                        class="group flex items-center gap-2 px-4 py-0.5 bg-white/50 backdrop-blur-sm border border-gray-200 hover:border-amber-300 hover:bg-amber-50 text-gray-500 hover:text-amber-700 rounded-full transition-all duration-300 shadow-sm hover:shadow-md">
                        <i
                            class="fa-solid fa-rotate-left text-xs transition-transform duration-500 group-hover:rotate-[-180deg]"></i>
                        <span class="text-[8px] font-black uppercase tracking-[0.15em]">
                            Cambiar Selección
                        </span>
                    </button>

                </div>
            @endif
        </h1>

        @if (!$sistemaSeleccionado)
            <p class="text-gray-500 mt-2 text-lg">
                Seleccione el tipo de sistema
            </p>
        @endif

        <div class="h-1 w-20 bg-amber-500 mx-auto mt-4 rounded-full"></div>
    </header>

    {{-- GRID DE SISTEMAS --}}
    @if (!$sistemaSeleccionado)

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 animate-fade-in">
            @php
                $sistemas = [
                    [
                        'id' => 'Sistema Nova',
                        'icon' => 'fa-star',
                        'color' => 'blue',
                        'desc' => 'Alta gama y perfiles reforzados.',
                    ],
                    [
                        'id' => 'Persiana',
                        'icon' => 'fa-bars',
                        'color' => 'amber',
                        'desc' => 'Privacidad con sistema enrollable.',
                    ],
                    [
                        'id' => 'Doble Corrediza',
                        'icon' => 'fa-arrows-left-right',
                        'color' => 'blue',
                        'desc' => 'Ambas hojas móviles.',
                    ],
                    [
                        'id' => 'Batiente',
                        'icon' => 'fa-door-open',
                        'color' => 'purple',
                        'desc' => 'Apertura tradicional de 90°.',
                    ],
                    [
                        'id' => 'Proyectante',
                        'icon' => 'fa-up-right-from-square',
                        'color' => 'emerald',
                        'desc' => 'Ventilación controlada superior.',
                    ],
                ];
            @endphp

            @foreach ($sistemas as $s)
                <button wire:click="seleccionar('{{ $s['id'] }}')"
                    class="group p-6 bg-white/60 backdrop-blur-md border-2 border-transparent rounded-3xl shadow-sm transition-all duration-300 hover:border-blue-500 hover:bg-white/80 flex flex-col justify-between text-center">
                    <div>
                        <div
                            class="w-16 h-16 bg-{{ $s['color'] }}-100 text-{{ $s['color'] }}-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid {{ $s['icon'] }} text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $s['id'] }}</h3>
                        <p class="text-sm text-gray-500 mt-2">{{ $s['desc'] }}</p>
                    </div>
                </button>
            @endforeach
        </div>
    @elseif ($sistemaSeleccionado === 'Sistema Nova')
        <livewire:ventanas.sistema-nova />

    @endif
</div>
