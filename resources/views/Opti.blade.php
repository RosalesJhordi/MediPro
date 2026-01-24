<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediPro - Optimizador</title>
    <script src="https://kit.fontawesome.com/a22afade38.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')

    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="https://unpkg.com/dom-to-image-more@3.2.0/dist/dom-to-image-more.min.js"></script>


    <script src="https://unpkg.com/dom-to-image-more@3.2.0/dist/dom-to-image-more.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
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
<body>
    <livewire:optimizador.app />
</body>
</html>
