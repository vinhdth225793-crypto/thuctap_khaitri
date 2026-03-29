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
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id')->nullable();
            $table->unsignedBigInteger('lich_hoc_id')->nullable();
            $table->string('tieu_de');
            $table->text('mo_ta')->nullable();
            $table->unsignedInteger('thoi_gian_lam_bai')->default(60);
            $table->dateTime('ngay_mo')->nullable();
            $table->dateTime('ngay_dong')->nullable();
            $table->string('pham_vi', 50);
            $table->string('loai_bai_kiem_tra', 50)->default('module');
            $table->string('loai_noi_dung', 50)->default('tu_luan');
            $table->string('trang_thai_duyet', 50)->default('da_duyet');
            $table->string('trang_thai_phat_hanh', 50)->default('phat_hanh');
            $table->decimal('tong_diem', 8, 2)->default(10);
            $table->unsignedInteger('so_lan_duoc_lam')->default(1);
            $table->boolean('randomize_questions')->default(false);
            $table->unsignedBigInteger('nguoi_tao_id')->nullable();
            $table->unsignedBigInteger('nguoi_duyet_id')->nullable();
            $table->dateTime('de_xuat_duyet_luc')->nullable();
            $table->dateTime('duyet_luc')->nullable();
            $table->dateTime('phat_hanh_luc')->nullable();
            $table->text('ghi_chu_duyet')->nullable();
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->nullOnDelete();
            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->nullOnDelete();
            $table->foreign('nguoi_tao_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('nguoi_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->index(['khoa_hoc_id', 'trang_thai_duyet', 'trang_thai_phat_hanh'], 'idx_bai_kiem_tra_course_approval_publish');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_kiem_tra');
    }
};