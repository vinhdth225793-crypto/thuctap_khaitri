<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\TaiKhoanChoPheDuyet;
use Illuminate\Http\Request;

class PheDuyetTaiKhoanController extends Controller
{
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

    public function approveTaiKhoan($id)
    {
        $taiKhoan = TaiKhoanChoPheDuyet::findOrFail($id);

        if (NguoiDung::where('email', $taiKhoan->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email này đã tồn tại trong hệ thống.',
            ], 422);
        }

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

        if ($nguoiDung->vai_tro === 'hoc_vien') {
            $nguoiDung->hocVien()->create([]);
        } elseif ($nguoiDung->vai_tro === 'giang_vien') {
            $nguoiDung->giangVien()->create([]);
        }

        $taiKhoan->update(['trang_thai' => 'da_phe_duyet']);

        try {
            app(\App\Services\NotificationService::class)->notifyAccountDecided(
                (int) $nguoiDung->ma_nguoi_dung,
                true
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $redirectUrl = $taiKhoan->vai_tro === 'giang_vien'
            ? route('admin.giang-vien.index')
            : route('admin.hoc-vien.index');

        return response()->json([
            'success' => true,
            'message' => 'Đã phê duyệt tài khoản '.$taiKhoan->ho_ten.'.',
            'redirect' => $redirectUrl,
            'vai_tro' => $taiKhoan->vai_tro,
        ]);
    }

    public function rejectTaiKhoan($id)
    {
        $taiKhoan = TaiKhoanChoPheDuyet::findOrFail($id);
        $taiKhoan->update(['trang_thai' => 'tu_choi']);

        return response()->json([
            'success' => true,
            'message' => 'Đã từ chối tài khoản '.$taiKhoan->ho_ten.'.',
        ]);
    }

    public function undoApproveTaiKhoan($id)
    {
        $taiKhoan = TaiKhoanChoPheDuyet::findOrFail($id);
        $nguoiDung = NguoiDung::where('email', $taiKhoan->email)->first();

        if (! $nguoiDung) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại để hủy phê duyệt.',
            ], 404);
        }

        $nguoiDung->delete();
        $taiKhoan->update(['trang_thai' => 'cho_phe_duyet']);

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy phê duyệt tài khoản '.$taiKhoan->ho_ten.'.',
        ]);
    }
}
