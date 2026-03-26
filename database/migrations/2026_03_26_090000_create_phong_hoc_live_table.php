<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phong_hoc_live', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bai_giang_id')->unique();
            $table->string('nen_tang_live', 50);
            $table->string('loai_live', 50)->default('class');
            $table->string('tieu_de');
            $table->text('mo_ta')->nullable();
            $table->unsignedBigInteger('moderator_id')->nullable();
            $table->unsignedBigInteger('tro_giang_id')->nullable();
            $table->dateTime('thoi_gian_bat_dau');
            $table->unsignedInteger('thoi_luong_phut');
            $table->unsignedInteger('mo_phong_truoc_phut')->default(15);
            $table->unsignedInteger('nhac_truoc_phut')->default(10);
            $table->unsignedInteger('suc_chua_toi_da')->nullable();
            $table->boolean('cho_phep_chat')->default(true);
            $table->boolean('cho_phep_thao_luan')->default(true);
            $table->boolean('cho_phep_chia_se_man_hinh')->default(false);
            $table->boolean('tat_mic_khi_vao')->default(true);
            $table->boolean('tat_camera_khi_vao')->default(true);
            $table->boolean('cho_phep_ghi_hinh')->default(false);
            $table->boolean('chi_admin_duoc_ghi_hinh')->default(false);
            $table->boolean('tu_dong_gan_ban_ghi')->default(false);
            $table->boolean('khoa_copy_noi_dung_mo_ta')->default(false);
            $table->string('trang_thai_duyet', 50)->default('nhap');
            $table->string('trang_thai_cong_bo', 50)->default('an');
            $table->string('trang_thai_phong', 50)->default('chua_mo');
            $table->json('du_lieu_nen_tang_json')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bai_giang_id')->references('id')->on('bai_giangs')->onDelete('cascade');
            $table->foreign('moderator_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('tro_giang_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('created_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('approved_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();

            $table->index(['nen_tang_live', 'trang_thai_phong'], 'idx_phong_hoc_live_platform_room_state');
            $table->index(['trang_thai_duyet', 'trang_thai_cong_bo'], 'idx_phong_hoc_live_approval_publish');
            $table->index('thoi_gian_bat_dau', 'idx_phong_hoc_live_start_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phong_hoc_live');
    }
};
