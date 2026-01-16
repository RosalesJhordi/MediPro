<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediPro - Puerta</title>
    <script src="https://kit.fontawesome.com/a22afade38.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')

    <style>
        /* Animación de flotación suave */
        @keyframes floating {
            0% {
                transform: translateY(0px);
                box-shadow: 0 5px 15px 0px rgba(217, 119, 6, 0.4);
            }
            50% {
                transform: translateY(-8px);
                box-shadow: 0 20px 25px -5px rgba(217, 119, 6, 0.3);
            }
            100% {
                transform: translateY(0px);
                box-shadow: 0 5px 15px 0px rgba(217, 119, 6, 0.4);
            }
        }

        .floating-button {
            animation: floating 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-gray-100 h-screen relative">

    <a href="/"
        class="fixed z-[999] top-4 left-4 flex items-center justify-center w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl shadow-lg transition-colors floating-button border-2 border-white/20">
        <i class="fa-solid fa-house text-lg"></i>
    </a>

    <livewire:puertas.app>

</body>

</html>
