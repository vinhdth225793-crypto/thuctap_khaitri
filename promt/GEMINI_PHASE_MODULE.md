# 🧩 GEMINI AGENT — PHASE MODULE
## Thứ tự bắt buộc: Phase 1 → 2A → 2B → 3A → 3B → 4 → 5
## Prerequisite: Đã hoàn thành Phase A + B + C (MonHoc & KhoaHoc fix)

---

# ════════════════════════════════════════════════
# PHASE 1 — OBSERVER: TỰ ĐỒNG BỘ tong_so_module
# Mục tiêu: tong_so_module luôn đúng dù thêm/xóa module bằng bất kỳ cách nào
# ════════════════════════════════════════════════

```
Bạn là senior Laravel developer. Implement Observer pattern cho ModuleHoc.

## CONTEXT
- Bảng khoa_hoc có cột tong_so_module (integer) lưu số module của khóa học
- Hiện tại tong_so_module chỉ được set 1 lần khi tạo KhoaHoc → sẽ sai khi thêm/xóa module riêng lẻ
- Giải pháp: dùng Eloquent Observer để tự đồng bộ sau mỗi thao tác

---

## FILE 1 — TẠO MỚI: app/Observers/ModuleHocObserver.php

```php
<?php

namespace App\Observers;

use App\Models\ModuleHoc;

class ModuleHocObserver
{
    /**
     * Sau khi tạo module mới → cập nhật tong_so_module của khóa học
     */
    public function created(ModuleHoc $moduleHoc): void
    {
        $this->syncTongSoModule($moduleHoc->khoa_hoc_id);
    }

    /**
     * Sau khi xóa module → cập nhật tong_so_module của khóa học
     */
    public function deleted(ModuleHoc $moduleHoc): void
    {
        $this->syncTongSoModule($moduleHoc->khoa_hoc_id);
    }

    /**
     * Đồng bộ tong_so_module = số module thực tế trong DB
     */
    private function syncTongSoModule(int $khoaHocId): void
    {
        $soModule = ModuleHoc::where('khoa_hoc_id', $khoaHocId)->count();

        \App\Models\KhoaHoc::where('id', $khoaHocId)
            ->update(['tong_so_module' => $soModule]);
    }
}
```

---

## FILE 2 — SỬA: app/Providers/AppServiceProvider.php

Trong method boot(), thêm:

```php
use App\Models\ModuleHoc;
use App\Observers\ModuleHocObserver;

public function boot(): void
{
    ModuleHoc::observe(ModuleHocObserver::class);
}
```

---

## FILE 3 — SỬA: app/Models/KhoaHoc.php

Thêm accessor sau vào class (sau các scopes):

```php
/**
 * Accessor: lấy số module thực tế (không phụ thuộc vào tong_so_module cached)
 * Dùng trong blade: $khoaHoc->so_module_thuc_te
 */
public function getSoModuleThucTeAttribute(): int
{
    return $this->module_hocs_count ?? $this->moduleHocs()->count();
}
```

---

## YÊU CẦU OUTPUT
1. Toàn bộ file app/Observers/ModuleHocObserver.php
2. Method boot() đã sửa của AppServiceProvider.php (chỉ cần phần boot)
3. Accessor thêm vào KhoaHoc.php (chỉ cần method)

## CHECKLIST SAU PHASE 1
- [ ] Tạo khóa học với 3 module → tong_so_module = 3
- [ ] Xóa 1 module → tong_so_module tự cập nhật = 2
- [ ] Thêm module mới → tong_so_module tự cập nhật = 3
```

---

# ════════════════════════════════════════════════
# PHASE 2A — CONTROLLER & ROUTES: CRUD MODULE
# Mục tiêu: Module có đầy đủ CRUD độc lập
# ════════════════════════════════════════════════

```
Bạn là senior Laravel developer. Tạo controller và routes cho Module học.

## CONTEXT — CẤU TRÚC DỮ LIỆU
Bảng module_hoc:
- id, khoa_hoc_id (FK → khoa_hoc), ma_module (UNIQUE), ten_module
- mo_ta, thu_tu_module (số thứ tự trong KH), thoi_luong_du_kien (phút, integer)
- trang_thai (boolean), created_at, updated_at
- UNIQUE constraint: (khoa_hoc_id, thu_tu_module)

Model relationships đã có:
- ModuleHoc belongsTo KhoaHoc
- KhoaHoc hasMany ModuleHoc (orderBy thu_tu_module)
- ModuleHoc hasMany PhanCongModuleGiangVien

Quy tắc sinh mã module: {ma_khoa_hoc}M{thu_tu:02d}
Ví dụ: KhoaHoc mã "PYT001" → module 1 = "PYT001M01", module 2 = "PYT001M02"

Auth: auth()->user() trả về NguoiDung với PK là ma_nguoi_dung

---

## FILE CẦN TẠO: app/Http/Controllers/Admin/ModuleHocController.php

Tạo controller với đầy đủ 8 methods sau:

### index(Request $request)
- Filter theo khoa_hoc_id (nếu có) và search (tên/mã module)
- Eager load: khoaHoc.monHoc để tránh N+1
- Paginate 10, giữ filter khi chuyển trang (->appends($request->query()))
- Pass thêm $khoaHocs = KhoaHoc::with('monHoc')->active()->get() để render dropdown filter

### create(Request $request)
- Nhận query param khoa_hoc_id (pre-select từ trang show KhoaHoc)
- Load $khoaHocs = KhoaHoc::with('monHoc')->active()->orderBy('ma_khoa_hoc')->get()
- Tính sẵn thu_tu_module gợi ý: nếu có khoa_hoc_id thì = count modules hiện tại + 1

### store(Request $request)
Validation:
- khoa_hoc_id: required|exists:khoa_hoc,id
- ten_module: required|string|max:200
- mo_ta: nullable|string
- thu_tu_module: required|integer|min:1 + unique theo khoa_hoc_id (exclude current on update)
- thoi_luong_du_kien: nullable|integer|min:1|max:600 (phút)
- trang_thai: nullable|boolean

Validation messages: tiếng Việt đầy đủ

Logic trong DB::transaction:
1. Lấy KhoaHoc để có ma_khoa_hoc
2. Sinh ma_module = $khoaHoc->ma_khoa_hoc . 'M' . str_pad($thu_tu_module, 2, '0', STR_PAD_LEFT)
3. Kiểm tra ma_module chưa tồn tại (phòng race condition)
4. ModuleHoc::create([...])
5. Commit → redirect về show module vừa tạo

Validation unique scoped:
Rule::unique('module_hoc')->where('khoa_hoc_id', $request->khoa_hoc_id)

### show($id)
Load: khoaHoc.monHoc, phanCongGiangViens.giangVien.nguoiDung

### edit($id)
Load: khoaHoc.monHoc
Pass $khoaHocs để có thể đổi khóa học (nếu cần)

### update(Request $request, $id)
Validation tương tự store nhưng:
- unique scoped exclude current: Rule::unique('module_hoc')->where('khoa_hoc_id', $request->khoa_hoc_id)->ignore($id)
- ma_khoa_hoc: readonly, không cho update (chỉ update tên, mô tả, thứ tự, thời lượng, trạng thái)
- Nếu thu_tu_module thay đổi → sinh lại ma_module
- Redirect về show

### destroy($id)
- Không cho xóa nếu có phan_cong đang da_nhan hoặc cho_xac_nhan
  (check: $moduleHoc->phanCongGiangViens()->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan'])->exists())
- Nếu có → redirect back với error message
- Nếu không → delete (cascade xóa phan_cong) → redirect về khoa_hoc show

### toggleStatus($id)
- Toggle trang_thai
- Redirect back với message rõ trạng thái mới

---

## FILE CẦN SỬA: routes/web.php

Tìm đoạn:
    Route::prefix('module-hoc')->name('module-hoc.')->group(function () {
        Route::get('/', ...)->name('index'); // hoặc comment placeholder
    });

Thay thành:
    Route::prefix('module-hoc')->name('module-hoc.')->group(function () {
        Route::get('/',              [ModuleHocController::class, 'index'])->name('index');
        Route::get('/create',        [ModuleHocController::class, 'create'])->name('create');
        Route::post('/',             [ModuleHocController::class, 'store'])->name('store');
        Route::get('/{id}',          [ModuleHocController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [ModuleHocController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [ModuleHocController::class, 'update'])->name('update');
        Route::delete('/{id}',       [ModuleHocController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [ModuleHocController::class, 'toggleStatus'])->name('toggle-status');
    });

Thêm use ở đầu hoặc dùng full class path:
    use App\Http\Controllers\Admin\ModuleHocController;

---

## YÊU CẦU OUTPUT
1. Toàn bộ file app/Http/Controllers/Admin/ModuleHocController.php
2. Đoạn routes cần thay thế (ghi rõ tìm gì, thay bằng gì)

Code phải chạy được ngay trên Laravel 11.
```

---

# ════════════════════════════════════════════════
# PHASE 2B — 4 VIEWS: CRUD MODULE
# Mục tiêu: Giao diện đầy đủ cho Module
# ════════════════════════════════════════════════

```
Bạn là senior Laravel developer. Tạo 4 Blade views cho Module học.

## CONTEXT
- Layout: @extends('layouts.app'), dùng class Bootstrap 5 + class vip-card, vip-card-header, vip-card-body, vip-card-title, vip-form-control (đang dùng nhất quán trong project)
- Icon: Font Awesome 5
- Flash message: session('success'), session('error')
- Breadcrumb pattern: Admin > Quản lý khóa học > Module học > [tên trang]
- Route names: admin.module-hoc.index, .create, .store, .show, .edit, .update, .destroy, .toggle-status

---

## VIEW 1: resources/views/pages/admin/khoa-hoc/module-hoc/index.blade.php

Nội dung:
- Breadcrumb + tiêu đề "Danh sách Module học" + nút "Thêm module mới" (→ create)
- Form filter: dropdown chọn KhoaHoc (có --Tất cả khóa học--) + input search + nút Lọc + nút Reset
- Bảng với các cột: STT | Mã module | Tên module | Khóa học (khoaHoc.ten_khoa_hoc) | Môn học (khoaHoc.monHoc.ten_mon_hoc) | Thứ tự | Thời lượng (hiển thị Xh Yp) | Trạng thái (badge) | Hành động
- Cột Hành động: nút Xem (info), Sửa (warning), Toggle (secondary), Xóa (danger + confirm JS)
- Phân trang: {{ $moduleHocs->appends(request()->query())->links() }}
- Thông báo empty state nếu không có module

---

## VIEW 2: resources/views/pages/admin/khoa-hoc/module-hoc/create.blade.php

Nội dung:
- Breadcrumb + tiêu đề "Thêm module mới"
- Form POST → admin.module-hoc.store, enctype không cần (không có file)
- Field khoa_hoc_id: select dropdown (group by Môn học), có selected từ old() hoặc query param
  ```
  <select name="khoa_hoc_id" id="khoa_hoc_id">
      <option value="">-- Chọn khóa học --</option>
      @foreach($khoaHocs as $kh)
          <option value="{{ $kh->id }}" {{ old('khoa_hoc_id', request('khoa_hoc_id')) == $kh->id ? 'selected' : '' }}>
              [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }} — {{ $kh->monHoc->ten_mon_hoc ?? '' }}
          </option>
      @endforeach
  </select>
  ```
- Field ten_module: text, required, old()
- Field thu_tu_module: number min=1, value="{{ old('thu_tu_module', $thuTuGoiY ?? '') }}", hint "Thứ tự hiển thị trong khóa học (1, 2, 3...)"
- Field thoi_luong_du_kien: number min=1 max=600, placeholder="90", hint "Đơn vị: phút. VD: 90 = 1 giờ 30 phút"
- Field mo_ta: textarea rows=4, old()
- Field trang_thai: checkbox switch, mặc định checked (active)
- Mã module: readonly text hiển thị "Tự sinh sau khi chọn KH và nhập thứ tự"
- Hiển thị lỗi validation @error cho từng field
- Nút Hủy (→ index) + Nút Lưu

---

## VIEW 3: resources/views/pages/admin/khoa-hoc/module-hoc/edit.blade.php

Nội dung:
- Breadcrumb + tiêu đề "Chỉnh sửa: {{ $moduleHoc->ten_module }}"
- Form PUT → admin.module-hoc.update, @csrf @method('PUT')
- ma_module: readonly (không cho sửa)
- khoa_hoc_id: readonly display (không cho chuyển KH)
  Hiển thị: [{{ $moduleHoc->khoaHoc->ma_khoa_hoc }}] {{ $moduleHoc->khoaHoc->ten_khoa_hoc }}
  Kèm hidden input để submit
- ten_module, mo_ta, thoi_luong_du_kien: editable với old()
- thu_tu_module: editable, hint về thứ tự
- trang_thai: checkbox switch với old() + $moduleHoc->trang_thai
- Hiển thị lỗi @error
- Nút Hủy (→ show) + Nút Lưu

---

## VIEW 4: resources/views/pages/admin/khoa-hoc/module-hoc/show.blade.php

Nội dung:
- Breadcrumb + tiêu đề "Chi tiết module: {{ $moduleHoc->ten_module }}"
- Layout 2 cột (col-md-8 + col-md-4):

  Cột trái — thông tin module:
  - Card: Mã module, Tên module, Thứ tự, Thời lượng (hiển thị Xh Yp), Mô tả, Trạng thái, Ngày tạo
  - Nút actions: Sửa | Toggle status | Xóa | Back về KhoaHoc show

  Cột phải — card Khóa học:
  - Tên KH, Mã KH, Môn học, Cấp độ (badge), Trạng thái KH
  - Link → admin.khoa-hoc.show

  Section dưới (full width) — Giảng viên phụ trách:
  - Placeholder cho Phase 3:
    ```
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Chức năng phân công giảng viên sẽ được thêm vào ở phase tiếp theo.
    </div>
    ```

---

## YÊU CẦU OUTPUT
4 files blade hoàn chỉnh, theo đúng thứ tự trên.
Ghi rõ đường dẫn từng file.
Dùng đúng class Bootstrap 5. Flash message ở đầu mỗi view.
```

---

# ════════════════════════════════════════════════
# PHASE 3A — PHÂN CÔNG GIẢNG VIÊN (Admin side)
# Mục tiêu: Admin gán giảng viên cho module, hủy phân công
# ════════════════════════════════════════════════

```
Bạn là senior Laravel developer. Implement tính năng phân công giảng viên dạy module.

## CONTEXT
Bảng phan_cong_module_giang_vien:
- id, khoa_hoc_id, module_hoc_id, giao_vien_id, ngay_phan_cong, trang_thai (ENUM: cho_xac_nhan/da_nhan/tu_choi), ghi_chu, created_by
- UNIQUE(module_hoc_id, giao_vien_id) — cùng GV không được assign 2 lần cùng module

Nghiệp vụ phân công:
1. Admin chọn GV → tạo bản ghi trang_thai = 'cho_xac_nhan'
2. Nếu GV đó đã có bản ghi cũ cho module này → cập nhật lại (reactivate)
3. Khi admin phân công GV mới → các GV cũ đang 'cho_xac_nhan' của module đó không bị ảnh hưởng
4. Admin có thể hủy phân công → set trang_thai = 'tu_choi'

---

## FILE CẦN TẠO: app/Http/Controllers/Admin/PhanCongController.php

```php
namespace App\Http\Controllers\Admin;

// Methods cần implement:

// 1. assign(Request $request, $moduleId)
// POST: nhận giao_vien_id + ghi_chu (optional)
// Validation: giao_vien_id required|exists:giang_vien,id
// Logic: updateOrCreate(['module_hoc_id'=>$moduleId, 'giao_vien_id'=>$giaoVienId], [...])
// Set: khoa_hoc_id (lấy từ module), ngay_phan_cong=now(), trang_thai='cho_xac_nhan', created_by=auth user
// Redirect: back() với success

// 2. huy(Request $request, $phanCongId)
// POST: tìm PhanCong by id
// Chỉ cho hủy nếu trang_thai là 'cho_xac_nhan' (không hủy 'da_nhan' vì GV đã nhận)
// Set trang_thai = 'tu_choi'
// Redirect: back() với success/error
```

---

## FILE CẦN SỬA: routes/web.php

Trong admin group, thêm 2 routes mới (đặt gần module-hoc):

```php
// Phân công giảng viên
Route::post('/module-hoc/{moduleId}/assign',
    [PhanCongController::class, 'assign'])->name('phan-cong.assign');
Route::post('/phan-cong/{id}/huy',
    [PhanCongController::class, 'huy'])->name('phan-cong.huy');
```

---

## FILE CẦN SỬA: resources/views/pages/admin/khoa-hoc/module-hoc/show.blade.php

Thay thế placeholder "Phân công GV sẽ thêm sau" bằng section thật:

Section Giảng viên phụ trách:
- Bảng danh sách tất cả phân công của module (load từ phanCongGiangViens)
  Cột: Giảng viên | Học vị | Ngày phân công | Trạng thái (badge màu) | Ghi chú | Hành động (Hủy nếu cho_xac_nhan)
  Badge màu: cho_xac_nhan=warning, da_nhan=success, tu_choi=danger

- Form phân công GV mới (đặt trước bảng):
  ```
  <form action="{{ route('admin.phan-cong.assign', $moduleHoc->id) }}" method="POST">
      @csrf
      <div class="row g-2 align-items-end">
          <div class="col-md-5">
              <label>Chọn giảng viên</label>
              <select name="giao_vien_id" class="form-select vip-form-control" required>
                  <option value="">-- Chọn giảng viên --</option>
                  @foreach($giangViens as $gv)
                      <option value="{{ $gv->id }}">
                          {{ $gv->nguoiDung->ho_ten ?? 'N/A' }}
                          @if($gv->hoc_vi) ({{ $gv->hoc_vi }}) @endif
                      </option>
                  @endforeach
              </select>
          </div>
          <div class="col-md-5">
              <label>Ghi chú (tùy chọn)</label>
              <input type="text" name="ghi_chu" class="form-control vip-form-control" placeholder="Ghi chú cho lần phân công này">
          </div>
          <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-user-plus"></i> Phân công
              </button>
          </div>
      </div>
  </form>
  ```

Cần cập nhật controller show() để pass thêm $giangViens:
```php
public function show($id)
{
    $moduleHoc = ModuleHoc::with([
        'khoaHoc.monHoc',
        'phanCongGiangViens.giangVien.nguoiDung'
    ])->findOrFail($id);

    $giangViens = \App\Models\GiangVien::with('nguoiDung')
        ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', true))
        ->get();

    return view('pages.admin.khoa-hoc.module-hoc.show', compact('moduleHoc', 'giangViens'));
}
```

---

## YÊU CẦU OUTPUT
1. Toàn bộ file app/Http/Controllers/Admin/PhanCongController.php
2. Đoạn routes cần thêm
3. Method show() đã sửa trong ModuleHocController
4. Phần view show.blade.php cần thay (ghi rõ tìm gì, thay bằng gì)

Code phải chạy được trên Laravel 11.
```

---

# ════════════════════════════════════════════════
# PHASE 3B — PHÂN CÔNG (Giảng viên side)
# Mục tiêu: GV xem + xác nhận/từ chối phân công của mình
# ════════════════════════════════════════════════

```
Bạn là senior Laravel developer. Tạo tính năng xác nhận phân công cho Giảng viên.

## CONTEXT
- Auth GV: auth()->user() là NguoiDung, từ đó lấy giangVien qua relationship
- Route prefix: /giang-vien, middleware: auth + CheckGiangVien
- GV chỉ thấy phân công của chính mình

---

## FILE CẦN SỬA: app/Http/Controllers/GiangVienController.php

Thêm 3 methods:

### phanCong()
Load tất cả phân công của GV này, chia 3 nhóm:
```php
$giangVien = auth()->user()->giangVien;

$phanCongChoXacNhan = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.monHoc'])
    ->where('giao_vien_id', $giangVien->id)
    ->where('trang_thai', 'cho_xac_nhan')
    ->latest('ngay_phan_cong')
    ->get();

$phanCongDaNhan = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.monHoc'])
    ->where('giao_vien_id', $giangVien->id)
    ->where('trang_thai', 'da_nhan')
    ->latest('ngay_phan_cong')
    ->get();

$lichSu = PhanCongModuleGiangVien::with(['moduleHoc.khoaHoc.monHoc'])
    ->where('giao_vien_id', $giangVien->id)
    ->where('trang_thai', 'tu_choi')
    ->latest()
    ->get();

return view('pages.giang-vien.phan-cong', compact('phanCongChoXacNhan', 'phanCongDaNhan', 'lichSu'));
```

### xacNhanPhanCong($id)
```php
$phanCong = PhanCongModuleGiangVien::where('id', $id)
    ->where('giao_vien_id', auth()->user()->giangVien->id)
    ->where('trang_thai', 'cho_xac_nhan')
    ->firstOrFail();

$phanCong->update(['trang_thai' => 'da_nhan']);

return redirect()->back()->with('success', 'Đã xác nhận nhận dạy module: ' . $phanCong->moduleHoc->ten_module);
```

### tuChoiPhanCong($id)
```php
$phanCong = PhanCongModuleGiangVien::where('id', $id)
    ->where('giao_vien_id', auth()->user()->giangVien->id)
    ->where('trang_thai', 'cho_xac_nhan')
    ->firstOrFail();

$phanCong->update(['trang_thai' => 'tu_choi']);

return redirect()->back()->with('success', 'Đã từ chối phân công module: ' . $phanCong->moduleHoc->ten_module);
```

---

## FILE CẦN SỬA: routes/web.php (giang-vien group)

Thêm vào trong giang-vien route group:
```php
Route::get('/phan-cong',
    [GiangVienController::class, 'phanCong'])->name('phan-cong');
Route::post('/phan-cong/{id}/xac-nhan',
    [GiangVienController::class, 'xacNhanPhanCong'])->name('phan-cong.xac-nhan');
Route::post('/phan-cong/{id}/tu-choi',
    [GiangVienController::class, 'tuChoiPhanCong'])->name('phan-cong.tu-choi');
```

---

## FILE CẦN TẠO: resources/views/pages/giang-vien/phan-cong.blade.php

Layout Bootstrap 5 với 3 tab:

Tab 1 — "Chờ xác nhận" (badge đỏ với số lượng):
- Mỗi item: card hiển thị tên module, tên KH, môn học, thời lượng (convert phút→Xh Yp), ngày phân công, ghi chú
- 2 nút: [✓ Xác nhận nhận dạy] (btn-success) + [✗ Từ chối] (btn-outline-danger)
- Mỗi nút là form POST riêng với @csrf

Tab 2 — "Đang dạy" (badge xanh):
- Danh sách module đã xác nhận
- Hiển thị thông tin tương tự, không có nút action

Tab 3 — "Lịch sử" (badge xám):
- Module đã từ chối
- Hiển thị lý do nếu có (ghi_chu)

JS: Bootstrap tab navigation, nhớ tab đang mở khi reload (dùng URL hash hoặc localStorage)

---

## YÊU CẦU OUTPUT
1. 3 methods thêm vào GiangVienController.php
2. Routes cần thêm vào giang-vien group
3. Toàn bộ file resources/views/pages/giang-vien/phan-cong.blade.php
```

---

# ════════════════════════════════════════════════
# PHASE 4 — SHOW KHOAHOC TÍCH HỢP MODULE + GV
# Mục tiêu: Trang show KhoaHoc là trung tâm quản lý
# ════════════════════════════════════════════════

```
Bạn là senior Laravel developer. Nâng cấp trang show Khóa học thành trang quản lý tích hợp.

## FILE CẦN SỬA: app/Http/Controllers/Admin/KhoaHocManagementController.php

Sửa method show():
```php
public function show($id)
{
    $khoaHoc = KhoaHoc::with([
        'monHoc',
        'moduleHocs.phanCongGiangViens.giangVien.nguoiDung'
    ])->findOrFail($id);

    $giangViens = \App\Models\GiangVien::with('nguoiDung')
        ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', true))
        ->get();

    return view('pages.admin.khoa-hoc.khoa-hoc.show', compact('khoaHoc', 'giangViens'));
}
```

---

## FILE CẦN VIẾT LẠI: resources/views/pages/admin/khoa-hoc/khoa-hoc/show.blade.php

Viết lại toàn bộ view với layout sau:

### Section 1 — Header
- Breadcrumb: Admin > Môn học > {monHoc.ten_mon_hoc} > {ten_khoa_hoc}
- Tiêu đề + badge cap_do (cơ bản/trung bình/nâng cao) + badge trạng thái
- Nút actions: [Sửa] [Toggle status] [Xóa] [Quay lại]

### Section 2 — Thông tin (2 cột)
- Cột trái (col-md-8): mô tả ngắn, mô tả chi tiết, ngày tạo/cập nhật
- Cột phải (col-md-4): ảnh khóa học, card thông tin: mã KH, môn học, số module

### Section 3 — Danh sách Module (bảng đầy đủ)
Header card: "Modules ({{ $khoaHoc->moduleHocs->count() }})" + nút "Thêm module" → create?khoa_hoc_id

Bảng module:
| STT | Mã | Tên module | Thời lượng | Giảng viên phụ trách | Trạng thái | Hành động |

Cột Giảng viên phụ trách:
```blade
@php $pc = $module->phanCongGiangViens->sortByDesc('updated_at')->first(); @endphp
@if($pc && $pc->trang_thai !== 'tu_choi')
    <span class="badge bg-{{ $pc->trang_thai === 'da_nhan' ? 'success' : 'warning' }}">
        {{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}
    </span>
    <small class="d-block text-muted">{{ $pc->trang_thai === 'da_nhan' ? 'Đã nhận' : 'Chờ xác nhận' }}</small>
@else
    <span class="text-danger small"><i class="fas fa-exclamation-circle"></i> Chưa có GV</span>
@endif
```

Cột Hành động:
- Nút nhỏ: Xem | Sửa | Phân công GV (trigger modal)
- Row highlight table-warning nếu chưa có GV đang active

### Modal phân công GV (dùng chung cho tất cả module)
```html
<div class="modal fade" id="modalPhanCong">
    <input type="hidden" id="phanCong-moduleId">
    <p id="phanCong-moduleName" class="fw-bold"></p>
    <select name="giao_vien_id">...</select>
    <input type="text" name="ghi_chu">
    <!-- Form action set dynamically bằng JS -->
</div>
```

JS: khi click nút "Phân công" trên row module → set moduleId vào modal → set form action → show modal

```javascript
document.querySelectorAll('.btn-phan-cong').forEach(btn => {
    btn.addEventListener('click', function() {
        const moduleId = this.dataset.moduleId;
        const moduleName = this.dataset.moduleName;
        document.getElementById('phanCong-moduleName').textContent = moduleName;
        document.getElementById('modalPhanCongForm').action =
            '/admin/module-hoc/' + moduleId + '/assign';
        new bootstrap.Modal(document.getElementById('modalPhanCong')).show();
    });
});
```

---

## YÊU CẦU OUTPUT
1. Method show() đã sửa
2. Toàn bộ file resources/views/pages/admin/khoa-hoc/khoa-hoc/show.blade.php

Đảm bảo không còn lỗi N+1 (eager loading đã đủ từ controller).
```

---

# ════════════════════════════════════════════════
# PHASE 5 — DASHBOARD THỐNG KÊ
# Mục tiêu: Admin thấy tổng quan toàn hệ thống
# ════════════════════════════════════════════════

```
Bạn là senior Laravel developer. Thêm thống kê khóa học vào dashboard admin.

## FILE CẦN SỬA: app/Http/Controllers/Admin/AdminController.php

Trong method dashboard() (hoặc index()), thêm các queries sau:

```php
// Thống kê tổng quan
$stats = [
    'tong_mon_hoc'        => \App\Models\MonHoc::count(),
    'mon_hoc_hoat_dong'   => \App\Models\MonHoc::active()->count(),
    'tong_khoa_hoc'       => \App\Models\KhoaHoc::count(),
    'khoa_hoc_hoat_dong'  => \App\Models\KhoaHoc::active()->count(),
    'tong_module'         => \App\Models\ModuleHoc::count(),
    'module_chua_co_gv'   => \App\Models\ModuleHoc::whereDoesntHave('phanCongGiangViens', function($q) {
                                $q->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan']);
                             })->count(),
    'phan_cong_cho_xn'    => \App\Models\PhanCongModuleGiangVien::where('trang_thai', 'cho_xac_nhan')->count(),
];

// Dữ liệu cho 2 bảng nhỏ
$phanCongMoiNhat = \App\Models\PhanCongModuleGiangVien::with([
        'moduleHoc.khoaHoc',
        'giangVien.nguoiDung'
    ])
    ->where('trang_thai', 'cho_xac_nhan')
    ->latest('ngay_phan_cong')
    ->take(5)
    ->get();

$moduleChuaCoGv = \App\Models\ModuleHoc::with(['khoaHoc.monHoc'])
    ->whereDoesntHave('phanCongGiangViens', function($q) {
        $q->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan']);
    })
    ->where('trang_thai', true)
    ->take(5)
    ->get();

// Pass tất cả sang view
return view('pages.admin.dashboard', compact('stats', 'phanCongMoiNhat', 'moduleChuaCoGv'));
```

---

## FILE CẦN SỬA: resources/views/pages/admin/dashboard.blade.php

Thêm 2 section vào dashboard:

### Section 1 — 4 stat cards (đặt sau các stat cards hiện có)

```html
<div class="row mb-4">
    <!-- Card 1: Môn học -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Môn học</p>
                        <h3 class="mb-0 fw-bold">{{ $stats['tong_mon_hoc'] }}</h3>
                        <small class="text-success">{{ $stats['mon_hoc_hoat_dong'] }} đang hoạt động</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-book fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Card 2: Khóa học (xanh lá) -->
    <!-- Card 3: Module chưa có GV (vàng cam - cần chú ý) -->
    <!-- Card 4: Phân công chờ xác nhận (đỏ - cần hành động) -->
</div>
```

Tạo 4 cards tương tự: Môn học (xanh), Khóa học (xanh lá), Module chưa GV (vàng), Phân công chờ XN (đỏ).

### Section 2 — 2 bảng nhỏ (đặt cạnh nhau)

```html
<div class="row">
    <!-- Bảng trái: Phân công chờ xác nhận -->
    <div class="col-md-6">
        <div class="vip-card">
            <div class="vip-card-header">
                <h6 class="vip-card-title">
                    <i class="fas fa-clock text-warning"></i>
                    Phân công chờ xác nhận ({{ $stats['phan_cong_cho_xn'] }})
                </h6>
            </div>
            <div class="vip-card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Module</th><th>Giảng viên</th><th>Ngày PC</th></tr></thead>
                    <tbody>
                        @forelse($phanCongMoiNhat as $pc)
                        <tr>
                            <td>
                                <a href="{{ route('admin.module-hoc.show', $pc->moduleHoc->id) }}">
                                    {{ $pc->moduleHoc->ten_module ?? 'N/A' }}
                                </a>
                                <small class="d-block text-muted">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc ?? '' }}</small>
                            </td>
                            <td>{{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}</td>
                            <td><small>{{ $pc->ngay_phan_cong?->format('d/m/Y') }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Không có phân công chờ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bảng phải: Module chưa có GV -->
    <div class="col-md-6">
        <div class="vip-card">
            <div class="vip-card-header">
                <h6 class="vip-card-title">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    Module chưa có giảng viên ({{ $stats['module_chua_co_gv'] }})
                </h6>
            </div>
            <!-- Bảng tương tự -->
        </div>
    </div>
</div>
```

---

## YÊU CẦU OUTPUT
1. Đoạn code thêm vào method dashboard() của AdminController
2. 2 section HTML cần thêm vào dashboard.blade.php (ghi rõ vị trí đặt)
```

---

## 🗺️ TOÀN BỘ LỘ TRÌNH

```
✅ Phase A — Fix MonHoc (model + controller + view)
✅ Phase B — Fix KhoaHoc (controller + views)
✅ Phase C — Cleanup (dead code, routes, accessor)
            ↓
⬜ Phase 1  — Observer tong_so_module (tự đồng bộ)
            ↓
⬜ Phase 2A — ModuleHocController (8 methods) + Routes
            ↓
⬜ Phase 2B — 4 Views Module (index, create, edit, show)
            ↓
⬜ Phase 3A — PhanCongController (admin gán GV)
            ↓
⬜ Phase 3B — GiangVienController (GV xác nhận/từ chối)
            ↓
⬜ Phase 4  — KhoaHoc show tích hợp module + phân công
            ↓
⬜ Phase 5  — Dashboard thống kê admin
```

---

*thuctap_khaitri — Module Phase Plan v1.0 — 03/2026*
