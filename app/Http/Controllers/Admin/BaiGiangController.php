<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertBaiGiangRequest;
use App\Models\BaiGiang;
use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Models\PhanCongModuleGiangVien;
use App\Models\PhongHocLive;
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

    public function index(Request $request)
    {
        $query = BaiGiang::with([
                'khoaHoc',
                'moduleHoc',
                'nguoiTao',
                'phongHocLive.moderator',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('trang_thai_duyet')) {
            $query->where('trang_thai_duyet', $request->trang_thai_duyet);
        }

        if ($request->filled('loai_bai_giang')) {
            $query->where('loai_bai_giang', $request->loai_bai_giang);
        }

        $baiGiangs = $query->paginate(20);

        return view('pages.admin.bai-giang.index', compact('baiGiangs'));
    }

    public function create()
    {
        $phanCongs = $this->getAssignmentOptions();
        $thuVien = TaiNguyenBuoiHoc::query()->daDuyet()->get();
        $moderatorOptions = $this->getModeratorOptions();

        return view('pages.admin.bai-giang.create', [
            'baiGiang' => null,
            'phanCongs' => $phanCongs,
            'thuVien' => $thuVien,
            'moderatorOptions' => $moderatorOptions,
            'assistantOptions' => $moderatorOptions,
            'defaultModeratorId' => null,
        ]);
    }

    public function store(UpsertBaiGiangRequest $request)
    {
        $validated = $request->validated();
        $phanCong = $this->findAssignedPhanCong((int) $validated['phan_cong_id']);
        $lichHocId = $this->resolveAuthorizedLichHocId(
            isset($validated['lich_hoc_id']) ? (int) $validated['lich_hoc_id'] : null,
            $phanCong
        );
        [$taiNguyenChinhId, $taiNguyenPhuIds] = $this->resolveApprovedResources(
            isset($validated['tai_nguyen_chinh_id']) ? (int) $validated['tai_nguyen_chinh_id'] : null,
            $validated['tai_nguyen_phu_ids'] ?? []
        );

        $lectureStatus = $this->resolveLectureApprovalStatus($validated['hanh_dong'] ?? 'luu_nhap');

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
                'ngay_gui_duyet' => in_array($lectureStatus, [BaiGiang::STATUS_DUYET_CHO, BaiGiang::STATUS_DUYET_DA_DUYET], true) ? now() : null,
                'ngay_duyet' => $lectureStatus === BaiGiang::STATUS_DUYET_DA_DUYET ? now() : null,
                'nguoi_duyet_id' => $lectureStatus === BaiGiang::STATUS_DUYET_DA_DUYET ? auth()->user()->id : null,
            ]);

            if ($taiNguyenPhuIds !== []) {
                $baiGiang->taiNguyenPhu()->sync($taiNguyenPhuIds);
            }

            $this->liveLectureService->syncLiveRoom($baiGiang, $validated, auth()->user(), true);
        });

        $message = $lectureStatus === BaiGiang::STATUS_DUYET_DA_DUYET
            ? 'Da tao bai giang va duyet ngay.'
            : 'Da tao bai giang nhap.';

        return redirect()->route('admin.bai-giang.index')->with('success', $message);
    }

    public function edit($id)
    {
        $baiGiang = BaiGiang::with(['taiNguyenChinh', 'taiNguyenPhu', 'phongHocLive'])->findOrFail($id);
        $phanCongs = $this->getAssignmentOptions();
        $thuVien = TaiNguyenBuoiHoc::query()->daDuyet()->get();
        $moderatorOptions = $this->getModeratorOptions();

        return view('pages.admin.bai-giang.edit', [
            'baiGiang' => $baiGiang,
            'phanCongs' => $phanCongs,
            'thuVien' => $thuVien,
            'moderatorOptions' => $moderatorOptions,
            'assistantOptions' => $moderatorOptions,
            'defaultModeratorId' => $baiGiang->phongHocLive?->moderator_id,
        ]);
    }

    public function update(UpsertBaiGiangRequest $request, $id)
    {
        $baiGiang = BaiGiang::with('phongHocLive')->findOrFail($id);
        $validated = $request->validated();
        $phanCong = $this->findAssignedPhanCong((int) $validated['phan_cong_id']);
        $lichHocId = $this->resolveAuthorizedLichHocId(
            isset($validated['lich_hoc_id']) ? (int) $validated['lich_hoc_id'] : null,
            $phanCong
        );
        [$taiNguyenChinhId, $taiNguyenPhuIds] = $this->resolveApprovedResources(
            isset($validated['tai_nguyen_chinh_id']) ? (int) $validated['tai_nguyen_chinh_id'] : null,
            $validated['tai_nguyen_phu_ids'] ?? []
        );

        $lectureStatus = $this->resolveLectureApprovalStatus($validated['hanh_dong'] ?? 'luu_nhap');

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
                'ngay_gui_duyet' => in_array($lectureStatus, [BaiGiang::STATUS_DUYET_CHO, BaiGiang::STATUS_DUYET_DA_DUYET], true) ? now() : null,
                'ngay_duyet' => $lectureStatus === BaiGiang::STATUS_DUYET_DA_DUYET ? now() : null,
                'nguoi_duyet_id' => $lectureStatus === BaiGiang::STATUS_DUYET_DA_DUYET ? auth()->user()->id : null,
            ]);

            $baiGiang->taiNguyenPhu()->sync($taiNguyenPhuIds);

            $this->liveLectureService->syncLiveRoom($baiGiang, $validated, auth()->user(), true);
        });

        $message = $lectureStatus === BaiGiang::STATUS_DUYET_DA_DUYET
            ? 'Đã cập nhật bài giảng và duyệt ngay.'
            : 'Đã cập nhật bài giảng nháp.';

        return redirect()->route('admin.bai-giang.index')->with('success', $message);
    }

    public function show($id)
    {
        $baiGiang = BaiGiang::with([
            'khoaHoc',
            'moduleHoc',
            'lichHoc',
            'nguoiTao',
            'taiNguyenChinh',
            'taiNguyenPhu',
            'phongHocLive.moderator',
            'phongHocLive.troGiang',
            'phongHocLive.banGhis',
        ])->findOrFail($id);

        return view('pages.admin.bai-giang.show', compact('baiGiang'));
    }

    public function duyet(Request $request, $id)
    {
        $baiGiang = BaiGiang::with('phongHocLive')->findOrFail($id);

        $validated = $request->validate([
            'trang_thai_duyet' => 'required|in:da_duyet,can_chinh_sua,tu_choi',
            'ghi_chu_admin' => 'nullable|string',
        ]);

        $this->liveLectureService->syncApprovalState(
            $baiGiang,
            $validated['trang_thai_duyet'],
            $validated['ghi_chu_admin'] ?? null,
            auth()->user()
        );

        return back()->with('success', 'Đã cập nhật trạng thái phê duyệt bài giảng.');
    }

    public function congBo(Request $request, $id)
    {
        $baiGiang = BaiGiang::with('phongHocLive')->findOrFail($id);

        if ($baiGiang->trang_thai_duyet !== BaiGiang::STATUS_DUYET_DA_DUYET) {
            return back()->with('error', 'Bai giang phai duoc duyet truoc khi cong bo.');
        }

        $trangThaiMoi = $this->liveLectureService->togglePublication($baiGiang);

        $msg = $trangThaiMoi === BaiGiang::CONG_BO_DA_CONG_BO ? 'Da cong bo bai giang.' : 'Da an bai giang.';
        return back()->with('success', $msg);
    }

    public function getLichHoc(Request $request)
    {
        $request->validate([
            'phan_cong_id' => 'required|integer',
        ]);

        $phanCong = $this->findAssignedPhanCong($request->integer('phan_cong_id'));

        $lichHocs = LichHoc::query()
            ->where('khoa_hoc_id', $phanCong->khoa_hoc_id)
            ->where('module_hoc_id', $phanCong->module_hoc_id)
            ->orderBy('buoi_so')
            ->get()
            ->map(fn (LichHoc $lichHoc) => $this->formatScheduleForLectureForm($lichHoc));

        return response()->json($lichHocs);
    }

    private function formatScheduleForLectureForm(LichHoc $lichHoc): array
    {
        $signal = strtolower((string) $lichHoc->nen_tang . ' ' . (string) $lichHoc->link_online);
        $platform = str_contains($signal, 'google') || str_contains($signal, 'meet.google.com')
            ? PhongHocLive::PLATFORM_GOOGLE_MEET
            : (str_contains($signal, 'zoom') || filled($lichHoc->link_online) ? PhongHocLive::PLATFORM_ZOOM : PhongHocLive::PLATFORM_INTERNAL);

        return [
            'id' => $lichHoc->id,
            'buoi_so' => $lichHoc->buoi_so,
            'ngay_hoc' => optional($lichHoc->ngay_hoc)->toDateString(),
            'starts_at' => optional($lichHoc->starts_at)->format('Y-m-d\TH:i'),
            'duration_minutes' => $lichHoc->starts_at && $lichHoc->ends_at
                ? max(15, $lichHoc->starts_at->diffInMinutes($lichHoc->ends_at))
                : 90,
            'platform' => $platform,
            'link_online' => \App\Support\OnlineMeetingUrl::normalize($lichHoc->link_online),
            'meeting_id' => $lichHoc->meeting_id,
            'meeting_code' => \App\Support\OnlineMeetingUrl::meetingCode($lichHoc->link_online),
            'passcode' => $lichHoc->mat_khau_cuoc_hop,
        ];
    }

    private function findAssignedPhanCong(int $phanCongId): PhanCongModuleGiangVien
    {
        $phanCong = PhanCongModuleGiangVien::query()
            ->with(['khoaHoc', 'moduleHoc', 'giangVien.nguoiDung'])
            ->whereKey($phanCongId)
            ->where('trang_thai', 'da_nhan')
            ->first();

        if (!$phanCong) {
            throw ValidationException::withMessages([
                'phan_cong_id' => 'Phan cong da chon khong hop le hoac chua duoc xac nhan.',
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
                'lich_hoc_id' => 'Buoi hoc da chon khong thuoc module cua phan cong nay.',
            ]);
        }

        return $lichHocId;
    }

    /**
     * @param array<int, mixed> $taiNguyenPhuIds
     * @return array{0: int|null, 1: array<int, int>}
     */
    private function resolveApprovedResources(?int $taiNguyenChinhId, array $taiNguyenPhuIds): array
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

        $approvedIds = TaiNguyenBuoiHoc::query()
            ->whereIn('id', $resourceIds->all())
            ->where('trang_thai_duyet', TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($taiNguyenChinhId !== null && !in_array($taiNguyenChinhId, $approvedIds, true)) {
            throw ValidationException::withMessages([
                'tai_nguyen_chinh_id' => 'Tai nguyen chinh da chon khong hop le hoac chua duoc duyet.',
            ]);
        }

        $invalidPhuIds = $normalizedPhuIds
            ->reject(fn (int $id) => in_array($id, $approvedIds, true))
            ->values();

        if ($invalidPhuIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'tai_nguyen_phu_ids' => 'Co tai nguyen phu khong hop le hoac chua duoc duyet.',
            ]);
        }

        return [$taiNguyenChinhId, $normalizedPhuIds->all()];
    }

    private function resolveLectureApprovalStatus(string $action): string
    {
        return $action === 'duyet_ngay'
            ? BaiGiang::STATUS_DUYET_DA_DUYET
            : BaiGiang::STATUS_DUYET_NHAP;
    }

    private function getAssignmentOptions()
    {
        return PhanCongModuleGiangVien::with(['khoaHoc', 'moduleHoc', 'giangVien.nguoiDung'])
            ->where('trang_thai', 'da_nhan')
            ->orderByDesc('created_at')
            ->get();
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
