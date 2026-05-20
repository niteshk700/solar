<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'pressure',
        'battery',
        'rssi',
        'bme_status',
        'solar_status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'temperature' => 'float',
        'humidity' => 'float',
        'pressure' => 'float',
        'battery' => 'float',
        'bme_status' => 'boolean',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
