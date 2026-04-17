<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Services\ExamConfigurationService;
use App\Services\ExamSurveillanceService;
use Illuminate\Http\Request;

class BaiKiemTraPheDuyetController extends Controller
{
    public function __construct(
        private readonly ExamConfigurationService $examConfigurationService,
        private readonly ExamSurveillanceService $surveillanceService,
    ) {
    }

    public function index(Request $request)
    {
        $baiKiemTras = BaiKiemTra::query()
            ->with([
                'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'moduleHoc:id,ma_module,ten_module',
                'nguoiTao:ma_nguoi_dung,ho_ten,email',
            ])
            ->withCount('chiTietCauHois')
            ->when($request->filled('trang_thai_duyet'), fn ($query) => $query->where('trang_thai_duyet', $request->string('trang_thai_duyet')))
            ->when($request->filled('trang_thai_phat_hanh'), fn ($query) => $query->where('trang_thai_phat_hanh', $request->string('trang_thai_phat_hanh')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));

                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('tieu_de', 'like', '%' . $search . '%')
                        ->orWhere('mo_ta', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("
                CASE trang_thai_duyet
                    WHEN 'cho_duyet' THEN 1
                    WHEN 'tu_choi' THEN 2
                    WHEN 'da_duyet' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.kiem-tra-online.phe-duyet.index', compact('baiKiemTras'));
    }

    public function show(int $id)
    {
        $baiKiemTra = BaiKiemTra::with([
            'khoaHoc',
            'moduleHoc',
            'lichHoc',
            'nguoiTao:ma_nguoi_dung,ho_ten,email',
            'nguoiDuyet:ma_nguoi_dung,ho_ten,email',
            'chiTietCauHois.cauHoi.dapAns',
            'baiLams.hocVien:ma_nguoi_dung,ho_ten,email',
            'baiLams.nguoiHauKiem:ma_nguoi_dung,ho_ten,email',
        ])->findOrFail($id);

        return view('pages.hoc-vien.bai-kiem-tra.admin-exam-review', compact('baiKiemTra'));
    }

    public function showAttempt(int $baiLamId)
    {
        $baiLam = BaiLamBaiKiemTra::with([
            'hocVien:ma_nguoi_dung,ho_ten,email',
            'baiKiemTra.khoaHoc',
            'baiKiemTra.moduleHoc',
            'chiTietTraLois.chiTietBaiKiemTra',
            'chiTietTraLois.cauHoi.dapAns',
            'chiTietTraLois.dapAn',
            'giamSatLogs',
            'giamSatSnapshots',
            'nguoiHauKiem:ma_nguoi_dung,ho_ten,email',
        ])->findOrFail($baiLamId);

        $surveillanceSummary = $baiLam->baiKiemTra->co_giam_sat
            ? $this->surveillanceService->summarizeLogs($baiLam)
            : [];
        $reviewStatusOptions = $this->surveillanceService->reviewStatusOptions();

        return view('pages.hoc-vien.bai-kiem-tra.admin-attempt-review', compact(
            'baiLam',
            'surveillanceSummary',
            'reviewStatusOptions'
        ));
    }

    public function updateAttemptSurveillance(Request $request, int $baiLamId)
    {
        $baiLam = BaiLamBaiKiemTra::with('baiKiemTra')->findOrFail($baiLamId);

        if (!$baiLam->baiKiemTra->co_giam_sat) {
            return back()->with('error', 'Bài làm này không áp dụng giám sát.');
        }

        $reviewStatusOptions = array_keys($this->surveillanceService->reviewStatusOptions());

        $validated = $request->validate([
            'trang_thai_giam_sat' => 'required|string|in:' . implode(',', $reviewStatusOptions),
            'ghi_chu_giam_sat' => 'nullable|string|max:2000',
        ]);

        $this->surveillanceService->updateReview($baiLam, $validated, auth()->id());

        return back()->with('success', 'Đã cập nhật trạng thái hậu kiểm cho bài làm.');
    }

    public function approve(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::with(['chiTietCauHois.cauHoi'])->findOrFail($id);
        $this->examConfigurationService->ensureReadyForApproval($baiKiemTra);

        $baiKiemTra->update([
            'trang_thai_duyet' => 'da_duyet',
            'nguoi_duyet_id' => auth()->id(),
            'duyet_luc' => now(),
            'ghi_chu_duyet' => $request->input('ghi_chu_duyet'),
        ]);

        return back()->with('success', 'Đã duyệt bài kiểm tra.');
    }

    public function reject(Request $request, int $id)
    {
        $request->validate([
            'ghi_chu_duyet' => 'required|string|max:2000',
        ]);

        $baiKiemTra = BaiKiemTra::findOrFail($id);
        $baiKiemTra->update([
            'trang_thai_duyet' => 'tu_choi',
            'nguoi_duyet_id' => auth()->id(),
            'duyet_luc' => now(),
            'ghi_chu_duyet' => $request->input('ghi_chu_duyet'),
            'trang_thai_phat_hanh' => 'nhap',
        ]);

        return back()->with('success', 'Đã từ chối bài kiểm tra.');
    }

    public function publish(int $id)
    {
        $baiKiemTra = BaiKiemTra::with(['chiTietCauHois.cauHoi'])->findOrFail($id);

        if ($baiKiemTra->trang_thai_duyet !== 'da_duyet') {
            return back()->with('error', 'Chỉ bài đã duyệt mới được phát hành.');
        }

        $this->examConfigurationService->ensureReadyForApproval($baiKiemTra);

        $baiKiemTra->update([
            'trang_thai_phat_hanh' => 'phat_hanh',
            'phat_hanh_luc' => now(),
            'trang_thai' => true,
        ]);

        return back()->with('success', 'Đã phát hành bài kiểm tra cho học viên.');
    }

    public function close(int $id)
    {
        $baiKiemTra = BaiKiemTra::findOrFail($id);
        $baiKiemTra->update([
            'trang_thai_phat_hanh' => 'dong',
        ]);

        return back()->with('success', 'Đã đóng bài kiểm tra.');
    }
}
