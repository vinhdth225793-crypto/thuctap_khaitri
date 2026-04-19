<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\BaiGiang;
use App\Models\BaiKiemTra;
use App\Models\GiangVienDonXinNghi;
use App\Models\TaiKhoanChoPheDuyet;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\ThongBao;
use App\Models\ModuleHoc;
use App\Models\YeuCauHocVien;
use App\Models\PhieuXetDuyetKetQua;
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
                'xet_duyet_ket_qua' => 0,
                'don_nghi' => 0,
                'yeu_cau_hoc_vien' => 0,
            ];

            if (auth()->check() && auth()->user()->isAdmin()) {
                $pendingApprovalCounts = [
                    'tai_khoan' => $this->countWhereIfTableReady(
                        TaiKhoanChoPheDuyet::class,
                        'tai_khoan_cho_phe_duyet',
                        'trang_thai',
                        'cho_phe_duyet'
                    ),
                    'tai_nguyen' => $this->countWhereIfTableReady(
                        TaiNguyenBuoiHoc::class,
                        'tai_nguyen_buoi_hoc',
                        'trang_thai_duyet',
                        TaiNguyenBuoiHoc::STATUS_DUYET_CHO
                    ),
                    'bai_giang' => $this->countWhereIfTableReady(
                        BaiGiang::class,
                        'bai_giangs',
                        'trang_thai_duyet',
                        BaiGiang::STATUS_DUYET_CHO
                    ),
                    'de_thi' => $this->countWhereIfTableReady(
                        BaiKiemTra::class,
                        'bai_kiem_tra',
                        'trang_thai_duyet',
                        'cho_duyet'
                    ),
                    'xet_duyet_ket_qua' => $this->countWhereIfTableReady(
                        PhieuXetDuyetKetQua::class,
                        'phieu_xet_duyet_ket_qua',
                        'trang_thai',
                        [
                            PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
                            PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
                        ]
                    ),
                    'don_nghi' => $this->countWhereIfTableReady(
                        GiangVienDonXinNghi::class,
                        'giang_vien_don_xin_nghi',
                        'trang_thai',
                        GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET
                    ),
                    'yeu_cau_hoc_vien' => $this->countWhereIfTableReady(
                        YeuCauHocVien::class,
                        'yeu_cau_hoc_vien',
                        'trang_thai',
                        'cho_duyet'
                    ),
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
            if (! Schema::hasTable('thong_bao') || ! Schema::hasColumn('thong_bao', 'nguoi_nhan_id')) {
                $view->with([
                    'headerNotificationCount' => 0,
                    'headerRecentNotifications' => collect(),
                ]);

                return;
            }

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

    private function countWhereIfTableReady(string $modelClass, string $table, string $column, mixed $value): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        $query = $modelClass::query();

        if (is_array($value)) {
            return $query->whereIn($column, $value)->count();
        }

        return $query->where($column, $value)->count();
    }
}
