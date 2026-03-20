<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaiKiemTra;
use Illuminate\Http\Request;

class BaiKiemTraPheDuyetController extends Controller
{
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
        ])->findOrFail($id);

        return view('pages.admin.kiem-tra-online.phe-duyet.show', compact('baiKiemTra'));
    }

    public function approve(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::findOrFail($id);

        if ($baiKiemTra->chiTietCauHois()->count() === 0 && blank($baiKiemTra->mo_ta)) {
            return back()->with('error', 'De chua co cau hoi hoac noi dung de duyet.');
        }

        $baiKiemTra->update([
            'trang_thai_duyet' => 'da_duyet',
            'nguoi_duyet_id' => auth()->id(),
            'duyet_luc' => now(),
            'ghi_chu_duyet' => $request->input('ghi_chu_duyet'),
        ]);

        return back()->with('success', 'Da duyet bai kiem tra.');
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

        return back()->with('success', 'Da tu choi bai kiem tra.');
    }

    public function publish(int $id)
    {
        $baiKiemTra = BaiKiemTra::findOrFail($id);

        if ($baiKiemTra->trang_thai_duyet !== 'da_duyet') {
            return back()->with('error', 'Chi bai da duyet moi duoc phat hanh.');
        }

        $baiKiemTra->update([
            'trang_thai_phat_hanh' => 'phat_hanh',
            'phat_hanh_luc' => now(),
            'trang_thai' => true,
        ]);

        return back()->with('success', 'Da phat hanh bai kiem tra cho hoc vien.');
    }

    public function close(int $id)
    {
        $baiKiemTra = BaiKiemTra::findOrFail($id);
        $baiKiemTra->update([
            'trang_thai_phat_hanh' => 'dong',
        ]);

        return back()->with('success', 'Da dong bai kiem tra.');
    }
}
