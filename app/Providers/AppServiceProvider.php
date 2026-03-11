<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\TaiKhoanChoPheDuyet;

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
        // Share số lượng tài khoản chờ phê duyệt tới tất cả views
        View::composer(['components.sidebar-admin', 'layouts.app'], function ($view) {
            $pendingAccountsCount = TaiKhoanChoPheDuyet::where('trang_thai', 'cho_phe_duyet')->count();
            $view->with('pendingAccountsCount', $pendingAccountsCount);
        });
    }
}
