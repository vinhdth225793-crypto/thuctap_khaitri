<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE phan_cong_module_giang_vien 
                       MODIFY COLUMN trang_thai 
                       ENUM('cho_xac_nhan','da_nhan','tu_choi') 
                       NOT NULL DEFAULT 'cho_xac_nhan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE phan_cong_module_giang_vien 
                       MODIFY COLUMN trang_thai 
                       ENUM('cho_xac_nhan','da_nhan','tu_choi') 
                       NOT NULL DEFAULT 'da_nhan'");
    }
};
