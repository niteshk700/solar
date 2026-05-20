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
            ['device_id' => 'weather_node_01'],
            [
                'api_key' => 'secret_node_01_key',
                'device_name' => 'Solar Meadow Node',
                'status' => 'active',
                'last_seen' => null,
            ]
        );

        \App\Models\Device::updateOrCreate(
            ['device_id' => 'weather_node_02'],
            [
                'api_key' => 'secret_node_02_key',
                'device_name' => 'Solar Roof Node',
                'status' => 'active',
                'last_seen' => null,
            ]
        );
    }
}
