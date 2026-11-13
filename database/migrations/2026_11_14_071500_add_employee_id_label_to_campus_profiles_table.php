<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campus_profiles') && !Schema::hasColumn('campus_profiles', 'employee_id_label')) {
            Schema::table('campus_profiles', function (Blueprint $table) {
                $table->string('employee_id_label')->default('NIK')->nullable()->after('campus_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('campus_profiles') && Schema::hasColumn('campus_profiles', 'employee_id_label')) {
            Schema::table('campus_profiles', function (Blueprint $table) {
                $table->dropColumn('employee_id_label');
            });
        }
    }
};
