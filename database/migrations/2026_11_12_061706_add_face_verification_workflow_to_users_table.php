<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('face_reference_status')->default('none')->after('face_reference');
            $table->string('pending_face_reference')->nullable()->after('face_reference_status');
            $table->text('face_rejection_reason')->nullable()->after('pending_face_reference');
            $table->boolean('face_unlock_requested')->default(false)->after('allow_face_registration');
        });

        // Set status = approved dan kunci registrasi untuk akun yang sudah memiliki face_reference
        DB::table('users')->whereNotNull('face_reference')->where('face_reference', '!=', '')->update([
            'face_reference_status' => 'approved',
            'allow_face_registration' => false,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'face_reference_status',
                'pending_face_reference',
                'face_rejection_reason',
                'face_unlock_requested',
            ]);
        });
    }
};
