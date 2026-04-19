<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phong_hoc_live', function (Blueprint $table) {
            if (! Schema::hasColumn('phong_hoc_live', 'platform_type')) {
                $table->string('platform_type', 50)->nullable()->after('nen_tang_live');
            }

            if (! Schema::hasColumn('phong_hoc_live', 'external_meeting_url')) {
                $table->string('external_meeting_url', 500)->nullable()->after('platform_type');
            }

            if (! Schema::hasColumn('phong_hoc_live', 'external_meeting_code')) {
                $table->string('external_meeting_code', 120)->nullable()->after('external_meeting_url');
            }

            if (! Schema::hasColumn('phong_hoc_live', 'external_link_updated_at')) {
                $table->dateTime('external_link_updated_at')->nullable()->after('external_meeting_code');
            }

            if (! Schema::hasColumn('phong_hoc_live', 'external_link_updated_by')) {
                $table->unsignedBigInteger('external_link_updated_by')->nullable()->after('external_link_updated_at');
            }
        });

        if (! Schema::hasTable('live_room_link_histories')) {
            Schema::create('live_room_link_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('phong_hoc_live_id')->nullable();
                $table->unsignedBigInteger('lich_hoc_id')->nullable();
                $table->string('provider', 50)->default('google_meet');
                $table->string('old_url', 500)->nullable();
                $table->string('new_url', 500);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->text('reason')->nullable();
                $table->json('metadata_json')->nullable();
                $table->timestamps();

                $table->foreign('phong_hoc_live_id')->references('id')->on('phong_hoc_live')->nullOnDelete();
                $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->nullOnDelete();
                $table->foreign('updated_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
                $table->index(['phong_hoc_live_id', 'provider'], 'idx_live_room_link_history_room_provider');
                $table->index(['lich_hoc_id', 'created_at'], 'idx_live_room_link_history_schedule_time');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('live_room_link_histories');

        Schema::table('phong_hoc_live', function (Blueprint $table) {
            foreach ([
                'platform_type',
                'external_meeting_url',
                'external_meeting_code',
                'external_link_updated_at',
                'external_link_updated_by',
            ] as $column) {
                if (Schema::hasColumn('phong_hoc_live', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
