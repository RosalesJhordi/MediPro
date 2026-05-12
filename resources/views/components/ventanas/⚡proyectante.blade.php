<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>

<div x-data="{ open: false }"
    class="min-h-screen bg-neutral-100 flex flex-col items-center justify-center p-10 font-mono overflow-hidden">

    <div class="mb-16 text-center">
        <h1 class="text-xs tracking-[0.45em] uppercase text-neutral-700 font-bold">
            Plano 2d: Proyectante
        </h1>
    </div>

    <div class="flex flex-wrap justify-center items-end gap-32">

        <div class="flex flex-col items-center">

            <div @click="open=!open" class="relative w-72 h-92 cursor-pointer">

                <div class="absolute inset-0 border-[16px] border-gray-900 shadow-lg">

                    <div class="absolute inset-0 bg-gray-900 border border-neutral-300 origin-top transition-all duration-700 ease-[cubic-bezier(.22,1,.36,1)] shadow-2xl"
                        :style="open ? 'transform:perspective(1600px) rotateX(-30deg) translateY(22px);' :
                            'transform:perspective(1600px) rotateX(0deg) translateY(0px);'">

                        <div
                            class="absolute text-center flex justify-center items-center inset-4 bg-sky-100 border border-sky-100">
                            <p>vidrio</p>
                        </div>

                        <div class="absolute bottom-3 left-1/2 transition-all duration-500 ease-out"
                            :style="open ? 'transform:translateX(-50%) rotate(90deg);' :
                                'transform:translateX(-50%) rotate(0deg);'">
                            <div class="w-12 h-2.5 bg-black rounded-full shadow-lg"></div>
                        </div>

                    </div>

                </div>

                <!-- MEDIDA ANCHO -->
                <div class="absolute -bottom-5 left-0 w-full flex items-center">
                    <div class="w-full border-t border-gray-500 border-dashed"></div>
                    <span class="absolute left-1/2 -translate-x-1/2 bg-neutral-100 px-2 text-[10px] text-gray-500">
                        120 cm
                    </span>
                </div>

                <!-- MEDIDA ALTO -->
                <div class="absolute -left-5 top-0 h-full flex flex-col items-center">
                    <div class="h-full border-l border-gray-500 border-dashed"></div>
                    <span
                        class="absolute w-20 top-1/2 -translate-y-1/2 -rotate-45 bg-neutral-100 px-2 text-[10px] text-gray-500">
                        180 cm
                    </span>
                </div>

            </div>

            <span class="mt-16 text-[10px] tracking-[0.25em] uppercase text-neutral-400 font-bold">
                Frontal
            </span>

        </div>

        <div class="flex flex-col items-center">

            <div class="relative w-40 h-92 overflow-visible">

                <div class="absolute left-0 top-0 w-5 h-full bg-gray-900"></div>

                <div class="absolute left-[20px] w-3 h-[367px] origin-top-left transition-all duration-700 ease-[cubic-bezier(.22,1,.36,1)] z-30"
                    :style="open ? 'transform:rotate(-28deg) translateY(8px);' : 'transform:rotate(0deg) translateY(0px);'">

                    <div class="absolute inset-0 bg-gray-900 border z-[999px] border-gray-900 shadow-xl"></div>

                    <div class="absolute top-[46px] left-0 origin-left z-10 transition-all duration-[800ms] ease-[cubic-bezier(.22,1,.36,1)]"
                        :style="open ? 'width:40px; transform:rotate(160deg);' : 'width:0px; transform:rotate(180deg);'">
                        <div class="h-[5px] bg-gray-400 w-full rounded-full"></div>
                    </div>

                    <div class="absolute top-[146px] left-0 origin-left z-10 transition-all duration-[815ms] ease-[cubic-bezier(.22,1,.36,1)]"
                        :style="open ? 'width:112px; transform:rotate(160deg);' : 'width:0px; transform:rotate(180deg);'">
                        <div class="h-[5px] bg-gray-400 w-full rounded-full"></div>
                    </div>

                    <div class="absolute bottom-6 -right-2 transition-all duration-500 ease-out"
                        :style="open ? 'transform:rotate(90deg);' : 'transform:rotate(0deg);'">
                        <div class="w-4 h-2 bg-black rounded-sm"></div>
                    </div>

                </div>

            </div>

            <span class="mt-16 text-[10px] tracking-[0.25em] uppercase text-neutral-400 font-bold">
                Lateral
            </span>

        </div>

    </div>

    <div class="mt-16 flex flex-col items-center gap-4">

        <button @click="open=!open"
            class="px-10 py-3 border-2 border-black bg-white text-[10px] tracking-[0.35em] uppercase font-black hover:bg-black hover:text-white transition-all duration-300 active:scale-95">
            <span x-text="open ? 'Cerrar' : 'Abrir'"></span>
        </button>

    </div>

</div>
