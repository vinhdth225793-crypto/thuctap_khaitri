<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\KhoaHocController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\KhoaHocManagementController;
use App\Http\Controllers\Admin\ModuleHocController;
use App\Http\Controllers\Admin\PhanCongController;
use App\Http\Controllers\GiangVienController;
use App\Http\Controllers\HocVienController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('home.search');

// =========== AUTH ROUTES ===========
Route::middleware('guest')->group(function () {
    Route::get('/dang-ky', [AuthController::class, 'showRegisterForm'])->name('dang-ky');
    Route::post('/dang-ky', [AuthController::class, 'register'])->name('xu-ly-dang-ky');
    Route::get('/dang-nhap', [AuthController::class, 'showLoginForm'])->name('dang-nhap');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('xu-ly-dang-nhap');
});

Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('dang-xuat')->middleware('auth');

// Route Profile chung (Điều hướng theo vai trò)
Route::get('/profile', function () {
    $user = auth()->user();
    if ($user->vai_tro === 'admin') {
        // Nếu admin chưa có trang profile riêng thì có thể tạo hoặc tạm thời dùng trang sửa user chính mình
        return redirect()->route('admin.tai-khoan.edit', $user->ma_nguoi_dung);
    } elseif ($user->vai_tro === 'giang_vien') {
        return redirect()->route('giang-vien.profile');
    } else {
        return redirect()->route('hoc-vien.profile');
    }
})->name('profile')->middleware('auth');

// =========== ADMIN ROUTES ===========
Route::prefix('admin')->name('admin.')->middleware(['auth', \App\Http\Middleware\CheckAdmin::class])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Quản lý Banner
    Route::prefix('banner')->name('banner.')->group(function () {
        Route::get('/', [BannerController::class, 'index'])->name('index');
        Route::get('/create', [BannerController::class, 'create'])->name('create');
        Route::post('/', [BannerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [BannerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BannerController::class, 'update'])->name('update');
        Route::delete('/{id}', [BannerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Quản lý Tài khoản & Phê duyệt
    Route::prefix('tai-khoan')->name('tai-khoan.')->group(function () {
        Route::get('/', [AdminController::class, 'indexNguoiDung'])->name('index');
        Route::get('/create', [AdminController::class, 'createNguoiDung'])->name('create');
        Route::post('/', [AdminController::class, 'storeNguoiDung'])->name('store');
        Route::get('/{id}', [AdminController::class, 'showNguoiDung'])->name('show');
        Route::get('/{id}/edit', [AdminController::class, 'editNguoiDung'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'updateNguoiDung'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroyNguoiDung'])->name('destroy');
        Route::post('/{id}/toggle-status', [AdminController::class, 'toggleStatusNguoiDung'])->name('toggle-status');
    });

    Route::prefix('phe-duyet-tai-khoan')->name('phe-duyet-tai-khoan.')->group(function () {
        Route::get('/', [AdminController::class, 'indexPheDuyetTaiKhoan'])->name('index');
        Route::post('/{id}/approve', [AdminController::class, 'approveTaiKhoan'])->name('approve');
        Route::post('/{id}/reject', [AdminController::class, 'rejectTaiKhoan'])->name('reject');
        Route::post('/{id}/undo', [AdminController::class, 'undoApproveTaiKhoan'])->name('undo');
    });

    Route::get('/giang-vien', [AdminController::class, 'indexGiangVien'])->name('giang-vien.index');
    Route::get('/hoc-vien', [AdminController::class, 'indexHocVien'])->name('hoc-vien.index');

    // Quản lý Môn học
    Route::prefix('mon-hoc')->name('mon-hoc.')->group(function () {
        Route::get('/', [KhoaHocController::class, 'indexMonHoc'])->name('index');
        Route::get('/create', [KhoaHocController::class, 'createMonHoc'])->name('create');
        Route::post('/', [KhoaHocController::class, 'storeMonHoc'])->name('store');
        Route::get('/{id}', [KhoaHocController::class, 'showMonHoc'])->name('show');
        Route::get('/{id}/edit', [KhoaHocController::class, 'editMonHoc'])->name('edit');
        Route::put('/{id}', [KhoaHocController::class, 'updateMonHoc'])->name('update');
        Route::delete('/{id}', [KhoaHocController::class, 'destroyMonHoc'])->name('destroy');
        Route::post('/{id}/toggle-status', [KhoaHocController::class, 'toggleStatusMonHoc'])->name('toggle-status');
    });

    // Quản lý Khóa học
    Route::prefix('khoa-hoc')->name('khoa-hoc.')->group(function () {
        Route::get('/', [KhoaHocManagementController::class, 'index'])->name('index');
        Route::get('/create', [KhoaHocManagementController::class, 'create'])->name('create');
        Route::post('/', [KhoaHocManagementController::class, 'store'])->name('store');
        Route::get('/{id}', [KhoaHocManagementController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [KhoaHocManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KhoaHocManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [KhoaHocManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [KhoaHocManagementController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Quản lý Module học độc lập
    Route::prefix('module-hoc')->name('module-hoc.')->group(function () {
        Route::get('/',              [ModuleHocController::class, 'index'])->name('index');
        Route::get('/create',        [ModuleHocController::class, 'create'])->name('create');
        Route::post('/',             [ModuleHocController::class, 'store'])->name('store');
        Route::get('/{id}',          [ModuleHocController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [ModuleHocController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [ModuleHocController::class, 'update'])->name('update');
        Route::delete('/{id}',       [ModuleHocController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [ModuleHocController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Phân công giảng viên
    Route::post('/module-hoc/{moduleId}/assign', [PhanCongController::class, 'assign'])->name('phan-cong.assign');
    Route::post('/phan-cong/{id}/huy', [PhanCongController::class, 'huy'])->name('phan-cong.huy');

    // Cài đặt hệ thống
    Route::prefix('settings')->group(function () {
        Route::get('/', [AdminController::class, 'showSettings'])->name('settings');
        Route::post('/', [AdminController::class, 'saveSettings'])->name('settings.save');
        Route::get('/contact', [AdminController::class, 'showContactSettings'])->name('settings.contact');
        Route::get('/social', [AdminController::class, 'showSocialSettings'])->name('settings.social');
        Route::get('/instructors', [AdminController::class, 'showInstructorSettings'])->name('settings.instructors');
        Route::post('/instructors', [AdminController::class, 'saveInstructorSettings'])->name('settings.instructors.save');

        // Quản lý Banner (nằm trong settings theo view cấu trúc)
        Route::prefix('banners')->name('settings.banners.')->group(function () {
            Route::get('/', [BannerController::class, 'index'])->name('index');
            Route::get('/create', [BannerController::class, 'create'])->name('create');
            Route::post('/', [BannerController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [BannerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BannerController::class, 'update'])->name('update');
            Route::delete('/{id}', [BannerController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/update-order', [BannerController::class, 'updateOrder'])->name('update-order');
        });
    });
});

// =========== GIẢNG VIÊN ROUTES ===========
Route::prefix('giang-vien')->name('giang-vien.')->middleware(['auth', \App\Http\Middleware\CheckGiangVien::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.giang-vien.dashboard');
    })->name('dashboard');
    Route::get('/profile', [GiangVienController::class, 'profile'])->name('profile');
    Route::post('/profile', [GiangVienController::class, 'updateProfile'])->name('profile.update');

    // Phân công dạy học
    Route::get('/phan-cong', [GiangVienController::class, 'phanCong'])->name('phan-cong');
    Route::get('/khoa-hoc', [GiangVienController::class, 'phanCong'])->name('khoa-hoc'); // Thêm alias này để fix lỗi
    Route::post('/phan-cong/{id}/xac-nhan', [GiangVienController::class, 'xacNhanPhanCong'])->name('phan-cong.xac-nhan');
    Route::post('/phan-cong/{id}/tu-choi', [GiangVienController::class, 'tuChoiPhanCong'])->name('phan-cong.tu-choi');

    // Các tính năng khác (Placeholder)
    Route::get('/tao-bai-giang', function () { return "Tính năng Tạo bài giảng đang được phát triển"; })->name('tao-bai-giang');
    Route::get('/tao-bai-kiem-tra', function () { return "Tính năng Tạo bài kiểm tra đang được phát triển"; })->name('tao-bai-kiem-tra');
    Route::get('/cham-diem', function () { return "Tính năng Chấm điểm đang được phát triển"; })->name('cham-diem');
});

// =========== HỌC VIÊN ROUTES ===========
Route::prefix('hoc-vien')->name('hoc-vien.')->middleware(['auth', \App\Http\Middleware\CheckHocVien::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.hoc-vien.dashboard');
    })->name('dashboard');
    Route::get('/profile', [HocVienController::class, 'profile'])->name('profile');
    Route::post('/profile', [HocVienController::class, 'updateProfile'])->name('profile.update');
});

// Redirect sau khi đăng nhập
Route::get('/home', function () {
    if (auth()->check()) {
        if (auth()->user()->vai_tro === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->vai_tro === 'giang_vien') {
            return redirect()->route('giang-vien.dashboard');
        } else {
            return redirect()->route('hoc-vien.dashboard');
        }
    } else {
        return redirect()->route('home');
    }
});
