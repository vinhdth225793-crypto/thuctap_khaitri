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

        if ($this->hasForeignKey('khoa_hoc_id', 'khoa_hoc', 'id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['khoa_hoc_id']);
            });
        }

        if ($this->hasForeignKey('hoc_vien_id', 'nguoi_dung', 'ma_nguoi_dung')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['hoc_vien_id']);
            });
        }

        if (Schema::hasIndex('ket_qua_hoc_tap', 'uniq_ket_qua_hoc_tap', 'unique')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropUnique('uniq_ket_qua_hoc_tap');
            });
        }

        if (Schema::hasIndex('ket_qua_hoc_tap', 'idx_ket_qua_hoc_tap_khoa_hoc')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropIndex('idx_ket_qua_hoc_tap_khoa_hoc');
            });
        }

        if (Schema::hasIndex('ket_qua_hoc_tap', 'uniq_ket_qua_phan_cap', 'unique')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropUnique('uniq_ket_qua_phan_cap');
            });
        }

        if (!Schema::hasIndex('ket_qua_hoc_tap', 'uniq_ket_qua_phan_cap', 'unique')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->unique(
                    ['hoc_vien_id', 'khoa_hoc_id', 'module_hoc_id', 'bai_kiem_tra_id'],
                    'uniq_ket_qua_phan_cap'
                );
            });
        }

        if (!Schema::hasIndex('ket_qua_hoc_tap', 'idx_ket_qua_hoc_tap_khoa_hoc')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                // Keep an explicit index for khoa_hoc_id so fresh builds stay FK-safe
                // across engines after the old unique key is replaced.
                $table->index('khoa_hoc_id', 'idx_ket_qua_hoc_tap_khoa_hoc');
            });
        }

        if (! $this->hasForeignKey('khoa_hoc_id', 'khoa_hoc', 'id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            });
        }

        if (! $this->hasForeignKey('hoc_vien_id', 'nguoi_dung', 'ma_nguoi_dung')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('ket_qua_hoc_tap')) {
            return;
        }

        if ($this->hasForeignKey('khoa_hoc_id', 'khoa_hoc', 'id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['khoa_hoc_id']);
            });
        }

        if ($this->hasForeignKey('hoc_vien_id', 'nguoi_dung', 'ma_nguoi_dung')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['hoc_vien_id']);
            });
        }

        if (Schema::hasIndex('ket_qua_hoc_tap', 'uniq_ket_qua_phan_cap', 'unique')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropUnique('uniq_ket_qua_phan_cap');
            });
        }

        if (Schema::hasIndex('ket_qua_hoc_tap', 'idx_ket_qua_hoc_tap_khoa_hoc')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropIndex('idx_ket_qua_hoc_tap_khoa_hoc');
            });
        }

        if (!Schema::hasIndex('ket_qua_hoc_tap', 'uniq_ket_qua_hoc_tap', 'unique')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->unique(['khoa_hoc_id', 'hoc_vien_id'], 'uniq_ket_qua_hoc_tap');
            });
        }

        if (! $this->hasForeignKey('khoa_hoc_id', 'khoa_hoc', 'id')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->cascadeOnDelete();
            });
        }

        if (! $this->hasForeignKey('hoc_vien_id', 'nguoi_dung', 'ma_nguoi_dung')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('hoc_vien_id')->references('ma_nguoi_dung')->on('nguoi_dung')->cascadeOnDelete();
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
