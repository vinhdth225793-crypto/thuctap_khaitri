<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tai_khoan_cho_phe_duyet', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten');
            $table->string('email')->unique();
            $table->string('mat_khau');
            $table->string('vai_tro', 50)->default('giang_vien');
            $table->string('so_dien_thoai', 20)->nullable();
            $table->date('ngay_sinh')->nullable();
            $table->text('dia_chi')->nullable();
            $table->enum('trang_thai', ['cho_phe_duyet', 'da_phe_duyet', 'tu_choi'])->default('cho_phe_duyet');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tai_khoan_cho_phe_duyet');
    }
};