<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_records', function (Blueprint $table) {
            $table->date('reminder_sent_for_date')->nullable()->after('next_service_km');
        });
    }

    public function down(): void
    {
        Schema::table('service_records', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_for_date');
        });
    }
};
