<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Device;
use App\Models\WeatherLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * POST /api/weather-data
     * Receive and store weather logs from ESP8266 devices.
     */
    public function storeWeatherData(Request $request): JsonResponse
    {
        // 1. Basic validation of JSON payload structure
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'device_id' => 'required|string',
            'temperature' => 'nullable|numeric|between:-50,60',
            'humidity' => 'nullable|numeric|between:0,100',
            'pressure' => 'nullable|numeric|between:800,1200',
            'battery' => 'nullable|numeric|between:0,6',
            'rssi' => 'nullable|integer|between:-150,0',
            'bme_status' => 'nullable|boolean',
            'solar_status' => 'nullable|string|in:idle,charging,full',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $validated = $validator->validated();

        // 2. Device authentication and status check
        $device = Device::where('device_id', $validated['device_id'])->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not registered'
            ], 401);
        }

        if ($device->api_key !== $validated['api_key']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized device credentials'
            ], 401);
        }

        if ($device->status !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Device is deactivated'
            ], 403);
        }

        // 3. Log the weather data (handling optional sensors gracefully)
        WeatherLog::create([
            'device_id' => $validated['device_id'],
            'temperature' => $validated['temperature'] ?? null,
            'humidity' => $validated['humidity'] ?? null,
            'pressure' => $validated['pressure'] ?? null,
            'battery' => $validated['battery'] ?? null,
            'rssi' => $validated['rssi'] ?? null,
            'bme_status' => $validated['bme_status'] ?? true,
            'solar_status' => $validated['solar_status'] ?? 'idle',
        ]);

        // 4. Update the device's last_seen timestamp
        $device->update([
            'last_seen' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data stored successfully'
        ], 200);
    }

    /**
     * GET /api/device/{id}/latest
     * Retrieve the single latest log point for the device.
     */
    public function latest(string $deviceId): JsonResponse
    {
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found'
            ], 404);
        }

        $latestLog = WeatherLog::where('device_id', $deviceId)
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'status' => 'success',
            'device' => [
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'status' => $device->status,
                'last_seen' => $device->last_seen ? $device->last_seen->toIso8601String() : null,
            ],
            'data' => $latestLog
        ], 200);
    }

    /**
     * GET /api/device/{id}/history
     * Retrieve past weather data points for the device.
     */
    public function history(string $deviceId, Request $request): JsonResponse
    {
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found'
            ], 404);
        }

        // Default to past 24 hours unless specified
        $hours = $request->query('hours', 24);
        if (!is_numeric($hours) || $hours <= 0 || $hours > 720) {
            $hours = 24;
        }

        $history = WeatherLog::where('device_id', $deviceId)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'device' => [
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
            ],
            'hours' => (int)$hours,
            'count' => $history->count(),
            'data' => $history
        ], 200);
    }
}
