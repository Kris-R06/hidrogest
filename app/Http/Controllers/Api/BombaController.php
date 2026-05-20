<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bomba;
use App\Models\LecturasBomba;
use Illuminate\Support\Facades\Http; // NECESARIO PARA TELEGRAM
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BombaController extends Controller
{
    public function index()
    {
        // Traemos todas las bombas con sus últimas 15 lecturas (historial para la gráfica)
        $bombas = Bomba::with(['readings' => function($query) {
            $query->latest()->take(15); 
        }])->get();

        // Laravel convierte automáticamente esto a formato JSON para que el mapa lo entienda
        return response()->json($bombas);
    }

    public function guardarLectura(Request $request)
    {
        // 1. Guardamos la lectura
        $lectura = LecturasBomba::create($request->all());

        // 2. Traemos la bomba
        $bomba = Bomba::find($request->bomba_id);

        // 3. Variables para evaluar
        $ph = $request->ph;
        $turbidez = $request->turb; // Asegúrate que coincida con el nombre que manda el ESP32
        $flujo = $request->flujo;
        $presion = $request->presion;
        $temp = $request->temp;
        $ppm = $request->ppm;

        // 4. Lógica de Alertas
        $estado = 'normal';
        $motivos = [];

        if ($flujo <= 15) $motivos[] = "Caudal crítico ($flujo L/s)";
        if ($presion < 20 || $presion > 75) $motivos[] = "Presión fuera de rango ($presion PSI)";
        if ($turbidez > 3.0) $motivos[] = "Agua turbia ($turbidez NTU)";
        if ($ph < 6.5 || $ph > 8.5) $motivos[] = "pH fuera de norma ($ph)";
        if ($ppm > 1000) $motivos[] = "Exceso de sólidos ($ppm PPM)";
        if ($temp > 20 && $temp < 45) $motivos[] = "Temperatura insegura ($temp °C)";

        // 5. ¿Hay alertas?
        if (count($motivos) > 0) {
            $estado = 'alert';

            // ARMAMOS EL MENSAJE PARA TELEGRAM
            $mensaje = "🚨 *ALERTA DE SISTEMA HÍDRICO* 🚨\n\n";
            $mensaje .= "📍 *Estación:* " . ($bomba->name ?? 'Desconocida') . "\n";
            $mensaje .= "⚠️ *Fallas:* " . implode(" | ", $motivos) . "\n";
            $mensaje .= "📅 *Fecha/Hora:* " . Carbon::now()->format('d/m/Y H:i:s');

            // ENVIAMOS EL MENSAJE
            $this->enviarNotificacionTelegram($mensaje);
        }

        // 6. Actualizamos estado (usamos 'status' en lugar de 'estado')
        $bomba->status = $estado;
        $bomba->save();

        return response()->json([
            'mensaje' => 'Datos procesados correctamente',
            'estado_bomba' => $estado
        ]);
    }

    private function enviarNotificacionTelegram($mensaje)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        // Agrega esto para ver si el código siquiera llega aquí
        Log::info("Intentando enviar mensaje a Telegram: " . $mensaje);

        if ($token && $chatId) {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $mensaje,
                'parse_mode' => 'Markdown'
            ]);
            
            // Agrega esto para ver qué respondió Telegram (si hubo error)
            Log::info("Respuesta de Telegram: " . $response->body());
        } else {
            Log::error("Faltan credenciales de Telegram en el .env");
        }
    }
}