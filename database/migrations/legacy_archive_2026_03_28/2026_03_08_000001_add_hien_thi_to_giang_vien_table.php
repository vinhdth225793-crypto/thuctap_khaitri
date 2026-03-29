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
        Schema::table('giang_vien', function (Blueprint $table) {
            $table->boolean('hien_thi_trang_chu')->default(false)->after('so_gio_day');
            $table->text('mo_ta_ngan')->nullable()->after('hien_thi_trang_chu');
            $table->string('avatar_url')->nullable()->after('mo_ta_ngan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('giang_vien', function (Blueprint $table) {
            $table->dropColumn(['hien_thi_trang_chu', 'mo_ta_ngan', 'avatar_url']);
        });
    }
};
