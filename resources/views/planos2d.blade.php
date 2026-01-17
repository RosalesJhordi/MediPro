<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Completo de Ventanas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            margin: 1cm;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* SALTO DE PÁGINA CLAVE */
            .page-break {
                page-break-after: always;
                display: block;
            }

            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: white;
            -webkit-print-color-adjust: exact;
        }

        .blueprint-border { border: 6px solid #1a1a1a; }
        .glass-blue { background-color: #e0f2fe !important; }
        .glass-dark { background-color: #bae6fd !important; }
        .bg-gray-800 { background-color: #1f2937 !important; }
    </style>
</head>

<body>

    {{-- RECORREMOS TODAS LAS VENTANAS GUARDADAS EN LA SESIÓN --}}
    @foreach (session('datos_lote', []) as $ventana)
        <div class="page-break p-4">

            {{-- CABECERA POR VENTANA --}}
            <div class="mb-10 text-center border-b-2 border-blue-100 pb-4">
                <span class="bg-blue-600 text-white px-4 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                    Documento Técnico - SISTEMA NOVA
                </span>
                <h1 class="text-xl font-black text-gray-800 mt-2 uppercase">
                    {{ $ventana['nv'] }}
                </h1>
                <p class="text-xs text-gray-500 mt-1">Medidas: {{ $ventana['ancho'] }} x {{ $ventana['alto'] }} cm</p>
            </div>

            @php
                $altoT = $ventana['alto'];
                $aInf = $ventana['altoInf'];
                $aSup = $ventana['altoSup'];
                $altoSupPct = $aSup > 0 ? ($aSup / $altoT) * 100 : 0;
                $altoInfPct = ($aInf / $altoT) * 100;
            @endphp

            {{-- PLANO 2D --}}
            <div class="relative mx-auto mb-24 mt-10" style="width: 85%; max-width: 700px; height: 350px;">

                {{-- COTA LATERAL TOTAL --}}
                <div class="absolute -left-12 top-0 h-full flex items-center justify-center">
                    <div class="w-[1.5px] h-full relative bg-slate-400">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="-rotate-90 whitespace-nowrap bg-white px-1 text-[11px] font-bold text-gray-500 uppercase">
                                {{ $altoT }} cm
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DIBUJO DEL MARCO --}}
                <div class="blueprint-border h-full w-full bg-gray-800 flex flex-col overflow-hidden shadow-xl">
                    {{-- SOBRELÚZ --}}
                    @if ($aSup > 0)
                        <div class="flex w-full border-b-[4px] border-gray-900" style="height: {{ $altoSupPct }}%;">
                            @foreach ($ventana['sobreluz'] as $parte)
                                <div class="flex-1 border-r-2 border-gray-900 glass-blue flex flex-col items-center justify-center relative">
                                    <span class="text-[10px] font-mono font-black text-blue-900">{{ $parte['ancho'] }} × {{ $parte['alto'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- CUERPO INFERIOR --}}
                    <div class="flex w-full" style="height: {{ $altoInfPct }}%;">
                        @foreach ($ventana['bloques'] as $i => $mod)
                            <div class="flex-1 border-r-[4px] border-gray-900 relative flex flex-col justify-center items-center {{ $mod['tipo'] === 'C' ? 'glass-dark' : 'glass-blue' }}">
                                <div class="absolute top-2 left-2 px-1 py-0.5 rounded-sm text-[8px] font-black {{ $mod['tipo'] === 'C' ? 'bg-yellow-400 text-yellow-900' : 'bg-green-600 text-white' }}">
                                    {{ $mod['tipo'] }}{{ $i + 1 }}
                                </div>
                                <div class="bg-white/40 px-2 py-1 rounded text-center">
                                    <span class="text-[11px] font-mono font-black text-gray-900">{{ $mod['ancho'] }} × {{ $mod['alto'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- TABLA DE MATERIALES --}}
            <div class="mt-12">
                <h2 class="text-sm font-black text-blue-800 uppercase tracking-widest mb-3 border-l-4 border-blue-600 pl-2">
                    Especificaciones de Materiales - {{ $ventana['nv'] }}
                </h2>
                <table class="w-full text-[11px] border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="border border-gray-300 p-2 text-left uppercase">Descripción</th>
                            <th class="border border-gray-300 p-2 text-center uppercase">Corte (cm)</th>
                            <th class="border border-gray-300 p-2 text-center uppercase">Cant.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ventana['detalle'] as $item)
                            @php
                                $codigo = $item['label'];
                                $nombreEncontrado = 'Perfil No Identificado (' . $codigo . ')';
                                if (isset($ventana['catalogo'])) {
                                    foreach ($ventana['catalogo'] as $linea) {
                                        if (str_contains(strval($linea), strval($codigo))) {
                                            $nombreEncontrado = $linea;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-300 p-2 font-bold text-gray-700">{{ $nombreEncontrado }}</td>
                                <td class="border border-gray-300 p-2 text-center font-mono font-bold text-blue-700">{{ $item['alto'] }}</td>
                                <td class="border border-gray-300 p-2 text-center font-bold">{{ $item['cantidad'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

</body>
</html>
