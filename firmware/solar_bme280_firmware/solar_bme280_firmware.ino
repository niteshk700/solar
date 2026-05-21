/**
 * ==========================================================================
 * SOLAR-POWERED ESP8266 + BME280 + DHT11 DUAL-SENSOR WEATHER PLATFORM sketch
 * WITH INTEGRATED COMPONENT-LEVEL FAILOVER REDUNDANCY & DUAL DIAGNOSTICS
 * ==========================================================================
 * 
 * 🛠️ DUAL-SENSOR SOLDERING & WIRING CONNECTION SCHEMATIC
 * --------------------------------------------------------------------------
 * 
 * 1. BME280 Environmental Sensor (I2C Mode - Primary Sensor)
 *    [BME280 Pin]      --->  [NodeMCU Pin]   --->  [Description]
 *    VCC               --->  3V3             --->  Power Supply (3.3V)
 *    GND               --->  GND             --->  System Ground
 *    SCL               --->  D6 (GPIO 12)    --->  I2C Clock Line
 *    SDA               --->  D5 (GPIO 14)    --->  I2C Data Line
 * 
 * 2. DHT11 Environmental Sensor (Single-bus - Secondary Backup Sensor)
 *    [DHT11 Pin]       --->  [NodeMCU Pin]   --->  [Description]
 *    VCC               --->  3V3             --->  Power Supply (3.3V)
 *    GND               --->  GND             --->  System Ground
 *    DATA              --->  D7 (GPIO 13)    --->  Digital Sensor Data Bus
 * 
 * 3. TP4056 Solar Charger Module
 *    [TP4056 Pin]      --->  [Connection]    --->  [Description]
 *    IN+               --->  Solar Panel (+) --->  Positive Input from 3W Solar Panel
 *    IN-               --->  Solar Panel (-) --->  Negative Input from 3W Solar Panel
 *    BAT+              --->  18650 Battery(+)--->  Positive Battery terminal (3.0V - 4.2V)
 *    BAT-              --->  18650 Battery(-)--->  Negative Battery terminal
 *    OUT+              --->  NodeMCU VIN     --->  System Power Supply (Filtered)
 *    OUT-              --->  NodeMCU GND     --->  System Common Ground
 *    CHRG (Pin 7 LED)  --->  NodeMCU D3      --->  Low when actively Charging (GPIO 0)
 *    STDBY (Pin 6 LED) --->  NodeMCU D4      --->  Low when battery is fully Charged (GPIO 2)
 * 
 * 4. 18650 Battery Monitoring Divider Circuit (A0 Pin)
 *    To read high battery voltages (3.0V - 4.2V) on the analog input safely:
 *    [Battery Positive (+)] -> [ 220k Ohm Resistor ] -> A0 -> [ 100k Ohm Resistor ] -> [Common GND (-)]
 *    - This scales a max battery voltage of 4.2V down to ~1.31V.
 * 
 * 5. Deep Sleep Hardware Wakeup Jumper
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
#include <ArduinoJson.h>

// Required libraries for WiFi Captive Portal Config
#include <DNSServer.h>
#include <ESP8266WebServer.h>
#include <WiFiManager.h> // Library: "WiFiManager" by tzapu

// Include both sensor libraries concurrently
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <DHT.h>

// ================= USER CONFIGURATION =================
// Device Registration Details (Must match Laravel Device Management)
const char* DEVICE_ID     = "solar_data_collector_v1";
const char* API_KEY       = "SolarDataCollectionV1";

// Laravel Server API Endpoint
const char* SERVER_URL    = "https://solar.yourdev.in/api/weather-data";

// Deep Sleep Duration: 10 seconds (for rapid bench-testing and debugging)
const uint64_t SLEEP_DURATION_US = 10 * 1000000ULL; 

// Captive Portal Hotspot configuration
const char* PORTAL_AP_SSID = "Solar_Weather_Setup"; // Open SSID name
const int PORTAL_TIMEOUT_SEC = 180; // 3-minute timeout to prevent battery drain

// TP4056 Charger Digital Monitoring Pins (Moved to D3 and D4 to make room for I2C)
const int PIN_TP4056_CHRG  = 0;  // D3 on NodeMCU (GPIO 0)
const int PIN_TP4056_STDBY = 2;  // D4 on NodeMCU (GPIO 2)
// ======================================================

// Instantiate sensor drivers globally
Adafruit_BME280 bme; // I2C Mode
#define DHTPIN 13    // D7 on NodeMCU is GPIO 13 (Strapping-safe pin)
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

bool bmeConnected = false;
bool dhtConnected = false;

void setup() {
  Serial.begin(115200);
  delay(10);
  Serial.println("\n--- Solar Weather Station Waking Up ---");

  // 1. Initialize TP4056 Charger Pins as digital inputs with pullup resistors
  pinMode(PIN_TP4056_CHRG, INPUT_PULLUP);
  pinMode(PIN_TP4056_STDBY, INPUT_PULLUP);

  // 2. Initialize BME280 Environmental Sensor (I2C Primary)
  Serial.println("Probing I2C bus for BME280 on pins D5 (SDA) and D6 (SCL)...");
  Wire.begin(14, 12); // SDA = GPIO 14 (D5), SCL = GPIO 12 (D6)
  if (bme.begin(0x76) || bme.begin(0x77)) {
    bmeConnected = true;
    Serial.println("BME280 Environmental Sensor successfully initialized!");
  } else {
    Serial.println("WARNING: BME280 Sensor not found, check soldering!");
  }

  // 3. Initialize DHT11 Sensor (Backup / Secondary)
  Serial.println("Initializing DHT11 on pin D7...");
  dht.begin();
  
  // Try reading DHT11 to check if physically present
  float checkT = dht.readTemperature();
  if (!isnan(checkT)) {
    dhtConnected = true;
    Serial.println("DHT11 Environmental Sensor successfully initialized!");
  } else {
    Serial.println("WARNING: DHT11 Sensor not found, check wiring!");
  }

  // 4. Read Meteorological Metrics (Executing dynamic BME280 ➔ DHT11 Failover Logic)
  float temperature = NAN;
  float humidity    = NAN;
  float pressure    = NAN;

  if (bmeConnected) {
    // Primary reading from high-accuracy BME280 sensor
    temperature = bme.readTemperature();
    humidity    = bme.readHumidity();
    pressure    = bme.readPressure() / 100.0F; // Convert Pa to hPa
    Serial.println("Environmental Data sourced from: BME280 (Primary)");
  } else if (dhtConnected) {
    // Failover reading from secondary DHT11 sensor
    temperature = dht.readTemperature();
    humidity    = dht.readHumidity();
    pressure    = NAN; // DHT11 does not measure pressure
    Serial.println("Environmental Data sourced from: DHT11 (Backup Failover)");
  } else {
    Serial.println("CRITICAL: Both sensors are offline! Transmitting empty metrics.");
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

  Serial.println("\n[BOOT] Diagnostics read successfully. Preparing radio transmitter...");

  // 5. Initialize WiFi Captive Portal Manager
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

  // 6. Construct JSON Payload
  StaticJsonDocument<384> doc;
  doc["api_key"]      = API_KEY;
  doc["device_id"]    = DEVICE_ID;
  
  if (!isnan(temperature) && !isnan(humidity)) {
    doc["temperature"] = round(temperature * 10) / 10.0; // 1 decimal place
    doc["humidity"]    = round(humidity * 10) / 10.0;
  } else {
    doc["temperature"] = nullptr;
    doc["humidity"]    = nullptr;
  }
  
  if (!isnan(pressure)) {
    doc["pressure"]    = round(pressure * 10) / 10.0;
  } else {
    doc["pressure"]    = nullptr;
  }
  
  doc["bme_status"]    = bmeConnected;
  doc["dht_status"]    = dhtConnected;
  doc["battery"]      = round(batteryVoltage * 100) / 100.0; // 2 decimal places
  doc["rssi"]         = rssi;
  doc["solar_status"] = solarStatus;

  String jsonPayload;
  serializeJson(doc, jsonPayload);
  Serial.print("JSON Payload: "); Serial.println(jsonPayload);

  // 7. Send HTTP POST to Laravel Platform (with Self-Healing HTTP Fallback)
  int retryCount = 0;
  bool uploadSuccess = false;
  
  while (retryCount < 3 && !uploadSuccess) {
    if (retryCount == 0) {
      Serial.println("Attempting secure HTTPS upload...");
      WiFiClientSecure secureClient;
      secureClient.setInsecure(); // Bypass TLS certificate checking
      
      HTTPClient http;
      if (http.begin(secureClient, "https://solar.yourdev.in/api/weather-data")) {
        http.addHeader("Content-Type", "application/json");
        int httpCode = http.POST(jsonPayload);
        
        if (httpCode > 0) {
          Serial.print("HTTPS Response Code: "); Serial.println(httpCode);
          String response = http.getString();
          Serial.print("Response: "); Serial.println(response);
          if (httpCode == 200) {
            uploadSuccess = true;
            Serial.println("Telemetry successfully received by Laravel!");
          }
        } else {
          Serial.print("HTTPS Connection Error: "); Serial.println(http.errorToString(httpCode).c_str());
        }
        http.end();
      } else {
        Serial.println("HTTPS initialization failed (out of heap memory).");
      }
    } else {
      Serial.println("Falling back to plain HTTP to bypass TLS memory constraints...");
      WiFiClient plainClient;
      
      HTTPClient http;
      if (http.begin(plainClient, "http://solar.yourdev.in/api/weather-data")) {
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
          Serial.print("HTTP Connection Error: "); Serial.println(http.errorToString(httpCode).c_str());
        }
        http.end();
      } else {
        Serial.println("HTTP initialization failed.");
      }
    }
    
    if (!uploadSuccess) {
      retryCount++;
      Serial.print("Upload failed, retrying in 2 seconds... (Try "); Serial.print(retryCount); Serial.println("/3)");
      delay(2000);
    }
  }

  // 8. Output dynamic console diagnostic dashboard
  Serial.println("\n================================================");
  Serial.println("         SOLAR WEATHER STATION DIAGNOSTICS      ");
  Serial.println("================================================");
  Serial.print("Device ID       : "); Serial.println(DEVICE_ID);
  Serial.print("BME280 Sensor   : "); Serial.println(bmeConnected ? "CONNECTED (OK)" : "ERROR (OFFLINE / CHECK I2C WIRING)");
  Serial.print("DHT11 Sensor    : "); Serial.println(dhtConnected ? "CONNECTED (OK)" : "ERROR (OFFLINE / CHECK WIRING)");
  Serial.println("------------------------------------------------");
  if (bmeConnected || dhtConnected) {
    Serial.print("Temperature     : "); Serial.print(temperature, 1); Serial.println(" °C");
    Serial.print("Humidity        : "); Serial.print(humidity, 1); Serial.println(" %");
    if (!isnan(pressure)) {
      Serial.print("Pressure        : "); Serial.print(pressure, 1); Serial.println(" hPa");
    } else {
      Serial.println("Pressure        : [NOT SUPPORTED BY DHT11]");
    }
  } else {
    Serial.println("Meteorology     : [SENSOR OFFLINE - NULL TRANSMITTED]");
  }
  Serial.println("------------------------------------------------");
  int batPct = 0;
  if (batteryVoltage >= 4.2) batPct = 100;
  else if (batteryVoltage <= 3.5) batPct = 0;
  else batPct = round(((batteryVoltage - 3.5) / 0.7) * 100);
  
  Serial.print("Battery Voltage : "); Serial.print(batteryVoltage, 2); Serial.print(" V ("); Serial.print(batPct); Serial.println("%)");
  Serial.print("Solar Charger   : "); 
  if (solarStatus == "charging") Serial.println("CHARGING (Sunlight Active)");
  else if (solarStatus == "full") Serial.println("FULLY CHARGED (Battery Topped Off)");
  else Serial.println("IDLE (Low Light / Solar Offline)");
  Serial.println("------------------------------------------------");
  Serial.print("WiFi Network    : "); Serial.println(WiFi.SSID());
  Serial.print("Signal Strength : "); Serial.print(WiFi.RSSI()); Serial.println(" dBm");
  Serial.print("Server Telemetry: "); Serial.println(uploadSuccess ? "SUCCESS (HTTP 200)" : "FAILED (CHECK INTERNET/HOSTINGER)");
  Serial.println("================================================\n");

  // 9. Enter Deep Sleep
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
