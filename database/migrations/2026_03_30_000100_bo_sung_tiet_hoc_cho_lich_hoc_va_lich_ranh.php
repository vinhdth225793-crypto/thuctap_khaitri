<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lich_hoc')) {
            return;
        }

        if (!Schema::hasColumn('lich_hoc', 'tiet_bat_dau')) {
            Schema::table('lich_hoc', function (Blueprint $table) {
                $table->tinyInteger('tiet_bat_dau')->nullable()->after('gio_ket_thuc');
            });
        }

        if (!Schema::hasColumn('lich_hoc', 'tiet_ket_thuc')) {
            Schema::table('lich_hoc', function (Blueprint $table) {
                $table->tinyInteger('tiet_ket_thuc')->nullable()->after('tiet_bat_dau');
            });
        }

        if (!Schema::hasColumn('lich_hoc', 'buoi_hoc')) {
            Schema::table('lich_hoc', function (Blueprint $table) {
                $table->string('buoi_hoc', 20)->nullable()->after('tiet_ket_thuc');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('lich_hoc')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('lich_hoc', 'tiet_bat_dau') ? 'tiet_bat_dau' : null,
            Schema::hasColumn('lich_hoc', 'tiet_ket_thuc') ? 'tiet_ket_thuc' : null,
            Schema::hasColumn('lich_hoc', 'buoi_hoc') ? 'buoi_hoc' : null,
        ]));

        if ($columns !== []) {
            Schema::table('lich_hoc', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
