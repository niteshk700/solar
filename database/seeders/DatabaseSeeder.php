<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@solar.yourdev.in'],
            [
                'name' => 'Solar Admin',
                'password' => bcrypt('admin123'),
            ]
        );

        // 2. Create IoT Devices
        $device1 = \App\Models\Device::updateOrCreate(
            ['device_id' => 'weather_node_01'],
            [
                'api_key' => 'secret_node_01_key',
                'device_name' => 'Solar Meadow Node',
                'status' => 'active',
                'last_seen' => now(),
            ]
        );

        $device2 = \App\Models\Device::updateOrCreate(
            ['device_id' => 'weather_node_02'],
            [
                'api_key' => 'secret_node_02_key',
                'device_name' => 'Solar Roof Node',
                'status' => 'active',
                'last_seen' => now()->subMinutes(12),
            ]
        );

        // 3. Generate 24 Hours of Weather Logs (every 15 mins = 96 records per device)
        $now = now();
        $logs = [];

        for ($hourOffset = 24; $hourOffset >= 0; $hourOffset--) {
            for ($minuteOffset = 0; $minuteOffset < 60; $minuteOffset += 15) {
                $time = (clone $now)->subHours($hourOffset)->subMinutes($minuteOffset);
                $hour = (int)$time->format('H');

                // 24h temperature fluctuation (peak around 14:00, low around 05:00)
                $tempBase = 26.0 + 6.0 * sin((($hour - 8) / 24) * 2 * M_PI);
                
                // Diurnal solar charging simulation
                // Sunlight is active between 07:00 and 18:00
                if ($hour >= 7 && $hour <= 18) {
                    // Solar panel is charging (voltage rises from 3.75V up to 4.15V)
                    $batVal = 3.8 + 0.35 * sin((($hour - 7) / 11) * M_PI) + rand(-10, 10) / 1000;
                } else {
                    // Battery discharges overnight (slow linear or cosine decay down to 3.7V)
                    $decayFraction = ($hour > 18) ? ($hour - 18) / 13 : ($hour + 6) / 13;
                    $batVal = 3.8 - 0.1 * $decayFraction + rand(-10, 10) / 1000;
                }

                // Add random noise to metrics
                $temp = round($tempBase + rand(-5, 5) / 10, 1);
                $hum = round(65.0 - 15.0 * sin((($hour - 8) / 24) * 2 * M_PI) + rand(-20, 20) / 10, 1);
                $press = round(1010.5 + 1.5 * cos((($hour) / 12) * M_PI) + rand(-3, 3) / 10, 1);
                $rssi = rand(-75, -62);

                // Ensure boundaries are realistic
                $hum = max(10, min(100, $hum));
                $batVal = round(max(3.0, min(4.2, $batVal)), 2);

                // Add to device 1
                $logs[] = [
                    'device_id' => 'weather_node_01',
                    'temperature' => $temp,
                    'humidity' => $hum,
                    'pressure' => $press,
                    'battery' => $batVal,
                    'rssi' => $rssi,
                    'created_at' => $time,
                ];

                // Add to device 2 (with slightly offset values for variety)
                $logs[] = [
                    'device_id' => 'weather_node_02',
                    'temperature' => round($temp - 1.2 + rand(-3, 3) / 10, 1),
                    'humidity' => round($hum + 5.0 + rand(-15, 15) / 10, 1),
                    'pressure' => round($press + 0.5 + rand(-2, 2) / 10, 1),
                    'battery' => round($batVal - 0.08 + rand(-5, 5) / 1000, 2),
                    'rssi' => rand(-78, -65),
                    'created_at' => $time,
                ];
            }
        }

        // Chunk insert to be fast and safe
        foreach (array_chunk($logs, 100) as $chunk) {
            \App\Models\WeatherLog::insert($chunk);
        }
    }
}
