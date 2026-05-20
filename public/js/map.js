// 1. Configuración del Mapa
const map = L.map('map', { zoomControl: false }).setView([25.8690, -97.5027], 13);
L.control.zoom({ position: 'topright' }).addTo(map);

L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    maxZoom: 19
}).addTo(map);

// Variables Globales
let idSeleccionado = null; // NUEVO: Rastrea qué bomba está seleccionada
let marcadores = {}; 
let chartInstancias = {}; 
let bombaSeleccionadaId = null;
let graficaGeneral = null; 
let bombasGlobal = [];

// 2. Función para crear íconos dinámicos con borde
function crearIcono(status, isSelected) {
    const borde = isSelected ? 'border-gray-900 border-[3px]' : 'border-white border-2';
    
    if (status === 'normal') {
        return L.divIcon({
            className: 'custom-leaflet-icon',
            html: `<div class="w-5 h-5 bg-green-500 rounded-full ${borde} shadow-[0_0_12px_rgba(34,197,94,0.8)]"></div>`,
            iconSize: [20, 20], iconAnchor: [10, 10]
        });
    } else {
        return L.divIcon({
            className: 'custom-leaflet-icon',
            html: `<div class="w-6 h-6 bg-red-500 rounded-full ${borde} shadow-[0_0_15px_rgba(239,68,68,0.9)] animate-pulse"></div>`,
            iconSize: [24, 24], iconAnchor: [12, 12]
        });
    }
}

// 3. Fetch de datos desde la API
async function cargarDatos() {
    try {
        const res = await fetch('/api/bombas');
        const bombas = await res.json();
        
        console.log("Datos de la BD:", bombas); 
        
        renderizarInterfaz(bombas);
    } catch (error) {
        console.error("Error consultando API", error);
    }
}

// 4. Renderizar Interfaz
function renderizarInterfaz(bombas) {
    const listContainer = document.getElementById('pumps-list'); 
    listContainer.innerHTML = '';

    bombas.forEach(bomba => {
        if (!bomba.lat || !bomba.lng) return;

        // Obtenemos el último registro
        const ultimaLectura = (bomba.lecturas && bomba.lecturas.length > 0) ? bomba.lecturas[0] : null;
        let flujoActual = 0; 
        let status = 'normal'; 

        // Evaluamos el último registro en tiempo real
        if (ultimaLectura) {
            flujoActual = ultimaLectura.flujo || 0;
            
            if (
                ultimaLectura.flujo <= 15 ||     
                ultimaLectura.turb > 3.0 ||      
                (ultimaLectura.ph < 6.5 || ultimaLectura.ph > 8.5) || 
                ultimaLectura.ppm > 1000 ||      
                (ultimaLectura.temp >= 20 && ultimaLectura.temp <= 45) || 
                (ultimaLectura.presion < 20 || ultimaLectura.presion > 75)
            ) {
                status = 'alert';
            }
        }

        // Saber si esta bomba está seleccionada
        let isSelected = (bomba.id === idSeleccionado);

        // Actualizamos o creamos pin en el mapa
        if (!marcadores[bomba.id]) {
            marcadores[bomba.id] = L.marker([bomba.lat, bomba.lng]).addTo(map);
            
            marcadores[bomba.id].on('click', () => {
                idSeleccionado = bomba.id; // Marcamos como seleccionada
                renderizarInterfaz(bombas); // Re-dibujamos para aplicar el borde
                mostrarGraficas(bomba);
            });
        }
        
        // Aplicamos el ícono generado dinámicamente
        marcadores[bomba.id].setIcon(crearIcono(status, isSelected));

        // Crear elemento en la lista lateral
        const li = document.createElement('li');
        
        // Clases dinámicas para la lista (se oscurece si está seleccionada)
        let clasesBase = `p-3 rounded-lg border cursor-pointer transition `;
        if (isSelected) {
            clasesBase += `bg-gray-100 border-gray-400 shadow-inner`; 
        } else {
            clasesBase += status === 'normal' ? 'bg-white hover:bg-gray-50' : 'bg-red-50 border-red-200'; 
        }
        li.className = clasesBase;
        
        li.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full ${status === 'normal' ? 'bg-green-500' : 'bg-red-500 animate-pulse'}"></div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">${bomba.name || 'Sin Nombre'}</p>
                </div>
            </div>
        `;
        li.onclick = () => {
            bombaSeleccionadaId = bomba.id;
            mostrarGraficas(bomba);
        };
        listContainer.appendChild(li);
    });
}

// 5. Lógica de las Gráficas
function mostrarGraficas(bomba) {
    const panel = document.getElementById('analytics-panel');
    if (!panel) return;
    
    panel.classList.remove('hidden');
    document.getElementById('panel-title').innerText = `Análisis del Sensor: ${bomba.name || 'Sensor'}`;

    // NUEVA LÓGICA: Diagnóstico de Alertas
    const alertTextContainer = document.getElementById('alert-reason-text');
    const alertMessage = document.getElementById('alert-reason-message');

    const ultimaLectura = (bomba.lecturas && bomba.lecturas.length > 0) ? bomba.lecturas[0] : null;

    if (ultimaLectura) {
        let motivos = []; 
        
        if (ultimaLectura.flujo <= 15) motivos.push(`Caudal crítico (${ultimaLectura.flujo} L/s)`);
        if (ultimaLectura.turb > 3.0) motivos.push(`Agua turbia (${ultimaLectura.turb} NTU)`);
        if (ultimaLectura.ph < 6.5 || ultimaLectura.ph > 8.5) motivos.push(`pH fuera de norma (${ultimaLectura.ph})`);
        if (ultimaLectura.ppm > 1000) motivos.push(`Exceso de sólidos (${ultimaLectura.ppm} PPM)`);
        if (ultimaLectura.temp >= 20 && ultimaLectura.temp <= 45) motivos.push(`Temp anómala (${ultimaLectura.temp}°C)`);
        
        if (ultimaLectura.presion < 20) motivos.push(`Baja presión (${ultimaLectura.presion} PSI)`);
        else if (ultimaLectura.presion > 75) motivos.push(`Sobrepresión (${ultimaLectura.presion} PSI)`);

        if (motivos.length > 0) {
            alertTextContainer.classList.remove('hidden');
            // Quitamos la palabra "ALERTA DETECTADA" para que sea más directo
            alertMessage.innerText = motivos.join(" | "); 
        } else {
            alertTextContainer.classList.add('hidden');
        }
    } else {
        alertTextContainer.classList.add('hidden');
    }

    // Preparación de datos para la gráfica
    let historial = (bomba.lecturas || []).slice().reverse();

    const labels = historial.map(r => {
        if (!r.created_at) return '00:00';
        return new Date(r.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    });

    if (graficaGeneral) {
        graficaGeneral.destroy();
    }

    const canvas = document.getElementById('chartGeneral');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    
    graficaGeneral = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'PPM', data: historial.map(r => r.ppm || 0), borderColor: '#f59e0b', yAxisID: 'y', tension: 0.3, borderWidth: 2 },
                { label: 'Flujo (L/s)', data: historial.map(r => r.flujo || 0), borderColor: '#3b82f6', yAxisID: 'y', tension: 0.3, borderWidth: 2 },
                { label: 'Presión (PSI)', data: historial.map(r => r.presion || 0), borderColor: '#8b5cf6', yAxisID: 'y', tension: 0.3, borderWidth: 2 },
                
                { label: 'Temperatura (°C)', data: historial.map(r => r.temp || 0), borderColor: '#ef4444', yAxisID: 'y1', tension: 0.3, borderWidth: 2 },
                { label: 'pH', data: historial.map(r => r.ph || 0), borderColor: '#10b981', yAxisID: 'y1', tension: 0.3, borderWidth: 2 },
                { label: 'Turbidez (NTU)', data: historial.map(r => r.turb || 0), borderColor: '#64748b', yAxisID: 'y1', tension: 0.3, borderWidth: 2 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } },
            scales: {
                x: { display: true },
                y: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Valores' } },
            }
        }
    });
}

async function cargarDatosGrafica() {
    await cargarDatos();
    // Si hay una bomba seleccionada, buscarla en los datos más recientes y actualizar la gráfica
    if (bombaSeleccionadaId && bombasGlobal.length > 0) {
        const bomba = bombasGlobal.find(b => b.id === bombaSeleccionadaId);
        if (bomba) {
            mostrarGraficas(bomba);
        }
    }
}

async function cargarDatos() {
    try {
        const res = await fetch('/api/bombas');
        const bombas = await res.json();
        bombasGlobal = bombas;
        console.log("Datos de la BD:", bombas); 
        renderizarInterfaz(bombas);
    } catch (error) {
        console.error("Error consultando API", error);
    }
}

cargarDatosGrafica();
setInterval(cargarDatosGrafica, 5000);
function cerrarPanel() {
    document.getElementById('analytics-panel').classList.add('hidden');
    idSeleccionado = null; // Limpiamos la selección
    cargarDatos(); // Forzamos un re-dibujado rápido para quitar el borde negro
}

// Ejecutar
cargarDatos();

setInterval(cargarDatos, 5000);