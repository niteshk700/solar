<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolarGenerationRecord extends Model
{
    protected $fillable = [
        'record_date',
        'generation_kwh',
        'specific_yield',
        'co2_tonnes',
        'cuf_percent',
        'grid_power',
        'dg_power',
        'plant_name',
    ];

    protected $casts = [
        'record_date' => 'date',
        'generation_kwh' => 'float',
        'specific_yield' => 'float',
        'co2_tonnes' => 'float',
        'cuf_percent' => 'float',
        'grid_power' => 'float',
        'dg_power' => 'float',
    ];
}
