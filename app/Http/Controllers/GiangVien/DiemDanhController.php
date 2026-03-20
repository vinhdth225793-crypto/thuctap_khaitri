<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\DiemDanh;
use App\Models\HocVienKhoaHoc;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use App\Services\KetQuaHocTapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DiemDanhController extends Controller
{
    public function __construct(
        private readonly KetQuaHocTapService $ketQuaHocTapService,
    ) {
    }

    /**
     * Láº¥y danh sÃ¡ch há»c viÃªn Ä‘á»ƒ hiá»ƒn thá»‹ trong Modal Ä‘iá»ƒm danh (Flow 4 - Phase 1)
     */
    public function show(Request $request, $lichHocId)
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);

        if ($response = $this->authorizeGiangVienForLichHoc($lichHoc, true)) {
            return $response;
        }

        $hocViens = HocVienKhoaHoc::with('hocVien')
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->where('trang_thai', 'dang_hoc')
            ->orderBy('created_at')
            ->get();

        $diemDanhs = DiemDanh::where('lich_hoc_id', $lichHocId)
            ->get()
            ->keyBy('hoc_vien_id');

        $data = $hocViens->map(function ($item) use ($diemDanhs) {
            $existing = $diemDanhs->get($item->hoc_vien_id);

            return [
                'ma_nguoi_dung' => $item->hoc_vien_id,
                'ho_ten' => $item->hocVien ? $item->hocVien->ho_ten : 'N/A (Há»c viÃªn khÃ´ng tá»“n táº¡i)',
                'trang_thai' => $existing ? $existing->trang_thai : null,
                'ghi_chu' => $existing ? $existing->ghi_chu : '',
            ];
        });

        return response()->json([
            'success' => true,
            'ngay' => $lichHoc->ngay_hoc->format('d/m/Y'),
            'bao_cao' => $lichHoc->bao_cao_giang_vien,
            'trang_thai_bao_cao' => $lichHoc->trang_thai_bao_cao,
            'data' => $data,
        ]);
    }

    /**
     * LÆ°u hoáº·c cáº­p nháº­t dá»¯ liá»‡u Ä‘iá»ƒm danh (Flow 4 - Phase 2)
     */
    public function store(Request $request, $lichHocId)
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $this->authorizeGiangVienForLichHoc($lichHoc);

        $hocVienIds = HocVienKhoaHoc::query()
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->where('trang_thai', 'dang_hoc')
            ->pluck('hoc_vien_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($hocVienIds === []) {
            return back()->with('info', 'KhÃ³a há»c nÃ y hiá»‡n khÃ´ng cÃ³ há»c viÃªn Ä‘ang há»c Ä‘á»ƒ Ä‘iá»ƒm danh.');
        }

        if (!$request->has('attendance') || empty($request->attendance)) {
            return back()->with('info', 'KhÃ´ng cÃ³ dá»¯ liá»‡u Ä‘iá»ƒm danh Ä‘á»ƒ lÆ°u.');
        }

        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.hoc_vien_id' => ['required', 'integer', Rule::in($hocVienIds)],
            'attendance.*.trang_thai' => 'required|in:co_mat,vang_mat,vao_tre',
            'attendance.*.ghi_chu' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $updatedHocVienIds = [];

            foreach ($request->attendance as $item) {
                DiemDanh::updateOrCreate(
                    [
                        'lich_hoc_id' => $lichHocId,
                        'hoc_vien_id' => $item['hoc_vien_id'],
                    ],
                    [
                        'trang_thai' => $item['trang_thai'],
                        'ghi_chu' => $item['ghi_chu'] ?? null,
                    ]
                );

                $updatedHocVienIds[] = (int) $item['hoc_vien_id'];
            }

            DB::commit();

            foreach (array_values(array_unique($updatedHocVienIds)) as $hocVienId) {
                $this->ketQuaHocTapService->refreshForCourseStudent((int) $lichHoc->khoa_hoc_id, $hocVienId);
            }

            return back()->with('success', 'ÄÃ£ lÆ°u dá»¯ liá»‡u Ä‘iá»ƒm danh thÃ nh cÃ´ng.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Lá»—i khi lÆ°u Ä‘iá»ƒm danh: ' . $e->getMessage());
        }
    }

    /**
     * Gá»­i bÃ¡o cÃ¡o Ä‘iá»ƒm danh cho Admin
     */
    public function report(Request $request, $lichHocId)
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $this->authorizeGiangVienForLichHoc($lichHoc);

        $request->validate([
            'bao_cao_giang_vien' => 'required|string|max:1000',
        ]);

        try {
            $lichHoc->update([
                'bao_cao_giang_vien' => $request->bao_cao_giang_vien,
                'thoi_gian_bao_cao' => now(),
                'trang_thai_bao_cao' => 'da_bao_cao',
            ]);

            return back()->with('success', 'ÄÃ£ gá»­i bÃ¡o cÃ¡o Ä‘iá»ƒm danh cho Admin thÃ nh cÃ´ng.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lá»—i khi gá»­i bÃ¡o cÃ¡o: ' . $e->getMessage());
        }
    }

    private function authorizeGiangVienForLichHoc(LichHoc $lichHoc, bool $jsonResponse = false)
    {
        $giangVien = auth()->user()?->giangVien;

        $isAssigned = $giangVien && PhanCongModuleGiangVien::query()
            ->where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->exists();

        if ($isAssigned) {
            return null;
        }

        if ($jsonResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Báº¡n khÃ´ng Ä‘Æ°á»£c phÃ¢n cÃ´ng dáº¡y buá»•i há»c nÃ y.',
            ], 403);
        }

        abort(403, 'Báº¡n khÃ´ng Ä‘Æ°á»£c phÃ¢n cÃ´ng dáº¡y buá»•i há»c nÃ y.');
    }
}
