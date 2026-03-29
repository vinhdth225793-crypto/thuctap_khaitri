<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tai_nguyen_buoi_hoc', function (Blueprint $table) {
            // Change ENUM to VARCHAR to support multiple types
            $table->string('loai_tai_nguyen', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tai_nguyen_buoi_hoc', function (Blueprint $table) {
            // Reverting back to ENUM if needed (not recommended but for completeness)
            \DB::statement("ALTER TABLE tai_nguyen_buoi_hoc MODIFY loai_tai_nguyen ENUM('bai_giang', 'tai_lieu', 'bai_tap')");
        });
    }
};
