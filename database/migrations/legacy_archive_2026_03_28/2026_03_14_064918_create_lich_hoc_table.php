<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lich_hoc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id');
            $table->unsignedBigInteger('giang_vien_id')->nullable();
            $table->date('ngay_hoc');
            $table->time('gio_bat_dau');
            $table->time('gio_ket_thuc');
            $table->tinyInteger('thu_trong_tuan')->comment('2=T2, 3=T3, 4=T4, 5=T5, 6=T6, 7=T7, 8=CN');
            $table->integer('buoi_so')->comment('Buổi thứ mấy của module (1,2,3...)');
            $table->string('phong_hoc', 100)->nullable();
            $table->enum('hinh_thuc', ['truc_tiep', 'online'])->default('truc_tiep');
            $table->string('link_online', 500)->nullable();
            $table->text('ghi_chu')->nullable();
            $table->enum('trang_thai', ['cho', 'dang_hoc', 'hoan_thanh', 'huy'])->default('cho');
            $table->timestamps();

            // Foreign keys
            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->onDelete('cascade');
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->onDelete('cascade');
            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lich_hoc');
    }
};
