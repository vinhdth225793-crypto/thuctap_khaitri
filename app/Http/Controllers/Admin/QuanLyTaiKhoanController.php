<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\GiangVienDonXinNghi;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class QuanLyTaiKhoanController extends Controller
{
    public function indexNguoiDung(Request $request)
    {
        $query = NguoiDung::withTrashed();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        if ($request->has('vai_tro') && $request->get('vai_tro') != 'all') {
            $query->where('vai_tro', $request->get('vai_tro'));
        }

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

        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $nguoiDung = $query->paginate(20)->withQueryString();

        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.index', compact('nguoiDung'));
    }

    public function createNguoiDung()
    {
        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.create');
    }

    public function storeNguoiDung(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:nguoi_dung,email',
                'unique:tai_khoan_cho_phe_duyet,email',
            ],
            'mat_khau' => 'required|string|min:8|confirmed',
            'vai_tro' => 'required|in:admin,giang_vien,hoc_vien',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date|before:today',
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trang_thai' => 'required|boolean',
        ], [
            'email.unique' => 'Email này đã được sử dụng hoặc đang chờ phê duyệt.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('anh_dai_dien', 'mat_khau_confirmation');
        $data['mat_khau'] = Hash::make($request->mat_khau);

        if ($request->hasFile('anh_dai_dien')) {
            $file = $request->file('anh_dai_dien');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['anh_dai_dien'] = $filename;
        }

        NguoiDung::create($data);

        return redirect()->route('admin.tai-khoan.index')
            ->with('success', 'Tạo người dùng mới thành công.');
    }

    public function showNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);

        $stats = [
            'tongDangKy' => 0,
            'ngayTao' => $nguoiDung->created_at,
            'lanCuoiDangNhap' => $nguoiDung->updated_at,
        ];

        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.show', compact('nguoiDung', 'stats'));
    }

    public function indexHocVien(Request $request)
    {
        $query = NguoiDung::where('vai_tro', 'hoc_vien')->withTrashed();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

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

        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        if (! in_array($sortField, ['ho_ten', 'email', 'created_at', 'trang_thai'])) {
            $sortField = 'created_at';
        }
        if (! in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        if ($sortField === 'ho_ten') {
            $query->orderByRaw("SUBSTRING(ho_ten, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, ho_ten {$sortDirection}");
        } elseif ($sortField === 'email') {
            $query->orderByRaw("SUBSTRING(email, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, email {$sortDirection}");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $hocVien = $query->with('hocVien')->paginate(20)->withQueryString();

        return view('pages.admin.quan-ly-tai-khoan.hoc-vien.index', compact('hocVien'));
    }

    public function indexGiangVien(Request $request)
    {
        $query = NguoiDung::where('vai_tro', 'giang_vien')->withTrashed();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

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

        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        if (! in_array($sortField, ['ho_ten', 'email', 'created_at', 'trang_thai'])) {
            $sortField = 'created_at';
        }
        if (! in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        if ($sortField === 'ho_ten') {
            $query->orderByRaw("SUBSTRING(ho_ten, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, ho_ten {$sortDirection}");
        } elseif ($sortField === 'email') {
            $query->orderByRaw("SUBSTRING(email, 1, 1) COLLATE utf8mb4_unicode_ci {$sortDirection}, email {$sortDirection}");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

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

    public function editNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);

        return view('pages.admin.quan-ly-tai-khoan.tai-khoan.edit', compact('nguoiDung'));
    }

    public function updateNguoiDung(Request $request, $id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:nguoi_dung,email,'.$id.',ma_nguoi_dung',
                'unique:tai_khoan_cho_phe_duyet,email',
            ],
            'vai_tro' => 'required|in:admin,giang_vien,hoc_vien',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date|before:today',
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trang_thai' => 'required|boolean',
            'mat_khau' => 'nullable|min:8|confirmed',
        ], [
            'email.unique' => 'Email này đã được sử dụng hoặc đang chờ phê duyệt.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('anh_dai_dien', 'mat_khau', 'mat_khau_confirmation');

        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        if ($request->hasFile('anh_dai_dien')) {
            if ($nguoiDung->anh_dai_dien && file_exists(public_path('images/'.$nguoiDung->anh_dai_dien))) {
                unlink(public_path('images/'.$nguoiDung->anh_dai_dien));
            }

            $file = $request->file('anh_dai_dien');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['anh_dai_dien'] = $filename;
        }

        if ($request->has('xoa_anh_dai_dien') && $nguoiDung->anh_dai_dien) {
            Storage::disk('public')->delete($nguoiDung->anh_dai_dien);
            $data['anh_dai_dien'] = null;
        }

        $nguoiDung->update($data);

        return redirect()->route('admin.tai-khoan.show', $nguoiDung->id)
            ->with('success', 'Cập nhật thông tin người dùng thành công.');
    }

    public function toggleStatusNguoiDung($id)
    {
        $nguoiDung = NguoiDung::findOrFail($id);

        if ($nguoiDung->id == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể khóa tài khoản của chính mình.',
            ], 403);
        }

        $nguoiDung->trang_thai = ! $nguoiDung->trang_thai;
        $nguoiDung->save();

        $action = $nguoiDung->trang_thai ? 'mở khóa' : 'khóa';

        return response()->json([
            'success' => true,
            'message' => "Đã {$action} tài khoản {$nguoiDung->ho_ten}.",
            'trang_thai' => $nguoiDung->trang_thai,
        ]);
    }

    public function destroyNguoiDung($id)
    {
        $nguoiDung = NguoiDung::findOrFail($id);

        if ($nguoiDung->id == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể xóa tài khoản của chính mình.',
            ], 403);
        }

        $nguoiDung->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tài khoản '.$nguoiDung->ho_ten.'. Tài khoản có thể được khôi phục trong vòng 30 ngày.',
        ]);
    }

    public function restoreNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);
        $nguoiDung->restore();

        return response()->json([
            'success' => true,
            'message' => 'Đã khôi phục tài khoản '.$nguoiDung->ho_ten.'.',
        ]);
    }

    public function forceDeleteNguoiDung($id)
    {
        $nguoiDung = NguoiDung::withTrashed()->findOrFail($id);

        if ($nguoiDung->id == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể xóa tài khoản của chính mình.',
            ], 403);
        }

        if ($nguoiDung->anh_dai_dien && file_exists(public_path('images/'.$nguoiDung->anh_dai_dien))) {
            unlink(public_path('images/'.$nguoiDung->anh_dai_dien));
        }

        $nguoiDung->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa vĩnh viễn tài khoản '.$nguoiDung->ho_ten.'.',
        ]);
    }

    public function exportNguoiDung(Request $request)
    {
        $nguoiDung = NguoiDung::all();

        $headers = [
            'Họ tên', 'Email', 'Vai trò', 'Số điện thoại',
            'Ngày sinh', 'Địa chỉ', 'Trạng thái', 'Ngày đăng ký',
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

        $filename = 'danh-sach-nguoi-dung-'.date('Y-m-d').'.csv';

        $handle = fopen('php://output', 'w');
        fputcsv($handle, $headers);

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return response()->streamDownload(function () use ($handle) {
            echo $handle;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function getRoleLabel($role)
    {
        $labels = [
            'admin' => 'Quản trị viên',
            'giang_vien' => 'Giảng viên',
            'hoc_vien' => 'Học viên',
        ];

        return $labels[$role] ?? $role;
    }

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
            'success' => true,
        ]);
    }

    public function timKiemNguoiDung(Request $request)
    {
        $search = $request->get('q');

        $nguoiDung = NguoiDung::where('ho_ten', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(10)
            ->get(['ma_nguoi_dung as id', 'ho_ten', 'email', 'vai_tro']);

        return response()->json($nguoiDung);
    }
}
