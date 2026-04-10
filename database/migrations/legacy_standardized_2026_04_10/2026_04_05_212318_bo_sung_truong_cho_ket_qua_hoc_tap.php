<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ket_qua_hoc_tap')) {
            return;
        }

        if (!Schema::hasColumn('ket_qua_hoc_tap', 'module_hoc_id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->unsignedBigInteger('module_hoc_id')->nullable()->after('hoc_vien_id');
            });
        }

        if (!Schema::hasColumn('ket_qua_hoc_tap', 'bai_kiem_tra_id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->unsignedBigInteger('bai_kiem_tra_id')->nullable()->after('module_hoc_id');
            });
        }

        if (!Schema::hasColumn('ket_qua_hoc_tap', 'trang_thai')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->string('trang_thai', 50)->default('dang_hoc')->after('so_bai_kiem_tra_hoan_thanh');
            });
        }

        if (!Schema::hasColumn('ket_qua_hoc_tap', 'nhan_xet_giang_vien')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->text('nhan_xet_giang_vien')->nullable()->after('trang_thai');
            });
        }

        if (! $this->hasForeignKey('module_hoc_id', 'module_hoc', 'id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->nullOnDelete();
            });
        }

        if (! $this->hasForeignKey('bai_kiem_tra_id', 'bai_kiem_tra', 'id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('bai_kiem_tra_id')->references('id')->on('bai_kiem_tra')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('ket_qua_hoc_tap')) {
            return;
        }

        if ($this->hasForeignKey('module_hoc_id', 'module_hoc', 'id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['module_hoc_id']);
            });
        }

        if ($this->hasForeignKey('bai_kiem_tra_id', 'bai_kiem_tra', 'id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['bai_kiem_tra_id']);
            });
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('ket_qua_hoc_tap', 'module_hoc_id') ? 'module_hoc_id' : null,
            Schema::hasColumn('ket_qua_hoc_tap', 'bai_kiem_tra_id') ? 'bai_kiem_tra_id' : null,
            Schema::hasColumn('ket_qua_hoc_tap', 'trang_thai') ? 'trang_thai' : null,
            Schema::hasColumn('ket_qua_hoc_tap', 'nhan_xet_giang_vien') ? 'nhan_xet_giang_vien' : null,
        ]));

        if ($columns !== []) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    private function hasForeignKey(string $column, string $targetTable, string $targetColumn): bool
    {
        if (!Schema::hasColumn('ket_qua_hoc_tap', $column)) {
            return false;
        }

        foreach (Schema::getForeignKeys('ket_qua_hoc_tap') as $foreignKey) {
            if (
                ($foreignKey['columns'] ?? null) === [$column]
                && ($foreignKey['foreign_table'] ?? null) === $targetTable
                && ($foreignKey['foreign_columns'] ?? null) === [$targetColumn]
            ) {
                return true;
            }
        }

        return false;
    }
};
