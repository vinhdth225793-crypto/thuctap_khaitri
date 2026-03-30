<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertTeacherAvailabilityRequest;
use App\Models\GiangVienLichRanh;
use App\Services\Scheduling\TeacherAvailabilityService;
use App\Services\Scheduling\TeacherScheduleViewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly TeacherAvailabilityService $availabilityService,
        private readonly TeacherScheduleViewService $scheduleViewService,
    ) {
    }

    public function index(Request $request)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $query = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacher->id);

        if (filled($request->trang_thai)) {
            $query->where('trang_thai', $request->string('trang_thai'));
        }

        if (filled($request->loai_lich_ranh)) {
            $query->where('loai_lich_ranh', $request->string('loai_lich_ranh'));
        }

        if (filled($request->thu_trong_tuan)) {
            $query->where('thu_trong_tuan', (int) $request->input('thu_trong_tuan'));
        }

        $availabilities = $query
            ->orderByRaw("CASE WHEN loai_lich_ranh = 'theo_tuan' THEN 0 ELSE 1 END")
            ->orderBy('thu_trong_tuan')
            ->orderBy('ngay_cu_the')
            ->orderBy('gio_bat_dau')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => GiangVienLichRanh::where('giang_vien_id', $teacher->id)->count(),
            'active' => GiangVienLichRanh::where('giang_vien_id', $teacher->id)->where('trang_thai', GiangVienLichRanh::TRANG_THAI_HOAT_DONG)->count(),
            'weekly' => GiangVienLichRanh::where('giang_vien_id', $teacher->id)->where('loai_lich_ranh', GiangVienLichRanh::LOAI_THEO_TUAN)->count(),
            'specific' => GiangVienLichRanh::where('giang_vien_id', $teacher->id)->where('loai_lich_ranh', GiangVienLichRanh::LOAI_THEO_NGAY)->count(),
        ];

        return view('pages.giang-vien.lich-ranh.index', [
            'teacher' => $teacher,
            'availabilities' => $availabilities,
            'availabilityOverview' => $this->availabilityService->availabilitySummaryForTeacher($teacher->id),
            'scheduleView' => $this->scheduleViewService->buildTeacherWeek($teacher->id, $request->input('week_start')),
            'stats' => $stats,
            'filters' => $request->only(['trang_thai', 'loai_lich_ranh', 'thu_trong_tuan']),
        ]);
    }

    public function create()
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        return view('pages.giang-vien.lich-ranh.form', [
            'teacher' => $teacher,
            'availability' => new GiangVienLichRanh([
                'loai_lich_ranh' => GiangVienLichRanh::LOAI_THEO_TUAN,
                'trang_thai' => GiangVienLichRanh::TRANG_THAI_HOAT_DONG,
            ]),
            'title' => 'Them lich giang',
            'formAction' => route('giang-vien.lich-ranh.store'),
            'formMethod' => 'POST',
            'impactedSchedules' => collect(),
        ]);
    }

    public function store(UpsertTeacherAvailabilityRequest $request)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $validated = $request->validated();
        $validated['trang_thai'] = $validated['trang_thai'] ?? GiangVienLichRanh::TRANG_THAI_HOAT_DONG;

        $payloads = $this->buildAvailabilityPayloads($validated);

        DB::transaction(function () use ($teacher, $payloads) {
            foreach ($payloads as $payload) {
                $teacher->lichRanh()->create($payload);
            }
        });

        return redirect()
            ->route('giang-vien.lich-ranh.index')
            ->with('success', count($payloads) > 1
                ? 'Da luu lich giang cho ' . count($payloads) . ' ngay da chon.'
                : 'Da luu lich giang moi.');
    }

    public function edit(int $id)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $availability = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacher->id)
            ->findOrFail($id);

        return view('pages.giang-vien.lich-ranh.form', [
            'teacher' => $teacher,
            'availability' => $availability,
            'title' => 'Cap nhat lich giang',
            'formAction' => route('giang-vien.lich-ranh.update', $availability->id),
            'formMethod' => 'PUT',
            'impactedSchedules' => $this->availabilityService->findImpactedSchedulesAfterChange($availability),
        ]);
    }

    public function update(UpsertTeacherAvailabilityRequest $request, int $id)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $availability = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacher->id)
            ->findOrFail($id);

        $validated = $request->validated();
        $validated['trang_thai'] = $validated['trang_thai'] ?? $availability->trang_thai;
        $validated['ngay_cu_the'] = $validated['ngay_cu_the'] ?? (($validated['ngay_ap_dung'][0] ?? null) ?: $availability->ngay_cu_the?->toDateString());

        $overlaps = $this->availabilityService->findOverlappingAvailabilities(
            $teacher->id,
            (string) $validated['loai_lich_ranh'],
            isset($validated['thu_trong_tuan']) ? (int) $validated['thu_trong_tuan'] : null,
            $validated['ngay_cu_the'] ?? null,
            (string) $validated['gio_bat_dau'],
            (string) $validated['gio_ket_thuc'],
            $availability->id,
        );

        if ($overlaps->isNotEmpty()) {
            return back()
                ->withErrors(['gio_bat_dau' => 'Khung lich giang nay bi chong lan voi mot lich da co.'])
                ->withInput();
        }

        $impactedSchedules = $this->availabilityService->findImpactedSchedulesAfterChange($availability, $validated);
        $availability->update($validated);

        $message = 'Da cap nhat lich giang.';
        if ($impactedSchedules->isNotEmpty()) {
            $message .= ' Canh bao: ' . $impactedSchedules->count() . ' buoi hoc tuong lai khong con nam trong lich giang vua cap nhat.';
        }

        return redirect()
            ->route('giang-vien.lich-ranh.index')
            ->with($impactedSchedules->isNotEmpty() ? 'error' : 'success', $message);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, array<string, mixed>>
     */
    private function buildAvailabilityPayloads(array $validated): array
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $dates = [];
        if (($validated['loai_lich_ranh'] ?? null) === GiangVienLichRanh::LOAI_THEO_NGAY) {
            $dates = array_values(array_unique(array_filter((array) ($validated['ngay_ap_dung'] ?? [$validated['ngay_cu_the'] ?? null]))));
        }

        if ($dates === []) {
            $dates = [$validated['ngay_cu_the'] ?? null];
        }

        $payloads = [];
        foreach ($dates as $date) {
            $payload = $validated;
            $payload['ngay_cu_the'] = $date;

            $overlaps = $this->availabilityService->findOverlappingAvailabilities(
                $teacher->id,
                (string) $payload['loai_lich_ranh'],
                isset($payload['thu_trong_tuan']) ? (int) $payload['thu_trong_tuan'] : null,
                $payload['ngay_cu_the'] ?? null,
                (string) $payload['gio_bat_dau'],
                (string) $payload['gio_ket_thuc'],
            );

            if ($overlaps->isNotEmpty()) {
                $label = filled($date) ? (' ngay ' . date('d/m/Y', strtotime((string) $date))) : '';
                throw ValidationException::withMessages([
                    'selected_tiets' => 'Khung tiet dang ky' . $label . ' bi chong lan voi mot lich da co.',
                    'gio_bat_dau' => 'Khung tiet dang ky' . $label . ' bi chong lan voi mot lich da co.',
                ]);
            }

            unset($payload['ngay_ap_dung'], $payload['selected_tiets']);
            $payloads[] = $payload;
        }

        return $payloads;
    }

    public function destroy(int $id)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $availability = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacher->id)
            ->findOrFail($id);

        $impactedSchedules = $this->availabilityService->findImpactedSchedulesAfterChange($availability, [
            'trang_thai' => GiangVienLichRanh::TRANG_THAI_TAM_AN,
        ]);

        if ($impactedSchedules->isNotEmpty()) {
            $availability->update(['trang_thai' => GiangVienLichRanh::TRANG_THAI_TAM_AN]);

            return redirect()
                ->route('giang-vien.lich-ranh.index')
                ->with('error', 'Khung lich giang nay dang lien quan den ' . $impactedSchedules->count() . ' buoi hoc tuong lai. He thong da chuyen sang tam an thay vi xoa han.');
        }

        $availability->delete();

        return redirect()
            ->route('giang-vien.lich-ranh.index')
            ->with('success', 'Da xoa lich giang.');
    }

    public function toggleStatus(int $id)
    {
        $teacher = auth()->user()->giangVien;
        abort_if(!$teacher, 404);

        $availability = GiangVienLichRanh::query()
            ->where('giang_vien_id', $teacher->id)
            ->findOrFail($id);

        $targetStatus = $availability->trang_thai === GiangVienLichRanh::TRANG_THAI_HOAT_DONG
            ? GiangVienLichRanh::TRANG_THAI_TAM_AN
            : GiangVienLichRanh::TRANG_THAI_HOAT_DONG;

        $impactedSchedules = $targetStatus === GiangVienLichRanh::TRANG_THAI_TAM_AN
            ? $this->availabilityService->findImpactedSchedulesAfterChange($availability, ['trang_thai' => $targetStatus])
            : collect();

        $availability->update(['trang_thai' => $targetStatus]);

        $message = $targetStatus === GiangVienLichRanh::TRANG_THAI_HOAT_DONG
            ? 'Da kich hoat lai lich giang.'
            : 'Da tam an lich giang.';

        if ($impactedSchedules->isNotEmpty()) {
            $message .= ' Canh bao: ' . $impactedSchedules->count() . ' buoi hoc tuong lai khong con duoc bao phu boi lich giang nay.';
        }

        return redirect()
            ->route('giang-vien.lich-ranh.index')
            ->with($impactedSchedules->isNotEmpty() ? 'error' : 'success', $message);
    }
}
