<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->foreignId('inventory_part_id')->nullable()->after('service_record_id')->constrained('parts')->nullOnDelete();
            $table->decimal('quantity', 12, 2)->default(1)->after('part_name');
            $table->boolean('use_inventory')->default(false)->after('labor_or_part_fee');
        });
    }

    public function down(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inventory_part_id');
            $table->dropColumn(['quantity', 'use_inventory']);
        });
    }
};
