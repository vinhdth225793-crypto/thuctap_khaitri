# PROMPT — Sửa chức năng điều hướng trang chủ ↔ trang tài khoản

## Phân tích hiện trạng & danh sách lỗi

| # | Vấn đề | File | Mức độ |
|---|--------|------|--------|
| 1 | `HomeController::index()` redirect thẳng về dashboard khi đã đăng nhập → không xem được trang chủ | `HomeController.php` | 🔴 Nghiêm trọng |
| 2 | Navbar trang chủ dùng sai field: `auth()->user()->avatar` (không tồn tại trong DB) → ảnh broken | `home/index.blade.php` | 🔴 Nghiêm trọng |
| 3 | Navbar trang chủ dùng sai field: `ten_nguoi_dung` / `name` thay vì `ho_ten` → tên sai | `home/index.blade.php` | 🔴 Nghiêm trọng |
| 4 | Navbar trang chủ gọi `route('logout')` không tồn tại (đúng là `dang-xuat`) → lỗi 500 | `home/index.blade.php` | 🔴 Nghiêm trọng |
| 5 | `header.blade.php` chỉ hiện chữ cái đầu, chưa dùng `anh_dai_dien` thực | `components/header.blade.php` | 🟡 Cần cải thiện |
| 6 | `header.blade.php` thiếu nút/link về trang chủ | `components/header.blade.php` | 🟡 Cần cải thiện |
| 7 | `sidebar-hoc-vien.blade.php` thiếu link trang chủ (admin & giang-vien đã có) | `components/sidebar-hoc-vien.blade.php` | 🟡 Cần cải thiện |
| 8 | Sidebar admin/giang-vien dùng `route('home', ['preview'=>1])` — tham số thừa sau khi fix | `components/sidebar-admin.blade.php` | ⚪ Cleanup |

---

> ⚠️ **Quy tắc:** Làm từng phase, xong báo lại. Không sửa ngoài phạm vi phase.

---

## PHASE 1 — Sửa HomeController: bỏ redirect khi đã đăng nhập

### Vấn đề
Hiện tại `HomeController::index()` có đoạn:
```php
if (auth()->check() && !$request->has('preview')) {
    // redirect về dashboard theo vai trò
}
```
→ Ai đăng nhập rồi vào `/` đều bị đẩy về dashboard, không xem được trang chủ.

### Giải pháp
**Xóa hoàn toàn** block `if (auth()->check()...)` đó. Sau khi xóa, method `index()` chỉ còn:

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

> Không cần truyền thêm biến user — view dùng `@auth` / `auth()->user()` trực tiếp.

### ✅ Checklist Phase 1
Đăng nhập admin, truy cập `/`
- Pass: thấy trang chủ bình thường, KHÔNG bị redirect về `/admin/dashboard`

---

## PHASE 2 — Sửa navbar trang chủ: 3 lỗi + cải thiện dropdown

**File:** `resources/views/pages/home/index.blade.php`

Tìm block `@auth ... @else ... @endauth` trong `<ul class="navbar-nav">`.

### Sửa lỗi 1 — Sai field ảnh

```blade
{{-- XÓA dòng img cũ --}}
<img src="{{ auth()->user()->avatar ?? 'https://via.placeholder.com/40' }}" ...>

{{-- THAY BẰNG block sau (thêm @php trước dropdown toggle) --}}
@php
    $navUser    = auth()->user();
    $navAvatar  = $navUser->anh_dai_dien ? asset($navUser->anh_dai_dien) : null;
@endphp
```

Rồi trong button dropdown toggle, thay phần hiển thị ảnh:
```blade
@if($navAvatar)
    <img src="{{ $navAvatar }}"
         alt="{{ $navUser->ho_ten }}"
         style="width:36px; height:36px; border-radius:50%; object-fit:cover;
                border:2px solid rgba(255,255,255,0.6); flex-shrink:0;">
@else
    <div style="width:36px; height:36px; border-radius:50%;
                background:rgba(255,255,255,0.25);
                display:flex; align-items:center; justify-content:center;
                color:white; font-weight:800; font-size:15px;
                border:2px solid rgba(255,255,255,0.5); flex-shrink:0;">
        {{ strtoupper(mb_substr($navUser->ho_ten, 0, 1)) }}
    </div>
@endif
```

### Sửa lỗi 2 — Sai field tên

```blade
{{-- XÓA --}}
{{ auth()->user()->ten_nguoi_dung ?? auth()->user()->name ?? 'User' }}

{{-- THAY BẰNG --}}
{{ $navUser->ho_ten }}
```

### Sửa lỗi 3 — Sai route đăng xuất

```blade
{{-- XÓA 2 dòng lỗi --}}
<a class="dropdown-item" href="{{ route('logout') }}" onclick="...document.getElementById('logout-form').submit();">
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">

{{-- THAY BẰNG --}}
<a class="dropdown-item" href="#"
   onclick="event.preventDefault(); document.getElementById('home-logout-form').submit();"
   style="color:#d32f2f;">
    <i class="fas fa-sign-out-alt"></i> Đăng xuất
</a>
<form id="home-logout-form" action="{{ route('dang-xuat') }}" method="POST" style="display:none;">
    @csrf
</form>
```

### Thêm: block thông tin user ở đầu dropdown

Trong `<ul class="dropdown-menu">` của `@auth`, thêm **ngay đầu** trước `<li>` Dashboard:

```blade
{{-- Thêm vào đầu dropdown --}}
<li>
    <div style="padding:12px 16px; display:flex; align-items:center; gap:10px;
                background:linear-gradient(135deg,rgba(102,126,234,.08),rgba(102,126,234,.03));
                border-bottom:1px solid #f1f5f9;">
        @if($navAvatar)
            <img src="{{ $navAvatar }}"
                 style="width:38px; height:38px; border-radius:50%; object-fit:cover;
                        border:2px solid #c7d2fe;">
        @else
            <div style="width:38px; height:38px; border-radius:50%; background:#667eea;
                        display:flex; align-items:center; justify-content:center;
                        color:white; font-weight:800; font-size:15px;">
                {{ strtoupper(mb_substr($navUser->ho_ten, 0, 1)) }}
            </div>
        @endif
        <div>
            <div style="font-weight:700; color:#1e293b; font-size:13px; line-height:1.2;">
                {{ $navUser->ho_ten }}
            </div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">
                @if($navUser->vai_tro === 'admin') Quản trị viên
                @elseif($navUser->vai_tro === 'giang_vien') Giảng viên
                @else Học viên
                @endif
                &middot; {{ $navUser->email }}
            </div>
        </div>
    </div>
</li>
<li><hr class="dropdown-divider" style="margin:4px 0;"></li>
```

### Sửa link Dashboard trong dropdown thành "Vào Dashboard"

```blade
{{-- Tìm li Dashboard cũ, sửa href --}}
<a class="dropdown-item" style="color:#1d4ed8; font-weight:600;"
   href="@if($navUser->vai_tro === 'admin'){{ route('admin.dashboard') }}
         @elseif($navUser->vai_tro === 'giang_vien'){{ route('giang-vien.dashboard') }}
         @else{{ route('hoc-vien.dashboard') }}@endif">
    <i class="fas fa-tachometer-alt" style="color:#2563eb;"></i>
    Vào Dashboard
</a>
```

### ✅ Checklist Phase 2
Đăng nhập, vào trang chủ `/`
- Pass: thấy ảnh đại diện (hoặc chữ cái đầu nếu chưa upload)
- Pass: tên hiển thị đúng `ho_ten`
- Pass: click "Đăng xuất" → logout thành công, không lỗi 500
- Pass: dropdown hiển thị email và vai trò
- Pass: click "Vào Dashboard" → đúng trang theo vai trò

---

## PHASE 3 — Sửa `header.blade.php`: ảnh thực + nút trang chủ

**File:** `resources/views/components/header.blade.php`

### 3.1 — Thêm nút về trang chủ vào header-right

Trong `<div class="header-right">`, thêm nút **trước** icon chuông thông báo:

```blade
{{-- Thêm TRƯỚC div.dropdown chuông --}}
<a href="{{ route('home') }}"
   class="btn btn-sm d-flex align-items-center gap-2"
   style="background:#eff6ff; color:#2563eb; border:1.5px solid #bfdbfe;
          border-radius:8px; padding:6px 14px; font-weight:700; font-size:12px;
          text-decoration:none; white-space:nowrap;"
   title="Xem trang chủ"
   onmouseover="this.style.background='#dbeafe'"
   onmouseout="this.style.background='#eff6ff'">
    <i class="fas fa-home"></i>
    <span class="d-none d-md-inline">Trang chủ</span>
</a>
```

### 3.2 — Sửa user-avatar: hiển thị ảnh thực

Tìm:
```blade
<div class="user-avatar">
    {{ strtoupper(substr(auth()->user()->ho_ten, 0, 1)) }}
</div>
```

Thay bằng:
```blade
<div class="user-avatar" style="overflow:hidden; padding:0;">
    @if(auth()->user()->anh_dai_dien)
        <img src="{{ asset(auth()->user()->anh_dai_dien) }}"
             alt="{{ auth()->user()->ho_ten }}"
             style="width:100%; height:100%; object-fit:cover; display:block;">
    @else
        <span style="display:flex; align-items:center; justify-content:center;
                     width:100%; height:100%; font-weight:700; font-size:15px;">
            {{ strtoupper(mb_substr(auth()->user()->ho_ten, 0, 1)) }}
        </span>
    @endif
</div>
```

### ✅ Checklist Phase 3
Vào bất kỳ trang dashboard nào:
- Pass: header-right có nút "🏠 Trang chủ" (icon trên mobile, text trên desktop)
- Pass: avatar hiển thị ảnh thực nếu có `anh_dai_dien`; fallback chữ nếu chưa có
- Pass: click nút "Trang chủ" → vào `/` thấy trang chủ (không redirect nữa)

---

## PHASE 4 — Sửa Sidebars: đồng bộ link trang chủ

### 4.1 — Sidebar Học viên: THÊM link trang chủ

**File:** `resources/views/components/sidebar-hoc-vien.blade.php`

Tìm `<div class="nav-item mt-4">` (phần Hồ sơ cá nhân). Thêm **trước** nó:

```blade
{{-- Thêm TRƯỚC <div class="nav-item mt-4"> --}}
<div class="nav-item">
    <a href="{{ route('home') }}" class="nav-link">
        <i class="fas fa-home"></i>
        <span>Trang chủ</span>
    </a>
</div>
```

### 4.2 — Sidebar Admin: xóa ?preview=1 thừa

**File:** `resources/views/components/sidebar-admin.blade.php`

Tìm:
```blade
<a href="{{ route('home', ['preview' => 1]) }}" class="nav-link">
```
Sửa thành:
```blade
<a href="{{ route('home') }}" class="nav-link">
```

### 4.3 — Sidebar Giảng viên: kiểm tra và thêm nếu thiếu

**File:** `resources/views/components/sidebar-giang-vien.blade.php`

Tìm xem đã có link `route('home')` chưa. Nếu chưa có, thêm vào trước `<div class="nav-item mt-4">` Hồ sơ cá nhân:

```blade
<div class="nav-item">
    <a href="{{ route('home') }}" class="nav-link">
        <i class="fas fa-home"></i>
        <span>Trang chủ</span>
    </a>
</div>
```

### ✅ Checklist Phase 4
- Pass: sidebar cả 3 vai trò đều có link "Trang chủ"
- Pass: click link → vào `/`, thấy trang chủ bình thường

---

## PHASE 5 — Test toàn bộ flow

### Flow A: Guest
1. Vào `/` → thấy trang chủ, navbar có nút "Đăng nhập" + "Đăng ký"

### Flow B: Admin
1. Đăng nhập → vào `/admin/dashboard`
2. Click "Trang chủ" ở sidebar → `/` hiển thị trang chủ ✅
3. Click "Trang chủ" ở header → tương tự ✅
4. Trang chủ navbar: avatar đúng, tên đúng, badge "Quản trị viên"
5. Dropdown: thấy email, nút "Vào Dashboard" → về `/admin/dashboard` ✅
6. Nút "Đăng xuất" → logout, không lỗi 500 ✅

### Flow C: Học viên
1. Đăng nhập → vào `/hoc-vien/dashboard`
2. Sidebar có link "Trang chủ" (mới thêm) ✅
3. Click → trang chủ hiển thị đúng thông tin học viên ✅

### Flow D: Giảng viên
1. Tương tự Flow C ✅

### ✅ PASS ALL — Checklist tổng

| Tiêu chí | Kết quả |
|----------|---------|
| Guest xem được trang chủ | ✅ |
| Đã đăng nhập vẫn xem được trang chủ (không redirect) | ✅ |
| Navbar trang chủ hiển thị đúng ảnh `anh_dai_dien` | ✅ |
| Navbar trang chủ hiển thị đúng `ho_ten` | ✅ |
| Đăng xuất từ trang chủ không lỗi 500 | ✅ |
| Header trang tài khoản có nút "Trang chủ" | ✅ |
| Header trang tài khoản hiện ảnh thực hoặc fallback chữ | ✅ |
| Sidebar 3 vai trò đều có link Trang chủ | ✅ |

---

## TÓM TẮT FILE CẦN SỬA

| File | Thay đổi chính |
|------|---------------|
| `app/Http/Controllers/HomeController.php` | Xóa block redirect khi đã đăng nhập |
| `resources/views/pages/home/index.blade.php` | Sửa 3 lỗi field + cải thiện dropdown user |
| `resources/views/components/header.blade.php` | Thêm nút Trang chủ + sửa avatar hiển thị ảnh thực |
| `resources/views/components/sidebar-hoc-vien.blade.php` | Thêm link Trang chủ |
| `resources/views/components/sidebar-admin.blade.php` | Xóa `?preview=1` trong link trang chủ |
| `resources/views/components/sidebar-giang-vien.blade.php` | Thêm/cập nhật link Trang chủ |
