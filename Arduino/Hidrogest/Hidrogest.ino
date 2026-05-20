#include <WiFi.h>
#include <HTTPClient.h>

// WiFi
const char* ssid = "Samsungs20fe";
const char* password = "dani1234";

// API / servidor
const char* serverUrl = "http://172.18.99.22:8000/api/sensor-data";

// ID del dispositivo
const int ID_DISPOSITIVO = 2;

// Tiempos
unsigned long ultimoEnvio = 0;
const unsigned long intervaloEnvio = 3000; // Enviar cada 3 segundos

float randomFloat(float minValue, float maxValue) {
  long r = random(0, 1000000);
  return minValue + ((float)r / 1000000.0) * (maxValue - minValue);
}

void conectarWiFi() {
  Serial.print("Conectando a WiFi: ");
  Serial.println(ssid);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int intentos = 0;

  while (WiFi.status() != WL_CONNECTED && intentos < 20) {
    delay(500);
    Serial.print(".");
    intentos++;
  }

  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("WiFi conectado correctamente");
    Serial.print("IP del ESP32: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("No se pudo conectar al WiFi");
  }
}

void asegurarWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;

  Serial.println("WiFi desconectado. Reintentando...");
  WiFi.disconnect();
  WiFi.begin(ssid, password);

  int intentos = 0;
  while (WiFi.status() != WL_CONNECTED && intentos < 10) {
    delay(500);
    Serial.print(".");
    intentos++;
  }

  Serial.println();
}

String generarJSON() {
  // Datos simulados
  float ph = randomFloat(6.5, 8.5);
  float turbidez = randomFloat(0.0, 5.0);       // NTU
  float tds = randomFloat(100.0, 500.0);        // PPM
  float temperatura = randomFloat(20.0, 32.0);  // °C
  float flujo = randomFloat(0.0, 12.0);         // L/min
  float presion = randomFloat(20.0, 80.0);      // PSI

  String json = "{";
  json += "\"bomba_id\":";
  json += ID_DISPOSITIVO;
  json += ",";
  json += "\"ph\":";
  json += String(ph, 2);
  json += ",";
  json += "\"turb\":";
  json += String(turbidez, 2);
  json += ",";
  json += "\"flujo\":";
  json += String(flujo, 2);
  json += ",";
  json += "\"presion\":";
  json += String(presion, 2);
  json += ",";
  json += "\"temp\":";
  json += String(temperatura, 2);
  json += ",";
  json += "\"ppm\":\"";
  json += String(tds, 2);
  json += "\"";
  json += "}";

  return json;
}

void enviarPOST(String jsonPayload) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("No hay WiFi. No se pudo enviar.");
    return;
  }

  HTTPClient http;

  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");

  Serial.println("Enviando JSON:");
  Serial.println(jsonPayload);

  int codigoRespuesta = http.POST(jsonPayload);

  Serial.print("Código HTTP: ");
  Serial.println(codigoRespuesta);

  if (codigoRespuesta > 0) {
    String respuesta = http.getString();
    Serial.println("Respuesta del servidor:");
    Serial.println(respuesta);
  } else {
    Serial.println("Error al enviar POST");
  }

  http.end();
}

void setup() {
  Serial.begin(115200);
  delay(1000);

  randomSeed(analogRead(34));

  conectarWiFi();
}

void loop() {
  asegurarWiFi();

  if (millis() - ultimoEnvio >= intervaloEnvio) {
    ultimoEnvio = millis();

    String json = generarJSON();
    enviarPOST(json);
  }
}