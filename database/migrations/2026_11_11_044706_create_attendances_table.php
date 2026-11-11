<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->date('date')->index();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 11, 7)->nullable();
            $table->integer('check_in_distance')->nullable();
            $table->string('check_in_photo')->nullable();
            $table->string('check_in_status')->default('on_time'); // 'on_time', 'late'
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 11, 7)->nullable();
            $table->integer('check_out_distance')->nullable();
            $table->string('check_out_photo')->nullable();
            $table->string('check_out_status')->nullable(); // 'normal', 'early_leave'
            $table->integer('working_minutes')->default(0);
            $table->integer('break_minutes')->default(0);
            $table->string('status')->default('present'); // 'present', 'late', 'half_day', 'absent', 'izin', 'sakit', 'cuti'
            $table->text('notes')->nullable();
            $table->boolean('face_verified')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
