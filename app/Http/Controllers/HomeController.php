<?php

namespace App\Http\Controllers;

use App\Models\GiangVien;
use App\Models\SystemSetting;
use App\Models\Banner;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ
     */
    public function index(Request $request)
    {
        // Lấy danh sách giảng viên hiển thị trên trang chủ
        $giangVienFeatured = GiangVien::hienThiTrangChu()
            ->with('nguoiDung')
            ->paginate(6);
        
        // Lấy danh sách banner hiển thị
        $banners = Banner::hienThi()->get();
        
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
            'banners' => $banners,
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