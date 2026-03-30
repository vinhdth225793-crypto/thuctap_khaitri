<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherLeaveRequest;
use App\Models\LichHoc;
use App\Services\Scheduling\TeacherLeaveRequestService;
use Illuminate\Http\Request;

class TeacherLeaveRequestController extends Controller
{
    public function __construct(
        private readonly TeacherLeaveRequestService $leaveRequestService,
    ) {
    }

    public function index(Request $request)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $query = $teacher->donXinNghis()->with(['khoaHoc', 'moduleHoc', 'lichHoc', 'nguoiDuyet']);

        if (filled($request->trang_thai)) {
            $query->where('trang_thai', $request->string('trang_thai'));
        }

        $leaveRequests = $query->paginate(10)->withQueryString();

        return view('pages.giang-vien.don-xin-nghi.index', [
            'teacher' => $teacher,
            'leaveRequests' => $leaveRequests,
            'filters' => $request->only('trang_thai'),
        ]);
    }

    public function create(Request $request)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $selectedSchedule = null;
        if ($request->filled('lich_hoc_id')) {
            $selectedSchedule = LichHoc::query()
                ->with(['khoaHoc', 'moduleHoc'])
                ->where('giang_vien_id', $teacher->id)
                ->findOrFail((int) $request->input('lich_hoc_id'));
        }

        $upcomingSchedules = $teacher->lichHocs()
            ->with(['khoaHoc', 'moduleHoc'])
            ->whereDate('ngay_hoc', '>=', today()->toDateString())
            ->where('trang_thai', '!=', 'huy')
            ->limit(20)
            ->get();

        return view('pages.giang-vien.don-xin-nghi.form', [
            'teacher' => $teacher,
            'selectedSchedule' => $selectedSchedule,
            'upcomingSchedules' => $upcomingSchedules,
        ]);
    }

    public function store(StoreTeacherLeaveRequest $request)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $this->leaveRequestService->createForTeacher($teacher, $request->validated());

        return redirect()
            ->route('giang-vien.don-xin-nghi.index')
            ->with('success', 'Da gui don xin nghi cho admin duyet.');
    }
}
