<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('khoa_hoc', 'phuong_thuc_danh_gia')) {
            Schema::table('khoa_hoc', function (Blueprint $table) {
                $table->string('phuong_thuc_danh_gia', 50)->default('cuoi_khoa')->after('tong_so_module');
            });
        }

        if (!Schema::hasColumn('khoa_hoc', 'ty_trong_diem_danh')) {
            Schema::table('khoa_hoc', function (Blueprint $table) {
                $table->decimal('ty_trong_diem_danh', 5, 2)->default(20)->after('phuong_thuc_danh_gia');
            });
        }

        if (!Schema::hasColumn('khoa_hoc', 'ty_trong_kiem_tra')) {
            Schema::table('khoa_hoc', function (Blueprint $table) {
                $table->decimal('ty_trong_kiem_tra', 5, 2)->default(80)->after('ty_trong_diem_danh');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('khoa_hoc', 'phuong_thuc_danh_gia') ? 'phuong_thuc_danh_gia' : null,
            Schema::hasColumn('khoa_hoc', 'ty_trong_diem_danh') ? 'ty_trong_diem_danh' : null,
            Schema::hasColumn('khoa_hoc', 'ty_trong_kiem_tra') ? 'ty_trong_kiem_tra' : null,
        ]));

        if ($columns !== []) {
            Schema::table('khoa_hoc', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
