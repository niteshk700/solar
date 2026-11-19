<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SolarGenerationRecord;
use Illuminate\Support\Facades\DB;
use DateTime;

class SolarGenerationRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = base_path('database/data/solar_plant_data.csv');
        if (!file_exists($csvPath)) {
            $csvPath = base_path('data.csv');
        }

        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        // Skip header line
        fgetcsv($file, 0, "\t");

        $records = [];
        $insertedCount = 0;
        $batchSize = 250;
        $now = now()->toDateTimeString();

        while (($row = fgetcsv($file, 0, "\t")) !== false) {
            if (count($row) < 3) continue;

            $dateStr = trim($row[0]);
            $dateObj = DateTime::createFromFormat('d/m/Y', $dateStr);
            if (!$dateObj) continue;

            $recordDate = $dateObj->format('Y-m-d');
            $rawDetail = $row[1] ?? '';
            $kwh = (float) trim($row[2] ?? 0);
            $co2 = (float) trim($row[3] ?? 0);
            $cuf = (float) trim($row[4] ?? 0);

            // Parse Specific Yield (SY) from detail if present
            $sy = null;
            if (preg_match('/SY:\s*([0-9.]+)/i', $rawDetail, $syMatch)) {
                $sy = (float) $syMatch[1];
            } else {
                $sy = round($kwh / 99.84, 2);
            }

            // Parse Grid and DG if present
            $grid = 0.00;
            if (preg_match('/Total Grid:\s*([0-9.]+)/i', $rawDetail, $gridMatch)) {
                $grid = (float) $gridMatch[1];
            }

            $dg = 0.00;
            if (preg_match('/Total DG:\s*([0-9.]+)/i', $rawDetail, $dgMatch)) {
                $dg = (float) $dgMatch[1];
            }

            $records[] = [
                'record_date' => $recordDate,
                'generation_kwh' => $kwh,
                'specific_yield' => $sy,
                'co2_tonnes' => $co2,
                'cuf_percent' => $cuf,
                'grid_power' => $grid,
                'dg_power' => $dg,
                'plant_name' => 'Nitra 99.84 kW (RTU664)',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($records) >= $batchSize) {
                DB::table('solar_generation_records')->upsert(
                    $records,
                    ['record_date'],
                    ['generation_kwh', 'specific_yield', 'co2_tonnes', 'cuf_percent', 'grid_power', 'dg_power', 'plant_name', 'updated_at']
                );
                $insertedCount += count($records);
                $records = [];
            }
        }

        if (count($records) > 0) {
            DB::table('solar_generation_records')->upsert(
                $records,
                ['record_date'],
                ['generation_kwh', 'specific_yield', 'co2_tonnes', 'cuf_percent', 'grid_power', 'dg_power', 'plant_name', 'updated_at']
            );
            $insertedCount += count($records);
        }

        fclose($file);

        if ($this->command) {
            $this->command->info("✅ Successfully ingested and stored {$insertedCount} daily solar plant records from data.csv!");
        }
    }
}
