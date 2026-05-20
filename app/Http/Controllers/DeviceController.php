<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Device;
use App\Models\WeatherLog;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DeviceController extends Controller
{
    /**
     * Display device management portal.
     */
    public function index()
    {
        $devices = Device::orderBy('created_at', 'desc')->get()->map(function ($device) {
            // Determine if online (seen in last 15 minutes)
            $device->is_online = $device->status === 'active'
                && $device->last_seen
                && Carbon::parse($device->last_seen)->gt(now()->subMinutes(15));
            return $device;
        });

        return view('devices.index', compact('devices'));
    }

    /**
     * Store a newly created device.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|alpha_dash|unique:devices,device_id|max:50',
            'device_name' => 'required|string|max:100',
            'api_key' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        // Auto-generate key if left blank
        if (empty($validated['api_key'])) {
            $validated['api_key'] = 'sol_' . Str::random(24);
        }

        Device::create($validated);

        return redirect()->route('devices.index')->with('success', 'IoT Device added successfully! Secure credentials generated.');
    }

    /**
     * Update the specified device in storage.
     */
    public function update(Request $request, string $id)
    {
        $device = Device::findOrFail($id);

        $validated = $request->validate([
            'device_name' => 'required|string|max:100',
            'api_key' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $device->update($validated);

        return redirect()->route('devices.index')->with('success', 'Device updated successfully!');
    }

    /**
     * Remove the specified device and its related telemetry history.
     */
    public function destroy(string $id)
    {
        $device = Device::findOrFail($id);
        
        // Delete all associated logs
        WeatherLog::where('device_id', $device->device_id)->delete();
        
        // Delete device
        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device and its weather telemetry logs successfully deleted!');
    }

    /**
     * Display historical telemetry analytics with timeline filters.
     */
    public function analytics(Request $request, string $id)
    {
        $device = Device::findOrFail($id);

        // Timeline filter (hours)
        $range = $request->query('range', '24h');
        
        switch ($range) {
            case '7d':
                $hours = 24 * 7;
                $format = 'Y-m-d H:i';
                $grouping = 4; // Fetch fewer points for large range
                break;
            case '30d':
                $hours = 24 * 30;
                $format = 'Y-m-d';
                $grouping = 16;
                break;
            case '24h':
            default:
                $hours = 24;
                $format = 'H:i';
                $grouping = 1;
                break;
        }

        // Fetch logs
        $logsQuery = WeatherLog::where('device_id', $device->device_id)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc');

        $allLogs = $logsQuery->get();

        // Downsample slightly if too many logs to prevent canvas lag
        $logs = collect();
        if ($allLogs->isNotEmpty()) {
            foreach ($allLogs as $index => $log) {
                if ($index % $grouping === 0) {
                    $logs->push($log);
                }
            }
        }

        // Aggregate current status
        $device->is_online = $device->status === 'active'
            && $device->last_seen
            && Carbon::parse($device->last_seen)->gt(now()->subMinutes(15));

        $latestLog = $allLogs->last();

        // Structure charts data
        $chartData = [
            'timestamps' => $logs->map(fn($l) => Carbon::parse($l->created_at)->format($format))->toArray(),
            'temperature' => $logs->map(fn($l) => (float)$l->temperature)->toArray(),
            'humidity' => $logs->map(fn($l) => (float)$l->humidity)->toArray(),
            'pressure' => $logs->map(fn($l) => (float)$l->pressure)->toArray(),
            'battery' => $logs->map(fn($l) => (float)$l->battery)->toArray(),
            'rssi' => $logs->map(fn($l) => (int)$l->rssi)->toArray(),
        ];

        return view('devices.analytics', compact('device', 'chartData', 'range', 'latestLog'));
    }
}
