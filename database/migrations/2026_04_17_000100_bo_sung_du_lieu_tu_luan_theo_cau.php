<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addMissingColumns('bai_kiem_tra', [
            'che_do_noi_dung' => fn (Blueprint $table) => $table->string('che_do_noi_dung', 50)->nullable()->after('loai_noi_dung'),
        ]);

        $this->addMissingColumns('ngan_hang_cau_hoi', [
            'dap_an_mau' => fn (Blueprint $table) => $table->text('dap_an_mau')->nullable()->after('goi_y_tra_loi'),
            'rubric_cham' => fn (Blueprint $table) => $table->text('rubric_cham')->nullable()->after('dap_an_mau'),
        ]);

        $this->addMissingColumns('chi_tiet_bai_kiem_tra', [
            'huong_dan_rieng' => fn (Blueprint $table) => $table->text('huong_dan_rieng')->nullable()->after('diem_so'),
            'rubric_rieng' => fn (Blueprint $table) => $table->text('rubric_rieng')->nullable()->after('huong_dan_rieng'),
        ]);

        $this->addMissingColumns('chi_tiet_bai_lam_bai_kiem_tra', [
            'file_dinh_kem' => fn (Blueprint $table) => $table->string('file_dinh_kem')->nullable()->after('cau_tra_loi_text'),
        ]);
    }

    public function down(): void
    {
        $this->dropExistingColumns('chi_tiet_bai_lam_bai_kiem_tra', ['file_dinh_kem']);
        $this->dropExistingColumns('chi_tiet_bai_kiem_tra', ['huong_dan_rieng', 'rubric_rieng']);
        $this->dropExistingColumns('ngan_hang_cau_hoi', ['dap_an_mau', 'rubric_cham']);
        $this->dropExistingColumns('bai_kiem_tra', ['che_do_noi_dung']);
    }

    private function addMissingColumns(string $tableName, array $columnDefinitions): void
    {
        if (!Schema::hasTable($tableName)) {
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
        if (!Schema::hasTable($tableName)) {
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
};
