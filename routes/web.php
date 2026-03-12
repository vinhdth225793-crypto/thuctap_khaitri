<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\KhoaHocController;
use App\Http\Controllers\Admin\BannerController;
// Route gốc chuyển đến trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Alias route 'login' để tương thích với Laravel auth middleware
Route::get('/login', function () {
    return redirect()->route('dang-nhap');
})->name('login');

// Public Routes - Truy cập khi chưa đăng nhập
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showDangNhap'])->name('dang-nhap');
    Route::post('/dang-nhap', [AuthController::class, 'xuLyDangNhap'])->name('xu-ly-dang-nhap');
    Route::get('/dang-ky', [AuthController::class, 'showDangKy'])->name('dang-ky');
    Route::post('/dang-ky', [AuthController::class, 'xuLyDangKy'])->name('xu-ly-dang-ky');
    Route::get('/quen-mat-khau', [AuthController::class, 'hienThiQuenMatKhau'])->name('quen-mat-khau');
    
    // Trang chủ công khai - dùng HomeController
    Route::get('/trang-chu', [HomeController::class, 'index'])->name('trang-chu');
    Route::get('/tim-giang-vien', [HomeController::class, 'searchGiangVien'])->name('tim-giang-vien');
});

// Logout - Ai cũng có thể truy cập
Route::post('/dang-xuat', [AuthController::class, 'dangXuat'])->name('dang-xuat');

// Protected Routes - Cần đăng nhập để truy cập
Route::middleware(['auth'])->group(function () {
    // Profile chung - tự động chuyển hướng theo vai trò
    Route::get('/profile', function () {
        $user = auth()->user();
        
        // Kiểm tra xem user có thuộc tính vai_tro không
        if (!isset($user->vai_tro)) {
            abort(403, 'Người dùng không có vai trò xác định');
        }
        
        if ($user->vai_tro === 'admin') {
            return redirect()->route('admin.profile');
        } elseif ($user->vai_tro === 'giang_vien') {
            return redirect()->route('giang-vien.profile');
        } else {
            return redirect()->route('hoc-vien.profile');
        }
    })->name('profile');
    
    // =========== ADMIN ROUTES ===========
    Route::prefix('admin')->name('admin.')->middleware(['auth', \App\Http\Middleware\CheckAdmin::class])->group(function () {
        // dashboard và trang tĩnh có thể vẫn gọi view tĩnh hoặc controller
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', function () {
            return view('pages.admin.profile');
        })->name('profile');
        Route::get('/cai-dat', [\App\Http\Controllers\Admin\AdminController::class, 'showSettings'])->name('settings');
        // Routes con cho cài đặt hệ thống
        Route::prefix('cai-dat')->name('settings.')->group(function () {
            Route::get('/thong-tin-lien-he', [\App\Http\Controllers\Admin\AdminController::class, 'showContactSettings'])->name('contact');
            Route::post('/thong-tin-lien-he', [\App\Http\Controllers\Admin\AdminController::class, 'saveSettings'])->name('contact.save');
            Route::get('/mang-xa-hoi', [\App\Http\Controllers\Admin\AdminController::class, 'showSocialSettings'])->name('social');
            Route::post('/mang-xa-hoi', [\App\Http\Controllers\Admin\AdminController::class, 'saveSettings'])->name('social.save');
            Route::get('/giang-vien', [\App\Http\Controllers\Admin\AdminController::class, 'showInstructorSettings'])->name('instructors');
            Route::post('/giang-vien', [\App\Http\Controllers\Admin\AdminController::class, 'saveInstructorSettings'])->name('instructors.save');

            // BANNER
            Route::prefix('banner')->name('banners.')->group(function () {
                Route::get('/',                    [BannerController::class, 'index'])->name('index');
                Route::get('/create',              [BannerController::class, 'create'])->name('create');
                Route::post('/',                   [BannerController::class, 'store'])->name('store');
                Route::post('/update-order',       [BannerController::class, 'updateOrder'])->name('update-order');
                Route::get('/{id}/edit',           [BannerController::class, 'edit'])->name('edit');
                Route::put('/{id}',                [BannerController::class, 'update'])->name('update');
                Route::delete('/{id}',             [BannerController::class, 'destroy'])->name('destroy');
                Route::post('/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('toggle-status');
            });
        });
        
        // các trang quản lý khác
        Route::get('/quan-ly-hoc-vien', function () {
            return redirect()->route('admin.tai-khoan.index');
        })->name('quan-ly-hoc-vien');
        Route::get('/quan-ly-giang-vien', function () {
            return redirect()->route('admin.tai-khoan.index');
        })->name('quan-ly-giang-vien');
        Route::get('/quan-ly-khoa-hoc', function () {
            return view('pages.admin.khoa-hoc');
        })->name('quan-ly-khoa-hoc');
        
        // QUẢN LÝ NGƯỜI DÙNG
        Route::get('/tai-khoan', [\App\Http\Controllers\Admin\AdminController::class, 'indexNguoiDung'])->name('tai-khoan.index');
        Route::get('/tai-khoan/create', [\App\Http\Controllers\Admin\AdminController::class, 'createNguoiDung'])->name('tai-khoan.create');
        Route::get('/tai-khoan/export', [\App\Http\Controllers\Admin\AdminController::class, 'exportNguoiDung'])->name('tai-khoan.export');
        Route::post('/tai-khoan', [\App\Http\Controllers\Admin\AdminController::class, 'storeNguoiDung'])->name('tai-khoan.store');
        Route::get('/tai-khoan/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'showNguoiDung'])->name('tai-khoan.show');
        Route::get('/tai-khoan/{id}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'editNguoiDung'])->name('tai-khoan.edit');
        Route::post('/tai-khoan/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'updateNguoiDung'])->name('tai-khoan.update');
        Route::post('/tai-khoan/{id}/toggle', [\App\Http\Controllers\Admin\AdminController::class, 'toggleStatusNguoiDung'])->name('tai-khoan.toggle');
        Route::post('/tai-khoan/{id}/restore', [\App\Http\Controllers\Admin\AdminController::class, 'restoreNguoiDung'])->name('tai-khoan.restore');
        Route::delete('/tai-khoan/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'destroyNguoiDung'])->name('tai-khoan.destroy');
        Route::delete('/tai-khoan/{id}/force', [\App\Http\Controllers\Admin\AdminController::class, 'forceDeleteNguoiDung'])->name('tai-khoan.forceDelete');
        
        // QUẢN LÝ HỌC VIÊN
        Route::get('/hoc-vien', [\App\Http\Controllers\Admin\AdminController::class, 'indexHocVien'])->name('hoc-vien.index');
        
        // QUẢN LÝ GIẢNG VIÊN
        Route::get('/giang-vien', [\App\Http\Controllers\Admin\AdminController::class, 'indexGiangVien'])->name('giang-vien.index');
        
        // PHÊ DUYỆT TÀI KHOẢN
        Route::get('/phe-duyet-tai-khoan', [\App\Http\Controllers\Admin\AdminController::class, 'indexPheDuyetTaiKhoan'])->name('phe-duyet-tai-khoan.index');
        Route::post('/phe-duyet-tai-khoan/{id}/approve', [\App\Http\Controllers\Admin\AdminController::class, 'approveTaiKhoan'])->name('phe-duyet-tai-khoan.approve');
        Route::post('/phe-duyet-tai-khoan/{id}/reject', [\App\Http\Controllers\Admin\AdminController::class, 'rejectTaiKhoan'])->name('phe-duyet-tai-khoan.reject');
        Route::post('/phe-duyet-tai-khoan/{id}/undo', [\App\Http\Controllers\Admin\AdminController::class, 'undoApproveTaiKhoan'])->name('phe-duyet-tai-khoan.undo');
        
        // QUẢN LÝ KHÓA HỌC (Môn học, Khóa học, Module)
        Route::prefix('mon-hoc')->name('mon-hoc.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\KhoaHocController::class, 'indexMonHoc'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\KhoaHocController::class, 'createMonHoc'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\KhoaHocController::class, 'storeMonHoc'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\KhoaHocController::class, 'showMonHoc'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\KhoaHocController::class, 'editMonHoc'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\KhoaHocController::class, 'updateMonHoc'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\KhoaHocController::class, 'destroyMonHoc'])->name('destroy');
            Route::post('/{id}/toggle-status', [\App\Http\Controllers\Admin\KhoaHocController::class, 'toggleStatusMonHoc'])->name('toggle-status');
        });
        
        Route::prefix('khoa-hoc')->name('khoa-hoc.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\KhoaHocManagementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\KhoaHocManagementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\KhoaHocManagementController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\KhoaHocManagementController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\KhoaHocManagementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\KhoaHocManagementController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\KhoaHocManagementController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [\App\Http\Controllers\Admin\KhoaHocManagementController::class, 'toggleStatus'])->name('toggle-status');
        });
        
        Route::prefix('module-hoc')->name('module-hoc.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\KhoaHocController::class, 'indexModuleHoc'])->name('index');
        });
    });
    
    // =========== GIẢNG VIÊN ROUTES ===========
    Route::prefix('giang-vien')->name('giang-vien.')->middleware(['auth', \App\Http\Middleware\CheckGiangVien::class])->group(function () {
        Route::get('/dashboard', function () {
            return view('pages.giang-vien.dashboard');
        })->name('dashboard');
        
        // profile via controller so we can update
        Route::get('/profile', [\App\Http\Controllers\GiangVienController::class, 'profile'])->name('profile');
        Route::post('/profile', [\App\Http\Controllers\GiangVienController::class, 'updateProfile'])->name('profile.update');
        
        Route::get('/khoa-hoc', function () {
            return view('pages.giang-vien.khoa-hoc');
        })->name('khoa-hoc');
        
        Route::get('/tao-bai-giang', function () {
            return view('pages.giang-vien.tao-bai-giang');
        })->name('tao-bai-giang');
        
        Route::get('/tao-bai-kiem-tra', function () {
            return view('pages.giang-vien.tao-bai-kiem-tra');
        })->name('tao-bai-kiem-tra');
        
        Route::get('/cham-diem', function () {
            return view('pages.giang-vien.cham-diem');
        })->name('cham-diem');
    });
    
    // =========== HỌC VIÊN ROUTES ===========
    Route::prefix('hoc-vien')->name('hoc-vien.')->middleware(['auth', \App\Http\Middleware\CheckHocVien::class])->group(function () {
        Route::get('/dashboard', function () {
            return view('pages.hoc-vien.dashboard');
        })->name('dashboard');
        
        Route::get('/profile', [\App\Http\Controllers\HocVienController::class, 'profile'])->name('profile');
        Route::post('/profile', [\App\Http\Controllers\HocVienController::class, 'updateProfile'])->name('profile.update');
        
        Route::get('/khoa-hoc-cua-toi', function () {
            return view('pages.hoc-vien.khoa-hoc-cua-toi');
        })->name('khoa-hoc-cua-toi');
        
        Route::get('/bai-kiem-tra', function () {
            return view('pages.hoc-vien.bai-kiem-tra');
        })->name('bai-kiem-tra');
        
        Route::get('/ket-qua', function () {
            return view('pages.hoc-vien.ket-qua');
        })->name('ket-qua');
    });
});

// =========== FALLBACK ROUTES ===========
Route::fallback(function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        if (!isset($user->vai_tro)) {
            abort(403, 'Người dùng không có vai trò xác định');
        }
        
        if ($user->vai_tro === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->vai_tro === 'giang_vien') {
            return redirect()->route('giang-vien.dashboard');
        } else {
            return redirect()->route('hoc-vien.dashboard');
        }
    } else {
        // Chuyển về trang chủ thay vì đăng nhập
        return redirect()->route('home');
    }
});