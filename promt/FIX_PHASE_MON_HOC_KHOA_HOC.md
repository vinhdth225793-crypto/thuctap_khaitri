# 🔧 FIX PHASE — Môn học & Khóa học
## Làm xong, test xong → mới sang Phase Module

> Mỗi phase = 1 prompt = 1 chức năng hoàn chỉnh.  
> Copy nguyên prompt → paste vào Gemini → nhận code → paste vào VS Code → chạy thử → sang phase tiếp.

---

---

# ═══════════════════════════════════════════
# PHASE A — FIX MÔN HỌC (MonHoc)
# ═══════════════════════════════════════════

## Gồm 3 lỗi cần sửa + 2 cải thiện nghiệp vụ:
- 🔴 BUG: `generateMaMonHoc()` gọi `$lastMonHoc + 1` thay vì `$lastNumber + 1` → TypeError
- 🟡 Redirect sau `store` về index thay vì show
- 🟡 `indexMonHoc()` dùng `request()` helper thay vì `Request $request` injection
- 🟡 `destroyMonHoc()` xóa cascade thủ công thay vì dùng DB cascade
- 🟡 `toggleStatusMonHoc()` chưa có flash message mô tả trạng thái mới

## ✅ Định nghĩa xong: Tạo môn học → mã tự sinh đúng → redirect sang show → thấy chi tiết môn học.

---

## 🤖 PROMPT PHASE A

```
Bạn là senior Laravel developer. Sửa các lỗi và cải thiện nghiệp vụ trong phần Môn học của dự án Laravel 11.

## FILE CẦN SỬA: app/Models/MonHoc.php

### BUG NGHIÊM TRỌNG — method generateMaMonHoc()
Code hiện tại có lỗi ở dòng này:
    $newNumber = $lastMonHoc + 1;   // ❌ SAI: $lastMonHoc là object Model, không phải số

Code đúng phải là:
    $newNumber = $lastNumber + 1;   // ✅ ĐÚNG: $lastNumber đã được extract ở dòng trên

Hãy sửa method generateMaMonHoc() thành code hoàn chỉnh sau:

```php
public static function generateMaMonHoc($tenMonHoc)
{
    $slug = strtoupper(Str::slug($tenMonHoc, '_'));
    $prefix = substr($slug, 0, 3);

    $lastMonHoc = self::where('ma_mon_hoc', 'LIKE', $prefix . '%')
                      ->orderBy('ma_mon_hoc', 'desc')
                      ->first();

    if ($lastMonHoc) {
        $lastNumber = intval(substr($lastMonHoc->ma_mon_hoc, strlen($prefix)));
        $newNumber = $lastNumber + 1;  // ← SỬA: dùng $lastNumber, không phải $lastMonHoc
    } else {
        $newNumber = 1;
    }

    return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
}
```

---

## FILE CẦN SỬA: app/Http/Controllers/Admin/KhoaHocController.php

Sửa toàn bộ các method liên quan đến MonHoc như sau:

### 1. Sửa indexMonHoc() — dùng Request injection thay vì request() helper

```php
public function indexMonHoc(Request $request)
{
    $search = $request->get('search', '');
    $perPage = 10;

    $monHocs = MonHoc::when($search, fn($q) => $q->search($search))
                     ->paginate($perPage);

    return view('pages.admin.khoa-hoc.mon-hoc.index', compact('monHocs', 'search'));
}
```

### 2. Sửa storeMonHoc() — redirect về show thay vì index

Thay dòng cuối:
```php
// ❌ Cũ:
return redirect()->route('admin.mon-hoc.index')
    ->with('success', 'Thêm môn học thành công với mã: ' . $maMonHoc);

// ✅ Mới: redirect về show để admin kiểm tra ngay
$monHoc = MonHoc::create($data);  // ← lưu kết quả create vào biến
return redirect()->route('admin.mon-hoc.show', $monHoc->id)
    ->with('success', 'Thêm môn học thành công! Mã: ' . $maMonHoc);
```

Lưu ý: hiện tại code gọi `MonHoc::create($data)` nhưng không lưu vào biến → phải sửa thành `$monHoc = MonHoc::create($data)` để có id redirect.

### 3. Sửa destroyMonHoc() — xóa ảnh của các module liên quan trước khi cascade delete

```php
public function destroyMonHoc($id)
{
    $monHoc = MonHoc::with('khoaHocs.moduleHocs')->findOrFail($id);

    // Xóa ảnh của chính môn học
    if ($monHoc->hinh_anh && file_exists(public_path($monHoc->hinh_anh))) {
        unlink(public_path($monHoc->hinh_anh));
    }

    // Xóa ảnh của từng khóa học liên quan
    foreach ($monHoc->khoaHocs as $khoaHoc) {
        if ($khoaHoc->hinh_anh && file_exists(public_path($khoaHoc->hinh_anh))) {
            unlink(public_path($khoaHoc->hinh_anh));
        }
    }

    // Cascade delete sẽ tự xóa khoa_hoc → module_hoc → phan_cong qua DB constraint
    $monHoc->delete();

    return redirect()->route('admin.mon-hoc.index')
        ->with('success', 'Đã xóa môn học "' . $monHoc->ten_mon_hoc . '" và tất cả dữ liệu liên quan.');
}
```

### 4. Sửa toggleStatusMonHoc() — thêm message rõ trạng thái mới

```php
public function toggleStatusMonHoc($id)
{
    $monHoc = MonHoc::findOrFail($id);
    $monHoc->update(['trang_thai' => !$monHoc->trang_thai]);

    $statusText = $monHoc->trang_thai ? 'kích hoạt' : 'tạm dừng';

    return redirect()->back()
        ->with('success', 'Môn học "' . $monHoc->ten_mon_hoc . '" đã được ' . $statusText . '.');
}
```

### 5. XÓA 2 methods dead code không có route nào dùng:

Xóa hoàn toàn 2 methods này khỏi KhoaHocController:
- `indexKhoaHoc()` — dead code, route khoa-hoc dùng KhoaHocManagementController
- `indexModuleHoc()` — dead code, route module-hoc sẽ dùng ModuleHocController mới

---

## YÊU CẦU OUTPUT
Viết lại toàn bộ 2 files hoàn chỉnh (không bỏ sót method nào):
1. `app/Models/MonHoc.php` — đầy đủ, chỉ sửa generateMaMonHoc()
2. `app/Http/Controllers/Admin/KhoaHocController.php` — đầy đủ, bỏ 2 dead methods, sửa 4 methods còn lại

Mỗi file ghi rõ đường dẫn. Code phải chạy được ngay trên Laravel 11.
```

---

## ✅ CHECKLIST SAU PHASE A
Trước khi sang Phase B, test những việc này:
- [ ] Tạo môn học mới → mã tự sinh (VD: PYT001) → redirect về trang show
- [ ] Tạo 2 môn học cùng prefix → mã tăng đúng (PYT001, PYT002)
- [ ] Bật/tắt trạng thái → flash message rõ "đã kích hoạt" / "đã tạm dừng"
- [ ] Xóa môn học → kiểm tra DB: khoa_hoc và module_hoc liên quan đã bị xóa

---

---

# ═══════════════════════════════════════════
# PHASE B — FIX KHÓA HỌC (KhoaHoc)
# ═══════════════════════════════════════════

## Gồm 4 lỗi cần sửa + 3 cải thiện nghiệp vụ:
- 🔴 BUG: `update()` validation có `trang_thai required|boolean` → form không có field này → LUÔN FAIL
- 🔴 BUG: `store()` hardcode `created_by = 1` thay vì dùng user đang đăng nhập
- 🟡 `store()` redirect về index thay vì show
- 🟡 `update()` redirect về index thay vì show
- 🟡 View blade label "Thời lượng (giờ)" nhưng DB lưu phút → không nhất quán
- 🟡 `create()` load `$existingModules` nặng, cần tối ưu
- 🟡 `destroy()` chưa xóa ảnh khi xóa khóa học

## ✅ Định nghĩa xong: Tạo KH → tạo modules cùng lúc → redirect show → Sửa KH → update được → Xóa KH → ảnh bị dọn sạch.

---

## 🤖 PROMPT PHASE B

```
Bạn là senior Laravel developer. Sửa các lỗi và cải thiện nghiệp vụ trong phần Khóa học của dự án Laravel 11.

## CONTEXT
- Auth user: auth()->user() trả về model NguoiDung với primary key là ma_nguoi_dung
- Đơn vị thời lượng đã được thống nhất: lưu PHÚT (integer) trong DB
- View label phải hiển thị đúng "Thời lượng (phút)"

## FILE CẦN SỬA: app/Http/Controllers/Admin/KhoaHocManagementController.php

### BUG 1 — store(): hardcode created_by = 1

Tìm đoạn:
    'created_by' => 1, // Giả sử admin có ID = 1

Sửa thành:
    'created_by' => auth()->user()->ma_nguoi_dung,

### BUG 2 — update(): validation sai gây fail hoàn toàn

Tìm validation rule:
    'trang_thai' => 'required|boolean',

Xóa dòng đó ra khỏi validation. Việc thay đổi trạng thái đã có route riêng (toggle-status), không được để trong form edit.

Sau khi xóa, cũng xóa 'trang_thai' khỏi dòng $request->only():
    // ❌ Cũ:
    $data = $request->only(['mon_hoc_id', 'ten_khoa_hoc', 'mo_ta_ngan', 'mo_ta_chi_tiet', 'cap_do', 'trang_thai']);
    // ✅ Mới:
    $data = $request->only(['mon_hoc_id', 'ten_khoa_hoc', 'mo_ta_ngan', 'mo_ta_chi_tiet', 'cap_do']);

### CẢI THIỆN 1 — store(): redirect về show sau khi tạo

Tìm dòng cuối của DB::commit():
    return redirect()->route('admin.khoa-hoc.index')
        ->with('success', 'Thêm khóa học thành công với mã: ' . $maKhoaHoc);

Sửa thành:
    return redirect()->route('admin.khoa-hoc.show', $khoaHoc->id)
        ->with('success', 'Tạo khóa học thành công! Mã: ' . $maKhoaHoc . ' — ' . count($request->modules) . ' module đã được tạo.');

### CẢI THIỆN 2 — update(): redirect về show sau khi sửa

Tìm:
    return redirect()->route('admin.khoa-hoc.index')
        ->with('success', 'Cập nhật khóa học thành công');

Sửa thành:
    return redirect()->route('admin.khoa-hoc.show', $khoaHoc->id)
        ->with('success', 'Cập nhật khóa học thành công.');

### CẢI THIỆN 3 — create(): tối ưu query $existingModules

$existingModules hiện load cả phanCongGiangViens.giangVien.nguoiDung rất nặng.
Giảm xuống chỉ cần tên module để tham khảo:

    // ❌ Cũ (nặng):
    $existingModules = ModuleHoc::with(['khoaHoc.monHoc', 'phanCongGiangViens.giangVien.nguoiDung'])
                               ->whereHas('khoaHoc', fn($q) => $q->where('trang_thai', true))
                               ->get()
                               ->groupBy('ten_module');

    // ✅ Mới (nhẹ, chỉ lấy tên để tham khảo):
    $existingModules = ModuleHoc::select('ten_module')
                               ->whereHas('khoaHoc', fn($q) => $q->where('trang_thai', true))
                               ->distinct()
                               ->orderBy('ten_module')
                               ->pluck('ten_module');

### CẢI THIỆN 4 — thêm method destroy() còn thiếu

Hiện tại KhoaHocManagementController KHÔNG có method destroy(). Route đã định nghĩa nhưng method không tồn tại.
Thêm method này:

```php
public function destroy($id)
{
    $khoaHoc = KhoaHoc::with('moduleHocs')->findOrFail($id);

    // Kiểm tra không cho xóa nếu có học viên đang theo học (bỏ qua nếu chưa có bảng đăng ký)

    // Xóa ảnh khóa học
    if ($khoaHoc->hinh_anh && file_exists(public_path($khoaHoc->hinh_anh))) {
        unlink(public_path($khoaHoc->hinh_anh));
    }

    // Xóa cascade: module_hoc và phan_cong đã có DB constraint onDelete cascade
    $khoaHoc->delete();

    return redirect()->route('admin.khoa-hoc.index')
        ->with('success', 'Đã xóa khóa học "' . $khoaHoc->ten_khoa_hoc . '".');
}
```

---

## FILE CẦN SỬA: resources/views/pages/admin/khoa-hoc/khoa-hoc/create.blade.php

### Sửa label thời lượng từ "giờ" thành "phút"

Tìm tất cả chỗ có:
    Thời lượng (giờ)
    placeholder="0"
    min="1"

Sửa thành:
    Thời lượng (phút)
    placeholder="60"
    min="1"
    max="600"

Thêm text gợi ý dưới input:
    <small class="text-muted">VD: 90 phút = 1.5 giờ · 120 phút = 2 giờ</small>

---

## FILE CẦN SỬA: resources/views/pages/admin/khoa-hoc/khoa-hoc/edit.blade.php

### Sửa label thời lượng từ "giờ" thành "phút"

Tìm:
    {{ $module->thoi_luong_du_kien }} giờ

Sửa thành (convert phút sang giờ:phút cho dễ đọc):
    @php
        $h = intdiv($module->thoi_luong_du_kien, 60);
        $m = $module->thoi_luong_du_kien % 60;
    @endphp
    {{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}
    <small class="text-muted">({{ $module->thoi_luong_du_kien }} phút)</small>

---

## YÊU CẦU OUTPUT
Viết lại các file sau hoàn chỉnh:
1. `app/Http/Controllers/Admin/KhoaHocManagementController.php` — đầy đủ tất cả methods, đã fix tất cả bugs trên
2. Phần sửa cụ thể trong 2 blade files (ghi rõ tìm gì, thay bằng gì)

Code phải chạy được ngay trên Laravel 11.
```

---

## ✅ CHECKLIST SAU PHASE B
Trước khi sang Phase C, test những việc này:
- [ ] Tạo khóa học + 2 module → redirect về trang show → thấy modules đã tạo
- [ ] Sửa khóa học (chỉ đổi tên, không đổi trạng thái) → update được → redirect về show
- [ ] Tạo 2 khóa học cùng môn học → mã tự tăng (VD: PYT001, PYT002)
- [ ] Xóa khóa học → kiểm tra DB module_hoc đã bị xóa cascade
- [ ] View create: label "phút" đúng, có hint "90 phút = 1.5 giờ"

---

---

# ═══════════════════════════════════════════
# PHASE C — DỌN DẸP & NHẤT QUÁN
# ═══════════════════════════════════════════

## Mục tiêu: Chuẩn bị sạch để Phase Module không bị vướng vào code cũ lộn xộn

---

## 🤖 PROMPT PHASE C

```
Bạn là senior Laravel developer. Dọn dẹp và chuẩn hóa codebase trước khi thêm tính năng mới (Module).

## TÁC VỤ 1 — Xóa dead code trong KhoaHocController

File: app/Http/Controllers/Admin/KhoaHocController.php

Xóa hoàn toàn 2 methods sau (không có route nào dùng):
- indexKhoaHoc() — dead code, đã được thay bằng KhoaHocManagementController
- indexModuleHoc() — dead code, sẽ được thay bằng ModuleHocController mới ở phase sau

Sau khi xóa, cũng xóa các `use` import không còn dùng:
- Nếu ModuleHoc không còn được dùng trong file → xóa `use App\Models\ModuleHoc;`
- Nếu KhoaHoc không còn được dùng → xóa `use App\Models\KhoaHoc;`

Viết lại file hoàn chỉnh sau khi xóa.

---

## TÁC VỤ 2 — Cập nhật routes/web.php phần module-hoc

Tìm đoạn:
    Route::prefix('module-hoc')->name('module-hoc.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\KhoaHocController::class, 'indexModuleHoc'])->name('index');
    });

Thay thành (placeholder để sẵn cho Phase Module):
    // =========== MODULE HOC — sẽ implement đầy đủ ở Phase Module ===========
    Route::prefix('module-hoc')->name('module-hoc.')->group(function () {
        // Routes sẽ được thêm vào trong Phase tiếp theo
        // Hiện tại comment để tránh gọi vào method đã bị xóa
    });

---

## TÁC VỤ 3 — Thêm accessor vào KhoaHoc model

File: app/Models/KhoaHoc.php

Thêm accessor sau vào trong class (sau các scopes):

```php
/**
 * Accessor: lấy số module thực tế từ DB (không phụ thuộc vào tong_so_module)
 * Dùng trong blade: $khoaHoc->so_module_thuc_te
 */
public function getSoModuleThucTeAttribute(): int
{
    return $this->moduleHocs()->count();
}
```

---

## TÁC VỤ 4 — Thêm Log cleanup vào store() KhoaHoc

Trong KhoaHocManagementController::store(), tìm và xóa 2 dòng Log debug đang để lộ thông tin:
    \Log::error('Validation failed in store method:', $validator->errors()->toArray());
    \Log::error('Request data:', $request->all());

Thay bằng 1 dòng log đúng level và không lộ toàn bộ request:
    \Log::warning('KhoaHoc store validation failed', ['errors' => $validator->errors()->keys()]);

---

## TÁC VỤ 5 — Chuẩn hóa view show.blade.php của Môn học

File: resources/views/pages/admin/khoa-hoc/mon-hoc/show.blade.php

Thêm link nhanh sang tạo khóa học cho môn học này (UX improvement):
Trong phần "Danh sách khóa học", thêm nút:
    <a href="{{ route('admin.khoa-hoc.create') }}?mon_hoc_id={{ $monHoc->id }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Thêm khóa học cho môn này
    </a>

Và trong KhoaHocManagementController::create(), đọc mon_hoc_id từ query string để pre-select:
    $preSelectedMonHocId = request()->get('mon_hoc_id');
    return view('...', compact('monHocs', 'giangViens', 'existingModules', 'preSelectedMonHocId'));

Trong create.blade.php, thêm selected vào option tương ứng:
    <option value="{{ $mh->id }}" {{ old('mon_hoc_id', $preSelectedMonHocId) == $mh->id ? 'selected' : '' }}>

---

## YÊU CẦU OUTPUT
Với mỗi tác vụ:
1. Ghi rõ đường dẫn file
2. Viết code hoàn chỉnh (không bỏ sót)
3. Với blade: ghi rõ TÌM đoạn nào, THAY bằng gì

Thứ tự: Tác vụ 1 → 2 → 3 → 4 → 5.
```

---

## ✅ CHECKLIST SAU PHASE C — SẴN SÀNG LÀM MODULE
- [ ] Route module-hoc không còn trỏ vào method đã xóa → không có 500 error khi truy cập
- [ ] KhoaHocController sạch, chỉ còn MonHoc methods
- [ ] `$khoaHoc->so_module_thuc_te` hoạt động trong blade
- [ ] Trang show Môn học có nút "Thêm khóa học" với mon_hoc_id pre-filled
- [ ] Không còn \Log::error lộ toàn bộ request data

---

---

# 📋 PROMPT REVIEW CHUNG (dùng sau khi xong cả 3 phase)

```
Review lại toàn bộ code đã sửa trong 3 phase (MonHoc + KhoaHoc + Cleanup).
Kiểm tra từng điểm và báo cáo:

1. [ ] generateMaMonHoc(): $lastNumber + 1 (không phải $lastMonHoc + 1)?
2. [ ] storeMonHoc(): lưu kết quả create vào biến để redirect show?
3. [ ] update() KhoaHoc: KHÔNG còn 'trang_thai' trong validation rules?
4. [ ] store() KhoaHoc: created_by dùng auth()->user()->ma_nguoi_dung?
5. [ ] destroy() KhoaHoc: method tồn tại và xóa được ảnh?
6. [ ] Tất cả redirect sau store/update về show (không phải index)?
7. [ ] KhoaHocController: đã xóa indexKhoaHoc() và indexModuleHoc()?
8. [ ] View label "Thời lượng" hiển thị "phút" (không phải "giờ")?
9. [ ] getSoModuleThucTeAttribute() đã có trong KhoaHoc model?
10. [ ] Route module-hoc không trỏ vào method đã xóa?

Với mỗi điểm chưa đạt: chỉ ra đúng file + dòng cần sửa.
```

---

---

## 🗺️ TOÀN BỘ LỘ TRÌNH

```
Phase A: Fix MonHoc     → test → ✅
    ↓
Phase B: Fix KhoaHoc    → test → ✅
    ↓
Phase C: Cleanup        → test → ✅
    ↓
Phase 1 (Module): Observer tong_so_module   → ✅
    ↓
Phase 2 (Module): CRUD Module độc lập       → ✅
    ↓
Phase 3 (Module): Phân công Giảng viên      → ✅
    ↓
Phase 4 (Module): Show KhoaHoc tích hợp    → ✅
    ↓
Phase 5 (Module): Dashboard thống kê       → ✅
```

---

*Fix Phase v1.0 — dựa trên review code thực tế từ GitHub — 03/2026*
