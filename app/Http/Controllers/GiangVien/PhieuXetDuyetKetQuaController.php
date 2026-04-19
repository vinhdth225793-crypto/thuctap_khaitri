<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\PhanCongModuleGiangVien;
use App\Models\PhieuXetDuyetKetQua;
use App\Services\CourseApprovalReviewService;
use Illuminate\Http\Request;

class PhieuXetDuyetKetQuaController extends Controller
{
    public function __construct(
        private readonly CourseApprovalReviewService $reviewService,
    ) {
    }

    public function show(Request $request, int $id)
    {
        [$khoaHoc, $phanCong] = $this->resolveTeacherCourse($id);
        $latestTicket = PhieuXetDuyetKetQua::query()
            ->with(['chiTiets.hocVien', 'approvedBy', 'rejectedBy', 'finalizedBy'])
            ->where('khoa_hoc_id', $khoaHoc->id)
            ->where('nguoi_lap_id', $request->user()->ma_nguoi_dung)
            ->latest('updated_at')
            ->first();

        $mode = $request->input('phuong_an')
            ?: $latestTicket?->phuong_an
            ?: PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE;

        $selectedIds = $request->input('bai_kiem_tra_ids', []);
        $selectedIds = is_array($selectedIds) ? $selectedIds : [$selectedIds];

        if ($selectedIds === []) {
            $selectedIds = $latestTicket && $latestTicket->phuong_an === $mode
                ? ($latestTicket->bai_kiem_tra_ids ?: [])
                : $this->reviewService->defaultExamIdsFor($khoaHoc, $mode);
        }

        $examGroups = $this->reviewService->examGroupsForCourse($khoaHoc);
        $preview = $this->reviewService->buildPreview($khoaHoc, $mode, $selectedIds);
        $tickets = PhieuXetDuyetKetQua::query()
            ->with(['approvedBy', 'rejectedBy', 'finalizedBy'])
            ->withCount('chiTiets')
            ->where('khoa_hoc_id', $khoaHoc->id)
            ->where('nguoi_lap_id', $request->user()->ma_nguoi_dung)
            ->latest('updated_at')
            ->take(8)
            ->get();

        return view('pages.giang-vien.xet-duyet-ket-qua.show', compact(
            'khoaHoc',
            'phanCong',
            'latestTicket',
            'tickets',
            'examGroups',
            'preview',
            'mode',
            'selectedIds'
        ));
    }

    public function storeDraft(Request $request, int $id)
    {
        [$khoaHoc, $phanCong] = $this->resolveTeacherCourse($id);
        $validated = $this->validateTicketRequest($request);

        $this->reviewService->saveDraft(
            $khoaHoc,
            $request->user(),
            $phanCong,
            $validated['phuong_an'],
            $validated['bai_kiem_tra_ids'],
            $validated['ghi_chu'] ?? null,
            false
        );

        return redirect()
            ->route('giang-vien.xet-duyet-ket-qua.show', $khoaHoc->id)
            ->with('success', 'Da luu nhap phieu xet duyet cuoi khoa.');
    }

    public function submit(Request $request, int $id)
    {
        [$khoaHoc, $phanCong] = $this->resolveTeacherCourse($id);
        $validated = $this->validateTicketRequest($request);

        $this->reviewService->saveDraft(
            $khoaHoc,
            $request->user(),
            $phanCong,
            $validated['phuong_an'],
            $validated['bai_kiem_tra_ids'],
            $validated['ghi_chu'] ?? null,
            true
        );

        return redirect()
            ->route('giang-vien.xet-duyet-ket-qua.show', $khoaHoc->id)
            ->with('success', 'Da gui phieu xet duyet cho admin.');
    }

    /**
     * @return array{phuong_an: string, bai_kiem_tra_ids: array<int, mixed>, ghi_chu?: string|null}
     */
    private function validateTicketRequest(Request $request): array
    {
        return $request->validate([
            'phuong_an' => 'required|in:final_exam_attendance,selected_exams_attendance',
            'bai_kiem_tra_ids' => 'required|array|min:1',
            'bai_kiem_tra_ids.*' => 'integer|exists:bai_kiem_tra,id',
            'ghi_chu' => 'nullable|string|max:2000',
        ]);
    }

    private function resolveTeacherCourse(int $identifier): array
    {
        $teacher = auth()->user()?->giangVien;
        abort_if(! $teacher, 403, 'Tai khoan chua lien ket voi giang vien.');

        $statusPriority = "CASE WHEN trang_thai = 'da_nhan' THEN 0 WHEN trang_thai = 'cho_xac_nhan' THEN 1 ELSE 2 END";
        $baseQuery = PhanCongModuleGiangVien::query()
            ->with(['khoaHoc.nhomNganh', 'moduleHoc'])
            ->where('giang_vien_id', $teacher->id)
            ->where('trang_thai', 'da_nhan');

        $assignment = (clone $baseQuery)->find($identifier);
        if (! $assignment) {
            $assignment = (clone $baseQuery)
                ->where('khoa_hoc_id', $identifier)
                ->orderByRaw($statusPriority)
                ->orderByDesc('id')
                ->first();
        }
        if (! $assignment) {
            $assignment = (clone $baseQuery)
                ->where('module_hoc_id', $identifier)
                ->orderByRaw($statusPriority)
                ->orderByDesc('id')
                ->first();
        }

        abort_if(! $assignment, 404);

        return [$assignment->khoaHoc, $assignment];
    }
}
