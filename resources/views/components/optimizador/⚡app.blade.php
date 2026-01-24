<?php

use Livewire\Component;
use Illuminate\Support\Collection;

new class extends Component {
    public array $ventanas = [];

    public float $longitudVarilla = 595.0;
    public float $grosorCorte = 0.5;

    public float $anchoPlancha = 330.0;
    public float $altoPlancha = 214.0;

    public function mount()
    {
        $this->ventanas = session('datos_lote', []);
    }

    /* =========================
     |  MATERIALES
     =========================*/
    public function getMaterialesProperty(): Collection
    {
        $perfiles = [];
        $vidrios = [];

        foreach ($this->ventanas as $v) {
            $nombreVentana = $v['nombre'] ?? 'S/N';

            /* ===== VIDRIOS ===== */
            foreach (['bloques', 'sobreluz'] as $grupo) {
                if (!empty($v[$grupo]) && is_array($v[$grupo])) {
                    foreach ($v[$grupo] as $g) {
                        $ancho = (float) ($g['ancho'] ?? 0);
                        $alto = (float) ($g['alto'] ?? 0);

                        if ($ancho > 0 && $alto > 0) {
                            $vidrios[] = [
                                'id' => uniqid('vid_', true),
                                'ventana' => $nombreVentana,
                                'ancho' => $ancho,
                                'alto' => $alto,
                                'area' => $ancho * $alto,
                            ];
                        }
                    }
                }
            }

            /* ===== PERFILES ===== */
            $catalogo = $v['catalogo'] ?? [];
            $excluir = ['garrucha', 'pestillo', 'tornillo', 'felpa', 'empaque', 'bisagra', 'vidrio'];

            foreach ($v['detalle'] ?? [] as $idDetalle => $d) {
                if (collect($excluir)->contains(fn($ex) => str_contains(strtolower($idDetalle), $ex))) {
                    continue;
                }

                $codigo = $d['label'] ?? null;
                $medida = (float) ($d['alto'] ?? 0);
                $cantidad = (int) ($d['cantidad'] ?? 1);

                if ($codigo && $medida > 0) {
                    $descripcion = collect($catalogo)->first(fn($c) => str_contains($c, (string) $codigo)) ?? "$codigo - $idDetalle";

                    for ($i = 0; $i < $cantidad; $i++) {
                        $perfiles[$descripcion][] = [
                            'medida' => $medida,
                            'ventana' => $nombreVentana,
                        ];
                    }
                }
            }
        }

        return collect([
            'perfiles' => $perfiles,
            'vidrios' => collect($vidrios),
        ]);
    }

    /* =========================
     |  OPTIMIZACIÓN ALUMINIO
     =========================*/
    public function getOptimizacionProperty(): array
    {
        $resultado = [];

        foreach ($this->materiales['perfiles'] as $perfil => $piezas) {
            usort($piezas, fn($a, $b) => $b['medida'] <=> $a['medida']);

            $varillas = [];

            foreach ($piezas as $p) {
                $ubicado = false;

                foreach ($varillas as &$v) {
                    if ($v['usado'] + $p['medida'] + $this->grosorCorte <= $this->longitudVarilla) {
                        $v['piezas'][] = $p;
                        $v['usado'] += $p['medida'] + $this->grosorCorte;
                        $ubicado = true;
                        break;
                    }
                }

                if (!$ubicado) {
                    $varillas[] = [
                        'usado' => $p['medida'] + $this->grosorCorte,
                        'piezas' => [$p],
                    ];
                }
            }

            $resultado[$perfil] = $varillas;
        }

        return $resultado;
    }

    /* =========================
     |  OPTIMIZACIÓN VIDRIO (FIX)
     =========================*/
    public function getPlanchasVidrioProperty(): array
    {
        $vidrios = $this->materiales['vidrios']->sortByDesc('alto')->values()->all();

        $planchas = [];

        foreach ($vidrios as $v) {
            $ubicado = false;

            foreach ($planchas as &$plancha) {
                $y_offset = 0;

                foreach ($plancha['estantes'] as &$estante) {
                    if ($v['alto'] <= $estante['alto'] && $estante['ancho_usado'] + $v['ancho'] <= $this->anchoPlancha) {
                        $pieza = $v;
                        $pieza['x'] = $estante['ancho_usado'];
                        $pieza['y'] = $y_offset;

                        $estante['piezas'][] = $pieza;
                        $estante['ancho_usado'] += $pieza['ancho'];
                        $plancha['area_usada'] += $pieza['area'];

                        $ubicado = true;
                        break;
                    }

                    $y_offset += $estante['alto'];
                }

                if (!$ubicado && $y_offset + $v['alto'] <= $this->altoPlancha) {
                    $pieza = $v;
                    $pieza['x'] = 0;
                    $pieza['y'] = $y_offset;

                    $plancha['estantes'][] = [
                        'alto' => $pieza['alto'],
                        'ancho_usado' => $pieza['ancho'],
                        'piezas' => [$pieza],
                    ];

                    $plancha['area_usada'] += $pieza['area'];
                    $ubicado = true;
                }

                if ($ubicado) {
                    break;
                }
            }

            if (!$ubicado) {
                $pieza = $v;
                $pieza['x'] = 0;
                $pieza['y'] = 0;

                $planchas[] = [
                    'area_usada' => $pieza['area'],
                    'estantes' => [
                        [
                            'alto' => $pieza['alto'],
                            'ancho_usado' => $pieza['ancho'],
                            'piezas' => [$pieza],
                        ],
                    ],
                ];
            }
        }

        return $planchas;
    }
};
?>

<div class="bg-slate-50 min-h-screen p-4 md:p-8 font-sans text-slate-900" x-data="{ vista: 'vidrio' }">
    <div class="max-w-6xl mx-auto">
        <header class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6 border-b border-slate-200 pb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-12 h-1 bg-blue-600 rounded-full"></span>
                    <span class="text-blue-600 font-bold tracking-[0.2em] text-xs uppercase">Sistema de Corte v3</span>
                </div>
                <h1 class="text-5xl font-black tracking-tighter italic text-slate-900">Optimizador<span
                        class="text-blue-600">.</span></h1>
            </div>

            <nav class="flex bg-slate-200/50 p-1.5 rounded-2xl backdrop-blur-sm border border-slate-200 shadow-inner">
                <button @click="vista='aluminio'"
                    :class="vista === 'aluminio' ? 'bg-white text-blue-600 shadow-md' : 'text-slate-500'"
                    class="px-8 py-3 rounded-xl text-xs font-black uppercase transition-all duration-300">Aluminio</button>
                <button @click="vista='vidrio'"
                    :class="vista === 'vidrio' ? 'bg-slate-800 text-white shadow-md' : 'text-slate-500'"
                    class="px-8 py-3 rounded-xl text-xs font-black uppercase transition-all duration-300">Vidrio</button>
            </nav>
        </header>

        <div x-show="vista === 'aluminio'" x-transition class="space-y-4">
            @forelse($this->optimizacion as $perfil => $varillas)
                <div class="bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden">
                    <div class="bg-slate-900 px-4 py-2 flex justify-between items-center">
                        <h3 class="text-white font-bold text-sm tracking-wide uppercase">{{ $perfil }}</h3>
                        <span
                            class="bg-blue-500/20 text-blue-400 px-4 py-1 rounded-full text-[10px] font-black border border-blue-500/30 uppercase">{{ count($varillas) }}
                            Varillas</span>
                    </div>
                    <div class="p-4 space-y-6">
                        @foreach ($varillas as $idx => $v)
                            <div>
                                <div
                                    class="flex justify-between text-[10px] mb-1 px-1 font-black text-slate-400 uppercase italic">
                                    <span>Barra #{{ $idx + 1 }}</span>
                                    <span class="text-emerald-600">Sobrante:
                                        {{ number_format($this->longitudVarilla - $v['usado'], 1) }} cm</span>
                                </div>
                                <div
                                    class="h-8 w-full bg-slate-100 rounded-lg flex p-0 gap-[2px] relative border border-slate-300 shadow-inner">
                                    @php $acumulado = 0; @endphp
                                    @foreach ($v['piezas'] as $p)
                                        @php $acumulado += $p['medida']; @endphp
                                        <div class="h-full bg-blue-500 rounded-sm flex flex-col items-center justify-center text-white relative shadow-sm"
                                            style="width: {{ ($p['medida'] / $this->longitudVarilla) * 100 }}%">
                                            <span class="text-[10px] font-black leading-none">{{ $p['medida'] }}</span>
                                            <span
                                                class="text-[7px] font-bold opacity-70 truncate w-full text-center px-1 uppercase">{{ $p['ventana'] }}</span>
                                        </div>
                                    @endforeach
                                    <div class="flex-1 h-full bg-slate-200/50 flex items-center justify-center"><span
                                            class="text-[8px] font-bold text-slate-400 uppercase">Libre</span></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center py-20 text-slate-400 font-bold italic uppercase">No hay perfiles para mostrar</p>
            @endforelse
        </div>

        <div x-show="vista === 'vidrio'" x-transition x-data="{ vidrioSeleccionado: null }" class="space-y-12  ">
            @php $planchas = $this->planchasVidrio; @endphp

            @foreach ($planchas as $index => $plancha)
                <div class="bg-white p-6 md:p-10 rounded-[2.5rem] border border-slate-200 shadow-xl font-mono ">

                    {{-- HEADER --}}
                    <div
                        class="mb-6 border-b-2 border-blue-500 pb-4 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <span class="bg-slate-900 text-white px-4 py-1 text-2xl font-black rounded-lg">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <h2 class="text-2xl font-black text-blue-600 uppercase italic leading-none">
                                    Hoja de Corte
                                </h2>
                                <p class="text-teal-600 text-[10px] font-bold uppercase tracking-widest mt-1">
                                    Plancha Madre: {{ $this->anchoPlancha * 10 }} x {{ $this->altoPlancha * 10 }} mm
                                </p>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-100 px-4 py-2 rounded-xl">
                            <p class="text-[9px] font-black text-blue-400 uppercase leading-none">Aprovechamiento</p>
                            <p class="text-lg font-black text-blue-600 leading-none mt-1">
                                {{ number_format(($plancha['area_usada'] / ($this->anchoPlancha * $this->altoPlancha)) * 100, 1) }}%
                            </p>
                        </div>
                    </div>
                    <div class="relative" class="w-[70%] h-[80%] bg-red-500">
                        <!-- FLECHA ANCHO (SUPERIOR) -->
                        <div class="absolute -bottom-5 left-0 w-full flex items-center justify-center">
                            <div class="flex items-center gap-2 w-full px-15">
                                <span class="text-sm font-black left-14 absolute text-slate-700">|</span>
                                <div class="flex-1 border-t-2 border-slate-700 relative">
                                    <span
                                        class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-sm font-black text-slate-800 bg-white px-2">
                                        {{ $this->anchoPlancha }} cm
                                    </span>
                                </div>
                                <span class="text-sm font-black right-14 absolute text-slate-700">|</span>
                            </div>
                        </div>

                        <!-- FLECHA ALTO (IZQUIERDA) -->
                        <div class="absolute top-0 left-10  h-full flex items-center">
                            <div class="flex flex-col items-center justify-between h-full">
                                <span class="text-sm absolute -top-2.5 rotate-45 font-black text-slate-700">|</span>
                                <div class="flex-2 border-l-2 border-slate-700 relative">
                                    <span
                                        class="absolute -right-4 top-1/2 text-sm font-black text-slate-800 w-20 px-2 -rotate-45">
                                        {{ $this->altoPlancha . ' ' . 'cm' }}
                                    </span>
                                </div>
                                <span class="text-sm rotate-45 absolute -bottom-2.5  font-black text-slate-700">|</span>
                            </div>
                        </div>

                        {{-- PLANCHA --}}
                        <div class="relative bg-[#FCF970] rounded-lg shadow-inner mx-auto overflow-hidden border-[2px] border-slate-800"
                            style="aspect-ratio: {{ $this->anchoPlancha }}/{{ $this->altoPlancha }};
                       width: 100%; max-width: 950px;">
                            @foreach ($plancha['estantes'] as $estante)
                                @foreach ($estante['piezas'] as $v)
                                    <div @click="
                                            vidrioSeleccionado === '{{ $v['id'] }}'
                                                ? vidrioSeleccionado = null
                                                : vidrioSeleccionado = '{{ $v['id'] }}'"
                                        :class="vidrioSeleccionado === '{{ $v['id'] }}'
                                            ?
                                            'border-blue-700 bg-blue-100 ring-2 ring-blue-500 z-20' :
                                            'border-slate-800 bg-white'"
                                        class="absolute border transition-all duration-200 cursor-pointer select-none"
                                        style="
                                            left: {{ ($v['x'] / $this->anchoPlancha) * 100 }}%;
                                            top: {{ ($v['y'] / $this->altoPlancha) * 100 }}%;
                                            width: {{ ($v['ancho'] / $this->anchoPlancha) * 100 }}%;
                                            height: {{ ($v['alto'] / $this->altoPlancha) * 100 }}%;
                                        ">

                                        <!-- ANCHO SUPERIOR -->
                                        <div
                                            class="absolute top-0 left-0 w-full text-center text-[14px] font-black text-slate-900">
                                            {{ $v['ancho'] }}
                                        </div>

                                        <!-- ALTO IZQUIERDO ROTADO -->
                                        <div class="absolute top-0 left-0 h-full flex items-center">
                                            <span
                                                class="text-[14px] font-black text-slate-900 transform rotate-45 origin-left ml-3 whitespace-nowrap">
                                                {{ $v['alto'] }}
                                            </span>
                                        </div>

                                        <!-- MEDIDAS CENTRALES (COMO EN LA IMAGEN) -->
                                        <div
                                            class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                            {{-- <p class="text-[12px] font-black text-slate-900 leading-none">
                                            {{ $v['ancho'] }} × {{ $v['alto'] }}
                                        </p> --}}
                                            <p
                                                class="text-[20px] font-bold text-blue-700 uppercase mt-1 truncate max-w-full px-1">
                                                {{ $v['ventana'] }}
                                            </p>
                                        </div>

                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    {{-- LISTADO --}}
                    <div class="mt-16 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                        @foreach ($plancha['estantes'] as $estante)
                            @foreach ($estante['piezas'] as $v)
                                <div @click="
                                vidrioSeleccionado === '{{ $v['id'] }}'
                                    ? vidrioSeleccionado = null
                                    : vidrioSeleccionado = '{{ $v['id'] }}'
                            "
                                    :class="vidrioSeleccionado === '{{ $v['id'] }}'
                                        ?
                                        'bg-blue-100 border-blue-600 ring-2 ring-blue-500' :
                                        'bg-slate-50 border-slate-200'"
                                    class="cursor-pointer border p-2 rounded text-center transition-all">
                                    <p class="text-[8px] font-black text-slate-400 uppercase leading-none mb-1">
                                        {{ $v['ventana'] }}
                                    </p>
                                    <p class="text-xs font-bold text-slate-800">
                                        {{ $v['ancho'] }} × {{ $v['alto'] }}
                                    </p>
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</div>
