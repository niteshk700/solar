<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_id',
        'api_key',
        'device_name',
        'status',
        'last_seen',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    public function weatherLogs()
    {
        return $this->hasMany(WeatherLog::class, 'device_id', 'device_id');
    }
}
