<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GhaziabadWeatherService
{
    // Ghaziabad, Uttar Pradesh, India Coordinates
    const LATITUDE = 28.6692;
    const LONGITUDE = 77.4538;
    const CACHE_KEY = 'ghaziabad_weather_current';
    const CACHE_TTL_SECONDS = 300; // 5 minutes cache

    /**
     * Get current weather for Ghaziabad from Open-Meteo Free API.
     */
    public function getCurrentWeather(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->fetchFromApi();
        });
    }

    /**
     * Force refresh the weather from Open-Meteo API.
     */
    public function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);
        return $this->getCurrentWeather();
    }

    /**
     * Fetch raw data from Open-Meteo API.
     */
    private function fetchFromApi(): array
    {
        try {
            $response = Http::timeout(4)
                ->withHeaders([
                    'User-Agent' => 'SolarIoT-WeatherPlatform/1.0'
                ])
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => self::LATITUDE,
                    'longitude' => self::LONGITUDE,
                    'current' => 'temperature_2m,relative_humidity_2m,surface_pressure,wind_speed_10m,weather_code',
                    'timezone' => 'Asia/Kolkata',
                ]);

            if ($response->successful()) {
                $current = $response->json('current') ?? [];
                $code = (int) ($current['weather_code'] ?? 0);
                $conditionInfo = $this->resolveWeatherCondition($code);

                return [
                    'available' => true,
                    'city' => 'Ghaziabad',
                    'state' => 'Uttar Pradesh',
                    'country' => 'India',
                    'coordinates' => self::LATITUDE . '° N, ' . self::LONGITUDE . '° E',
                    'temperature' => isset($current['temperature_2m']) ? round($current['temperature_2m'], 1) : null,
                    'humidity' => isset($current['relative_humidity_2m']) ? round($current['relative_humidity_2m']) : null,
                    'pressure' => isset($current['surface_pressure']) ? round($current['surface_pressure'], 1) : null,
                    'wind_speed' => isset($current['wind_speed_10m']) ? round($current['wind_speed_10m'], 1) : null,
                    'weather_code' => $code,
                    'condition' => $conditionInfo['text'],
                    'icon' => $conditionInfo['icon'],
                    'icon_color' => $conditionInfo['color'],
                    'time' => $current['time'] ?? now('Asia/Kolkata')->format('Y-m-d H:i'),
                    'last_updated' => now('Asia/Kolkata')->format('h:i A'),
                    'source' => 'Open-Meteo Free API'
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch Ghaziabad weather from Open-Meteo: ' . $e->getMessage());
        }

        // Graceful fallback if network fails
        return [
            'available' => false,
            'city' => 'Ghaziabad',
            'state' => 'Uttar Pradesh',
            'country' => 'India',
            'coordinates' => self::LATITUDE . '° N, ' . self::LONGITUDE . '° E',
            'temperature' => 16.5,
            'humidity' => 68,
            'pressure' => 1012.0,
            'wind_speed' => 5.2,
            'weather_code' => 0,
            'condition' => 'Clear Sky',
            'icon' => 'fa-solid fa-sun',
            'icon_color' => '#f59e0b',
            'time' => now('Asia/Kolkata')->format('Y-m-d H:i'),
            'last_updated' => now('Asia/Kolkata')->format('h:i A'),
            'source' => 'Offline Baseline'
        ];
    }

    /**
     * Map WMO weather code to human description, icon, and accent color.
     */
    private function resolveWeatherCondition(int $code): array
    {
        return match (true) {
            $code === 0 => [
                'text' => 'Clear Sky',
                'icon' => 'fa-solid fa-sun',
                'color' => '#f59e0b'
            ],
            in_array($code, [1, 2]) => [
                'text' => 'Partly Cloudy',
                'icon' => 'fa-solid fa-cloud-sun',
                'color' => '#38bdf8'
            ],
            $code === 3 => [
                'text' => 'Overcast',
                'icon' => 'fa-solid fa-cloud',
                'color' => '#94a3b8'
            ],
            in_array($code, [45, 48]) => [
                'text' => 'Haze / Mist',
                'icon' => 'fa-solid fa-smog',
                'color' => '#cbd5e1'
            ],
            in_array($code, [51, 53, 55, 56, 57]) => [
                'text' => 'Drizzle',
                'icon' => 'fa-solid fa-cloud-rain',
                'color' => '#0ea5e9'
            ],
            in_array($code, [61, 63, 65, 66, 67, 80, 81, 82]) => [
                'text' => 'Rain',
                'icon' => 'fa-solid fa-cloud-showers-heavy',
                'color' => '#2563eb'
            ],
            in_array($code, [71, 73, 75, 77, 85, 86]) => [
                'text' => 'Snow',
                'icon' => 'fa-solid fa-snowflake',
                'color' => '#e0f2fe'
            ],
            in_array($code, [95, 96, 99]) => [
                'text' => 'Thunderstorm',
                'icon' => 'fa-solid fa-bolt-lightning',
                'color' => '#eab308'
            ],
            default => [
                'text' => 'Fair',
                'icon' => 'fa-solid fa-cloud-sun',
                'color' => '#38bdf8'
            ],
        };
    }
}
