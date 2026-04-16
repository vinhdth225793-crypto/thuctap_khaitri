<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten');
            $table->string('email')->unique();
            $table->string('mat_khau');
            $table->enum('vai_tro', ['admin', 'giang_vien', 'hoc_vien'])->default('hoc_vien');
            $table->string('so_dien_thoai', 20)->nullable();
            $table->string('dia_chi', 500)->nullable();
            $table->date('ngay_sinh')->nullable();
            $table->string('anh_dai_dien')->nullable();
            $table->boolean('trang_thai')->default(true);
            $table->timestamp('email_xac_thuc_luc')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
    }
};
