<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ket_qua_hoc_tap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('hoc_vien_id')->constrained('hoc_vien')->cascadeOnDelete();
            $table->decimal('diem_diem_danh', 8, 2)->nullable();
            $table->decimal('diem_kiem_tra', 8, 2)->nullable();
            $table->decimal('diem_tong_ket', 8, 2)->nullable();
            $table->unsignedInteger('tong_so_buoi')->default(0);
            $table->unsignedInteger('so_buoi_tham_du')->default(0);
            $table->json('chi_tiet')->nullable();
            $table->timestamps();

            $table->unique(['lop_hoc_id', 'hoc_vien_id'], 'uq_ket_qua_hoc_tap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ket_qua_hoc_tap');
    }
};
