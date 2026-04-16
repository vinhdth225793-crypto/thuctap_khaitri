<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_kiem_tra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('module_hoc_id')->nullable()->constrained('module_hoc')->nullOnDelete();
            $table->foreignId('lich_hoc_id')->nullable()->constrained('lich_hoc')->nullOnDelete();
            $table->string('tieu_de');
            $table->text('mo_ta')->nullable();
            $table->unsignedInteger('thoi_gian_lam_phut')->default(60);
            $table->dateTime('thoi_gian_bat_dau')->nullable();
            $table->dateTime('thoi_gian_ket_thuc')->nullable();
            $table->unsignedInteger('so_lan_lam_toi_da')->default(1);
            $table->decimal('diem_dat', 8, 2)->default(5.00);
            $table->boolean('tron_cau_hoi')->default(true);
            $table->boolean('tron_dap_an')->default(true);
            $table->boolean('cho_xem_ket_qua')->default(true);
            $table->boolean('cho_xem_dap_an')->default(false);
            $table->enum('trang_thai', ['ban_nhap', 'cho_phe_duyet', 'da_phe_duyet', 'dang_dien_ra', 'ket_thuc'])->default('ban_nhap');
            $table->json('cau_hinh_giam_sat')->nullable();
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_kiem_tra');
    }
};
