<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SolarTelemetryGeneratorService;

class GenerateHistoricalSolarDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solar:seed-historical {--days=365 : Number of past days to generate} {--interval=30 : Interval in minutes between telemetry logs} {--device=solar_data_collector_v1 : Device ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed authentic, diurnal 1-year telemetry history for the solar weather station';

    /**
     * Execute the console command.
     */
    public function handle(SolarTelemetryGeneratorService $generator): int
    {
        $days = (int) $this->option('days');
        $interval = (int) $this->option('interval');
        $device = (string) $this->option('device');

        $this->info("☀️ Generating {$days} days of realistic solar weather telemetry for '{$device}' (every {$interval} mins)...");

        $start = microtime(true);
        $count = $generator->seedHistoricalYear($device, $days, $interval);
        $duration = round(microtime(true) - $start, 2);

        $this->info("✅ Successfully generated and stored {$count} realistic telemetry records in {$duration}s!");

        return Command::SUCCESS;
    }
}
