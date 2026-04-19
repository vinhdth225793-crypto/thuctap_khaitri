<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhieuXetDuyetKetQua;
use App\Services\CourseApprovalReviewService;
use Illuminate\Http\Request;

class PhieuXetDuyetKetQuaController extends Controller
{
    public function __construct(
        private readonly CourseApprovalReviewService $reviewService,
    ) {
    }

    public function index(Request $request)
    {
        $status = $request->query('trang_thai');
        $allowedStatuses = [
            PhieuXetDuyetKetQua::TRANG_THAI_DRAFT,
            PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
            PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
            PhieuXetDuyetKetQua::TRANG_THAI_REJECTED,
            PhieuXetDuyetKetQua::TRANG_THAI_APPROVED,
            PhieuXetDuyetKetQua::TRANG_THAI_FINALIZED,
        ];

        $tickets = PhieuXetDuyetKetQua::query()
            ->with(['khoaHoc', 'nguoiLap', 'approvedBy', 'rejectedBy', 'finalizedBy'])
            ->withCount('chiTiets')
            ->when(in_array($status, $allowedStatuses, true), fn ($query) => $query->where('trang_thai', $status))
            ->orderByRaw("
                CASE trang_thai
                    WHEN 'submitted' THEN 1
                    WHEN 'reviewing' THEN 2
                    WHEN 'approved' THEN 3
                    WHEN 'rejected' THEN 4
                    WHEN 'draft' THEN 5
                    WHEN 'finalized' THEN 6
                    ELSE 7
                END
            ")
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'submitted' => PhieuXetDuyetKetQua::where('trang_thai', PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED)->count(),
            'reviewing' => PhieuXetDuyetKetQua::where('trang_thai', PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING)->count(),
            'approved' => PhieuXetDuyetKetQua::where('trang_thai', PhieuXetDuyetKetQua::TRANG_THAI_APPROVED)->count(),
            'finalized' => PhieuXetDuyetKetQua::where('trang_thai', PhieuXetDuyetKetQua::TRANG_THAI_FINALIZED)->count(),
        ];

        return view('pages.admin.xet-duyet-ket-qua.index', compact('tickets', 'summary', 'status'));
    }

    public function show(PhieuXetDuyetKetQua $phieu)
    {
        $phieu->load([
            'khoaHoc',
            'nguoiLap',
            'approvedBy',
            'rejectedBy',
            'finalizedBy',
            'chiTiets.hocVien',
        ]);

        return view('pages.admin.xet-duyet-ket-qua.show', compact('phieu'));
    }

    public function startReview(Request $request, PhieuXetDuyetKetQua $phieu)
    {
        $this->reviewService->startReview($phieu, $request->user());

        return back()->with('success', 'Da chuyen phieu sang trang thai dang xem xet.');
    }

    public function approve(Request $request, PhieuXetDuyetKetQua $phieu)
    {
        $this->reviewService->approve($phieu, $request->user());

        return back()->with('success', 'Da duyet phieu. Admin co the chot chinh thuc khi san sang.');
    }

    public function reject(Request $request, PhieuXetDuyetKetQua $phieu)
    {
        $validated = $request->validate([
            'reject_reason' => 'required|string|max:2000',
        ]);

        $this->reviewService->reject($phieu, $request->user(), $validated['reject_reason']);

        return redirect()
            ->route('admin.xet-duyet-ket-qua.index')
            ->with('success', 'Da tu choi phieu va tra ve cho giang vien chinh sua.');
    }

    public function finalize(Request $request, PhieuXetDuyetKetQua $phieu)
    {
        $validated = $request->validate([
            'ghi_chu_duyet' => 'nullable|string|max:2000',
        ]);

        $this->reviewService->finalize($phieu, $request->user(), $validated['ghi_chu_duyet'] ?? null);

        return back()->with('success', 'Da chot ket qua chinh thuc va luu vao ho so hoc tap.');
    }
}
