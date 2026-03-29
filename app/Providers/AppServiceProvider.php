<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\TaiKhoanChoPheDuyet;
use App\Models\ModuleHoc;
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
            $pendingAccountsCount = TaiKhoanChoPheDuyet::where('trang_thai', 'cho_phe_duyet')->count();
            $view->with('pendingAccountsCount', $pendingAccountsCount);
        });
    }
}
