<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan_cong_giang_vien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('module_hoc_id')->constrained('module_hoc')->cascadeOnDelete();
            $table->foreignId('giang_vien_id')->constrained('giang_vien')->cascadeOnDelete();
            $table->dateTime('ngay_phan_cong')->useCurrent();
            $table->enum('trang_thai', ['cho_xac_nhan', 'da_nhan', 'tu_choi'])->default('cho_xac_nhan');
            $table->text('ghi_chu')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();

            $table->unique(['lop_hoc_id', 'module_hoc_id', 'giang_vien_id'], 'uq_phan_cong_giang_vien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan_cong_giang_vien');
    }
};
