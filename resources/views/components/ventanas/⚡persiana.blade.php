<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="w-full flex justify-center py-12 bg-slate-100 overflow-auto">

    <div class="relative">

        {{-- titulo --}}
        <div class="flex justify-center mb-10">

            <div
                class="px-10 py-3 rounded-full bg-blue-600 text-white text-sm font-black tracking-[0.2em] shadow-lg">

                PLANO TÉCNICO · PERSIANA VITROVEN

            </div>

        </div>

        {{-- contenedor --}}
        <div class="relative flex justify-center">

            {{-- medida izquierda --}}
            <div
                class="absolute -left-24 top-1/2 -translate-y-1/2 flex flex-col items-center">

                <div class="relative w-[2px] h-[500px] bg-sky-400">

                    <div
                        class="absolute -top-2 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[7px] border-r-[7px] border-b-[12px] border-transparent border-b-sky-400">
                    </div>

                    <div
                        class="absolute -bottom-2 left-1/2 -translate-x-1/2 rotate-180 w-0 h-0 border-l-[7px] border-r-[7px] border-b-[12px] border-transparent border-b-sky-400">
                    </div>

                </div>

                <div
                    class="absolute bg-white border border-slate-300 shadow-sm px-4 py-2 rounded-lg text-slate-700 font-black rotate-[-90deg]">

                    150 cm

                </div>

            </div>

            {{-- ventana --}}
            <div
                class="relative w-[420px] h-[500px] border-[10px] border-slate-800 bg-slate-200 shadow-[0_25px_50px_rgba(15,23,42,0.18)] overflow-hidden">

                {{-- profundidad interna --}}
                <div
                    class="absolute inset-[10px] border-[4px] border-slate-500 bg-white">

                    {{-- riel izquierdo --}}
                    <div
                        class="absolute left-0 top-0 bottom-0 w-5 bg-slate-300 border-r border-slate-500">
                    </div>

                    {{-- riel derecho --}}
                    <div
                        class="absolute right-0 top-0 bottom-0 w-5 bg-slate-300 border-l border-slate-500">
                    </div>

                    {{-- lamas --}}
                    <div
                        class="absolute inset-0 px-5 py-5 flex flex-col gap-[6px]">

                        @for ($i = 0; $i < 10; $i++)

                            <div class="relative flex justify-center">

                                {{-- brazo izquierdo --}}
                                <div
                                    class="absolute left-0 top-1/2 -translate-y-1/2 w-4 h-[2px] bg-slate-500">
                                </div>

                                {{-- brazo derecho --}}
                                <div
                                    class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-[2px] bg-slate-500">
                                </div>

                                {{-- lama --}}
                                <div
                                    class="relative w-full h-9 border border-slate-400 bg-slate-50 shadow-sm"
                                    style="clip-path: polygon(0 8%, 100% 0%, 100% 92%, 0 100%);">

                                    {{-- brillo --}}
                                    <div
                                        class="absolute inset-x-0 top-0 h-[40%] bg-white/60">
                                    </div>

                                </div>

                            </div>

                        @endfor

                    </div>

                    {{-- eje central --}}
                    <div
                        class="absolute left-1/2 top-0 bottom-0 w-[2px] bg-slate-300 -translate-x-1/2 opacity-40">
                    </div>

                </div>

                {{-- informacion --}}
                <div
                    class="absolute bottom-5 left-1/2 -translate-x-1/2 bg-white/90 border border-slate-300 backdrop-blur-sm px-6 py-4 rounded-2xl shadow-lg text-center">

                    <div
                        class="text-blue-800 font-black tracking-wide text-xl">

                        PERSIANA

                    </div>

                    <div
                        class="mt-1 text-slate-600 font-bold text-sm tracking-wide">

                        80 x 150

                    </div>

                </div>

                {{-- etiqueta --}}
                <div
                    class="absolute top-4 left-4 px-3 py-1 bg-emerald-600 text-white text-xs font-black rounded-md shadow">

                    PV1

                </div>

            </div>

            {{-- medida derecha --}}
            <div
                class="absolute -right-24 top-1/2 -translate-y-1/2 flex flex-col items-center">

                <div class="relative w-[2px] h-[420px] bg-amber-500">

                    <div
                        class="absolute -top-2 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[7px] border-r-[7px] border-b-[12px] border-transparent border-b-amber-500">
                    </div>

                    <div
                        class="absolute -bottom-2 left-1/2 -translate-x-1/2 rotate-180 w-0 h-0 border-l-[7px] border-r-[7px] border-b-[12px] border-transparent border-b-amber-500">
                    </div>

                </div>

                <div
                    class="absolute bg-white border border-slate-300 shadow-sm px-4 py-2 rounded-lg text-slate-700 font-black rotate-[-90deg]">

                    130 cm

                </div>

            </div>

        </div>

        {{-- medida inferior --}}
        <div class="relative mt-12 flex justify-center">

            <div class="relative w-[420px] h-[2px] bg-blue-400">

                <div
                    class="absolute -left-1 -top-[5px] rotate-90 w-0 h-0 border-l-[7px] border-r-[7px] border-b-[12px] border-transparent border-b-blue-400">
                </div>

                <div
                    class="absolute -right-1 -top-[5px] -rotate-90 w-0 h-0 border-l-[7px] border-r-[7px] border-b-[12px] border-transparent border-b-blue-400">
                </div>

                <div
                    class="absolute left-1/2 -translate-x-1/2 -top-5 bg-white border border-slate-300 px-6 py-2 rounded-xl text-slate-700 font-black text-xl shadow-sm">

                    80 cm

                </div>

            </div>

        </div>

    </div>

</div>
