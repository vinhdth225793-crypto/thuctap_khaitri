<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giang_vien_lich_ranh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('giang_vien_id');
            $table->enum('loai_lich_ranh', ['theo_tuan', 'theo_ngay'])->default('theo_tuan');
            $table->tinyInteger('thu_trong_tuan')->nullable();
            $table->date('ngay_cu_the')->nullable();
            $table->time('gio_bat_dau');
            $table->time('gio_ket_thuc');
            $table->string('ca_day', 20)->nullable();
            $table->text('ghi_chu')->nullable();
            $table->enum('trang_thai', ['hoat_dong', 'tam_an'])->default('hoat_dong');
            $table->timestamps();

            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->cascadeOnDelete();
            $table->index(['giang_vien_id', 'trang_thai'], 'idx_gv_lich_ranh_status');
            $table->index(['giang_vien_id', 'loai_lich_ranh', 'thu_trong_tuan'], 'idx_gv_lich_ranh_weekly');
            $table->index(['giang_vien_id', 'loai_lich_ranh', 'ngay_cu_the'], 'idx_gv_lich_ranh_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giang_vien_lich_ranh');
    }
};
