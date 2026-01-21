<?php

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    /* ===============================
     |  ESTADO BASE (EDITABLE)
     =============================== */
    public float $ancho = 205;
    public float $alto = 165;
    public float $altoPuente = 130;
    public int $numCorredizas = 1;
    public int $numFijos = 2;

    /* ===============================
     |  VENTANASpublic array $ventanas = [];
    public int $ventanaActiva = 0;
     =============================== */
    public array $ventanas = [];
    public int $ventanaActiva = 0;

    /* ===============================
     |  OTROS ESTADOS
     =============================== */
    public ?string $sistemaSelet = null;
    public array $ordenBloques = [];
    public array $data = [];
    public $planoExportHtml = null;

    /* ===============================
     |  AJUSTES INTERNOS
     =============================== */
    protected float $vidrio = 0.6;
    protected float $pffijo = 0.3;
    protected float $pfcorrediza = 2.0;
    protected float $sobreluz = 2.1;
    protected float $sbancho = 0.3;
    protected float $vfijo = 1.0;
    protected float $vcorrediza = 2.5;

    /* ===============================
     |  CICLO DE VIDA
     =============================== */
    public function mount()
    {
        $this->procesarPerfiles();

        // Cargar ventanas desde sesión si existen (V3)
        $this->ventanas = session('ventanas', [
            [
                'nombre' => 'V - 1',
                'ancho' => $this->ancho,
                'alto' => $this->alto,
                'altoPuente' => $this->altoPuente,
                'numCorredizas' => $this->numCorredizas,
                'numFijos' => $this->numFijos,
                'ordenBloques' => [],
                'sistemaSelet' => null,
            ],
        ]);

        $this->ventanaActiva = 0;
        $this->cargarVentanaActiva();
    }

    /* ===============================
     |  HELPERS
     =============================== */
    private function num($v, float $d = 0): float
    {
        return is_numeric($v) && $v > 0 ? (float) $v : $d;
    }

    private function div($a, $b, float $d = 0): float
    {
        return ($b = $this->num($b)) === 0 ? $d : $this->num($a) / $b;
    }

    private function trunc($v, int $d = 1): float
    {
        $f = 10 ** $d;
        return floor($this->num($v) * $f) / $f;
    }

    /* ===============================
     |  COMPUTED PROPERTIES
     =============================== */
    public function getDivisionesInferioresProperty(): int
    {
        return $this->numCorredizas + $this->numFijos;
    }

    public function getAnchoAjustadoProperty(): float
    {
        return match ($this->divisionesInferiores) {
            3 => $this->ancho + 1,
            5 => $this->ancho + 2,
            6 => $this->ancho + 3,
            default => $this->ancho,
        };
    }

    public function getAltoInfProperty(): float
    {
        return $this->altoPuente;
    }

    public function getAltoSupProperty(): float
    {
        return max(0, $this->alto - $this->altoPuente - $this->sobreluz);
    }

    public function getBloquesProperty(): array
    {
        $t = $this->divisionesInferiores;

        if ($t === 5) {
            return ['Fijo', 'Corrediza', 'Fijo', 'Corrediza', 'Fijo'];
        }
        if ($t === 6) {
            return ['Fijo', 'Corrediza', 'Fijo', 'Corrediza', 'Fijo', 'Corrediza'];
        }

        $l = intdiv($this->numFijos, 2);
        return [...array_fill(0, $l, 'Fijo'), ...array_fill(0, $this->numCorredizas, 'Corrediza'), ...array_fill(0, $this->numFijos - $l, 'Fijo')];
    }

    public function getSobreluzPartesProperty(): array
    {
        $d = $this->divisionesInferiores;
        $c = $d >= 5 ? 3 : ($d >= 3 ? 2 : 1);
        $a = $this->div($this->ancho, $c);

        return collect(range(1, $c))
            ->map(
                fn($i) => [
                    'ancho' => $this->trunc($a) - $this->sbancho,
                    'alto' => $this->trunc($this->altoSup),
                    'label' => $c > 1 ? "TL $i" : 'TL',
                ],
            )
            ->toArray();
    }

    public function getMedidasBloquesProperty(): array
    {
        $base = $this->div($this->anchoAjustado, $this->divisionesInferiores);
        return collect($this->bloques)
            ->map(
                fn($t) => [
                    'tipo' => $t === 'Fijo' ? 'F' : 'C',
                    'ancho' => $this->trunc($base + ($t === 'Fijo' ? $this->vidrio : -$this->vidrio)),
                    'alto' => $this->trunc($this->altoPuente - ($t === 'Fijo' ? $this->vfijo : $this->vcorrediza)),
                ],
            )
            ->toArray();
    }

    public function getDetalleModulosProperty(): array
    {
        $det = [];
        $w = $this->trunc($this->ancho);

        $det['U 3/4'] = ['label' => '7955', 'alto' => $w, 'cantidad' => 1];
        $det['T/M'] = ['label' => '5283', 'alto' => $w, 'cantidad' => 1];
        $det['RIEL L'] = ['label' => '8413', 'alto' => $w, 'cantidad' => 1];

        $f = $c = [];
        foreach ($this->medidasBloques as $b) {
            $a = number_format($b['ancho'], 1, '.', '');
            $b['tipo'] === 'F' ? ($f[$a] = ($f[$a] ?? 0) + 1) : ($c[$a] = ($c[$a] ?? 0) + 1);
        }

        foreach ($f as $a => $n) {
            $det["U F ($a)"] = ['label' => '3003', 'alto' => $a, 'cantidad' => $n];
        }
        foreach ($c as $a => $n) {
            $det["H ($a)"] = ['label' => '8220', 'alto' => $a, 'cantidad' => $n];
        }

        $pf = 0;
        foreach ($this->bloques as $i => $b) {
            if ($b === 'Fijo') {
                $pf += ($this->bloques[$i - 1] ?? null) === 'Corrediza' && ($this->bloques[$i + 1] ?? null) === 'Corrediza' ? 2 : 1;
            }
        }

        $det['PF Fijo'] = ['label' => '8115', 'alto' => $this->trunc($this->altoPuente - $this->pffijo), 'cantidad' => $pf];
        $det['PF Corrediza'] = ['label' => '8115', 'alto' => $this->trunc($this->altoPuente - $this->pfcorrediza), 'cantidad' => $this->numCorredizas * 2];

        return $det;
    }

    /* ===============================
     |  VENTANAS (GESTIÓN)
     =============================== */

    // V2: snapshot completo
    private function snapshotVentana(): array
    {
        return [
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'altoPuente' => $this->altoPuente,
            'numCorredizas' => $this->numCorredizas,
            'numFijos' => $this->numFijos,
            'ordenBloques' => $this->ordenBloques,
            'sistemaSelet' => $this->sistemaSelet,
        ];
    }

    private function guardarVentanaActual(): void
    {
        if (!isset($this->ventanas[$this->ventanaActiva])) {
            return;
        }

        $this->ventanas[$this->ventanaActiva] = array_merge(['nombre' => $this->ventanas[$this->ventanaActiva]['nombre']], $this->snapshotVentana());
    }

    private function cargarVentanaActiva(): void
    {
        $v = $this->ventanas[$this->ventanaActiva];

        $this->ancho = $v['ancho'];
        $this->alto = $v['alto'];
        $this->altoPuente = $v['altoPuente'];
        $this->numCorredizas = $v['numCorredizas'];
        $this->numFijos = $v['numFijos'];
        $this->ordenBloques = $v['ordenBloques'] ?? [];
        $this->sistemaSelet = $v['sistemaSelet'] ?? null;
    }

    public function cambiarVentana(int $i): void
    {
        $this->guardarVentanaActual();
        $this->ventanaActiva = $i;
        $this->cargarVentanaActiva();
    }

    public function agregarVentana(): void
    {
        $this->guardarVentanaActual();

        $this->ventanas[] = array_merge(['nombre' => 'V - ' . (count($this->ventanas) + 1)], $this->snapshotVentana());

        $this->ventanaActiva = count($this->ventanas) - 1;
        $this->cargarVentanaActiva();
    }

    // V3: Guardado automático en sesión
    public function updated($propertyName)
    {
        $this->guardarVentanaActual();
        session()->put('ventanas', $this->ventanas);
    }

    /* ===============================
     |  EXCEL
     =============================== */
    public function procesarPerfiles(): void
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

    public function getAccesoriosProperty(): array
    {
        return [
            'garruchas' => $this->numCorredizas * 2,
            'pestillos' => $this->numCorredizas,
            'topes' => $this->numCorredizas * 2,
        ];
    }

    private function calcularMedidasBloques(float $ancho, float $altoPuente, int $numCorredizas, int $numFijos, array $ordenBloques = []): array
    {
        $divisiones = $numCorredizas + $numFijos;
        if ($divisiones <= 0) {
            return [];
        }

        $anchoAjustado = $ancho;
        if ($divisiones === 3) {
            $anchoAjustado += 1;
        }
        if ($divisiones === 5) {
            $anchoAjustado += 2;
        }
        if ($divisiones === 6) {
            $anchoAjustado += 3;
        }

        $anchoPorParte = $anchoAjustado / $divisiones;

        if ($divisiones === 5) {
            $bloques = ['F', 'C', 'F', 'C', 'F'];
        } elseif ($divisiones === 6) {
            $bloques = ['F', 'C', 'F', 'C', 'F', 'C'];
        } else {
            $fijosIzq = intdiv($numFijos, 2);
            $fijosDer = $numFijos - $fijosIzq;
            $bloques = array_merge(array_fill(0, $fijosIzq, 'F'), array_fill(0, $numCorredizas, 'C'), array_fill(0, $fijosDer, 'F'));
        }

        $resultado = [];
        foreach ($bloques as $tipo) {
            $ajuste = $tipo === 'F' ? $this->vidrio : -$this->vidrio;

            $resultado[] = [
                'tipo' => $tipo,
                'ancho' => $this->trunc($anchoPorParte + $ajuste, 1),
                'alto' => $this->trunc($altoPuente - ($tipo === 'F' ? $this->vfijo : $this->vcorrediza), 1),
            ];
        }

        if (!empty($ordenBloques)) {
            $ordenados = [];
            foreach ($ordenBloques as $i) {
                if (isset($resultado[$i])) {
                    $ordenados[] = $resultado[$i];
                }
            }
            return $ordenados;
        }

        return $resultado;
    }
    private function calcularAnchoAjustado(float $ancho, int $numCorredizas, int $numFijos): float
    {
        $divisiones = $numCorredizas + $numFijos;

        return match ($divisiones) {
            3 => $ancho + 1,
            5 => $ancho + 2,
            6 => $ancho + 3,
            default => $ancho,
        };
    }
    private function calcularDetalleModulos(float $ancho, float $altoPuente, int $numCorredizas, int $numFijos): array
    {
        $divisiones = $numCorredizas + $numFijos;
        $anchoAjustado = $this->calcularAnchoAjustado($ancho, $numCorredizas, $numFijos);

        $base = $anchoAjustado / $divisiones;

        // Bloques
        if ($divisiones === 5) {
            $bloques = ['F', 'C', 'F', 'C', 'F'];
        } elseif ($divisiones === 6) {
            $bloques = ['F', 'C', 'F', 'C', 'F', 'C'];
        } else {
            $fIzq = intdiv($numFijos, 2);
            $fDer = $numFijos - $fIzq;
            $bloques = array_merge(array_fill(0, $fIzq, 'F'), array_fill(0, $numCorredizas, 'C'), array_fill(0, $fDer, 'F'));
        }

        $det = [];

        // Perfiles horizontales
        $w = $this->trunc($ancho);
        $det['U 3/4'] = ['label' => '7955', 'alto' => $w, 'cantidad' => 1];
        $det['T/M'] = ['label' => '5283', 'alto' => $w, 'cantidad' => 1];
        $det['RIEL L'] = ['label' => '8413', 'alto' => $w, 'cantidad' => 1];

        $f = $c = [];

        foreach ($bloques as $tipo) {
            $a = $this->trunc($base + ($tipo === 'F' ? $this->vidrio : -$this->vidrio));
            $key = number_format($a, 1, '.', '');

            $tipo === 'F' ? ($f[$key] = ($f[$key] ?? 0) + 1) : ($c[$key] = ($c[$key] ?? 0) + 1);
        }

        foreach ($f as $a => $n) {
            $det["U F ($a)"] = ['label' => '3003', 'alto' => $a, 'cantidad' => $n];
        }

        foreach ($c as $a => $n) {
            $det["H ($a)"] = ['label' => '8220', 'alto' => $a, 'cantidad' => $n];
        }

        // Parantes
        $pf = 0;
        foreach ($bloques as $i => $b) {
            if ($b === 'F') {
                $pf += ($bloques[$i - 1] ?? null) === 'C' && ($bloques[$i + 1] ?? null) === 'C' ? 2 : 1;
            }
        }

        $det['PF Fijo'] = [
            'label' => '8115',
            'alto' => $this->trunc($altoPuente - $this->pffijo),
            'cantidad' => $pf,
        ];

        $det['PF Corrediza'] = [
            'label' => '8115',
            'alto' => $this->trunc($altoPuente - $this->pfcorrediza),
            'cantidad' => $numCorredizas * 2,
        ];

        // Accesorios corrediza
        $det['Garruchas'] = [
            'label' => 'Garrucha armada',
            'alto' => 0,
            'cantidad' => $numCorredizas * 2,
        ];
        $det['Pestillos'] = [
            'label' => 'Accesorio',
            'alto' => 0,
            'cantidad' => $numCorredizas,
        ];

        return $det;
    }

    public function imprimirTodo(): void
    {
        $ventanasCompletas = [];

        $estadoOriginal = [
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'altoPuente' => $this->altoPuente,
            'numCorredizas' => $this->numCorredizas,
            'numFijos' => $this->numFijos,
            'ordenBloques' => $this->ordenBloques,
            'ventanaActiva' => $this->ventanaActiva,
        ];

        foreach ($this->ventanas as $index => $v) {
            // NO necesitamos cambiar el estado del componente
            // para calcular bloques, lo hacemos con la función directa
            $anchoAjustado = $this->calcularAnchoAjustado($v['ancho'], $v['numCorredizas'], $v['numFijos']);
            $ventanasCompletas[] = [
                'nombre' => $v['nombre'],
                'ancho' => $v['ancho'],
                'alto' => $v['alto'],
                'altoPuente' => $v['altoPuente'],
                'numCorredizas' => $v['numCorredizas'],
                'numFijos' => $v['numFijos'],

                'altoInf' => $v['altoPuente'],
                'altoSup' => max(0, $v['alto'] - $v['altoPuente'] - $this->sobreluz),

                'bloques' => $this->calcularMedidasBloques($v['ancho'], $v['altoPuente'], $v['numCorredizas'], $v['numFijos']),
                //'sobreluz' => $this->calcularSobreluz($v['ancho'], $v['alto'], $v['altoPuente']),
                'sobreluz' => $this->calcularSobreluz($v['ancho'], $v['alto'], $v['altoPuente'], $v['numCorredizas'], $v['numFijos']),

                //'sobreluz' => $this->getSobreluzPartesProperty(),
                'anchoAjustado' => $anchoAjustado,
                'detalle' => $this->calcularDetalleModulos($v['ancho'], $v['altoPuente'], $v['numCorredizas'], $v['numFijos']),
                //'detalle' => $this->getDetalleModulosProperty(),
                'catalogo' => $this->data,
            ];
        }

        session()->put('datos_lote', $ventanasCompletas);
        session()->save();

        //$datos = session('datos_lote', []);
        //dd($datos);
        $this->dispatch('disparar-impresion-total');
    }
    private function calcularSobreluz(float $ancho, float $alto, float $altoPuente, int $numCorredizas, int $numFijos): array
    {
        $altoSup = max(0, $alto - $altoPuente - $this->sobreluz);

        if ($altoSup <= 0) {
            return [];
        }

        $divisiones = $numCorredizas + $numFijos;
        $c = $divisiones >= 5 ? 3 : ($divisiones >= 3 ? 2 : 1);
        $a = $ancho / $c;

        return collect(range(1, $c))
            ->map(
                fn($i) => [
                    'ancho' => $this->trunc($a) - $this->sbancho,
                    'alto' => $this->trunc($altoSup),
                    'label' => $c > 1 ? "TL $i" : 'TL',
                ],
            )
            ->toArray();
    }

    public function confirmarImpresion(): void
    {
        session()->forget('datos_lote');
        session()->forget('ventanas'); // si también quieres borrar ventanas
        session()->save();
        // Reiniciar estado del componente
        $this->ventanas = [
            [
                'nombre' => 'V - 1',
                'ancho' => $this->ancho,
                'alto' => $this->alto,
                'altoPuente' => $this->altoPuente,
                'numCorredizas' => $this->numCorredizas,
                'numFijos' => $this->numFijos,
                'ordenBloques' => [],
                'sistemaSelet' => null,
            ],
        ];

        $this->ventanaActiva = 0;
    }
    public function eliminarVentana(int $index): void
    {
        // No permitir borrar si solo queda una
        if (count($this->ventanas) <= 1) {
            return;
        }

        unset($this->ventanas[$index]);

        // Reindexar array
        $this->ventanas = array_values($this->ventanas);

        // Ajustar ventana activa
        if ($this->ventanaActiva >= count($this->ventanas)) {
            $this->ventanaActiva = count($this->ventanas) - 1;
        }

        $this->cargarVentanaActiva();

        // Guardar en sesión
        session()->put('ventanas', $this->ventanas);
    }
};

// $datos = session('datos_lote', []);
// dd($datos);

?>

<div wire:cloak class="relative p-2 md:p-6 max-w-6xl mx-auto mb-[100px]">
    <div class="flex justify-between items-center gap-1 mb-6 px-2 overflow-x-auto border-b border-gray-200">
        <div class="flex gap-">
            @foreach ($ventanas as $index => $v)
                <div
                    class="group relative flex items-center rounded-t-xl border transition-all
        {{ $ventanaActiva == $index
            ? 'bg-white border-gray-200 text-blue-600 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]'
            : 'bg-gray-100 border-transparent text-gray-400 hover:bg-gray-200' }}">

                    {{-- Botón principal --}}
                    <button wire:click="cambiarVentana({{ $index }})"
                        class="flex items-center gap-2 px-5 py-2 text-xs font-black uppercase tracking-tighter focus:outline-none">
                        <i class="fa-solid fa-window-maximize"></i>
                        {{ $v['nombre'] }}
                    </button>

                    {{-- Botón cerrar --}}
                    <button wire:click.stop="eliminarVentana({{ $index }})"
                        class="absolute -right-1 -top-1 w-5 h-5 rounded-full bg-red-500 text-white text-[10px]
                   opacity-0 group-hover:opacity-100 transition
                   hover:bg-red-600 flex items-center justify-center shadow">
                        ✕
                    </button>
                </div>
            @endforeach

            <button wire:click="agregarVentana"
                class="ml-2 px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors flex items-center gap-2 text-xs font-bold">
                <i class="fa-solid fa-plus-circle"></i> Nuevo
            </button>
            <button wire:click="confirmarImpresion"
                class="ml-2 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-2 text-xs font-bold">
                <i class="fa-solid fa-trash"></i> Vaciar
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
            <input type="number" wire:model.blur
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
            window.addEventListener('disparar-impresion-total', () => {
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

    <div wire:loading class="fixed inset-0 z-50 bg-gray-500/50">
        <div class="absolute inset-0 flex items-center justify-center">
            <div role="status">
                <svg aria-hidden="true" class="w-16 h-16 text-gray-200 animate-spin fill-blue-600"
                    viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
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
