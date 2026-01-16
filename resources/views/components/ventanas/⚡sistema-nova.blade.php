<?php

use Livewire\Component;
use Spatie\Browsershot\Browsershot;

new class extends Component {
    public $ancho = 205;
    public $alto = 165;
    public $altoPuente = 130;
    public $numCorredizas = 1;
    public $numFijos = 2;
    public $sistemaSelet;
    public $ordenBloques = [];
    public $planoExportHtml;

    public function descargarPdf()
    {
        $fecha = now()->format('d/m/Y H:i:s');

        $data = [
            'fecha' => $fecha,
            'tipo' => $this->sistemaSelet,
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'puente' => $this->altoPuente,
            'corredizos' => $this->numCorredizas,
            'fijos' => $this->numFijos,
            'bloques' => $this->medidasBloques,
            'sobreluz' => $this->sobreluzPartes,
            'altoInf' => $this->altoInf,
            'altoSup' => $this->altoSup,
            'detalle' => $this->detalleModulos,
        ];

        $html = view('planos2d', $data)->render();
        $ruta = storage_path('app/public/plano.pdf');

        Browsershot::html($html)->format('A4')->margins(10, 10, 10, 10)->showBackground()->save($ruta);

        return response()->download($ruta)->deleteFileAfterSend();
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
        return (float) max(0, $this->safeFloat($this->alto, 165) - $this->safeFloat($this->altoPuente) - 2.1);
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
                'ancho' => $this->safeFloat($this->truncar($anchoPorParte, 1)) - 0.3,
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
            $ajuste = $tipo === 'Fijo' ? 0.6 : -0.6;
            $anchoFinal = $anchoPorParte + $ajuste;

            $bloques[] = [
                'tipo' => $tipo === 'Fijo' ? 'F' : 'C',
                'ancho' => $this->truncar($anchoFinal, 1),
                'alto' => $this->truncar($this->altoPuente - ($tipo === 'Fijo' ? 1 : 2.5)),
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

        $detalle['PF Fijo'] = ['label' => '8115', 'alto' => $this->truncar($this->altoPuente - 0.3), 'cantidad' => $pfFijos];
        $detalle['PF Corrediza'] = ['label' => '8115', 'alto' => $this->truncar($this->altoPuente - 2), 'cantidad' => (int) $this->numCorredizas * 2];

        return $detalle;
    }
};
?>

<div wire:cloak class="relative p-2 md:p-6 max-w-6xl mx-auto mb-[100px]">
    <div class="grid grid-cols-2 gap-6 p-2 mb-6 md:grid-cols-3 lg:grid-cols-5">
        <div class="relative group">
            <label class="block mb-2 ml-1 text-xs font-bold tracking-wider text-gray-500 uppercase">Ancho <span
                    class="text-blue-500">(cm)</span></label>
            <input type="number" wire:model.blur="ancho" oninput="if(this.value < 0) this.value = 1;"
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
        <div class="flex items-center justify-end w-full gap-3 my-4">
            <button wire:click="descargarPdf"
                class="group relative h-[45px] w-[45px] flex items-center justify-center bg-white border border-slate-300 rounded-2xl shadow-sm hover:border-blue-500 transition-all">
                <i class="text-xl fa-solid fa-download text-slate-600 group-hover:text-blue-600"></i>
            </button>
        </div>

        <div class="w-full text-center">
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
                                    <span class="text-[10px] font-mono font-black text-blue-900">{{ $parte['ancho'] }} x
                                        {{ $parte['alto'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div id="bloques" class="flex w-full grow" style="height: {{ ($this->altoInf / $alto) * 100 }}%;">
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

    <div class="p-6 mt-8 border border-gray-100 shadow-sm bg-slate-50 rounded-xl">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Resumen</h2>
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="p-4 bg-white border rounded-lg shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase">Ancho Ajustado</p>
                <p class="text-2xl font-bold text-blue-700">{{ $this->anchoAjustado }} cm</p>
            </div>
            <div class="p-4 bg-white border rounded-lg shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase">Alto Inferior (Puente)</p>
                <p class="text-2xl font-bold text-blue-700">{{ $this->altoInf }} cm</p>
            </div>
            <div class="p-4 bg-white border rounded-lg shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase">Hojas Totales</p>
                <p class="text-2xl font-bold text-gray-800">{{ $this->divisionesInferiores }}</p>
            </div>
            <div class="p-4 bg-white border rounded-lg shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase">Garruchas</p>
                <p class="text-2xl font-bold text-gray-800">{{ $this->accesorios['garruchas'] }}</p>
            </div>
        </div>

        <div class="overflow-hidden bg-white border rounded-xl">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-600 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-4">Accesorio / Pieza</th>
                        <th class="px-6 py-4 text-center">Medida (cm)</th>
                        <th class="px-6 py-4 text-center">Cantidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($this->detalleModulos as $item)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $item['label'] }}</td>
                            <td class="px-6 py-4 text-center font-bold text-blue-600">{{ $item['alto'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="px-3 py-1 bg-gray-100 rounded-md font-bold">{{ $item['cantidad'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
