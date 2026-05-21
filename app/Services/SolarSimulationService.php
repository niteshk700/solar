<?php

namespace App\Services;

use Carbon\Carbon;

class SolarSimulationService
{
    /**
     * Get the target solar stats for a specific date, including exact historical overrides.
     */
    public function getTargetStatsForDate(string $dateString): array
    {
        // 100% exact overrides requested by the user
        $overrides = [

            // 2026 (Live presentation dates) matching the flow beautifully
            '2026-05-20' => ['gen' => 320.40, 'sy' => 3.20, 'co2' => 0.29, 'cuf' => 0.13],
            '2026-05-21' => ['gen' => 350.00, 'sy' => 3.50, 'co2' => 0.32, 'cuf' => 0.15],
            '2026-05-22' => ['gen' => 368.50, 'sy' => 3.69, 'co2' => 0.33, 'cuf' => 0.15],
            '2026-05-23' => ['gen' => 345.20, 'sy' => 3.45, 'co2' => 0.31, 'cuf' => 0.14],
            '2026-05-24' => ['gen' => 392.10, 'sy' => 3.92, 'co2' => 0.35, 'cuf' => 0.16],
            '2026-05-25' => ['gen' => 310.80, 'sy' => 3.11, 'co2' => 0.28, 'cuf' => 0.13],
            '2026-05-26' => ['gen' => 335.50, 'sy' => 3.36, 'co2' => 0.30, 'cuf' => 0.14],
            '2026-05-27' => ['gen' => 370.20, 'sy' => 3.70, 'co2' => 0.33, 'cuf' => 0.15],
            '2026-05-28' => ['gen' => 355.00, 'sy' => 3.55, 'co2' => 0.32, 'cuf' => 0.15],
            '2026-05-29' => ['gen' => 385.60, 'sy' => 3.86, 'co2' => 0.35, 'cuf' => 0.16],
            '2026-05-30' => ['gen' => 340.00, 'sy' => 3.40, 'co2' => 0.31, 'cuf' => 0.14],
        ];

        if (isset($overrides[$dateString])) {
            return $overrides[$dateString];
        }

        // Generate a stable, deterministic random value based on the date so it doesn't change on refresh
        $hash = hexdec(substr(md5($dateString), 0, 8));
        
        // Random daily generation between 220 and 410 kWh
        $gen = round(220 + ($hash % 190) + ($hash % 100) / 100, 2);
        
        return [
            'gen' => $gen,
            'sy' => round($gen / 100, 2),
            'co2' => round($gen * 0.0009, 2),
            'cuf' => round(($gen / 2400), 2) // rounded like other CUFs
        ];
    }

    /**
     * Compute instantaneous stats at a specific Carbon date/time.
     */
    public function getLiveStats(Carbon $dateTime): array
    {
        $dateStr = $dateTime->format('Y-m-d');
        $target = $this->getTargetStatsForDate($dateStr);
        $totalGen = $target['gen'];

        // Decimal hour of the day
        $hour = $dateTime->hour + ($dateTime->minute / 60) + ($dateTime->second / 3600);

        // Peak active power calculations: P_max = E_day * (pi / 24)
        $pMax = $totalGen * (M_PI / 24);

        $activePower = 0.00;
        $cumulativeGen = 0.00;

        if ($hour >= 6.00 && $hour <= 18.00) {
            // Sine curve power profile
            $curvePos = ($hour - 6.00) / 12.00;
            
            // Add minute-seeded deterministic noise for realistic sensor jitter (±4%)
            $minuteSeed = hexdec(substr(md5($dateTime->format('Y-m-d H:i')), 0, 4));
            $noise = (($minuteSeed % 100) - 50) / 1250; // -0.04 to +0.04
            
            $activePower = max(0.00, round($pMax * sin($curvePos * M_PI) * (1 + $noise), 2));

            // Analytical integral to find exact smooth cumulative energy
            $cumulativeGen = round($pMax * (12.00 / M_PI) * (1 - cos($curvePos * M_PI)), 2);
            $cumulativeGen = min($cumulativeGen, $totalGen);
        } elseif ($hour > 18.00) {
            $activePower = 0.00;
            $cumulativeGen = $totalGen;
        } else {
            $activePower = 0.00;
            $cumulativeGen = 0.00;
        }

        $sy = round($cumulativeGen / 100, 2);
        $co2 = round($cumulativeGen * 0.0009, 2);
        $cuf = round(($cumulativeGen / 2400), 2);

        return [
            'active_power' => $activePower,
            'gen_today' => $cumulativeGen,
            'sy' => $sy,
            'co2' => $co2,
            'cuf' => $cuf,
            'total_grid' => 0.00,
            'total_dg' => 0.00,
            'target_gen' => $totalGen
        ];
    }

    /**
     * Generate list of historical points for a date, capping at $maxTime if it is today.
     * Generates logs at 1-hour intervals.
     */
    public function getDailyPoints(Carbon $date, ?Carbon $maxTime = null): array
    {
        $points = [];
        $isToday = $maxTime && $date->isSameDay($maxTime);

        for ($h = 0; $h < 24; $h++) {
            $pointTime = $date->copy()->setTime($h, 0, 0);

            // If we are simulating today, do not produce future points!
            if ($isToday && $pointTime->gt($maxTime)) {
                break;
            }

            $stats = $this->getLiveStats($pointTime);
            $points[] = array_merge([
                'time' => $pointTime->format('H:i:s'),
                'timestamp' => $pointTime->toIso8601String(),
            ], $stats);
        }

        return $points;
    }
}
