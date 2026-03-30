<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Services\Scheduling\TeacherScheduleViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TeacherScheduleController extends Controller
{
    public function __construct(
        private readonly TeacherScheduleViewService $scheduleViewService,
    ) {
    }

    public function show(Request $request, int $giangVienId)
    {
        $teacher = GiangVien::with(['nguoiDung'])->findOrFail($giangVienId);

        $upcomingSchedules = $teacher->lichHocs()
            ->with(['moduleHoc.khoaHoc'])
            ->whereDate('ngay_hoc', '>=', Carbon::today()->toDateString())
            ->where('trang_thai', '!=', 'huy')
            ->limit(10)
            ->get();

        $acceptedAssignments = $teacher->phanCongModules()
            ->with(['moduleHoc.khoaHoc'])
            ->where('trang_thai', 'da_nhan')
            ->latest('ngay_phan_cong')
            ->get()
            ->unique(fn ($assignment) => ($assignment->khoa_hoc_id ?? 'course') . '-' . ($assignment->module_hoc_id ?? 'module'))
            ->values();

        $recentLeaveRequests = $teacher->donXinNghis()
            ->with(['khoaHoc', 'moduleHoc', 'lichHoc', 'nguoiDuyet'])
            ->limit(10)
            ->get();

        $stats = [
            'assigned_modules' => $acceptedAssignments->count(),
            'upcoming_schedules' => $upcomingSchedules->count(),
            'leave_requests_total' => $teacher->donXinNghis()->count(),
            'leave_requests_pending' => $teacher->donXinNghis()->where('trang_thai', 'cho_duyet')->count(),
            'leave_requests_approved' => $teacher->donXinNghis()->where('trang_thai', 'da_duyet')->count(),
        ];

        return view('pages.admin.quan-ly-tai-khoan.giang-vien.lich-giang', [
            'teacher' => $teacher,
            'scheduleView' => $this->scheduleViewService->buildTeacherWeek($teacher->id, $request->input('week_start')),
            'upcomingSchedules' => $upcomingSchedules,
            'acceptedAssignments' => $acceptedAssignments,
            'recentLeaveRequests' => $recentLeaveRequests,
            'stats' => $stats,
        ]);
    }
}
