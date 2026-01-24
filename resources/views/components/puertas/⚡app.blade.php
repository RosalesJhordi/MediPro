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
    }

    private function snapshotPuerta(): array
    {
        return [
            'material' => $this->material,
            'anchoTotal' => $this->anchoTotal,
            'altoTotal' => $this->altoTotal,
            'color' => $this->color,
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
        $this->color = $p['color'];
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

        $this->datos["{$codigo} - Sobreluz Arriba"] = [
            'medida' => $ancho,
            'cantidad' => 1,
        ];

        // 🔴 DESCUENTOS REALES DEL PUENTE
        $altoVidrio =
            $this->altoSobreluz -
            $perfil - // solo UN perfil arriba
            0.5; // holgura vidrio

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
        dd($datos);
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

    {{-- LOADER --}}
    <div wire:loading class="fixed inset-0 z-50 bg-gray-500/50">
        <div class="absolute inset-0 flex items-center justify-center">
            <svg class="w-16 h-16 text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101">
                <path fill="currentColor"
                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908Z" />
                <path fill="currentFill" d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539" />
            </svg>
        </div>
    </div>

    {{-- TÍTULO --}}
    <h1 class="text-3xl font-extrabold mb-6 text-center text-blue-800">
        Puerta
    </h1>

    {{-- TABS --}}
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

                    {{-- ❌ eliminar --}}
                    @if (count($puertas) > 1)
                        <button wire:click.stop="eliminarPuerta({{ $index }})"
                            class="absolute -top-1 -right-1 w-4 h-4 text-[10px]
                       bg-red-500 text-white rounded-full
                       flex items-center justify-center
                       opacity-0 group-hover:opacity-100 transition">
                            ✕
                        </button>
                    @endif
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
            <button class="px-6 py-2 bg-orange-600 text-white text-xs font-black rounded-xl hover:bg-orange-700">
                <i class="fa-solid fa-scissors"></i> Optimizar
            </button>

            <button wire:click="imprimirTodo()"
                class="px-6 py-2 bg-emerald-600 text-white text-xs font-black rounded-xl hover:bg-emerald-700">
                <i class="fa-solid fa-file-pdf"></i> IMPRIMIR
            </button>
        </div>
    </div>

    {{-- FORM --}}
    <div class="grid gap-4 p-4 mb-6
            grid-cols-3 lg:grid-cols-4">

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
        <div class="w-full lg:w-1/2 bg-gray-50 border rounded-2xl p-6 shadow-inner flex flex-col items-center">

            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full mb-6">
                Plano Técnico · Serie {{ $material }}
            </span>

            {{-- CONTENEDOR GENERAL --}}
            <div class="relative w-[280px]
        {{ $conSobreluz ? 'h-[600px]' : 'h-[520px]' }}
        border-[8px] shadow-xl ring-1 ring-black/10
        flex flex-col bg-white"
                style="border-color: {{ $color === 'negro' ? '#1a1a1a' : '#525252' }}">

                {{-- COTA ALTO TOTAL --}}
                <div
                    class="absolute -left-14 top-0 h-full flex flex-col items-center justify-between text-[10px] text-gray-600">
                    <span>{{ $altoTotal }} cm</span>
                    <div class="w-px flex-1 bg-gray-400"></div>
                    <span>ALTO</span>
                </div>

                {{-- SOBRELUZ --}}
                @if ($conSobreluz && isset($datos['Vidrio Sobreluz']))
                    <div class="relative bg-sky-200/50 border-b-[6px] border-black
                flex items-center justify-center"
                        style="height: {{ max(80, $altoSobreluz) }}px">

                        <div class="text-center">
                            <div class="text-[10px] font-black uppercase text-gray-700">
                                Vidrio Sobreluz
                            </div>
                            <div class="text-[11px] font-mono text-gray-800">
                                {{ $datos['Vidrio Sobreluz']['medida'] }}
                            </div>
                        </div>

                        <span class="absolute right-2 top-1 text-[9px] text-gray-500">
                            {{ $altoSobreluz }} cm
                        </span>
                    </div>
                @endif

                {{-- PUERTA --}}
                <div class="flex-1 flex flex-col justify-between p-1">

                    {{-- VIDRIO SUPERIOR --}}
                    <div
                        class="flex-1 bg-sky-200/50 border border-sky-300
                flex flex-col items-center justify-center shadow-inner">

                        <span class="text-[10px] font-black uppercase text-sky-800">
                            Vidrio
                        </span>
                        <span class="text-[11px] font-mono text-sky-900">
                            {{ $datos['Vidrio']['medida'] ?? '—' }}
                        </span>
                    </div>

                    {{-- TRAVESAÑO --}}
                    <div
                        class="h-[40px] bg-gray-700
                flex items-center justify-between px-3
                text-white text-xs relative">

                        <span class="font-bold">REF 5227</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono">
                                {{ $datos['5227 - Travesaño']['medida'] ?? '—' }} cm
                            </span>
                            <div
                                class="w-5 h-5 rounded-full border border-white/30 flex items-center justify-center bg-gray-400/20">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full shadow-sm"></div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute left-[-4px] top-0 h-full flex flex-col justify-around py-12">
                        <div class="w-2 h-8 bg-gray-400 rounded-sm border border-black/20"></div>
                        <div class="w-2 h-8 bg-gray-400 rounded-sm border border-black/20"></div>
                        <div class="w-2 h-8 bg-gray-400 rounded-sm border border-black/20"></div>
                    </div>
                    {{-- VIDRIO INFERIOR --}}
                    <div
                        class="flex-1 bg-sky-200/50 border border-sky-300
                flex flex-col items-center justify-center shadow-inner">

                        <span class="text-[10px] font-black uppercase text-sky-800">
                            Vidrio
                        </span>
                        <span class="text-[11px] font-mono text-sky-900">
                            {{ $datos['Vidrio']['medida'] ?? '—' }}
                        </span>
                    </div>
                </div>

                {{-- COTA ANCHO --}}
                <div
                    class="absolute -bottom-10 left-0 w-full flex items-center justify-between text-[10px] text-gray-600">
                    <span>{{ $anchoTotal }} cm</span>
                    <div class="h-px flex-1 bg-gray-400 mx-2"></div>
                    <span>ANCHO</span>
                </div>
            </div>
        </div>



        {{-- TABLA --}}
        <div class="w-full lg:w-1/2 bg-white rounded-2xl border shadow-sm overflow-hidden mt-6 lg:mt-0">

            <div class="bg-gray-50 px-5 py-4 border-b font-bold flex items-center gap-2">
                <i class="fa-solid fa-toolbox text-blue-600"></i>
                Accesorios
            </div>

            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Perfil</th>
                        <th class="px-6 py-3 text-center">Medida</th>
                        <th class="px-6 py-3 text-right">Cant.</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($datos as $nombre => $item)
                        <tr class="hover:bg-blue-50/30">
                            <td class="px-6 py-4 font-semibold text-slate-700">
                                {{ $nombre }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono">
                                {{ $item['medida'] }}
                            </td>
                            <td class="px-6 py-4 text-right font-black tabular-nums">
                                {{ $item['cantidad'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-12 text-gray-400 italic">
                                No hay datos calculados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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

    <script>
        function imprimirProyecto() {
            Livewire.dispatch('preparar-impresion');
            window.print();
        }
    </script>

</div>
