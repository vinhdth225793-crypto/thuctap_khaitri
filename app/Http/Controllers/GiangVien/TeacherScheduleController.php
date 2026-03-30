<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Services\Scheduling\TeacherScheduleViewService;
use Illuminate\Http\Request;

class TeacherScheduleController extends Controller
{
    public function __construct(
        private readonly TeacherScheduleViewService $scheduleViewService,
    ) {
    }

    public function index(Request $request)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $upcomingSchedules = $teacher->lichHocs()
            ->with(['khoaHoc', 'moduleHoc'])
            ->whereDate('ngay_hoc', '>=', today()->toDateString())
            ->where('trang_thai', '!=', 'huy')
            ->limit(10)
            ->get();

        $recentLeaveRequests = $teacher->donXinNghis()
            ->with(['khoaHoc', 'moduleHoc', 'lichHoc'])
            ->limit(8)
            ->get();

        $stats = [
            'upcoming_schedules' => $upcomingSchedules->count(),
            'leave_requests_pending' => $teacher->donXinNghis()->where('trang_thai', 'cho_duyet')->count(),
            'leave_requests_approved' => $teacher->donXinNghis()->where('trang_thai', 'da_duyet')->count(),
        ];

        return view('pages.giang-vien.lich-giang.index', [
            'teacher' => $teacher,
            'scheduleView' => $this->scheduleViewService->buildTeacherWeek($teacher->id, $request->input('week_start')),
            'upcomingSchedules' => $upcomingSchedules,
            'recentLeaveRequests' => $recentLeaveRequests,
            'stats' => $stats,
        ]);
    }
}
