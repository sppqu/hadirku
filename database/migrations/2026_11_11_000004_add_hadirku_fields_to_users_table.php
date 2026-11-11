<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 50)->nullable()->unique()->after('id');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('role', 20)->default('pegawai')->after('password'); // 'admin', 'pegawai'
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('role');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete()->after('department_id');
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete()->after('shift_id');
            $table->string('position', 100)->nullable()->after('office_id'); // e.g. Staff IT, HR, dll
            $table->string('face_reference')->nullable()->after('position'); // Face reference photo path
            $table->string('avatar')->nullable()->after('face_reference');
            $table->boolean('is_active')->default(true)->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['shift_id']);
            $table->dropForeign(['office_id']);
            $table->dropColumn([
                'nik',
                'phone',
                'role',
                'department_id',
                'shift_id',
                'office_id',
                'position',
                'face_reference',
                'avatar',
                'is_active',
            ]);
        });
    }
};
