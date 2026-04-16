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
        Schema::table('lich_hoc', function (Blueprint $table) {
            $table->unsignedBigInteger('lop_hoc_id')->nullable()->change();
            $table->unsignedBigInteger('giang_vien_id')->nullable()->change();
            // Đổi enum sang string để dễ linh hoạt hoặc đổi lại giá trị cũ
            $table->string('hinh_thuc')->default('truc_tiep')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lich_hoc', function (Blueprint $table) {
            $table->unsignedBigInteger('lop_hoc_id')->nullable(false)->change();
            $table->unsignedBigInteger('giang_vien_id')->nullable(false)->change();
            $table->enum('hinh_thuc', ['offline', 'online'])->default('offline')->change();
        });
    }
};
