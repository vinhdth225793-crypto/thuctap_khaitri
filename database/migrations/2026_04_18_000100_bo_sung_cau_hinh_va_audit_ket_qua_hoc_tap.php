<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addMissingColumns('bai_kiem_tra', [
            'attempt_strategy' => fn (Blueprint $table) => $table->string('attempt_strategy', 50)->default('highest_score')->after('so_lan_duoc_lam'),
            'ket_qua_config' => fn (Blueprint $table) => $table->json('ket_qua_config')->nullable()->after('attempt_strategy'),
        ]);

        $this->addMissingColumns('module_hoc', [
            'ket_qua_config' => fn (Blueprint $table) => $table->json('ket_qua_config')->nullable()->after('trang_thai'),
        ]);

        $this->addMissingColumns('khoa_hoc', [
            'ket_qua_config' => fn (Blueprint $table) => $table->json('ket_qua_config')->nullable()->after('ty_trong_kiem_tra'),
        ]);

        $this->addMissingColumns('ket_qua_hoc_tap', [
            'attempt_strategy_used' => fn (Blueprint $table) => $table->string('attempt_strategy_used', 50)->nullable()->after('phuong_thuc_danh_gia'),
            'aggregation_strategy_used' => fn (Blueprint $table) => $table->string('aggregation_strategy_used', 50)->nullable()->after('attempt_strategy_used'),
            'source_attempt_id' => fn (Blueprint $table) => $table->unsignedBigInteger('source_attempt_id')->nullable()->after('aggregation_strategy_used'),
            'source_attempt_ids' => fn (Blueprint $table) => $table->json('source_attempt_ids')->nullable()->after('source_attempt_id'),
            'calculation_metadata' => fn (Blueprint $table) => $table->json('calculation_metadata')->nullable()->after('chi_tiet'),
        ]);

        if (
            Schema::hasTable('ket_qua_hoc_tap')
            && Schema::hasColumn('ket_qua_hoc_tap', 'source_attempt_id')
            && ! $this->hasForeignKey('ket_qua_hoc_tap', 'source_attempt_id', 'bai_lam_bai_kiem_tra', 'id')
        ) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->foreign('source_attempt_id')->references('id')->on('bai_lam_bai_kiem_tra')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('ket_qua_hoc_tap')
            && Schema::hasColumn('ket_qua_hoc_tap', 'source_attempt_id')
            && $this->hasForeignKey('ket_qua_hoc_tap', 'source_attempt_id', 'bai_lam_bai_kiem_tra', 'id')
        ) {
            Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
                $table->dropForeign(['source_attempt_id']);
            });
        }

        $this->dropExistingColumns('ket_qua_hoc_tap', [
            'attempt_strategy_used',
            'aggregation_strategy_used',
            'source_attempt_id',
            'source_attempt_ids',
            'calculation_metadata',
        ]);

        $this->dropExistingColumns('khoa_hoc', ['ket_qua_config']);
        $this->dropExistingColumns('module_hoc', ['ket_qua_config']);
        $this->dropExistingColumns('bai_kiem_tra', ['attempt_strategy', 'ket_qua_config']);
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
