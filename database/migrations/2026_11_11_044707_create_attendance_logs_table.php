<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30); // 'check_in', 'break_start', 'break_end', 'check_out'
            $table->date('date')->index();
            $table->time('time');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->integer('distance_meters')->nullable();
            $table->string('location_name')->nullable()->default('Office');
            $table->string('photo_path')->nullable();
            $table->string('status', 50)->nullable()->default('On time');
            $table->string('duration', 30)->nullable(); // e.g. "00:45" for break
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
