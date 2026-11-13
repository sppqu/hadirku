<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campus_profiles') && !Schema::hasColumn('campus_profiles', 'foundation')) {
            Schema::table('campus_profiles', function (Blueprint $table) {
                $table->string('foundation')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('campus_profiles') && Schema::hasColumn('campus_profiles', 'foundation')) {
            Schema::table('campus_profiles', function (Blueprint $table) {
                $table->dropColumn('foundation');
            });
        }
    }
};
