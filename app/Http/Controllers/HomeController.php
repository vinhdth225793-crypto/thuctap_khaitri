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

        // Kiểm tra xem học viên đã tham gia khóa học nào chưa
        if ($user && $user->vai_tro === 'hoc_vien') {
            $enrolledCourseIds = HocVienKhoaHoc::where('hoc_vien_id', $user->ma_nguoi_dung)
                ->pluck('khoa_hoc_id')
                ->toArray();
            
            $courses->getCollection()->transform(function ($course) use ($enrolledCourseIds) {
                $course->is_enrolled = in_array($course->id, $enrolledCourseIds);
                return $course;
            });
        }

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

        if ($featuredCourse && $user && $user->vai_tro === 'hoc_vien') {
            $featuredCourse->is_enrolled = in_array($featuredCourse->id, $enrolledCourseIds ?? []);
        }

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

    /**
     * AJAX: gợi ý tìm kiếm tức thì cho ô tìm kiếm trên navbar.
     * Trả về JSON gồm khóa học + giảng viên khớp keyword.
     */
    public function searchSuggest(Request $request)
    {
        $keyword = trim((string) $request->get('q', ''));

        if (mb_strlen($keyword) < 2) {
            return response()->json([
                'q' => $keyword,
                'courses' => [],
                'instructors' => [],
                'total' => 0,
            ]);
        }

        $courses = KhoaHoc::query()
            ->active()
            ->hoatDong()
            ->whereIn('trang_thai_van_hanh', ['cho_giang_vien', 'san_sang', 'dang_day'])
            ->where(function ($q) use ($keyword) {
                $q->where('ten_khoa_hoc', 'like', "%{$keyword}%")
                    ->orWhere('ma_khoa_hoc', 'like', "%{$keyword}%")
                    ->orWhere('mo_ta_ngan', 'like', "%{$keyword}%");
            })
            ->with('nhomNganh:id,ten_nhom_nganh')
            ->limit(5)
            ->get(['id', 'ma_khoa_hoc', 'ten_khoa_hoc', 'cap_do', 'hinh_anh', 'nhom_nganh_id']);

        $instructors = GiangVien::hienThiTrangChu()
            ->whereHas('nguoiDung', function ($q) use ($keyword) {
                $q->where('ho_ten', 'like', "%{$keyword}%");
            })
            ->orWhere('chuyen_nganh', 'like', "%{$keyword}%")
            ->with('nguoiDung:ma_nguoi_dung,ho_ten,anh_dai_dien')
            ->limit(3)
            ->get(['id', 'nguoi_dung_id', 'chuyen_nganh', 'hoc_vi', 'avatar_url']);

        $coursePayload = $courses->map(fn ($course) => [
            'id' => $course->id,
            'title' => $course->ten_khoa_hoc,
            'code' => $course->ma_khoa_hoc,
            'level' => $course->cap_do,
            'level_label' => match ($course->cap_do) {
                'co_ban' => 'Cơ bản',
                'trung_binh' => 'Trung bình',
                'nang_cao' => 'Nâng cao',
                default => 'Tổng hợp',
            },
            'category' => $course->nhomNganh->ten_nhom_nganh ?? 'Đa lĩnh vực',
            'image' => $course->hinh_anh ? asset($course->hinh_anh) : asset('images/default-course.svg'),
            'url' => route('home', ['q' => $course->ma_khoa_hoc]) . '#courses',
        ]);

        $instructorPayload = $instructors->map(function ($gv) {
            $avatar = $gv->avatar_url ?: optional($gv->nguoiDung)->anh_dai_dien;
            $avatarUrl = null;
            if ($avatar) {
                $avatarUrl = \Illuminate\Support\Str::startsWith($avatar, ['http://', 'https://'])
                    ? $avatar
                    : asset(\Illuminate\Support\Str::startsWith($avatar, ['avatars/']) ? 'storage/' . $avatar : $avatar);
            }

            return [
                'id' => $gv->id,
                'name' => $gv->nguoiDung->ho_ten ?? 'Giảng viên',
                'specialty' => $gv->chuyen_nganh ?: 'Chuyên gia đào tạo',
                'degree' => $gv->hoc_vi ?: 'Giảng viên',
                'avatar' => $avatarUrl,
                'initial' => mb_substr($gv->nguoiDung->ho_ten ?? 'GV', 0, 1),
            ];
        });

        return response()->json([
            'q' => $keyword,
            'courses' => $coursePayload,
            'instructors' => $instructorPayload,
            'total' => $coursePayload->count() + $instructorPayload->count(),
        ]);
    }

    /**
     * AJAX: 5 thông báo gần nhất của user đang đăng nhập (cho notification bell).
     */
    public function notificationsRecent(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['items' => [], 'unread' => 0]);
        }

        $items = \App\Models\ThongBao::query()
            ->where('nguoi_nhan_id', $user->ma_nguoi_dung)
            ->latest()
            ->limit(6)
            ->get(['id', 'tieu_de', 'noi_dung', 'loai', 'url', 'da_doc', 'created_at']);

        $unread = \App\Models\ThongBao::query()
            ->where('nguoi_nhan_id', $user->ma_nguoi_dung)
            ->where('da_doc', false)
            ->count();

        return response()->json([
            'unread' => $unread,
            'items' => $items->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->tieu_de,
                'preview' => \Illuminate\Support\Str::limit(strip_tags($item->noi_dung ?? ''), 80),
                'url' => $item->url ?: route('thong-bao.doc-mot', $item->id),
                'is_read' => (bool) $item->da_doc,
                'time_ago' => $item->created_at?->diffForHumans() ?? '',
                'type' => $item->loai,
            ])->values(),
        ]);
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
