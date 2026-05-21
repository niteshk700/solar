<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('weather_logs', function (Blueprint $table) {
            $table->boolean('dht_status')->default(true)->after('bme_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weather_logs', function (Blueprint $table) {
            $table->dropColumn('dht_status');
        });
    }
};
