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
        Schema::create('khoa_hoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mon_hoc_id');
            $table->string('ma_khoa_hoc', 50)->unique();
            $table->string('ten_khoa_hoc', 200);
            $table->string('mo_ta_ngan', 500)->nullable();
            $table->text('mo_ta_chi_tiet')->nullable();
            $table->string('hinh_anh', 255)->nullable();
            $table->enum('cap_do', ['co_ban', 'trung_binh', 'nang_cao'])->default('co_ban');
            $table->integer('tong_so_module')->default(0);
            $table->boolean('trang_thai')->default(1);
            $table->timestamps();
            
            // Foreign key
            $table->foreign('mon_hoc_id')->references('id')->on('mon_hoc')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khoa_hoc');
    }
};
