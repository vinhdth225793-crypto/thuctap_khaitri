<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\GiangVien;
use App\Models\LichHoc;
use App\Models\NganHangCauHoi;
use App\Models\PhanCongModuleGiangVien;
use App\Services\BaiKiemTraScoringService;
use App\Services\KetQuaHocTapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaiKiemTraController extends Controller
{
    public function __construct(
        private readonly BaiKiemTraScoringService $scoringService,
        private readonly KetQuaHocTapService $ketQuaHocTapService,
    ) {
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'tieu_de' => 'required|string|max:255',
            'pham_vi' => 'required|in:module,buoi_hoc,cuoi_khoa',
            'thoi_gian_lam_bai' => 'required|integer|min:1|max:300',
            'module_hoc_id' => 'nullable|exists:module_hoc,id',
            'lich_hoc_id' => 'nullable|exists:lich_hoc,id',
            'mo_ta' => 'nullable|string',
        ]);

        $giangVien = auth()->user()?->giangVien;
        abort_if(!$giangVien, 403, 'Tai khoan chua duoc lien ket voi giang vien.');

        [$moduleId, $lichHoc] = $this->resolveScope($validated);
        $loaiBaiKiemTra = $this->resolveExamType($validated['pham_vi']);

        $this->authorizeTeacherForScope($giangVien, (int) $validated['khoa_hoc_id'], $moduleId, $loaiBaiKiemTra);

        $baiKiemTra = BaiKiemTra::create([
            'khoa_hoc_id' => $validated['khoa_hoc_id'],
            'module_hoc_id' => $moduleId,
            'lich_hoc_id' => $lichHoc?->id,
            'tieu_de' => $validated['tieu_de'],
            'mo_ta' => $validated['mo_ta'] ?? null,
            'thoi_gian_lam_bai' => $validated['thoi_gian_lam_bai'],
            'pham_vi' => $validated['pham_vi'],
            'loai_bai_kiem_tra' => $loaiBaiKiemTra,
            'loai_noi_dung' => 'tu_luan',
            'trang_thai_duyet' => 'nhap',
            'trang_thai_phat_hanh' => 'nhap',
            'tong_diem' => 10,
            'so_lan_duoc_lam' => 1,
            'nguoi_tao_id' => auth()->id(),
            'trang_thai' => true,
        ]);

        return redirect()
            ->route('giang-vien.bai-kiem-tra.edit', $baiKiemTra->id)
            ->with('success', 'Da tao khung bai kiem tra. Hay cau hinh cau hoi va gui duyet.');
    }

    public function edit(int $id)
    {
        $baiKiemTra = BaiKiemTra::with([
            'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc,phuong_thuc_danh_gia',
            'moduleHoc:id,ma_module,ten_module',
            'lichHoc:id,buoi_so,ngay_hoc',
            'chiTietCauHois.cauHoi.dapAns',
            'baiLams' => fn ($query) => $query->with('hocVien:ma_nguoi_dung,ho_ten,email')->orderByDesc('created_at')->limit(10),
        ])->findOrFail($id);

        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        $availableQuestions = $this->queryAvailableQuestions($baiKiemTra)
            ->with('dapAns')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.giang-vien.bai-kiem-tra.edit', compact('baiKiemTra', 'availableQuestions'));
    }

    public function update(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::with('chiTietCauHois')->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'thoi_gian_lam_bai' => 'required|integer|min:1|max:300',
            'ngay_mo' => 'nullable|date',
            'ngay_dong' => 'nullable|date|after:ngay_mo',
            'so_lan_duoc_lam' => 'required|integer|min:1|max:10',
            'randomize_questions' => 'nullable|boolean',
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'integer|exists:ngan_hang_cau_hoi,id',
            'question_scores' => 'nullable|array',
            'question_scores.*' => 'nullable|numeric|min:0.25|max:100',
        ]);

        DB::transaction(function () use ($baiKiemTra, $validated, $request) {
            $baiKiemTra->update([
                'tieu_de' => $validated['tieu_de'],
                'mo_ta' => $validated['mo_ta'] ?? null,
                'thoi_gian_lam_bai' => $validated['thoi_gian_lam_bai'],
                'ngay_mo' => $validated['ngay_mo'] ?? null,
                'ngay_dong' => $validated['ngay_dong'] ?? null,
                'so_lan_duoc_lam' => $validated['so_lan_duoc_lam'],
                'randomize_questions' => $request->boolean('randomize_questions'),
            ]);

            $questionIds = array_values(array_map('intval', $validated['question_ids'] ?? []));
            $questionScores = $validated['question_scores'] ?? [];

            if ($questionIds !== []) {
                [$tongDiem, $loaiNoiDung] = $this->syncExamQuestions($baiKiemTra, $questionIds, $questionScores);

                $baiKiemTra->update([
                    'tong_diem' => $tongDiem,
                    'loai_noi_dung' => $loaiNoiDung,
                ]);
            } elseif ($baiKiemTra->chiTietCauHois()->count() === 0) {
                $baiKiemTra->update([
                    'tong_diem' => 10,
                    'loai_noi_dung' => 'tu_luan',
                ]);
            }
        });

        return back()->with('success', 'Da cap nhat bai kiem tra.');
    }

    public function submitForApproval(int $id)
    {
        $baiKiemTra = BaiKiemTra::withCount('chiTietCauHois')->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        if ($baiKiemTra->chi_tiet_cau_hois_count === 0 && blank($baiKiemTra->mo_ta)) {
            return back()->with('error', 'Hay them cau hoi hoac mo ta de bai truoc khi gui duyet.');
        }

        $baiKiemTra->update([
            'trang_thai_duyet' => 'cho_duyet',
            'trang_thai_phat_hanh' => 'nhap',
            'de_xuat_duyet_luc' => now(),
        ]);

        return back()->with('success', 'Da gui bai kiem tra cho admin duyet.');
    }

    public function destroy(int $id)
    {
        $baiKiemTra = BaiKiemTra::withCount('baiLams')->findOrFail($id);
        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        if ($baiKiemTra->bai_lams_count > 0) {
            return back()->with('error', 'Khong the xoa bai kiem tra da co hoc vien lam bai.');
        }

        $baiKiemTra->delete();

        return back()->with('success', 'Da xoa bai kiem tra.');
    }

    public function chamDiemIndex()
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(!$giangVien, 403, 'Tai khoan chua duoc lien ket voi giang vien.');

        $moduleIds = $this->getAcceptedModuleIds($giangVien);
        $courseIds = $this->getAcceptedCourseIds($giangVien);

        $baiLams = BaiLamBaiKiemTra::query()
            ->with([
                'baiKiemTra.khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
                'baiKiemTra.moduleHoc:id,ma_module,ten_module',
                'hocVien:ma_nguoi_dung,ho_ten,email',
            ])
            ->where('trang_thai_cham', 'cho_cham')
            ->whereHas('baiKiemTra', function ($query) use ($moduleIds, $courseIds) {
                $query->where(function ($nestedQuery) use ($moduleIds, $courseIds) {
                    $nestedQuery->when($moduleIds !== [], fn ($q) => $q->orWhereIn('module_hoc_id', $moduleIds))
                        ->when($courseIds !== [], fn ($q) => $q->orWhere(function ($innerQuery) use ($courseIds) {
                            $innerQuery->where('loai_bai_kiem_tra', 'cuoi_khoa')
                                ->whereIn('khoa_hoc_id', $courseIds);
                        }));
                });
            })
            ->orderByDesc('nop_luc')
            ->paginate(15);

        return view('pages.giang-vien.bai-kiem-tra.cham-diem-index', compact('baiLams'));
    }

    public function chamDiemShow(int $id)
    {
        $baiLam = BaiLamBaiKiemTra::with([
            'hocVien:ma_nguoi_dung,ho_ten,email',
            'baiKiemTra.khoaHoc',
            'baiKiemTra.moduleHoc',
            'chiTietTraLois.chiTietBaiKiemTra',
            'chiTietTraLois.cauHoi.dapAns',
            'chiTietTraLois.dapAn',
        ])->findOrFail($id);

        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiLam->baiKiemTra);

        return view('pages.giang-vien.bai-kiem-tra.cham-diem-show', compact('baiLam'));
    }

    public function chamDiemStore(Request $request, int $id)
    {
        $baiLam = BaiLamBaiKiemTra::with([
            'baiKiemTra',
            'chiTietTraLois.chiTietBaiKiemTra',
            'chiTietTraLois.cauHoi',
        ])->findOrFail($id);

        $giangVien = auth()->user()?->giangVien;
        $this->authorizeTeacherForExam($giangVien, $baiLam->baiKiemTra);

        $grades = $request->input('grades', []);
        $normalizedGrades = [];

        foreach ($baiLam->chiTietTraLois as $chiTietTraLoi) {
            if ($chiTietTraLoi->cauHoi?->loai_cau_hoi !== 'tu_luan') {
                continue;
            }

            $grade = $grades[$chiTietTraLoi->id] ?? null;
            $diemToiDa = (float) ($chiTietTraLoi->chiTietBaiKiemTra?->diem_so ?? 0);
            $diemTuLuan = $grade['diem_tu_luan'] ?? null;

            if ($diemTuLuan === null || $diemTuLuan === '') {
                throw ValidationException::withMessages([
                    'grades.' . $chiTietTraLoi->id . '.diem_tu_luan' => 'Vui long nhap diem cho moi cau tu luan.',
                ]);
            }

            if (!is_numeric($diemTuLuan) || (float) $diemTuLuan < 0 || (float) $diemTuLuan > $diemToiDa) {
                throw ValidationException::withMessages([
                    'grades.' . $chiTietTraLoi->id . '.diem_tu_luan' => 'Diem phai nam trong khoang 0 - ' . $diemToiDa . '.',
                ]);
            }

            $normalizedGrades[$chiTietTraLoi->id] = [
                'diem_tu_luan' => $diemTuLuan,
                'nhan_xet' => $grade['nhan_xet'] ?? null,
            ];
        }

        DB::transaction(function () use ($baiLam, $normalizedGrades, $giangVien) {
            $this->scoringService->applyManualGrades($baiLam, $normalizedGrades, $giangVien);
            $this->ketQuaHocTapService->refreshForCourseStudent($baiLam->baiKiemTra->khoa_hoc_id, $baiLam->hoc_vien_id);
        });

        return redirect()
            ->route('giang-vien.cham-diem.show', $baiLam->id)
            ->with('success', 'Da cham bai va cap nhat ket qua hoc tap.');
    }

    /**
     * @return array{0: int|null, 1: LichHoc|null}
     */
    private function resolveScope(array $validated): array
    {
        $moduleId = isset($validated['module_hoc_id']) ? (int) $validated['module_hoc_id'] : null;
        $lichHoc = null;

        if ($validated['pham_vi'] === 'buoi_hoc') {
            $lichHoc = LichHoc::query()
                ->where('khoa_hoc_id', $validated['khoa_hoc_id'])
                ->findOrFail($validated['lich_hoc_id']);

            $moduleId = (int) $lichHoc->module_hoc_id;
        }

        if ($validated['pham_vi'] === 'module' && !$moduleId) {
            throw ValidationException::withMessages([
                'module_hoc_id' => 'Vui long chon module cho bai kiem tra nay.',
            ]);
        }

        return [$moduleId, $lichHoc];
    }

    private function resolveExamType(string $phamVi): string
    {
        return match ($phamVi) {
            'cuoi_khoa' => 'cuoi_khoa',
            'buoi_hoc' => 'buoi_hoc',
            default => 'module',
        };
    }

    private function authorizeTeacherForScope(GiangVien $giangVien, int $khoaHocId, ?int $moduleId, string $loaiBaiKiemTra): void
    {
        $query = PhanCongModuleGiangVien::query()
            ->where('giao_vien_id', $giangVien->id)
            ->where('khoa_hoc_id', $khoaHocId)
            ->where('trang_thai', 'da_nhan');

        if ($loaiBaiKiemTra !== 'cuoi_khoa') {
            $query->where('module_hoc_id', $moduleId);
        }

        abort_unless($query->exists(), 403, 'Ban khong duoc phan cong cho bai kiem tra nay.');
    }

    private function authorizeTeacherForExam(?GiangVien $giangVien, BaiKiemTra $baiKiemTra): void
    {
        abort_if(!$giangVien, 403, 'Tai khoan chua duoc lien ket voi giang vien.');

        $this->authorizeTeacherForScope(
            $giangVien,
            (int) $baiKiemTra->khoa_hoc_id,
            $baiKiemTra->module_hoc_id ? (int) $baiKiemTra->module_hoc_id : null,
            $baiKiemTra->loai_bai_kiem_tra
        );
    }

    private function queryAvailableQuestions(BaiKiemTra $baiKiemTra)
    {
        return NganHangCauHoi::query()
            ->where('khoa_hoc_id', $baiKiemTra->khoa_hoc_id)
            ->when($baiKiemTra->loai_bai_kiem_tra !== 'cuoi_khoa' && $baiKiemTra->module_hoc_id, fn ($query) => $query->where('module_hoc_id', $baiKiemTra->module_hoc_id))
            ->where('trang_thai', 'san_sang');
    }

    /**
     * @param  array<int, int>  $questionIds
     * @param  array<int|string, mixed>  $questionScores
     * @return array{0: float, 1: string}
     */
    private function syncExamQuestions(BaiKiemTra $baiKiemTra, array $questionIds, array $questionScores): array
    {
        $availableQuestions = $this->queryAvailableQuestions($baiKiemTra)
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        if ($availableQuestions->count() !== count(array_unique($questionIds))) {
            throw ValidationException::withMessages([
                'question_ids' => 'Danh sach cau hoi co muc khong hop le hoac ngoai pham vi de.',
            ]);
        }

        $baiKiemTra->chiTietCauHois()->delete();

        $tongDiem = 0.0;
        $types = [];

        foreach (array_values($questionIds) as $index => $questionId) {
            $cauHoi = $availableQuestions->get($questionId);
            $diemSo = (float) ($questionScores[$questionId] ?? $questionScores[$index] ?? $cauHoi->diem_mac_dinh ?? 1);
            $diemSo = max(0.25, $diemSo);

            $baiKiemTra->chiTietCauHois()->create([
                'ngan_hang_cau_hoi_id' => $cauHoi->id,
                'thu_tu' => $index + 1,
                'diem_so' => $diemSo,
                'bat_buoc' => true,
            ]);

            $tongDiem += $diemSo;
            $types[] = $cauHoi->loai_cau_hoi;
        }

        $loaiNoiDung = count(array_unique($types)) > 1
            ? 'hon_hop'
            : ($types[0] ?? 'tu_luan');

        return [round($tongDiem, 2), $loaiNoiDung];
    }

    /**
     * @return array<int, int>
     */
    private function getAcceptedModuleIds(GiangVien $giangVien): array
    {
        return PhanCongModuleGiangVien::query()
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->pluck('module_hoc_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function getAcceptedCourseIds(GiangVien $giangVien): array
    {
        return PhanCongModuleGiangVien::query()
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->pluck('khoa_hoc_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
