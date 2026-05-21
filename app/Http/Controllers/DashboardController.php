<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\WeatherLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Display the main HTML Dashboard.
     */
    public function index()
    {
        $data = $this->getDashboardStats();
        return view('dashboard.index', $data);
    }

    /**
     * AJAX Polling Endpoint: Return live JSON data of all nodes & metrics.
     */
    public function liveData(): JsonResponse
    {
        $data = $this->getDashboardStats();
        return response()->json($data);
    }

    /**
     * Shared helper to compute dynamic aggregates and device health states.
     */
    private function getDashboardStats(): array
    {
        $devices = Device::all()->map(function ($device) {
            $latestLog = WeatherLog::where('device_id', $device->device_id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Consider online if active and seen within the last 1 minute
            $isOnline = $device->status === 'active'
                && $device->last_seen
                && Carbon::parse($device->last_seen)->gt(now()->subMinutes(1));

            $device->latest_log = $latestLog;
            $device->is_online = $isOnline;
            
            // Format nice human relative time
            $device->last_seen_human = $device->last_seen 
                ? Carbon::parse($device->last_seen)->diffForHumans() 
                : 'Never';

            return $device;
        });

        // Compute aggregates from online devices (or all devices if none online)
        $onlineDevices = $devices->filter(fn($d) => $d->is_online);
        $targetDevices = $onlineDevices->isNotEmpty() ? $onlineDevices : $devices;

        $avgTemp = 0;
        $avgHum = 0;
        $avgPress = 0;
        $avgBattery = 0;
        $logCount = 0;

        foreach ($targetDevices as $dev) {
            if ($dev->latest_log) {
                if (!is_null($dev->latest_log->temperature)) {
                    $avgTemp += $dev->latest_log->temperature;
                }
                if (!is_null($dev->latest_log->humidity)) {
                    $avgHum += $dev->latest_log->humidity;
                }
                if (!is_null($dev->latest_log->pressure)) {
                    $avgPress += $dev->latest_log->pressure;
                }
                if (!is_null($dev->latest_log->battery)) {
                    $avgBattery += $dev->latest_log->battery;
                }
                $logCount++;
            }
        }

        $stats = [
            'total_devices' => $devices->count(),
            'online_devices' => $devices->filter(fn($d) => $d->is_online)->count(),
            'offline_devices' => $devices->filter(fn($d) => !$d->is_online && $d->status === 'active')->count(),
            'inactive_devices' => $devices->filter(fn($d) => $d->status === 'inactive')->count(),
            'avg_temp' => $logCount > 0 ? round($avgTemp / $logCount, 1) : null,
            'avg_hum' => $logCount > 0 ? round($avgHum / $logCount, 1) : null,
            'avg_press' => $logCount > 0 ? round($avgPress / $logCount, 1) : null,
            'avg_battery' => $logCount > 0 ? round($avgBattery / $logCount, 2) : null,
        ];

        // Fetch recent weather stream (latest 10 data packets received globally)
        $recentLogs = WeatherLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) use ($devices) {
                $dev = $devices->firstWhere('device_id', $log->device_id);
                $log->device_name = $dev ? $dev->device_name : 'Unknown Device';
                $log->created_at_human = Carbon::parse($log->created_at)->diffForHumans();
                return $log;
            });

        return compact('devices', 'stats', 'recentLogs');
    }

    /**
     * Stream weather logs as a high-performance CSV download.
     */
    public function export(Request $request)
    {
        $fileName = 'weather_telemetry_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $devices = Device::all();
        
        $callback = function() use($devices) {
            $file = fopen('php://output', 'w');
            
            // Write CSV UTF-8 BOM for Microsoft Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header Row
            fputcsv($file, [
                'Timestamp', 
                'Device ID', 
                'Device Name', 
                'Temperature (C)', 
                'Humidity (%)', 
                'Pressure (hPa)', 
                'Battery (V)', 
                'Signal RSSI (dBm)', 
                'BME280 Status', 
                'DHT11 Status', 
                'Solar Status'
            ]);

            // Chunked queries prevent memory limits from blowing up on huge datasets
            WeatherLog::orderBy('created_at', 'desc')
                ->chunk(200, function($logs) use($file, $devices) {
                    foreach ($logs as $log) {
                        $dev = $devices->firstWhere('device_id', $log->device_id);
                        $deviceName = $dev ? $dev->device_name : 'Unknown Device';
                        
                        fputcsv($file, [
                            $log->created_at->format('Y-m-d H:i:s'),
                            $log->device_id,
                            $deviceName,
                            !is_null($log->temperature) ? number_format($log->temperature, 1) : 'N/A',
                            !is_null($log->humidity) ? number_format($log->humidity, 1) : 'N/A',
                            !is_null($log->pressure) ? number_format($log->pressure, 1) : 'N/A',
                            !is_null($log->battery) ? number_format($log->battery, 2) : 'N/A',
                            !is_null($log->rssi) ? $log->rssi : 'N/A',
                            $log->bme_status ? 'CONNECTED' : 'OFFLINE',
                            (isset($log->dht_status) && !$log->dht_status) ? 'OFFLINE' : 'CONNECTED',
                            strtoupper($log->solar_status ?: 'IDLE')
                        ]);
                    }
                });
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
