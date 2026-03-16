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
        Schema::table('module_hoc', function (Blueprint $table) {
            $table->integer('so_buoi')->default(1)->after('thoi_luong_du_kien')
                  ->comment('Số buổi học quy định cho module này');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_hoc', function (Blueprint $table) {
            $table->dropColumn('so_buoi');
        });
    }
};
