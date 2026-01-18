<?php

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    public $ancho = 205;
    public $alto = 165;
    public $altoPuente = 130;
    public $numCorredizas = 1;
    public $numFijos = 2;
    public $sistemaSelet;
    public $ordenBloques = [];
    public $planoExportHtml;

    //descuentos

    public $vidrio = 0.6;
    public $pffijo = 0.3;
    public $pfcorrediza = 2;
    public $sobreluz = 2.1;
    public $sbancho = 0.3;
    public $vfijo = 1;
    public $vcorrediza = 2.5;

    public function imprimirTodo()
    {
        $datos = session('datos_lote', []);
        dd($datos);
        // Sincronizar la ventana que el usuario tiene abierta actualmente
        $this->cambiarVentana($this->ventanaActiva);

        // // Verificamos que no haya datos vacíos por error
        // $datosLimpios = array_map(function ($v) {
        //     return $v;
        // }, $this->ventanas);

        // session()->put('datos_lote', $datosLimpios);
        // session()->save();

        $this->dispatch('disparar-impresion-total');
        $this->resetearProyecto();
    }
    public $data = [];
    public function procesarPerfiles()
    {
        $rutaArchivo = public_path('datos.xlsx');

        if (!file_exists($rutaArchivo)) {
            $this->data = [];
            return;
        }

        try {
            // Leemos el Excel
            $datosExcel = Excel::toArray([], $rutaArchivo)[0];
            $catalogo = [];

            foreach ($datosExcel as $fila) {
                // Suponiendo que el nombre completo está en la primera columna
                $nombre = isset($fila[0]) ? trim($fila[0]) : null;
                if (!empty($nombre)) {
                    $catalogo[] = $nombre;
                }
            }
            $this->data = $catalogo;
        } catch (\Exception $e) {
            $this->data = [];
        }
    }

    private function safeFloat($valor, $default = 0.0)
    {
        if ($valor === '' || $valor === null || (float) $valor <= 0) {
            return (float) $default;
        }
        return is_numeric($valor) ? (float) $valor : (float) $default;
    }

    private function safeDiv($numerador, $denominador, $default = 0.0)
    {
        $num = $this->safeFloat($numerador);
        $den = $this->safeFloat($denominador);
        return $den == 0.0 ? $default : $num / $den;
    }

    private function truncar($valor, $decimales = 1)
    {
        $factor = pow(10, (int) $decimales);
        $resultado = floor($this->safeFloat($valor) * $factor) / $factor;
        return number_format($resultado, $decimales, '.', '');
    }

    public function getDivisionesInferioresProperty()
    {
        return (int) $this->safeFloat($this->numCorredizas) + (int) $this->safeFloat($this->numFijos);
    }

    public function getAnchoAjustadoProperty()
    {
        $ancho = $this->safeFloat($this->ancho, 205);
        $divisiones = (int) $this->divisionesInferiores;

        switch ($divisiones) {
            case 3:
                $ancho += 1;
                break;
            case 4:
                $ancho += 0;
                break;
            case 5:
                $ancho += 2;
                break;
            case 6:
                $ancho += 3;
                break;
        }
        return $ancho;
    }

    public function getAltoInfProperty()
    {
        return $this->safeFloat($this->altoPuente, 130);
    }

    public function getAltoSupProperty()
    {
        return (float) max(0, $this->safeFloat($this->alto, 165) - $this->safeFloat($this->altoPuente) - $this->sobreluz);
    }

    public function getBloquesProperty()
    {
        $total = (int) $this->divisionesInferiores;
        if ($total === 0) {
            return [];
        }
        if ($total === 5) {
            return ['Fijo', 'Corrediza', 'Fijo', 'Corrediza', 'Fijo'];
        }
        if ($total === 6) {
            return ['Fijo', 'Corrediza', 'Fijo', 'Corrediza', 'Fijo', 'Corrediza'];
        }

        $numFijos = (int) $this->safeFloat($this->numFijos);
        $numCorredizas = (int) $this->safeFloat($this->numCorredizas);
        $fijosIzq = (int) floor($this->safeDiv($numFijos, 2));
        $fijosDer = $numFijos - $fijosIzq;

        return [...array_fill(0, $fijosIzq, 'Fijo'), ...array_fill(0, $numCorredizas, 'Corrediza'), ...array_fill(0, $fijosDer, 'Fijo')];
    }

    public function getSobreluzPartesProperty()
    {
        $partes = [];
        $divisiones = (int) $this->divisionesInferiores;
        $altoS = $this->altoSup;
        $anchoTotal = $this->safeFloat($this->ancho);

        if ($divisiones >= 5) {
            $cantidad = 3;
            $anchoPorParte = $this->safeDiv($anchoTotal, $cantidad);
        } elseif ($divisiones >= 3) {
            $cantidad = 2;
            $anchoPorParte = $this->safeDiv($anchoTotal, $cantidad);
        } else {
            $cantidad = 1;
            $anchoPorParte = $anchoTotal;
        }

        for ($i = 1; $i <= $cantidad; $i++) {
            $partes[] = [
                'ancho' => $this->safeFloat($this->truncar($anchoPorParte, 1)) - $this->sbancho,
                'alto' => $this->truncar($altoS, 1),
                'label' => $cantidad > 1 ? "TL $i" : 'TL',
            ];
        }

        return $partes;
    }

    public function getAccesoriosProperty()
    {
        $numCorredizas = (int) $this->safeFloat($this->numCorredizas);
        return [
            'garruchas' => $numCorredizas * 2,
            'pestillos' => $numCorredizas,
        ];
    }

    public function getMedidasBloquesProperty()
    {
        $bloques = [];
        $totalHojas = (int) $this->divisionesInferiores;

        if ($totalHojas <= 0) {
            return [];
        }

        $anchoTotal = $this->safeFloat($this->anchoAjustado);
        $anchoPorParte = $this->safeDiv($anchoTotal, $totalHojas);

        foreach ($this->bloques as $tipo) {
            $ajuste = $tipo === 'Fijo' ? $this->vidrio : -$this->vidrio;
            $anchoFinal = $anchoPorParte + $ajuste;

            $bloques[] = [
                'tipo' => $tipo === 'Fijo' ? 'F' : 'C',
                'ancho' => $this->truncar($anchoFinal, 1),
                'alto' => $this->truncar($this->altoPuente - ($tipo === 'Fijo' ? $this->vfijo : $this->vcorrediza), 1),
            ];
        }

        if (!empty($this->ordenBloques)) {
            $ordenados = [];
            foreach ($this->ordenBloques as $i) {
                if (isset($bloques[$i])) {
                    $ordenados[] = $bloques[$i];
                }
            }
            return $ordenados;
        }

        return $bloques;
    }
    public function resetearProyecto()
    {
        // Reset de propiedades simples
        $this->ancho = 205;
        $this->alto = 165;
        $this->altoPuente = 130;
        $this->numCorredizas = 1;
        $this->numFijos = 2;

        $this->ordenBloques = [];
        $this->sistemaSelet = null;
        $this->planoExportHtml = null;

        // Reset de ventanas
        $this->ventanas = [
            [
                'nombre' => 'V - 1',
                'ancho' => 205,
                'alto' => 165,
                'altoPuente' => 130,
                'numCorredizas' => 1,
                'numFijos' => 2,
            ],
        ];

        $this->ventanaActiva = 0;
    }

    public function getDetalleModulosProperty()
    {
        $detalle = [];
        $anchoTotal = $this->truncar($this->ancho);

        $detalle['U 3/4'] = ['label' => '7955', 'alto' => $anchoTotal, 'cantidad' => 1];
        $detalle['T/M'] = ['label' => '5283', 'alto' => $anchoTotal, 'cantidad' => 1];
        $detalle['RIEL L'] = ['label' => '8413', 'alto' => $anchoTotal, 'cantidad' => 1];

        $bloques = $this->medidasBloques;
        $anchoFijos = [];
        $anchoCorredizas = [];

        foreach ($bloques as $b) {
            $ancho = number_format((float) $b['ancho'], 1, '.', '');
            if ($b['tipo'] === 'F') {
                $anchoFijos[$ancho] = ($anchoFijos[$ancho] ?? 0) + 1;
            } else {
                $anchoCorredizas[$ancho] = ($anchoCorredizas[$ancho] ?? 0) + 1;
            }
        }

        foreach ($anchoFijos as $ancho => $cant) {
            $detalle["U F ($ancho cm)"] = ['label' => '3003', 'alto' => $ancho, 'cantidad' => $cant];
        }
        foreach ($anchoCorredizas as $ancho => $cant) {
            $detalle["H ($ancho cm)"] = ['label' => '8220', 'alto' => $ancho, 'cantidad' => $cant];
        }

        $pfFijos = 0;
        foreach ($bloques as $i => $b) {
            if ($b['tipo'] === 'F') {
                $izq = $bloques[$i - 1]['tipo'] ?? null;
                $der = $bloques[$i + 1]['tipo'] ?? null;
                $pfFijos += $izq === 'C' && $der === 'C' ? 2 : 1;
            }
        }

        $detalle['PF Fijo'] = ['label' => '8115', 'alto' => $this->truncar($this->altoPuente - $this->pffijo), 'cantidad' => $pfFijos];
        $detalle['PF Corrediza'] = ['label' => '8115', 'alto' => $this->truncar($this->altoPuente - $this->pfcorrediza), 'cantidad' => (int) $this->numCorredizas * 2];

        return $detalle;
    }

    public function mount()
    {
        $this->procesarPerfiles();
        // Inicializar con una ventana
        $this->ventanas[] = [
            'nombre' => 'V - 1',
            'ancho' => 205,
            'alto' => 165,
            'altoPuente' => 130,
            'numCorredizas' => 1,
            'numFijos' => 2,
        ];
    }

    public $ventanas = [];
    public $ventanaActiva = 0;

    public function agregarVentana()
    {
        $this->cambiarVentana($this->ventanaActiva); // Sincroniza actual
        $nuevoId = count($this->ventanas) + 1;
        $this->ventanas[] = [
            'nombre' => "V - $nuevoId",
            'ancho' => 205,
            'alto' => 165,
            'altoPuente' => 130,
            'numCorredizas' => 1,
            'numFijos' => 2
        ];
        
        $this->cambiarVentana(count($this->ventanas) - 1);

        // Verificamos que no haya datos vacíos por error
        $datosLimpios = array_map(function ($v) {
            return $v;
        }, $this->ventanas);

        session()->put('datos_lote', $datosLimpios);
        session()->save();
        // $datos = session('datos_lote', []);
        // dd($datos);
    }

    public function cambiarVentana($index)
    {
        $this->ventanas[$this->ventanaActiva] = [
            'nombre' => $this->ventanas[$this->ventanaActiva]['nombre'],
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'altoPuente' => $this->altoPuente,
            'numCorredizas' => $this->numCorredizas,
            'numFijos' => $this->numFijos,

            // Forzamos la creación de un arreglo independiente para cada parte
            'altoInf' => $this->getAltoInfProperty(),
            'altoSup' => $this->getAltoSupProperty(),
            'bloques' => json_decode(json_encode($this->getMedidasBloquesProperty()), true),
            'sobreluz' => json_decode(json_encode($this->getSobreluzPartesProperty()), true),
            'detalle' => json_decode(json_encode($this->getDetalleModulosProperty()), true),
            'catalogo' => $this->data, // Guardamos el catálogo vigente en esa ventana
        ];

        // 2. Cambiamos al nuevo índice
        $this->ventanaActiva = $index;

        // 3. Cargamos los valores de la nueva ventana a los inputs
        $v = $this->ventanas[$index];
        $this->ancho = $v['ancho'];
        $this->alto = $v['alto'];
        $this->altoPuente = $v['altoPuente'];
        $this->numCorredizas = $v['numCorredizas'];
        $this->numFijos = $v['numFijos'];
    }

};
?>

<div wire:cloak class="relative p-2 md:p-6 max-w-6xl mx-auto mb-[100px]">
    <div class="flex justify-between items-center gap-1 mb-6 px-2 overflow-x-auto border-b border-gray-200">
        <div class="flex gap-">
            @foreach ($ventanas as $index => $v)
                <button wire:click="cambiarVentana({{ $index }})"
                    class="px-6 py-2 text-xs font-black uppercase tracking-tighter transition-all rounded-t-xl border-t border-l border-r {{ $ventanaActiva == $index ? 'bg-white border-gray-200 text-blue-600 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]' : 'bg-gray-100 border-transparent text-gray-400 hover:bg-gray-200' }}">
                    <i class="fa-solid fa-window-maximize mr-2"></i> {{ $v['nombre'] }}
                </button>
            @endforeach
            <button wire:click="agregarVentana"
                class="ml-2 px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors flex items-center gap-2 text-xs font-bold">
                <i class="fa-solid fa-plus-circle"></i> Nuevo
            </button>
        </div>
        <div class="flex gap-2">
            <button
                class="px-6 py-2 bg-orange-600 text-white text-xs font-black rounded-xl hover:bg-orange-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-scissors"></i> Optimizar
            </button>

            <button wire:click="imprimirTodo"
                class="px-6 py-2 bg-emerald-600 text-white text-xs font-black rounded-xl hover:bg-emerald-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-pdf"></i> IMPRIMIR PROYECTO
            </button>
        </div>

    </div>

    <div class="mb-4 px-4">
        <span class="text-[10px] bg-green-400 text-white px-3 py-1 rounded-full font-bold">
            EDITANDO: {{ $ventanas[$ventanaActiva]['nombre'] }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-6 p-2 mb-6 md:grid-cols-3 lg:grid-cols-5">
        <div class="relative group">
            <label class="block mb-2 ml-1 text-xs font-bold tracking-wider text-gray-500 uppercase">Ancho <span
                    class="text-blue-500">(cm)</span></label>
            <input type="number" wire:model.
            ="ancho" oninput="if(this.value < 0) this.value = 1;"
                class="w-full px-4 py-3 font-bold text-gray-700 border-2 border-gray-200 rounded-2xl focus:border-blue-500 outline-none">
        </div>
        <div class="relative group">
            <label class="block mb-2 ml-1 text-xs font-bold tracking-wider text-gray-500 uppercase">Alto <span
                    class="text-blue-500">(cm)</span></label>
            <input type="number" wire:model.blur="alto" oninput="if(this.value < 0) this.value = 1;"
                class="w-full px-4 py-3 font-bold text-gray-700 border-2 border-gray-200 rounded-2xl focus:border-blue-500 outline-none">
        </div>
        <div class="relative group">
            <label class="block mb-2 ml-1 text-xs font-bold tracking-wider text-gray-500 uppercase">Altura Puente <span
                    class="text-blue-500">(cm)</span></label>
            <input type="number" wire:model.blur="altoPuente" oninput="if(this.value < 0) this.value = 1;"
                class="w-full px-4 py-3 font-bold text-gray-700 border-2 border-gray-200 rounded-2xl focus:border-amber-500 outline-none">
        </div>
        <div class="relative group">
            <label class="block mb-2 ml-1 text-xs font-bold tracking-wider text-gray-500 uppercase">Corredizas</label>
            <input type="number" wire:model.blur="numCorredizas" min="1"
                oninput="if(this.value < 0) this.value = 1;"
                class="w-full px-4 py-3 font-bold text-gray-700 border-2 border-gray-200 rounded-2xl focus:border-blue-500 outline-none">
        </div>
        <div class="relative group">
            <label class="block mb-2 ml-1 text-xs font-bold tracking-wider text-gray-500 uppercase">Fijos</label>
            <input type="number" wire:model.blur="numFijos" min="1" oninput="if(this.value < 0) this.value = 1;"
                class="w-full px-4 py-3 font-bold text-gray-700 border-2 border-gray-200 rounded-2xl focus:border-blue-500 outline-none">
        </div>
    </div>

    <div
        class="flex flex-col items-center justify-center p-4 border border-gray-200 shadow-inner bg-gray-50 md:p-6 rounded-3xl">

        <iframe id="iframeLote" src="{{ route('plano.imprimir') }}" style="display:none;"></iframe>

        <script>
            window.addEventListener('disparar-impresion-total', event => {
                const iframe = document.getElementById('iframeLote');
                iframe.src = "{{ route('plano.imprimir') }}";

                iframe.onload = function() {
                    setTimeout(() => {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    }, 600);
                };
            });
        </script>
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            /* 2. Forzar que los colores se vean al imprimir */
            @media print {
                body {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }

            /* 3. Estilos adicionales para que no salgan bordes raros */
            body {
                background: white !important;
            }
        </style>
        <div class="w-full text-center" id="area-mapa-corte">
            <div class="mb-10">
                <span
                    class="px-6 py-2 bg-blue-600 text-white text-xs font-black rounded-full uppercase tracking-widest shadow-lg">
                    {{ $sistemaSelet ?? 'PLANO TÉCNICO: SISTEMA NOVA' }}
                </span>
            </div>

            <div class="relative mx-auto mt-4 mb-12" style="width: 90%; max-width: 700px; height: 350px;">

                <div class="absolute top-0 flex items-center justify-center h-full -left-14">
                    <div class="w-[2px] h-full relative bg-blue-400">
                        <div class="absolute -top-1 -left-[4px] text-[10px] text-blue-400">▲</div>
                        <div class="absolute -bottom-1 -left-[4px] text-[10px] text-blue-400">▼</div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div
                                class="-rotate-45 bg-gray-50 px-2 text-[14px] font-bold text-gray-600 border border-slate-200 rounded whitespace-nowrap">
                                {{ $alto, 165 }} cm
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-0 flex items-center justify-center -right-14"
                    style="height: {{ ($this->altoInf / $alto) * 100 }}%;">
                    <div class="w-[2px] h-full relative bg-amber-500">
                        <div class="absolute -top-1 -left-[4px] text-[10px] text-amber-500">▲</div>
                        <div class="absolute -bottom-1 -left-[4px] text-[10px] text-amber-500">▼</div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div
                                class="-rotate-45 bg-gray-50 px-2 text-[14px] font-bold text-gray-600 border border-slate-200 rounded whitespace-nowrap">
                                {{ $this->altoInf, 130 }} cm
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-12 left-0 w-full flex justify-center h-4">
                    <div class="w-full h-[2px] relative bg-slate-400">
                        <div class="absolute -left-1 -top-[5px] text-[10px] text-slate-400">◀</div>
                        <div class="absolute -right-1 -top-[5px] text-[10px] text-slate-400">▶</div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div
                                class="bg-gray-50 px-3 text-[14px] font-bold text-gray-600 border border-slate-200 rounded">
                                Ancho: {{ $ancho, 205 }} cm
                            </div>
                        </div>
                    </div>
                </div>

                <div id="plano-2d"
                    class="w-full h-full border-[8px] border-gray-900 bg-gray-800 relative flex flex-col shadow-inner overflow-hidden">
                    @if ($this->altoSup > 0)
                        <div class="w-full flex border-b-[6px] border-gray-900"
                            style="height: {{ ($this->altoSup / $alto) * 100 }}%;">
                            @foreach ($this->sobreluzPartes as $parte)
                                <div
                                    class="flex-1 border-r-[4px] border-gray-900 bg-sky-100 relative flex flex-col items-center justify-center">
                                    <span class="text-[9px] font-black text-blue-800">{{ $parte['label'] }}</span>
                                    <span class="text-[10px] font-mono font-black text-blue-900">{{ $parte['ancho'] }}
                                        x
                                        {{ $parte['alto'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div id="bloques" class="flex w-full grow"
                        style="height: {{ ($this->altoInf / $alto) * 100 }}%;">
                        @foreach ($this->medidasBloques as $i => $mod)
                            <div
                                class="flex-1 border-r-[6px] border-gray-900 relative flex flex-col items-center justify-center {{ $mod['tipo'] === 'C' ? 'bg-sky-200' : 'bg-sky-50' }}">
                                <div
                                    class="absolute top-3 left-3 px-2 py-0.5 text-[10px] font-black {{ $mod['tipo'] === 'C' ? 'bg-yellow-400 text-yellow-900' : 'bg-green-600 text-white' }}">
                                    {{ $mod['tipo'] }}{{ $i + 1 }}
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] font-black text-blue-800 uppercase">Vidrio</p>
                                    <span class="text-[11px] font-mono font-black text-blue-950">{{ $mod['ancho'] }} x
                                        {{ $mod['alto'] }}</span>
                                </div>

                                @if ($mod['tipo'] === 'C')
                                    <div
                                        class="absolute bottom-0 left-0 w-full h-[4%] bg-black border-t border-gray-900/50 flex items-center justify-center">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="w-full  mt-10 p-6 bg-white rounded-2xl border border-dashed border-slate-300">
                <h4
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group"></i> Mapeo de Componentes
                </h4>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="w-8 h-8 bg-gray-900 rounded shadow-lg flex items-center justify-center">
                            <div class="w-4 h-4 border border-white/20"></div>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-800 leading-none">Perfiles</p>
                            <p class="text-[9px] text-gray-400">Aluminio Negro</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="w-8 h-8 bg-green-600 rounded shadow-lg flex items-center justify-center">
                            <i class="fa-solid fa-lock text-white text-[10px]"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-800 leading-none">Fijos</p>
                            <p class="text-[9px] text-gray-400">Estructura Verde</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="w-8 h-8 bg-sky-300 rounded shadow-lg flex items-center justify-center">
                            <div class="w-4 h-[1px] bg-white/50 -rotate-45"></div>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-800 leading-none">Vidrios</p>
                            <p class="text-[9px] text-gray-400">Cristal Celeste</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="w-8 h-8 bg-yellow-400 rounded shadow-lg flex items-center justify-center">
                            <i class="fa-solid fa-arrows-left-right text-yellow-900 text-[10px]"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-800 leading-none">Corredizas</p>
                            <p class="text-[9px] text-gray-400">Móviles Amarillo</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div
        class="p-6 mt-10 transition-all duration-300 border shadow-2xl bg-slate-50/50 rounded-3xl border-slate-200/60 backdrop-blur-sm">

        <div class="flex items-center gap-3 mb-8">
            <div
                class="flex items-center justify-center w-10 h-10 text-white shadow-lg bg-gradient-to-br bg-blue-600 to-indigo-700 rounded-xl">
                <i class="fa-solid fa-list-check text-sm"></i>
            </div>
            <h2 class="text-2xl font-black tracking-tight text-slate-800">Resumen detallado</h2>
        </div>

        <div class="grid grid-cols-1 gap-6 mb-10 sm:grid-cols-2 lg:grid-cols-4">

            <div
                class="relative p-5 overflow-hidden transition-transform bg-white border border-blue-100 shadow-sm group hover:-translate-y-1 rounded-2xl">
                <div
                    class="absolute top-0 right-0 w-16 h-16 -mr-6 -mt-6 transition-transform group-hover:scale-110 opacity-10 bg-blue-600 rounded-full">
                </div>
                <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-1">Ancho Ajustado</p>
                <p class="text-3xl font-black text-slate-800">{{ $this->anchoAjustado }} <span
                        class="text-sm font-medium text-slate-400">cm</span></p>
            </div>

            <div
                class="relative p-5 overflow-hidden transition-transform bg-white border border-amber-100 shadow-sm group hover:-translate-y-1 rounded-2xl">
                <div
                    class="absolute top-0 right-0 w-16 h-16 -mr-6 -mt-6 transition-transform group-hover:scale-110 opacity-10 bg-amber-500 rounded-full">
                </div>
                <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-1">Alto Puente</p>
                <p class="text-3xl font-black text-slate-800">{{ $this->altoInf }} <span
                        class="text-sm font-medium text-slate-400">cm</span></p>
            </div>

            <div
                class="relative p-5 overflow-hidden transition-transform bg-white border border-indigo-100 shadow-sm group hover:-translate-y-1 rounded-2xl">
                <div
                    class="absolute top-0 right-0 w-16 h-16 -mr-6 -mt-6 transition-transform group-hover:scale-110 opacity-10 bg-indigo-600 rounded-full">
                </div>
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-1">Hojas Totales</p>
                <p class="text-3xl font-black text-slate-800">{{ $this->divisionesInferiores }} <span
                        class="text-sm font-medium text-slate-400">und</span></p>
            </div>

            <div
                class="relative p-5 overflow-hidden transition-transform bg-white border border-emerald-100 shadow-sm group hover:-translate-y-1 rounded-2xl">
                <div
                    class="absolute top-0 right-0 w-16 h-16 -mr-6 -mt-6 transition-transform group-hover:scale-110 opacity-10 bg-emerald-500 rounded-full">
                </div>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-1">Accesorios (G)</p>
                <p class="text-3xl font-black text-slate-800">{{ $this->accesorios['garruchas'] }} <span
                        class="text-sm font-medium text-slate-400">und</span></p>
            </div>
        </div>

        <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest">Detalle
                            del Perfil / Accesorio</th>
                        <th
                            class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest text-center">
                            Medida</th>
                        <th
                            class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest text-center">
                            Cant.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($this->detalleModulos as $item)
                        @php
                            $codigoBuscado = $item['label'];
                            $nombreProductoExcel = 'Perfil no identificado';
                            foreach ($this->data as $nombreCompleto) {
                                if (str_contains($nombreCompleto, $codigoBuscado)) {
                                    $nombreProductoExcel = $nombreCompleto;
                                    break;
                                }
                            }
                        @endphp
                        <tr class="transition-colors hover:bg-blue-50/30 group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-slate-700 group-hover:text-blue-700 transition-colors">{{ $nombreProductoExcel }}</span>
                                    <span class="text-[10px] font-mono font-bold text-slate-400">COD:
                                        {{ $codigoBuscado }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center px-3 py-1 text-sm font-black text-blue-700 rounded-full bg-blue-50">
                                    {{ $item['alto'] }} cm
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ $item['cantidad'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
