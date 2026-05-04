<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaiGiang;
use App\Models\BaiKiemTra;
use App\Models\GiangVien;
use App\Models\GiangVienDonXinNghi;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Models\PhanCongModuleGiangVien;
use App\Models\TaiKhoanChoPheDuyet;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\YeuCauHocVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        $userStats = [
            'tongNguoiDung' => NguoiDung::count(),
            'tongHocVien' => NguoiDung::where('vai_tro', 'hoc_vien')->count(),
            'tongGiangVien' => NguoiDung::where('vai_tro', 'giang_vien')->count(),
            'tongAdmin' => NguoiDung::where('vai_tro', 'admin')->count(),
            'nguoiDungMoi' => NguoiDung::withTrashed()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        $trainingStats = [
            'tong_nhom_nganh' => NhomNganh::count(),
            'nhom_nganh_hoat_dong' => NhomNganh::active()->count(),
            'tong_khoa_hoc' => KhoaHoc::count(),
            'khoa_hoc_hoat_dong' => KhoaHoc::active()->count(),
            'khoa_hoc_cho_gv' => KhoaHoc::where('trang_thai_van_hanh', 'cho_giang_vien')->count(),
            'khoa_hoc_dang_hoc' => KhoaHoc::where('trang_thai_van_hanh', 'dang_day')->count(),
            'tong_module' => ModuleHoc::count(),
            'module_chua_co_gv' => ModuleHoc::whereDoesntHave('phanCongGiangViens', function ($q) {
                $q->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan']);
            })
                ->whereHas('khoaHoc', fn ($q) => $q->where('loai', 'hoat_dong'))
                ->count(),
            'phan_cong_cho_xn' => PhanCongModuleGiangVien::where('trang_thai', 'cho_xac_nhan')->count(),
            'tai_khoan_cho_duyet' => TaiKhoanChoPheDuyet::where('trang_thai', 'cho_phe_duyet')->count(),
            'yeu_cau_hoc_vien_cho_duyet' => YeuCauHocVien::where('trang_thai', 'cho_duyet')->count(),
            'bai_giang_cho_duyet' => BaiGiang::where('trang_thai_duyet', BaiGiang::STATUS_DUYET_CHO)->count(),
            'tai_nguyen_cho_duyet' => TaiNguyenBuoiHoc::where('trang_thai_duyet', TaiNguyenBuoiHoc::STATUS_DUYET_CHO)->count(),
            'bai_kiem_tra_cho_duyet' => BaiKiemTra::where('trang_thai_duyet', 'cho_duyet')->count(),
            'lich_hoc_hom_nay' => LichHoc::whereDate('ngay_hoc', $today)
                ->where('trang_thai', '!=', 'huy')
                ->count(),
            'lich_hoc_sap_toi' => LichHoc::whereDate('ngay_hoc', '>', $today)
                ->where('trang_thai', '!=', 'huy')
                ->count(),
            'giang_vien_co_lich_day_tuong_lai' => GiangVien::whereHas('lichHocs', function ($query) {
                $query->whereDate('ngay_hoc', '>=', now()->toDateString())
                    ->where('trang_thai', '!=', 'huy');
            })->count(),
            'don_xin_nghi_cho_duyet' => GiangVienDonXinNghi::where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)->count(),
            'giang_vien_can_xu_ly_don_nghi' => GiangVienDonXinNghi::where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)
                ->distinct('giang_vien_id')
                ->count('giang_vien_id'),
        ];

        $phanCongMoiNhat = PhanCongModuleGiangVien::with([
            'moduleHoc.khoaHoc',
            'giangVien.nguoiDung',
        ])
            ->where('trang_thai', 'cho_xac_nhan')
            ->latest('ngay_phan_cong')
            ->take(5)
            ->get();

        $moduleChuaCoGv = ModuleHoc::with(['khoaHoc.nhomNganh'])
            ->whereDoesntHave('phanCongGiangViens', function ($q) {
                $q->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan']);
            })
            ->whereHas('khoaHoc', fn ($q) => $q->where('loai', 'hoat_dong'))
            ->where('trang_thai', true)
            ->take(5)
            ->get();

        $chartData = $this->getChartData();

        $taskStats = [
            [
                'key' => 'tai_khoan',
                'title' => 'Tài khoản chờ duyệt',
                'description' => 'Học viên/giảng viên vừa gửi đăng ký.',
                'count' => $trainingStats['tai_khoan_cho_duyet'],
                'route' => route('admin.phe-duyet-tai-khoan.index'),
                'icon' => 'fas fa-user-check',
                'tone' => 'primary',
            ],
            [
                'key' => 'yeu_cau_hoc_vien',
                'title' => 'Yêu cầu học viên',
                'description' => 'Yêu cầu vào lớp hoặc cập nhật học viên.',
                'count' => $trainingStats['yeu_cau_hoc_vien_cho_duyet'],
                'route' => route('admin.yeu-cau-hoc-vien.index'),
                'icon' => 'fas fa-user-plus',
                'tone' => 'info',
            ],
            [
                'key' => 'don_nghi',
                'title' => 'Đơn xin nghỉ',
                'description' => 'Đơn nghỉ giảng viên cần phản hồi.',
                'count' => $trainingStats['don_xin_nghi_cho_duyet'],
                'route' => route('admin.giang-vien-don-xin-nghi.index'),
                'icon' => 'fas fa-calendar-xmark',
                'tone' => 'warning',
            ],
            [
                'key' => 'bai_giang',
                'title' => 'Bài giảng chờ duyệt',
                'description' => 'Nội dung giảng viên gửi lên hệ thống.',
                'count' => $trainingStats['bai_giang_cho_duyet'],
                'route' => route('admin.bai-giang.index'),
                'icon' => 'fas fa-book-open',
                'tone' => 'success',
            ],
            [
                'key' => 'thu_vien',
                'title' => 'Tài nguyên chờ duyệt',
                'description' => 'Tài liệu trong thư viện cần kiểm tra.',
                'count' => $trainingStats['tai_nguyen_cho_duyet'],
                'route' => route('admin.thu-vien.index'),
                'icon' => 'fas fa-folder-open',
                'tone' => 'secondary',
            ],
            [
                'key' => 'kiem_tra',
                'title' => 'Bài kiểm tra chờ duyệt',
                'description' => 'Đề kiểm tra cần duyệt hoặc phát hành.',
                'count' => $trainingStats['bai_kiem_tra_cho_duyet'],
                'route' => route('admin.kiem-tra-online.phe-duyet.index'),
                'icon' => 'fas fa-clipboard-check',
                'tone' => 'danger',
            ],
        ];

        $urgentTotal = collect($taskStats)->sum('count')
            + $trainingStats['module_chua_co_gv']
            + $trainingStats['phan_cong_cho_xn'];

        $pendingAccounts = TaiKhoanChoPheDuyet::where('trang_thai', 'cho_phe_duyet')
            ->latest()
            ->take(5)
            ->get();

        $pendingLeaveRequests = GiangVienDonXinNghi::with(['giangVien.nguoiDung', 'khoaHoc', 'moduleHoc'])
            ->where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)
            ->latest()
            ->take(5)
            ->get();

        $pendingStudentRequests = YeuCauHocVien::with(['khoaHoc', 'giangVien.nguoiDung', 'hocVienNguoiDung'])
            ->where('trang_thai', 'cho_duyet')
            ->latest()
            ->take(5)
            ->get();

        $todaysSchedules = LichHoc::with(['khoaHoc', 'moduleHoc', 'giangVien.nguoiDung'])
            ->whereDate('ngay_hoc', $today)
            ->where('trang_thai', '!=', 'huy')
            ->orderBy('gio_bat_dau')
            ->take(7)
            ->get();

        return view('pages.admin.dashboard', array_merge($userStats, [
            'stats' => $trainingStats,
            'phanCongMoiNhat' => $phanCongMoiNhat,
            'moduleChuaCoGv' => $moduleChuaCoGv,
            'taskStats' => $taskStats,
            'urgentTotal' => $urgentTotal,
            'pendingAccounts' => $pendingAccounts,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'pendingStudentRequests' => $pendingStudentRequests,
            'todaysSchedules' => $todaysSchedules,
        ], $chartData));
    }

    private function getChartData()
    {
        $registrationData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $registrationData[$date] = [
                'hoc_vien' => NguoiDung::where('vai_tro', 'hoc_vien')
                    ->whereDate('created_at', $date)
                    ->count(),
                'giang_vien' => NguoiDung::where('vai_tro', 'giang_vien')
                    ->whereDate('created_at', $date)
                    ->count(),
                'admin' => NguoiDung::where('vai_tro', 'admin')
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        $roleDistribution = [
            'hoc_vien' => NguoiDung::where('vai_tro', 'hoc_vien')->count(),
            'giang_vien' => NguoiDung::where('vai_tro', 'giang_vien')->count(),
            'admin' => NguoiDung::where('vai_tro', 'admin')->count(),
        ];

        $monthlyActivity = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($i = 0; $i < 6; $i++) {
            $month = now()->subMonths(5 - $i);
            $monthlyActivity[$months[$month->month - 1]] = [
                'nguoi_dung' => NguoiDung::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'khoa_hoc' => KhoaHoc::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'module' => ModuleHoc::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        }

        return [
            'registrationData' => $registrationData,
            'roleDistribution' => $roleDistribution,
            'monthlyActivity' => $monthlyActivity,
        ];
    }

    public function thongKe()
    {
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $monthlyStats[$month->format('Y-m')] = [
                'nguoi_dung' => NguoiDung::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'khoa_hoc' => KhoaHoc::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'module' => ModuleHoc::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            ];
        }

        $roleStats = [
            'total' => NguoiDung::count(),
            'hoc_vien' => NguoiDung::where('vai_tro', 'hoc_vien')->count(),
            'giang_vien' => NguoiDung::where('vai_tro', 'giang_vien')->count(),
            'admin' => NguoiDung::where('vai_tro', 'admin')->count(),
        ];

        return view('pages.admin.thong-ke.index', compact('monthlyStats', 'roleStats'));
    }

    public function backupDatabase()
    {
        $filename = 'backup-'.date('Y-m-d-H-i-s').'.sql';
        $path = storage_path('app/backups/'.$filename);

        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.host'),
            config('database.connections.mysql.database'),
            $path
        );

        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return response()->download($path)->deleteFileAfterSend(true);
        } else {
            return redirect()->back()
                ->with('error', 'Không thể sao lưu cơ sở dữ liệu. Vui lòng kiểm tra cấu hình.');
        }
    }

    public function nhatKy(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');

        if (! file_exists($logFile)) {
            return view('pages.admin.nhat-ky.index', ['logs' => [], 'error' => 'File log không tồn tại.']);
        }

        $logs = [];
        $file = fopen($logFile, 'r');

        while (! feof($file)) {
            $line = fgets($file);
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(\w+)\.(\w+): (.*)$/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => $matches[3],
                    'message' => $matches[4],
                    'type' => $this->getLogType($matches[3]),
                ];
            }
        }

        fclose($file);

        if ($request->has('level') && $request->level != 'all') {
            $logs = array_filter($logs, function ($log) use ($request) {
                return strtolower($log['level']) == strtolower($request->level);
            });
        }

        $logs = array_reverse($logs);

        $perPage = 50;
        $currentPage = $request->get('page', 1);
        $paginatedLogs = array_slice($logs, ($currentPage - 1) * $perPage, $perPage);
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLogs,
            count($logs),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.admin.nhat-ky.index', compact('logs'));
    }

    private function getLogType($level)
    {
        $types = [
            'ERROR' => 'danger',
            'CRITICAL' => 'danger',
            'ALERT' => 'danger',
            'EMERGENCY' => 'danger',
            'WARNING' => 'warning',
            'NOTICE' => 'info',
            'INFO' => 'info',
            'DEBUG' => 'secondary',
        ];

        return $types[strtoupper($level)] ?? 'secondary';
    }

    public function xoaNhatKy()
    {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        return redirect()->route('admin.nhat-ky')
            ->with('success', 'Đã xóa tất cả nhật ký hệ thống.');
    }

    public function giangVienDashboard()
    {
        $giangVienId = auth()->user()->giangVien->id ?? null;

        if (! $giangVienId) {
            return redirect()->route('home')->with('error', 'Tài khoản của bạn chưa được thiết lập profile giảng viên.');
        }

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
            'so_gio_day' => auth()->user()->giangVien->so_gio_day ?? 0,
        ];

        $phanCongMoi = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.nhomNganh'])
            ->where('giang_vien_id', $giangVienId)
            ->where('trang_thai', 'cho_xac_nhan')
            ->latest()
            ->take(5)
            ->get();

        $lopDangDay = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.nhomNganh'])
            ->where('giang_vien_id', $giangVienId)
            ->where('trang_thai', 'da_nhan')
            ->latest()
            ->take(5)
            ->get();

        return view('pages.giang-vien.dashboard', compact('stats', 'phanCongMoi', 'lopDangDay'));
    }

    public function hocVienDashboard()
    {
        $user = auth()->user();

        $stats = [
            'tongKhoaHoc' => 0,
            'diemTrungBinh' => 0,
            'tienDo' => 0,
        ];

        return view('pages.hoc-vien.dashboard', compact('stats'));
    }
}
