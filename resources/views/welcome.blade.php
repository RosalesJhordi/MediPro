<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a MediPro</title>
    <script src="https://cdn.tailwindcss.com"></script>

    @vite('resources/css/app.css')
    <style>
        /* Fondo con movimiento de aurora */
        body {
            background: linear-gradient(125deg, #ffffff 0%, #f0f7ff 50%, #e0eaff 100%);
            overflow: hidden;
        }

        /* Animación de burbujas orgánicas */
        @keyframes orbit {
            0% {
                transform: translate(0, 0) scale(1) rotate(0deg);
            }

            33% {
                transform: translate(100px, 100px) scale(1.2) rotate(120deg);
            }

            66% {
                transform: translate(-50px, 150px) scale(0.8) rotate(240deg);
            }

            100% {
                transform: translate(0, 0) scale(1) rotate(360deg);
            }
        }

        /* Partículas pequeñas de brillo */
        @keyframes shine {

            0%,
            100% {
                opacity: 0.2;
                transform: translateY(0);
            }

            50% {
                opacity: 0.8;
                transform: translateY(-20px);
            }
        }

        .bubble {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            mix-blend-mode: multiply;
            animation: orbit 25s infinite linear;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(59, 130, 246, 0.5);
            border-radius: 50%;
            animation: shine 5s infinite ease-in-out;
        }

        /* Efecto de cristal para las tarjetas */
        .glass-card {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        /* Texto con gradiente fluido */
        .text-gradient {
            background: linear-gradient(to right, #1e40af, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% auto;
            animation: textFlow 5s linear infinite;
        }

        @keyframes textFlow {
            to {
                background-position: 200% center;
            }
        }

        /* Animación sutil para el badge superior */
        @keyframes pulse-soft {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        .badge-anim {
            animation: pulse-soft 3s infinite ease-in-out;
        }
    </style>
    <style>
        [wire\:cloak],
        [x-cloak] {
            display: none !important;
        }
    </style>

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #area-mapa-corte,
            #area-mapa-corte * {
                visibility: visible !important;
            }

            #area-mapa-corte {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .shadow-2xl,
            .shadow-inner,
            .shadow-xl {
                box-shadow: none !important;
            }

            button,
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="relative flex items-center justify-center h-screen text-gray-800">

    <div class="absolute inset-0 overflow-hidden -z-10">
        <div class="bubble w-[500px] h-[500px] bg-blue-300/40 top-[-10%] left-[-10%]"></div>
        <div class="bubble w-[400px] h-[400px] bg-purple-300/30 bottom-[5%] right-[-5%]"
            style="animation-duration: 30s; animation-delay: -5s;"></div>
        <div class="bubble w-[300px] h-[300px] bg-pink-200/40 top-[20%] right-[15%]"
            style="animation-duration: 20s; animation-delay: -2s;"></div>
        <div class="bubble w-[450px] h-[450px] bg-emerald-100/50 bottom-[-10%] left-[10%]"
            style="animation-duration: 35s;"></div>

        <div class="particle top-1/4 left-1/4"></div>
        <div class="particle top-1/3 right-1/4" style="animation-delay: 1s;"></div>
        <div class="particle bottom-1/4 left-1/2" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative w-full lg:max-w-xl px-4 lg:px-6 text-center">

        <div class="flex justify-center mb-6">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 border border-blue-100 rounded-full shadow-sm badge-anim bg-white/60 backdrop-blur-md">
                <span class="relative flex w-2 h-2">
                    <span
                        class="absolute inline-flex w-full h-full bg-blue-400 rounded-full opacity-75 animate-ping"></span>
                    <span class="relative inline-flex w-2 h-2 bg-blue-500 rounded-full"></span>
                </span>
                <span class="text-[10px] font-black text-blue-700 uppercase tracking-[0.2em]">MediPro v1.0</span>
            </div>
        </div>

        <div class="flex justify-center mb-2">
            <span class="inline-block w-12 h-1 bg-blue-500 rounded-full opacity-50"></span>
        </div>

        <h1 class="pb-2 text-5xl font-black tracking-tighter lg:text-7xl text-gradient">
            Bienvenido
        </h1>

        <p class="mt-4 text-md lg:text-xl md:text-2xl font-light text-gray-500 tracking-wide uppercase text-[0.9rem]">
            ¿Qué desea realizar <span class="font-bold text-gray-700">hoy</span>?
        </p>

        <div class="flex items-center justify-center w-full  gap-6 p-2 lg:p-4 mt-6">

            <a href="Ventana" wire:navigate
                class="flex flex-col items-center justify-center w-1/2 h-40 lg:w-40 lg:h-40 p-1.5 lg:p-4 space-y-2 lg:space-y-4 transition-transform cursor-pointer glass-card rounded-3xl hover:scale-105 active:scale-95">
                <div
                    class="p-3 lg:p-4 rounded-2xl bg-gradient-to-tr bg-green-400 to-emerald-600 text-white shadow-[0_10px_20px_rgba(16,185,129,0.2)]">
                    <img src="{{ asset('img/ventana.svg') }}" class="w-10 h-10 " alt="Ventana">
                </div>
                <span class="text-sm font-bold text-gray-700">Ventana</span>
            </a>

            <a href="Puerta" wire:navigate
                class="flex flex-col items-center justify-center w-1/2 h-40 lg:w-40 lg:h-40 p-1.5 lg:p-4 space-y-2 lg:space-y-4 transition-transform cursor-pointer glass-card rounded-3xl hover:scale-105 active:scale-95">
                <div
                    class=" p-3 lg:p-4 rounded-2xl bg-gradient-to-tr bg-orange-400 to-red-600 text-white shadow-[0_10px_20px_rgba(239,68,68,0.2)]">
                    <img src="{{ asset('img/puerta.svg') }}" class="w-10 h-10" alt="Puerta">
                </div>
                <span class="text-sm font-bold text-gray-700">Puerta</span>
            </a>

        </div>

        <div class="mt-20 opacity-60">
            <div class="h-[1px] w-full bg-gradient-to-r from-transparent via-gray-300 to-transparent mb-4"></div>
            <p class="text-[10px] tracking-[0.2em] uppercase text-gray-400">
                by <a href="https://www.facebook.com/share/1Eh3Dx3iKB/" target="_blank" rel="noopener noreferrer">
                    <span class="font-bold text-blue-600">Jhon Rosales</span></a>
            </p>
        </div>
    </div>

</body>

</html>
