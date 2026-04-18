<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addMissingColumns('ket_qua_hoc_tap', [
            'diem_giang_vien_chot' => fn (Blueprint $table) => $table->decimal('diem_giang_vien_chot', 8, 2)->nullable()->after('diem_tong_ket'),
            'trang_thai_chot' => fn (Blueprint $table) => $table->string('trang_thai_chot', 50)->default('chua_chot')->after('trang_thai'),
            'chot_boi' => fn (Blueprint $table) => $table->unsignedBigInteger('chot_boi')->nullable()->after('trang_thai_chot'),
            'chot_luc' => fn (Blueprint $table) => $table->dateTime('chot_luc')->nullable()->after('chot_boi'),
            'ghi_chu_chot' => fn (Blueprint $table) => $table->text('ghi_chu_chot')->nullable()->after('chot_luc'),
            'trang_thai_duyet' => fn (Blueprint $table) => $table->string('trang_thai_duyet', 50)->default('chua_gui')->after('ghi_chu_chot'),
            'admin_duyet_id' => fn (Blueprint $table) => $table->unsignedBigInteger('admin_duyet_id')->nullable()->after('trang_thai_duyet'),
            'duyet_luc' => fn (Blueprint $table) => $table->dateTime('duyet_luc')->nullable()->after('admin_duyet_id'),
            'ghi_chu_duyet' => fn (Blueprint $table) => $table->text('ghi_chu_duyet')->nullable()->after('duyet_luc'),
            'luu_ho_so_luc' => fn (Blueprint $table) => $table->dateTime('luu_ho_so_luc')->nullable()->after('ghi_chu_duyet'),
        ]);

        if (
            Schema::hasColumn('ket_qua_hoc_tap', 'chot_boi')
            && ! $this->hasForeignKey('ket_qua_hoc_tap', 'chot_boi', 'nguoi_dung', 'ma_nguoi_dung')
        ) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('chot_boi')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            });
        }

        if (
            Schema::hasColumn('ket_qua_hoc_tap', 'admin_duyet_id')
            && ! $this->hasForeignKey('ket_qua_hoc_tap', 'admin_duyet_id', 'nguoi_dung', 'ma_nguoi_dung')
        ) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('admin_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            });
        }

        if (! Schema::hasIndex('ket_qua_hoc_tap', 'idx_kqht_duyet_ho_so')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->index(['khoa_hoc_id', 'trang_thai_duyet'], 'idx_kqht_duyet_ho_so');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('ket_qua_hoc_tap', 'idx_kqht_duyet_ho_so')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropIndex('idx_kqht_duyet_ho_so');
            });
        }

        if ($this->hasForeignKey('ket_qua_hoc_tap', 'admin_duyet_id', 'nguoi_dung', 'ma_nguoi_dung')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['admin_duyet_id']);
            });
        }

        if ($this->hasForeignKey('ket_qua_hoc_tap', 'chot_boi', 'nguoi_dung', 'ma_nguoi_dung')) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['chot_boi']);
            });
        }

        $this->dropExistingColumns('ket_qua_hoc_tap', [
            'diem_giang_vien_chot',
            'trang_thai_chot',
            'chot_boi',
            'chot_luc',
            'ghi_chu_chot',
            'trang_thai_duyet',
            'admin_duyet_id',
            'duyet_luc',
            'ghi_chu_duyet',
            'luu_ho_so_luc',
        ]);
    }

    private function addMissingColumns(string $tableName, array $columnDefinitions): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columnDefinitions as $column => $definition) {
            if (Schema::hasColumn($tableName, $column)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }

    private function dropExistingColumns(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($tableName, $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });
    }

    private function hasForeignKey(string $tableName, string $column, string $targetTable, string $targetColumn): bool
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
            return false;
        }

        foreach (Schema::getForeignKeys($tableName) as $foreignKey) {
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
