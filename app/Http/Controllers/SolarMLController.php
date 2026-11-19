<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolarGenerationRecord;
use App\Services\SolarMLForecastingService;
use Illuminate\Http\JsonResponse;

class SolarMLController extends Controller
{
    /**
     * Display the Solar AI & Machine Learning Forecasting Studio.
     */
    public function index(SolarMLForecastingService $mlService)
    {
        $totalRecords = SolarGenerationRecord::count();
        $earliest = SolarGenerationRecord::orderBy('record_date', 'asc')->first();
        $latest = SolarGenerationRecord::orderBy('record_date', 'desc')->first();
        $totalGen = SolarGenerationRecord::sum('generation_kwh');
        $avgGen = $totalRecords > 0 ? round($totalGen / $totalRecords, 2) : 0;
        $maxGen = SolarGenerationRecord::max('generation_kwh') ?? 0;
        $totalCo2 = SolarGenerationRecord::sum('co2_tonnes');

        $datasetStats = [
            'total_records' => $totalRecords,
            'earliest_date' => $earliest ? $earliest->record_date->format('M d, Y') : '--',
            'latest_date' => $latest ? $latest->record_date->format('M d, Y') : '--',
            'total_generation_kwh' => $totalGen,
            'avg_daily_kwh' => $avgGen,
            'max_generation_kwh' => $maxGen,
            'total_co2_tonnes' => round($totalCo2, 2),
            'plant_capacity_kw' => 99.84,
        ];

        // Run default initial training (Random Forest, 80/20 split)
        $initialResult = $mlService->train('random_forest', [
            'estimators' => 100,
            'tree_depth' => 12,
            'split_ratio' => 0.80,
        ]);

        // Preview recent records for data table
        $recentRecords = SolarGenerationRecord::orderBy('record_date', 'desc')->limit(15)->get();

        return view('solar_ai.index', compact('datasetStats', 'initialResult', 'recentRecords'));
    }

    /**
     * AJAX Endpoint: Train selected algorithm with custom hyperparameters.
     */
    public function train(Request $request, SolarMLForecastingService $mlService): JsonResponse
    {
        $algorithm = $request->input('algorithm', 'random_forest');
        $params = [
            'estimators' => (int) $request->input('estimators', 100),
            'tree_depth' => (int) $request->input('tree_depth', 12),
            'lstm_units' => (int) $request->input('lstm_units', 64),
            'epochs' => (int) $request->input('epochs', 50),
            'batch_size' => (int) $request->input('batch_size', 32),
            'learning_rate' => (float) $request->input('learning_rate', 0.001),
            'split_ratio' => (float) $request->input('split_ratio', 0.80),
            'c' => (float) $request->input('c', 10.0),
            'epsilon' => (float) $request->input('epsilon', 0.10),
            'p' => (int) $request->input('p', 3),
            'd' => (int) $request->input('d', 1),
            'q' => (int) $request->input('q', 2),
            'discount_factor' => (float) $request->input('discount_factor', 0.90),
            'episodes' => (int) $request->input('episodes', 25),
        ];

        $result = $mlService->train($algorithm, $params);

        return response()->json($result);
    }

    /**
     * Export all 1,334 solar generation records as CSV.
     */
    public function exportData()
    {
        $fileName = 'nitra_99kw_solar_plant_dataset_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($file, [
                'Record Date',
                'Plant Name',
                'Generation (kWh)',
                'Specific Yield (SY)',
                'CO2 Reduction (Tonnes)',
                'CUF (%)',
                'Total Grid (kW)',
                'Total DG (kW)',
            ]);

            SolarGenerationRecord::orderBy('record_date', 'asc')->chunk(250, function ($chunk) use ($file) {
                foreach ($chunk as $rec) {
                    fputcsv($file, [
                        $rec->record_date->format('Y-m-d'),
                        $rec->plant_name,
                        $rec->generation_kwh,
                        $rec->specific_yield,
                        $rec->co2_tonnes,
                        $rec->cuf_percent,
                        $rec->grid_power,
                        $rec->dg_power,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
