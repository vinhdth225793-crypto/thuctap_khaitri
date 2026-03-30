<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\TaiKhoanChoPheDuyet;
use App\Models\GiangVien;
use App\Models\GiangVienDonXinNghi;
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
     * Hi?n th? trang dashboard admin
     */
    public function dashboard()
    {
        // 1. Th?ng kê t?ng quan ngu?i dùng
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

        // 2. Th?ng kê dào t?o & Module (Phase 5)
        $trainingStats = [
            'tong_nhom_nganh'        => NhomNganh::count(),
            'nhom_nganh_hoat_dong'   => NhomNganh::active()->count(),
            'tong_khoa_hoc'       => KhoaHoc::count(),
            'khoa_hoc_hoat_dong'  => KhoaHoc::active()->count(),
            'tong_module'         => ModuleHoc::count(),
            'module_chua_co_gv'   => ModuleHoc::whereDoesntHave('phanCongGiangViens', function($q) {
                                        $q->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan']);
                                     })->count(),
            'phan_cong_cho_xn'    => PhanCongModuleGiangVien::where('trang_thai', 'cho_xac_nhan')->count(),
            'giang_vien_co_lich_day_tuong_lai' => GiangVien::whereHas('lichHocs', function ($query) {
                $query->whereDate('ngay_hoc', '>=', now()->toDateString())
                    ->where('trang_thai', '!=', 'huy');
            })->count(),
            'don_xin_nghi_cho_duyet' => GiangVienDonXinNghi::where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)->count(),
            'giang_vien_can_xu_ly_don_nghi' => GiangVienDonXinNghi::where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)
                ->distinct('giang_vien_id')
                ->count('giang_vien_id'),
        ];

        // 3. D? li?u b?ng chi ti?t (Phase 5)
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
            ->where('trang_thai', true) // Ch? l?y module dang active
            ->take(5)
            ->get();

        // D? li?u cho chart
        $chartData = $this->getChartData();

        return view('pages.admin.dashboard', array_merge($userStats, [
            'stats' => $trainingStats,
            'phanCongMoiNhat' => $phanCongMoiNhat,
            'moduleChuaCoGv' => $moduleChuaCoGv
        ], $chartData));
    }

    /**
     * Tính toán ph?n tram tang tru?ng
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
     * L?y d? li?u cho bi?u d?
     */
    private function getChartData()
    {
        // D? li?u dang ký trong 7 ngày g?n nh?t
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

        // D? li?u ngu?i dùng theo vai trò
        $roleDistribution = [
            'hoc_vien' => NguoiDung::where('vai_tro', 'hoc_vien')->count(),
            'giang_vien' => NguoiDung::where('vai_tro', 'giang_vien')->count(),
            'admin' => NguoiDung::where('vai_tro', 'admin')->count(),
        ];

        // D? li?u ho?t d?ng theo tháng
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
     * Hi?n th? danh sách ngu?i dùng
     */
    public function indexNguoiDung(Request $request)
    {
        $query = NguoiDung::withTrashed();

        // Tìm ki?m
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        // L?c theo vai trò
        if ($request->has('vai_tro') && $request->get('vai_tro') != 'all') {
            $query->where('vai_tro', $request->get('vai_tro'));
        }

        // L?c theo tr?ng thái
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

        // S?p x?p
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $nguoiDung = $query->paginate(20)->withQueryString();

        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.index', compact('nguoiDung'));
    }

    /**
     * Hi?n th? form t?o ngu?i dùng m?i
     */
    public function createNguoiDung()
    {
        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.create');
    }

    /**
     * Luu ngu?i dùng m?i
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

        // X? lý upload ?nh d?i di?n
        if ($request->hasFile('anh_dai_dien')) {
            $file = $request->file('anh_dai_dien');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['anh_dai_dien'] = $filename;
        }

        NguoiDung::create($data);

        return redirect()->route('admin.tai-khoan.index')
            ->with('success', 'T?o ngu?i dùng m?i thành công.');
    }

    /**
     * Hi?n th? chi ti?t ngu?i dùng
     */
    public function showNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);

        // Th?ng kê co b?n c?a ngu?i dùng
        $stats = [
            'tongDangKy' => 0, // Có th? thêm sau n?u có b?ng dang ký
            'ngayTao' => $nguoiDung->created_at,
            'lanCuoiDangNhap' => $nguoiDung->updated_at,
        ];

        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.show', compact('nguoiDung', 'stats'));
    }

    /**
     * Qu?n lý h?c viên
     */
    public function indexHocVien(Request $request)
    {
        $query = NguoiDung::where('vai_tro', 'hoc_vien')->withTrashed();

        // Tìm ki?m
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        // L?c theo tr?ng thái
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

        // S?p x?p
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (!in_array($sortField, ['ho_ten', 'email', 'created_at', 'trang_thai'])) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        // S?p x?p theo ch? cái d?u tiên cho tên và email
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
     * Qu?n lý gi?ng viên
     */
    public function indexGiangVien(Request $request)
    {
        $query = NguoiDung::where('vai_tro', 'giang_vien')->withTrashed();

        // Tìm ki?m
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        // L?c theo tr?ng thái
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

        // S?p x?p
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (!in_array($sortField, ['ho_ten', 'email', 'created_at', 'trang_thai'])) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        // S?p x?p theo ch? cái d?u tiên cho tên và email
        if ($sortField === 'ho_ten') {
            $query->orderByRaw("SUBSTRING(ho_ten, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, ho_ten {$sortDirection}");
        } elseif ($sortField === 'email') {
            $query->orderByRaw("SUBSTRING(email, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, email {$sortDirection}");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Eager load giangVien details
        $giangVien = $query->with([
            'giangVien' => function ($teacherQuery) {
                $teacherQuery->withCount([
                    'phanCongModules as phan_cong_da_nhan_count' => function ($assignmentQuery) {
                        $assignmentQuery->where('trang_thai', 'da_nhan');
                    },
                    'lichHocs as buoi_day_tuong_lai_count' => function ($scheduleQuery) {
                        $scheduleQuery
                            ->whereDate('ngay_hoc', '>=', now()->toDateString())
                            ->where('trang_thai', '!=', 'huy');
                    },
                    'donXinNghis as tong_don_xin_nghi_count',
                    'donXinNghis as don_xin_nghi_cho_duyet_count' => function ($leaveQuery) {
                        $leaveQuery->where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET);
                    },
                ]);
            },
        ])->paginate(20)->withQueryString();

        $teacherSummary = [
            'total' => GiangVien::count(),
            'with_upcoming_schedule' => GiangVien::whereHas('lichHocs', function ($query) {
                $query->whereDate('ngay_hoc', '>=', now()->toDateString())
                    ->where('trang_thai', '!=', 'huy');
            })->count(),
            'pending_leave_requests' => GiangVienDonXinNghi::where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)->count(),
            'teachers_with_pending_leave' => GiangVienDonXinNghi::where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)
                ->distinct('giang_vien_id')
                ->count('giang_vien_id'),
        ];

        return view('pages.admin.quan-ly-tai-khoan.giang-vien.index', compact('giangVien', 'teacherSummary'));
    }

    /**
     * Hi?n th? form ch?nh s?a ngu?i dùng
     */
    public function editNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);
        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.edit', compact('nguoiDung'));
    }

    /**
     * C?p nh?t thông tin ngu?i dùng
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

        // C?p nh?t m?t kh?u n?u có
        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        // X? lý upload ?nh d?i di?n m?i
        if ($request->hasFile('anh_dai_dien')) {
            // Xóa ?nh cu n?u t?n t?i
            if ($nguoiDung->anh_dai_dien && file_exists(public_path('images/' . $nguoiDung->anh_dai_dien))) {
                unlink(public_path('images/' . $nguoiDung->anh_dai_dien));
            }

            $file = $request->file('anh_dai_dien');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['anh_dai_dien'] = $filename;
        }

        // Xóa ?nh d?i di?n n?u ngu?i dùng yêu c?u
        if ($request->has('xoa_anh_dai_dien') && $nguoiDung->anh_dai_dien) {
            Storage::disk('public')->delete($nguoiDung->anh_dai_dien);
            $data['anh_dai_dien'] = null;
        }

        $nguoiDung->update($data);

        return redirect()->route('admin.tai-khoan.show', $nguoiDung->ma_nguoi_dung)
            ->with('success', 'C?p nh?t thông tin ngu?i dùng thành công.');
    }

    /**
     * Khóa/M? khóa tài kho?n ngu?i dùng
     */
    public function toggleStatusNguoiDung($id)
    {
        $nguoiDung = NguoiDung::findOrFail($id);
        
        // Không cho khóa chính mình
        if ($nguoiDung->ma_nguoi_dung == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'B?n không th? khóa tài kho?n c?a chính mình.'
            ], 403);
        }

        $nguoiDung->trang_thai = !$nguoiDung->trang_thai;
        $nguoiDung->save();

        $action = $nguoiDung->trang_thai ? 'm? khóa' : 'khóa';
        
        return response()->json([
            'success' => true,
            'message' => "Ðã {$action} tài kho?n {$nguoiDung->ho_ten}.",
            'trang_thai' => $nguoiDung->trang_thai
        ]);
    }

    /**
     * Xóa m?m ngu?i dùng
     */
    public function destroyNguoiDung($id)
    {
        $nguoiDung = NguoiDung::findOrFail($id);
        
        // Không cho xóa chính mình
        if ($nguoiDung->ma_nguoi_dung == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'B?n không th? xóa tài kho?n c?a chính mình.'
            ], 403);
        }

        $nguoiDung->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ðã xóa tài kho?n ' . $nguoiDung->ho_ten . '. Tài kho?n có th? du?c khôi ph?c trong vòng 30 ngày.'
        ]);
    }

    /**
     * Khôi ph?c ngu?i dùng dã xóa
     */
    public function restoreNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);
        $nguoiDung->restore();

        return response()->json([
            'success' => true,
            'message' => 'Ðã khôi ph?c tài kho?n ' . $nguoiDung->ho_ten . '.'
        ]);
    }

    /**
     * Xóa vinh vi?n ngu?i dùng
     */
    public function forceDeleteNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);
        
        // Không cho xóa chính mình
        if ($nguoiDung->ma_nguoi_dung == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'B?n không th? xóa tài kho?n c?a chính mình.'
            ], 403);
        }

        // Xóa ?nh d?i di?n n?u t?n t?i
        if ($nguoiDung->anh_dai_dien && file_exists(public_path('images/' . $nguoiDung->anh_dai_dien))) {
            unlink(public_path('images/' . $nguoiDung->anh_dai_dien));
        }

        $nguoiDung->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Ðã xóa vinh vi?n tài kho?n ' . $nguoiDung->ho_ten . '.'
        ]);
    }

    /**
     * Xu?t danh sách ngu?i dùng ra Excel
     */
    public function exportNguoiDung(Request $request)
    {
        $nguoiDung = NguoiDung::all();
        
        $headers = [
            'H? tên', 'Email', 'Vai trò', 'S? di?n tho?i', 
            'Ngày sinh', 'Ð?a ch?', 'Tr?ng thái', 'Ngày dang ký'
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
                $user->trang_thai ? 'Ho?t d?ng' : 'Ðã khóa',
                $user->created_at->format('d/m/Y H:i'),
            ];
        }

        // T?o file CSV
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
     * L?y nhãn vai trò
     */
    private function getRoleLabel($role)
    {
        $labels = [
            'admin' => 'Qu?n tr? viên',
            'giang_vien' => 'Gi?ng viên',
            'hoc_vien' => 'H?c viên',
        ];

        return $labels[$role] ?? $role;
    }

    /**
     * Th?ng kê h? th?ng chi ti?t
     */
    public function thongKe()
    {
        // Th?ng kê theo tháng
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

        // Th?ng kê theo vai trò
        $roleStats = [
            'total' => NguoiDung::count(),
            'hoc_vien' => NguoiDung::where('vai_tro', 'hoc_vien')->count(),
            'giang_vien' => NguoiDung::where('vai_tro', 'giang_vien')->count(),
            'admin' => NguoiDung::where('vai_tro', 'admin')->count(),
        ];

        return view('pages.admin.thong-ke.index', compact('monthlyStats', 'roleStats'));
    }

    /**
     * Cài d?t h? th?ng
     */
    public function caiDat()
    {
        $settings = [
            'site_name' => config('app.name', 'H? th?ng Qu?n lý'),
            'site_email' => config('mail.from.address', 'admin@example.com'),
            'items_per_page' => config('app.items_per_page', 20),
            'enable_registration' => config('app.enable_registration', true),
            'maintenance_mode' => config('app.maintenance_mode', false),
        ];

        return view('pages.admin.settings.cai-dat', compact('settings'));
    }

    /**
     * Luu cài d?t h? th?ng
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

        // Luu cài d?t vào file .env ho?c database
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
            ->with('success', 'C?p nh?t cài d?t h? th?ng thành công.');
    }

    /**
     * Sao luu co s? d? li?u
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
                ->with('error', 'Không th? sao luu co s? d? li?u. Vui lòng ki?m tra c?u hình.');
        }
    }

    /**
     * Xem nh?t ký h? th?ng
     */
    public function nhatKy(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return view('pages.admin.nhat-ky.index', ['logs' => [], 'error' => 'File log không t?n t?i.']);
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
     * Xác d?nh lo?i log
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
     * Xóa nh?t ký
     */
    public function xoaNhatKy()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        return redirect()->route('admin.nhat-ky')
            ->with('success', 'Ðã xóa t?t c? nh?t ký h? th?ng.');
    }

    /**
     * API l?y thông tin ngu?i dùng
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
     * Tìm ki?m ngu?i dùng nhanh (cho autocomplete)
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
     * Dashboard cho gi?ng viên
     */
    public function giangVienDashboard()
    {
        $giangVienId = auth()->user()->giangVien->id ?? null;

        if (!$giangVienId) {
            return redirect()->route('home')->with('error', 'Tài kho?n c?a b?n chua du?c thi?t l?p profile gi?ng viên.');
        }

        $stats = [
            'dang_day' => PhanCongModuleGiangVien::where('giang_vien_id', $giangVienId)
                ->where('trang_thai', 'da_nhan')
                ->count(),
            'cho_xac_nhan' => PhanCongModuleGiangVien::where('giang_vien_id', $giangVienId)
                ->where('trang_thai', 'cho_xac_nhan')
                ->count(),
            'tong_hoc_vien' => DB::table('hoc_vien_khoa_hoc')
                ->whereIn('khoa_hoc_id', function($query) use ($giangVienId) {
                    $query->select('khoa_hoc_id')
                        ->from('phan_cong_module_giang_vien')
                        ->where('giang_vien_id', $giangVienId);
                })
                ->count(),
            'so_gio_day' => auth()->user()->giangVien->so_gio_day ?? 0,
        ];

        // L?y danh sách phân công m?i nh?t c?n xác nh?n
        $phanCongMoi = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.nhomNganh'])
            ->where('giang_vien_id', $giangVienId)
            ->where('trang_thai', 'cho_xac_nhan')
            ->latest()
            ->take(5)
            ->get();

        // L?y danh sách l?p dang d?y (t? các module dã nh?n)
        $lopDangDay = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.nhomNganh'])
            ->where('giang_vien_id', $giangVienId)
            ->where('trang_thai', 'da_nhan')
            ->latest()
            ->take(5)
            ->get();

        return view('pages.giang-vien.dashboard', compact('stats', 'phanCongMoi', 'lopDangDay'));
    }

    /**
     * Dashboard cho h?c viên
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
     * Ð?m s? h?c viên c?a gi?ng viên
     */
    private function countStudentsOfTeacher($teacherId)
    {
        return 0; // Placeholder
    }

    /**
     * Tính doanh thu c?a gi?ng viên
     */
    private function calculateRevenue($teacherId)
    {
        return 0; // Placeholder
    }

    /**
     * Hi?n th? danh sách tài kho?n ch? phê duy?t
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
     * Phê duy?t tài kho?n
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
            'message' => 'Ðã phê duy?t tài kho?n ' . $taiKhoan->ho_ten . '.',
            'redirect' => $redirectUrl,
            'vai_tro' => $taiKhoan->vai_tro
        ]);
    }

    /**
     * T? ch?i tài kho?n
     */
    public function rejectTaiKhoan($id)
    {
        $taiKhoan = TaiKhoanChoPheDuyet::findOrFail($id);
        $taiKhoan->update(['trang_thai' => 'tu_choi']);

        return response()->json([
            'success' => true,
            'message' => 'Ðã t? ch?i tài kho?n ' . $taiKhoan->ho_ten . '.'
        ]);
    }

    /**
     * H?y phê duy?t tài kho?n
     */
    public function undoApproveTaiKhoan($id)
    {
        $taiKhoan = TaiKhoanChoPheDuyet::findOrFail($id);
        $nguoiDung = NguoiDung::where('email', $taiKhoan->email)->first();

        if (!$nguoiDung) {
            return response()->json([
                'success' => false,
                'message' => 'Tài kho?n không t?n t?i d? h?y phê duy?t.'
            ], 404);
        }

        $nguoiDung->delete();
        $taiKhoan->update(['trang_thai' => 'cho_phe_duyet']);

        return response()->json([
            'success' => true,
            'message' => 'Ðã h?y phê duy?t tài kho?n ' . $taiKhoan->ho_ten . '.'
        ]);
    }

    /**
     * Hi?n th? trang cài d?t h? th?ng
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
     * Luu cài d?t h? th?ng
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
            'address' => 'nullable|string',
            'general_notification' => 'nullable|string',
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
                ->with('success', 'Thông tin liên h? dã du?c c?p nh?t thành công!');
        } elseif (str_contains($currentRoute, 'social')) {
            return redirect()->route('admin.settings.social')
                ->with('success', 'M?ng xã h?i dã du?c c?p nh?t thành công!');
        } else {
            return redirect()->route('admin.settings')
                ->with('success', 'Cài d?t h? th?ng dã du?c c?p nh?t thành công!');
        }
    }

    /**
     * Luu các gi?ng viên hi?n th? trên trang ch?
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
            ->with('success', 'Ch?n gi?ng viên hi?n th? trên trang ch? dã du?c c?p nh?t thành công!');
    }

    /**
     * Hi?n th? trang cài d?t thông tin liên h?
     */
    public function showContactSettings()
    {
        $settings = [
            'site_name' => SystemSetting::get('site_name', ''),
            'site_logo' => SystemSetting::get('site_logo', ''),
            'hotline' => SystemSetting::get('hotline', ''),
            'email' => SystemSetting::get('email', ''),
            'address' => SystemSetting::get('address', ''),
            'general_notification' => SystemSetting::get('general_notification', ''),
            'banner_images' => collect(json_decode(SystemSetting::get('banner_images', '[]'), true) ?: [])
                                ->map(fn($p) => asset($p))
                                ->toArray(),
        ];

        return view('pages.admin.settings.contact', compact('settings'));
    }

    /**
     * Hi?n th? trang cài d?t m?ng xã h?i
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
     * Hi?n th? trang cài d?t gi?ng viên
     */
    public function showInstructorSettings()
    {
        $instructors = GiangVien::with('nguoiDung')->get();
        return view('pages.admin.settings.instructors', compact('instructors'));
    }
}





