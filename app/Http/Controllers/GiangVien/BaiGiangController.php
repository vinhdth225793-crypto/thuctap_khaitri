<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\PhanCongModuleGiangVien;
use App\Models\BaiGiang;
use App\Models\LichHoc;
use Illuminate\Http\Request;

class BaiGiangController extends Controller
{
    public function index()
    {
        $giangVien = auth()->user()->giangVien;
        if (!$giangVien) {
            return redirect()->route('home')->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        $baiGiangs = BaiGiang::with(['khoaHoc', 'moduleHoc', 'lichHoc', 'taiNguyenChinh'])
            ->where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pages.giang-vien.bai-giang.list', compact('baiGiangs'));
    }

    public function create(Request $request)
    {
        $giangVien = auth()->user()->giangVien;
        
        // Lấy danh sách khóa học và module mà giảng viên được phân công
        $phanCongs = PhanCongModuleGiangVien::with(['khoaHoc', 'moduleHoc'])
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->get();

        // Lấy thư viện tài nguyên đã duyệt của giảng viên
        $thuVien = TaiNguyenBuoiHoc::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)
            ->daDuyet()
            ->get();

        return view('pages.giang-vien.bai-giang.create', compact('phanCongs', 'thuVien'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'phan_cong_id' => 'required|exists:phan_cong_module_giang_vien,id',
            'lich_hoc_id' => 'nullable|exists:lich_hoc,id',
            'loai_bai_giang' => 'required|string',
            'tai_nguyen_chinh_id' => 'nullable|exists:tai_nguyen_buoi_hoc,id',
            'tai_nguyen_phu_ids' => 'nullable|array',
            'tai_nguyen_phu_ids.*' => 'exists:tai_nguyen_buoi_hoc,id',
            'thoi_diem_mo' => 'nullable|date',
            'thu_tu_hien_thi' => 'nullable|integer',
        ]);

        $phanCong = PhanCongModuleGiangVien::findOrFail($validated['phan_cong_id']);

        $baiGiang = BaiGiang::create([
            'khoa_hoc_id' => $phanCong->khoa_hoc_id,
            'module_hoc_id' => $phanCong->module_hoc_id,
            'lich_hoc_id' => $validated['lich_hoc_id'],
            'nguoi_tao_id' => auth()->user()->ma_nguoi_dung,
            'tieu_de' => $validated['tieu_de'],
            'mo_ta' => $validated['mo_ta'],
            'loai_bai_giang' => $validated['loai_bai_giang'],
            'tai_nguyen_chinh_id' => $validated['tai_nguyen_chinh_id'],
            'thu_tu_hien_thi' => $validated['thu_tu_hien_thi'] ?? 0,
            'thoi_diem_mo' => $validated['thoi_diem_mo'],
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_NHAP,
            'trang_thai_cong_bo' => BaiGiang::CONG_BO_AN,
        ]);

        if (!empty($validated['tai_nguyen_phu_ids'])) {
            $baiGiang->taiNguyenPhu()->attach($validated['tai_nguyen_phu_ids']);
        }

        return redirect()->route('giang-vien.bai-giang.index')->with('success', 'Đã tạo bài giảng mới.');
    }

    public function edit($id)
    {
        $baiGiang = BaiGiang::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)->findOrFail($id);
        
        $giangVien = auth()->user()->giangVien;
        $phanCongs = PhanCongModuleGiangVien::with(['khoaHoc', 'moduleHoc'])
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->get();

        $thuVien = TaiNguyenBuoiHoc::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)
            ->daDuyet()
            ->get();

        return view('pages.giang-vien.bai-giang.edit', compact('baiGiang', 'phanCongs', 'thuVien'));
    }

    public function update(Request $request, $id)
    {
        $baiGiang = BaiGiang::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)->findOrFail($id);

        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'phan_cong_id' => 'required|exists:phan_cong_module_giang_vien,id',
            'lich_hoc_id' => 'nullable|exists:lich_hoc,id',
            'loai_bai_giang' => 'required|string',
            'tai_nguyen_chinh_id' => 'nullable|exists:tai_nguyen_buoi_hoc,id',
            'tai_nguyen_phu_ids' => 'nullable|array',
            'tai_nguyen_phu_ids.*' => 'exists:tai_nguyen_buoi_hoc,id',
            'thoi_diem_mo' => 'nullable|date',
            'thu_tu_hien_thi' => 'nullable|integer',
        ]);

        $phanCong = PhanCongModuleGiangVien::findOrFail($validated['phan_cong_id']);

        $baiGiang->update([
            'khoa_hoc_id' => $phanCong->khoa_hoc_id,
            'module_hoc_id' => $phanCong->module_hoc_id,
            'lich_hoc_id' => $validated['lich_hoc_id'],
            'tieu_de' => $validated['tieu_de'],
            'mo_ta' => $validated['mo_ta'],
            'loai_bai_giang' => $validated['loai_bai_giang'],
            'tai_nguyen_chinh_id' => $validated['tai_nguyen_chinh_id'],
            'thu_tu_hien_thi' => $validated['thu_tu_hien_thi'] ?? 0,
            'thoi_diem_mo' => $validated['thoi_diem_mo'],
        ]);

        $baiGiang->taiNguyenPhu()->sync($validated['tai_nguyen_phu_ids'] ?? []);

        return redirect()->route('giang-vien.bai-giang.index')->with('success', 'Đã cập nhật bài giảng.');
    }

    public function guiDuyet($id)
    {
        $baiGiang = BaiGiang::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)->findOrFail($id);
        
        $baiGiang->update([
            'trang_thai_duyet' => BaiGiang::STATUS_DUYET_CHO,
            'ngay_gui_duyet' => now(),
        ]);

        return back()->with('success', 'Đã gửi yêu cầu duyệt bài giảng.');
    }

    public function destroy($id)
    {
        $baiGiang = BaiGiang::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)->findOrFail($id);
        $baiGiang->delete();

        return redirect()->route('giang-vien.bai-giang.index')->with('success', 'Đã xóa bài giảng.');
    }

    /**
     * Helper AJAX: Lấy danh sách lịch học theo Module
     */
    public function getLichHoc(Request $request)
    {
        $phanCongId = $request->phan_cong_id;
        $phanCong = PhanCongModuleGiangVien::findOrFail($phanCongId);
        
        $lichHocs = LichHoc::where('khoa_hoc_id', $phanCong->khoa_hoc_id)
            ->where('module_hoc_id', $phanCong->module_hoc_id)
            ->orderBy('buoi_so')
            ->get(['id', 'buoi_so', 'ngay_hoc']);

        return response()->json($lichHocs);
    }
}
