<?php

use Livewire\Component;

new class extends Component {
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

    <div>
        {{-- HEADER --}}
        <header class="mb-6 text-center">

            <h1 class="text-3xl md:text-5xl font-black tracking-tight text-gray-800">

                @if (!$sistemaSeleccionado)
                    <span class="relative inline-block">

                        Configuración

                        <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-16 h-1 rounded-full bg-blue-500">
                        </span>

                    </span>
                @else
                    <div class="flex flex-col items-center gap-5 mt-4 animate-fade-in">

                        <span class="text-3xl md:text-5xl font-black tracking-tight text-blue-600 animate-pulse">

                            {{ $sistemaSeleccionado }}

                        </span>

                        <button wire:click="resetear"
                            class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:border-blue-300 hover:-translate-y-0.5 shadow-sm hover:shadow-md transition-all duration-300">

                            <i
                                class="fa-solid fa-rotate-left text-xs transition-transform duration-500 group-hover:-rotate-180">
                            </i>

                            <span class="text-sm font-semibold">
                                Cambiar sistema
                            </span>

                        </button>

                    </div>
                @endif

            </h1>

            @if (!$sistemaSeleccionado)
                <p class="text-gray-500 mt-5 text-lg animate-fade-in">
                    Seleccione el tipo de sistema
                </p>
            @endif

        </header>

        {{-- GRID DE SISTEMAS --}}
        @if (!$sistemaSeleccionado)

            @php
                $sistemas = [
                    [
                        'id' => 'Sistema Nova',
                        'icon' => 'fa-star',
                        'color' => 'primary',
                        'desc' => 'Alta gama y perfiles reforzados.',
                    ],
                    [
                        'id' => 'Vitroven',
                        'icon' => 'fa-bars',
                        'color' => 'warning',
                        'desc' => 'Privacidad y control solar.',
                    ],
                    [
                        'id' => 'Doble Corrediza',
                        'icon' => 'fa-arrows-left-right',
                        'color' => 'secondary',
                        'desc' => 'Movimiento suave y moderno.',
                    ],
                    [
                        'id' => 'Batiente',
                        'icon' => 'fa-door-open',
                        'color' => 'success',
                        'desc' => 'Apertura clásica optimizada.',
                    ],
                    [
                        'id' => 'Proyectante',
                        'icon' => 'fa-up-right-from-square',
                        'color' => 'accent',
                        'desc' => 'Ventilación inteligente.',
                    ],
                ];
            @endphp

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-5">

                @foreach ($sistemas as $s)
                    <button wire:click="seleccionar('{{ $s['id'] }}')" class="group">

                        <div
                            class="card bg-base-100 border flex border-base-300 hover:border-{{ $s['color'] }} hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 overflow-hidden">

                            <div class="card-body justify-start items-start p-5 md:p-7">

                                <div
                                    class="w-16 h-16 rounded-2xl bg-{{ $s['color'] }}/10 text-{{ $s['color'] }} flex items-center justify-center mb-6 transition-all duration-500 group-hover:scale-110">

                                    <i class="fa-solid {{ $s['icon'] }} text-2xl"></i>

                                </div>

                                <h2 class="card-title text-lg md:text-2xl font-black">
                                    {{ $s['id'] }}
                                </h2>

                                <p class="text-base-content/60 mt-2">
                                    {{ $s['desc'] }}
                                </p>

                                <div class="mt-6">

                                    <div class="btn btn-{{ $s['color'] }} btn-sm rounded-xl">

                                        Abrir sistema

                                        <i class="fa-solid fa-arrow-right"></i>

                                    </div>

                                </div>

                            </div>

                            {{-- EFECTO --}}
                            <div
                                class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500 bg-gradient-to-br from-{{ $s['color'] }}/5 to-transparent pointer-events-none">
                            </div>

                        </div>

                    </button>
                @endforeach

            </div>
        @elseif ($sistemaSeleccionado === 'Sistema Nova')
            <livewire:ventanas.sistema-nova />
        @elseif ($sistemaSeleccionado === 'Vitroven')
            <livewire:ventanas.persiana/>
        @else
            <div class="min-h-screen bg-slate-50 flex items-center justify-center px-6 py-12">
                <div
                    class="relative max-w-2xl w-full bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-100 p-10 md:p-16 text-center overflow-hidden">

                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-amber-100 rounded-full blur-3xl opacity-50">
                    </div>
                    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-orange-100 rounded-full blur-3xl opacity-50">
                    </div>

                    <div class="relative">
                        <div class="mb-12 inline-block">
                            <div class="relative transform transition-all duration-700 hover:scale-110">

                                <div class="animate-bounce" style="animation-duration: 3s;">
                                    <img src="{{ asset('img/rocket-0d392ed0.webp') }}" alt="Cohete Filament"
                                        class="w-48 h-48 object-contain drop-shadow-[0_20px_30px_rgba(245,158,11,0.4)] mx-auto rotate-[15deg]" />
                                </div>

                                <div class="w-24 h-2 bg-slate-900/5 rounded-[100%] mx-auto mt-2 blur-md animate-pulse">
                                </div>
                            </div>
                        </div>

                        <h1 class="text-4xl md:text-6xl font-black text-slate-900 mb-6 tracking-tighter">
                            Estamos <span class="text-amber-500">trabajando</span>
                        </h1>

                        <p class="text-slate-500 text-lg font-medium mb-10 max-w-sm mx-auto">
                            Nuestra plataforma de <span class="text-slate-800 font-bold">MediPro</span> está a punto de
                            despegar.
                        </p>

                        <div class="flex justify-center">
                            <div
                                class="group relative inline-flex items-center space-x-3 px-10 py-4 bg-slate-900 text-white rounded-2xl font-bold shadow-xl transition-all hover:bg-slate-800 active:scale-95 cursor-pointer">
                                <span class="flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                </span>
                                <span class="tracking-widest uppercase text-sm">Próximamente</span>
                            </div>
                        </div>

                        <div
                            class="mt-16 pt-8 border-t border-slate-100 flex items-center justify-between text-slate-400">
                            <span class="text-[10px] font-bold tracking-[0.2em] uppercase">v2.0 Stable</span>
                            <p class="text-[10px] tracking-[0.2em] uppercase text-gray-400">
                                by <a href="https://www.facebook.com/share/1Eh3Dx3iKB/" target="_blank"
                                    rel="noopener noreferrer">
                                    <span class="font-bold text-blue-600">Jhon Rosales</span></a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div wire:loading class="fixed inset-0 z-50 bg-gray-500/50">
        <div class="absolute inset-0 flex items-center justify-center">
            <img src="{{ asset('img/tape.gif') }}" alt="" srcset="" class="rounded-full w-40 h-40">
        </div>
    </div>
</div>
