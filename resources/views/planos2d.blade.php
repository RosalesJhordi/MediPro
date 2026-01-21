<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Completo de Ventanas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 10px;
        }

        @media print {
            body {
                zoom: 0.65;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            table {
                font-size: 9px;
            }

            table th,
            table td {
                padding: 3px !important;
            }

            .plano {
                max-height: 135mm;
                border: 1px dashed #e5e7eb;
                padding: 6mm;
                box-sizing: border-box;
                break-inside: avoid;
                transform: scale(0.85);
                transform-origin: top center;
            }
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: white;
        }

        .blueprint-border {
            border: 6px solid #1a1a1a;
        }

        .glass-blue {
            background-color: #e0f2fe !important;
        }

        .glass-dark {
            background-color: #bae6fd !important;
        }

        .bg-gray-800 {
            background-color: #1f2937 !important;
        }
    </style>
</head>

<body>
    <div class="grid gap-8 {{ count($datos) > 3 ? 'grid-cols-2' : 'grid-cols-1' }}">
        @foreach ($datos as $ventana)
            @php
                $altoTotal = (float) $ventana['alto'];
                $anchoTotal = (float) $ventana['ancho'];
                $aInf = (float) ($ventana['altoInf'] ?? $altoTotal);
                $aSup = (float) ($ventana['altoSup'] ?? 0);
                $altoPuente = (float) ($ventana['altoPuente'] ?? 0);

                $sumaAlturas = $aSup + $aInf;
                $denominador = $sumaAlturas > 0 ? $sumaAlturas : 1;

                $altoSupPct = ($aSup / $denominador) * 100;
                $altoInfPct = ($aInf / $denominador) * 100;

                $bloquesActuales = $ventana['bloques'] ?? [];
                $sobreluzActual = $ventana['sobreluz'] ?? [];
                $detalleActual = $ventana['detalle'] ?? [];
            @endphp

            <div class="w-full p-2 ">

                {{-- CABECERA --}}
                <div class="mb-2 text-center border-b-2 border-blue-300 pb-2">
                    <span
                        class="bg-blue-600 text-white px-4 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                        Plano Técnico - SISTEMA NOVA
                    </span>
                    <h1 class="text-xl font-black text-gray-800 mt-2 uppercase">
                        {{ $ventana['nombre'] ?? $ventana['nv'] }}
                    </h1>
                    <p class="text-sm flex flex-col text-gray-500 mt-1 font-bold uppercase">
                        {{ $anchoTotal }} cm (Ancho) x {{ $altoTotal }} cm (Alto)
                        <span class="text-blue-600 text-xs">
                            Ancho ajustado: {{ $ventana['anchoAjustado'] }} cm
                        </span>
                    </p>
                </div>

                {{-- PLANO 2D --}}
                <div class="relative mx-auto mb-16 w-[80%] mt-4" style=" height: 350px;">

                    {{-- COTA LATERAL IZQUIERDA (TOTAL) --}}
                    <div class="absolute -left-14 top-0 h-full flex items-center justify-center">
                        <div class="w-[2px] h-full relative bg-blue-500">
                            <div class="absolute -top-1 -left-[4px] text-[8px] text-blue-500">▲</div>
                            <div class="absolute -bottom-1 -left-[4px] text-[8px] text-blue-500">▼</div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="-rotate-90 bg-white px-2 text-[12px] font-black text-blue-600 border border-blue-200 rounded">
                                    {{ $altoTotal }} cm
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- COTA LATERAL DERECHA (ALTO PUENTE) --}}
                    <div class="absolute -right-14 bottom-0 flex items-center justify-center"
                        style="height: {{ $altoInfPct }}%;">
                        <div class="w-[2px] h-full relative bg-orange-500">
                            <div class="absolute -top-1 -left-[4px] text-[8px] text-orange-500">▲</div>
                            <div class="absolute -bottom-1 -left-[4px] text-[8px] text-orange-500">▼</div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="rotate-90 bg-white px-2 text-[11px] font-black text-orange-600 border border-orange-200 rounded whitespace-nowrap">
                                    {{ $altoPuente }} cm
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DIBUJO DEL MARCO --}}
                    <div class="blueprint-border h-full w-full bg-gray-900 flex flex-col overflow-hidden shadow-2xl">

                        {{-- SECCIÓN SOBRELÚZ --}}
                        @if ($aSup > 0)
                            <div class="flex w-full border-b-[6px] border-gray-950"
                                style="height: {{ $altoSupPct }}%;">
                                @foreach ($sobreluzActual as $parte)
                                    <div
                                        class="flex-1 border-r-2 border-gray-900 glass-blue flex flex-col items-center justify-center relative">
                                        <span
                                            class="text-[9px] font-black text-blue-700 absolute top-2 uppercase tracking-tighter">{{ $parte['label'] ?? 'TL' }}</span>
                                        <span class="text-[11px] font-bold text-gray-800 mt-2">{{ $parte['ancho'] }} x
                                            {{ $parte['alto'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- SECCIÓN INFERIOR (HOJAS) --}}
                        <div class="flex w-full" style="height: {{ $altoInfPct }}%;">
                            @foreach ($bloquesActuales as $i => $mod)
                                <div
                                    class="flex-1 border-r-[4px] border-gray-950 relative flex flex-col justify-center items-center {{ $mod['tipo'] === 'C' ? 'glass-dark' : 'glass-blue' }}">

                                    {{-- INDICADOR DE HOJA --}}
                                    <div
                                        class="absolute top-2 left-2 px-1.5 py-0.5 rounded text-[10px] font-black {{ $mod['tipo'] === 'C' ? 'bg-yellow-400 text-yellow-900' : 'bg-green-600 text-white' }}">
                                        {{ $mod['tipo'] }}{{ $i + 1 }}
                                    </div>

                                    {{-- TEXTO DEL VIDRIO --}}
                                    <div class="text-center z-10">
                                        <p class="text-[10px] font-black text-blue-800 uppercase mb-1">Vidrio</p>
                                        <span
                                            class="text-[12px] font-mono font-black text-gray-900">{{ $mod['ancho'] }}
                                            x
                                            {{ $mod['alto'] }}</span>
                                    </div>

                                    {{-- EFECTO DE DESCUENTO (SOLO PARA TIPO 'C') --}}
                                    @if ($mod['tipo'] === 'C')
                                        <div
                                            class="absolute bottom-0 left-0 w-full h-[3%] bg-black border-t border-gray-900/50 flex items-center justify-center">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- COTA INFERIOR (ANCHO TOTAL) --}}
                    <div class="absolute -bottom-10 left-0 w-full flex justify-center">
                        <div class="h-[2px] w-full relative bg-blue-500">
                            <div class="absolute left-[-2px] top-[-3px] text-[8px] text-blue-500">◀</div>
                            <div class="absolute right-[-2px] top-[-3px] text-[8px] text-blue-500">▶</div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="bg-white px-4 text-[12px] font-black text-gray-700 border-x border-blue-200">
                                    Ancho: {{ $anchoTotal }} cm
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                {{-- TABLA DE MATERIALES --}}
                <div class="mt-2 flex flex-col justify-center items-center">
                    {{-- <h2
                        class="text-[12px] font-black text-gray-800 uppercase tracking-tighter mb-2 flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-600"></div> MAPEO DE COMPONENTES Y PERFILERÍA
                    </h2> --}}
                    <table class="w-[80%]  text-[11px] border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="border border-gray-600 p-2 text-left uppercase w-[60%]">Descripción de
                                    Perfiles /
                                    Accesorio</th>
                                <th class="border border-gray-600 p-2 text-center uppercase">Medida Corte</th>
                                <th class="border border-gray-600 p-2 text-center uppercase">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detalleActual as $tipo => $item)
                                @php
                                    $codigo = $item['label'];
                                    $nombreEncontrado = 'No identificado (' . $codigo . ')';

                                    if (!empty($ventana['catalogo'])) {
                                        foreach ($ventana['catalogo'] as $linea) {
                                            if (str_contains((string) $linea, (string) $codigo)) {
                                                $nombreEncontrado = $linea;
                                                break;
                                            }
                                        }
                                    }

                                    // Diferenciar parante 8115
                                    if ($codigo === '8115') {
                                        if ($tipo === 'PF Fijo') {
                                            $nombreEncontrado .= ' - FIJO';
                                        } elseif ($tipo === 'PF Corrediza') {
                                            $nombreEncontrado .= ' - CORREDIZA';
                                        }
                                    }
                                @endphp

                                <tr class="even:bg-gray-50">
                                    <td class="border border-gray-300 p-2 font-bold text-gray-700 uppercase">
                                        {{ $nombreEncontrado }}
                                    </td>
                                    <td
                                        class="border border-gray-300 p-2 text-center font-mono font-black text-blue-700 bg-blue-50/50">
                                        {{ $item['alto'] }} cm
                                    </td>
                                    <td class="border border-gray-300 p-2 text-center font-black">
                                        {{ $item['cantidad'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        <p class="text-[10px] text-end px-5 sticky bottom-2 tracking-[0.2em] uppercase text-gray-400">
            by <a href="https://www.facebook.com/share/1Eh3Dx3iKB/" target="_blank" rel="noopener noreferrer">
                <span class="font-bold text-blue-600">Jhon Rosales</span></a>
        </p>
    </div>
</body>

</html>
