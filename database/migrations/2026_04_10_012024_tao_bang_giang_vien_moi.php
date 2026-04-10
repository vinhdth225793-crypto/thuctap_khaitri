<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giang_vien', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('nguoi_dung_id')->unique();
            $table->string('chuyen_nganh')->nullable();
            $table->string('hoc_vi')->nullable();
            $table->text('mo_ta_ngan')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('hien_thi_trang_chu')->default(false);
            $table->timestamps();

            $table->foreign('nguoi_dung_id')->references('id')->on('nguoi_dung')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giang_vien');
    }
};
