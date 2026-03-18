<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;

class BaiGiangController extends Controller
{
    /**
     * Phase 7: Tổng hợp danh sách bài giảng và tài liệu của giảng viên
     */
    public function index()
    {
        $giangVien = auth()->user()->giangVien;
        if (!$giangVien) {
            return redirect()->route('home')->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        // Lấy các phân công module mà giảng viên phụ trách
        // Sử dụng subquery counts để tối ưu hiệu năng
        $phanCongs = PhanCongModuleGiangVien::with(['khoaHoc.nhomNganh', 'moduleHoc'])
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->get();

        foreach ($phanCongs as $pc) {
            $pc->tong_tai_nguyen = TaiNguyenBuoiHoc::whereHas('lichHoc', function($q) use ($pc) {
                $q->where('module_hoc_id', $pc->module_hoc_id)
                  ->where('khoa_hoc_id', $pc->khoa_hoc_id);
            })->count();

            $pc->tai_nguyen_cho = TaiNguyenBuoiHoc::whereHas('lichHoc', function($q) use ($pc) {
                $q->where('module_hoc_id', $pc->module_hoc_id)
                  ->where('khoa_hoc_id', $pc->khoa_hoc_id);
            })->where('trang_thai_hien_thi', 'an')->count();
        }

        return view('pages.giang-vien.bai-giang.index', compact('phanCongs'));
    }
}
