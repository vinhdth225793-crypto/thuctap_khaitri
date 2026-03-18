<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\NguoiDung;
use App\Models\HocVienKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;

class HocVienController extends Controller
{
    public function __construct()
    {
        // sử dụng tên lớp middleware trực tiếp để tránh lỗi khi alias không được tải
        $this->middleware(['auth', \App\Http\Middleware\CheckHocVien::class]);
    }

    /**
     * Danh sách khóa học của học viên (Phase 5)
     */
    public function khoaHocCuaToi()
    {
        $user = auth()->user();
        
        // Lấy các khóa học học viên đang tham gia thông qua bảng trung gian
        $khoaHocs = HocVienKhoaHoc::with(['khoaHoc.nhomNganh'])
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->get();
            
        return view('pages.hoc-vien.khoa-hoc.index', compact('khoaHocs'));
    }

    /**
     * Chi tiết khóa học và tài nguyên buổi học (Phase 5)
     */
    public function chiTietKhoaHoc($id)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền truy cập (Học viên phải thuộc khóa học này)
        $isMember = HocVienKhoaHoc::where('khoa_hoc_id', $id)
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->exists();
            
        if (!$isMember) {
            return redirect()->route('hoc-vien.khoa-hoc-cua-toi')->with('error', 'Bạn không có quyền truy cập khóa học này.');
        }

        $khoaHoc = KhoaHoc::with(['nhomNganh', 'moduleHocs'])->findOrFail($id);
        
        // Lấy lịch học kèm tài nguyên (chỉ lấy tài nguyên đã công khai)
        $lichHocs = LichHoc::with(['moduleHoc', 'taiNguyen' => function($query) {
                $query->where('trang_thai_hien_thi', 'hien')->orderBy('thu_tu_hien_thi');
            }])
            ->where('khoa_hoc_id', $id)
            ->orderBy('ngay_hoc')
            ->orderBy('gio_bat_dau')
            ->get();

        return view('pages.hoc-vien.khoa-hoc.show', compact('khoaHoc', 'lichHocs'));
    }

    public function profile()
    {
        $user = auth()->user();
        $user->load('hocVien');
        return view('pages.hoc-vien.profile', compact('user'));
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
            // thông tin riêng học viên
            'lop' => 'nullable|string|max:50',
            'nganh' => 'nullable|string|max:255',
            'diem_trung_binh' => 'nullable|numeric|min:0|max:10',
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

        $hv = $user->hocVien;
        if (!$hv) {
            $hv = $user->hocVien()->create([]);
        }
        $hv->update($request->only(['lop','nganh','diem_trung_binh']));

        return redirect()->route('hoc-vien.profile')->with('success', 'Cập nhật thông tin thành công');
    }
}
