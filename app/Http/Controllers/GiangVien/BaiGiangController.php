<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertBaiGiangRequest;
use App\Models\BaiGiang;
use App\Models\GiangVien;
use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Models\PhanCongModuleGiangVien;
use App\Models\TaiNguyenBuoiHoc;
use App\Services\LiveLectureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaiGiangController extends Controller
{
    public function __construct(
        private readonly LiveLectureService $liveLectureService
    ) {
    }

    public function index()
    {
        $giangVien = auth()->user()->giangVien;
        if (!$giangVien) {
            return redirect()->route('home')->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        $baiGiangs = BaiGiang::with([
                'khoaHoc',
                'moduleHoc',
                'lichHoc',
                'taiNguyenChinh',
                'phongHocLive.moderator',
            ])
            ->where('nguoi_tao_id', auth()->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('pages.giang-vien.bai-giang.list', compact('baiGiangs'));
    }

    public function create(Request $request)
    {
        $giangVien = $this->resolveCurrentGiangVien();

        $phanCongs = PhanCongModuleGiangVien::with(['khoaHoc', 'moduleHoc', 'giangVien.nguoiDung'])
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->get();

        $thuVien = TaiNguyenBuoiHoc::query()
            ->where('nguoi_tao_id', auth()->user()->id)
            ->daDuyet()
            ->get();

        $moderatorOptions = $this->getModeratorOptions();

        return view('pages.giang-vien.bai-giang.create', [
            'baiGiang' => null,
            'phanCongs' => $phanCongs,
            'thuVien' => $thuVien,
            'moderatorOptions' => $moderatorOptions,
            'assistantOptions' => $moderatorOptions,
            'defaultModeratorId' => auth()->user()->id,
        ]);
    }

    public function store(UpsertBaiGiangRequest $request)
    {
        $validated = $request->validated();
        $giangVien = $this->resolveCurrentGiangVien();
        $phanCong = $this->findAuthorizedPhanCong((int) $validated['phan_cong_id'], $giangVien);
        $lichHocId = $this->resolveAuthorizedLichHocId(
            isset($validated['lich_hoc_id']) ? (int) $validated['lich_hoc_id'] : null,
            $phanCong
        );
        [$taiNguyenChinhId, $taiNguyenPhuIds] = $this->resolveAuthorizedLibraryResources(
            isset($validated['tai_nguyen_chinh_id']) ? (int) $validated['tai_nguyen_chinh_id'] : null,
            $validated['tai_nguyen_phu_ids'] ?? [],
            auth()->user()->id
        );

        $lectureStatus = $this->resolveLectureApprovalStatus($validated['hanh_dong'] ?? 'luu_nhap');
        $failureMessage = $lectureStatus === BaiGiang::STATUS_DUYET_CHO
            ? 'Không thể lưu bài giảng để gửi duyệt lúc này. Vui lòng kiểm tra thông tin và thử lại.'
            : 'Không thể lưu bài giảng lúc này. Vui lòng thử lại.';

        try {
            DB::transaction(function () use (
                &$baiGiang,
                $validated,
                $phanCong,
                $lichHocId,
                $taiNguyenChinhId,
                $taiNguyenPhuIds,
                $lectureStatus
            ) {
                $baiGiang = BaiGiang::create([
                    'khoa_hoc_id' => $phanCong->khoa_hoc_id,
                    'module_hoc_id' => $phanCong->module_hoc_id,
                    'lich_hoc_id' => $lichHocId,
                    'nguoi_tao_id' => auth()->user()->id,
                    'tieu_de' => $validated['tieu_de'],
                    'mo_ta' => $validated['mo_ta'] ?? null,
                    'loai_bai_giang' => $validated['loai_bai_giang'],
                    'tai_nguyen_chinh_id' => $taiNguyenChinhId,
                    'thu_tu_hien_thi' => $validated['thu_tu_hien_thi'] ?? 0,
                    'thoi_diem_mo' => $validated['thoi_diem_mo'] ?? null,
                    'trang_thai_duyet' => $lectureStatus,
                    'trang_thai_cong_bo' => BaiGiang::CONG_BO_AN,
                    'ngay_gui_duyet' => $lectureStatus === BaiGiang::STATUS_DUYET_CHO ? now() : null,
                ]);

                if ($taiNguyenPhuIds !== []) {
                    $baiGiang->taiNguyenPhu()->sync($taiNguyenPhuIds);
                }

                $this->liveLectureService->syncLiveRoom($baiGiang, $validated, auth()->user(), false);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', $failureMessage);
        }

        $message = $lectureStatus === BaiGiang::STATUS_DUYET_CHO
            ? 'Đã lưu bài giảng và gửi admin duyệt.'
            : 'Đã lưu bài giảng thành công.';

        if ($phanCong && $lichHocId) {
            return redirect()
                ->route('giang-vien.khoa-hoc.show', [
                    'id' => $phanCong->khoa_hoc_id,
                    'focus_lich_hoc_id' => $lichHocId,
                    'quick_action' => 'lecture',
                ])
                ->with('success', $message);
        }

        return redirect()->route('giang-vien.bai-giang.index')->with('success', $message);
    }

    public function edit($id)
    {
        $baiGiang = BaiGiang::with(['taiNguyenPhu', 'phongHocLive'])
            ->where('nguoi_tao_id', auth()->user()->id)
            ->findOrFail($id);

        $giangVien = $this->resolveCurrentGiangVien();
        $phanCongs = PhanCongModuleGiangVien::with(['khoaHoc', 'moduleHoc', 'giangVien.nguoiDung'])
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->get();

        $thuVien = TaiNguyenBuoiHoc::query()
            ->where('nguoi_tao_id', auth()->user()->id)
            ->daDuyet()
            ->get();

        $moderatorOptions = $this->getModeratorOptions();

        return view('pages.giang-vien.bai-giang.edit', [
            'baiGiang' => $baiGiang,
            'phanCongs' => $phanCongs,
            'thuVien' => $thuVien,
            'moderatorOptions' => $moderatorOptions,
            'assistantOptions' => $moderatorOptions,
            'defaultModeratorId' => $baiGiang->phongHocLive?->moderator_id ?? auth()->user()->id,
        ]);
    }

    public function update(UpsertBaiGiangRequest $request, $id)
    {
        $baiGiang = BaiGiang::with('phongHocLive')
            ->where('nguoi_tao_id', auth()->user()->id)
            ->findOrFail($id);

        $validated = $request->validated();
        $giangVien = $this->resolveCurrentGiangVien();
        $phanCong = $this->findAuthorizedPhanCong((int) $validated['phan_cong_id'], $giangVien);
        $lichHocId = $this->resolveAuthorizedLichHocId(
            isset($validated['lich_hoc_id']) ? (int) $validated['lich_hoc_id'] : null,
            $phanCong
        );
        [$taiNguyenChinhId, $taiNguyenPhuIds] = $this->resolveAuthorizedLibraryResources(
            isset($validated['tai_nguyen_chinh_id']) ? (int) $validated['tai_nguyen_chinh_id'] : null,
            $validated['tai_nguyen_phu_ids'] ?? [],
            auth()->user()->id
        );

        $lectureStatus = $this->resolveLectureApprovalStatus($validated['hanh_dong'] ?? 'luu_nhap');
        $failureMessage = $lectureStatus === BaiGiang::STATUS_DUYET_CHO
            ? 'Không thể cập nhật bài giảng để gửi duyệt lúc này. Vui lòng kiểm tra thông tin và thử lại.'
            : 'Không thể cập nhật bài giảng lúc này. Vui lòng thử lại.';

        try {
            DB::transaction(function () use (
                $baiGiang,
                $validated,
                $phanCong,
                $lichHocId,
                $taiNguyenChinhId,
                $taiNguyenPhuIds,
                $lectureStatus
            ) {
                $baiGiang->update([
                    'khoa_hoc_id' => $phanCong->khoa_hoc_id,
                    'module_hoc_id' => $phanCong->module_hoc_id,
                    'lich_hoc_id' => $lichHocId,
                    'tieu_de' => $validated['tieu_de'],
                    'mo_ta' => $validated['mo_ta'] ?? null,
                    'loai_bai_giang' => $validated['loai_bai_giang'],
                    'tai_nguyen_chinh_id' => $taiNguyenChinhId,
                    'thu_tu_hien_thi' => $validated['thu_tu_hien_thi'] ?? 0,
                    'thoi_diem_mo' => $validated['thoi_diem_mo'] ?? null,
                    'trang_thai_duyet' => $lectureStatus,
                    'trang_thai_cong_bo' => $lectureStatus === BaiGiang::STATUS_DUYET_DA_DUYET
                        ? $baiGiang->trang_thai_cong_bo
                        : BaiGiang::CONG_BO_AN,
                    'ngay_gui_duyet' => $lectureStatus === BaiGiang::STATUS_DUYET_CHO ? now() : null,
                ]);

                $baiGiang->taiNguyenPhu()->sync($taiNguyenPhuIds);

                $this->liveLectureService->syncLiveRoom($baiGiang, $validated, auth()->user(), false);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', $failureMessage);
        }

        $message = $lectureStatus === BaiGiang::STATUS_DUYET_CHO
            ? 'Đã cập nhật bài giảng và gửi admin duyệt.'
            : 'Đã cập nhật bài giảng.';

        if ($phanCong && $lichHocId) {
            return redirect()
                ->route('giang-vien.khoa-hoc.show', [
                    'id' => $phanCong->khoa_hoc_id,
                    'focus_lich_hoc_id' => $lichHocId,
                    'quick_action' => 'lecture',
                ])
                ->with('success', $message);
        }

        return redirect()->route('giang-vien.bai-giang.index')->with('success', $message);
    }

    public function guiDuyet($id)
    {
        $baiGiang = BaiGiang::with('phongHocLive')
            ->where('nguoi_tao_id', auth()->user()->id)
            ->findOrFail($id);

        if ($baiGiang->isLive() && !$baiGiang->phongHocLive) {
            return back()->with('error', 'Bài giảng live cần có cấu hình phòng học trước khi gửi duyệt.');
        }

        $baiGiang->update([
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_CHO,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_AN,
            'ngay_gui_duyet' => now(),
        ]);

        if ($baiGiang->phongHocLive) {
            $baiGiang->phongHocLive->update([
                'trang_thai_duyet' => BaiGiang::STATUS_DUYET_CHO,
                'trang_thai_cong_bo' => BaiGiang::CONG_BO_AN,
            ]);
        }

        return back()->with('success', 'Đã gửi yêu cầu duyệt bài giảng.');
    }

    public function destroy(Request $request, $id)
    {
        $baiGiang = BaiGiang::where('nguoi_tao_id', auth()->user()->id)->findOrFail($id);
        $khoaHocId = $baiGiang->khoa_hoc_id;
        $lichHocId = $baiGiang->lich_hoc_id;

        $baiGiang->delete();

        if ($request->query('redirect_to_session') === '1' && $khoaHocId && $lichHocId) {
            return redirect()
                ->route('giang-vien.khoa-hoc.show', [
                    'id' => $khoaHocId,
                    'focus_lich_hoc_id' => $lichHocId,
                    'quick_action' => 'lecture',
                ])
                ->with('success', 'Đã xóa bài giảng.');
        }

        return redirect()->route('giang-vien.bai-giang.index')->with('success', 'Đã xóa bài giảng.');
    }

    public function getLichHoc(Request $request)
    {
        $request->validate([
            'phan_cong_id' => 'required|integer',
        ]);

        $giangVien = $this->resolveCurrentGiangVien();
        $phanCong = PhanCongModuleGiangVien::query()
            ->whereKey($request->integer('phan_cong_id'))
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->first();

        abort_if(!$phanCong, 403, 'Bạn không có quyền xem lịch học của phân công này.');

        $lichHocs = LichHoc::where('khoa_hoc_id', $phanCong->khoa_hoc_id)
            ->where('module_hoc_id', $phanCong->module_hoc_id)
            ->orderBy('buoi_so')
            ->get(['id', 'buoi_so', 'ngay_hoc']);

        return response()->json($lichHocs);
    }

    private function resolveCurrentGiangVien(): GiangVien
    {
        $giangVien = auth()->user()?->giangVien;
        abort_if(!$giangVien, 403, 'Tài khoản chưa được liên kết với giảng viên.');

        return $giangVien;
    }

    private function findAuthorizedPhanCong(int $phanCongId, GiangVien $giangVien): PhanCongModuleGiangVien
    {
        $phanCong = PhanCongModuleGiangVien::query()
            ->whereKey($phanCongId)
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->first();

        if (!$phanCong) {
            throw ValidationException::withMessages([
                'phan_cong_id' => 'Phân công đã chọn không hợp lệ hoặc không thuộc quyền của bạn.',
            ]);
        }

        return $phanCong;
    }

    private function resolveAuthorizedLichHocId(?int $lichHocId, PhanCongModuleGiangVien $phanCong): ?int
    {
        if ($lichHocId === null) {
            return null;
        }

        $isValidLichHoc = LichHoc::query()
            ->whereKey($lichHocId)
            ->where('khoa_hoc_id', $phanCong->khoa_hoc_id)
            ->where('module_hoc_id', $phanCong->module_hoc_id)
            ->exists();

        if (!$isValidLichHoc) {
            throw ValidationException::withMessages([
                'lich_hoc_id' => 'Buổi học đã chọn không thuộc module được phân công.',
            ]);
        }

        return $lichHocId;
    }

    private function resolveLectureApprovalStatus(string $action): string
    {
        return $action === 'gui_duyet'
            ? BaiGiang::STATUS_DUYET_CHO
            : BaiGiang::STATUS_DUYET_NHAP;
    }

    /**
     * @param  array<int, mixed>  $taiNguyenPhuIds
     * @return array{0: int|null, 1: array<int, int>}
     */
    private function resolveAuthorizedLibraryResources(?int $taiNguyenChinhId, array $taiNguyenPhuIds, int $nguoiDungId): array
    {
        $normalizedPhuIds = collect($taiNguyenPhuIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $taiNguyenChinhId !== null && $id === $taiNguyenChinhId)
            ->unique()
            ->values();

        $resourceIds = $normalizedPhuIds
            ->when($taiNguyenChinhId !== null, fn ($collection) => $collection->prepend($taiNguyenChinhId))
            ->unique()
            ->values();

        if ($resourceIds->isEmpty()) {
            return [$taiNguyenChinhId, $normalizedPhuIds->all()];
        }

        $accessibleIds = TaiNguyenBuoiHoc::query()
            ->whereIn('id', $resourceIds->all())
            ->where('nguoi_tao_id', $nguoiDungId)
            ->where('trang_thai_duyet', TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($taiNguyenChinhId !== null && !in_array($taiNguyenChinhId, $accessibleIds, true)) {
            throw ValidationException::withMessages([
                'tai_nguyen_chinh_id' => 'Tài nguyên chính đã chọn không hợp lệ hoặc chưa được duyệt cho bạn sử dụng.',
            ]);
        }

        $invalidPhuIds = $normalizedPhuIds
            ->reject(fn (int $id) => in_array($id, $accessibleIds, true))
            ->values();

        if ($invalidPhuIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'tai_nguyen_phu_ids' => 'Có tài nguyên phụ không hợp lệ hoặc chưa được duyệt cho bạn sử dụng.',
            ]);
        }

        return [$taiNguyenChinhId, $normalizedPhuIds->all()];
    }

    private function getModeratorOptions()
    {
        return NguoiDung::query()
            ->where('trang_thai', true)
            ->whereIn('vai_tro', ['admin', 'giang_vien'])
            ->orderBy('ho_ten')
            ->get(['ma_nguoi_dung as id', 'ho_ten', 'vai_tro']);
    }
}
