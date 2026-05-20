<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Device;
use App\Models\WeatherLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the main IoT Dashboard.
     */
    public function index()
    {
        $devices = Device::all()->map(function ($device) {
            $latestLog = WeatherLog::where('device_id', $device->device_id)
                ->orderBy('created_at', 'desc')
                ->first();

            // A device is considered online if it is active and has transmitted data in the last 15 minutes
            $isOnline = $device->status === 'active'
                && $device->last_seen
                && Carbon::parse($device->last_seen)->gt(now()->subMinutes(15));

            $device->latest_log = $latestLog;
            $device->is_online = $isOnline;

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
                $avgTemp += $dev->latest_log->temperature;
                $avgHum += $dev->latest_log->humidity;
                $avgPress += $dev->latest_log->pressure;
                if ($dev->latest_log->battery) {
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
            'avg_temp' => $logCount > 0 ? round($avgTemp / $logCount, 1) : 0,
            'avg_hum' => $logCount > 0 ? round($avgHum / $logCount, 1) : 0,
            'avg_press' => $logCount > 0 ? round($avgPress / $logCount, 1) : 0,
            'avg_battery' => $logCount > 0 ? round($avgBattery / $logCount, 2) : 0,
        ];

        // Fetch recent weather stream (latest 10 data packets received globally)
        $recentLogs = WeatherLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) use ($devices) {
                $dev = $devices->firstWhere('device_id', $log->device_id);
                $log->device_name = $dev ? $dev->device_name : 'Unknown Device';
                return $log;
            });

        return view('dashboard.index', compact('devices', 'stats', 'recentLogs'));
    }
}
