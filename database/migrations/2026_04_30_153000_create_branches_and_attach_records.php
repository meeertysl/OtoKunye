<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $branchId = DB::table('branches')->insertGetId([
            'name' => 'Merkez Şube',
            'code' => 'MERKEZ',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('owner_user_id')->constrained()->nullOnDelete();
        });
        Schema::table('service_records', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        DB::table('users')->whereNull('branch_id')->update(['branch_id' => $branchId]);
        DB::table('vehicles')->whereNull('branch_id')->update(['branch_id' => $branchId]);
        DB::table('service_records')->whereNull('branch_id')->update(['branch_id' => $branchId]);
    }

    public function down(): void
    {
        Schema::table('service_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::dropIfExists('branches');
    }
};
