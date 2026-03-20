<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE tai_nguyen_buoi_hoc
            MODIFY loai_tai_nguyen ENUM('bai_giang', 'tai_lieu', 'bai_tap') NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE tai_nguyen_buoi_hoc
            MODIFY loai_tai_nguyen VARCHAR(255) NOT NULL
        ");
    }
};
