<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\NhomNganhController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\KhoaHocManagementController;
use App\Http\Controllers\Admin\HocVienKhoaHocController;
use App\Http\Controllers\Admin\ModuleHocController;
use App\Http\Controllers\Admin\LichHocController;
use App\Http\Controllers\Admin\PhanCongController as AdminPhanCongController;
use App\Http\Controllers\GiangVien\PhanCongController;
use App\Http\Controllers\GiangVien\TaiNguyenController;
use App\Http\Controllers\GiangVien\DiemDanhController;
use App\Http\Controllers\GiangVien\BaiKiemTraController;
use App\Http\Controllers\GiangVienController;
use App\Http\Controllers\HocVienController;
use App\Http\Controllers\ThongBaoController;

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
        return redirect()->route('admin.tai-khoan.edit', $user->ma_nguoi_dung);
    } elseif ($user->vai_tro === 'giang_vien') {
        return redirect()->route('giang-vien.profile');
    } else {
        return redirect()->route('hoc-vien.profile');
    }
})->name('profile')->middleware('auth');

// =========== THÔNG BÁO ROUTES ===========
Route::middleware(['auth'])->group(function () {
    Route::get('/thong-bao',        [ThongBaoController::class, 'index'])  ->name('thong-bao.index');
    Route::get('/thong-bao/{id}',   [ThongBaoController::class, 'docMot']) ->name('thong-bao.doc-mot');
});

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

    // Quản lý Nhóm ngành (Thay thế Môn học)
    Route::prefix('nhom-nganh')->name('mon-hoc.')->group(function () {
        Route::get('/', [NhomNganhController::class, 'index'])->name('index');
        Route::get('/create', [NhomNganhController::class, 'create'])->name('create');
        Route::post('/', [NhomNganhController::class, 'store'])->name('store');
        Route::get('/{id}', [NhomNganhController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [NhomNganhController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NhomNganhController::class, 'update'])->name('update');
        Route::delete('/{id}', [NhomNganhController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [NhomNganhController::class, 'toggleStatus'])->name('toggle-status');
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
        Route::post('/{id}/kich-hoat-mau', [KhoaHocManagementController::class, 'kichHoatMau'])->name('kich-hoat-mau');
        Route::post('/{id}/xac-nhan-mo-lop', [KhoaHocManagementController::class, 'xacNhanMoLop'])->name('xac-nhan-mo-lop');

        // Mở lớp từ khóa học mẫu
        Route::get('/{id}/mo-lop',  [KhoaHocManagementController::class, 'showMoLop'])->name('mo-lop');
        Route::post('/{id}/mo-lop', [KhoaHocManagementController::class, 'storeMoLop'])->name('mo-lop.store');

        // Quản lý học viên trong khóa học
        Route::prefix('{khoaHocId}/hoc-vien')->name('hoc-vien.')->group(function () {
            Route::get('/',         [HocVienKhoaHocController::class, 'index'])->name('index');
            Route::post('/',        [HocVienKhoaHocController::class, 'store'])->name('store');
            Route::put('/{id}',     [HocVienKhoaHocController::class, 'update'])->name('update');
            Route::delete('/{id}',  [HocVienKhoaHocController::class, 'destroy'])->name('destroy');
        });

        // Lịch học của khóa học
        Route::prefix('{khoaHocId}/lich-hoc')->name('lich-hoc.')->group(function () {
            Route::get('/',                      [LichHocController::class, 'index'])->name('index');
            Route::post('/',                     [LichHocController::class, 'store'])->name('store');
            Route::post('/tu-dong',              [LichHocController::class, 'storeAuto'])->name('store-auto');
            Route::delete('/bulk-delete',        [LichHocController::class, 'destroyBulk'])->name('destroy-bulk');
            Route::delete('/module/{moduleId}',  [LichHocController::class, 'destroyModuleSchedules'])->name('destroy-module');
            Route::get('/{id}/edit',             [LichHocController::class, 'edit'])->name('edit');
            Route::put('/{id}',                  [LichHocController::class, 'update'])->name('update');
            Route::delete('/{id}',               [LichHocController::class, 'destroy'])->name('destroy');
            Route::post('/module/{moduleId}/so-buoi', [LichHocController::class, 'updateSoBuoiModule'])
                 ->name('update-so-buoi');
        });
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
        
        // Phân công giảng viên
        Route::post('/{moduleId}/assign', [AdminPhanCongController::class, 'assign'])->name('assign');
    });

    // Phân công chung
    Route::post('/phan-cong/{id}/huy', [AdminPhanCongController::class, 'huy'])->name('phan-cong.huy');
    Route::post('/phan-cong/{id}/replace', [AdminPhanCongController::class, 'replace'])->name('phan-cong.replace');

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
    Route::get('/dashboard', [GiangVienController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [GiangVienController::class, 'profile'])->name('profile');
    Route::post('/profile', [GiangVienController::class, 'updateProfile'])->name('profile.update');

    // Phân công dạy học (Nâng cấp Phase B)
    Route::get('/khoa-hoc', [PhanCongController::class, 'index'])->name('khoa-hoc');
    Route::get('/khoa-hoc/{id}', [PhanCongController::class, 'show'])->name('khoa-hoc.show');
    Route::post('/khoa-hoc/{id}/xac-nhan', [PhanCongController::class, 'xacNhan'])->name('khoa-hoc.xac-nhan');
    Route::put('/buoi-hoc/{id}/link', [PhanCongController::class, 'updateLinkOnline'])->name('buoi-hoc.update-link');
    
    // Quản lý Học viên & Yêu cầu (Phase 6)
    Route::post('/khoa-hoc/{khoaHocId}/yeu-cau-hoc-vien', [PhanCongController::class, 'guiYeuCauHocVien'])->name('khoa-hoc.gui-yeu-cau-hoc-vien');
    
    // Quản lý Tài nguyên (Phase 4)
    Route::post('/buoi-hoc/{lichHocId}/tai-nguyen', [TaiNguyenController::class, 'store'])->name('buoi-hoc.tai-nguyen.store');
    Route::delete('/tai-nguyen/{id}', [TaiNguyenController::class, 'destroy'])->name('buoi-hoc.tai-nguyen.destroy');

    // Điểm danh (Phase 7)
    Route::get('/buoi-hoc/{lichHocId}/diem-danh', [DiemDanhController::class, 'show'])->name('buoi-hoc.diem-danh.show');
    Route::post('/buoi-hoc/{lichHocId}/diem-danh', [DiemDanhController::class, 'store'])->name('buoi-hoc.diem-danh.store');

    // Bài kiểm tra (Phase 8)
    Route::post('/bai-kiem-tra', [BaiKiemTraController::class, 'store'])->name('bai-kiem-tra.store');
    Route::delete('/bai-kiem-tra/{id}', [BaiKiemTraController::class, 'destroy'])->name('bai-kiem-tra.destroy');
    
    // Giữ route cũ cho backward compatibility nếu cần (nhưng ta sẽ cập nhật các view chính)
    Route::get('/phan-cong', [PhanCongController::class, 'index'])->name('phan-cong.index');
    Route::post('/phan-cong/{id}/xac-nhan', [PhanCongController::class, 'xacNhan'])->name('phan-cong.xac-nhan');

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
