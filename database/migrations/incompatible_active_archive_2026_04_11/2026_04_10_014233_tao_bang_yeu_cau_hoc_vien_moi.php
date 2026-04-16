<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yeu_cau_hoc_vien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_vien_id')->constrained('hoc_vien')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->nullable()->constrained('lop_hoc')->cascadeOnDelete();
            $table->string('loai_yeu_cau', 50); // xin_tham_gia, ho_tro, khac
            $table->text('noi_dung');
            $table->enum('trang_thai', ['cho_xu_ly', 'da_xac_nhan', 'tu_choi'])->default('cho_xu_ly');
            $table->text('phan_hoi_admin')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_hoc_vien');
    }
};
