<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div
    x-data="{ abierta: true }"
    class="flex items-center justify-center h-screen bg-gradient-to-br from-gray-200 to-gray-300"
>

    <!-- Marco exterior -->
    <div class="relative w-[650px] h-[300px] border-[10px] border-b-[5px] border-gray-800 bg-gray-100 overflow-hidden shadow-2xl">

        <!-- Riel superior -->
        <div class="absolute top-0 left-0 w-full h-5 bg-gradient-to-b from-gray-900 to-gray-700"></div>

        <!-- Pared izquierda -->
        <div class="absolute top-0 left-0 w-1/2 h-full bg-gray-300 flex items-center justify-center text-gray-500 font-semibold text-lg">
            Muro
        </div>

        <!-- Hueco derecho -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-white flex items-center justify-center text-gray-300 text-xl font-bold tracking-widest">
            HUECO
        </div>

        <!-- Puerta corrediza -->
        <div
            @click="abierta = !abierta"
            :class="abierta
                ? 'translate-x-0 rotate-y-0 shadow-xl'
                : 'translate-x-full rotate-y-6 shadow-2xl'"
            class="absolute top-0 left-0 w-1/2 h-full
                   bg-gradient-to-r bg-blue-700 to-blue-500
                   text-white font-semibold text-lg
                   flex items-center justify-center
                   cursor-pointer
                   border-black border-8
                   transition-all duration-[1200ms] ease-in-out
                   transform-gpu"
            style="transform-style: preserve-3d;"
        >

            <!-- Manija -->
            <div class="absolute right-4 w-4 h-13 bg-gray-200 rounded-full shadow-inner border border-gray-400"></div>

            Hoja Corrediza

        </div>

    </div>

</div>

<style>
.rotate-y-6 {
    transform: rotateY(6deg);
}
.rotate-y-0 {
    transform: rotateY(0deg);
}
</style>
