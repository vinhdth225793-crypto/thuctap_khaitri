<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lich_hoc')) {
            Schema::table('lich_hoc', function (Blueprint $table) {
                if (! Schema::hasColumn('lich_hoc', 'actual_started_at')) {
                    $table->dateTime('actual_started_at')->nullable();
                }

                if (! Schema::hasColumn('lich_hoc', 'actual_finished_at')) {
                    $table->dateTime('actual_finished_at')->nullable();
                }

                if (! Schema::hasColumn('lich_hoc', 'online_link_source')) {
                    $table->string('online_link_source', 50)->nullable();
                }

                if (! Schema::hasColumn('lich_hoc', 'teacher_monitoring_status')) {
                    $table->string('teacher_monitoring_status', 50)->default('binh_thuong');
                }

                if (! Schema::hasColumn('lich_hoc', 'teacher_monitoring_note')) {
                    $table->text('teacher_monitoring_note')->nullable();
                }

                if (! Schema::hasColumn('lich_hoc', 'teacher_monitoring_flagged_at')) {
                    $table->dateTime('teacher_monitoring_flagged_at')->nullable();
                }

                if (! Schema::hasColumn('lich_hoc', 'allow_open_before_minutes')) {
                    $table->unsignedSmallInteger('allow_open_before_minutes')->default(30);
                }

                if (! Schema::hasColumn('lich_hoc', 'allow_close_after_minutes')) {
                    $table->unsignedSmallInteger('allow_close_after_minutes')->default(60);
                }

                if (! Schema::hasColumn('lich_hoc', 'attendance_remind_after_finish_minutes')) {
                    $table->unsignedSmallInteger('attendance_remind_after_finish_minutes')->default(15);
                }

                if (! Schema::hasColumn('lich_hoc', 'attendance_deadline_at')) {
                    $table->dateTime('attendance_deadline_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('diem_danh_giang_vien')) {
            Schema::table('diem_danh_giang_vien', function (Blueprint $table) {
                if (! Schema::hasColumn('diem_danh_giang_vien', 'expected_start_at')) {
                    $table->dateTime('expected_start_at')->nullable();
                }

                if (! Schema::hasColumn('diem_danh_giang_vien', 'expected_end_at')) {
                    $table->dateTime('expected_end_at')->nullable();
                }

                if (! Schema::hasColumn('diem_danh_giang_vien', 'check_in_status')) {
                    $table->string('check_in_status', 50)->nullable();
                }

                if (! Schema::hasColumn('diem_danh_giang_vien', 'check_out_status')) {
                    $table->string('check_out_status', 50)->nullable();
                }

                if (! Schema::hasColumn('diem_danh_giang_vien', 'late_minutes')) {
                    $table->unsignedInteger('late_minutes')->nullable();
                }

                if (! Schema::hasColumn('diem_danh_giang_vien', 'early_leave_minutes')) {
                    $table->unsignedInteger('early_leave_minutes')->nullable();
                }

                if (! Schema::hasColumn('diem_danh_giang_vien', 'flag_reason')) {
                    $table->string('flag_reason', 100)->nullable();
                }

                if (! Schema::hasColumn('diem_danh_giang_vien', 'flagged_at')) {
                    $table->dateTime('flagged_at')->nullable();
                }
            });
        }

        if (! Schema::hasTable('teaching_session_alerts')) {
            Schema::create('teaching_session_alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lich_hoc_id');
                $table->unsignedBigInteger('giang_vien_id')->nullable();
                $table->string('alert_key', 160)->unique();
                $table->string('alert_type', 50);
                $table->string('severity', 30)->default('warning');
                $table->string('status', 30)->default('open');
                $table->string('tieu_de', 255);
                $table->text('noi_dung')->nullable();
                $table->json('metadata')->nullable();
                $table->dateTime('notified_admin_at')->nullable();
                $table->dateTime('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['lich_hoc_id', 'alert_type'], 'idx_teaching_alert_schedule_type');
                $table->index(['giang_vien_id', 'status'], 'idx_teaching_alert_teacher_status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_session_alerts');

        $attendanceColumns = [
            'expected_start_at',
            'expected_end_at',
            'check_in_status',
            'check_out_status',
            'late_minutes',
            'early_leave_minutes',
            'flag_reason',
            'flagged_at',
        ];

        if (Schema::hasTable('diem_danh_giang_vien')) {
            $existingColumns = array_values(array_filter(
                $attendanceColumns,
                fn (string $column): bool => Schema::hasColumn('diem_danh_giang_vien', $column)
            ));

            if ($existingColumns !== []) {
                Schema::table('diem_danh_giang_vien', function (Blueprint $table) use ($existingColumns) {
                    $table->dropColumn($existingColumns);
                });
            }
        }

        $scheduleColumns = [
            'actual_started_at',
            'actual_finished_at',
            'online_link_source',
            'teacher_monitoring_status',
            'teacher_monitoring_note',
            'teacher_monitoring_flagged_at',
            'allow_open_before_minutes',
            'allow_close_after_minutes',
            'attendance_remind_after_finish_minutes',
            'attendance_deadline_at',
        ];

        if (Schema::hasTable('lich_hoc')) {
            $existingColumns = array_values(array_filter(
                $scheduleColumns,
                fn (string $column): bool => Schema::hasColumn('lich_hoc', $column)
            ));

            if ($existingColumns !== []) {
                Schema::table('lich_hoc', function (Blueprint $table) use ($existingColumns) {
                    $table->dropColumn($existingColumns);
                });
            }
        }
    }
};
