<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaiNguyenBuoiHoc;
use Illuminate\Http\Request;

class ThuVienController extends Controller
{
    public function index(Request $request)
    {
        $query = TaiNguyenBuoiHoc::with('nguoiTao')->orderBy('created_at', 'desc');

        if ($request->filled('trang_thai_duyet')) {
            $query->where('trang_thai_duyet', $request->trang_thai_duyet);
        }

        if ($request->filled('loai_tai_nguyen')) {
            $query->where('loai_tai_nguyen', $request->loai_tai_nguyen);
        }

        $taiNguyens = $query->paginate(20);

        return view('pages.admin.thu-vien.index', compact('taiNguyens'));
    }

    public function show($id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::with(['nguoiTao', 'nguoiDuyet'])->findOrFail($id);
        return view('pages.admin.thu-vien.show', compact('taiNguyen'));
    }

    public function duyet(Request $request, $id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::findOrFail($id);

        $validated = $request->validate([
            'trang_thai_duyet' => 'required|in:da_duyet,can_chinh_sua,tu_choi',
            'ghi_chu_admin' => 'nullable|string',
        ]);

        $taiNguyen->update([
            'trang_thai_duyet' => $validated['trang_thai_duyet'],
            'ghi_chu_admin' => $validated['ghi_chu_admin'],
            'ngay_duyet' => now(),
            'nguoi_duyet_id' => auth()->user()->ma_nguoi_dung,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái phê duyệt tài nguyên.');
    }

    public function destroy($id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::findOrFail($id);
        
        if ($taiNguyen->duong_dan_file) {
            \Storage::disk('public')->delete($taiNguyen->duong_dan_file);
        }

        $taiNguyen->delete();

        return redirect()->route('admin.thu-vien.index')->with('success', 'Đã xóa tài nguyên khỏi hệ thống.');
    }
}
