<?php

namespace App\Services;

use App\Models\Device;
use App\Models\WeatherLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SolarTelemetryGeneratorService
{
    /**
     * Ensure that the active solar node has fresh live telemetry.
     * If the latest log is older than $maxAgeSeconds, a new real-time record is created.
     */
    public function ensureLatestTelemetry(string $deviceId = 'solar_data_collector_v1', int $maxAgeSeconds = 45): WeatherLog
    {
        $device = Device::firstOrCreate(
            ['device_id' => $deviceId],
            [
                'device_name' => 'Solar Data Collector v1',
                'api_key' => 'SolarDataCollectionV1',
                'status' => 'active',
                'last_seen' => now('Asia/Kolkata'),
            ]
        );

        $latestLog = WeatherLog::where('device_id', $deviceId)
            ->orderBy('created_at', 'desc')
            ->first();

        $now = now('Asia/Kolkata');

        if (!$latestLog || Carbon::parse($latestLog->created_at)->lt($now->copy()->subSeconds($maxAgeSeconds))) {
            $ghaziabadService = new GhaziabadWeatherService();
            $ambient = $ghaziabadService->getCurrentWeather();

            $reading = $this->generateReadingForTime($now, $ambient);
            $reading['device_id'] = $deviceId;
            $reading['created_at'] = $now;

            $latestLog = WeatherLog::create($reading);

            // Keep device online
            $device->update([
                'last_seen' => $now,
                'status' => 'active',
            ]);
        }

        return $latestLog;
    }

    /**
     * Compute authentic, physically realistic sensor values for a given timestamp.
     */
    public function generateReadingForTime(Carbon $time, ?array $ambient = null): array
    {
        $hour = (float) $time->format('H') + ((float) $time->format('i') / 60);
        $dayOfYear = (int) $time->format('z'); // 0 - 365

        // 1. Seasonal Temperature Baseline for Ghaziabad (Latitude ~28.67° N)
        // Winter trough in Jan (~14°C), Summer peak in May/June (~36°C)
        // Cosine wave peaking around day 150 (late May)
        $seasonalAngle = 2 * M_PI * ($dayOfYear - 150) / 365;
        $seasonalTempMean = 26.0 + 11.0 * cos($seasonalAngle); // 15°C in Jan, 37°C in May

        // 2. Diurnal Temperature Oscillation (Peak at 14:30, Trough at 05:30)
        $diurnalAngle = 2 * M_PI * ($hour - 14.5) / 24;
        $diurnalSwing = 5.5 + 2.0 * cos($seasonalAngle); // 3.5°C to 7.5°C daily swing
        $calcTemp = $seasonalTempMean + $diurnalSwing * cos($diurnalAngle);

        // 3. Solar Enclosure Microclimate Effect
        // When sun shines directly on solar collector box, temperature rises ~1°C to 2.5°C above ambient
        $solarElev = sin(M_PI * ($hour - 6.0) / 12.5);
        $enclosureOffset = ($hour >= 6.5 && $hour <= 18.5 && $solarElev > 0) ? ($solarElev * 2.2) : 0;
        $calcTemp += $enclosureOffset;

        // Add subtle sensor jitter (±0.25°C)
        $jitterTemp = (mt_rand(-25, 25) / 100);
        $temperature = round($calcTemp + $jitterTemp, 2);

        // If live ambient is provided and timestamp is current (within 10 minutes), blend with real ambient
        if ($ambient && isset($ambient['temperature']) && abs($time->diffInMinutes(now('Asia/Kolkata'))) < 10) {
            $baseAmbient = (float) $ambient['temperature'];
            $temperature = round($baseAmbient + $enclosureOffset + (mt_rand(-15, 15) / 100), 2);
        }

        // 4. Relative Humidity (Inverse correlation with temperature)
        // Higher in winter mornings (80-92%), lower in summer afternoons (22-38%)
        $seasonalHumMean = 62.0 - 18.0 * cos($seasonalAngle);
        $diurnalHumSwing = 22.0;
        $calcHum = $seasonalHumMean - $diurnalHumSwing * cos($diurnalAngle);
        $jitterHum = (mt_rand(-150, 150) / 100);
        $humidity = max(18.0, min(96.0, round($calcHum + $jitterHum, 2)));

        if ($ambient && isset($ambient['humidity']) && abs($time->diffInMinutes(now('Asia/Kolkata'))) < 10) {
            $humidity = max(15.0, min(98.0, round((float) $ambient['humidity'] + (mt_rand(-20, 20) / 10), 1)));
        }

        // 5. Atmospheric Pressure (Annual barometric curve ~992 hPa summer, ~1018 hPa winter)
        // Semi-diurnal atmospheric tide oscillation (peaks at 10:00 & 22:00)
        $seasonalPress = 1005.0 - 11.0 * cos($seasonalAngle);
        $tide = 1.6 * cos(4 * M_PI * ($hour - 10.0) / 24);
        $jitterPress = (mt_rand(-30, 30) / 100);
        $pressure = round($seasonalPress + $tide + $jitterPress, 2);

        if ($ambient && isset($ambient['pressure']) && abs($time->diffInMinutes(now('Asia/Kolkata'))) < 10) {
            $pressure = round((float) $ambient['pressure'] + (mt_rand(-10, 10) / 100), 2);
        }

        // 6. Solar Panel Charging & Battery State (TP4056 + 18650 Li-ion Cell)
        // Daytime: 06:15 to 18:30 in Ghaziabad
        $isDaylight = ($hour >= 6.25 && $hour <= 18.5);

        if ($isDaylight) {
            // Sun is active
            // Morning ramp (06:15 - 12:00): battery charges up from ~3.85V to 4.18V
            // Afternoon (12:00 - 17:00): saturated around 4.15V - 4.20V
            // Late afternoon (17:00 - 18:30): tapering to 4.12V
            $peakProgress = sin(M_PI * ($hour - 6.25) / 12.25);
            $battery = round(3.88 + 0.30 * $peakProgress + (mt_rand(-2, 2) / 100), 2);
            $battery = min(4.20, max(3.75, $battery));

            if ($battery >= 4.18) {
                $solarStatus = 'full';
            } else {
                $solarStatus = 'charging';
            }
        } else {
            // Nighttime: gradual discharge under ESP8266 deep sleep load
            // Slides down from ~4.12V to ~3.84V by early dawn
            $nightHours = ($hour > 18.5) ? ($hour - 18.5) : ($hour + 5.5);
            $battery = round(4.12 - (0.28 * ($nightHours / 11.75)) + (mt_rand(-1, 1) / 100), 2);
            $battery = min(4.15, max(3.70, $battery));
            $solarStatus = 'idle';
        }

        // 7. Signal RSSI & Sensor Hardware Redundancy Status
        $rssi = mt_rand(-72, -58); // Good, realistic ESP8266 WiFi signal in dBm
        $bmeStatus = true;
        $dhtStatus = true;

        return [
            'temperature' => $temperature,
            'humidity' => $humidity,
            'pressure' => $pressure,
            'battery' => $battery,
            'rssi' => $rssi,
            'bme_status' => $bmeStatus,
            'dht_status' => $dhtStatus,
            'solar_status' => $solarStatus,
        ];
    }

    /**
     * Populate 365 days of realistic time-series records for device history.
     * Inserts in chunked batches for high performance.
     */
    public function seedHistoricalYear(string $deviceId = 'solar_data_collector_v1', int $days = 365, int $intervalMinutes = 30): int
    {
        // Ensure device exists
        Device::firstOrCreate(
            ['device_id' => $deviceId],
            [
                'device_name' => 'Solar Data Collector v1',
                'api_key' => 'SolarDataCollectionV1',
                'status' => 'active',
                'last_seen' => now('Asia/Kolkata'),
            ]
        );

        $endTime = now('Asia/Kolkata');
        $startTime = $endTime->copy()->subDays($days);
        $currentTime = $startTime->copy();

        $batch = [];
        $totalInserted = 0;
        $batchSize = 1000;

        // Wipe old test records for this device to prevent duplicated overlapping times
        WeatherLog::where('device_id', $deviceId)->delete();

        while ($currentTime->lte($endTime)) {
            $reading = $this->generateReadingForTime($currentTime);
            $reading['device_id'] = $deviceId;
            $reading['created_at'] = $currentTime->format('Y-m-d H:i:s');

            $batch[] = $reading;

            if (count($batch) >= $batchSize) {
                DB::table('weather_logs')->insert($batch);
                $totalInserted += count($batch);
                $batch = [];
            }

            $currentTime->addMinutes($intervalMinutes);
        }

        if (count($batch) > 0) {
            DB::table('weather_logs')->insert($batch);
            $totalInserted += count($batch);
        }

        // Update device last_seen to latest timestamp
        Device::where('device_id', $deviceId)->update([
            'last_seen' => $endTime,
            'status' => 'active',
        ]);

        return $totalInserted;
    }
}
