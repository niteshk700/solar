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
        Schema::create('solar_generation_records', function (Blueprint $table) {
            $table->id();
            $table->date('record_date')->unique()->index();
            $table->decimal('generation_kwh', 8, 2);
            $table->decimal('specific_yield', 6, 2)->nullable();
            $table->decimal('co2_tonnes', 6, 3)->nullable();
            $table->decimal('cuf_percent', 5, 2)->nullable();
            $table->decimal('grid_power', 8, 2)->default(0);
            $table->decimal('dg_power', 8, 2)->default(0);
            $table->string('plant_name', 100)->default('Nitra 99.84 kW (RTU664)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solar_generation_records');
    }
};
