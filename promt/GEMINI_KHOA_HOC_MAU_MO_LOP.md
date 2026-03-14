# 🎯 GEMINI AGENT — KHÓA HỌC MẪU & MỞ LỚP
**Dự án:** thuctap_khaitri · Laravel 11 · Bootstrap 5 · Blade · MySQL  
**URL:** http://127.0.0.1/thuctap_khaitri/public/admin/khoa-hoc  
**Quy tắc vàng:** Làm xong 1 phase → liệt kê file đã tạo/sửa → dừng chờ xác nhận → mới làm phase tiếp theo.

---

## 📦 CONTEXT DỰ ÁN HIỆN TẠI

```
TABLE khoa_hoc (đang có)
  id, mon_hoc_id FK→mon_hoc.id (cascade)
  ma_khoa_hoc VARCHAR(50) UNIQUE
  ten_khoa_hoc VARCHAR(200)
  mo_ta_ngan VARCHAR(500) nullable
  mo_ta_chi_tiet TEXT nullable
  hinh_anh VARCHAR(255) nullable
  cap_do ENUM('co_ban','trung_binh','nang_cao') default 'co_ban'
  tong_so_module INT default 0
  trang_thai BOOLEAN default 1
  timestamps

TABLE module_hoc (đang có)
  id, khoa_hoc_id FK cascade, ma_module UNIQUE
  ten_module, mo_ta, thu_tu_module INT
  thoi_luong_du_kien INT nullable (phút), trang_thai BOOLEAN, timestamps

TABLE phan_cong_module_giang_vien (đang có)
  id, khoa_hoc_id, module_hoc_id, giao_vien_id
  ngay_phan_cong DATETIME
  trang_thai ENUM('cho_xac_nhan','da_nhan','tu_choi') default 'da_nhan'
  ghi_chu TEXT nullable
  created_by FK→nguoi_dung.ma_nguoi_dung · timestamps

Models hiện có: KhoaHoc, MonHoc, ModuleHoc, GiangVien, NguoiDung
Auth: NguoiDung · PK = ma_nguoi_dung · vai_tro = admin/giang_vien/hoc_vien
Layout: @extends('layouts.app')
CSS classes hiện dùng: vip-card, vip-card-header, vip-card-body, vip-form-control
Icons: Font Awesome 5
Route prefix: admin · Middleware: auth, check.role:admin
```

---

## 🏢 NGHIỆP VỤ — ĐỌC KỸ TRƯỚC KHI CODE

### Khái niệm 2 loại khóa học

**📋 KHÓA HỌC MẪU** (`loai = 'mau'`)
- Template chuẩn bị sẵn: đầy đủ module theo chương trình đào tạo
- Chưa có giảng viên, chưa có ngày học, chưa có học viên
- Dùng để "nhân bản" thành lớp học thật khi đủ điều kiện khai giảng
- **Không bao giờ bị xóa hoặc thay đổi khi mở lớp** — mãi ở đó để mở lớp tiếp
- Hiển thị ở tab "Khóa học mẫu" trên trang index

**⚡ KHÓA HỌC ĐANG HOẠT ĐỘNG** (`loai = 'hoat_dong'`)
- Được tạo ra khi admin bấm "Mở lớp" từ một khóa học mẫu
- Có đầy đủ: module (copy từ mẫu), giảng viên, 3 mốc ngày
- Hiển thị ở tab "Đang hoạt động" trên trang index
- Mã khóa học tự sinh: lấy prefix của mẫu + số thứ tự lần mở (VD: PHP-K01, PHP-K02)

### 3 mốc ngày của khóa học hoạt động (QUAN TRỌNG)

| Trường | Ý nghĩa | Bắt buộc |
|--------|---------|----------|
| `ngay_khai_giang` | Ngày tổ chức **buổi lễ khai giảng** (chào mừng, giới thiệu) | Có |
| `ngay_mo_lop` | Ngày học viên **bắt đầu vào học thật** (ngày đầu tiên có bài giảng) | Có |
| `ngay_ket_thuc` | Ngày kết thúc toàn bộ khóa học | Có |

> 💡 Ví dụ thực tế: Lễ khai giảng 01/05 → Học viên bắt đầu học 05/05 → Kết thúc 31/07

### Mã khóa học — quy tắc sinh mã

**Khóa học mẫu:** Admin tự nhập mã (VD: `PHP-MAU`, `JAVA-MAU`)  
**Khóa học hoạt động khi mở lớp:** Hệ thống tự sinh = `{ma_kh_mau}-K{so_thu_tu_2_chu_so}`

Ví dụ: Mẫu `PHP-MAU` đã mở 2 lần → lần này sinh `PHP-MAU-K03`  
Đếm bằng cách: `SELECT COUNT(*) FROM khoa_hoc WHERE khoa_hoc_mau_id = {id_mau}`

### Trạng thái vận hành (`trang_thai_van_hanh`)

```
cho_mo          → KH mẫu vừa tạo, chờ mở lớp
cho_giang_vien  → Đã mở lớp, đang chờ GV xác nhận phân công
san_sang        → Tất cả GV xác nhận, chờ đến ngày khai giảng
dang_day        → Đang trong thời gian dạy học (ngay_mo_lop ≤ hôm nay ≤ ngay_ket_thuc)
ket_thuc        → Đã qua ngày kết thúc
```

### Flow tổng thể

```
[1] Admin tạo KH mẫu
      → nhập tên, mã mẫu, môn học, cấp độ, mô tả
      → thêm danh sách module (tên + thứ tự + thời lượng)
      → lưu: loai='mau', trang_thai_van_hanh='cho_mo'

[2] Trang index /admin/khoa-hoc
      Tab "Khóa học mẫu": danh sách loai='mau'
        → Mỗi dòng: Tên | Mã mẫu | Số module | Đã mở X lần | Nút "Mở lớp"
      Tab "Đang hoạt động": danh sách loai='hoat_dong'
        → Mỗi dòng: Tên | Mã | Từ mẫu nào | Ngày khai giảng | Ngày mở lớp | Ngày kết thúc | Trạng thái

[3] Admin bấm "Mở lớp" từ KH mẫu → form mở lớp
      → Hiển thị: thông tin mẫu (chỉ đọc), danh sách module (chỉ đọc)
      → Admin điền:
           - Ngày khai giảng *
           - Ngày mở lớp *
           - Ngày kết thúc *
           - Ghi chú nội bộ
           - Chọn GV cho từng module (optional, có thể để trống rồi phân công sau)
      → Submit → hệ thống:
           (a) Sinh mã mới (PHP-MAU-K03)
           (b) Tạo KhoaHoc mới với loai='hoat_dong', khoa_hoc_mau_id=id_mau
           (c) Copy toàn bộ module từ mẫu → tạo ModuleHoc mới thuộc KH mới
               (ma_module mới = ma_kh_moi + 'M' + pad(thuTu,2))
           (d) Nếu có GV → tạo PhanCongModuleGiangVien trang_thai='cho_xac_nhan'
           (e) KH mẫu gốc KHÔNG thay đổi gì cả
           (f) flash message thành công + redirect về trang detail lớp mới

[4] Trang detail khóa học mẫu /admin/khoa-hoc/{id}
      → Hiển thị: thông tin + module + badge "Đã mở X lần"
      → Lịch sử các lần mở lớp (danh sách KH hoạt động sinh từ mẫu này)
      → Nút "Mở lớp mới"

[5] Trang detail khóa học hoạt động /admin/khoa-hoc/{id}
      → Banner màu xanh: Mã khóa học | Từ mẫu: {ten_mau} | Lần thứ X
      → 3 mốc ngày hiển thị nổi bật: Khai giảng | Mở lớp | Kết thúc
      → Danh sách module + GV phụ trách
      → Trạng thái vận hành
```

---

## 📋 QUY TẮC BẮT BUỘC CHO GEMINI

```
1.  Làm DUY NHẤT 1 phase mỗi lần. Sau phase → liệt kê file → dừng chờ confirm.
2.  Sửa file cũ: chỉ rõ "TÌM đoạn [X] → THAY bằng [Y]", kèm context đủ để tìm.
3.  File mới: code đầy đủ 100%. KHÔNG dùng // TODO, placeholder, "tương tự bên trên".
4.  Validation messages: 100% tiếng Việt.
5.  Mọi DB write (INSERT/UPDATE/DELETE): bọc trong DB::transaction().
6.  Eager loading: dùng with() đúng chỗ. KHÔNG để N+1 query.
7.  Conflict với code cũ: báo rõ → đợi developer quyết định → không tự xử lý.
8.  Cuối mỗi phase: liệt kê đầy đủ [TẠO MỚI] và [SỬA] với đường dẫn tuyệt đối.
9.  KHÔNG thêm feature ngoài scope phase đang làm.
10. Tất cả import/use ở đầu file phải đầy đủ.
11. Route: dùng resource route kết hợp route bổ sung, đặt tên rõ ràng.
12. Không tạo middleware mới nếu dự án đã có check.role.
```

---

# ══════════════════════════════════════════
# PHASE 0 — MIGRATION: THÊM CỘT VÀO BẢNG
# ══════════════════════════════════════════

```
Bạn là senior Laravel developer làm việc trên dự án thuctap_khaitri.

NHIỆM VỤ: Tạo 1 file migration để alter bảng khoa_hoc, thêm các cột sau.

Tên file migration: YYYY_MM_DD_XXXXXX_add_columns_to_khoa_hoc_table.php

CÁC CỘT CẦN THÊM:

1. loai ENUM('mau','hoat_dong') NOT NULL DEFAULT 'mau'
   → after('trang_thai')

2. trang_thai_van_hanh ENUM('cho_mo','cho_giang_vien','san_sang','dang_day','ket_thuc')
   NOT NULL DEFAULT 'cho_mo'
   → after('loai')

3. khoa_hoc_mau_id BIGINT UNSIGNED NULL
   → FK → khoa_hoc.id → onDelete SET NULL
   → after('trang_thai_van_hanh')
   Ý nghĩa: KH hoạt động trỏ về KH mẫu gốc. KH mẫu để NULL.

4. lan_mo_thu INT UNSIGNED NOT NULL DEFAULT 0
   → after('khoa_hoc_mau_id')
   Ý nghĩa: Lần mở thứ mấy từ mẫu đó. KH mẫu để 0.

5. ngay_khai_giang DATE NULL
   → after('lan_mo_thu')
   Ý nghĩa: Ngày tổ chức buổi lễ khai giảng.

6. ngay_mo_lop DATE NULL
   → after('ngay_khai_giang')
   Ý nghĩa: Ngày học viên bắt đầu vào học thật (có thể sau ngày khai giảng vài ngày).

7. ngay_ket_thuc DATE NULL
   → after('ngay_mo_lop')
   Ý nghĩa: Ngày kết thúc toàn bộ khóa học.

8. ghi_chu_noi_bo TEXT NULL
   → after('ngay_ket_thuc')
   Ý nghĩa: Ghi chú chỉ admin thấy.

9. created_by VARCHAR(20) NULL
   → after('ghi_chu_noi_bo')
   FK → nguoi_dung.ma_nguoi_dung → onDelete SET NULL
   (nullable vì dữ liệu cũ chưa có)

PHƯƠNG THỨC down(): reverse toàn bộ — dropForeign trước, dropColumn sau.

SAU KHI VIẾT MIGRATION, hướng dẫn developer chạy:
   php artisan migrate

CHECKLIST PHASE 0:
□ Migration tạo đủ 9 cột
□ FK khoa_hoc_mau_id tham chiếu đúng bảng khoa_hoc
□ FK created_by tham chiếu đúng nguoi_dung.ma_nguoi_dung
□ Phương thức down() hoàn chỉnh
□ Chạy migrate không có lỗi

Liệt kê [TẠO MỚI]: đường dẫn file migration đầy đủ
Dừng lại, báo kết quả, đợi xác nhận trước khi làm Phase 1.
```

---

# ══════════════════════════════════════════
# PHASE 1 — MODEL & RELATIONSHIP
# ══════════════════════════════════════════

```
NHIỆM VỤ: Cập nhật Model KhoaHoc và kiểm tra Model liên quan.

── FILE: app/Models/KhoaHoc.php ──────────────────────────────────

Thêm vào $fillable tất cả cột mới:
'loai', 'trang_thai_van_hanh', 'khoa_hoc_mau_id', 'lan_mo_thu',
'ngay_khai_giang', 'ngay_mo_lop', 'ngay_ket_thuc',
'ghi_chu_noi_bo', 'created_by'

Thêm $casts:
'ngay_khai_giang' => 'date',
'ngay_mo_lop'     => 'date',
'ngay_ket_thuc'   => 'date',
'trang_thai'      => 'boolean',

Thêm các relationships:

// KH hoạt động → KH mẫu gốc
public function khoaHocMau(): BelongsTo
{
    return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_mau_id');
}

// KH mẫu → tất cả lớp đã mở từ mẫu này
public function lopDaMo(): HasMany
{
    return $this->hasMany(KhoaHoc::class, 'khoa_hoc_mau_id');
}

// Relationship đã có sẵn (kiểm tra, không ghi đè)
public function monHoc(): BelongsTo { ... }
public function moduleHocs(): HasMany { ... }
public function phanCongModuleGiangVien(): HasMany { ... }

// Scopes tiện dụng
public function scopeMau($query)
{
    return $query->where('loai', 'mau');
}

public function scopeHoatDong($query)
{
    return $query->where('loai', 'hoat_dong');
}

// Accessor: đếm số lần đã mở từ mẫu này
public function getSoLanMoAttribute(): int
{
    return $this->lopDaMo()->count();
}

// Helper: label trạng thái vận hành tiếng Việt
public function getLabelTrangThaiVanHanhAttribute(): string
{
    return match($this->trang_thai_van_hanh) {
        'cho_mo'          => 'Chờ mở lớp',
        'cho_giang_vien'  => 'Chờ giảng viên xác nhận',
        'san_sang'        => 'Sẵn sàng khai giảng',
        'dang_day'        => 'Đang giảng dạy',
        'ket_thuc'        => 'Đã kết thúc',
        default           => 'Không xác định',
    };
}

// Helper: màu badge Bootstrap theo trạng thái
public function getBadgeTrangThaiAttribute(): string
{
    return match($this->trang_thai_van_hanh) {
        'cho_mo'          => 'secondary',
        'cho_giang_vien'  => 'warning',
        'san_sang'        => 'info',
        'dang_day'        => 'success',
        'ket_thuc'        => 'dark',
        default           => 'light',
    };
}

── KIỂM TRA model ModuleHoc ──────────────────────────────────────
Đảm bảo $fillable có: khoa_hoc_id, ma_module, ten_module, mo_ta,
thu_tu_module, thoi_luong_du_kien, trang_thai

CHECKLIST PHASE 1:
□ $fillable đầy đủ 9 cột mới
□ $casts đúng kiểu date cho 3 trường ngày
□ 2 relationships khoaHocMau() và lopDaMo() đúng FK
□ 2 scopes scopeMau() và scopeHoatDong() hoạt động
□ 3 accessors/helpers không gây lỗi syntax
□ ModuleHoc $fillable đầy đủ

Liệt kê [SỬA]: app/Models/KhoaHoc.php (và app/Models/ModuleHoc.php nếu có sửa)
Dừng lại, đợi xác nhận.
```

---

# ══════════════════════════════════════════
# PHASE 2 — ROUTES & CONTROLLER SKELETON
# ══════════════════════════════════════════

```
NHIỆM VỤ: Cập nhật routes và tạo/cập nhật KhoaHocController.

── routes/web.php ────────────────────────────────────────────────

Trong group middleware(['auth', 'check.role:admin']) prefix('admin'):
Thay thế hoặc bổ sung block route khoa-hoc thành:

Route::prefix('khoa-hoc')->name('admin.khoa-hoc.')->group(function () {
    // CRUD cơ bản
    Route::get('/',           [KhoaHocController::class, 'index'])->name('index');
    Route::get('/create',     [KhoaHocController::class, 'create'])->name('create');
    Route::post('/',          [KhoaHocController::class, 'store'])->name('store');
    Route::get('/{id}',       [KhoaHocController::class, 'show'])->name('show');
    Route::get('/{id}/edit',  [KhoaHocController::class, 'edit'])->name('edit');
    Route::put('/{id}',       [KhoaHocController::class, 'update'])->name('update');
    Route::delete('/{id}',    [KhoaHocController::class, 'destroy'])->name('destroy');

    // Mở lớp từ khóa học mẫu
    Route::get('/{id}/mo-lop',  [KhoaHocController::class, 'showMoLop'])->name('mo-lop');
    Route::post('/{id}/mo-lop', [KhoaHocController::class, 'storeMoLop'])->name('mo-lop.store');
});

── app/Http/Controllers/Admin/KhoaHocController.php ─────────────

Tạo file đầy đủ với tất cả phương thức. Mỗi phương thức phải hoạt động.
Không dùng placeholder hay comment "implement later".

use App\Models\KhoaHoc;
use App\Models\MonHoc;
use App\Models\ModuleHoc;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

─── index() ───────────────────────────────────────────────────────
Query:
  $khoaHocMau = KhoaHoc::mau()
      ->with(['monHoc', 'moduleHocs', 'lopDaMo'])
      ->withCount('lopDaMo')
      ->orderBy('created_at', 'desc')
      ->paginate(10, ['*'], 'page_mau');

  $khoaHocHoatDong = KhoaHoc::hoatDong()
      ->with(['monHoc', 'moduleHocs', 'khoaHocMau'])
      ->whereIn('trang_thai_van_hanh', ['cho_giang_vien','san_sandy','dang_day'])
      ->orderBy('ngay_mo_lop', 'asc')
      ->paginate(10, ['*'], 'page_hd');

  $activeTab = request('tab', 'mau'); // 'mau' hoặc 'hoat_dong'

  return view('pages.admin.khoa-hoc.khoa-hoc.index', compact(
      'khoaHocMau', 'khoaHocHoatDong', 'activeTab'
  ));

─── create() ──────────────────────────────────────────────────────
  $monHocs = MonHoc::where('trang_thai', 1)->orderBy('ten_mon_hoc')->get();
  return view('pages.admin.khoa-hoc.khoa-hoc.create', compact('monHocs'));

─── store() ───────────────────────────────────────────────────────
Validation:
  mon_hoc_id     → required|exists:mon_hoc,id
  ma_khoa_hoc    → required|string|max:50|unique:khoa_hoc,ma_khoa_hoc
  ten_khoa_hoc   → required|string|max:200
  cap_do         → required|in:co_ban,trung_binh,nang_cao
  mo_ta_ngan     → nullable|string|max:500
  mo_ta_chi_tiet → nullable|string
  hinh_anh       → nullable|image|mimes:jpg,jpeg,png|max:2048
  ghi_chu_noi_bo → nullable|string
  modules        → required|array|min:1
  modules.*.ten_module           → required|string|max:200
  modules.*.thoi_luong_du_kien   → nullable|integer|min:1

  Messages tiếng Việt cho tất cả rule trên.

Logic trong DB::transaction():
  1. Upload hinh_anh nếu có → Storage::disk('public')->store('images/khoa-hoc')
  2. Tạo KhoaHoc:
       loai                = 'mau'
       trang_thai_van_hanh = 'cho_mo'
       khoa_hoc_mau_id     = null
       lan_mo_thu          = 0
       ngay_khai_giang, ngay_mo_lop, ngay_ket_thuc = null
       created_by          = Auth::user()->ma_nguoi_dung
  3. Loop modules → tạo ModuleHoc
       ma_module = $khoaHoc->ma_khoa_hoc . 'M' . str_pad($index+1, 2, '0', STR_PAD_LEFT)
       thu_tu_module = $index + 1
  4. Update tong_so_module = count(modules)
  5. Redirect → route('admin.khoa-hoc.show', $khoaHoc->id)
     Flash: 'Tạo khóa học mẫu thành công!'

─── showMoLop() ───────────────────────────────────────────────────
  $khoaHocMau = KhoaHoc::mau()
      ->with(['monHoc', 'moduleHocs'])
      ->findOrFail($id);

  $giangViens = GiangVien::where('trang_thai', 1)
      ->orderBy('ho_ten')
      ->get();

  $soLanDaMo = $khoaHocMau->lop_da_mo_count ?? $khoaHocMau->lopDaMo()->count();
  $maMoiDuKien = $khoaHocMau->ma_khoa_hoc . '-K' . str_pad($soLanDaMo + 1, 2, '0', STR_PAD_LEFT);

  return view('pages.admin.khoa-hoc.khoa-hoc.mo-lop', compact(
      'khoaHocMau', 'giangViens', 'soLanDaMo', 'maMoiDuKien'
  ));

─── storeMoLop() ──────────────────────────────────────────────────
Validation:
  ngay_khai_giang → required|date|after_or_equal:today
  ngay_mo_lop     → required|date|after_or_equal:ngay_khai_giang
  ngay_ket_thuc   → required|date|after:ngay_mo_lop
  ghi_chu_noi_bo  → nullable|string
  giang_vien_modules          → nullable|array
  giang_vien_modules.*        → nullable|exists:giang_vien,id

  Messages tiếng Việt.

Logic trong DB::transaction():
  1. Load KH mẫu findOrFail($id) — KIỂM TRA loai='mau', nếu không → abort(404)
  2. Đếm số lần đã mở: $lanThu = $khoaHocMau->lopDaMo()->count() + 1
  3. Sinh mã mới: $maMoi = $khoaHocMau->ma_khoa_hoc . '-K' . str_pad($lanThu, 2, '0', STR_PAD_LEFT)
     Kiểm tra unique: if (KhoaHoc::where('ma_khoa_hoc', $maMoi)->exists()) abort(422, 'Mã bị trùng')
  4. Tạo KhoaHoc mới:
       ten_khoa_hoc    = $khoaHocMau->ten_khoa_hoc . ' (Khóa ' . $lanThu . ')'
       ma_khoa_hoc     = $maMoi
       mon_hoc_id      = $khoaHocMau->mon_hoc_id
       cap_do          = $khoaHocMau->cap_do
       mo_ta_ngan      = $khoaHocMau->mo_ta_ngan
       mo_ta_chi_tiet  = $khoaHocMau->mo_ta_chi_tiet
       hinh_anh        = $khoaHocMau->hinh_anh
       loai                = 'hoat_dong'
       trang_thai_van_hanh = 'cho_mo'  (sẽ chuyển sang cho_giang_vien nếu có GV)
       khoa_hoc_mau_id     = $khoaHocMau->id
       lan_mo_thu          = $lanThu
       ngay_khai_giang     = $request->ngay_khai_giang
       ngay_mo_lop         = $request->ngay_mo_lop
       ngay_ket_thuc       = $request->ngay_ket_thuc
       ghi_chu_noi_bo      = $request->ghi_chu_noi_bo
       created_by          = Auth::user()->ma_nguoi_dung
  5. Copy module từ mẫu → tạo ModuleHoc mới thuộc KH mới:
       foreach ($khoaHocMau->moduleHocs as $moduleMau) {
           $maModuleMoi = $maMoi . 'M' . str_pad($moduleMau->thu_tu_module, 2, '0', STR_PAD_LEFT);
           ModuleHoc::create([
               'khoa_hoc_id'         => $khoaMoi->id,
               'ma_module'           => $maModuleMoi,
               'ten_module'          => $moduleMau->ten_module,
               'mo_ta'               => $moduleMau->mo_ta,
               'thu_tu_module'       => $moduleMau->thu_tu_module,
               'thoi_luong_du_kien'  => $moduleMau->thoi_luong_du_kien,
               'trang_thai'          => 1,
           ]);
       }
  6. Update tong_so_module của KH mới
  7. Nếu có giang_vien_modules (array [module_hoc_id => giang_vien_id]):
       → Tạo PhanCongModuleGiangVien với trang_thai='cho_xac_nhan'
       → Update trang_thai_van_hanh của KH mới = 'cho_giang_vien'
  8. KH MẪU GỐC KHÔNG ĐƯỢC SỬA GÌ CẢ
  9. Redirect → route('admin.khoa-hoc.show', $khoaMoi->id)
     Flash: 'Đã mở lớp thành công! Mã khóa học: ' . $maMoi

─── show() ────────────────────────────────────────────────────────
  $khoaHoc = KhoaHoc::with([
      'monHoc',
      'moduleHocs.phanCongModuleGiangVien.giangVien',
      'khoaHocMau',    // nếu là KH hoạt động
      'lopDaMo.monHoc' // nếu là KH mẫu
  ])->findOrFail($id);

  return view('pages.admin.khoa-hoc.khoa-hoc.show', compact('khoaHoc'));

─── edit() / update() ─────────────────────────────────────────────
  Chỉ cho phép edit KH mẫu (loai='mau').
  Nếu loai='hoat_dong' → redirect back với flash error:
    'Không thể chỉnh sửa khóa học đang hoạt động. Hãy dùng chức năng Mở lớp.'

─── destroy() ─────────────────────────────────────────────────────
  Chỉ cho phép xóa KH mẫu chưa có lớp nào mở.
  if ($khoaHoc->loai === 'mau' && $khoaHoc->lopDaMo()->exists()) {
      return back()->with('error', 'Không thể xóa mẫu đã có lớp học. Hãy ẩn thay vì xóa.');
  }

CHECKLIST PHASE 2:
□ Routes không conflict với routes cũ
□ 8 phương thức controller không có lỗi syntax
□ store() tạo đúng loai='mau'
□ storeMoLop() copy module, KH mẫu gốc không thay đổi
□ Sinh mã tự động đúng pattern {MA_MAU}-K{01}
□ Validation messages tiếng Việt
□ Mọi DB write bọc trong transaction()
□ destroy() có guard không cho xóa mẫu đã mở lớp

Liệt kê [TẠO/SỬA] file. Dừng lại đợi xác nhận.
```

---

# ══════════════════════════════════════════
# PHASE 3 — VIEW: INDEX (2 TAB)
# ══════════════════════════════════════════

```
NHIỆM VỤ: Tạo view index hiển thị 2 tab.

── File: resources/views/pages/admin/khoa-hoc/khoa-hoc/index.blade.php ──

@extends('layouts.app')
@section('title', 'Quản lý Khóa học')

HEADER ROW:
  H4: "Quản lý Khóa học"
  Bên phải: button "➕ Tạo khóa học mẫu" → route('admin.khoa-hoc.create')
             (màu btn-outline-primary)

FLASH MESSAGES:
  @if(session('success')) alert alert-success dismissible @endif
  @if(session('error'))   alert alert-danger  dismissible @endif

TAB NAV (Bootstrap nav-tabs):
  Tab 1: "📋 Khóa học mẫu"   (badge: $khoaHocMau->total() items)
  Tab 2: "⚡ Đang hoạt động" (badge: $khoaHocHoatDong->total() items)

Active tab theo $activeTab (query param ?tab=mau hoặc ?tab=hoat_dong)

──── TAB 1: Khóa học mẫu ─────────────────────────────────────────

Table responsive có các cột:
  STT | Mã mẫu | Tên khóa học | Môn học | Cấp độ | Số module | Đã mở | Ngày tạo | Hành động

"Đã mở": badge bg-info → "$khoaHoc->lop_da_mo_count lần"
  Nếu = 0: badge bg-secondary "Chưa mở"

"Hành động" với mỗi dòng:
  btn btn-sm btn-success "Mở lớp"  → route('admin.khoa-hoc.mo-lop', $kh->id)
  btn btn-sm btn-primary "Chi tiết" → route('admin.khoa-hoc.show', $kh->id)
  btn btn-sm btn-warning "Sửa"      → route('admin.khoa-hoc.edit', $kh->id)
  btn btn-sm btn-danger  "Xóa"      → form DELETE với confirm JS

Phân trang: {{ $khoaHocMau->appends(['tab' => 'mau'])->links() }}

──── TAB 2: Đang hoạt động ───────────────────────────────────────

Table responsive có các cột:
  STT | Mã | Tên lớp | Từ mẫu | Lần thứ | Ngày khai giảng | Ngày mở lớp | Ngày kết thúc | Trạng thái | Hành động

"Từ mẫu": tên khóa học mẫu (link nhỏ)
"Lần thứ": badge bg-secondary "K{{ $kh->lan_mo_thu }}"
"Ngày khai giảng": format d/m/Y
"Ngày mở lớp": format d/m/Y — hiển thị màu xanh nếu trong khoảng đang dạy
"Trạng thái": badge màu theo $kh->badge_trang_thai + label $kh->label_trang_thai_van_hanh

"Hành động":
  btn btn-sm btn-primary "Chi tiết" → route('admin.khoa-hoc.show', $kh->id)

Phân trang: {{ $khoaHocHoatDong->appends(['tab' => 'hoat_dong'])->links() }}

JAVASCRIPT:
  // Giữ active tab theo URL query param
  document.addEventListener('DOMContentLoaded', () => {
      const tab = new URLSearchParams(window.location.search).get('tab') || 'mau';
      document.querySelector(`[data-tab="${tab}"]`)?.click();
  });

CHECKLIST PHASE 3:
□ 2 tab hiển thị đúng dữ liệu
□ Badge đếm số KH mẫu, số KH hoạt động
□ "Đã mở X lần" hiển thị đúng
□ 3 cột ngày hiển thị đúng format d/m/Y
□ JS giữ active tab không lỗi
□ Phân trang không mất query param tab
□ Button Xóa có confirm JS

Dừng lại đợi xác nhận.
```

---

# ══════════════════════════════════════════
# PHASE 4 — VIEW: CREATE (TẠO KH MẪU)
# ══════════════════════════════════════════

```
NHIỆM VỤ: Tạo form tạo khóa học mẫu.

── File: resources/views/pages/admin/khoa-hoc/khoa-hoc/create.blade.php ──

HEADER:
  H4: "📋 Tạo Khóa học mẫu" + badge bg-info "Template"
  p.text-muted: "Chuẩn bị nội dung chương trình học. Giảng viên và lịch dạy sẽ setup sau khi mở lớp."
  Breadcrumb: Trang chủ / Khóa học / Tạo mẫu

FORM: POST → route('admin.khoa-hoc.store'), enctype=multipart/form-data

─── SECTION 1: Thông tin chung (vip-card) ───────────────────────
  Select Môn học * (options từ $monHocs)
  Input Mã khóa học * — placeholder: "VD: PHP-MAU, JAVA-MAU"
    Helper text: "Mã này sẽ là prefix. VD: PHP-MAU → lớp mở sẽ là PHP-MAU-K01, PHP-MAU-K02"
  Input Tên khóa học *
  Radio Cấp độ *: Cơ bản | Trung bình | Nâng cao
  Textarea Mô tả ngắn (max 500 ký tự, counter)
  Textarea Mô tả chi tiết
  File upload Hình ảnh (jpg,png,max 2MB, preview ảnh trước khi upload)
  Textarea Ghi chú nội bộ (placeholder: "Ghi chú dành riêng cho admin, học viên không thấy")

─── SECTION 2: Danh sách Module (vip-card) ──────────────────────
  Header: "📚 Cấu trúc Module học tập" + badge đếm số module

  Mỗi module item:
  <div class="module-item border rounded p-3 mb-2">
    Row:
      Col-5: Input "Tên module *" name="modules[{i}][ten_module]"
      Col-3: Input number "Thời lượng (phút)" name="modules[{i}][thoi_luong_du_kien]"
      Col-3: Input "Mô tả ngắn" name="modules[{i}][mo_ta]"
      Col-1: button btn-outline-danger btn-sm "✕" (xóa dòng — không xóa nếu chỉ còn 1)
    Drag handle icon ở đầu dòng (fa-grip-vertical text-muted)
  </div>

  Button "+ Thêm module" (btn-outline-secondary)
  Note nhỏ: "Kéo thả để sắp xếp thứ tự module"

─── FOOTER BUTTONS ──────────────────────────────────────────────
  btn btn-secondary "Hủy" → route('admin.khoa-hoc.index')
  btn btn-primary "Lưu khóa học mẫu" (submit)

JAVASCRIPT:
  1. Thêm/xóa dòng module động (clone template, update index [i])
  2. Cập nhật badge đếm module realtime
  3. Kéo thả sắp xếp module (dùng Sortable.js CDN hoặc native drag-drop đơn giản)
  4. Preview ảnh khi chọn file
  5. Counter ký tự cho textarea mô tả ngắn

LỖI VALIDATION: hiển thị @error('field') dưới mỗi input

CHECKLIST PHASE 4:
□ Form submit đúng route store()
□ Thêm/xóa module động không lỗi JS
□ Badge đếm module cập nhật realtime
□ Preview ảnh hoạt động
□ @error hiển thị đủ
□ Không có input ngày khai giảng/mở lớp/kết thúc (chỉ KH mẫu, chưa cần)

Dừng lại đợi xác nhận.
```

---

# ══════════════════════════════════════════
# PHASE 5 — VIEW: MỞ LỚP TỪ KHÓA HỌC MẪU
# ══════════════════════════════════════════

```
NHIỆM VỤ: Tạo form mở lớp từ KH mẫu.

── File: resources/views/pages/admin/khoa-hoc/khoa-hoc/mo-lop.blade.php ──

HEADER:
  H4: "⚡ Mở lớp từ khóa học mẫu"
  Breadcrumb: Trang chủ / Khóa học / {{ $khoaHocMau->ten_khoa_hoc }} / Mở lớp

─── CARD THÔNG TIN MẪU (chỉ đọc, bg-light) ─────────────────────
  "📋 Khóa học mẫu gốc" (không thể chỉnh sửa)
  Row 2 cột:
    Trái: Mã mẫu | Tên | Môn học | Cấp độ | Số module
    Phải: Badge "Đã mở {{ $soLanDaMo }} lần" (bg-info nếu >0, bg-secondary nếu =0)
          Preview mã sẽ sinh: 
            <div class="alert alert-success">
              Mã khóa học lớp mới: <strong class="fs-5">{{ $maMoiDuKien }}</strong>
            </div>

─── CARD DANH SÁCH MODULE (chỉ đọc) ────────────────────────────
  Table đơn giản: STT | Tên module | Thời lượng
  Note: "Các module này sẽ được copy sang lớp mới. Bạn có thể phân công giảng viên sau."

FORM: POST → route('admin.khoa-hoc.mo-lop.store', $khoaHocMau->id)

─── SECTION: 3 MỐC NGÀY (vip-card border-primary) ───────────────
  Header icon 📅 màu primary: "Lịch học của lớp mới"
  
  Alert info nhỏ:
  "💡 3 mốc ngày này là riêng biệt nhau:
   • Khai giảng: buổi lễ chào mừng, giới thiệu (học viên không cần học bài)
   • Mở lớp: ngày đầu tiên học viên vào học thật (có bài giảng)
   • Kết thúc: ngày kết thúc toàn bộ chương trình"

  Row 3 cột:
    Input date "Ngày khai giảng *"
      name="ngay_khai_giang", min="{{ date('Y-m-d') }}"
      placeholder="dd/mm/yyyy"
      Helper: "Buổi lễ khai giảng chính thức"
    
    Input date "Ngày mở lớp *"
      name="ngay_mo_lop"
      Helper: "Ngày học viên bắt đầu học thật (≥ ngày khai giảng)"
    
    Input date "Ngày kết thúc *"
      name="ngay_ket_thuc"
      Helper: "Kết thúc toàn bộ chương trình (sau ngày mở lớp)"

─── SECTION: PHÂN CÔNG GIẢNG VIÊN (optional, vip-card) ──────────
  Header: "👨‍🏫 Phân công giảng viên (có thể để trống, phân công sau)"
  
  Alert warning nhỏ: "Phân công GV ngay sẽ gửi thông báo xác nhận. Để trống nếu chưa chọn được GV."

  Table:
  | Thứ tự | Tên module | Thời lượng | Giảng viên phụ trách |
  Mỗi dòng:
    name="giang_vien_modules[{module_id}]"
    Select → <option value="">-- Chọn sau --</option> + options từ $giangViens
    Hiển thị GV: "Họ tên (Chuyên ngành)" 

─── SECTION: GHI CHÚ ────────────────────────────────────────────
  Textarea "Ghi chú nội bộ" name="ghi_chu_noi_bo"
  Placeholder: "VD: Đợt này ưu tiên học viên đã đăng ký trước..."

─── FOOTER ──────────────────────────────────────────────────────
  btn btn-secondary "← Quay lại" → route('admin.khoa-hoc.show', $khoaHocMau->id)
  btn btn-success btn-lg "✅ Xác nhận mở lớp" (submit)
    Icon fa-rocket, text: "Mở lớp — Mã: {{ $maMoiDuKien }}"

JAVASCRIPT:
  // Validation ngày: ngay_mo_lop >= ngay_khai_giang, ngay_ket_thuc > ngay_mo_lop
  document.getElementById('ngay_khai_giang').addEventListener('change', function() {
      document.getElementById('ngay_mo_lop').min = this.value;
  });
  document.getElementById('ngay_mo_lop').addEventListener('change', function() {
      document.getElementById('ngay_ket_thuc').min = this.value;
  });

CHECKLIST PHASE 5:
□ Mã mới dự kiến hiển thị đúng (PHP-MAU-K03)
□ 3 input ngày có label rõ ràng, phân biệt được nghiệp vụ
□ JS chặn chọn ngày mở lớp < ngày khai giảng
□ JS chặn chọn ngày kết thúc ≤ ngày mở lớp
□ Select GV optional (không bắt buộc)
□ Submit đúng route storeMoLop()
□ KH mẫu gốc hiển thị chỉ-đọc, không có input sửa

Dừng lại đợi xác nhận.
```

---

# ══════════════════════════════════════════
# PHASE 6 — VIEW: SHOW (CHI TIẾT)
# ══════════════════════════════════════════

```
NHIỆM VỤ: Tạo trang chi tiết hiển thị khác nhau theo loại KH.

── File: resources/views/pages/admin/khoa-hoc/khoa-hoc/show.blade.php ──

Dùng @if($khoaHoc->loai === 'mau') để hiển thị 2 layout khác nhau.

──── KHI LÀ KHÓA HỌC MẪU (loai='mau') ──────────────────────────

Banner màu info:
  "📋 KHÓA HỌC MẪU — Template chương trình học"
  Nút: "⚡ Mở lớp mới" (btn-success) → route('admin.khoa-hoc.mo-lop', $khoaHoc->id)
  Nút: "✏️ Chỉnh sửa" (btn-warning)  → route('admin.khoa-hoc.edit', $khoaHoc->id)

Card thông tin cơ bản: Mã | Tên | Môn học | Cấp độ | Mô tả | Ngày tạo

Card "📊 Lịch sử các lần mở lớp":
  Badge lớn: "Đã mở {{ $khoaHoc->so_lan_mo }} lần"
  @if($khoaHoc->lopDaMo->isNotEmpty())
    Table: Mã lớp | Tên lớp | Ngày khai giảng | Ngày mở lớp | Ngày kết thúc | Trạng thái | Link chi tiết
  @else
    Alert info: "Chưa có lớp nào được mở từ mẫu này."
    Button lớn: "Mở lớp đầu tiên →"
  @endif

Card "📚 Danh sách Module":
  Table: STT | Mã | Tên module | Thời lượng | Trạng thái

──── KHI LÀ KHÓA HỌC ĐANG HOẠT ĐỘNG (loai='hoat_dong') ─────────

Banner màu theo badge_trang_thai:
  "⚡ KHÓA HỌC ĐANG HOẠT ĐỘNG"
  Badge trạng thái vận hành: {{ $khoaHoc->label_trang_thai_van_hanh }}

Info box quan trọng:
  Row 3 cột nổi bật (card nhỏ bg-light):
    [📅 Khai giảng]     [🏁 Mở lớp]         [🔚 Kết thúc]
    {{ d/m/Y }}         {{ d/m/Y }}           {{ d/m/Y }}
    "Buổi lễ khai giảng" "Ngày bắt đầu học"  "Ngày kết thúc"

Info phụ:
  Mã khóa học: badge lớn fs-5 {{ $khoaHoc->ma_khoa_hoc }}
  Từ mẫu: link → show của KH mẫu
  Lần thứ: {{ $khoaHoc->lan_mo_thu }}
  Môn học | Cấp độ | Số module

Card "👨‍🏫 Module & Giảng viên phụ trách":
  Table: STT | Tên module | Thời lượng | Giảng viên | Trạng thái phân công
  "Trạng thái phân công": badge
    cho_xac_nhan → warning "Chờ xác nhận"
    da_nhan      → success "Đã nhận"
    tu_choi      → danger  "Từ chối"
    (trống)      → secondary "Chưa phân công"

CHECKLIST PHASE 6:
□ 2 layout khác nhau rõ ràng (mau vs hoat_dong)
□ 3 mốc ngày hiển thị đúng, có label giải thích
□ Badge mã khóa học nổi bật
□ Lịch sử mở lớp hiển thị trong trang mẫu
□ Trạng thái phân công GV theo module

Dừng lại đợi xác nhận.
```

---

# ══════════════════════════════════════════
# PHASE 7 — KIỂM TRA & DỌN DẸP CUỐI
# ══════════════════════════════════════════

```
NHIỆM VỤ: Kiểm tra toàn bộ feature và fix các vấn đề nhỏ.

CHECKLIST TỔNG:
□ Tạo KH mẫu → lưu loai='mau', trang_thai_van_hanh='cho_mo'
□ Trang index tab "Khóa học mẫu" hiển thị đúng dữ liệu
□ Trang index tab "Đang hoạt động" đúng
□ Mở lớp từ mẫu → KH mới tạo đúng, mã tự sinh PHP-MAU-K01
□ KH mẫu gốc KHÔNG thay đổi sau khi mở lớp
□ Module được copy đúng (tên giống, mã module mới)
□ 3 mốc ngày lưu đúng (ngay_khai_giang, ngay_mo_lop, ngay_ket_thuc)
□ Mở lớp lần 2 → sinh mã PHP-MAU-K02 đúng
□ Trang detail mẫu: lịch sử mở lớp hiển thị đủ
□ Trang detail hoạt động: 3 ngày hiển thị nổi bật
□ Xóa KH mẫu đã mở lớp → bị chặn, có thông báo rõ ràng
□ Flash message tiếng Việt cho tất cả action
□ Không có N+1 query (eager loading đúng)
□ Tất cả DB write trong transaction()

NẾU CÓ LỖI: báo cụ thể file + dòng + nguyên nhân, đề xuất fix.
NẾU PHÁT HIỆN REGRESSION (code cũ bị ảnh hưởng): báo ngay.

DANH SÁCH TOÀN BỘ FILE ĐÃ TẠO/SỬA (liệt kê lại tổng kết):
[TẠO MỚI]
  - database/migrations/XXXX_add_columns_to_khoa_hoc_table.php
  - app/Http/Controllers/Admin/KhoaHocController.php
  - resources/views/pages/admin/khoa-hoc/khoa-hoc/index.blade.php
  - resources/views/pages/admin/khoa-hoc/khoa-hoc/create.blade.php
  - resources/views/pages/admin/khoa-hoc/khoa-hoc/mo-lop.blade.php
  - resources/views/pages/admin/khoa-hoc/khoa-hoc/show.blade.php

[SỬA]
  - app/Models/KhoaHoc.php
  - routes/web.php

══════════════════════════════════════════
✅ KẾT THÚC — Feature hoàn chỉnh.
══════════════════════════════════════════
```

---

## 📌 NOTES CHO DEVELOPER

| Vấn đề | Giải pháp |
|--------|-----------|
| Mã KH mẫu bị trùng với lớp mới | Mẫu dùng suffix `-MAU`, lớp dùng `-K01`, unique constraint vẫn đủ |
| Module mẫu bị xóa → lớp đang hoạt động mất module | `khoa_hoc_mau_id` SET NULL không cascade module, module đã copy độc lập |
| Admin muốn sửa module sau khi mở lớp | Cho phép edit module của KH hoạt động qua trang show — Phase sau nếu cần |
| Sinh mã bị race condition (2 admin cùng mở lớp) | Dùng DB::transaction() + SELECT FOR UPDATE hoặc unique constraint tự bắn exception |
