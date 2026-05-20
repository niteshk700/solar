/**
 * ==========================================================================
 * SOLAR-POWERED ESP8266 + BME280 + TP4056 WEATHER STATION FIRMWARE
 * WITH REAL-TIME COMPONENT DIAGNOSTICS & CAPTIVE PORTAL CONFIGURATION
 * ==========================================================================
 * 
 * 🛠️ DETAILED SOLDERING & WIRING CONNECTION SCHEMATIC
 * --------------------------------------------------------------------------
 * 
 * 1. BME280 Sensor (I2C)
 *    [BME280 Pin]      --->  [NodeMCU Pin]   --->  [Description]
 *    VCC               --->  3V3             --->  Power Supply (3.3V)
 *    GND               --->  GND             --->  System Ground
 *    SCL               --->  D1 (GPIO 5)     --->  I2C Clock Line
 *    SDA               --->  D2 (GPIO 4)     --->  I2C Data Line
 * 
 * 2. TP4056 Solar Charger Module
 *    [TP4056 Pin]      --->  [Connection]    --->  [Description]
 *    IN+               --->  Solar Panel (+) --->  Positive Input from 3W Solar Panel
 *    IN-               --->  Solar Panel (-) --->  Negative Input from 3W Solar Panel
 *    BAT+              --->  18650 Battery(+)--->  Positive Battery terminal (3.0V - 4.2V)
 *    BAT-              --->  18650 Battery(-)--->  Negative Battery terminal
 *    OUT+              --->  NodeMCU VIN     --->  System Power Supply (Filtered)
 *    OUT-              --->  NodeMCU GND     --->  System Common Ground
 *    CHRG (Pin 7 LED)  --->  NodeMCU D5      --->  Low when actively Charging (GPIO 14)
 *    STDBY (Pin 6 LED) --->  NodeMCU D6      --->  Low when battery is fully Charged (GPIO 12)
 * 
 * 3. 18650 Battery Monitoring Divider Circuit (A0 Pin)
 *    To read high battery voltages (3.0V - 4.2V) on the analog input safely:
 *    [Battery Positive (+)] -> [ 220k Ohm Resistor ] -> A0 -> [ 100k Ohm Resistor ] -> [Common GND (-)]
 *    - This scales a max battery voltage of 4.2V down to ~1.31V.
 *    - The NodeMCU board has an internal 220k/100k divider, mapping this to the raw ESP8266 ADC.
 * 
 * 4. Deep Sleep Hardware Wakeup Jumper
 *    [NodeMCU Pin]      --->  [NodeMCU Pin]   --->  [Description]
 *    D0 (GPIO 16)      --->  RST             --->  Physically jumpered to wake up ESP8266
 *    ⚠️ WARNING: Disconnect this jumper wire while flashing/uploading code via USB!
 * 
 * ==========================================================================
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

// TP4056 Charger Digital Monitoring Pins
const int PIN_TP4056_CHRG  = 14; // D5 on NodeMCU (GPIO 14)
const int PIN_TP4056_STDBY = 12; // D6 on NodeMCU (GPIO 12)
// ======================================================

Adafruit_BME280 bme; // I2C Mode
bool bmeConnected = false;

void setup() {
  Serial.begin(115200);
  delay(10);
  Serial.println("\n--- Solar Weather Station Waking Up ---");

  // 1. Initialize TP4056 Charger Pins as digital inputs with pullup resistors
  pinMode(PIN_TP4056_CHRG, INPUT_PULLUP);
  pinMode(PIN_TP4056_STDBY, INPUT_PULLUP);

  // 2. Initialize BME280 Sensor
  if (bme.begin(0x76)) { // Alternate address is 0x77
    bmeConnected = true;
    Serial.println("BME280 Environmental Sensor successfully initialized!");
  } else {
    Serial.println("WARNING: Could not find a valid BME280 sensor, check soldering!");
    // We will still proceed to transmit battery status even if sensor fails
  }

  // 3. Read Sensors immediately (minimize active power draw before radio starts)
  float temperature = NAN;
  float humidity    = NAN;
  float pressure    = NAN;

  if (bmeConnected) {
    temperature = bme.readTemperature();
    humidity    = bme.readHumidity();
    pressure    = bme.readPressure() / 100.0F; // Convert Pa to hPa
  }

  // Read Battery Voltage (A0 Pin)
  int rawADC = analogRead(A0);
  float batteryVoltage = (rawADC / 1023.0) * 4.2; // Calibrated divider voltage factor
  
  // Read TP4056 charging module outputs
  bool isCharging = (digitalRead(PIN_TP4056_CHRG) == LOW);
  bool isFull     = (digitalRead(PIN_TP4056_STDBY) == LOW);
  
  String solarStatus = "idle";
  if (isCharging) {
    solarStatus = "charging";
  } else if (isFull) {
    solarStatus = "full";
  }

  Serial.println("\n--- Diagnostic Telemetry Snapshot ---");
  if (bmeConnected) {
    Serial.print("Temp:      "); Serial.print(temperature); Serial.println(" C");
    Serial.print("Hum:       "); Serial.print(humidity); Serial.println(" %");
    Serial.print("Pres:      "); Serial.print(pressure); Serial.println(" hPa");
  } else {
    Serial.println("Temp/Hum/Pres: [SENSOR OFFLINE]");
  }
  Serial.print("Battery V: "); Serial.print(batteryVoltage); Serial.println(" V");
  Serial.print("Solar Chg: "); Serial.println(solarStatus);

  // 4. Initialize WiFi Captive Portal Manager
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

  // 5. Construct JSON Payload
  StaticJsonDocument<384> doc;
  doc["api_key"]      = API_KEY;
  doc["device_id"]    = DEVICE_ID;
  
  if (bmeConnected && !isnan(temperature) && !isnan(humidity) && !isnan(pressure)) {
    doc["temperature"] = round(temperature * 10) / 10.0; // 1 decimal place
    doc["humidity"]    = round(humidity * 10) / 10.0;
    doc["pressure"]    = round(pressure * 10) / 10.0;
    doc["bme_status"]  = true;
  } else {
    doc["temperature"] = nullptr;
    doc["humidity"]    = nullptr;
    doc["pressure"]    = nullptr;
    doc["bme_status"]  = false;
  }
  
  doc["battery"]      = round(batteryVoltage * 100) / 100.0; // 2 decimal places
  doc["rssi"]         = rssi;
  doc["solar_status"] = solarStatus;

  String jsonPayload;
  serializeJson(doc, jsonPayload);
  Serial.print("JSON Payload: "); Serial.println(jsonPayload);

  // 6. Send HTTP POST to Laravel Platform
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

  // 7. Enter Deep Sleep
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
