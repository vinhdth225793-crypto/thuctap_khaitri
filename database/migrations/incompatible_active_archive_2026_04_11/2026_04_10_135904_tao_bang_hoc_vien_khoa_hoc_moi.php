<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoc_vien_khoa_hoc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_vien_id')->constrained('hoc_vien')->cascadeOnDelete();
            $table->foreignId('khoa_hoc_id')->constrained('khoa_hoc')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->nullable()->constrained('lop_hoc')->cascadeOnDelete();
            $table->dateTime('ngay_tham_gia')->useCurrent();
            $table->enum('trang_thai', ['dang_hoc', 'hoan_thanh', 'ngung_hoc', 'cho_duyet'])->default('dang_hoc');
            $table->text('ghi_chu')->nullable();
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hoc_vien_id', 'khoa_hoc_id', 'lop_hoc_id'], 'uq_hoc_vien_khoa_hoc_lop');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoc_vien_khoa_hoc');
    }
};
