<?php

namespace App\Http\Controllers\HocVien;

use App\Http\Controllers\Controller;
use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\HocVienKhoaHoc;
use Illuminate\Http\Request;

class BaiKiemTraController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $baiKiemTras = $this->queryBaiKiemTraHocVien($user->ma_nguoi_dung)
            ->get()
            ->sortBy(function (BaiKiemTra $baiKiemTra) {
                $priority = match ($baiKiemTra->access_status_key) {
                    'dang_mo' => 1,
                    'sap_mo' => 2,
                    'da_dong' => 3,
                    default => 4,
                };

                $timestamp = $baiKiemTra->ngay_mo?->timestamp ?? 0;

                return sprintf('%s-%s', $priority, str_pad((string) $timestamp, 12, '0', STR_PAD_LEFT));
            })
            ->values();

        $stats = [
            'tong' => $baiKiemTras->count(),
            'dang_mo' => $baiKiemTras->where('access_status_key', 'dang_mo')->count(),
            'sap_mo' => $baiKiemTras->where('access_status_key', 'sap_mo')->count(),
            'da_nop' => $baiKiemTras->filter(fn (BaiKiemTra $item) => optional($item->baiLams->first())->is_submitted)->count(),
        ];

        return view('pages.hoc-vien.bai-kiem-tra.index', compact('baiKiemTras', 'stats'));
    }

    public function show(int $id)
    {
        $baiKiemTra = $this->findBaiKiemTraHocVien($id, auth()->user()->ma_nguoi_dung);
        $baiLam = $baiKiemTra->baiLams->first();

        return view('pages.hoc-vien.bai-kiem-tra.show', compact('baiKiemTra', 'baiLam'));
    }

    public function batDau(int $id)
    {
        $user = auth()->user();
        $baiKiemTra = $this->findBaiKiemTraHocVien($id, $user->ma_nguoi_dung);

        if (!$baiKiemTra->can_student_start) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('error', 'Bai kiem tra nay chua mo hoac da dong.');
        }

        $baiLam = BaiLamBaiKiemTra::firstOrNew([
            'bai_kiem_tra_id' => $baiKiemTra->id,
            'hoc_vien_id' => $user->ma_nguoi_dung,
        ]);

        if ($baiLam->is_submitted) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('info', 'Ban da nop bai kiem tra nay roi.');
        }

        if (!$baiLam->exists) {
            $baiLam->trang_thai = 'dang_lam';
        }

        if (!$baiLam->bat_dau_luc) {
            $baiLam->bat_dau_luc = now();
        }

        $baiLam->save();

        return redirect()
            ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
            ->with('success', 'Da bat dau lam bai. Hay nhap noi dung bai lam va nop bai khi hoan thanh.');
    }

    public function nopBai(Request $request, int $id)
    {
        $user = auth()->user();
        $baiKiemTra = $this->findBaiKiemTraHocVien($id, $user->ma_nguoi_dung);

        if (!$baiKiemTra->can_student_start) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('error', 'Khong the nop bai vi bai kiem tra nay da dong hoac chua den gio mo.');
        }

        $request->validate([
            'noi_dung_bai_lam' => 'required|string|max:50000',
        ]);

        $baiLam = BaiLamBaiKiemTra::firstOrNew([
            'bai_kiem_tra_id' => $baiKiemTra->id,
            'hoc_vien_id' => $user->ma_nguoi_dung,
        ]);

        if ($baiLam->is_submitted) {
            return redirect()
                ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
                ->with('info', 'Ban da nop bai kiem tra nay roi.');
        }

        if (!$baiLam->bat_dau_luc) {
            $baiLam->bat_dau_luc = now();
        }

        $baiLam->noi_dung_bai_lam = $request->noi_dung_bai_lam;
        $baiLam->trang_thai = 'da_nop';
        $baiLam->nop_luc = now();
        $baiLam->save();

        return redirect()
            ->route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id)
            ->with('success', 'Da nop bai kiem tra thanh cong.');
    }

    private function queryBaiKiemTraHocVien(int $hocVienId)
    {
        $khoaHocIds = HocVienKhoaHoc::query()
            ->where('hoc_vien_id', $hocVienId)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->pluck('khoa_hoc_id');

        return BaiKiemTra::query()
            ->where('trang_thai', true)
            ->whereIn('khoa_hoc_id', $khoaHocIds)
            ->with([
                'khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                'moduleHoc:id,ten_module,ma_module',
                'lichHoc:id,khoa_hoc_id,module_hoc_id,buoi_so,ngay_hoc',
                'baiLams' => fn ($query) => $query->where('hoc_vien_id', $hocVienId),
            ])
            ->orderByDesc('created_at');
    }

    private function findBaiKiemTraHocVien(int $id, int $hocVienId): BaiKiemTra
    {
        return $this->queryBaiKiemTraHocVien($hocVienId)
            ->where('id', $id)
            ->firstOrFail();
    }
}
