<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Move data from 'noi_dung_cau_hoi' to 'noi_dung' if 'noi_dung' is empty
        $questions = DB::table('ngan_hang_cau_hoi')->get();
        foreach ($questions as $q) {
            $updateData = [];
            if (empty($q->noi_dung) && !empty($q->noi_dung_cau_hoi)) {
                $updateData['noi_dung'] = $q->noi_dung_cau_hoi;
            }
            
            if (!empty($updateData)) {
                DB::table('ngan_hang_cau_hoi')->where('id', $q->id)->update($updateData);
            }
        }

        // 2. Migrate existing answers to 'dap_an_cau_hoi' table
        foreach ($questions as $q) {
            $exists = DB::table('dap_an_cau_hoi')->where('ngan_hang_cau_hoi_id', $q->id)->exists();
            
            if (!$exists && isset($q->dap_an_dung) && !empty($q->dap_an_dung)) {
                // Insert Correct Answer
                DB::table('dap_an_cau_hoi')->insert([
                    'ngan_hang_cau_hoi_id' => $q->id,
                    'noi_dung' => $q->dap_an_dung,
                    'is_dap_an_dung' => true,
                    'thu_tu' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Insert Wrong Answers
                foreach (['dap_an_sai_1', 'dap_an_sai_2', 'dap_an_sai_3'] as $idx => $col) {
                    if (!empty($q->$col)) {
                        DB::table('dap_an_cau_hoi')->insert([
                            'ngan_hang_cau_hoi_id' => $q->id,
                            'noi_dung' => $q->$col,
                            'is_dap_an_dung' => false,
                            'thu_tu' => $idx + 2,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // 3. Drop redundant columns and fix constraints
        Schema::table('ngan_hang_cau_hoi', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('ngan_hang_cau_hoi', 'dap_an_sai_1')) $columnsToDrop[] = 'dap_an_sai_1';
            if (Schema::hasColumn('ngan_hang_cau_hoi', 'dap_an_sai_2')) $columnsToDrop[] = 'dap_an_sai_2';
            if (Schema::hasColumn('ngan_hang_cau_hoi', 'dap_an_sai_3')) $columnsToDrop[] = 'dap_an_sai_3';
            if (Schema::hasColumn('ngan_hang_cau_hoi', 'dap_an_dung')) $columnsToDrop[] = 'dap_an_dung';
            if (Schema::hasColumn('ngan_hang_cau_hoi', 'noi_dung_cau_hoi')) $columnsToDrop[] = 'noi_dung_cau_hoi';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            // Keep it nullable for now to avoid truncation warnings
            $table->longText('noi_dung')->nullable()->change();
            $table->string('ma_cau_hoi', 60)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ngan_hang_cau_hoi', function (Blueprint $table) {
            $table->text('noi_dung_cau_hoi')->after('khoa_hoc_id')->nullable();
            $table->text('dap_an_sai_1')->nullable();
            $table->text('dap_an_sai_2')->nullable();
            $table->text('dap_an_sai_3')->nullable();
            $table->text('dap_an_dung')->nullable();
            
            $table->longText('noi_dung')->nullable()->change();
        });
    }
};
