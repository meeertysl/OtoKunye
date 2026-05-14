<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_records', function (Blueprint $table) {
            $table->string('invoice_status')->default('yok')->after('invoice_url');
            $table->string('invoice_external_id')->nullable()->after('invoice_status');
            $table->timestamp('invoice_requested_at')->nullable()->after('invoice_external_id');
            $table->timestamp('invoice_synced_at')->nullable()->after('invoice_requested_at');
            $table->text('invoice_last_error')->nullable()->after('invoice_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_records', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_status',
                'invoice_external_id',
                'invoice_requested_at',
                'invoice_synced_at',
                'invoice_last_error',
            ]);
        });
    }
};
