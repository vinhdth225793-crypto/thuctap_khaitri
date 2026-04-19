<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phieu_xet_duyet_ket_qua', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('phan_cong_id')->nullable();
            $table->unsignedBigInteger('giang_vien_id')->nullable();
            $table->unsignedBigInteger('nguoi_lap_id');
            $table->string('phuong_an', 60);
            $table->decimal('ty_trong_kiem_tra', 5, 2)->default(80);
            $table->decimal('ty_trong_diem_danh', 5, 2)->default(20);
            $table->decimal('diem_dat', 5, 2)->default(5);
            $table->json('bai_kiem_tra_ids')->nullable();
            $table->json('cong_thuc')->nullable();
            $table->string('trang_thai', 30)->default('draft');
            $table->text('ghi_chu')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('reviewing_by_id')->nullable();
            $table->timestamp('reviewing_at')->nullable();
            $table->unsignedBigInteger('approved_by_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by_id')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->unsignedBigInteger('finalized_by_id')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->foreign('khoa_hoc_id', 'fk_pxdkq_khoa_hoc')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            $table->foreign('phan_cong_id', 'fk_pxdkq_phan_cong')->references('id')->on('phan_cong_module_giang_vien')->nullOnDelete();
            $table->foreign('giang_vien_id', 'fk_pxdkq_giang_vien')->references('id')->on('giang_vien')->nullOnDelete();
            $table->foreign('nguoi_lap_id', 'fk_pxdkq_nguoi_lap')->references('ma_nguoi_dung')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('reviewing_by_id', 'fk_pxdkq_reviewing_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('approved_by_id', 'fk_pxdkq_approved_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('rejected_by_id', 'fk_pxdkq_rejected_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('finalized_by_id', 'fk_pxdkq_finalized_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();

            $table->index(['khoa_hoc_id', 'trang_thai'], 'idx_pxdkq_course_status');
            $table->index(['nguoi_lap_id', 'trang_thai'], 'idx_pxdkq_teacher_status');
        });

        Schema::create('chi_tiet_phieu_xet_duyet_ket_qua', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('phieu_id');
            $table->unsignedBigInteger('hoc_vien_id');
            $table->unsignedInteger('tong_so_buoi')->default(0);
            $table->unsignedInteger('so_buoi_tham_du')->default(0);
            $table->decimal('ty_le_tham_du', 5, 2)->nullable();
            $table->decimal('diem_chuyen_can', 8, 2)->nullable();
            $table->decimal('diem_kiem_tra', 8, 2)->nullable();
            $table->decimal('diem_xet_duyet', 8, 2)->nullable();
            $table->string('ket_qua', 30)->default('chua_du');
            $table->json('chi_tiet_bai_kiem_tra')->nullable();
            $table->json('calculation_metadata')->nullable();
            $table->timestamps();

            $table->foreign('phieu_id', 'fk_ctpxd_phieu')->references('id')->on('phieu_xet_duyet_ket_qua')->cascadeOnDelete();
            $table->foreign('hoc_vien_id', 'fk_ctpxd_hoc_vien')->references('ma_nguoi_dung')->on('nguoi_dung')->cascadeOnDelete();
            $table->unique(['phieu_id', 'hoc_vien_id'], 'uniq_ctpxd_phieu_hv');
            $table->index(['hoc_vien_id', 'ket_qua'], 'idx_ctpxd_student_result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_phieu_xet_duyet_ket_qua');
        Schema::dropIfExists('phieu_xet_duyet_ket_qua');
    }
};
