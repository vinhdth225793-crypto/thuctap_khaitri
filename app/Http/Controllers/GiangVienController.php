<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\NguoiDung;
use App\Models\PhanCongModuleGiangVien;

class GiangVienController extends Controller
{
    public function __construct()
    {
        // chỉ giảng viên hoặc admin mới được vào
        $this->middleware(['auth', \App\Http\Middleware\CheckGiangVien::class]);
    }

    public function profile()
    {
        $user = auth()->user();
        // load thông tin giảng viên nếu đã tồn tại
        $user->load('giangVien');
        return view('pages.giang-vien.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dung,email,' . $user->ma_nguoi_dung . ',ma_nguoi_dung',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date|before:today',
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mat_khau' => 'nullable|min:8|confirmed',
            // trường riêng giảng viên
            'chuyen_nganh' => 'nullable|string|max:255',
            'hoc_vi' => 'nullable|string|max:255',
            'so_gio_day' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['ho_ten','email','so_dien_thoai','ngay_sinh','dia_chi','trang_thai']);

        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        if ($request->hasFile('anh_dai_dien')) {
            if ($user->anh_dai_dien && Storage::disk('public')->exists($user->anh_dai_dien)) {
                Storage::disk('public')->delete($user->anh_dai_dien);
            }
            $data['anh_dai_dien'] = $request->file('anh_dai_dien')->store('avatars','public');
        }
        if ($request->has('xoa_anh_dai_dien') && $user->anh_dai_dien) {
            Storage::disk('public')->delete($user->anh_dai_dien);
            $data['anh_dai_dien'] = null;
        }

        $user->update($data);

        // cập nhật thông tin bổ sung
        $giang = $user->giangVien;
        if (!$giang) {
            $giang = $user->giangVien()->create([]);
        }
        $giang->update($request->only(['chuyen_nganh','hoc_vi','so_gio_day']));

        return redirect()->route('giang-vien.profile')->with('success', 'Cập nhật thông tin thành công');
    }

    /**
     * phanCong()
     * Load tất cả phân công của GV này, chia 3 nhóm
     */
    public function phanCong()
    {
        $giangVien = auth()->user()->giangVien;

        if (!$giangVien) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin giảng viên để truy xuất phân công.');
        }

        $phanCongChoXacNhan = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.monHoc'])
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'cho_xac_nhan')
            ->latest('ngay_phan_cong')
            ->get();

        $phanCongDaNhan = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.monHoc'])
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->latest('ngay_phan_cong')
            ->get();

        $lichSu = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.monHoc'])
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'tu_choi')
            ->latest()
            ->get();

        return view('pages.giang-vien.phan-cong', compact('phanCongChoXacNhan', 'phanCongDaNhan', 'lichSu'));
    }

    /**
     * xacNhanPhanCong($id)
     */
    public function xacNhanPhanCong($id)
    {
        $giangVien = auth()->user()->giangVien;
        
        $phanCong = PhanCongModuleGiangVien::where('id', $id)
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'cho_xac_nhan')
            ->firstOrFail();

        $phanCong->update(['trang_thai' => 'da_nhan']);

        return redirect()->back()->with('success', 'Đã xác nhận nhận dạy module: ' . $phanCong->moduleHoc->ten_module);
    }

    /**
     * tuChoiPhanCong($id)
     */
    public function tuChoiPhanCong($id)
    {
        $giangVien = auth()->user()->giangVien;

        $phanCong = PhanCongModuleGiangVien::where('id', $id)
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'cho_xac_nhan')
            ->firstOrFail();

        $phanCong->update(['trang_thai' => 'tu_choi']);

        return redirect()->back()->with('success', 'Đã từ chối phân công module: ' . $phanCong->moduleHoc->ten_module);
    }
}
