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
            <div role="status">
                <svg aria-hidden="true" class="w-16 h-16 text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                        fill="currentColor" />
                    <path
                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0872 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                        fill="currentFill" />
                </svg>
                <span class="sr-only">Cargando...</span>
            </div>
        </div>
    </div>
</div>
