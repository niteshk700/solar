<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@solar.yourdev.in'],
            [
                'name' => 'Solar Admin',
                'password' => bcrypt('admin123'),
            ]
        );

        // 2. Create IoT Devices (Clean registration with no mock history)
        \App\Models\Device::updateOrCreate(
            ['device_id' => 'solar_data_collector_v1'],
            [
                'api_key' => 'SolarDataCollectionV1',
                'device_name' => 'Solar Data Collector v1',
                'status' => 'active',
                'last_seen' => null,
            ]
        );
    }
}
