<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\GiangVienLichRanh;
use App\Services\Scheduling\TeacherAvailabilityService;
use App\Services\Scheduling\TeacherScheduleViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TeacherAvailabilityController extends Controller
{
    public function __construct(
        private readonly TeacherAvailabilityService $availabilityService,
        private readonly TeacherScheduleViewService $scheduleViewService,
    ) {
    }

    public function show(Request $request, int $giangVienId)
    {
        $teacher = GiangVien::with([
            'nguoiDung',
        ])->findOrFail($giangVienId);

        $availabilityQuery = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacher->id);

        if (filled($request->trang_thai)) {
            $availabilityQuery->where('trang_thai', $request->string('trang_thai'));
        }

        if (filled($request->loai_lich_ranh)) {
            $availabilityQuery->where('loai_lich_ranh', $request->string('loai_lich_ranh'));
        }

        if (filled($request->thu_trong_tuan)) {
            $availabilityQuery->where('thu_trong_tuan', (int) $request->input('thu_trong_tuan'));
        }

        $availabilities = $availabilityQuery
            ->orderByRaw("CASE WHEN loai_lich_ranh = 'theo_tuan' THEN 0 ELSE 1 END")
            ->orderBy('thu_trong_tuan')
            ->orderBy('ngay_cu_the')
            ->orderBy('gio_bat_dau')
            ->paginate(12)
            ->withQueryString();

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

        $stats = [
            'total' => GiangVienLichRanh::where('giang_vien_id', $teacher->id)->count(),
            'active' => GiangVienLichRanh::where('giang_vien_id', $teacher->id)
                ->where('trang_thai', GiangVienLichRanh::TRANG_THAI_HOAT_DONG)
                ->count(),
            'weekly' => GiangVienLichRanh::where('giang_vien_id', $teacher->id)
                ->where('loai_lich_ranh', GiangVienLichRanh::LOAI_THEO_TUAN)
                ->count(),
            'specific' => GiangVienLichRanh::where('giang_vien_id', $teacher->id)
                ->where('loai_lich_ranh', GiangVienLichRanh::LOAI_THEO_NGAY)
                ->count(),
            'assigned_modules' => $acceptedAssignments->count(),
            'upcoming_schedules' => $upcomingSchedules->count(),
        ];

        return view('pages.admin.quan-ly-tai-khoan.giang-vien.lich-ranh', [
            'teacher' => $teacher,
            'availabilities' => $availabilities,
            'availabilityOverview' => $this->availabilityService->availabilitySummaryForTeacher($teacher->id),
            'scheduleView' => $this->scheduleViewService->buildTeacherWeek($teacher->id, $request->input('week_start')),
            'upcomingSchedules' => $upcomingSchedules,
            'acceptedAssignments' => $acceptedAssignments,
            'stats' => $stats,
            'filters' => $request->only(['trang_thai', 'loai_lich_ranh', 'thu_trong_tuan']),
        ]);
    }
}
