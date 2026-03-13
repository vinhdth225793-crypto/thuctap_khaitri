# PROMPT — Sửa lỗi trang chủ bị render trong khung admin + thêm nút quay lại dashboard

## Root Cause (đọc kỹ trước khi làm)

```
home/index.blade.php   →   @extends('layouts.app')
                                    ↓
layouts.app khi @auth  →   bọc sidebar + header vào MỌI trang
                                    ↓
Kết quả: trang chủ hiển thị bên trong khung admin (sidebar + home content đồng thời)
```

**Cách sửa đúng:** Tạo layout riêng `layouts.home.blade.php` (không có sidebar),
rồi cho `home/index.blade.php` extends layout mới này thay vì `layouts.app`.

---

> ⚠️ Quy tắc: Làm từng phase, xong báo lại. Không sửa ngoài phạm vi phase.

---

## PHASE 1 — Tạo layout riêng cho trang chủ

**Tạo file mới:** `resources/views/layouts/home.blade.php`

```blade
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trang chủ') — {{ config('app.name', 'Khải Trí') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: #fff; color: #333; }
    </style>

    @stack('styles')
</head>
<body>

{{-- Nội dung trang chủ (navbar, hero, sections, footer đều nằm trong @yield('content')) --}}
@yield('content')

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- AOS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>

@stack('scripts')
</body>
</html>
```

> **Lưu ý quan trọng:** Layout này KHÔNG có `@auth ... sidebar ... @endauth`.
> `layouts.app` vẫn giữ nguyên cho các trang dashboard/admin — KHÔNG sửa file đó.

### ✅ Checklist Phase 1
File tồn tại tại `resources/views/layouts/home.blade.php`

---

## PHASE 2 — Đổi home/index.blade.php sang dùng layout mới

**File:** `resources/views/pages/home/index.blade.php`

### 2.1 — Đổi @extends (dòng đầu tiên)

```blade
{{-- XÓA dòng cũ --}}
@extends('layouts.app', ['title' => 'Trang chủ'])

{{-- THAY BẰNG --}}
@extends('layouts.home')
@section('title', 'Trang chủ')
```

> Dòng title cũ `['title' => 'Trang chủ']` bây giờ tách ra thành `@section('title', ...)`.

### 2.2 — Sửa 3 lỗi trong navbar @auth (phần dropdown user)

Tìm block `@auth` trong thẻ `<nav>` của trang chủ. Sửa 3 điểm:

**Lỗi A — Field ảnh sai (`avatar` không tồn tại trong DB):**
```blade
{{-- XÓA --}}
<img src="{{ auth()->user()->avatar ?? 'https://via.placeholder.com/40' }}" ...>

{{-- THAY BẰNG: thêm @php trước button dropdown-toggle --}}
@php
    $homeUser   = auth()->user();
    $homeAvatar = $homeUser->anh_dai_dien ? asset($homeUser->anh_dai_dien) : null;
@endphp

{{-- Rồi hiển thị avatar: --}}
@if($homeAvatar)
    <img src="{{ $homeAvatar }}"
         alt="{{ $homeUser->ho_ten }}"
         style="width:36px; height:36px; border-radius:50%; object-fit:cover;
                border:2px solid rgba(255,255,255,0.6); flex-shrink:0;">
@else
    <div style="width:36px; height:36px; border-radius:50%;
                background:rgba(255,255,255,0.25); color:white; font-weight:800;
                font-size:15px; display:flex; align-items:center; justify-content:center;
                border:2px solid rgba(255,255,255,0.5); flex-shrink:0;">
        {{ strtoupper(mb_substr($homeUser->ho_ten, 0, 1)) }}
    </div>
@endif
```

**Lỗi B — Field tên sai:**
```blade
{{-- XÓA --}}
{{ auth()->user()->ten_nguoi_dung ?? auth()->user()->name ?? 'User' }}

{{-- THAY BẰNG --}}
{{ $homeUser->ho_ten }}
```

**Lỗi C — Route đăng xuất sai (gây lỗi 500):**
```blade
{{-- XÓA dòng route('logout') --}}
<a ... href="{{ route('logout') }}" onclick="...document.getElementById('logout-form').submit();">
<form id="logout-form" action="{{ route('logout') }}" ...>

{{-- THAY BẰNG --}}
<a class="dropdown-item" href="#" style="color:#d32f2f;"
   onclick="event.preventDefault(); document.getElementById('home-logout-form').submit();">
    <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
</a>
<form id="home-logout-form" action="{{ route('dang-xuat') }}" method="POST" style="display:none;">
    @csrf
</form>
```

### 2.3 — Sửa link href trang chủ trong navbar (bỏ ?preview=1)

```blade
{{-- XÓA --}}
href="{{ route('home', ['preview' => 1]) }}"

{{-- THAY BẰNG --}}
href="{{ route('home') }}"
```
> Sửa tất cả chỗ nào dùng `['preview' => 1]` trong file này (khoảng 2-3 chỗ).

### ✅ Checklist Phase 2
1. Đăng nhập admin, vào `/`
   - **Pass:** Trang chủ hiển thị đúng, KHÔNG có sidebar admin bên trái
   - **Pass:** Có navbar của trang chủ (gradient tím)
2. Click avatar trên navbar trang chủ:
   - **Pass:** Dropdown mở, thấy ảnh thực hoặc chữ cái đầu
   - **Pass:** Tên hiển thị đúng `ho_ten`
3. Click "Đăng xuất" trong dropdown:
   - **Pass:** Đăng xuất thành công, không lỗi 500

---

## PHASE 3 — Thêm banner "Quay lại Dashboard" theo vai trò

Khi đã đăng nhập, hiển thị một banner nhỏ sticky phía trên cùng trang chủ để quay lại nhanh.

**File:** `resources/views/pages/home/index.blade.php`

Thêm đoạn code sau vào **ngay sau thẻ mở `@section('content')`**, trước `<nav>`:

```blade
{{-- ===== BANNER QUAY LẠI DASHBOARD (chỉ hiện khi đã đăng nhập) ===== --}}
@auth
@php
    $authUser  = auth()->user();
    $dashRoute = match($authUser->vai_tro) {
        'admin'      => route('admin.dashboard'),
        'giang_vien' => route('giang-vien.dashboard'),
        default      => route('hoc-vien.dashboard'),
    };

    // Label nút theo vai trò
    $dashLabel = match($authUser->vai_tro) {
        'admin'      => '⚙️ Quay lại Dashboard quản trị',
        'giang_vien' => '📚 Hoạt động dạy của ' . $authUser->ho_ten,
        default      => '🎓 Hoạt động học của ' . $authUser->ho_ten,
    };

    // Màu sắc theo vai trò
    $dashColor = match($authUser->vai_tro) {
        'admin'      => '#dc2626',   // đỏ
        'giang_vien' => '#2563eb',   // xanh dương
        default      => '#16a34a',   // xanh lá
    };

    $homeAvatar2 = $authUser->anh_dai_dien ? asset($authUser->anh_dai_dien) : null;
@endphp

<div style="background: {{ $dashColor }}; color:white; padding:8px 20px;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; z-index:2000;
            box-shadow:0 2px 10px rgba(0,0,0,.2); gap:12px; flex-wrap:wrap;">

    {{-- Thông tin user --}}
    <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
        @if($homeAvatar2)
            <img src="{{ $homeAvatar2 }}" alt="{{ $authUser->ho_ten }}"
                 style="width:30px; height:30px; border-radius:50%; object-fit:cover;
                        border:2px solid rgba(255,255,255,0.7);">
        @else
            <div style="width:30px; height:30px; border-radius:50%;
                        background:rgba(255,255,255,0.25); color:white; font-weight:800;
                        font-size:13px; display:flex; align-items:center; justify-content:center;
                        border:2px solid rgba(255,255,255,0.5);">
                {{ strtoupper(mb_substr($authUser->ho_ten, 0, 1)) }}
            </div>
        @endif
        <span style="font-size:13px; font-weight:600; opacity:.9;">
            Xin chào, <strong>{{ $authUser->ho_ten }}</strong>
        </span>
    </div>

    {{-- Nút quay lại dashboard --}}
    <a href="{{ $dashRoute }}"
       style="background:white; color:{{ $dashColor }}; border:none; border-radius:20px;
              padding:5px 18px; font-size:13px; font-weight:800; text-decoration:none;
              display:flex; align-items:center; gap:6px; white-space:nowrap;
              transition:all .2s; box-shadow:0 2px 8px rgba(0,0,0,.15);"
       onmouseover="this.style.transform='scale(1.04)'"
       onmouseout="this.style.transform='scale(1)'">
        {{ $dashLabel }}
    </a>

</div>
@endauth
{{-- ===== END BANNER ===== --}}
```

> **Giải thích `match()`:** Cú pháp này yêu cầu PHP 8.0+. Nếu server dùng PHP 7.x thì thay bằng:
> ```php
> if ($authUser->vai_tro === 'admin') {
>     $dashRoute = route('admin.dashboard');
>     $dashLabel = '⚙️ Quay lại Dashboard quản trị';
>     $dashColor = '#dc2626';
> } elseif ($authUser->vai_tro === 'giang_vien') {
>     $dashRoute = route('giang-vien.dashboard');
>     $dashLabel = '📚 Hoạt động dạy của ' . $authUser->ho_ten;
>     $dashColor = '#2563eb';
> } else {
>     $dashRoute = route('hoc-vien.dashboard');
>     $dashLabel = '🎓 Hoạt động học của ' . $authUser->ho_ten;
>     $dashColor = '#16a34a';
> }
> ```

### ✅ Checklist Phase 3

| Tài khoản | Banner hiển thị | Màu | Click dẫn đến |
|-----------|----------------|-----|---------------|
| Admin | "⚙️ Quay lại Dashboard quản trị" | 🔴 Đỏ | `/admin/dashboard` |
| Giảng viên | "📚 Hoạt động dạy của [tên GV]" | 🔵 Xanh dương | `/giang-vien/dashboard` |
| Học viên | "🎓 Hoạt động học của [tên HV]" | 🟢 Xanh lá | `/hoc-vien/dashboard` |
| Guest | Không hiển thị banner | — | — |

---

## PHASE 4 — Cập nhật sidebar: bỏ ?preview=1

Vì trang chủ không redirect nữa, tham số `?preview=1` không còn cần thiết.

### Sidebar Admin: `resources/views/components/sidebar-admin.blade.php`
```blade
{{-- TÌM --}}
<a href="{{ route('home', ['preview' => 1]) }}" class="nav-link">

{{-- SỬA THÀNH --}}
<a href="{{ route('home') }}" class="nav-link">
```

### Sidebar Học viên: `resources/views/components/sidebar-hoc-vien.blade.php`
Tìm xem đã có link trang chủ chưa. Nếu chưa, thêm trước `<div class="nav-item mt-4">` Hồ sơ cá nhân:
```blade
<div class="nav-item">
    <a href="{{ route('home') }}" class="nav-link">
        <i class="fas fa-home"></i>
        <span>Trang chủ</span>
    </a>
</div>
```

### Sidebar Giảng viên: `resources/views/components/sidebar-giang-vien.blade.php`
Tương tự — tìm và thêm nếu thiếu.

### ✅ Checklist Phase 4
- Pass: sidebar 3 vai trò có link "Trang chủ"
- Pass: click → vào `/`, thấy trang chủ đúng (không có sidebar)

---

## PHASE 5 — Sửa HomeController (bỏ redirect khi đã đăng nhập)

**File:** `app/Http/Controllers/HomeController.php`

**Xóa toàn bộ** đoạn redirect này trong method `index()`:
```php
// XÓA ĐOẠN NÀY
if (auth()->check() && !$request->has('preview')) {
    $user = auth()->user();
    if (!isset($user->vai_tro)) { abort(403, ...); }
    if ($user->vai_tro === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->vai_tro === 'giang_vien') {
        return redirect()->route('giang-vien.dashboard');
    } else {
        return redirect()->route('hoc-vien.dashboard');
    }
}
```

Sau khi xóa, method `index()` chỉ còn phần load data và trả về view:
```php
public function index(Request $request)
{
    $giangVienFeatured = GiangVien::hienThiTrangChu()
        ->with('nguoiDung')
        ->paginate(6);

    $settings = [
        'site_name'  => SystemSetting::get('site_name', 'Trung tâm Khải Trí'),
        'site_logo'  => SystemSetting::get('site_logo', ''),
        'hotline'    => SystemSetting::get('hotline', ''),
        'zalo'       => SystemSetting::get('zalo', ''),
        'facebook'   => SystemSetting::get('facebook', ''),
        'email'      => SystemSetting::get('email', ''),
    ];

    return view('pages.home.index', [
        'giangVienFeatured' => $giangVienFeatured,
        'settings'          => $settings,
    ]);
}
```

### ✅ Checklist Phase 5
- Pass: đăng nhập admin → vào `/` → thấy trang chủ, không bị đẩy về dashboard

---

## TỔNG KẾT

### Kết quả sau khi hoàn tất

| Hành động | Kết quả mong đợi |
|-----------|-----------------|
| Guest vào `/` | Trang chủ bình thường, navbar có nút Đăng nhập |
| Admin vào `/` | Trang chủ đúng (NO sidebar), banner đỏ "⚙️ Quay lại Dashboard quản trị" |
| Admin click banner | Về `/admin/dashboard` |
| Giảng viên vào `/` | Banner xanh "📚 Hoạt động dạy của [Tên GV]" |
| Học viên vào `/` | Banner xanh lá "🎓 Hoạt động học của [Tên HV]" |
| Click "Trang chủ" ở sidebar | Vào `/`, hiển thị đúng không bị bọc sidebar |

### File cần tạo/sửa

| Thao tác | File |
|----------|------|
| **TẠO MỚI** | `resources/views/layouts/home.blade.php` |
| **Sửa dòng 1** | `resources/views/pages/home/index.blade.php` — đổi extends + sửa 3 lỗi field + thêm banner |
| **Sửa** | `app/Http/Controllers/HomeController.php` — xóa block redirect |
| **Sửa** | `resources/views/components/sidebar-admin.blade.php` — xóa `?preview=1` |
| **Sửa** | `resources/views/components/sidebar-hoc-vien.blade.php` — thêm link trang chủ |
| **Sửa** | `resources/views/components/sidebar-giang-vien.blade.php` — thêm link trang chủ nếu thiếu |
