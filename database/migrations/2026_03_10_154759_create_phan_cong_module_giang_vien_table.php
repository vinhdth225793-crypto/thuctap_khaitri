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
        Schema::create('phan_cong_module_giang_vien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id');
            $table->unsignedBigInteger('giao_vien_id');
            $table->datetime('ngay_phan_cong')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->enum('trang_thai', ['cho_xac_nhan', 'da_nhan', 'tu_choi'])->default('da_nhan');
            $table->text('ghi_chu')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Foreign keys
            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->onDelete('cascade');
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->onDelete('cascade');
            $table->foreign('giao_vien_id')->references('id')->on('giang_vien')->onDelete('cascade');
            $table->foreign('created_by')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('cascade');

            // Unique constraint
            $table->unique(['module_hoc_id', 'giao_vien_id'], 'uq_module_giang_vien');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phan_cong_module_giang_vien');
    }
};
