<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\TaiKhoanChoPheDuyet;
use App\Models\GiangVien;
use App\Models\SystemSetting;
use App\Models\NhomNganh;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Hiển thị trang dashboard admin
     */
    public function dashboard()
    {
        // 1. Thống kê tổng quan người dùng
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

        // 2. Thống kê đào tạo & Module (Phase 5)
        $trainingStats = [
            'tong_mon_hoc'        => NhomNganh::count(),
            'mon_hoc_hoat_dong'   => NhomNganh::active()->count(),
            'tong_khoa_hoc'       => KhoaHoc::count(),
            'khoa_hoc_hoat_dong'  => KhoaHoc::active()->count(),
            'tong_module'         => ModuleHoc::count(),
            'module_chua_co_gv'   => ModuleHoc::whereDoesntHave('phanCongGiangViens', function($q) {
                                        $q->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan']);
                                     })->count(),
            'phan_cong_cho_xn'    => PhanCongModuleGiangVien::where('trang_thai', 'cho_xac_nhan')->count(),
        ];

        // 3. Dữ liệu bảng chi tiết (Phase 5)
        $phanCongMoiNhat = PhanCongModuleGiangVien::with([
                'moduleHoc.khoaHoc',
                'giangVien.nguoiDung'
            ])
            ->where('trang_thai', 'cho_xac_nhan')
            ->latest('ngay_phan_cong')
            ->take(5)
            ->get();

        $moduleChuaCoGv = ModuleHoc::with(['khoaHoc.nhomNganh'])
            ->whereDoesntHave('phanCongGiangViens', function($q) {
                $q->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan']);
            })
            ->where('trang_thai', true) // Chỉ lấy module đang active
            ->take(5)
            ->get();

        // Dữ liệu cho chart
        $chartData = $this->getChartData();

        return view('pages.admin.dashboard', array_merge($userStats, [
            'stats' => $trainingStats,
            'phanCongMoiNhat' => $phanCongMoiNhat,
            'moduleChuaCoGv' => $moduleChuaCoGv
        ], $chartData));
    }

    /**
     * Tính toán phần trăm tăng trưởng
     */
    private function calculateGrowth($table, $role = null)
    {
        $currentMonth = DB::table($table)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($role, function ($query) use ($role) {
                return $query->where('vai_tro', $role);
            })
            ->count();

        $lastMonth = DB::table($table)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->when($role, function ($query) use ($role) {
                return $query->where('vai_tro', $role);
            })
            ->count();

        if ($lastMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    /**
     * Lấy dữ liệu cho biểu đồ
     */
    private function getChartData()
    {
        // Dữ liệu đăng ký trong 7 ngày gần nhất
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

        // Dữ liệu người dùng theo vai trò
        $roleDistribution = [
            'hoc_vien' => NguoiDung::where('vai_tro', 'hoc_vien')->count(),
            'giang_vien' => NguoiDung::where('vai_tro', 'giang_vien')->count(),
            'admin' => NguoiDung::where('vai_tro', 'admin')->count(),
        ];

        // Dữ liệu hoạt động theo tháng
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

    /**
     * Hiển thị danh sách người dùng
     */
    public function indexNguoiDung(Request $request)
    {
        $query = NguoiDung::withTrashed();

        // Tìm kiếm
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        // Lọc theo vai trò
        if ($request->has('vai_tro') && $request->get('vai_tro') != 'all') {
            $query->where('vai_tro', $request->get('vai_tro'));
        }

        // Lọc theo trạng thái
        if ($request->has('trang_thai')) {
            $trang_thai = $request->get('trang_thai');
            if ($trang_thai == 'active') {
                $query->where('trang_thai', true);
            } elseif ($trang_thai == 'inactive') {
                $query->where('trang_thai', false);
            } elseif ($trang_thai == 'deleted') {
                $query->onlyTrashed();
            }
        }

        // Sắp xếp
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $nguoiDung = $query->paginate(20)->withQueryString();

        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.index', compact('nguoiDung'));
    }

    /**
     * Hiển thị form tạo người dùng mới
     */
    public function createNguoiDung()
    {
        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.create');
    }

    /**
     * Lưu người dùng mới
     */
    public function storeNguoiDung(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nguoi_dung',
            'mat_khau' => 'required|string|min:8|confirmed',
            'vai_tro' => 'required|in:admin,giang_vien,hoc_vien',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date|before:today',
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trang_thai' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('anh_dai_dien', 'mat_khau_confirmation');
        $data['mat_khau'] = Hash::make($request->mat_khau);

        // Xử lý upload ảnh đại diện
        if ($request->hasFile('anh_dai_dien')) {
            $file = $request->file('anh_dai_dien');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['anh_dai_dien'] = $filename;
        }

        NguoiDung::create($data);

        return redirect()->route('admin.tai-khoan.index')
            ->with('success', 'Tạo người dùng mới thành công.');
    }

    /**
     * Hiển thị chi tiết người dùng
     */
    public function showNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);

        // Thống kê cơ bản của người dùng
        $stats = [
            'tongDangKy' => 0, // Có thể thêm sau nếu có bảng đăng ký
            'ngayTao' => $nguoiDung->created_at,
            'lanCuoiDangNhap' => $nguoiDung->updated_at,
        ];

        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.show', compact('nguoiDung', 'stats'));
    }

    /**
     * Quản lý học viên
     */
    public function indexHocVien(Request $request)
    {
        $query = NguoiDung::where('vai_tro', 'hoc_vien')->withTrashed();

        // Tìm kiếm
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->has('trang_thai')) {
            $status = $request->get('trang_thai');
            if ($status === 'active') {
                $query->where('trang_thai', 1)->whereNull('deleted_at');
            } elseif ($status === 'inactive') {
                $query->where('trang_thai', 0);
            } elseif ($status === 'deleted') {
                $query->onlyTrashed();
            }
        }

        // Sắp xếp
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (!in_array($sortField, ['ho_ten', 'email', 'created_at', 'trang_thai'])) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        // Sắp xếp theo chữ cái đầu tiên cho tên và email
        if ($sortField === 'ho_ten') {
            $query->orderByRaw("SUBSTRING(ho_ten, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, ho_ten {$sortDirection}");
        } elseif ($sortField === 'email') {
            $query->orderByRaw("SUBSTRING(email, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, email {$sortDirection}");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Eager load hocVien details
        $hocVien = $query->with('hocVien')->paginate(20)->withQueryString();

        return view('pages.admin.quan-ly-tai-khoan.hoc-vien.index', compact('hocVien'));
    }

    /**
     * Quản lý giảng viên
     */
    public function indexGiangVien(Request $request)
    {
        $query = NguoiDung::where('vai_tro', 'giang_vien')->withTrashed();

        // Tìm kiếm
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->has('trang_thai')) {
            $status = $request->get('trang_thai');
            if ($status === 'active') {
                $query->where('trang_thai', 1)->whereNull('deleted_at');
            } elseif ($status === 'inactive') {
                $query->where('trang_thai', 0);
            } elseif ($status === 'deleted') {
                $query->onlyTrashed();
            }
        }

        // Sắp xếp
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (!in_array($sortField, ['ho_ten', 'email', 'created_at', 'trang_thai'])) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        // Sắp xếp theo chữ cái đầu tiên cho tên và email
        if ($sortField === 'ho_ten') {
            $query->orderByRaw("SUBSTRING(ho_ten, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, ho_ten {$sortDirection}");
        } elseif ($sortField === 'email') {
            $query->orderByRaw("SUBSTRING(email, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, email {$sortDirection}");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Eager load giangVien details
        $giangVien = $query->with('giangVien')->paginate(20)->withQueryString();

        return view('pages.admin.quan-ly-tai-khoan.giang-vien.index', compact('giangVien'));
    }

    /**
     * Hiển thị form chỉnh sửa người dùng
     */
    public function editNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);
        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.edit', compact('nguoiDung'));
    }

    /**
     * Cập nhật thông tin người dùng
     */
    public function updateNguoiDung(Request $request, $id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dung,email,' . $id . ',ma_nguoi_dung',
            'vai_tro' => 'required|in:admin,giang_vien,hoc_vien',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date|before:today',
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trang_thai' => 'required|boolean',
            'mat_khau' => 'nullable|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('anh_dai_dien', 'mat_khau', 'mat_khau_confirmation');

        // Cập nhật mật khẩu nếu có
        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        // Xử lý upload ảnh đại diện mới
        if ($request->hasFile('anh_dai_dien')) {
            // Xóa ảnh cũ nếu tồn tại
            if ($nguoiDung->anh_dai_dien && file_exists(public_path('images/' . $nguoiDung->anh_dai_dien))) {
                unlink(public_path('images/' . $nguoiDung->anh_dai_dien));
            }

            $file = $request->file('anh_dai_dien');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['anh_dai_dien'] = $filename;
        }

        // Xóa ảnh đại diện nếu người dùng yêu cầu
        if ($request->has('xoa_anh_dai_dien') && $nguoiDung->anh_dai_dien) {
            Storage::disk('public')->delete($nguoiDung->anh_dai_dien);
            $data['anh_dai_dien'] = null;
        }

        $nguoiDung->update($data);

        return redirect()->route('admin.tai-khoan.show', $nguoiDung->ma_nguoi_dung)
            ->with('success', 'Cập nhật thông tin người dùng thành công.');
    }

    /**
     * Khóa/Mở khóa tài khoản người dùng
     */
    public function toggleStatusNguoiDung($id)
    {
        $nguoiDung = NguoiDung::findOrFail($id);
        
        // Không cho khóa chính mình
        if ($nguoiDung->ma_nguoi_dung == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể khóa tài khoản của chính mình.'
            ], 403);
        }

        $nguoiDung->trang_thai = !$nguoiDung->trang_thai;
        $nguoiDung->save();

        $action = $nguoiDung->trang_thai ? 'mở khóa' : 'khóa';
        
        return response()->json([
            'success' => true,
            'message' => "Đã {$action} tài khoản {$nguoiDung->ho_ten}.",
            'trang_thai' => $nguoiDung->trang_thai
        ]);
    }

    /**
     * Xóa mềm người dùng
     */
    public function destroyNguoiDung($id)
    {
        $nguoiDung = NguoiDung::findOrFail($id);
        
        // Không cho xóa chính mình
        if ($nguoiDung->ma_nguoi_dung == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể xóa tài khoản của chính mình.'
            ], 403);
        }

        $nguoiDung->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tài khoản ' . $nguoiDung->ho_ten . '. Tài khoản có thể được khôi phục trong vòng 30 ngày.'
        ]);
    }

    /**
     * Khôi phục người dùng đã xóa
     */
    public function restoreNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);
        $nguoiDung->restore();

        return response()->json([
            'success' => true,
            'message' => 'Đã khôi phục tài khoản ' . $nguoiDung->ho_ten . '.'
        ]);
    }

    /**
     * Xóa vĩnh viễn người dùng
     */
    public function forceDeleteNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);
        
        // Không cho xóa chính mình
        if ($nguoiDung->ma_nguoi_dung == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể xóa tài khoản của chính mình.'
            ], 403);
        }

        // Xóa ảnh đại diện nếu tồn tại
        if ($nguoiDung->anh_dai_dien && file_exists(public_path('images/' . $nguoiDung->anh_dai_dien))) {
            unlink(public_path('images/' . $nguoiDung->anh_dai_dien));
        }

        $nguoiDung->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa vĩnh viễn tài khoản ' . $nguoiDung->ho_ten . '.'
        ]);
    }

    /**
     * Xuất danh sách người dùng ra Excel
     */
    public function exportNguoiDung(Request $request)
    {
        $nguoiDung = NguoiDung::all();
        
        $headers = [
            'Họ tên', 'Email', 'Vai trò', 'Số điện thoại', 
            'Ngày sinh', 'Địa chỉ', 'Trạng thái', 'Ngày đăng ký'
        ];

        $data = [];
        foreach ($nguoiDung as $user) {
            $data[] = [
                $user->ho_ten,
                $user->email,
                $this->getRoleLabel($user->vai_tro),
                $user->so_dien_thoai,
                $user->ngay_sinh ? $user->ngay_sinh->format('d/m/Y') : '',
                $user->dia_chi,
                $user->trang_thai ? 'Hoạt động' : 'Đã khóa',
                $user->created_at->format('d/m/Y H:i'),
            ];
        }

        // Tạo file CSV
        $filename = 'danh-sach-nguoi-dung-' . date('Y-m-d') . '.csv';
        
        $handle = fopen('php://output', 'w');
        fputcsv($handle, $headers);
        
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);

        return response()->streamDownload(function() use ($handle) {
            echo $handle;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Lấy nhãn vai trò
     */
    private function getRoleLabel($role)
    {
        $labels = [
            'admin' => 'Quản trị viên',
            'giang_vien' => 'Giảng viên',
            'hoc_vien' => 'Học viên',
        ];

        return $labels[$role] ?? $role;
    }

    /**
     * Thống kê hệ thống chi tiết
     */
    public function thongKe()
    {
        // Thống kê theo tháng
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

        // Thống kê theo vai trò
        $roleStats = [
            'total' => NguoiDung::count(),
            'hoc_vien' => NguoiDung::where('vai_tro', 'hoc_vien')->count(),
            'giang_vien' => NguoiDung::where('vai_tro', 'giang_vien')->count(),
            'admin' => NguoiDung::where('vai_tro', 'admin')->count(),
        ];

        return view('pages.admin.thong-ke.index', compact('monthlyStats', 'roleStats'));
    }

    /**
     * Cài đặt hệ thống
     */
    public function caiDat()
    {
        $settings = [
            'site_name' => config('app.name', 'Hệ thống Quản lý'),
            'site_email' => config('mail.from.address', 'admin@example.com'),
            'items_per_page' => config('app.items_per_page', 20),
            'enable_registration' => config('app.enable_registration', true),
            'maintenance_mode' => config('app.maintenance_mode', false),
        ];

        return view('pages.admin.settings.cai-dat', compact('settings'));
    }

    /**
     * Lưu cài đặt hệ thống
     */
    public function luuCaiDat(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'required|email',
            'items_per_page' => 'required|integer|min:5|max:100',
            'enable_registration' => 'required|boolean',
            'maintenance_mode' => 'required|boolean',
        ]);

        // Lưu cài đặt vào file .env hoặc database
        $envPath = base_path('.env');
        
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            foreach ($validated as $key => $value) {
                $envKey = 'APP_' . strtoupper($key);
                $envValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                
                if (strpos($envContent, "{$envKey}=") !== false) {
                    $envContent = preg_replace(
                        "/^{$envKey}=.*/m",
                        "{$envKey}={$envValue}",
                        $envContent
                    );
                } else {
                    $envContent .= "\n{$envKey}={$envValue}";
                }
            }
            
            file_put_contents($envPath, $envContent);
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Cập nhật cài đặt hệ thống thành công.');
    }

    /**
     * Sao lưu cơ sở dữ liệu
     */
    public function backupDatabase()
    {
        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        
        if (!file_exists(dirname($path))) {
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

    /**
     * Xem nhật ký hệ thống
     */
    public function nhatKy(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return view('pages.admin.nhat-ky.index', ['logs' => [], 'error' => 'File log không tồn tại.']);
        }

        $logs = [];
        $file = fopen($logFile, 'r');
        
        while (!feof($file)) {
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
            $logs = array_filter($logs, function($log) use ($request) {
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

    /**
     * Xác định loại log
     */
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

    /**
     * Xóa nhật ký
     */
    public function xoaNhatKy()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        return redirect()->route('admin.nhat-ky')
            ->with('success', 'Đã xóa tất cả nhật ký hệ thống.');
    }

    /**
     * API lấy thông tin người dùng
     */
    public function apiGetNguoiDung(Request $request)
    {
        $query = NguoiDung::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        if ($request->has('vai_tro')) {
            $query->where('vai_tro', $request->get('vai_tro'));
        }

        $nguoiDung = $query->paginate(10);

        return response()->json([
            'data' => $nguoiDung,
            'success' => true
        ]);
    }

    /**
     * Tìm kiếm người dùng nhanh (cho autocomplete)
     */
    public function timKiemNguoiDung(Request $request)
    {
        $search = $request->get('q');
        
        $nguoiDung = NguoiDung::where('ho_ten', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(10)
            ->get(['ma_nguoi_dung', 'ho_ten', 'email', 'vai_tro']);

        return response()->json($nguoiDung);
    }

    /**
     * Dashboard cho giảng viên
     */
    public function giangVienDashboard()
    {
        $giangVienId = auth()->user()->giangVien->id ?? null;

        if (!$giangVienId) {
            return redirect()->route('home')->with('error', 'Tài khoản của bạn chưa được thiết lập profile giảng viên.');
        }

        $stats = [
            'dang_day' => PhanCongModuleGiangVien::where('giao_vien_id', $giangVienId)
                ->where('trang_thai', 'da_nhan')
                ->count(),
            'cho_xac_nhan' => PhanCongModuleGiangVien::where('giao_vien_id', $giangVienId)
                ->where('trang_thai', 'cho_xac_nhan')
                ->count(),
            'tong_hoc_vien' => DB::table('hoc_vien_khoa_hoc')
                ->whereIn('khoa_hoc_id', function($query) use ($giangVienId) {
                    $query->select('khoa_hoc_id')
                        ->from('phan_cong_module_giang_vien')
                        ->where('giao_vien_id', $giangVienId);
                })
                ->count(),
            'so_gio_day' => auth()->user()->giangVien->so_gio_day ?? 0,
        ];

        // Lấy danh sách phân công mới nhất cần xác nhận
        $phanCongMoi = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.nhomNganh'])
            ->where('giao_vien_id', $giangVienId)
            ->where('trang_thai', 'cho_xac_nhan')
            ->latest()
            ->take(5)
            ->get();

        // Lấy danh sách lớp đang dạy (từ các module đã nhận)
        $lopDangDay = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.nhomNganh'])
            ->where('giao_vien_id', $giangVienId)
            ->where('trang_thai', 'da_nhan')
            ->latest()
            ->take(5)
            ->get();

        return view('pages.giang-vien.dashboard', compact('stats', 'phanCongMoi', 'lopDangDay'));
    }

    /**
     * Dashboard cho học viên
     */
    public function hocVienDashboard()
    {
        $user = auth()->user();
        
        $stats = [
            'tongKhoaHoc' => 0, // Placeholder
            'diemTrungBinh' => 0,
            'tienDo' => 0,
        ];

        return view('pages.hoc-vien.dashboard', compact('stats'));
    }

    /**
     * Đếm số học viên của giảng viên
     */
    private function countStudentsOfTeacher($teacherId)
    {
        return 0; // Placeholder
    }

    /**
     * Tính doanh thu của giảng viên
     */
    private function calculateRevenue($teacherId)
    {
        return 0; // Placeholder
    }

    /**
     * Hiển thị danh sách tài khoản chờ phê duyệt
     */
    public function indexPheDuyetTaiKhoan(Request $request)
    {
        $query = TaiKhoanChoPheDuyet::where('trang_thai', 'cho_phe_duyet');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $taiKhoanChoPheDuyet = $query->paginate(20)->withQueryString();

        return view('pages.admin.quan-ly-tai-khoan.phe-duyet-tai-khoan.index', compact('taiKhoanChoPheDuyet'));
    }

    /**
     * Phê duyệt tài khoản
     */
    public function approveTaiKhoan($id)
    {
        $taiKhoan = TaiKhoanChoPheDuyet::findOrFail($id);

        $nguoiDung = NguoiDung::create([
            'ho_ten' => $taiKhoan->ho_ten,
            'email' => $taiKhoan->email,
            'mat_khau' => $taiKhoan->mat_khau,
            'vai_tro' => $taiKhoan->vai_tro,
            'so_dien_thoai' => $taiKhoan->so_dien_thoai,
            'ngay_sinh' => $taiKhoan->ngay_sinh,
            'dia_chi' => $taiKhoan->dia_chi,
            'trang_thai' => true,
        ]);

        $taiKhoan->update(['trang_thai' => 'da_phe_duyet']);

        $redirectUrl = $taiKhoan->vai_tro === 'giang_vien' 
            ? route('admin.giang-vien.index') 
            : route('admin.hoc-vien.index');

        return response()->json([
            'success' => true,
            'message' => 'Đã phê duyệt tài khoản ' . $taiKhoan->ho_ten . '.',
            'redirect' => $redirectUrl,
            'vai_tro' => $taiKhoan->vai_tro
        ]);
    }

    /**
     * Từ chối tài khoản
     */
    public function rejectTaiKhoan($id)
    {
        $taiKhoan = TaiKhoanChoPheDuyet::findOrFail($id);
        $taiKhoan->update(['trang_thai' => 'tu_choi']);

        return response()->json([
            'success' => true,
            'message' => 'Đã từ chối tài khoản ' . $taiKhoan->ho_ten . '.'
        ]);
    }

    /**
     * Hủy phê duyệt tài khoản
     */
    public function undoApproveTaiKhoan($id)
    {
        $taiKhoan = TaiKhoanChoPheDuyet::findOrFail($id);
        $nguoiDung = NguoiDung::where('email', $taiKhoan->email)->first();

        if (!$nguoiDung) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại để hủy phê duyệt.'
            ], 404);
        }

        $nguoiDung->delete();
        $taiKhoan->update(['trang_thai' => 'cho_phe_duyet']);

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy phê duyệt tài khoản ' . $taiKhoan->ho_ten . '.'
        ]);
    }

    /**
     * Hiển thị trang cài đặt hệ thống
     */
    public function showSettings()
    {
        $settings = [
            'hotline' => SystemSetting::get('hotline', ''),
            'email' => SystemSetting::get('email', ''),
            'facebook' => SystemSetting::get('facebook', ''),
            'zalo' => SystemSetting::get('zalo', ''),
        ];

        $instructors = GiangVien::with('nguoiDung')->get();

        return view('pages.admin.settings.cai-dat', [
            'settings' => $settings,
            'instructors' => $instructors,
        ]);
    }

    /**
     * Lưu cài đặt hệ thống
     */
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hotline' => 'nullable|string',
            'email' => 'nullable|email',
            'facebook' => 'nullable|url',
            'zalo' => 'nullable|url',
        ]);

        if ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $filename = time() . '_logo.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $validated['site_logo'] = 'images/' . $filename;
        }

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                SystemSetting::set($key, $value);
            }
        }

        $currentRoute = $request->route()->getName();

        if (str_contains($currentRoute, 'contact')) {
            return redirect()->route('admin.settings.contact')
                ->with('success', 'Thông tin liên hệ đã được cập nhật thành công!');
        } elseif (str_contains($currentRoute, 'social')) {
            return redirect()->route('admin.settings.social')
                ->with('success', 'Mạng xã hội đã được cập nhật thành công!');
        } else {
            return redirect()->route('admin.settings')
                ->with('success', 'Cài đặt hệ thống đã được cập nhật thành công!');
        }
    }

    /**
     * Lưu các giảng viên hiển thị trên trang chủ
     */
    public function saveInstructorSettings(Request $request)
    {
        $instructorIds = $request->get('instructors', []);
        GiangVien::query()->update(['hien_thi_trang_chu' => false]);

        if (!empty($instructorIds)) {
            GiangVien::whereIn('id', $instructorIds)
                ->update(['hien_thi_trang_chu' => true]);
        }

        return redirect()->route('admin.settings.instructors')
            ->with('success', 'Chọn giảng viên hiển thị trên trang chủ đã được cập nhật thành công!');
    }

    /**
     * Hiển thị trang cài đặt thông tin liên hệ
     */
    public function showContactSettings()
    {
        $settings = [
            'site_name' => SystemSetting::get('site_name', ''),
            'site_logo' => SystemSetting::get('site_logo', ''),
            'hotline' => SystemSetting::get('hotline', ''),
            'email' => SystemSetting::get('email', ''),
            'banner_images' => collect(json_decode(SystemSetting::get('banner_images', '[]'), true) ?: [])
                                ->map(fn($p) => asset($p))
                                ->toArray(),
        ];

        return view('pages.admin.settings.contact', compact('settings'));
    }

    /**
     * Hiển thị trang cài đặt mạng xã hội
     */
    public function showSocialSettings()
    {
        $settings = [
            'facebook' => SystemSetting::get('facebook', ''),
            'zalo' => SystemSetting::get('zalo', ''),
        ];

        return view('pages.admin.settings.social', compact('settings'));
    }

    /**
     * Hiển thị trang cài đặt giảng viên
     */
    public function showInstructorSettings()
    {
        $instructors = GiangVien::with('nguoiDung')->get();
        return view('pages.admin.settings.instructors', compact('instructors'));
    }
}
