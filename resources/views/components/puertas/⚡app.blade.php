<?php

use Livewire\Component;

new class extends Component
{
    public int $material = 7852;
    public float $anchoTotal = 90;
    public float $altoTotal  = 220;
    public string $color = 'negro';
    public string $vista = '2d';

    public float $tubo      = 2.5;
    public float $canal     = 2.2;
    public float $cuadrado  = 3.8;
    public float $paflon    = 8.2;
    public float $luzArriba = 0.5;
    public float $luzAbajo  = 1;
    public float $luzLados  = 0.6;

    public array $datos = [];

    public function mount()
    {
        $this->procesarPerfiles();
        $this->recalcular();
    }
    public $data = [];
    public function procesarPerfiles()
    {
        $rutaArchivo = public_path('datos.xlsx');

        if (!file_exists($rutaArchivo)) {
            $catalogoExcel = [];
        } else {
            $datosExcel = \Maatwebsite\Excel\Facades\Excel::toArray([], $rutaArchivo)[0];
            $catalogoExcel = [];

            foreach ($datosExcel as $fila) {
                $nombreProducto = isset($fila[0]) ? trim($fila[0]) : null;

                if (!empty($nombreProducto)) {
                    $catalogoExcel[] = $nombreProducto;
                }
            }
        }

        $this->data = $catalogoExcel;
    }

    public function updatedMaterial($value)
    {
        $this->material = (int) $value; // 👈 CLAVE
        $this->recalcular();
    }

    public function updatedAnchoTotal()
    {
        $this->recalcular();
    }

    public function updatedAltoTotal()
    {
        $this->recalcular();
    }

    /* =======================
     *  LÓGICA PRINCIPAL
     * ======================= */
    private function recalcular(): void
    {
        $this->dispatch('redibujar-puerta', [
            'ancho' => $this->anchoTotal,
            'alto'  => $this->altoTotal,
        ]);

        match ($this->material) {
            7830 => $this->calcularMaterial('canal', 7830),
            7852 => $this->calcularMaterial('tubo', 7852),
            default => $this->datos = [],
        };
    }
    public $dimensionPlancha = '183x244';
    private function calcularMaterial(string $tipo, int $codigo): void
    {
        $perfil = $tipo === 'canal' ? $this->canal : $this->tubo;

        /* ===== Tubos ===== */
        $tuboLados  = $this->altoTotal;
        $tuboArriba = $this->anchoTotal - ($perfil * 2);

        /* ===== Cuadrados ===== */
        $cuadradoAA = $this->anchoTotal - ($perfil * 2);
        $lados      = $cuadradoAA - $this->luzLados - ($this->cuadrado * 2);

        $cuadradoLA = $this->altoTotal - $this->luzAbajo - $this->luzArriba;
        $arribas    = $cuadradoLA - $perfil;

        /* ===== Paflón ===== */
        $paflon = $cuadradoAA - ($this->cuadrado * 2) - $this->luzLados;

        /* ===== Vidrios ===== */
        $vidrioAlto  = ($arribas - ($this->cuadrado * 2) - $this->paflon) / 2;
        $vidrioAncho = $cuadradoAA - ($this->cuadrado * 2) - $this->luzLados;

        $this->datos = [
            "{$codigo} - Lados" => [
                'medida'   => $tuboLados,
                'cantidad' => 2,
            ],
            "{$codigo} - Arriba" => [
                'medida'   => $tuboArriba,
                'cantidad' => 1,
            ],
            '5414 - Arriba y Abajo' => [
                'medida'   => $lados,
                'cantidad' => 2,
            ],
            '5414 - Lados' => [
                'medida'   => $arribas,
                'cantidad' => 2,
            ],
            '5227 - Medio' => [
                'medida'   => $paflon,
                'cantidad' => 1,
            ],
            'Vidrios' => [
                'medida'   =>
                number_format($vidrioAlto - 0.5, 2) . ' x ' .
                    number_format($vidrioAncho - 0.5, 2),
                'cantidad' => 2,
            ],
            'Bisagras' => [
                'medida'   => '3x3',
                'cantidad' => 3,
            ],
            'Chapas' => [
                'medida'   => 'Unidad',
                'cantidad' => 1,
            ],
        ];
    }
};
?>

<div class="p-2 md:p-6 max-w-5xl mx-auto mb-[30px] font-sans">
    <!-- Título -->
    <h1 class="text-3xl font-extrabold mb-6 flex items-center justify-center gap-3 text-blue-800">
        Puerta
        {{-- <button
            class="relative group p-2 rounded-full bg-gray-800 text-orange-400
             hover:bg-gray-700 hover:text-white transition duration-300
             shadow-lg border border-gray-600"
            onclick="document.getElementById('modalPuerta').showModal()">
            <i class="fa-solid fa-arrows-rotate text-lg"></i>
            <span
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2
               text-xs text-white bg-gray-900 px-2 py-1 rounded-md
               opacity-0 group-hover:opacity-100 transition duration-200
               whitespace-nowrap pointer-events-none shadow-md">
                Cambiar modelo
            </span>
        </button> --}}
    </h1>


    <div class="grid grid-cols-3 lg:flex gap-10 text-black mb-2 justify-between items-center">
        <div class="flex w-full flex-col">
            <label class="block text-sm font-semibold">Ancho</label>
            <input type="text" value="90" wire:model.lazy='anchoTotal'
                class="w-full  input input-bordered px-2 bg-white">
        </div>

        <div class="flex w-full  flex-col">
            <label class="block text-sm font-semibold ">Alto</label>
            <input type="text" value="220" wire:model.lazy='altoTotal'
                class="w-full input input-bordered px-2 bg-white">
        </div>

        <div class="flex w-full  flex-col">
            <label class=" text-sm font-semibold text-gray-600">Material</label>
            <select wire:model.live='material' class="w-full  p-2 input-bordered  px-2 bg-white text-gray-800">
                <option value="7830">Canal 60 - 7830</option>
                <option value="7852">Rectangular 60 - 7852</option>
            </select>
        </div>
        <!-- COLOR -->
        <div class="flex w-full  flex-col">
            <label class="block text-sm font-semibold text-gray-600">Color</label>
            <select wire:model.live="color" class=" w-full  p-2 rounded-xl input-bordered bg-white text-gray-800">
                <option value="gris">Gris</option>
                <option value="negro">Negro</option>
            </select>
        </div>

        <!-- VISTA -->
        <div class="flex w-full  flex-col">
            <label class="block text-sm font-semibold text-gray-600">Vista</label>
            <select wire:model.live="vista" class="w-full p-2 rounded-xl input-bordered bg-white text-gray-800">
                <option value="2d">2D</option>
                <option value="3d">3D</option>
            </select>
        </div>
    </div>

    @if ($vista === '2d')
        <div class="block  lg:flex justify-center gap-2 items-center">
            <div
                class="flex w-1/2 flex-col items-center justify-center bg-gray-50 p-6 md:p-10 rounded-2xl border border-gray-200 shadow-inner">

                <div class="mb-8 text-center">
                    <span
                        class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full uppercase tracking-widest">
                        Plano Técnico: Serie {{ $material }}
                    </span>
                </div>

                <div class="relative" style="width: 260px; height: 520px;">

                    <div class="absolute -left-12 top-0 h-full flex items-center justify-center">
                        <div class="w-[1.5px] h-full relative bg-slate-400">
                            <span class="absolute -top-1 -left-[4px] text-[12px] text-slate-400">▲</span>
                            <span class="absolute -bottom-1 -left-[4px] text-[12px] text-slate-400">▼</span>

                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="-rotate-45 whitespace-nowrap bg-white px-1 text-[15px] font-bold text-gray-500 uppercase tracking-tighter">
                                    {{ $altoTotal }} cm
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-10 left-0 w-full flex justify-center">
                        <div class="h-[1.5px] w-full relative bg-slate-400">
                            <span class="absolute -left-1 -top-[5.5px] text-[10px] text-slate-400">◀</span>
                            <span class="absolute -right-1 -top-[5.5px] text-[10px] text-slate-400">▶</span>

                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="bg-white px-2 text-[15px] font-bold text-gray-500 uppercase tracking-tighter">
                                    {{ $anchoTotal }} cm
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-full border-[8px] shadow-xl relative flex flex-col justify-between p-1"
                        style="border-color: {{ $color === 'negro' ? '#1a1a1a' : '#525252' }}; background-color: #e5e7eb;">

                        <div class="w-full bg-sky-200/50 border border-sky-300 flex flex-col items-center justify-center relative overflow-hidden"
                            style="height: 44%;">
                            <div class="absolute top-0 left-0 w-full h-full opacity-20 pointer-events-none"
                                style="background: linear-gradient(135deg, transparent 45%, white 50%, transparent 55%); background-size: 200% 200%;">
                            </div>

                            <span class="text-[9px] font-black text-sky-700 uppercase">Vidrio 1</span>
                            <span class="text-[10px] font-mono font-bold text-sky-900">
                                @if (isset($datos['Vidrios']))
                                    {{ explode('x', $datos['Vidrios']['medida'])[1] }} x
                                    {{ explode('x', $datos['Vidrios']['medida'])[0] }}
                                @endif
                            </span>
                        </div>

                        <div class="w-full flex items-center justify-between px-3 shadow-sm relative"
                            style="height: 40px; background-color: {{ $color === 'negro' ? '#333' : '#666' }};">

                            <div
                                class="w-5 h-5 rounded-full border border-white/30 flex items-center justify-center bg-gray-400/20">
                                <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full shadow-sm"></div>
                            </div>

                            <span class="text-[10px] text-white/50 font-mono tracking-tighter">REF: 5227</span>

                            <div class="text-[10px] text-white font-bold">
                                {{ $anchoTotal - ($material == 7852 ? $tubo * 2 : $canal * 2) - 0.6 - 3.8 * 2 }} cm
                            </div>
                        </div>

                        <div class="w-full bg-sky-200/50 border border-sky-300 flex flex-col items-center justify-center relative"
                            style="height: 44%;">
                            <span class="text-[9px] font-black text-sky-700 uppercase">Vidrio 2</span>
                            <span class="text-[10px] font-mono font-bold text-sky-900">
                                @if (isset($datos['Vidrios']))
                                    {{ explode('x', $datos['Vidrios']['medida'])[1] }} x
                                    {{ explode('x', $datos['Vidrios']['medida'])[0] }}
                                @endif
                            </span>
                        </div>

                        <div class="absolute right-[-4px] top-0 h-full flex flex-col justify-around py-12">
                            <div class="w-2 h-6 bg-gray-400 rounded-sm border border-black/20"></div>
                            <div class="w-2 h-6 bg-gray-400 rounded-sm border border-black/20"></div>
                            <div class="w-2 h-6 bg-gray-400 rounded-sm border border-black/20"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-16 grid grid-cols-2 gap-x-6 gap-y-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-sky-200 border border-sky-400"></div>
                        <span class="text-[10px] text-gray-500 font-bold uppercase">Vidrio</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 border border-gray-400"
                            style="background-color: {{ $color === 'negro' ? '#1a1a1a' : '#525252' }}"></div>
                        <span class="text-[10px] text-gray-500 font-bold uppercase">Aluminio
                            {{ ucfirst($color) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-gray-200 border border-gray-400"></div>
                        <span class="text-[10px] text-gray-500 font-bold uppercase">3x3 Bisagras</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-yellow-200 border border-yellow-400"></div>
                        <span class="text-[10px] text-gray-500 font-bold uppercase">Chapa</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-start justify-center w-1/2">
                <div class="mt-6 w-full bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

                    <div class="bg-gray-50/50 border-b border-gray-200 px-5 py-4 flex items-center justify-between">
                        <h3 class="text-slate-800 font-bold text-sm md:text-base flex items-center gap-2">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600">
                                <i class="fa-solid fa-toolbox"></i>
                            </span>
                            Accesorios
                        </h3>
                        <span
                            class="text-[10px] font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded-md uppercase tracking-wider">
                            Desglose Técnico
                        </span>
                    </div>

                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 font-bold text-[11px] uppercase tracking-wider">
                                    <th class="px-6 py-3 border-b border-gray-200">Accesorio / Perfiles</th>
                                    <th class="px-6 py-3 text-center border-b border-gray-200">Medida</th>
                                    <th class="px-6 py-3 text-right border-b border-gray-200">Cantidad</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @forelse($datos as $nombre => $item)
                                    <tr class="hover:bg-blue-50/30 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-slate-700 capitalize">
                                                    {{ str_replace('_', ' ', $nombre) }}
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-medium italic">Referencia
                                                    estándar</span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-slate-100 text-slate-600 font-mono text-xs font-bold border border-slate-200">
                                                {{ $item['medida'] }} cm
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <span class="text-slate-900 font-black text-base">
                                                    {{ $item['cantidad'] }}
                                                </span>
                                                <span
                                                    class="text-[10px] text-slate-400 font-bold uppercase">unid.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <i class="fa-solid fa-folder-open text-gray-300 text-3xl"></i>
                                                <p class="text-gray-400 italic text-sm font-medium">No hay datos
                                                    calculados actualmente</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-slate-50 border-t border-gray-100 px-6 py-3">
                        <p class="text-[10px] text-slate-400 font-medium">
                            * Las medidas mostradas son aproximadas para el corte del material.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        body {
            margin: 0;
            background: #f3f4f6;
        }

        model-viewer {
            margin-top: 20px;
            width: 50%;
            height: 90vh;
            background-color: #e7e7e7;
            /* 👈 fondo del visor */
            border-radius: 12px;
            /* opcional */
        }

        #2DVIEW {
            width: 50%;
            height: 80vh;
            border: 1px solid #ccc;
        }


        /* Tablets */
        @media (max-width: 1024px) {

            model-viewer,
            #2DVIEW {
                height: 50vh;
            }
        }

        /* Móviles */
        @media (max-width: 720px) {

            model-viewer,
            #2DVIEW {
                height: 400px;
            }
        }

        /* Móviles pequeños */
        @media (max-width: 480px) {

            model-viewer,
            #2DVIEW {
                height: 300px;
            }
        }
    </style>

    @if ($vista === '3d')
        <div class="block lg:flex justify-center gap-2 items-center">
            @if ($material == '7852')
                <model-viewer class="w-full lg:w-[50%]"
                    src="{{ $color === 'gris' ? '/modelos/puerta-gris.glb' : '/modelos/puerta-negro.glb' }}"
                    camera-controls auto-rotate camera-orbit="0deg 75deg" shadow-intensity="3" exposure="3">
                </model-viewer>
            @elseif ($material = '7830')
                <model-viewer class="w-full lg:w-[50%]"
                    src="{{ $color === 'gris' ? '/modelos/puerta-gris.glb' : '/modelos/puerta-negro.glb' }}"
                    camera-controls auto-rotate camera-orbit="0deg 75deg" shadow-intensity="3" exposure="3">
                </model-viewer>
            @endif
            <div class="flex flex-col md:flex-row items-start justify-center w-1/2">
                <div class="mt-6 w-full bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

                    <div class="bg-gray-50/50 border-b border-gray-200 px-5 py-4 flex items-center justify-between">
                        <h3 class="text-slate-800 font-bold text-sm md:text-base flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600">
                                <i class="fa-solid fa-toolbox"></i>
                            </span>
                            Accesorios
                        </h3>
                        <span
                            class="text-[10px] font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded-md uppercase tracking-wider">
                            Desglose Técnico
                        </span>
                    </div>

                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 font-bold text-[11px] uppercase tracking-wider">
                                    <th class="px-6 py-3 border-b border-gray-200">Accesorio / Perfiles</th>
                                    <th class="px-6 py-3 text-center border-b border-gray-200">Medida</th>
                                    <th class="px-6 py-3 text-right border-b border-gray-200">Cantidad</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @forelse($datos as $nombre => $item)
                                    <tr class="hover:bg-blue-50/30 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-slate-700 capitalize">
                                                    {{ str_replace('_', ' ', $nombre) }}
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-medium italic">Referencia
                                                    estándar</span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-slate-100 text-slate-600 font-mono text-xs font-bold border border-slate-200">
                                                {{ $item['medida'] }} cm
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <span class="text-slate-900 font-black text-base">
                                                    {{ $item['cantidad'] }}
                                                </span>
                                                <span
                                                    class="text-[10px] text-slate-400 font-bold uppercase">unid.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <i class="fa-solid fa-folder-open text-gray-300 text-3xl"></i>
                                                <p class="text-gray-400 italic text-sm font-medium">No hay datos
                                                    calculados actualmente</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-slate-50 border-t border-gray-100 px-6 py-3">
                        <p class="text-[10px] text-slate-400 font-medium">
                            * Las medidas mostradas son aproximadas para el corte del material.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-slate-50 mt-3 p-4 md:p-8 rounded-3xl border border-slate-200 shadow-inner" x-data="{ vista: 'perfiles' }">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-6">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Centro de Corte</h2>
                <p class="text-slate-500 text-sm font-medium">Optimización de materiales</p>
            </div>

            <div class="flex bg-slate-200 p-1 rounded-2xl w-full md:w-auto shadow-inner">
                <button @click="vista = 'perfiles'"
                    :class="vista === 'perfiles' ? 'bg-white text-blue-600 shadow-md' : 'text-slate-500 hover:text-slate-700'"
                    class="flex-1 md:flex-none px-6 py-2 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa- Donald-tub"></i> Aluminio
                </button>
                <button @click="vista = 'vidrio'"
                    :class="vista === 'vidrio' ? 'bg-[#1e293b] text-blue-400 shadow-md' :
                        'text-slate-500 hover:text-slate-700'"
                    class="flex-1 md:flex-none px-6 py-2 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-gem"></i> Vidrio
                </button>
            </div>
        </div>

        <div class="min-h-[500px]">

            <div x-show="vista === 'perfiles'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-6">
                @php
                    $longitudVarilla = 595;
                    $corteExtra = 0.5;
                    $gruposAlu = [];
                    $excluir = ['bisagra', '3x3', 'tornillo', 'felpa', 'empaque', 'jalador', 'pijas', 'escuadra'];

                    foreach ($datos as $nombre => $item) {
                        $nombreLower = strtolower($nombre);
                        $esAcc = false;
                        foreach ($excluir as $ex) {
                            if (str_contains($nombreLower, $ex)) {
                                $esAcc = true;
                            }
                        }

                        if (!$esAcc && !str_contains($nombreLower, 'vidrio') && (float) $item['medida'] > 0) {
                            preg_match('/\d{4,}/', $nombre, $matches);
                            $cod = $matches[0] ?? 'Perfiles';
                            for ($i = 0; $i < $item['cantidad']; $i++) {
                                $gruposAlu[$cod][] = [
                                    'n' => preg_replace('/\d{4,}/', '', $nombre),
                                    'm' => (float) $item['medida'],
                                ];
                            }
                        }
                    }
                @endphp

                @forelse($gruposAlu as $cod => $piezas)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                @php
                                    // Buscamos el nombre del producto en el array del Excel que contenga el código
                                    $nombreProductoExcel = 'Perfil no identificado';

                                    foreach ($data as $producto) {
                                        // Si el nombre del producto en el Excel contiene el código (ej: "1451")
                                        if (str_contains($producto, $cod)) {
                                            $nombreProductoExcel = $producto;
                                            break;
                                        }
                                    }
                                @endphp

                                <div class="flex flex-col">
                                    <span class="bg-blue-600 text-white px-3 py-1 rounded-lg font-black text-sm w-fit">

                                        {{ $nombreProductoExcel }}
                                    </span>
                                </div>
                            </div>
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ count($piezas) }}
                                cortes totales</span>
                        </div>

                        <div class="p-6 gap-x-12 gap-y-8">
                            @php
                                $varillas = [];
                                $actual = ['p' => [], 'u' => 0];
                                foreach ($piezas as $p) {
                                    if ($actual['u'] + $p['m'] + $corteExtra > $longitudVarilla) {
                                        $varillas[] = $actual;
                                        $actual = ['p' => [], 'u' => 0];
                                    }
                                    $actual['p'][] = $p;
                                    $actual['u'] += $p['m'] + $corteExtra;
                                }
                                if (!empty($actual['p'])) {
                                    $varillas[] = $actual;
                                }
                            @endphp

                            @foreach ($varillas as $idx => $v)
                                <div
                                    class="group/varilla bg-slate-50/50 p-2 mb-1.5 rounded-xl border border-slate-200 hover:bg-white transition-all duration-200">

                                    <div class="flex justify-between items-center mb-1 px-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-[9px] font-black bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded">#{{ $idx + 1 }}</span>
                                            <h4
                                                class="text-[10px] font-black text-slate-700 uppercase tracking-tighter">
                                                VARILLA {{ $idx + 1 }}</h4>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <span
                                                    class="text-[9px] font-bold text-slate-400 uppercase italic">Retaso:</span>
                                                <span
                                                    class="text-[10px] font-mono font-black text-emerald-600 bg-emerald-50 px-1.5 rounded border border-emerald-100">
                                                    {{ $longitudVarilla - round($v['u'], 1) }} cm
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="relative h-7 w-full bg-slate-200 rounded-lg flex p-0.5 gap-0.5 border border-slate-300 shadow-inner overflow-hidden">

                                        @foreach ($v['p'] as $pz)
                                            @php
                                                $anchoPct = ($pz['m'] / $longitudVarilla) * 100;

                                                $colores = [
                                                    'bg-blue-500 border-blue-600',
                                                    'bg-indigo-500 border-indigo-600',
                                                    'bg-violet-500 border-violet-600',
                                                    'bg-cyan-500 border-cyan-600',
                                                    'bg-sky-500 border-sky-600',
                                                    'bg-slate-600 border-slate-700',
                                                ];
                                                $colorSeleccionado = $colores[array_rand($colores)];
                                            @endphp

                                            <div class="h-full {{ $colorSeleccionado }} border rounded flex items-center justify-center group relative cursor-help transition-all hover:brightness-110 shadow-sm"
                                                style="width: {{ $anchoPct }}%">

                                                <span class="text-[9px] font-black text-white drop-shadow-sm">
                                                    {{ round($pz['m'], 1) }}
                                                </span>

                                                <div
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-50">
                                                    <div
                                                        class="bg-slate-900 text-white text-[9px] py-1 px-3 rounded-md shadow-xl border border-slate-700 whitespace-nowrap">
                                                        <span
                                                            class="font-black text-blue-300">{{ $pz['n'] }}</span>
                                                        | {{ $pz['m'] }} cm
                                                    </div>
                                                    <div
                                                        class="w-2 h-2 bg-slate-900 rotate-45 -mt-1 mx-auto border-r border-b border-slate-700">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @php $porcentajeRestante = 100 - (($v['u'] / $longitudVarilla) * 100); @endphp
                                        <div class="h-full opacity-20 bg-desperdicio"
                                            style="width: {{ $porcentajeRestante }}%"></div>
                                    </div>
                                </div>
                            @endforeach

                            <style>
                                .bg-desperdicio {
                                    background-color: #94a3b8;
                                    background-image: repeating-linear-gradient(45deg, transparent, transparent 4px, #475569 4px, #475569 5px);
                                }
                            </style>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-20 rounded-3xl border-2 border-dashed border-slate-200 text-center">
                        <p class="text-slate-400 font-bold uppercase text-sm">No se detectaron perfiles para optimizar
                        </p>
                    </div>
                @endforelse
            </div>
            <div x-show="vista === 'vidrio'" x-transition
                class="bg-slate-50 p-6 rounded-xl border border-slate-300 shadow-2xl">

                <div
                    class="flex flex-col md:flex-row justify-between items-center mb-6 bg-white p-4 rounded-lg border border-slate-200 shadow-sm gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-400 rounded-lg shadow-sm">
                            <i class="fa-solid fa-ruler-combined text-slate-800"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-800 uppercase leading-none">Mapa de Corte
                                Industrial</h2>
                            <p class="text-[10px] text-blue-600 font-bold uppercase tracking-widest mt-1">Consolidado
                                de Retales y Área Neta</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="text-right">
                            <p class="text-[9px] font-black text-slate-400 uppercase">Formato de Plancha</p>
                            <select wire:model.live="dimensionPlancha"
                                class="bg-slate-50 border-2 border-slate-200 text-xs font-black rounded px-3 py-1 outline-none focus:border-blue-500">
                                <option value="214x330">214 x 330 cm</option>
                                <option value="213.5x330">213.5 x 330 cm</option>
                                <option value="183x244">183 x 244 cm</option>
                                <option value="150x200">150 x 200 cm</option>
                            </select>
                        </div>
                    </div>
                </div>

                @php
                    $dim = explode('x', $dimensionPlancha ?? '214x330');
                    $altoP = (float) ($dim[0] ?? 214);
                    $anchoP = (float) ($dim[1] ?? 330);

                    $piezas = [];
                    $areaCorteCm2 = 0;
                    foreach ($datos as $nom => $it) {
                        if (str_contains(strtolower($nom), 'vidrio') && !empty($it['medida'])) {
                            $m = explode('x', $it['medida']);
                            $h = (float) $m[0];
                            $w = (float) $m[1];
                            $cant = $it['cantidad'] ?? 1;
                            for ($i = 0; $i < $cant; $i++) {
                                $piezas[] = ['h' => $h, 'w' => $w];
                                $areaCorteCm2 += $h * $w;
                            }
                        }
                    }

                    $piesCuadradosNetos = $areaCorteCm2 / 929.03;

                    $curX = 0;
                    $curY = 0;
                    $anchoColumnaActual = 0;
                    $maxAnchoUsadoPct = 0;
                    $sobrantesColumnas = [];
                    $areaSobranteTotalCm2 = 0;

                    foreach ($piezas as $idx => $p) {
                        $wPct = ($p['w'] / $anchoP) * 100;
                        $hPct = ($p['h'] / $altoP) * 100;

                        if ($curY + $hPct > 100.1) {
                            $altoSob = $altoP - ($curY * $altoP) / 100;
                            $anchoSob = ($anchoColumnaActual * $anchoP) / 100;
                            $areaSobranteTotalCm2 += $altoSob * $anchoSob;

                            $sobrantesColumnas[] = [
                                'x' => $curX,
                                'w' => $anchoColumnaActual,
                                'y' => $curY,
                                'h' => 100 - $curY,
                                'val' => $altoSob,
                            ];

                            $curY = 0;
                            $curX += $anchoColumnaActual;
                            $anchoColumnaActual = 0;
                        }
                        $anchoColumnaActual = max($anchoColumnaActual, $wPct);
                        $maxAnchoUsadoPct = max($maxAnchoUsadoPct, $curX + $wPct);
                        $curY += $hPct;
                    }

                    $ultimoSobH = $altoP - ($curY * $altoP) / 100;
                    if ($ultimoSobH > 0) {
                        $areaSobranteTotalCm2 += $ultimoSobH * (($anchoColumnaActual * $anchoP) / 100);
                    }

                    $sobranteLatW = $anchoP - ($maxAnchoUsadoPct * $anchoP) / 100;
                    if ($sobranteLatW > 0) {
                        $areaSobranteTotalCm2 += $sobranteLatW * $altoP;
                    }

                    $piesCuadradosRetal = $areaSobranteTotalCm2 / 929.03;
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div id="area-mapa-corte"
                        class="lg:col-span-8 flex flex-col items-center bg-white p-12 rounded-2xl border border-slate-200 shadow-inner relative">

                        <div class="absolute left-1 top-12 bottom-12 flex flex-col items-center justify-between py-4">
                            <div class="w-[2px] h-full bg-blue-500 relative">
                                <span class="absolute -top-2 -left-[4px] text-blue-500 text-[10px]">▲</span>
                                <span class="absolute -bottom-2 -left-[4px] text-blue-500 text-[10px]">▼</span>
                            </div>
                            <span
                                class="absolute top-1/2 rotate-45 whitespace-nowrap text-[11px] font-black text-blue-700 bg-white px-2">
                                {{ $altoP }} cm
                            </span>
                        </div>

                        <div class="relative border-[3px] border-slate-900 bg-[#FFEA00] shadow-2xl overflow-hidden"
                            style="width: 530px; height: 314px;">

                            @php
                                $curX = 0;
                                $curY = 0;
                                $anchoColumnaActual = 0;
                                $maxAnchoUsadoPct = 0;
                                $maxAltoUsadoEnColumnaPct = 0;
                            @endphp

                            @foreach ($piezas as $p)
                                @php
                                    $wPct = ($p['w'] / $anchoP) * 100;
                                    $hPct = ($p['h'] / $altoP) * 100;

                                    if ($curY + $hPct > 100.1) {
                                        $curY = 0;
                                        $curX += $anchoColumnaActual;
                                        $anchoColumnaActual = 0;
                                    }
                                    $anchoColumnaActual = max($anchoColumnaActual, $wPct);
                                    $maxAnchoUsadoPct = max($maxAnchoUsadoPct, $curX + $wPct);
                                    $maxAltoUsadoEnColumnaPct = max($maxAltoUsadoEnColumnaPct, $curY + $hPct);
                                @endphp

                                <div class="absolute border border-slate-900 bg-white flex items-center justify-center overflow-hidden transition-all hover:bg-blue-50"
                                    style="left: {{ $curX }}%; top: {{ $curY }}%; width: {{ $wPct }}%; height: {{ $hPct }}%;">
                                    <div class="flex items-center gap-1 px-1">
                                        <span
                                            class="text-[9px] font-black text-slate-800">{{ round($p['w'], 1) }}</span>
                                        <span class="text-[7px] font-bold text-slate-400">x</span>
                                        <span class="text-[9px] font-black text-slate-800">{{ round($p['h'], 1) }}
                                            cm</span>
                                    </div>
                                </div>

                                @php $curY += $hPct; @endphp
                            @endforeach

                            {{-- LÓGICA DE RETALES CON DIMENSIONES TOTALES --}}
                            @php
                                // Retal lateral (Derecha)
                                $retalLateralAncho = $anchoP - ($maxAnchoUsadoPct * $anchoP) / 100;
                                $retalLateralAlto = $altoP;

                                // Retal inferior (Debajo de las piezas)
                                $retalInferiorAncho = ($maxAnchoUsadoPct * $anchoP) / 100;
                                $retalInferiorAlto = $altoP - ($maxAltoUsadoEnColumnaPct * $altoP) / 100;
                            @endphp

                            {{-- Render Retal Lateral --}}
                            @if ($retalLateralAncho > 1)
                                <div class="absolute top-0 right-0 h-full border-l-2 border-dashed border-red-600 bg-yellow-400 flex flex-col items-center justify-center"
                                    style="width: {{ 100 - $maxAnchoUsadoPct }}%;">
                                    <div
                                        class="bg-red-700 text-white px-2 py-1 rounded shadow-lg flex flex-col items-center">
                                        <span class="text-[11px] font-black whitespace-nowrap">
                                            {{ round($retalLateralAncho, 1) }} x {{ round($retalLateralAlto, 1) }} cm
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Render Retal Inferior --}}
                            @if ($retalInferiorAlto > 1 && $maxAnchoUsadoPct > 0)
                                <div class="absolute bottom-0 left-0 border-t-2 border-dashed border-red-600 bg-yellow-400 flex items-center justify-center"
                                    style="width: {{ $maxAnchoUsadoPct }}%; height: {{ 100 - $maxAltoUsadoEnColumnaPct }}%;">
                                    <div
                                        class="bg-red-700 text-white px-2 py-1 rounded shadow-lg flex flex-col items-center">
                                        <span class="text-[10px] font-black whitespace-nowrap">
                                            {{ round($retalInferiorAncho, 1) }} x {{ round($retalInferiorAlto, 1) }}
                                            cm
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="w-[330px] mt-4 flex items-center justify-between px-1">
                            <div class="h-[2px] w-full bg-blue-500 relative">
                                <span class="absolute -left-1 -top-[4.5px] text-blue-500 text-[10px]">◀</span>
                                <span class="absolute -right-1 -top-[4.5px] text-blue-500 text-[10px]">▶</span>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="bg-white px-3 text-[11px] font-black text-blue-700 uppercase">
                                        {{ $anchoP }} cm
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-4">
                        <div
                            class="bg-slate-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden group border border-slate-800">
                            <div
                                class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-gem text-5xl"></i>
                            </div>
                            <span class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">Área Útil
                                (pies)</span>
                            <div class="flex items-baseline gap-2 mt-1">
                                <h3 class="text-4xl font-black text-white">{{ number_format($piesCuadradosNetos, 2) }}
                                </h3>
                                <span class="text-lg font-bold text-slate-500 italic">ft²</span>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-3xl p-6 border-2 border-yellow-400 shadow-xl relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="fa-solid fa-recycle text-5xl text-yellow-600"></i>
                            </div>
                            <span class="text-[10px] font-bold text-yellow-600 uppercase tracking-widest">Retaso Total
                                (pies)</span>
                            <div class="flex items-baseline gap-2 mt-1">
                                <h3 class="text-4xl font-black text-slate-800">
                                    {{ number_format($piesCuadradosRetal, 2) }}</h3>
                                <span class="text-lg font-bold text-slate-400 italic">ft²</span>
                            </div>
                            <p class="text-[9px] text-slate-400 font-bold mt-2 uppercase tracking-tight leading-tight">
                                * Incluye lateral de <span
                                    class="text-slate-600">{{ round($retalLateralAncho, 1) }}cm</span> y restos de
                                corte.
                            </p>
                        </div>

                        <button onclick="window.print()"
                            class="w-full bg-slate-800 hover:bg-slate-900 text-white p-4 rounded-3xl shadow-lg transition-all duration-300 flex items-center justify-center gap-4 group active:scale-95">
                            <div class="bg-slate-700 p-2 rounded-xl border border-slate-600">
                                <i class="fa-solid fa-print text-xl text-blue-400"></i>
                            </div>
                            <div class="text-left">
                                <span class="block text-xs font-black uppercase tracking-widest text-white">Imprimir
                                    Plano</span>
                                <span class="text-[10px] font-medium opacity-60 text-slate-300">Solo el esquema
                                    técnico</span>
                            </div>
                        </button>
                    </div>

                    <style>
                        @media print {
                            body * {
                                visibility: hidden !important;
                            }

                            #area-mapa-corte,
                            #area-mapa-corte * {
                                visibility: visible !important;
                            }

                            #area-mapa-corte {
                                position: absolute !important;
                                left: 0 !important;
                                top: 0 !important;
                                width: 100% !important;
                                padding: 0 !important;
                                margin: 0 !important;
                                background: white !important;
                            }

                            * {
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                            }

                            .shadow-2xl,
                            .shadow-inner,
                            .shadow-xl {
                                box-shadow: none !important;
                            }

                            button,
                            .no-print {
                                display: none !important;
                            }
                        }
                    </style>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <dialog id="modalPuerta" class="modal">
        <div
            class="modal-box relative max-w-md bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-2xl border border-blue-100">
            <h3 class="text-2xl font-bold text-blue-700 text-center">Seleccionar otro modelo</h3>
            <p class="py-2 text-sm text-gray-500 text-center">Elige un tipo de puerta</p>
            <div class="mt-6 grid grid-cols-1 gap-4">
                <button
                    class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-blue-400 transition">
                    Puerta corrediza
                </button>
                <button
                    class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-blue-400 transition">
                    Puerta de dos hojas
                </button>
                <button
                    class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-blue-400 transition">
                    Puerta con ventiluz
                </button>
            </div>
            <p class="mt-6 text-xs text-gray-400 text-center">Diseño estático de ejemplo</p>
        </div>
    </dialog>
</div>
