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
}
