<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lop_hoc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khoa_hoc_id')->constrained('khoa_hoc')->cascadeOnDelete();
            $table->string('ma_lop_hoc', 50)->unique();
            $table->date('ngay_khai_giang')->nullable();
            $table->date('ngay_ket_thuc')->nullable();
            $table->enum('trang_thai_van_hanh', ['cho_mo', 'cho_giang_vien', 'san_sang', 'dang_day', 'ket_thuc', 'huy'])->default('cho_mo');
            $table->decimal('ty_trong_diem_danh', 5, 2)->default(20);
            $table->decimal('ty_trong_kiem_tra', 5, 2)->default(80);
            $table->text('ghi_chu')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lop_hoc');
    }
};
