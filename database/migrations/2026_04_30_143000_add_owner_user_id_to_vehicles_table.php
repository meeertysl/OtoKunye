<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('owner_user_id')
                ->nullable()
                ->after('last_updated_by_user_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::statement('UPDATE vehicles SET owner_user_id = last_updated_by_user_id WHERE owner_user_id IS NULL AND last_updated_by_user_id IS NOT NULL');

        $firstUserId = DB::table('users')->orderBy('id')->value('id');
        if ($firstUserId) {
            DB::table('vehicles')
                ->whereNull('owner_user_id')
                ->update(['owner_user_id' => $firstUserId]);
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_user_id');
        });
    }
};
