<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaiGiang;
use Illuminate\Http\Request;

class BaiGiangController extends Controller
{
    public function index(Request $request)
    {
        $query = BaiGiang::with(['khoaHoc', 'moduleHoc', 'nguoiTao'])->orderBy('created_at', 'desc');

        if ($request->filled('trang_thai_duyet')) {
            $query->where('trang_thai_duyet', $request->trang_thai_duyet);
        }

        $baiGiangs = $query->paginate(20);

        return view('pages.admin.bai-giang.index', compact('baiGiangs'));
    }

    public function show($id)
    {
        $baiGiang = BaiGiang::with(['khoaHoc', 'moduleHoc', 'lichHoc', 'nguoiTao', 'taiNguyenChinh', 'taiNguyenPhu'])->findOrFail($id);
        return view('pages.admin.bai-giang.show', compact('baiGiang'));
    }

    public function duyet(Request $request, $id)
    {
        $baiGiang = BaiGiang::findOrFail($id);

        $validated = $request->validate([
            'trang_thai_duyet' => 'required|in:da_duyet,can_chinh_sua,tu_choi',
            'ghi_chu_admin' => 'nullable|string',
        ]);

        $baiGiang->update([
            'trang_thai_duyet' => $validated['trang_thai_duyet'],
            'ghi_chu_admin' => $validated['ghi_chu_admin'],
            'ngay_duyet' => now(),
            'nguoi_duyet_id' => auth()->user()->ma_nguoi_dung,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái phê duyệt bài giảng.');
    }

    public function congBo(Request $request, $id)
    {
        $baiGiang = BaiGiang::findOrFail($id);
        
        if ($baiGiang->trang_thai_duyet !== BaiGiang::STATUS_DUYET_DA_DUYET) {
            return back()->with('error', 'Bài giảng phải được duyệt trước khi công bố.');
        }

        $trangThaiMoi = $baiGiang->trang_thai_cong_bo === BaiGiang::CONG_BO_DA_CONG_BO 
            ? BaiGiang::CONG_BO_AN 
            : BaiGiang::CONG_BO_DA_CONG_BO;

        $baiGiang->update(['trang_thai_cong_bo' => $trangThaiMoi]);

        $msg = $trangThaiMoi === BaiGiang::CONG_BO_DA_CONG_BO ? 'Đã công bố bài giảng.' : 'Đã ẩn bài giảng.';
        return back()->with('success', $msg);
    }
}
