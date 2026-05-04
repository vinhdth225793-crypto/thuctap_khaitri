<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdmin::class,
            'giang_vien' => \App\Http\Middleware\CheckGiangVien::class,
            'hoc_vien' => \App\Http\Middleware\CheckHocVien::class,
        ]);

        // Guest bị chặn -> chuyển về trang đăng nhập tiếng Việt thay vì route('login') mặc định
        $middleware->redirectGuestsTo(fn () => route('dang-nhap'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Bất cứ chỗ nào gọi route('login') (vd: package thứ 3, code legacy) sẽ
        // không crash mà trả về trang chủ.
        $exceptions->render(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e, $request) {
            if (str_contains($e->getMessage(), 'Route [login]')) {
                return $request->expectsJson()
                    ? response()->json(['message' => 'Yêu cầu đăng nhập.'], 401)
                    : redirect()->route('home');
            }
            // Các route name khác chưa định nghĩa -> giữ behavior gốc (trả 500 cho dev biết)
        });
    })->create();
