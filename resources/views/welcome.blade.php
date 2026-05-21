<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HidroGest</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('css/map.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 h-screen flex overflow-hidden font-sans">

    <aside class="w-96 bg-white shadow-2xl flex flex-col z-20">
        <div class="bg-blue-900 text-white p-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/hidrogest.svg') }}" alt="Logo HidroGest" class="h-10 w-10">                    
                    <h1 class="text-2xl font-bold tracking-tight">HidroGest - Matamoros</h1>
                </div>
                <p class="text-blue-200 text-sm mt-1">Sistema Tecnológico para la Gestión del Servicio de Agua Potable</p>
        </div>
        <div class="flex-1 overflow-y-auto p-4">
            <h2 class="text-gray-500 text-xs font-bold uppercase mb-4">Bombas de Agua Registradas</h2>
            <ul id="pumps-list" class="space-y-3">
                <li class="text-sm text-gray-500 text-center">Cargando sensores...</li>
            </ul>
        </div>
    </aside>

    <main class="flex-1 relative">
        <div id="map" class="w-full h-full z-10"></div>
        
        <div id="analytics-panel" class="absolute bottom-4 left-4 right-4 bg-white/95 backdrop-blur-sm p-4 rounded-xl shadow-2xl z-20 hidden border border-gray-200 transition-all duration-300">
            
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 id="panel-title" class="text-lg font-bold text-gray-800">Cargando Análisis...</h3>
                    <p id="alert-reason-text" class="hidden text-sm font-bold text-red-600 mt-1 flex items-center gap-2">
                        <span class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></span>
                        <span id="alert-reason-message">Motivo de la alerta...</span>
                    </p>
                </div>
                <button onclick="cerrarPanel()" class="text-gray-500 hover:text-red-600 bg-gray-100 hover:bg-red-50 rounded-lg px-3 py-1 font-bold text-sm transition self-start">X Cerrar</button>
            </div>
            
            <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-sm w-full mb-3" style="height: 180px;">
                <canvas id="chartGeneral"></canvas>
            </div>

            <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-sm w-full" style="height: 140px;">
                <canvas id="chartPPM"></canvas>
            </div>

        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/map.js') }}"></script>
</body>
</html>