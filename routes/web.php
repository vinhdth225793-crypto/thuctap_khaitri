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
use App\Http\Controllers\Admin\NganHangCauHoiController;
use App\Http\Controllers\Admin\BaiKiemTraPheDuyetController;
use App\Http\Controllers\Admin\PhanCongController as AdminPhanCongController;
use App\Http\Controllers\GiangVien\PhanCongController;
use App\Http\Controllers\GiangVien\TaiNguyenController;
use App\Http\Controllers\GiangVien\DiemDanhController;
use App\Http\Controllers\GiangVien\BaiKiemTraController;
use App\Http\Controllers\GiangVien\BaiGiangController;
use App\Http\Controllers\GiangVien\LiveRoomController as GiangVienLiveRoomController;
use App\Http\Controllers\HocVien\BaiKiemTraController as HocVienBaiKiemTraController;
use App\Http\Controllers\HocVien\LiveRoomController as HocVienLiveRoomController;
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

    // Quản lý Yêu cầu từ Giảng viên (Phase 3)
    Route::get('/yeu-cau-hoc-vien', [App\Http\Controllers\Admin\YeuCauHocVienController::class, 'index'])->name('yeu-cau-hoc-vien.index');
    Route::post('/yeu-cau-hoc-vien/{id}/xac-nhan', [App\Http\Controllers\Admin\YeuCauHocVienController::class, 'xacNhan'])->name('yeu-cau-hoc-vien.xac-nhan');

    // Quản lý Thư viện & Bài giảng (Phase 10 Upgrade)
    Route::prefix('thu-vien')->name('thu-vien.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ThuVienController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\ThuVienController::class, 'show'])->name('show');
        Route::post('/{id}/duyet', [App\Http\Controllers\Admin\ThuVienController::class, 'duyet'])->name('duyet');
        Route::delete('/{id}', [App\Http\Controllers\Admin\ThuVienController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('bai-giang-phe-duyet')->name('bai-giang.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\BaiGiangController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\BaiGiangController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\BaiGiangController::class, 'store'])->name('store');
        Route::get('/ajax/get-lich-hoc', [App\Http\Controllers\Admin\BaiGiangController::class, 'getLichHoc'])->name('get-lich-hoc');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\BaiGiangController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\BaiGiangController::class, 'update'])->name('update');
        Route::get('/{id}', [App\Http\Controllers\Admin\BaiGiangController::class, 'show'])->name('show');
        Route::post('/{id}/duyet', [App\Http\Controllers\Admin\BaiGiangController::class, 'duyet'])->name('duyet');
        Route::post('/{id}/cong-bo', [App\Http\Controllers\Admin\BaiGiangController::class, 'congBo'])->name('cong-bo');
    });

    Route::prefix('kiem-tra-online')->name('kiem-tra-online.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.kiem-tra-online.cau-hoi.index');
        });
        
        Route::prefix('cau-hoi')->name('cau-hoi.')->group(function () {
            Route::get('/', [NganHangCauHoiController::class, 'index'])->name('index');
            Route::get('/create', [NganHangCauHoiController::class, 'create'])->name('create');
            Route::post('/', [NganHangCauHoiController::class, 'store'])->name('store');
            Route::get('/template', [NganHangCauHoiController::class, 'downloadTemplate'])->name('template');
            Route::post('/import', [NganHangCauHoiController::class, 'import'])->name('import');
            Route::get('/preview', [NganHangCauHoiController::class, 'preview'])->name('preview');
            Route::get('/export-preview', [NganHangCauHoiController::class, 'exportPreview'])->name('export-preview');
            Route::post('/confirm-import', [NganHangCauHoiController::class, 'confirmImport'])->name('confirm-import');
            Route::get('/{id}/edit', [NganHangCauHoiController::class, 'edit'])->name('edit');
            Route::put('/{id}', [NganHangCauHoiController::class, 'update'])->name('update');
            Route::post('/{id}/toggle-status', [NganHangCauHoiController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{id}/toggle-reusable', [NganHangCauHoiController::class, 'toggleReusable'])->name('toggle-reusable');
            Route::delete('/{id}', [NganHangCauHoiController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('phe-duyet')->name('phe-duyet.')->group(function () {
            Route::get('/', [BaiKiemTraPheDuyetController::class, 'index'])->name('index');
            Route::get('/{id}', [BaiKiemTraPheDuyetController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [BaiKiemTraPheDuyetController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [BaiKiemTraPheDuyetController::class, 'reject'])->name('reject');
            Route::post('/{id}/publish', [BaiKiemTraPheDuyetController::class, 'publish'])->name('publish');
            Route::post('/{id}/close', [BaiKiemTraPheDuyetController::class, 'close'])->name('close');
        });
    });

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
Route::prefix('giang-vien')->name('giang-vien.')->middleware(['auth', 'giang_vien'])->group(function () {
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
    
    // Quản lý Tài nguyên (Phase 4 & 6)
    Route::post('/buoi-hoc/{lichHocId}/tai-nguyen', [TaiNguyenController::class, 'store'])->name('buoi-hoc.tai-nguyen.store');
    Route::put('/tai-nguyen/{id}', [TaiNguyenController::class, 'update'])->name('buoi-hoc.tai-nguyen.update');
    Route::patch('/tai-nguyen/{id}/toggle', [TaiNguyenController::class, 'toggleHienThi'])->name('buoi-hoc.tai-nguyen.toggle');
    Route::delete('/tai-nguyen/{id}', [TaiNguyenController::class, 'destroy'])->name('buoi-hoc.tai-nguyen.destroy');

    // Quản lý Bài giảng (Phase 7)
    Route::get('/bai-giang', [BaiGiangController::class, 'index'])->name('bai-giang.index');

    // Quản lý Thư viện tài nguyên (Phase 10 Upgrade)
    Route::prefix('thu-vien')->name('thu-vien.')->group(function () {
        Route::get('/', [TaiNguyenController::class, 'index'])->name('index');
        Route::get('/create', [TaiNguyenController::class, 'create'])->name('create');
        Route::post('/', [TaiNguyenController::class, 'storeLibrary'])->name('store');
        Route::get('/{id}/edit', [TaiNguyenController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TaiNguyenController::class, 'updateLibrary'])->name('update');
        Route::post('/{id}/gui-duyet', [TaiNguyenController::class, 'guiDuyet'])->name('gui-duyet');
        Route::delete('/{id}', [TaiNguyenController::class, 'destroyLibrary'])->name('destroy');
    });

    // Quản lý Bài giảng (Phase 10 Upgrade)
    Route::prefix('bai-giang')->name('bai-giang.')->group(function () {
        Route::get('/', [BaiGiangController::class, 'index'])->name('index');
        Route::get('/create', [BaiGiangController::class, 'create'])->name('create');
        Route::post('/', [BaiGiangController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [BaiGiangController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BaiGiangController::class, 'update'])->name('update');
        Route::post('/{id}/gui-duyet', [BaiGiangController::class, 'guiDuyet'])->name('gui-duyet');
        Route::delete('/{id}', [BaiGiangController::class, 'destroy'])->name('destroy');
        Route::get('/ajax/get-lich-hoc', [BaiGiangController::class, 'getLichHoc'])->name('get-lich-hoc');
    });

    Route::prefix('live-room')->name('live-room.')->group(function () {
        Route::get('/{id}', [GiangVienLiveRoomController::class, 'show'])->name('show');
        Route::post('/{id}/start', [GiangVienLiveRoomController::class, 'start'])->name('start');
        Route::post('/{id}/join', [GiangVienLiveRoomController::class, 'join'])->name('join');
        Route::post('/{id}/leave', [GiangVienLiveRoomController::class, 'leave'])->name('leave');
        Route::post('/{id}/end', [GiangVienLiveRoomController::class, 'end'])->name('end');
        Route::post('/{id}/recordings', [GiangVienLiveRoomController::class, 'storeRecording'])->name('recordings.store');
        Route::delete('/{id}/recordings/{recordingId}', [GiangVienLiveRoomController::class, 'destroyRecording'])->name('recordings.destroy');
    });

    // Điểm danh (Flow 4 - Phase 1)
    Route::get('/buoi-hoc/{lichHocId}/diem-danh', [App\Http\Controllers\GiangVien\DiemDanhController::class, 'show'])->name('buoi-hoc.diem-danh.show');
    Route::post('/buoi-hoc/{lichHocId}/diem-danh', [App\Http\Controllers\GiangVien\DiemDanhController::class, 'store'])->name('buoi-hoc.diem-danh.store');
    Route::post('/buoi-hoc/{lichHocId}/bao-cao-diem-danh', [App\Http\Controllers\GiangVien\DiemDanhController::class, 'report'])->name('buoi-hoc.diem-danh.report');

    // Bài kiểm tra (Phase 8)
    Route::post('/bai-kiem-tra', [BaiKiemTraController::class, 'store'])->name('bai-kiem-tra.store');
    Route::get('/bai-kiem-tra/{id}/edit', [BaiKiemTraController::class, 'edit'])->name('bai-kiem-tra.edit');
    Route::put('/bai-kiem-tra/{id}', [BaiKiemTraController::class, 'update'])->name('bai-kiem-tra.update');
    Route::post('/bai-kiem-tra/{id}/gui-duyet', [BaiKiemTraController::class, 'submitForApproval'])->name('bai-kiem-tra.submit');
    Route::delete('/bai-kiem-tra/{id}', [BaiKiemTraController::class, 'destroy'])->name('bai-kiem-tra.destroy');
    Route::get('/cham-diem/danh-sach', [BaiKiemTraController::class, 'chamDiemIndex'])->name('cham-diem.index');
    Route::get('/cham-diem/{id}', [BaiKiemTraController::class, 'chamDiemShow'])->name('cham-diem.show');
    Route::post('/cham-diem/{id}', [BaiKiemTraController::class, 'chamDiemStore'])->name('cham-diem.store');
    
    // Giữ route cũ cho backward compatibility nếu cần (nhưng ta sẽ cập nhật các view chính)
    Route::get('/phan-cong', [PhanCongController::class, 'index'])->name('phan-cong.index');
    Route::post('/phan-cong/{id}/xac-nhan', [PhanCongController::class, 'xacNhan'])->name('phan-cong.xac-nhan');

    // Các tính năng khác (Placeholder)
    Route::get('/tao-bai-giang', function () { return "Tính năng Tạo bài giảng đang được phát triển"; })->name('tao-bai-giang');
    Route::redirect('/tao-bai-kiem-tra', '/giang-vien/khoa-hoc')->name('tao-bai-kiem-tra');
    Route::redirect('/cham-diem', '/giang-vien/cham-diem/danh-sach')->name('cham-diem');
});

// =========== HỌC VIÊN ROUTES ===========
Route::prefix('hoc-vien')->name('hoc-vien.')->middleware(['auth', \App\Http\Middleware\CheckHocVien::class])->group(function () {
    Route::get('/dashboard', [HocVienController::class, 'dashboard'])->name('dashboard');
    Route::get('/hoat-dong-tien-do', [HocVienController::class, 'hoatDongVaTienDo'])->name('hoat-dong-tien-do');
    Route::get('/bai-kiem-tra', [HocVienBaiKiemTraController::class, 'index'])->name('bai-kiem-tra');
    Route::get('/bai-kiem-tra/{id}', [HocVienBaiKiemTraController::class, 'show'])->name('bai-kiem-tra.show');
    Route::post('/bai-kiem-tra/{id}/bat-dau', [HocVienBaiKiemTraController::class, 'batDau'])->name('bai-kiem-tra.bat-dau');
    Route::post('/bai-kiem-tra/{id}/nop', [HocVienBaiKiemTraController::class, 'nopBai'])->name('bai-kiem-tra.nop');
    
    Route::get('/khoa-hoc-cua-toi', [HocVienController::class, 'khoaHocCuaToi'])->name('khoa-hoc-cua-toi');
    Route::get('/khoa-hoc-tham-gia', [HocVienController::class, 'khoaHocCoTheThamGia'])->name('khoa-hoc-tham-gia');
    Route::post('/khoa-hoc/{khoaHocId}/xin-tham-gia', [HocVienController::class, 'guiYeuCauThamGia'])->name('khoa-hoc.gui-yeu-cau-tham-gia');
    Route::get('/khoa-hoc/{id}', [HocVienController::class, 'chiTietKhoaHoc'])->name('chi-tiet-khoa-hoc');
    Route::get('/bai-giang/{id}', [HocVienController::class, 'chiTietBaiGiang'])->name('bai-giang.show');
    Route::get('/live-room/{id}', [HocVienLiveRoomController::class, 'show'])->name('live-room.show');
    Route::post('/live-room/{id}/join', [HocVienLiveRoomController::class, 'join'])->name('live-room.join');
    Route::post('/live-room/{id}/leave', [HocVienLiveRoomController::class, 'leave'])->name('live-room.leave');
    
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
