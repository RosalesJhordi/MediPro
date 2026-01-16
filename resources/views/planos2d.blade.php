<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Plano Técnico - {{ $tipo }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: white;
        }

        .blueprint-border {
            border: 6px solid #1a1a1a;
        }

        .glass-blue {
            background-color: #e0f2fe;
        }

        .glass-dark {
            background-color: #bae6fd;
        }
    </style>
</head>

<body class="p-4">

    <div class="mb-10 text-center border-b-2 border-blue-100 pb-4">
        <span class="bg-blue-600 text-white px-4 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
            Documento Técnico
        </span>
        <h1 class="text-3xl font-black text-gray-800 mt-2 uppercase">
            Plano 2D: {{ $tipo }}
        </h1>
        <p class="text-xs text-gray-500 mt-1">Generado el {{ $fecha }}</p>
    </div>

    @php
        $altoTotal = $altoInf + $altoSup;
        $altoSupPct = $altoSup > 0 ? ($altoSup / $altoTotal) * 100 : 0;
        $altoInfPct = ($altoInf / $altoTotal) * 100;
    @endphp

    <div class="relative mx-auto mb-24 mt-10" style="width: 85%; max-width: 700px; height: 350px;">

        <div class="absolute -left-12 top-0 h-full flex items-center justify-center">
            <div class="w-[1.5px] h-full relative bg-slate-400">
                <span class="absolute -top-1 -left-[4px] text-[10px] text-slate-400">▲</span>
                <span class="absolute -bottom-1 -left-[4px] text-[10px] text-slate-400">▼</span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div
                        class="-rotate-90 whitespace-nowrap bg-white px-1 text-[11px] font-bold text-gray-500 uppercase tracking-tighter">
                        {{ $altoTotal }} cm
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute -right-12 bottom-0 flex items-center justify-center" style="height: {{ $altoInfPct }}%;">
            <div class="w-[1.5px] h-full relative bg-slate-400">
                <span class="absolute -top-1 -left-[3.5px] text-[10px] text-slate-400">▲</span>
                <span class="absolute -bottom-1 -left-[3.5px] text-[10px] text-slate-400">▼</span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div
                        class="-rotate-90 whitespace-nowrap bg-white px-1 text-[11px] font-bold text-gray-500 uppercase tracking-tighter">
                        {{ $altoInf }} cm
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute -bottom-10 left-0 w-full flex justify-center">
            <div class="h-[1.5px] w-full relative bg-slate-400">
                <span class="absolute -left-1 -top-[5.5px] text-[10px] text-slate-400">◀</span>
                <span class="absolute -right-1 -top-[5.5px] text-[10px] text-slate-400">▶</span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="bg-white px-2 text-[11px] font-bold text-gray-500 uppercase tracking-tighter">
                        {{ $ancho }} cm
                    </div>
                </div>
            </div>
        </div>

        <div class="blueprint-border h-full w-full bg-gray-800 flex flex-col overflow-hidden shadow-xl">
            {{-- SOBRELÚZ --}}
            @if ($altoSup > 0)
                <div class="flex w-full border-b-[4px] border-gray-900" style="height: {{ $altoSupPct }}%;">
                    @foreach ($sobreluz as $parte)
                        <div
                            class="flex-1 border-r-2 border-gray-900 glass-blue flex flex-col items-center justify-center relative">
                            <span
                                class="text-[8px] font-black text-blue-800 opacity-50 uppercase tracking-widest">TL</span>
                            <span class="text-[10px] font-mono font-black text-blue-900">
                                {{ $parte['ancho'] }} × {{ $parte['alto'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- CUERPO INFERIOR --}}
            <div class="flex w-full" style="height: {{ $altoInfPct }}%;">
                @foreach ($bloques as $i => $mod)
                    <div
                        class="flex-1 border-r-[4px] border-gray-900 relative flex flex-col justify-center items-center {{ $mod['tipo'] === 'C' ? 'glass-dark' : 'glass-blue' }}">
                        <div
                            class="absolute top-2 left-2 px-1 py-0.5 rounded-sm text-[8px] font-black {{ $mod['tipo'] === 'C' ? 'bg-yellow-400 text-yellow-900' : 'bg-green-600 text-white' }}">
                            {{ $mod['tipo'] }}{{ $i + 1 }}
                        </div>

                        @if ($mod['tipo'] === 'C')
                            <div class="mb-1">
                                <svg width="100" height="25" viewBox="0 0 24 24" fill="none" stroke="#1e40af"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <!-- Flecha izquierda -->
                                    <path d="M10 6l-4 4 4 4" />
                                    <path d="M6 10h6" />

                                    <!-- Flecha derecha -->
                                    <path d="M14 14l4-4-4-4" />
                                    <path d="M18 10h-6" />
                                </svg>
                            </div>
                        @endif

                        <div class="bg-white/40 px-2 py-1 rounded text-center">
                            <span class="text-[9px] font-black text-blue-900 block uppercase opacity-60">Vidrio</span>
                            <span class="text-[11px] font-mono font-black text-gray-900">
                                {{ $mod['ancho'] }} × {{ $mod['alto'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-sm font-black text-blue-800 uppercase tracking-widest mb-3 border-l-4 border-blue-600 pl-2">
            Especificaciones de Materiales
        </h2>
        <table class="w-full text-[11px] border-collapse shadow-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="border border-gray-300 p-2 text-left uppercase">Descripción</th>
                    <th class="border border-gray-300 p-2 text-center uppercase">Corte (cm)</th>
                    <th class="border border-gray-300 p-2 text-center uppercase">Cant.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalle as $item)
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="border border-gray-300 p-2 font-bold text-gray-700">{{ $item['label'] }}</td>
                        <td class="border border-gray-300 p-2 text-center font-mono font-bold text-blue-700">
                            {{ $item['alto'] }}</td>
                        <td class="border border-gray-300 p-2 text-center font-bold">{{ $item['cantidad'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-8 text-center border-t border-gray-100 pt-4">
        <p class="text-[9px] text-gray-400 italic">Este plano es una representación técnica computarizada. Verifique
            medidas en obra.</p>
    </div>

</body>

</html>
