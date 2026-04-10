<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bai_kiem_tra', function (Blueprint $table) {
            $table->boolean('randomize_answers')->default(false)->after('randomize_questions');
            $table->string('che_do_tinh_diem', 50)->default('thu_cong')->after('tong_diem');
            $table->unsignedInteger('so_cau_goi_diem')->nullable()->after('che_do_tinh_diem');
        });
    }

    public function down(): void
    {
        Schema::table('bai_kiem_tra', function (Blueprint $table) {
            $table->dropColumn(['randomize_answers', 'che_do_tinh_diem', 'so_cau_goi_diem']);
        });
    }
};
