<?php

namespace App\Http\Controllers;

use App\Models\PhanCongModuleGiangVien;
use App\Services\TeacherAssignmentResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GiangVienController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\CheckGiangVien::class]);
    }

    public function dashboard()
    {
        $giangVien = auth()->user()->giangVien;

        if (!$giangVien) {
            return redirect()->route('home')->with('error', 'Tài khoản của bạn chưa được thiết lập hồ sơ giảng viên.');
        }

        $giangVienId = $giangVien->id;

        $stats = [
            'dang_day' => PhanCongModuleGiangVien::where('giang_vien_id', $giangVienId)
                ->where('trang_thai', 'da_nhan')
                ->count(),
            'cho_xac_nhan' => PhanCongModuleGiangVien::where('giang_vien_id', $giangVienId)
                ->where('trang_thai', 'cho_xac_nhan')
                ->count(),
            'tong_hoc_vien' => DB::table('hoc_vien_khoa_hoc')
                ->whereIn('khoa_hoc_id', function ($query) use ($giangVienId) {
                    $query->select('khoa_hoc_id')
                        ->from('phan_cong_module_giang_vien')
                        ->where('giang_vien_id', $giangVienId);
                })
                ->count(),
            'so_gio_day' => $giangVien->so_gio_day ?? 0,
            'buoi_sap_toi' => $giangVien->lichHocs()
                ->whereDate('ngay_hoc', '>=', now()->toDateString())
                ->where('trang_thai', '!=', 'huy')
                ->count(),
            'don_xin_nghi_cho_duyet' => $giangVien->donXinNghis()->where('trang_thai', 'cho_duyet')->count(),
        ];

        $phanCongMoi = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.nhomNganh'])
            ->where('giang_vien_id', $giangVienId)
            ->where('trang_thai', 'cho_xac_nhan')
            ->latest()
            ->take(5)
            ->get();

        $lopDangDay = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.nhomNganh', 'moduleHoc.lichHocs'])
            ->where('giang_vien_id', $giangVienId)
            ->where('trang_thai', 'da_nhan')
            ->latest()
            ->take(5)
            ->get();

        $lichHomNay = $giangVien->lichHocs()
            ->with(['khoaHoc', 'moduleHoc'])
            ->whereDate('ngay_hoc', now()->toDateString())
            ->where('trang_thai', '!=', 'huy')
            ->orderBy('gio_bat_dau')
            ->get();

        $assignmentMap = app(TeacherAssignmentResolver::class)->mapAcceptedAssignmentsForSchedules($giangVienId, $lichHomNay);
        $lichHomNay->each(function ($lichHoc) use ($assignmentMap) {
            $specificKey = (int) $lichHoc->khoa_hoc_id . ':' . ($lichHoc->module_hoc_id !== null ? (int) $lichHoc->module_hoc_id : '*');
            $fallbackKey = (int) $lichHoc->khoa_hoc_id . ':*';

            $lichHoc->setAttribute('phan_cong_id', $assignmentMap[$specificKey] ?? $assignmentMap[$fallbackKey] ?? null);
        });

        return view('pages.giang-vien.dashboard', compact('stats', 'phanCongMoi', 'lopDangDay', 'lichHomNay'));
    }

    public function profile()
    {
        $user = auth()->user();
        $user->load('giangVien');
        return view('pages.giang-vien.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dung,email,' . $user->id . ',id',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date|before:today',
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mat_khau' => 'nullable|min:8|confirmed',
            'chuyen_nganh' => 'nullable|string|max:255',
            'hoc_vi' => 'nullable|string|max:255',
            'so_gio_day' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['ho_ten', 'email', 'so_dien_thoai', 'ngay_sinh', 'dia_chi', 'trang_thai']);

        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        if ($request->hasFile('anh_dai_dien')) {
            if ($user->anh_dai_dien && Storage::disk('public')->exists($user->anh_dai_dien)) {
                Storage::disk('public')->delete($user->anh_dai_dien);
            }
            $data['anh_dai_dien'] = $request->file('anh_dai_dien')->store('avatars', 'public');
        }
        if ($request->has('xoa_anh_dai_dien') && $user->anh_dai_dien) {
            Storage::disk('public')->delete($user->anh_dai_dien);
            $data['anh_dai_dien'] = null;
        }

        $user->update($data);

        $giang = $user->giangVien;
        if (!$giang) {
            $giang = $user->giangVien()->create([]);
        }
        $giang->update($request->only(['chuyen_nganh', 'hoc_vi', 'so_gio_day']));

        return redirect()->route('giang-vien.profile')->with('success', 'Cập nhật thông tin thành công.');
    }
}
