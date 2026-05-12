<?php

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    public int $material = 7852;
    public float $anchoTotal = 90;
    public float $altoTotal = 220;
    public string $color = 'negro';

    public float $tubo = 2.5;
    public float $canal = 2.2;
    public float $cuadrado = 3.8;
    public float $paflon = 8.2;
    public float $luzArriba = 0.5;
    public float $luzAbajo = 1;
    public float $luzLados = 0.6;

    public array $datos = [];
    public array $data = [];

    public array $puertas = [];
    public int $puertaActiva = 0;

    public function mount()
    {
        $this->procesarPerfiles();

        // 👉 cargar desde sesión si existe
        if (session()->has('puertas')) {
            $this->puertas = session('puertas');
            $this->puertaActiva = 0;
            $this->cargarPuertaActiva();
        } else {
            $this->puertas = [array_merge(['nombre' => 'P - 1'], $this->snapshotPuerta())];
            $this->puertaActiva = 0;
            $this->recalcular();
        }
    }
    public function eliminarPuerta(int $index): void
    {
        if (count($this->puertas) <= 1) {
            return; // siempre debe quedar una
        }

        unset($this->puertas[$index]);
        $this->puertas = array_values($this->puertas);

        if ($this->puertaActiva >= count($this->puertas)) {
            $this->puertaActiva = count($this->puertas) - 1;
        }

        $this->cargarPuertaActiva();
        session()->put('puertas', $this->puertas);
        $this->dispatch('delete', 'Vista eliminada con exito');
    }

    private function snapshotPuerta(): array
    {
        return [
            'material' => $this->material,
            'anchoTotal' => $this->anchoTotal,
            'altoTotal' => $this->altoTotal,
            'conSobreluz' => $this->conSobreluz,
            'altoSobreluz' => $this->altoSobreluz,
            'datos' => $this->datos,
        ];
    }

    private function guardarPuertaActual(): void
    {
        if (!isset($this->puertas[$this->puertaActiva])) {
            return;
        }

        $this->puertas[$this->puertaActiva] = array_merge(['nombre' => $this->puertas[$this->puertaActiva]['nombre']], $this->snapshotPuerta());

        session()->put('puertas', $this->puertas);
    }

    private function cargarPuerta(int $i): void
    {
        $p = $this->puertas[$i];

        $this->material = $p['material'];
        $this->anchoTotal = $p['anchoTotal'];
        $this->altoTotal = $p['altoTotal'];
        $this->color = $p['color'];

        $this->recalcular();
    }
    public function cambiarPuerta(int $i): void
    {
        $this->guardarPuertaActual();
        $this->puertaActiva = $i;
        $this->cargarPuertaActiva();
    }
    private function cargarPuertaActiva(): void
    {
        if (!isset($this->puertas[$this->puertaActiva])) {
            return;
        }

        $p = $this->puertas[$this->puertaActiva];

        $this->material = $p['material'];
        $this->anchoTotal = $p['anchoTotal'];
        $this->altoTotal = $p['altoTotal'];
        $this->conSobreluz = $p['conSobreluz'] ?? false;
        $this->altoSobreluz = $p['altoSobreluz'] ?? 30;
        $this->datos = $p['datos'] ?? [];
    }

    public function agregarPuerta(): void
    {
        $this->guardarPuertaActual();

        $this->material = 7852;
        $this->anchoTotal = 90;
        $this->altoTotal = 220;
        $this->color = 'negro';
        $this->recalcular();

        $this->puertas[] = array_merge(['nombre' => 'P - ' . (count($this->puertas) + 1)], $this->snapshotPuerta());

        $this->puertaActiva = count($this->puertas) - 1;
        $this->dispatch('correcto', 'Vista agregada con exito');
    }

    private function resetPuertas(): void
    {
        $this->puertas = [['nombre' => 'P - 1']];

        $this->puertaActiva = 0;

        $this->material = 7852;
        $this->anchoTotal = 90;
        $this->altoTotal = 220;
        $this->color = 'negro';
        $this->datos = [];

        $this->recalcular();
    }

    public function updated($prop)
    {
        $this->guardarPuertaActual();

        session()->put('puertas', $this->puertas);
        if (in_array($prop, ['material', 'anchoTotal', 'altoTotal', 'conSobreluz', 'altoSobreluz'])) {
            $this->recalcular();
            $this->guardarPuertaActual();
        }
    }
    private function calcularMarco(float $perfil, int $codigo): void
    {
        $this->datos["{$codigo} - Lados"] = [
            'medida' => $this->altoTotal,
            'cantidad' => 2,
        ];

        $this->datos["{$codigo} - Arriba"] = [
            'medida' => $this->anchoTotal - $perfil * 2,
            'cantidad' => $this->conSobreluz ? 2 : 1,
        ];
    }
    private function calcularHoja(float $perfil): void
    {
        $altoHoja = $this->conSobreluz ? $this->altoTotal - $this->altoSobreluz : $this->altoTotal;

        $anchoHoja = $this->anchoTotal - $perfil * 2 - $this->luzLados;

        $this->datos['5414 - Arriba y Abajo'] = [
            'medida' => $anchoHoja - $this->cuadrado * 2,
            'cantidad' => 2,
        ];

        $this->datos['5414 - Lados'] = [
            'medida' => $altoHoja - $this->luzArriba - $this->luzAbajo - $perfil,
            'cantidad' => 2,
        ];

        $this->datos['5227 - Travesaño'] = [
            'medida' => $anchoHoja - $this->cuadrado * 2,
            'cantidad' => 1,
        ];

        // dd([
        //     $altoHoja,
        //     $this->altoTotal
        //     ,$this->altoSobreluz,
        //     $perfil

        //     ]);
    }
    private function calcularVidrios(float $perfil): void
    {
        $altoHoja = $this->conSobreluz ? $this->altoTotal - $this->altoSobreluz : $this->altoTotal;

        $anchoVidrio = $this->anchoTotal - $perfil * 2 - $this->luzLados - $this->cuadrado * 2;

        $descuentoLuces = $this->luzArriba + $this->luzAbajo;
        $descuentoMarco = $this->cuadrado * 2;
        $descuentoCentral = $this->paflon;

        $altoUtil = $altoHoja - $descuentoLuces - $descuentoMarco - $descuentoCentral - $perfil;

        $altoVidrio = $altoUtil / 2;

        $this->datos['Vidrio'] = [
            'medida' => number_format($altoVidrio - 0.5, 2) . ' x ' . number_format($anchoVidrio - 0.5, 2),
            'cantidad' => 2,
        ];
    }
    private function calcularSobreluz(float $perfil, int $codigo): void
    {
        // Marco sobreluz
        $ancho = $this->anchoTotal - $perfil * 2;

        // $this->datos["{$codigo} - Sobreluz"] = [
        //     'medida' => $ancho,
        //     'cantidad' => 1,
        // ];

        $altoVidrio = $this->altoSobreluz - $perfil - 0.5;

        $this->datos['Vidrio Sobreluz'] = [
            'medida' => number_format($altoVidrio, 2) . ' x ' . number_format($ancho - 0.5, 2),
            'cantidad' => 1,
        ];
    }

    private function recalcular(): void
    {
        $this->datos = [];

        $perfil = $this->material == 7852 ? $this->tubo : $this->canal;
        $codigo = $this->material;

        $this->calcularMarco($perfil, $codigo);
        $this->calcularHoja($perfil);
        $this->calcularVidrios($perfil);

        if ($this->conSobreluz) {
            $this->calcularSobreluz($perfil, $codigo);
        }

        $this->calcularAccesorios();
    }
    private function calcularAccesorios(): void
    {
        $this->datos['Bisagras'] = [
            'medida' => '3x3',
            'cantidad' => 3,
        ];

        $this->datos['Chapas'] = [
            'medida' => 'Unidad',
            'cantidad' => 1,
        ];
    }

    public function procesarPerfiles()
    {
        $ruta = public_path('datos.xlsx');
        if (!file_exists($ruta)) {
            return;
        }

        $this->data = collect(Excel::toArray([], $ruta)[0])
            ->pluck(0)
            ->filter()
            ->values()
            ->toArray();
    }
    public function imprimirTodo()
    {
        $this->guardarPuertaActual();

        session()->put('puertas', $this->puertas);
        $datos = session('puertas', []);
        // dd($datos);
        $this->dispatch('imprimir-puertas');
    }
    protected $listeners = ['limpiar-puertas'];

    public function limpiarPuertas(): void
    {
        session()->forget('puertas');
        $this->resetPuertas();
    }

    public bool $conSobreluz = false;
    public float $altoSobreluz = 30;
};

?>

<div class="p-2 md:p-6 max-w-5xl mx-auto mb-[30px] font-sans">

    <div wire:loading class="fixed inset-0 z-50 bg-gray-500/50">
        <div class="absolute inset-0 flex items-center justify-center">
            <img src="{{ asset('img/tape.gif') }}" alt="" srcset="" class="rounded-full w-40 h-40">
        </div>
    </div>

    <div
        class="lg:flex grid grid-cols-1 lg:gap-2 justify-between items-center gap-1 mb-6 px-2 border-b border-gray-200 overflow-x-auto">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($puertas as $index => $v)
                <div class="relative group">
                    <button wire:click="cambiarPuerta({{ $index }})"
                        class="px-6 py-2 text-xs font-black uppercase tracking-tighter rounded-t-xl border transition-all
                {{ $puertaActiva == $index
                    ? 'bg-white border-gray-200 text-blue-600 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]'
                    : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}">
                        <i class="fa-solid fa-door-closed"></i> {{ $v['nombre'] }}
                    </button>


                    <button wire:click.stop="eliminarPuerta({{ $index }})"
                        class="absolute -top-1 -right-1 w-4 h-4 text-[10px]
                       bg-red-500 text-white rounded-full
                       flex items-center justify-center
                       opacity-0 group-hover:opacity-100 transition">
                        ✕
                    </button>

                </div>
            @endforeach

            <button wire:click="agregarPuerta"
                class="ml-2 px-4 py-2 text-xs font-bold text-blue-600 hover:bg-blue-50 rounded-lg">
                <i class="fa-solid fa-plus-circle"></i> Nuevo
            </button>

            <button wire:click="limpiarPuertas"
                class="ml-2 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg">
                <i class="fa-solid fa-trash"></i> Vaciar
            </button>
        </div>

        <div class="flex gap-2">

            <button wire:click="imprimirTodo()"
                class="px-6 py-2 bg-emerald-600 text-white text-xs font-black rounded-xl hover:bg-emerald-700">
                <i class="fa-solid fa-file-pdf"></i> IMPRIMIR
            </button>
        </div>
    </div>

    {{-- FORM --}}
    <div class="grid gap-4 p-4 mb-4 lg:mb-6
            grid-cols-3 lg:grid-cols-4 flex-wrap">

        {{-- ANCHO --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                Ancho (cm)
            </label>
            <input type="text" wire:model.lazy="anchoTotal" placeholder="90"
                class="w-full rounded-lg border border-gray-300 bg-white
                   px-3 py-2.5 text-sm font-medium text-gray-900
                   placeholder-gray-400
                   transition focus:border-indigo-500
                   focus:ring-2 focus:ring-indigo-200 focus:outline-none">
        </div>

        {{-- ALTO --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                Alto (cm)
            </label>
            <input type="text" wire:model.lazy="altoTotal" placeholder="220"
                class="w-full rounded-lg border border-gray-300 bg-white
                   px-3 py-2.5 text-sm font-medium text-gray-900
                   placeholder-gray-400
                   transition focus:border-indigo-500
                   focus:ring-2 focus:ring-indigo-200 focus:outline-none">
        </div>

        {{-- MATERIAL --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                Material
            </label>
            <select wire:model.live="material"
                class="w-full rounded-lg border border-gray-300 bg-white
                   px-3 py-2.5 text-sm font-medium text-gray-900
                   cursor-pointer
                   transition focus:border-indigo-500
                   focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                <option value="7830">Canal 60 - 7830</option>
                <option value="7852">Rectangular 60 - 7852</option>
            </select>
        </div>

        {{-- ALTO SOBRELUZ --}}
        @if ($conSobreluz)
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                    Alto sobreluz (cm)
                </label>
                <input type="text" wire:model.lazy="altoSobreluz" placeholder="40"
                    class="w-full rounded-lg border border-gray-300 bg-white
                       px-3 py-2.5 text-sm font-medium text-gray-900
                       placeholder-gray-400
                       transition focus:border-indigo-500
                       focus:ring-2 focus:ring-indigo-200 focus:outline-none">
            </div>
        @endif

        {{-- CHECKBOX SOBRELUZ --}}
        <div class="flex items-end sm:col-span-2 lg:col-span-5 pt-2">
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <input type="checkbox" wire:model.live="conSobreluz"
                    class="h-5 w-5 rounded border-gray-300
                       text-indigo-600
                       focus:ring-2 focus:ring-indigo-300">
                <span class="text-sm font-medium text-gray-700">
                    Puerta con sobreluz
                </span>
            </label>
        </div>

    </div>

    {{-- CONTENIDO --}}
    <div class="lg:flex gap-6">

        {{-- PLANO TÉCNICO --}}
<div class="w-full lg:w-1/2 bg-gray-200 border rounded-xl p-6 flex flex-col items-center">

    {{-- TÍTULO --}}
    <div class="mb-6 text-center">
        <div class="text-[11px] tracking-[0.2em] text-gray-600 font-mono">
            PLANO TÉCNICO
        </div>
        <div class="text-sm font-semibold text-gray-800">
            Serie {{ $material }}
        </div>
    </div>

    {{-- CONTENEDOR --}}
    <div
        class="relative flex border-b-0  flex-col w-[220px] lg:w-[280px]
        {{ $conSobreluz ? 'h-[500px] lg:h-[600px]' : 'h-[450px] lg:h-[520px]' }}
        border-[5px] border-black bg-white"

        style="
            border-color: {{ $color === 'negro' ? '#111' : '#444' }};
            background-image:
                linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px);
            background-size: 20px 20px;
        "
    >

        {{-- COTA ALTO --}}
        <div class="absolute -left-16 top-0 h-full flex flex-col items-center justify-between text-[10px] font-mono text-black">

            <span>{{ $altoTotal }} cm</span>

            <div class="relative flex-1 w-px bg-black">
                <span class="absolute -top-1 left-[-3px] border-l-[4px] border-r-[4px] border-b-[6px] border-transparent border-b-black"></span>
                <span class="absolute -bottom-1 left-[-3px] border-l-[4px] border-r-[4px] border-t-[6px] border-transparent border-t-black"></span>
            </div>

            <span class="tracking-widest">ALTO</span>
        </div>

        {{-- SOBRELUZ --}}
        @if ($conSobreluz && isset($datos['Vidrio Sobreluz']))
            <div
                class="relative flex items-center justify-center border-b-[4px] border-black"
                style="
                    height: {{ max(80, $altoSobreluz) }}px;
                    background: linear-gradient(135deg,#e0f2fe 0%,#7dd3fc55 100%);
                "
            >

                <div class="text-center font-mono">
                    <div class="text-[10px] tracking-wider text-gray-700">
                        SOBRELUZ
                    </div>
                    <div class="text-[11px] text-black">
                        {{ $datos['Vidrio Sobreluz']['medida'] }}
                    </div>
                </div>

                {{-- brillo vidrio --}}
                <div class="absolute top-0 left-0 w-full h-1/3 bg-white/20"></div>

                <span class="absolute right-2 top-1 text-[9px] text-black font-mono">
                    {{ $altoSobreluz }} cm
                </span>
            </div>
        @endif

        {{-- CUERPO --}}
        <div class="flex flex-col flex-1 justify-between p-[2px]">

            {{-- VIDRIO SUPERIOR --}}
            <div class="relative flex flex-col items-center justify-center flex-1 border-[5px] border-b-0 border-black"
                style="background: linear-gradient(135deg,#e0f2fe 0%,#7dd3fc55 100%);">

                <span class="text-[10px] font-mono text-black tracking-wider">VIDRIO</span>
                <span class="text-[11px] font-mono text-black">
                    {{ $datos['Vidrio']['medida'] ?? '—' }}
                </span>

                <div class="absolute top-0 left-0 w-full h-1/3 bg-white/20"></div>
            </div>

            {{-- TRAVESAÑO --}}
            <div
                class="relative flex items-center justify-between h-[38px] px-3 text-[10px] text-white border-y border-black"
                style="
                    background: repeating-linear-gradient(
                        45deg,
                        #2f2f2f,
                        #2f2f2f 2px,
                        #444 2px,
                        #444 6px
                    );
                "
            >
                <span class="font-mono tracking-wider">REF 5227</span>

                <div class="flex items-center gap-2">
                    <span class="font-mono">
                        {{ $datos['5227']['medida'] ?? '—' }} cm
                    </span>

                    {{-- perilla --}}
                    <div class="w-5 h-5 flex items-center justify-center rounded-full border border-black bg-gray-300">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    </div>
                </div>
            </div>

            {{-- BISAGRAS --}}
            <div class="absolute left-[-6px] top-0 h-full flex flex-col justify-around py-12">
                @for ($i = 0; $i < 3; $i++)
                    <div class="w-2 h-8 bg-gray-500 border border-black relative">
                        <div class="absolute inset-y-0 left-1/2 w-[1px] bg-black"></div>
                    </div>
                @endfor
            </div>

            {{-- VIDRIO INFERIOR --}}
            <div class="relative flex flex-col items-center justify-center flex-1 border-[5px] border-t-0 border-black"
                style="background: linear-gradient(135deg,#e0f2fe 0%,#7dd3fc55 100%);">

                <span class="text-[10px] font-mono text-black tracking-wider">VIDRIO</span>
                <span class="text-[11px] font-mono text-black">
                    {{ $datos['Vidrio']['medida'] ?? '—' }}
                </span>

                <div class="absolute top-0 left-0 w-full h-1/3 bg-white/20"></div>
            </div>
        </div>

        {{-- COTA ANCHO --}}
        <div class="absolute -bottom-12 left-0 w-full flex items-center justify-between text-[10px] font-mono text-black">

            <span>{{ $anchoTotal }} cm</span>

            <div class="relative flex-1 h-px mx-2 bg-black">
                <span class="absolute left-0 top-[-3px] border-t-[4px] border-b-[4px] border-r-[6px] border-transparent border-r-black"></span>
                <span class="absolute right-0 top-[-3px] border-t-[4px] border-b-[4px] border-l-[6px] border-transparent border-l-black"></span>
            </div>

            <span class="tracking-widest">ANCHO</span>
        </div>

    </div>
</div>

        {{-- TABLA ACCESORIOS --}}
        <div
            class="w-full lg:w-1/2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-6 lg:mt-0">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 bg-blue-600 text-white">
                <div class="flex items-center gap-2 font-semibold">
                    <i class="fa-solid fa-toolbox"></i>
                    Accesorios
                </div>
                <span class="text-xs bg-blue-500 px-3 py-1 rounded-full">
                    {{ count($datos) }} items
                </span>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-blue-700 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Perfil</th>
                            <th class="px-6 py-3 text-center">Medida</th>
                            <th class="px-6 py-3 text-right">Cant.</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 text-xs">
                        @forelse($datos as $nombre => $item)
                            @if (str_starts_with($nombre, 'Vidrio'))
                                @continue
                            @endif
                            @php
                                // Extraer código desde el nombre del perfil
                                preg_match('/^\d+/', $nombre, $m);
                                $codigoBuscado = $m[0] ?? null;

                                // Valor por defecto
                                $nombreProductoExcel = $nombre;

                                // Buscar coincidencia en Excel
                                if ($codigoBuscado) {
                                    foreach ($data ?? [] as $nombreCompleto) {
                                        if (str_contains((string) $nombreCompleto, (string) $codigoBuscado)) {
                                            $nombreProductoExcel = $nombreCompleto;
                                            break;
                                        }
                                    }
                                }
                            @endphp


                            <tr class="hover:bg-blue-50 transition">

                                {{-- PERFIL CALCULADO --}}
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    {{ $nombreProductoExcel }}
                                </td>

                                {{-- MEDIDA --}}
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full
                   bg-indigo-100 text-indigo-700 font-mono text-xs">
                                        {{ $item['medida'] }}
                                    </span>
                                </td>

                                {{-- CANTIDAD --}}
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="inline-flex items-center justify-center min-w-[3rem]
                   px-3 py-1 rounded-full
                   bg-emerald-100 text-emerald-700 font-bold tabular-nums">
                                        {{ $item['cantidad'] }}
                                    </span>
                                </td>

                                {{-- NOMBRE DESDE EXCEL --}}
                                {{-- <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
            {{ $nombreProductoExcel === 'Perfil no identificado' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $nombreProductoExcel }}
                                    </span>
                                </td> --}}

                            </tr>

                        @empty
                            <tr>
                                <td colspan="3" class="py-14 text-center text-slate-400 italic">
                                    No hay datos calculados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <iframe wire:ignore id="iframePuertas" data-url="{{ route('puertas.imprimir') }}" class="hidden"></iframe>


        <script>
            window.addEventListener('imprimir-puertas', () => {
                const iframe = document.getElementById('iframePuertas');
                iframe.src = iframe.dataset.url;

                iframe.onload = function() {

                    setTimeout(() => {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();

                    }, 600);
                };
            });
        </script>

    </div>

    {{-- ESTILOS --}}
    <style>
        .input-base {
            @apply w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200;
        }

        body {
            background: #f3f4f6;
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
</div>
<script>
    window.addEventListener('correcto', () => {
        iziToast.success({
            message: event.detail,
            position: 'topRight',
            timeout: 5000,
            progressBar: true,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: true,
            theme: 'light',
            transitionIn: 'bounce',
            zindex: 999999
        });
    });
    window.addEventListener('delete', () => {
        iziToast.info({
            message: event.detail,
            position: 'topRight',
            timeout: 5000,
            progressBar: true,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: true,
            theme: 'light',
            transitionIn: 'bounce',
            zindex: 999999
        });
    });
</script>
