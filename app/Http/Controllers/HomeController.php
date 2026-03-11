<?php

namespace App\Http\Controllers;

use App\Models\GiangVien;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ
     */
    public function index(Request $request)
    {
        // Nếu đã đăng nhập VÀ không ở chế xem trước thì redirect đến dashboard theo vai trò
        if (auth()->check() && !$request->has('preview')) {
            $user = auth()->user();
            
            if (!isset($user->vai_tro)) {
                abort(403, 'Người dùng không có vai trò xác định');
            }
            
            if ($user->vai_tro === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->vai_tro === 'giang_vien') {
                return redirect()->route('giang-vien.dashboard');
            } else {
                return redirect()->route('hoc-vien.dashboard');
            }
        }
        
        // Nếu chưa đăng nhập hoặc đã request preview, hiển thị trang chủ public
        // Lấy danh sách giảng viên hiển thị trên trang chủ
        $giangVienFeatured = GiangVien::hienThiTrangChu()
            ->with('nguoiDung')
            ->paginate(6);
        
        // Lấy cài đặt hệ thống
        $settings = [
            'site_name' => SystemSetting::get('site_name', ''),
            'site_logo' => SystemSetting::get('site_logo', ''),
            'hotline' => SystemSetting::get('hotline', ''),
            'zalo' => SystemSetting::get('zalo', ''),
            'facebook' => SystemSetting::get('facebook', ''),
            'email' => SystemSetting::get('email', ''),
        ];
        
        return view('pages.home.index', [
            'giangVienFeatured' => $giangVienFeatured,
            'settings' => $settings,
        ]);
    }

    /**
     * Search instructors
     */
    public function searchGiangVien(Request $request)
    {
        $keyword = $request->get('q', '');
        $loaiSearch = $request->get('type', 'all');

        $query = GiangVien::hienThiTrangChu()->with('nguoiDung');

        if ($keyword) {
            $query->whereHas('nguoiDung', function($q) use ($keyword) {
                $q->where('ho_ten', 'LIKE', "%{$keyword}%")
                  ->orWhere('email', 'LIKE', "%{$keyword}%");
            })->orWhere('chuyen_nganh', 'LIKE', "%{$keyword}%");
        }

        if ($loaiSearch !== 'all') {
            $query->where('chuyen_nganh', $loaiSearch);
        }

        $results = $query->paginate(12);

        return view('pages.home.search-results', [
            'giangVien' => $results,
            'keyword' => $keyword,
            'loaiSearch' => $loaiSearch,
        ]);
    }
}