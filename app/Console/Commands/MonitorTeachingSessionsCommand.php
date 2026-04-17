<?php

namespace App\Console\Commands;

use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use App\Services\TeachingSessionWindowService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonitorTeachingSessionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teaching:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tu dong giam sat buoi hoc cua giang vien va canh bao vi pham.';

    public function __construct(
        private readonly TeachingSessionWindowService $windowService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting teaching session monitoring...');

        // Quét các buổi học có ngày học là hôm nay hoặc hôm qua
        $schedules = LichHoc::query()
            ->whereIn('ngay_hoc', [now()->toDateString(), now()->subDay()->toDateString()])
            ->whereNotIn('trang_thai', ['hoan_thanh', 'da_huy'])
            ->with(['giangVien.nguoiDung', 'khoaHoc', 'moduleHoc'])
            ->get();

        $violationCount = 0;

        foreach ($schedules as $schedule) {
            $violation = $this->detectViolation($schedule);
            
            if ($violation) {
                $this->processViolation($schedule, $violation);
                $violationCount++;
            }
        }

        $this->info("Monitoring completed. Processed {$violationCount} violations.");
    }

    /**
     * Phat hien vi pham cho mot buoi hoc.
     * 
     * @param LichHoc $schedule
     * @return array{status: string, note: string}|null
     */
    private function detectViolation(LichHoc $schedule): ?array
    {
        $now = now();
        $hasCheckedIn = $schedule->actual_started_at !== null;
        
        // 1. Kiem tra Khong day (Qua deadline checkout ma chua check-in)
        if ($this->windowService->shouldFlagNoShow($schedule, $hasCheckedIn, $now)) {
            // Neu da flag roi thi bo qua (tranh duplicate alert)
            if ($schedule->teacher_monitoring_status === LichHoc::TEACHER_MONITORING_KHONG_DAY) {
                return null;
            }

            return [
                'status' => LichHoc::TEACHER_MONITORING_KHONG_DAY,
                'note' => "Giao vien khong thuc hien check-in/day buoi hoc du da qua thoi han ket thuc.",
            ];
        }

        // 2. Kiem tra Chua checkout (Dang hoc nhung da qua deadline checkout)
        if ($this->windowService->shouldFlagMissingCheckout($schedule, false, $now)) {
            if ($schedule->teacher_monitoring_status === LichHoc::TEACHER_MONITORING_CHUA_CHECKOUT) {
                return null;
            }

            return [
                'status' => LichHoc::TEACHER_MONITORING_CHUA_CHECKOUT,
                'note' => "Buoi hoc dang o trang thai 'dang_hoc' nhung giao vien chua thuc hien check-out sau gio ket thuc.",
            ];
        }

        // 3. Kiem tra Vao tre (Da qua gio bat dau nhung chua check-in)
        // Lưu ý: Vao tre thuong duoc check khi giao vien check-in, nhung command nay check cho truong hop ho chua check-in luon
        if (!$hasCheckedIn && $this->windowService->isLateCheckIn($schedule, $now)) {
             if ($schedule->teacher_monitoring_status === LichHoc::TEACHER_MONITORING_VAO_TRE) {
                return null;
            }

            $lateMinutes = $this->windowService->lateMinutes($schedule, $now);
            // Chi flag vao tre neu tre hon 5 phut de tranh noise do lech giay
            if ($lateMinutes >= 5) {
                return [
                    'status' => LichHoc::TEACHER_MONITORING_VAO_TRE,
                    'note' => "Buoi hoc da bat dau duoc {$lateMinutes} phut nhung chua ghi nhan check-in.",
                ];
            }
        }

        return null;
    }

    /**
     * Xu ly vi pham: Cap nhat model va gui thong bao.
     */
    private function processViolation(LichHoc $schedule, array $violation): void
    {
        DB::transaction(function () use ($schedule, $violation) {
            $schedule->teacher_monitoring_status = $violation['status'];
            $schedule->teacher_monitoring_note = trim(implode(PHP_EOL, array_filter([
                $schedule->teacher_monitoring_note,
                $violation['note'] . " (Ghi nhan tu dong luc " . now()->format('H:i d/m/Y') . ")"
            ])));
            $schedule->teacher_monitoring_flagged_at = now();
            $schedule->save();

            $this->notifyAdmins($schedule, $violation);
        });

        $this->warn("Violation detected for Schedule #{$schedule->id}: {$violation['status']}");
    }

    /**
     * Gui thong bao cho tat ca Admin.
     */
    private function notifyAdmins(LichHoc $schedule, array $violation): void
    {
        $admins = NguoiDung::where('vai_tro', 'admin')->get();
        $teacherName = $schedule->giangVien?->nguoiDung?->ho_ten ?? 'N/A';
        $courseName = $schedule->khoaHoc?->ten_khoa_hoc ?? 'N/A';
        $timeStr = $schedule->ngay_hoc->format('d/m/Y') . " " . $schedule->gio_bat_dau . "-" . $schedule->gio_ket_thuc;

        $typeLabel = match($violation['status']) {
            LichHoc::TEACHER_MONITORING_VAO_TRE => 'Vào trễ',
            LichHoc::TEACHER_MONITORING_KHONG_DAY => 'Không dạy',
            LichHoc::TEACHER_MONITORING_CHUA_CHECKOUT => 'Chưa check-out',
            LichHoc::TEACHER_MONITORING_DONG_SOM => 'Đóng sớm',
            default => 'Bất thường',
        };

        $title = "Cảnh báo vi phạm giảng dạy: {$typeLabel}";
        $content = "Giảng viên {$teacherName} vi phạm {$typeLabel} tại buổi học {$timeStr}. Khóa học: {$courseName}. Chi tiết: {$violation['note']}";

        foreach ($admins as $admin) {
            ThongBao::create([
                'nguoi_nhan_id' => $admin->ma_nguoi_dung,
                'tieu_de' => $title,
                'noi_dung' => $content,
                'loai' => 'he_thong',
                'url' => route('admin.khoa-hoc.lich-hoc.edit', ['khoaHocId' => $schedule->khoa_hoc_id, 'id' => $schedule->id]),
                'da_doc' => false,
            ]);
        }
    }
}
