<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('uuid');
            $table->string('model')->nullable()->after('brand');
        });

        Schema::table('service_records', function (Blueprint $table) {
            $table->unsignedInteger('next_service_km')->nullable()->after('next_service_date');
        });
    }

    public function down(): void
    {
        Schema::table('service_records', function (Blueprint $table) {
            $table->dropColumn('next_service_km');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model']);
        });
    }
};
