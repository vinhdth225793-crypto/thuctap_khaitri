<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\LichHoc;
use App\Models\BaiGiang;
use App\Services\AttendanceReportService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $attendanceReportService,
    ) {
    }

    public function index(Request $request)
    {
        $activeTab = in_array($request->query('tab'), ['giang-vien', 'hoc-vien'], true)
            ? $request->query('tab')
            : 'giang-vien';

        $filterOptions = $this->attendanceReportService->filterOptions();
        $filters = $request->only(['khoa_hoc_id', 'giang_vien_id', 'lich_hoc_id', 'ngay_hoc', 'trang_thai', 'week_start']);

        $teacherAttendances = null;
        $teacherWeeklyDashboard = null;
        $studentAttendances = null;

        if ($activeTab === 'giang-vien') {
            $teacherWeeklyDashboard = $this->attendanceReportService->teacherWeeklyDashboard($filters);
        } else {
            $studentAttendances = $this->attendanceReportService->studentAttendanceReport($filters);
        }

        $scheduleOptions = LichHoc::query()
            ->with('moduleHoc')
            ->when(filled($filters['khoa_hoc_id'] ?? null), fn ($query) => $query->where('khoa_hoc_id', (int) $filters['khoa_hoc_id']))
            ->orderByDesc('ngay_hoc')
            ->orderByDesc('gio_bat_dau')
            ->limit(200)
            ->get();

        return view('pages.admin.diem-danh.index', [
            'activeTab' => $activeTab,
            'teacherAttendances' => $teacherAttendances,
            'teacherWeeklyDashboard' => $teacherWeeklyDashboard,
            'studentAttendances' => $studentAttendances,
            'courses' => $filterOptions['courses'],
            'teachers' => $filterOptions['teachers'],
            'scheduleOptions' => $scheduleOptions,
            'filters' => $filters,
        ]);
    }

    public function showTeacherAttendance(Request $request, LichHoc $lichHoc, GiangVien $giangVien)
    {
        $schedule = $this->attendanceReportService->teacherAttendanceDetail($lichHoc, $giangVien);
        $backLinkParams = array_filter(
            array_merge(
                ['tab' => 'giang-vien'],
                $request->only(['week_start', 'khoa_hoc_id', 'giang_vien_id', 'trang_thai'])
            ),
            fn ($value) => filled($value)
        );

        $liveLectures = $schedule->baiGiangs()
            ->where('loai_bai_giang', BaiGiang::TYPE_LIVE)
            ->with(['phongHocLive'])
            ->get();

        $studentAttendances = $schedule->diemDanhs()
            ->with(['hocVien.nguoiDung'])
            ->get()
            ->filter(fn($item) => $item->hocVien !== null)
            ->sortBy(fn($item) => $item->hocVien->nguoiDung->ho_ten ?? '');

        return view('pages.admin.diem-danh.teacher-show', [
            'schedule' => $schedule,
            'teacher' => $giangVien->load('nguoiDung'),
            'attendance' => $schedule->teacherAttendanceLogs->first(),
            'liveLectures' => $liveLectures,
            'studentAttendances' => $studentAttendances,
            'backLinkParams' => $backLinkParams,
        ]);
    }

    public function resolveMonitoring($lichHocId)
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $lichHoc->update([
            'teacher_monitoring_status' => 'binh_thuong',
            'teacher_monitoring_note' => $lichHoc->teacher_monitoring_note . "\nAdmin đã xác nhận xử lý lúc " . now()->format('H:i d/m/Y'),
        ]);

        // Đánh dấu tất cả alerts của buổi này là resolved
        $lichHoc->teachingSessionAlerts()->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Đã xác nhận xử lý vi phạm cho buổi học này.');
    }
}
