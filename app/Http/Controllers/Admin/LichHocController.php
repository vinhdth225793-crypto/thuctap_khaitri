<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminScheduleRequest;
use App\Http\Requests\StoreAutoAdminScheduleRequest;
use App\Http\Requests\UpdateAdminScheduleRequest;
use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Services\Scheduling\AdminSchedulePlanningService;
use App\Services\LearningProgressStatusService;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LichHocController extends Controller
{
    public function __construct(
        private readonly AdminSchedulePlanningService $planningService,
        private readonly LearningProgressStatusService $learningProgressStatusService,
    ) {
    }

    public function index(int $khoaHocId)
    {
        $khoaHoc = KhoaHoc::with([
            'nhomNganh',
            'lichHocs',
            'moduleHocs.lichHocs.giangVien.nguoiDung',
            'moduleHocs.phanCongGiangViens.giangVien.nguoiDung',
            'moduleHocs.phanCongGiangViens.giangVien.donXinNghis',
        ])->findOrFail($khoaHocId);

        foreach ($khoaHoc->moduleHocs as $module) {
            $lastSession = $module->lichHocs->last();
            $module->ngay_ket_thuc_thuc_te = $lastSession ? $lastSession->ngay_hoc->format('Y-m-d') : null;
            $module->assignedTeachers = $module->phanCongGiangViens
                ->where('trang_thai', 'da_nhan')
                ->values();
        }

        $giangViens = GiangVien::with(['nguoiDung'])
            ->whereHas('nguoiDung', fn ($query) => $query->where('trang_thai', 1))
            ->get();

        return view('pages.admin.lich-hoc.index', compact('khoaHoc', 'giangViens'));
    }

    public function teacherPlanningContext(Request $request, int $khoaHocId)
    {
        $validated = $request->validate([
            'module_hoc_id' => 'required|integer|exists:module_hoc,id',
            'ngay_hoc' => 'required|date',
            'selected_tiets' => 'nullable|array|min:1',
            'selected_tiets.*' => 'integer|between:1,12',
            'tiet_bat_dau' => 'nullable|integer|between:1,12',
            'tiet_ket_thuc' => 'nullable|integer|between:1,12|gte:tiet_bat_dau',
            'buoi_hoc' => 'nullable|in:sang,chieu,toi',
            'gio_bat_dau' => 'nullable|date_format:H:i',
            'gio_ket_thuc' => 'nullable|date_format:H:i|after:gio_bat_dau',
            'giang_vien_id' => 'nullable|integer|exists:giang_vien,id',
            'ignore_lich_hoc_id' => 'nullable|integer|exists:lich_hoc,id',
        ]);

        $range = TeachingPeriodCatalog::normalizeSelectedPeriods((array) $request->input('selected_tiets', []))
            ?? TeachingPeriodCatalog::normalizeRange(
                $request->filled('tiet_bat_dau') ? (int) $request->input('tiet_bat_dau') : null,
                $request->filled('tiet_ket_thuc') ? (int) $request->input('tiet_ket_thuc') : null,
                $request->input('buoi_hoc'),
            );

        if ($range !== null) {
            $times = TeachingPeriodCatalog::timeRangeFromPeriods($range['start'], $range['end']);
            $validated['tiet_bat_dau'] = $range['start'];
            $validated['tiet_ket_thuc'] = $range['end'];
            $validated['buoi_hoc'] = $range['session'];
            $validated['gio_bat_dau'] = $validated['gio_bat_dau'] ?? $times['start_time'];
            $validated['gio_ket_thuc'] = $validated['gio_ket_thuc'] ?? $times['end_time'];
        }

        if (blank($validated['gio_bat_dau'] ?? null) || blank($validated['gio_ket_thuc'] ?? null)) {
            return response()->json([
                'message' => 'Can chon tiet hoc hoac khung gio de he thong phan tich.',
                'errors' => [
                    'selected_tiets' => ['Can chon tiet hoc hoac khung gio de he thong phan tich.'],
                ],
            ], 422);
        }

        $ignoreScheduleId = $request->integer('ignore_lich_hoc_id') ?: null;
        if ($ignoreScheduleId !== null) {
            $this->findCourseSchedule($khoaHocId, $ignoreScheduleId);
        }

        $context = $this->planningService->inspect($khoaHocId, $validated, $ignoreScheduleId);

        return response()->json([
            'can_schedule' => $context['can_schedule'],
            'teacher_name' => $context['teacher']?->nguoiDung?->ho_ten,
            'assignment' => $context['assignment'],
            'teaching_window' => $context['teaching_window'],
            'standard_window' => $context['standard_window'],
            'leave_requests' => $context['leave_requests'],
            'conflicts' => $context['conflicts'],
            'suggestions' => $context['suggestions'],
            'errors' => $context['errors'],
        ]);
    }

    public function updateSoBuoiModule(Request $request, int $khoaHocId, int $moduleId)
    {
        $request->validate([
            'so_buoi' => 'required|integer|min:1|max:100',
        ]);

        $module = ModuleHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail($moduleId);
        $module->update(['so_buoi' => $request->integer('so_buoi')]);

        return back()->with('success', "Da cap nhat so buoi cho module {$module->ten_module}.");
    }

    public function store(StoreAdminScheduleRequest $request, int $khoaHocId)
    {
        $validated = $request->validated();

        $planningContext = $this->planningService->inspect($khoaHocId, $validated);
        if (!$planningContext['can_schedule']) {
            return back()
                ->withErrors($planningContext['errors'])
                ->withInput()
                ->with('error', $this->buildPlanningErrorMessage($planningContext));
        }

        $module = ModuleHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail((int) $validated['module_hoc_id']);
        $buoiSo = $module->lichHocs()->count() + 1;
        $date = Carbon::parse($validated['ngay_hoc']);

        LichHoc::create($this->prepareSchedulePayload($validated, $khoaHocId, $date, $buoiSo));

        return back()->with('success', 'Da them buoi hoc moi thanh cong.');
    }

    public function storeAuto(StoreAutoAdminScheduleRequest $request, int $khoaHocId)
    {
        $validated = $request->validated();
        $module = ModuleHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail((int) $validated['module_hoc_id']);
        $soBuoiQuyDinh = (int) $module->so_buoi;

        DB::beginTransaction();

        try {
            $module->lichHocs()->where('trang_thai', 'cho')->delete();

            $soBuoiDaCo = $module->lichHocs()->count();
            $soBuoiCanTao = max(0, $soBuoiQuyDinh - $soBuoiDaCo);

            if ($soBuoiCanTao <= 0) {
                $this->learningProgressStatusService->syncCourseStatus($khoaHocId);
                DB::commit();

                return back()->with('info', 'So buoi hien tai da du hoac vuot muc quy dinh. Khong can sinh them.');
            }

            $currentDate = Carbon::parse($validated['ngay_bat_dau']);
            $stopDate = $currentDate->copy()->addYears(2);
            $daysOfWeek = array_map('intval', (array) $validated['thu_trong_tuan']);
            $createdCount = 0;

            while ($createdCount < $soBuoiCanTao && $currentDate->lessThan($stopDate)) {
                $dbDay = $this->resolveThuTrongTuan($currentDate);

                if (in_array($dbDay, $daysOfWeek, true)) {
                    $singlePayload = [
                        'module_hoc_id' => $module->id,
                        'ngay_hoc' => $currentDate->toDateString(),
                        'gio_bat_dau' => $validated['gio_bat_dau'],
                        'gio_ket_thuc' => $validated['gio_ket_thuc'],
                        'tiet_bat_dau' => $validated['tiet_bat_dau'] ?? null,
                        'tiet_ket_thuc' => $validated['tiet_ket_thuc'] ?? null,
                        'buoi_hoc' => $validated['buoi_hoc'] ?? null,
                        'phong_hoc' => $validated['phong_hoc'] ?? null,
                        'hinh_thuc' => $validated['hinh_thuc'],
                        'giang_vien_id' => $validated['giang_vien_id'] ?? null,
                        'ghi_chu' => $validated['ghi_chu'] ?? null,
                    ];

                    $planningContext = $this->planningService->inspect($khoaHocId, $singlePayload);
                    if (!$planningContext['can_schedule']) {
                        DB::rollBack();

                        return back()
                            ->withInput()
                            ->with('error', 'Khong the sinh lich vao ngay ' . $currentDate->format('d/m/Y') . ': ' . $this->buildPlanningErrorMessage($planningContext));
                    }

                    LichHoc::create($this->prepareSchedulePayload(
                        $singlePayload,
                        $khoaHocId,
                        $currentDate,
                        $soBuoiDaCo + $createdCount + 1,
                    ));

                    $createdCount++;
                }

                $currentDate->addDay();
            }

            $this->learningProgressStatusService->syncCourseStatus($khoaHocId);
            DB::commit();

            return back()->with('success', "Da tu dong sinh {$createdCount} buoi hoc moi cho module.");
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->with('error', 'Loi he thong: ' . $exception->getMessage());
        }
    }

    public function edit(int $khoaHocId, int $id)
    {
        $lichHoc = $this->findCourseSchedule($khoaHocId, $id, [
            'moduleHoc.phanCongGiangViens.giangVien.nguoiDung',
            'moduleHoc.phanCongGiangViens.giangVien.donXinNghis',
            'khoaHoc',
            'giangVien.nguoiDung',
            'giangVien.donXinNghis',
        ]);

        $teacherOptions = $this->buildTeacherOptionsForSchedule($lichHoc);

        return view('pages.admin.lich-hoc.edit', [
            'lichHoc' => $lichHoc,
            'teacherOptions' => $teacherOptions,
            'planningContext' => $lichHoc->giang_vien_id
                ? $this->planningService->inspect($khoaHocId, [
                    'module_hoc_id' => $lichHoc->module_hoc_id,
                    'ngay_hoc' => $lichHoc->ngay_hoc?->toDateString(),
                    'gio_bat_dau' => substr((string) $lichHoc->gio_bat_dau, 0, 5),
                    'gio_ket_thuc' => substr((string) $lichHoc->gio_ket_thuc, 0, 5),
                    'tiet_bat_dau' => $lichHoc->tiet_bat_dau,
                    'tiet_ket_thuc' => $lichHoc->tiet_ket_thuc,
                    'buoi_hoc' => $lichHoc->buoi_hoc,
                    'giang_vien_id' => $lichHoc->giang_vien_id,
                ], $lichHoc->id)
                : null,
        ]);
    }

    public function update(UpdateAdminScheduleRequest $request, int $khoaHocId, int $id)
    {
        $lichHoc = $this->findCourseSchedule($khoaHocId, $id);
        $validated = $request->validated();

        if (($validated['trang_thai'] ?? 'cho') !== 'huy') {
            $planningContext = $this->planningService->inspect($khoaHocId, [
                'module_hoc_id' => $lichHoc->module_hoc_id,
                'ngay_hoc' => $validated['ngay_hoc'],
                'gio_bat_dau' => $validated['gio_bat_dau'],
                'gio_ket_thuc' => $validated['gio_ket_thuc'],
                'tiet_bat_dau' => $validated['tiet_bat_dau'] ?? null,
                'tiet_ket_thuc' => $validated['tiet_ket_thuc'] ?? null,
                'buoi_hoc' => $validated['buoi_hoc'] ?? null,
                'giang_vien_id' => $validated['giang_vien_id'] ?? null,
            ], $lichHoc->id);

            if (!$planningContext['can_schedule']) {
                return back()
                    ->withErrors($planningContext['errors'])
                    ->withInput()
                    ->with('error', $this->buildPlanningErrorMessage($planningContext));
            }
        }

        $date = Carbon::parse($validated['ngay_hoc']);
        $data = $this->prepareSchedulePayload($validated, $khoaHocId, $date, $lichHoc->buoi_so);
        $data['trang_thai'] = $validated['trang_thai'];

        DB::beginTransaction();

        try {
            $lichHoc->update($data);

            if (($validated['hinh_thuc'] ?? null) === 'online' && $request->boolean('apply_to_all_online')) {
                LichHoc::where('khoa_hoc_id', $khoaHocId)
                    ->where('hinh_thuc', 'online')
                    ->update([
                        'link_online' => $data['link_online'],
                    ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.khoa-hoc.lich-hoc.index', $khoaHocId)
                ->with('success', 'Da cap nhat lich hoc.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->with('error', 'Loi: ' . $exception->getMessage());
        }
    }

    public function destroy(int $khoaHocId, int $id)
    {
        $lichHoc = $this->findCourseSchedule($khoaHocId, $id);
        $lichHoc->delete();

        return back()->with('success', 'Da xoa buoi hoc.');
    }

    public function destroyModuleSchedules(Request $request, int $khoaHocId, int $moduleId)
    {
        $deleted = LichHoc::where('khoa_hoc_id', $khoaHocId)
            ->where('module_hoc_id', $moduleId)
            ->where('trang_thai', 'cho')
            ->delete();

        if ($deleted > 0) {
            $this->learningProgressStatusService->syncCourseStatus($khoaHocId);
        }

        return back()->with('success', "Da xoa {$deleted} buoi hoc cua module.");
    }

    public function destroyBulk(Request $request, int $khoaHocId)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Vui long chon it nhat mot buoi hoc de xoa.');
        }

        $deleted = LichHoc::whereIn('id', $ids)
            ->where('khoa_hoc_id', $khoaHocId)
            ->where('trang_thai', 'cho')
            ->delete();

        if ($deleted > 0) {
            $this->learningProgressStatusService->syncCourseStatus($khoaHocId);
        }

        return back()->with('success', "Da xoa {$deleted} buoi hoc da chon.");
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareSchedulePayload(array $data, int $courseId, Carbon $date, int $sessionNumber): array
    {
        $locationValue = $data['phong_hoc'] ?? null;
        $isOnline = ($data['hinh_thuc'] ?? 'truc_tiep') === 'online';

        return [
            'khoa_hoc_id' => $courseId,
            'module_hoc_id' => (int) $data['module_hoc_id'],
            'giang_vien_id' => filled($data['giang_vien_id'] ?? null) ? (int) $data['giang_vien_id'] : null,
            'ngay_hoc' => $date->toDateString(),
            'gio_bat_dau' => substr((string) $data['gio_bat_dau'], 0, 5),
            'gio_ket_thuc' => substr((string) $data['gio_ket_thuc'], 0, 5),
            'tiet_bat_dau' => isset($data['tiet_bat_dau']) ? (int) $data['tiet_bat_dau'] : null,
            'tiet_ket_thuc' => isset($data['tiet_ket_thuc']) ? (int) $data['tiet_ket_thuc'] : null,
            'buoi_hoc' => $data['buoi_hoc'] ?? null,
            'thu_trong_tuan' => $this->resolveThuTrongTuan($date),
            'buoi_so' => $sessionNumber,
            'phong_hoc' => !$isOnline ? $locationValue : null,
            'hinh_thuc' => $data['hinh_thuc'],
            'link_online' => $isOnline ? $locationValue : null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
            'trang_thai' => $data['trang_thai'] ?? 'cho',
        ];
    }

    private function resolveThuTrongTuan(Carbon $date): int
    {
        return $date->dayOfWeek === Carbon::SUNDAY ? 8 : ($date->dayOfWeek + 1);
    }

    /**
     * @param  array<int, string>  $with
     */
    private function findCourseSchedule(int $courseId, int $scheduleId, array $with = []): LichHoc
    {
        $query = LichHoc::query()
            ->where('khoa_hoc_id', $courseId);

        if ($with !== []) {
            $query->with($with);
        }

        return $query->findOrFail($scheduleId);
    }

    private function buildTeacherOptionsForSchedule(LichHoc $schedule)
    {
        $teachers = $schedule->moduleHoc->phanCongGiangViens
            ->where('trang_thai', 'da_nhan')
            ->map(fn ($assignment) => $assignment->giangVien)
            ->filter(fn ($teacher) => $teacher?->nguoiDung?->trang_thai)
            ->unique('id')
            ->values();

        $currentTeacher = $schedule->giangVien;
        if ($currentTeacher && !$teachers->contains(fn (GiangVien $teacher) => $teacher->id === $currentTeacher->id)) {
            $teachers->push($currentTeacher);
        }

        return $teachers->values();
    }

    /**
     * @param  array<string, mixed>  $planningContext
     */
    private function buildPlanningErrorMessage(array $planningContext): string
    {
        $errors = array_values((array) ($planningContext['errors'] ?? []));
        if ($errors !== []) {
            return (string) $errors[0];
        }

        if (!empty($planningContext['conflicts']['message'])) {
            return (string) $planningContext['conflicts']['message'];
        }

        if (!empty($planningContext['leave_requests']['message']) && ($planningContext['leave_requests']['ok'] ?? null) === false) {
            return (string) $planningContext['leave_requests']['message'];
        }

        if (!empty($planningContext['standard_window']['message']) && ($planningContext['standard_window']['ok'] ?? null) === false) {
            return (string) $planningContext['standard_window']['message'];
        }

        return 'Khong the luu lich hoc voi du lieu hien tai.';
    }
}






