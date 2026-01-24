<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

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