<?php

// app/Http/Middleware/CheckGiangVien.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckGiangVien
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !(auth()->user()->isGiangVien() || auth()->user()->isAdmin())) {
            abort(403, 'Bạn không có quyền truy cập trang này');
        }
        return $next($request);
    }
}