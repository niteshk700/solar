/**
 * SOLAR-POWERED ESP8266 + BME280 WEATHER STATION FIRMWARE
 * WITH CAPTIVE PORTAL WIFI AUTO-CONFIGURATION
 * 
 * Hardware Wiring:
 * - BME280 I2C connection:
 *     VCC  -> 3.3V
 *     GND  -> GND
 *     SCL  -> D1 (GPIO 5)
 *     SDA  -> D2 (GPIO 4)
 * - Battery Monitoring (Analog Pin A0):
 *     To measure a 18650 Li-Ion battery (3.0V - 4.2V) safely on the A0 pin:
 *     Wiring: Battery (+) -> [ 220k Ohm Resistor ] -> A0 -> [ 100k Ohm Resistor ] -> GND
 *     This scales 4.2V down to ~1.31V, which fits NodeMCU's board divider perfectly.
 * - Deep Sleep Support:
 *     GPIO16 (D0) must be physically connected to the RST pin.
 *     Warning: Disconnect this jumper wire during firmware flashing/uploading!
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <ArduinoJson.h>

// Required libraries for WiFi Captive Portal Config
#include <DNSServer.h>
#include <ESP8266WebServer.h>
#include <WiFiManager.h> // Library: "WiFiManager" by tzapu

// ================= USER CONFIGURATION =================
// Device Registration Details (Must match Laravel Device Management)
const char* DEVICE_ID     = "solar_data_collector_v1";
const char* API_KEY       = "SolarDataCollectionV1";

// Laravel Server API Endpoint
const char* SERVER_URL    = "https://solar.yourdev.in/api/weather-data";

// Deep Sleep Duration: 5 minutes (in microseconds)
const uint64_t SLEEP_DURATION_US = 5 * 60 * 1000000ULL; 

// Captive Portal Hotspot configuration
const char* PORTAL_AP_SSID = "Solar_Weather_Setup"; // Open SSID name
const int PORTAL_TIMEOUT_SEC = 180; // 3-minute timeout to prevent battery drain
// ======================================================

Adafruit_BME280 bme; // I2C Mode

void setup() {
  Serial.begin(115200);
  delay(10);
  Serial.println("\n--- Solar Weather Station Waking Up ---");

  // 1. Initialize BME280 Sensor
  if (!bme.begin(0x76)) { // Alternate address is 0x77
    Serial.println("Could not find a valid BME280 sensor, check wiring!");
    // We will still proceed to transmit battery status even if sensor fails
  }

  // 2. Read Sensors immediately (minimize active power draw before radio starts)
  float temperature = bme.readTemperature();
  float humidity    = bme.readHumidity();
  float pressure    = bme.readPressure() / 100.0F; // Convert Pa to hPa

  // Read Battery Voltage (A0 Pin)
  int rawADC = analogRead(A0);
  float batteryVoltage = (rawADC / 1023.0) * 4.2; // Typical resistor calibration factor
  
  Serial.print("Sensor Temp: "); Serial.print(temperature); Serial.println(" C");
  Serial.print("Sensor Hum:  "); Serial.print(humidity); Serial.println(" %");
  Serial.print("Sensor Pres: "); Serial.print(pressure); Serial.println(" hPa");
  Serial.print("Battery V:   "); Serial.print(batteryVoltage); Serial.println(" V");

  // 3. Initialize WiFi Captive Portal Manager
  WiFiManager wifiManager;

  // Design Customizations for Config Portal Webpage
  wifiManager.setAPStaticIPConfig(IPAddress(192, 168, 4, 1), IPAddress(192, 168, 4, 1), IPAddress(255, 255, 255, 0));
  
  // Power-optimization: portal timeout. If no user connects/saves credentials within 3 mins,
  // sleep the station to avoid infinite power consumption.
  wifiManager.setConfigPortalTimeout(PORTAL_TIMEOUT_SEC);

  Serial.println("Attempting to connect to stored WiFi credentials...");
  
  // If stored WiFi connection fails, WiFiManager spins up the Captive Portal Hotspot
  if (!wifiManager.autoConnect(PORTAL_AP_SSID)) {
    Serial.println("Connection failed or configuration portal timed out. Entering deep sleep...");
    enterDeepSleep();
  }

  // Once connected to the local WiFi, the access point hotspot is automatically turned off.
  Serial.println("\nWiFi Connected successfully!");
  long rssi = WiFi.RSSI();
  Serial.print("RSSI Signal Strength: "); Serial.print(rssi); Serial.println(" dBm");

  // 4. Construct JSON Payload
  StaticJsonDocument<256> doc;
  doc["api_key"]     = API_KEY;
  doc["device_id"]   = DEVICE_ID;
  
  if (isnan(temperature)) doc["temperature"] = 0.0;
  else doc["temperature"] = round(temperature * 10) / 10.0; // 1 decimal place
  
  if (isnan(humidity)) doc["humidity"] = 0.0;
  else doc["humidity"] = round(humidity * 10) / 10.0;
  
  if (isnan(pressure)) doc["pressure"] = 0.0;
  else doc["pressure"] = round(pressure * 10) / 10.0;
  
  doc["battery"]     = round(batteryVoltage * 100) / 100.0; // 2 decimal places
  doc["rssi"]        = rssi;

  String jsonPayload;
  serializeJson(doc, jsonPayload);
  Serial.print("JSON Payload: "); Serial.println(jsonPayload);

  // 5. Send HTTP POST to Laravel Platform
  WiFiClientSecure client;
  client.setInsecure(); // Secure SSL connections without certificate strict checking
  
  HTTPClient http;
  Serial.print("Transmitting telemetry to: "); Serial.println(SERVER_URL);
  
  int retryCount = 0;
  bool uploadSuccess = false;
  
  while (retryCount < 3 && !uploadSuccess) {
    if (http.begin(client, SERVER_URL)) {
      http.addHeader("Content-Type", "application/json");
      
      int httpCode = http.POST(jsonPayload);
      
      if (httpCode > 0) {
        Serial.print("HTTP Response Code: "); Serial.println(httpCode);
        String response = http.getString();
        Serial.print("Response: "); Serial.println(response);
        
        if (httpCode == 200) {
          uploadSuccess = true;
          Serial.println("Telemetry successfully received by Laravel!");
        }
      } else {
        Serial.print("HTTP Error: "); Serial.println(http.errorToString(httpCode).c_str());
      }
      http.end();
    }
    
    if (!uploadSuccess) {
      retryCount++;
      Serial.print("Upload failed, retrying in 2 seconds... (Try "); Serial.print(retryCount); Serial.println("/3)");
      delay(2000);
    }
  }

  // 6. Enter Deep Sleep
  enterDeepSleep();
}

void loop() {
  // Loop is never reached as device goes to sleep in setup()
}

void enterDeepSleep() {
  Serial.println("Powering down WiFi radio...");
  WiFi.disconnect(true);
  delay(1);
  
  Serial.print("Entering ultra-low power Deep Sleep for ");
  Serial.print(SLEEP_DURATION_US / 1000000ULL / 60);
  Serial.println(" minutes...");
  
  ESP.deepSleep(SLEEP_DURATION_US);
}
