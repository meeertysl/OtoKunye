<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('entry_date');
            $table->date('exit_date')->nullable();
            $table->text('master_note')->nullable();
            $table->boolean('customer_approval_status')->default(false);
            $table->decimal('estimated_amount', 12, 2)->nullable();
            $table->date('next_service_date')->nullable();
            $table->string('before_image_path')->nullable();
            $table->string('after_image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
