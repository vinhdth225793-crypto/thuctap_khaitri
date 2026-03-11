<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckHocVien
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('dang-nhap')->with('error', 'Vui lòng đăng nhập để truy cập.');
        }

        $user = Auth::user();
        
        if (!$user->isHocVien()) {
            return redirect()->route('trang-chu')->with('error', 'Bạn không có quyền truy cập khu vực học viên.');
        }

        return $next($request);
    }
}