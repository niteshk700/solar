<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SolarTelemetryGeneratorService;

class ProduceLiveSolarTelemetryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solar:produce-telemetry {--device=solar_data_collector_v1 : Device ID} {--force : Force insertion regardless of age}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Produce and store a continuous live real-time telemetry packet for the solar weather station';

    /**
     * Execute the console command.
     */
    public function handle(SolarTelemetryGeneratorService $generator): int
    {
        $device = (string) $this->option('device');
        $maxAge = $this->option('force') ? 0 : 45;

        $log = $generator->ensureLatestTelemetry($device, $maxAge);

        $this->info("⚡ Live telemetry packet stored [ID: {$log->id}] - Temp: {$log->temperature}°C | Humidity: {$log->humidity}% | Battery: {$log->battery}V ({$log->solar_status})");

        return Command::SUCCESS;
    }
}
