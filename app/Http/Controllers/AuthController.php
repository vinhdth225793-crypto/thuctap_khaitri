<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NguoiDung;
use App\Models\TaiKhoanChoPheDuyet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Hiển thị form đăng nhập
    public function showDangNhap()
    {
        return view('pages.auth.dang-nhap'); // Sửa từ auth.dang-nhap -> pages.auth.dang-nhap
    }

    // Xử lý đăng nhập
    public function xuLyDangNhap(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required|min:8',
        ]);

        // Tìm user theo email
        $user = NguoiDung::where('email', $credentials['email'])->first();

        // Kiểm tra user tồn tại và mật khẩu đúng
        if ($user && Hash::check($credentials['mat_khau'], $user->mat_khau)) {
            // Đăng nhập thủ công
            Auth::login($user, $request->has('ghi_nho'));
            $request->session()->regenerate();

            // Chuyển hướng theo vai trò
            $vai_tro = $user->vai_tro ?? 'hoc_vien'; // Mặc định là học viên
            
            if ($vai_tro === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($vai_tro === 'giang_vien') {
                return redirect()->route('giang-vien.dashboard');
            } else {
                return redirect()->route('hoc-vien.dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    // Hiển thị form đăng ký
    public function showDangKy()
    {
        return view('pages.auth.dang-ky'); // Sửa từ auth.dang-ky -> pages.auth.dang-ky
    }

    // Xử lý đăng ký
    public function xuLyDangKy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nguoi_dung',
            'mat_khau' => 'required|string|min:8|confirmed',
            'vai_tro' => 'required|in:hoc_vien,giang_vien',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date',
            'dia_chi' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->vai_tro === 'hoc_vien') {
            // Tạo tài khoản học viên ngay lập tức
            $nguoiDung = NguoiDung::create([
                'ho_ten' => $request->ho_ten,
                'email' => $request->email,
                'mat_khau' => Hash::make($request->mat_khau),
                'password' => Hash::make($request->mat_khau),
                'vai_tro' => $request->vai_tro,
                'so_dien_thoai' => $request->so_dien_thoai,
                'ngay_sinh' => $request->ngay_sinh,
                'dia_chi' => $request->dia_chi,
                'trang_thai' => true,
            ]);

            // Đăng nhập tự động
            Auth::login($nguoiDung);
            return redirect()->route('hoc-vien.dashboard')->with('success', 'Đăng ký tài khoản học viên thành công!');
        } else {
            // Lưu tài khoản giảng viên vào bảng chờ phê duyệt
            TaiKhoanChoPheDuyet::create([
                'ho_ten' => $request->ho_ten,
                'email' => $request->email,
                'mat_khau' => Hash::make($request->mat_khau),
                'vai_tro' => $request->vai_tro,
                'so_dien_thoai' => $request->so_dien_thoai,
                'ngay_sinh' => $request->ngay_sinh,
                'dia_chi' => $request->dia_chi,
                'trang_thai' => 'cho_phe_duyet',
            ]);

            return redirect()->back()->with('success', 'Yêu cầu tạo tài khoản GIẢNG VIÊN của bạn đang được thực hiện. VUI LÒNG CHỜ PHẢN HỒI !');
        }
    }

    // Hiển thị form quên mật khẩu
    public function hienThiQuenMatKhau()
    {
        return view('pages.auth.quen-mat-khau'); // Sửa để phù hợp với cấu trúc
    }

   // Đăng xuất
    public function dangXuat(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home'); // Thay đổi từ 'dang-nhap' thành 'home'
    }
}