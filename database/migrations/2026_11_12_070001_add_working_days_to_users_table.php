<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('working_days')->nullable()->after('shift_id');
        });

        // Set default Senin - Jumat (1..5) untuk pegawai yang sudah ada
        DB::table('users')
            ->whereNull('working_days')
            ->update(['working_days' => json_encode([1, 2, 3, 4, 5])]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('working_days');
        });
    }
};
