<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\LichHoc;
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
        $filters = $request->only(['khoa_hoc_id', 'giang_vien_id', 'lich_hoc_id', 'ngay_hoc', 'trang_thai']);

        $teacherAttendances = null;
        $studentAttendances = null;

        if ($activeTab === 'giang-vien') {
            $teacherAttendances = $this->attendanceReportService->teacherAttendanceReport($filters);
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
            'studentAttendances' => $studentAttendances,
            'courses' => $filterOptions['courses'],
            'teachers' => $filterOptions['teachers'],
            'scheduleOptions' => $scheduleOptions,
            'filters' => $filters,
        ]);
    }

    public function showTeacherAttendance(LichHoc $lichHoc, GiangVien $giangVien)
    {
        $schedule = $this->attendanceReportService->teacherAttendanceDetail($lichHoc, $giangVien);

        return view('pages.admin.diem-danh.teacher-show', [
            'schedule' => $schedule,
            'teacher' => $giangVien->load('nguoiDung'),
            'attendance' => $schedule->teacherAttendanceLogs->first(),
        ]);
    }
}
