<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('license_plate')->unique();
            $table->string('chassis_number', 17)->unique();
            $table->string('engine_number')->nullable();
            $table->enum('fuel_type', ['Benzin', 'Dizel', 'LPG', 'Hibrit', 'Elektrik']);
            $table->enum('transmission_type', ['Manuel', 'Otomatik', 'Yarı-Otomatik']);
            $table->unsignedInteger('current_km');
            $table->date('inspection_date')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
