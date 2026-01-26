<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    @vite('resources/css/app.css')

    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            body {
                zoom: 0.7;
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .shadow-sm,
            .shadow-xl,
            .shadow-inner {
                box-shadow: none !important;
            }

            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-900 text-sm">

    <div class="max-w-6xl mx-auto p-8 space-y-20">

        @foreach ($datos as $puerta)
            @php
                $datosPuerta = $puerta['datos'];

                $ruta = public_path('datos.xlsx');
                $data = [];
                if (file_exists($ruta)) {
                    $data = collect(\Maatwebsite\Excel\Facades\Excel::toArray([], $ruta)[0])
                        ->pluck(0)
                        ->filter()
                        ->values()
                        ->toArray();
                }
            @endphp

            <div class="">

                {{-- ================= HEADER ================= --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-3 mb-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-extrabold text-blue-800">
                            {{ $puerta['nombre'] }}
                        </h2>

                        <span
                            class="px-3 py-1 rounded-full text-xs font-bold
            {{ $puerta['conSobreluz'] ?? false ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $puerta['conSobreluz'] ?? false ? 'Con sobreluz' : 'Sin sobreluz' }}
                        </span>
                    </div>

                    <div class="mt-2 grid grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs text-slate-500 uppercase">Material</span>
                            <div class="font-semibold">{{ $puerta['material'] }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 uppercase">Ancho</span>
                            <div class="font-semibold">{{ $puerta['anchoTotal'] }} cm</div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 uppercase">Alto</span>
                            <div class="font-semibold">{{ $puerta['altoTotal'] }} cm</div>
                        </div>
                    </div>
                </div>

                {{-- ================= CONTENIDO ================= --}}
                <div class="grid grid-cols-2 gap-10 items-start">

                    {{-- ================= PLANO ================= --}}
                    <div class="bg-gray-50  p-6 flex flex-col items-center">

                        <span class="px-4 py-1.5 bg-blue-600 text-white text-xs font-black rounded-full mb-6">
                            Plano Técnico · Serie {{ $puerta['material'] }}
                        </span>

                        <div
                            class="relative w-[240px]
    {{ $puerta['conSobreluz'] ?? false ? 'h-[560px]' : 'h-[500px]' }}
    border-[6px] rounded-xl ring-1 ring-black/10 bg-white flex flex-col">

                            {{-- COTA ALTO --}}
                            <div
                                class="absolute -left-14 top-0 h-full flex flex-col items-center justify-between text-[10px] text-gray-600">
                                <span>{{ $puerta['altoTotal'] }} cm</span>
                                <div class="w-px flex-1 bg-gray-400"></div>
                                <span>ALTO</span>
                            </div>

                            {{-- SOBRELUZ --}}
                            @if (($puerta['conSobreluz'] ?? false) && isset($datosPuerta['Vidrio Sobreluz']))
                                <div class="relative bg-sky-100 border-b-4 border-black flex items-center justify-center"
                                    style="height: {{ max(80, $puerta['altoSobreluz']) }}px">

                                    <div class="text-center">
                                        <div class="text-[10px] font-extrabold uppercase text-sky-800">
                                            Vidrio Sobreluz
                                        </div>
                                        <div class="text-[11px] font-mono">
                                            {{ $datosPuerta['Vidrio Sobreluz']['medida'] }}
                                        </div>
                                    </div>

                                    <span class="absolute right-2 top-1 text-[9px] text-gray-500">
                                        {{ $puerta['altoSobreluz'] }} cm
                                    </span>
                                </div>
                            @endif

                            {{-- PUERTA --}}
                            <div class="flex-1 flex flex-col justify-between p-1">

                                {{-- VIDRIO SUPERIOR --}}
                                <div
                                    class="flex-1 bg-sky-100 border border-sky-300 flex flex-col items-center justify-center">
                                    <span class="text-[10px] font-extrabold uppercase text-sky-800">Vidrio</span>
                                    <span
                                        class="text-[11px] font-mono">{{ $datosPuerta['Vidrio']['medida'] ?? '—' }}</span>
                                </div>

                                {{-- TRAVESAÑO --}}
                                <div
                                    class="h-[40px] bg-gray-700 text-white flex items-center justify-between px-3 text-xs">
                                    <span class="font-bold">REF 5227</span>
                                    <span class="font-mono">{{ $datosPuerta['5227 - Travesaño']['medida'] ?? '—' }}
                                        cm</span>
                                    <div class="flex items-end gap-2">
                                        <span class="font-mono">
                                            {{ $datos['5227']['medida'] ?? '—' }} cm
                                        </span>
                                        <div
                                            class="w-5 h-5 rounded-full border border-white/30 flex items-center justify-center bg-gray-400/20">
                                            <div class="w-3 h-3 bg-yellow-500 rounded-full shadow-sm"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- BISAGRAS --}}
                                <div class="absolute left-[-4px] top-0 h-full flex flex-col justify-around py-12">
                                    <div class="w-2 h-8 bg-gray-400 rounded-sm"></div>
                                    <div class="w-2 h-8 bg-gray-400 rounded-sm"></div>
                                    <div class="w-2 h-8 bg-gray-400 rounded-sm"></div>
                                </div>

                                {{-- VIDRIO INFERIOR --}}
                                <div
                                    class="flex-1 bg-sky-100 border border-sky-300 flex flex-col items-center justify-center">
                                    <span class="text-[10px] font-extrabold uppercase text-sky-800">Vidrio</span>
                                    <span
                                        class="text-[11px] font-mono">{{ $datosPuerta['Vidrio']['medida'] ?? '—' }}</span>
                                </div>

                            </div>

                            {{-- COTA ANCHO --}}
                            <div
                                class="absolute -bottom-10 left-0 w-full flex items-center justify-between text-[10px] text-gray-600">
                                <span>{{ $puerta['anchoTotal'] }} cm</span>
                                <div class="h-px flex-1 bg-gray-400 mx-2"></div>
                                <span>ANCHO</span>
                            </div>

                        </div>
                    </div>

                    {{-- ================= TABLA ================= --}}
                    <div class="bg-white rounded-2xl h-full overflow-hidden">

                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 text-white font-extrabold">
                            Accesorios
                        </div>

                        <table class="w-full text-xs">
                            <thead class="bg-slate-100 uppercase text-slate-600">
                                <tr>
                                    <th class="px-4 py-2 text-left">Perfil</th>
                                    <th class="px-4 py-2 text-center">Medida</th>
                                    <th class="px-4 py-2 text-right">Cant.</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                @foreach ($datosPuerta as $nombre => $item)
                                    @if (str_starts_with($nombre, 'Vidrio'))
                                        @continue
                                    @endif

                                    @php
                                        preg_match('/^\d+/', $nombre, $m);
                                        $codigo = $m[0] ?? null;
                                        $nombreExcel = $nombre;

                                        if ($codigo) {
                                            foreach ($data as $row) {
                                                if (str_contains($row, $codigo)) {
                                                    $nombreExcel = $row;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp

                                    <tr class="even:bg-slate-50">
                                        <td class="px-4 py-2 font-medium">{{ $nombreExcel }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <span
                                                class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 font-mono">
                                                {{ $item['medida'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <span
                                                class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-bold">
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
        @endforeach

    </div>
</body>

</html>
