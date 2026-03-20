<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_lam_bai_kiem_tra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bai_kiem_tra_id');
            $table->unsignedBigInteger('hoc_vien_id');
            $table->longText('noi_dung_bai_lam')->nullable();
            $table->string('trang_thai')->default('dang_lam'); // dang_lam, da_nop
            $table->timestamp('bat_dau_luc')->nullable();
            $table->timestamp('nop_luc')->nullable();
            $table->decimal('diem_so', 5, 2)->nullable();
            $table->text('nhan_xet')->nullable();
            $table->timestamps();

            $table->foreign('bai_kiem_tra_id')
                ->references('id')
                ->on('bai_kiem_tra')
                ->onDelete('cascade');

            $table->foreign('hoc_vien_id')
                ->references('ma_nguoi_dung')
                ->on('nguoi_dung')
                ->onDelete('cascade');

            $table->unique(['bai_kiem_tra_id', 'hoc_vien_id'], 'unique_bai_lam_hoc_vien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_lam_bai_kiem_tra');
    }
};
