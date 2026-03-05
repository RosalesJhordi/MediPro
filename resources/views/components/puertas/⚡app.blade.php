<?php

use Livewire\Component;

new class extends Component {
    public $PuertaSeleccionado = null;

    public function mount()
    {
        $this->PuertaSeleccionado = session('PuertaSeleccionado');
    }

    public function seleccionar($sistema)
    {
        logger('CLICK OK: ' . $sistema); // o dump($sistema)
        $this->PuertaSeleccionado = $sistema;

        session(['PuertaSeleccionado' => $sistema]);
    }

    public function resetear()
    {
        $this->PuertaSeleccionado = null;
        session()->forget('PuertaSeleccionado');
    }
};
?>

<div class="p-2 md:p-5 text-center max-w-7xl mx-auto">

    <div>
        {{-- HEADER --}}
        <header class="mb-4 transition-all duration-500">
            <h1 class="text-2xl md:text-4xl font-black text-gray-800 tracking-tight">
                @if (!$PuertaSeleccionado)
                    Configuración
                @else
                    <div class="flex flex-col items-center gap-6 mt-4 animate-fade-in">

                        <span class="text-2xl md:text-4xl font-black text-blue-600 tracking-tighter drop-shadow-sm">
                            {{ $PuertaSeleccionado }}
                        </span>

                        <button wire:click="resetear"
                            class="group flex items-center gap-2 px-3 py-1 bg-white border border-gray-200 hover:border-amber-300 hover:bg-amber-50 text-gray-500 hover:text-amber-700 rounded-lg transition-all duration-300 shadow-sm hover:shadow-md">
                            <i class="fa-solid fa-rotate-left text-xs transition-transform group-hover:-rotate-180"></i>
                            <span class="text-xs font-semibold">
                                Cambiar
                            </span>
                        </button>

                    </div>
                @endif
            </h1>

            @if (!$PuertaSeleccionado)
                <p class="text-gray-500 mt-2 text-lg">
                    Seleccione el tipo de sistema
                </p>
            @endif

            <div class="h-1 w-20 bg-amber-500 mx-auto mt-4 rounded-full"></div>
        </header>

        {{-- GRID DE SISTEMAS --}}
        @if (!$PuertaSeleccionado)

            <div class="grid lg:grid-cols-3 grid-cols-2 gap-2 lg:gap-8 animate-fade-in">
                @php
                    $puertas = [
                        [
                            'id' => 'Puerta Clásica',
                            'icon' => 'fa-shield-halved',
                            'color' => 'slate',
                            'desc' => 'Perfiles reforzados y alta durabilidad.',
                        ],
                        [
                            'id' => 'Puerta - Diseño Personalizable',
                            'icon' => 'fa-sliders',
                            'color' => 'cyan',
                            'desc' => 'Configuración estética y técnica a medida.',
                        ],
                        [
                            'id' => 'Puerta Corrediza - 1 Hoja',
                            'icon' => 'fa-arrows-left-right',
                            'color' => 'emerald',
                            'desc' => '1 Hoja móvile con guiado lineal.',
                        ],
                        [
                            'id' => 'Puerta 2 Hojas',
                            'icon' => 'fa-door-closed',
                            'color' => 'amber',
                            'desc' => 'Doble hoja abatible con cierre central.',
                        ],
                        [
                            'id' => 'Puerta Corrediza - 2 Hoja',
                            'icon' => 'fa-arrows-left-right',
                            'color' => 'blue',
                            'desc' => '2 Hojas móviles con guiado lineal.',
                        ],
                    ];

                @endphp

                @foreach ($puertas as $s)
                    <button wire:click="seleccionar('{{ $s['id'] }}')"
                        class="group lg:p-6 p-2 bg-white/60  shadow-md backdrop-blur-md border-2 border-transparent rounded-3xl transition-all duration-300 hover:border-blue-500 hover:bg-white/80 flex flex-col justify-between text-center">
                        <div>
                            <div
                                class="w-16 h-16 bg-{{ $s['color'] }}-100 text-{{ $s['color'] }}-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid {{ $s['icon'] }} text-2xl"></i>
                            </div>
                            <h3 class="lg:text-xl text-md font-bold text-gray-800">{{ $s['id'] }}</h3>
                            <p class="text-xs lg:text-sm text-gray-500 mt-2">{{ $s['desc'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        @elseif ($PuertaSeleccionado === 'Puerta Clásica')
            <livewire:puertas.normal />
        @elseif ($PuertaSeleccionado === 'Puerta - Diseño Personalizable')
            <livewire:puertas.personalizable />
        @elseif ($PuertaSeleccionado === 'Puerta Corrediza - 1 Hoja')
            <livewire:puertas.corrediza1hoja />
        @elseif ($PuertaSeleccionado === 'Puerta Corrediza - 2 Hoja')
            <livewire:puertas.corrediza2hoja />
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
