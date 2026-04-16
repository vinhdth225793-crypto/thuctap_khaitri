<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\GiangVien;
use App\Models\HocVienKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\NhomNganh;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

use App\Models\PhanCongModuleGiangVien;
use App\Models\LichHoc;
use App\Models\TaiKhoanChoPheDuyet;
use App\Models\NguoiDung;
use App\Services\StudentLearningDashboardService;

class HomeController extends Controller
{
    protected $studentDashboardService;

    public function __construct(StudentLearningDashboardService $studentDashboardService)
    {
        $this->studentDashboardService = $studentDashboardService;
    }

    public function index(Request $request)
    {
        $settings = $this->buildSettings();
        $user = auth()->user();
        $dashboardData = [];

        if ($user) {
            if ($user->vai_tro === 'hoc_vien') {
                $dashboardData = $this->studentDashboardService->buildFor($user);
            } elseif ($user->vai_tro === 'giang_vien') {
                $dashboardData = $this->buildTeacherDashboard($user);
            } elseif ($user->vai_tro === 'admin') {
                $dashboardData = $this->buildAdminDashboard();
            }
        }

        $publicCourseBase = KhoaHoc::query()
            ->active()
            ->hoatDong()
            ->whereIn('trang_thai_van_hanh', ['cho_giang_vien', 'san_sang', 'dang_day']);

        $keyword = trim((string) $request->get('q', ''));
        $level = $request->get('level');
        $category = $request->get('category');

        $courseQuery = (clone $publicCourseBase)
            ->with([
                'nhomNganh:id,ma_nhom_nganh,ten_nhom_nganh',
            ])
            ->withCount([
                'moduleHocs',
                'lichHocs',
                'hocVienKhoaHocs as hoc_vien_dang_hoc_count' => fn ($query) => $query->where('trang_thai', 'dang_hoc'),
            ]);

        if ($keyword !== '') {
            $courseQuery->where(function ($query) use ($keyword) {
                $query->where('ten_khoa_hoc', 'like', "%{$keyword}%")
                    ->orWhere('ma_khoa_hoc', 'like', "%{$keyword}%")
                    ->orWhere('mo_ta_ngan', 'like', "%{$keyword}%");
            });
        }

        if (in_array($level, ['co_ban', 'trung_binh', 'nang_cao'], true)) {
            $courseQuery->where('cap_do', $level);
        }

        if (filled($category)) {
            $courseQuery->where('nhom_nganh_id', $category);
        }

        $courses = $courseQuery
            ->orderByRaw("
                CASE trang_thai_van_hanh
                    WHEN 'dang_day' THEN 0
                    WHEN 'san_sang' THEN 1
                    WHEN 'cho_giang_vien' THEN 2
                    ELSE 3
                END
            ")
            ->orderByRaw('ngay_khai_giang IS NULL')
            ->orderBy('ngay_khai_giang')
            ->orderByDesc('created_at')
            ->paginate(6)
            ->withQueryString();

        $featuredCourse = (clone $publicCourseBase)
            ->with([
                'nhomNganh:id,ma_nhom_nganh,ten_nhom_nganh',
            ])
            ->withCount([
                'moduleHocs',
                'hocVienKhoaHocs as hoc_vien_dang_hoc_count' => fn ($query) => $query->where('trang_thai', 'dang_hoc'),
            ])
            ->orderByRaw("
                CASE trang_thai_van_hanh
                    WHEN 'dang_day' THEN 0
                    WHEN 'san_sang' THEN 1
                    WHEN 'cho_giang_vien' THEN 2
                    ELSE 3
                END
            ")
            ->orderByRaw('ngay_khai_giang IS NULL')
            ->orderBy('ngay_khai_giang')
            ->orderByDesc('created_at')
            ->first();

        $featuredInstructors = GiangVien::hienThiTrangChu()
            ->with('nguoiDung:ma_nguoi_dung,ho_ten,email,anh_dai_dien')
            ->orderByRaw('CAST(COALESCE(so_gio_day, 0) AS UNSIGNED) DESC')
            ->limit(4)
            ->get();

        $categories = NhomNganh::query()
            ->active()
            ->select('nhom_nganh.*')
            ->selectSub(function ($query) {
                $query->from('khoa_hoc')
                    ->selectRaw('count(*)')
                    ->whereColumn('khoa_hoc.nhom_nganh_id', 'nhom_nganh.id')
                    ->where('khoa_hoc.trang_thai', 1)
                    ->where('khoa_hoc.loai', 'hoat_dong')
                    ->whereIn('khoa_hoc.trang_thai_van_hanh', ['cho_giang_vien', 'san_sang', 'dang_day']);
            }, 'public_course_count')
            ->having('public_course_count', '>', 0)
            ->orderByDesc('public_course_count')
            ->orderBy('ten_nhom_nganh')
            ->limit(6)
            ->get();

        $stats = [
            'tong_khoa_hoc' => (clone $publicCourseBase)->count(),
            'tong_hoc_vien' => HocVienKhoaHoc::query()
                ->whereIn('khoa_hoc_id', (clone $publicCourseBase)->select('id'))
                ->where('trang_thai', 'dang_hoc')
                ->count(),
            'tong_module' => (clone $publicCourseBase)
                ->withCount('moduleHocs')
                ->get()
                ->sum('module_hocs_count'),
            'tong_giang_vien_noi_bat' => GiangVien::hienThiTrangChu()->count(),
            'sap_khai_giang' => (clone $publicCourseBase)
                ->whereDate('ngay_khai_giang', '>=', today())
                ->count(),
        ];

        $heroBanner = Banner::hienThi()
            ->where('thu_tu', 0)
            ->orderByDesc('created_at')
            ->first();

        $sliderBanners = Banner::hienThi()
            ->where('thu_tu', '>=', 1)
            ->limit(8)
            ->get();

        return view('pages.home.index', [
            'settings' => $settings,
            'heroBanner' => $heroBanner,
            'sliderBanners' => $sliderBanners,
            'banners' => $sliderBanners,
            'courses' => $courses,
            'featuredCourse' => $featuredCourse,
            'featuredInstructors' => $featuredInstructors,
            'categories' => $categories,
            'stats' => $stats,
            'dashboardData' => $dashboardData,
            'filters' => [
                'q' => $keyword,
                'level' => $level,
                'category' => $category,
            ],
        ]);
    }

    private function buildTeacherDashboard(NguoiDung $user): array
    {
        $giangVien = $user->giangVien;
        if (!$giangVien) return [];

        $today = now()->toDateString();
        
        $lichDayHomNay = LichHoc::query()
            ->whereHas('phanCongGiangViens', function ($q) use ($giangVien) {
                $q->where('giang_vien_id', $giangVien->id)
                  ->where('trang_thai', 'da_nhan');
            })
            ->whereDate('ngay_hoc', $today)
            ->with([
                'khoaHoc',
                'moduleHoc',
                'teacherAttendanceLogs' => fn ($query) => $query->where('giang_vien_id', $giangVien->id),
            ])
            ->orderBy('gio_bat_dau')
            ->get();

        $phanCongChoXN = PhanCongModuleGiangVien::where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'cho_xac_nhan')
            ->count();

        return [
            'lichDayHomNay' => $lichDayHomNay,
            'phanCongChoXN' => $phanCongChoXN,
            'giangVien' => $giangVien,
        ];
    }

    private function buildAdminDashboard(): array
    {
        return [
            'taiKhoanChoDuyet' => TaiKhoanChoPheDuyet::count(),
            'hocVienMoiHomNay' => NguoiDung::where('vai_tro', 'hoc_vien')
                ->whereDate('created_at', today())
                ->count(),
            'phanCongChoXN' => PhanCongModuleGiangVien::where('trang_thai', 'cho_xac_nhan')->count(),
        ];
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    public function searchGiangVien(Request $request)
    {
        return $this->search($request);
    }

    private function buildSettings(): array
    {
        return [
            'site_name' => SystemSetting::get('site_name', config('app.name', 'Khải Trí')),
            'site_logo' => SystemSetting::get('site_logo', ''),
            'hotline' => SystemSetting::get('hotline', ''),
            'zalo' => SystemSetting::get('zalo', ''),
            'facebook' => SystemSetting::get('facebook', ''),
            'email' => SystemSetting::get('email', ''),
            'address' => SystemSetting::get('address', ''),
            'general_notification' => SystemSetting::get('general_notification', ''),
        ];
    }
}
