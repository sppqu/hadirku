<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Universitas HadirKU');
            $table->string('campus_code')->nullable(); // Kode PT / PDDikti
            $table->string('rector_name')->nullable(); // Nama Rektor
            $table->string('accreditation')->nullable(); // Unggul, A, Baik Sekali, dll
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->text('description')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_profiles');
    }
};
