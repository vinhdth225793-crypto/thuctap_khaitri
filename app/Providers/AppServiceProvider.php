<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\BaiGiang;
use App\Models\BaiKiemTra;
use App\Models\GiangVienDonXinNghi;
use App\Models\TaiKhoanChoPheDuyet;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\ThongBao;
use App\Models\ModuleHoc;
use App\Models\YeuCauHocVien;
use App\Observers\ModuleHocObserver;

use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Phase 1 - Register ModuleHocObserver
        ModuleHoc::observe(ModuleHocObserver::class);

        // Share số lượng tài khoản chờ phê duyệt tới tất cả views
        View::composer(['components.sidebar-admin', 'layouts.app'], function ($view) {
            $pendingApprovalCounts = [
                'tai_khoan' => 0,
                'tai_nguyen' => 0,
                'bai_giang' => 0,
                'de_thi' => 0,
                'don_nghi' => 0,
                'yeu_cau_hoc_vien' => 0,
            ];

            if (auth()->check() && auth()->user()->isAdmin()) {
                $pendingApprovalCounts = [
                    'tai_khoan' => TaiKhoanChoPheDuyet::query()
                        ->where('trang_thai', 'cho_phe_duyet')
                        ->count(),
                    'tai_nguyen' => TaiNguyenBuoiHoc::query()
                        ->where('trang_thai_duyet', TaiNguyenBuoiHoc::STATUS_DUYET_CHO)
                        ->count(),
                    'bai_giang' => BaiGiang::query()
                        ->where('trang_thai_duyet', BaiGiang::STATUS_DUYET_CHO)
                        ->count(),
                    'de_thi' => BaiKiemTra::query()
                        ->where('trang_thai_duyet', 'cho_duyet')
                        ->count(),
                    'don_nghi' => GiangVienDonXinNghi::query()
                        ->where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)
                        ->count(),
                    'yeu_cau_hoc_vien' => YeuCauHocVien::query()
                        ->where('trang_thai', 'cho_duyet')
                        ->count(),
                ];
            }

            $view->with([
                'pendingAccountsCount' => $pendingApprovalCounts['tai_khoan'],
                'pendingApprovalCounts' => $pendingApprovalCounts,
                'pendingApprovalTotal' => array_sum($pendingApprovalCounts),
            ]);
        });

        View::composer('components.header', function ($view) {
            if (!auth()->check()) {
                $view->with([
                    'headerNotificationCount' => 0,
                    'headerRecentNotifications' => collect(),
                ]);

                return;
            }

            $userId = auth()->id();

            $view->with([
                'headerNotificationCount' => ThongBao::query()
                    ->where('nguoi_nhan_id', $userId)
                    ->where('da_doc', 0)
                    ->count(),
                'headerRecentNotifications' => ThongBao::query()
                    ->where('nguoi_nhan_id', $userId)
                    ->latest('created_at')
                    ->take(5)
                    ->get(),
            ]);
        });
    }
}
