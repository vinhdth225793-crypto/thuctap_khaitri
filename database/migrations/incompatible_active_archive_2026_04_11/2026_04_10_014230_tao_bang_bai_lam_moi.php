<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_lam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bai_kiem_tra_id')->constrained('bai_kiem_tra')->cascadeOnDelete();
            $table->foreignId('hoc_vien_id')->constrained('hoc_vien')->cascadeOnDelete();
            $table->unsignedInteger('lan_lam_thu')->default(1);
            $table->timestamp('bat_dau_luc')->nullable();
            $table->timestamp('nop_luc')->nullable();
            $table->decimal('diem_trac_nghiem', 8, 2)->nullable();
            $table->decimal('diem_tu_luan', 8, 2)->nullable();
            $table->decimal('tong_diem', 8, 2)->nullable();
            $table->string('trang_thai', 50)->default('dang_lam'); // dang_lam, hoan_thanh, bi_huy
            $table->string('trang_thai_cham', 50)->default('chua_cham'); // chua_cham, dang_cham, da_cham
            $table->text('nhan_xet_giang_vien')->nullable();
            $table->foreignId('nguoi_cham_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->dateTime('cham_luc')->nullable();
            $table->timestamps();

            $table->unique(['bai_kiem_tra_id', 'hoc_vien_id', 'lan_lam_thu'], 'uq_bai_lam_hoc_vien_lan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_lam');
    }
};
