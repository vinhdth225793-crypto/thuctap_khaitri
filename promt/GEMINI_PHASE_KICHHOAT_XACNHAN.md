# 🎯 GEMINI AGENT — NÂNG CẤP KÍCH HOẠT LỚP & XÁC NHẬN GIẢNG VIÊN
**Dự án:** thuctap_khaitri · Laravel 11 · Bootstrap 5 · Blade · MySQL  
**Quy tắc BẮTBUỘC:** Làm xong 1 phase → chạy hết checklist → báo kết quả → mới làm phase tiếp theo  
**KHÔNG làm gộp nhiều phase. KHÔNG skip checklist.**

---

## 📦 DATABASE HIỆN TẠI (đọc kỹ trước khi code)

```
TABLE khoa_hoc
  id, mon_hoc_id FK→mon_hoc.id
  ma_khoa_hoc VARCHAR(50) UNIQUE
  ten_khoa_hoc VARCHAR(200)
  loai ENUM('mau','truc_tiep') default 'mau'
  trang_thai_van_hanh ENUM('cho_mo','cho_giang_vien','san_sang','dang_day','ket_thuc') default 'cho_mo'
  ngay_khai_giang DATE nullable
  ngay_ket_thuc_du_kien DATE nullable
  cap_do ENUM('co_ban','trung_binh','nang_cao')
  tong_so_module INT default 0
  trang_thai BOOLEAN default 1
  timestamps

TABLE module_hoc
  id, khoa_hoc_id FK cascade, ma_module UNIQUE
  ten_module, mo_ta, thu_tu_module INT
  thoi_luong_du_kien INT nullable (phút), trang_thai BOOLEAN, timestamps

TABLE phan_cong_module_giang_vien
  id, khoa_hoc_id, module_hoc_id, giao_vien_id
  ngay_phan_cong DATETIME default CURRENT_TIMESTAMP
  trang_thai ENUM('cho_xac_nhan','da_nhan','tu_choi') default 'da_nhan'   ← LỖI: phải sửa default → 'cho_xac_nhan'
  ghi_chu TEXT nullable
  created_by FK→nguoi_dung.ma_nguoi_dung
  UNIQUE(module_hoc_id, giao_vien_id)
  timestamps

TABLE giang_vien
  id
  nguoi_dung_id FK→nguoi_dung.ma_nguoi_dung UNIQUE
  chuyen_nganh VARCHAR nullable
  hoc_vi VARCHAR nullable          ← đây là "trình độ" (Cử nhân / Thạc sĩ / Tiến sĩ)
  so_gio_day VARCHAR nullable
  hien_thi_trang_chu BOOLEAN default 0
  mo_ta_ngan TEXT nullable
  avatar_url VARCHAR nullable
  timestamps

TABLE nguoi_dung
  ma_nguoi_dung PK (unsignedBigInteger)
  ho_ten, email, vai_tro ENUM('admin','giang_vien','hoc_vien')
  ...

TABLE thong_bao  (nếu chưa có → Phase B sẽ tạo)
  id
  nguoi_nhan_id FK→nguoi_dung.ma_nguoi_dung
  tieu_de VARCHAR(255)
  noi_dung TEXT
  loai ENUM('phan_cong','xac_nhan_gv','mo_lop','he_thong') default 'he_thong'
  da_doc BOOLEAN default 0
  url VARCHAR(500) nullable        ← link redirect khi click thông báo
  timestamps
```

**Models & Relationships:**
```
KhoaHoc      → hasMany ModuleHoc (orderBy thu_tu_module)
KhoaHoc      → hasManyThrough PhanCongModuleGiangVien
ModuleHoc    → hasMany PhanCongModuleGiangVien
ModuleHoc    → belongsTo KhoaHoc
GiangVien    → belongsTo NguoiDung (FK: nguoi_dung_id)
GiangVien    → hasMany PhanCongModuleGiangVien (FK: giao_vien_id)
PhanCong     → belongsTo ModuleHoc, GiangVien, KhoaHoc
NguoiDung    → hasOne GiangVien (FK: nguoi_dung_id)
```

**Auth / Layout:**
```
auth()->user()          → trả về NguoiDung (PK = ma_nguoi_dung)
auth()->user()->giangVien → hasOne GiangVien
Layout: @extends('layouts.app')
CSS custom: vip-card · vip-card-header · vip-card-body · vip-form-control
Icons: Font Awesome 5
Flash: session('success'), session('error')
```

---

## 🗺️ TỔNG QUAN CÁC PHASE

```
PHASE A  →  Nâng cấp section kích hoạt (#section-kich-hoat):
            + Thêm block "Mở lớp dự kiến"
            + Dropdown GV hiển thị chuyên ngành + trình độ
            + Fix default trang_thai phân công = 'cho_xac_nhan'

PHASE B  →  GV side — Xác nhận dạy module:
            + Trang /giang-vien/phan-cong: danh sách module cần xác nhận
            + GV bấm "Xác nhận dạy" / "Từ chối"
            + Sau mỗi GV xác nhận → check xem đủ chưa → cập nhật trang_thai_van_hanh

PHASE C  →  Admin side — Nhận thông báo & mở lớp chính thức:
            + Badge thông báo trên navbar khi KH đạt 'san_sang'
            + Trang /admin/thong-bao hoặc nội tuyến trong show KH
            + Admin bấm "Xác nhận mở lớp" → trang_thai_van_hanh = 'dang_day'
            + Unlock tính năng thêm học sinh vào lớp
```

---

# ══════════════════════════════════════════════════
# PHASE A — NÂNG CẤP SECTION KÍCH HOẠT
# Mục tiêu:
#   1. Fix bug default trang_thai phân công
#   2. Thêm block "Mở lớp dự kiến" vào section kích hoạt
#   3. Dropdown chọn GV hiển thị chuyên ngành + trình độ
# ══════════════════════════════════════════════════

```
Bạn là senior Laravel developer. Làm theo đúng từng bước dưới đây.
KHÔNG làm thêm bất cứ thứ gì ngoài scope Phase A.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC A1 — FIX MIGRATION BUG (default trang_thai)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Tạo migration mới:
  php artisan make:migration fix_default_trang_thai_phan_cong_table

  up():
    DB::statement("ALTER TABLE phan_cong_module_giang_vien
                   MODIFY COLUMN trang_thai
                   ENUM('cho_xac_nhan','da_nhan','tu_choi')
                   NOT NULL DEFAULT 'cho_xac_nhan'");

  down():
    DB::statement("ALTER TABLE phan_cong_module_giang_vien
                   MODIFY COLUMN trang_thai
                   ENUM('cho_xac_nhan','da_nhan','tu_choi')
                   NOT NULL DEFAULT 'da_nhan'");

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC A2 — SỬA MODEL PhanCongModuleGiangVien
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Models/PhanCongModuleGiangVien.php

Đảm bảo $fillable có đủ:
  protected $fillable = [
      'khoa_hoc_id', 'module_hoc_id', 'giao_vien_id',
      'ngay_phan_cong', 'trang_thai', 'ghi_chu', 'created_by',
  ];

Thêm $attributes (default PHP-level):
  protected $attributes = [
      'trang_thai' => 'cho_xac_nhan',
  ];

Thêm accessor getTrangThaiLabelAttribute(): array
  $map = [
      'cho_xac_nhan' => ['label' => 'Chờ xác nhận', 'color' => 'warning',  'icon' => 'fa-clock'],
      'da_nhan'      => ['label' => 'Đã xác nhận',  'color' => 'success',  'icon' => 'fa-check-circle'],
      'tu_choi'      => ['label' => 'Từ chối',       'color' => 'danger',   'icon' => 'fa-times-circle'],
  ];
  return $map[$this->trang_thai]
      ?? ['label' => 'Không xác định', 'color' => 'secondary', 'icon' => 'fa-question'];

Đảm bảo relationships:
  public function giangVien() { return $this->belongsTo(GiangVien::class, 'giao_vien_id'); }
  public function moduleHoc()  { return $this->belongsTo(ModuleHoc::class, 'module_hoc_id'); }
  public function khoaHoc()    { return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id'); }

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC A3 — SỬA CONTROLLER: method show() & kichHoatMau()
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Http/Controllers/Admin/KhoaHocManagementController.php

### Method show($id):
  $khoaHoc = KhoaHoc::with([
      'monHoc',
      'moduleHocs.phanCongGiangViens.giangVien.nguoiDung',
  ])->findOrFail($id);

  $tongModule  = $khoaHoc->moduleHocs->count();
  $moduleCoGv  = $khoaHoc->moduleHocs
      ->filter(fn($m) => $m->phanCongGiangViens
          ->where('trang_thai', 'da_nhan')->count() > 0
      )->count();

  // Load danh sách GV cho form kích hoạt (kèm chuyên ngành + trình độ)
  $giangViens = GiangVien::with('nguoiDung')
      ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', 1))
      ->orderBy('id')
      ->get();

  return view('pages.admin.khoa-hoc.khoa-hoc.show', compact(
      'khoaHoc', 'tongModule', 'moduleCoGv', 'giangViens'
  ));

### Method kichHoatMau(Request $request, $id):
  $khoaHoc = KhoaHoc::with('moduleHocs')->findOrFail($id);

  // Guard: chỉ KH mẫu + đang chờ mở
  if ($khoaHoc->loai !== 'mau' || $khoaHoc->trang_thai_van_hanh !== 'cho_mo') {
      return redirect()->route('admin.khoa-hoc.show', $id)
          ->with('error', 'Khóa học này không thể kích hoạt.');
  }

  // Validate
  $validated = $request->validate([
      'ngay_khai_giang'       => 'required|date|after_or_equal:today',
      'ngay_ket_thuc_du_kien' => 'required|date|after:ngay_khai_giang',
      'giang_viens'           => 'required|array',
      'giang_viens.*'         => 'required|exists:giang_vien,id',
  ], [
      'ngay_khai_giang.required'       => 'Vui lòng chọn ngày khai giảng.',
      'ngay_khai_giang.after_or_equal' => 'Ngày khai giảng phải từ hôm nay trở đi.',
      'ngay_ket_thuc_du_kien.required' => 'Vui lòng chọn ngày kết thúc dự kiến.',
      'ngay_ket_thuc_du_kien.after'    => 'Ngày kết thúc phải sau ngày khai giảng.',
      'giang_viens.required'           => 'Vui lòng chọn giảng viên cho tất cả module.',
      'giang_viens.*.required'         => 'Mỗi module phải có giảng viên.',
      'giang_viens.*.exists'           => 'Giảng viên không hợp lệ.',
  ]);

  // Kiểm tra tất cả module đều có GV được chọn
  foreach ($khoaHoc->moduleHocs as $module) {
      if (empty($validated['giang_viens'][$module->id])) {
          return back()
              ->withInput()
              ->with('error', "Module \"{$module->ten_module}\" chưa được chọn giảng viên.");
      }
  }

  DB::transaction(function () use ($khoaHoc, $validated) {
      // Cập nhật ngày + trạng thái khóa học
      $khoaHoc->update([
          'ngay_khai_giang'       => $validated['ngay_khai_giang'],
          'ngay_ket_thuc_du_kien' => $validated['ngay_ket_thuc_du_kien'],
          'trang_thai_van_hanh'   => 'cho_giang_vien',
      ]);

      // Tạo phân công cho từng module
      foreach ($khoaHoc->moduleHocs as $module) {
          $giangVienId = $validated['giang_viens'][$module->id];

          // Xóa phân công cũ nếu có (tránh duplicate)
          PhanCongModuleGiangVien::where('module_hoc_id', $module->id)->delete();

          PhanCongModuleGiangVien::create([
              'khoa_hoc_id'   => $khoaHoc->id,
              'module_hoc_id' => $module->id,
              'giao_vien_id'  => $giangVienId,
              'ngay_phan_cong'=> now(),
              'trang_thai'    => 'cho_xac_nhan',    // ← LUÔN cho_xac_nhan
              'created_by'    => auth()->id(),
          ]);
      }
  });

  return redirect()->route('admin.khoa-hoc.show', $khoaHoc->id)
      ->with('success', 'Đã kích hoạt lớp học! Đang chờ giảng viên xác nhận.');

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC A4 — SỬA VIEW show.blade.php
      (CHỈ sửa 2 phần: block "Mở lớp dự kiến" + dropdown GV)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: resources/views/pages/admin/khoa-hoc/khoa-hoc/show.blade.php

### PHẦN 1 — Block "Mở lớp dự kiến" (thêm VÀO TRONG card kích hoạt, trước form)

TÌM đoạn (bên trong #section-kich-hoat, trước @if($tongModule === 0)):

  Thêm block thông tin dự kiến này VÀO TRÊN form kích hoạt:

```blade
{{-- Block: Thông tin mở lớp dự kiến --}}
@if($khoaHoc->trang_thai_van_hanh === 'cho_mo')
<div class="alert alert-info d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="fas fa-calendar-check fa-lg mt-1 text-info"></i>
    <div>
        <h6 class="fw-bold mb-1">📋 Mở lớp dự kiến</h6>
        <p class="mb-2 small text-muted">
            Điền thông tin bên dưới để <strong>lên kế hoạch mở lớp dự kiến</strong>.
            Sau khi xác nhận, hệ thống sẽ gửi thông báo cho giảng viên — lớp chính thức
            chỉ được mở khi <strong>tất cả giảng viên đã xác nhận dạy</strong>.
        </p>
        <div class="row g-2 small">
            <div class="col-auto">
                <span class="badge bg-light text-dark border">
                    <i class="fas fa-circle text-warning me-1" style="font-size:8px"></i>
                    Bước 1: Admin chọn GV + điền ngày → Kích hoạt
                </span>
            </div>
            <div class="col-auto">
                <span class="badge bg-light text-dark border">
                    <i class="fas fa-circle text-info me-1" style="font-size:8px"></i>
                    Bước 2: GV đăng nhập → Xác nhận dạy module
                </span>
            </div>
            <div class="col-auto">
                <span class="badge bg-light text-dark border">
                    <i class="fas fa-circle text-success me-1" style="font-size:8px"></i>
                    Bước 3: Admin nhận thông báo → Xác nhận mở lớp chính thức
                </span>
            </div>
        </div>
    </div>
</div>
@endif
```

### PHẦN 2 — Dropdown chọn GV hiển thị chuyên ngành + trình độ

TÌM đoạn select chọn GV bên trong bảng phân công (foreach moduleHocs):

THAY từng <option> thành:

```blade
@foreach($giangViens as $gv)
@php
    $tenGv      = $gv->nguoiDung->ho_ten ?? 'N/A';
    $chuyenNganh = $gv->chuyen_nganh ? "({$gv->chuyen_nganh})" : '';
    $trinhDo     = $gv->hoc_vi       ? "[{$gv->hoc_vi}]"       : '';
    $label       = trim("{$tenGv} {$trinhDo} {$chuyenNganh}");
@endphp
<option value="{{ $gv->id }}"
        {{ old("giang_viens.{$module->id}") == $gv->id ? 'selected' : '' }}>
    {{ $label }}
</option>
@endforeach
```

Kết quả hiển thị trong dropdown sẽ là:
  "Nguyễn Văn A [Thạc sĩ] (Lập trình Web)"
  "Trần Thị B [Cử nhân] (Thiết kế UI/UX)"
  "Lê Văn C (Kinh doanh)"   ← nếu không có trình độ

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
YÊU CẦU OUTPUT PHASE A:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. File migration fix default (hoàn chỉnh)
2. Đoạn sửa trong PhanCongModuleGiangVien.php (chỉ phần thêm/sửa)
3. Method show() và kichHoatMau() hoàn chỉnh
4. Đoạn blade thay thế trong show.blade.php (ghi rõ TÌM đoạn nào → THAY bằng gì)
5. Liệt kê file đã tạo/sửa với đường dẫn đầy đủ

✅ CHECKLIST PHASE A:
  ☐ Migration chạy thành công không lỗi
  ☐ Tạo phân công mới → trang_thai = 'cho_xac_nhan' (không phải 'da_nhan')
  ☐ Dropdown GV trong form kích hoạt hiện: "Tên [Trình độ] (Chuyên ngành)"
  ☐ Block "Mở lớp dự kiến" xuất hiện bên trong card #section-kich-hoat
  ☐ 3 bước quy trình (badges) hiển thị đúng trong block dự kiến
  ☐ Submit form thiếu GV → lỗi tiếng Việt, giữ nguyên dữ liệu đã nhập
  ☐ Submit đủ → trang_thai_van_hanh = 'cho_giang_vien', phân công tạo đúng
  ☐ Sau kích hoạt: card #section-kich-hoat biến mất, progress bar xuất hiện

══ DỪNG. CHẠY CHECKLIST. BÁO KẾT QUẢ. MỚI SANG PHASE B ══
```

---

# ══════════════════════════════════════════════════
# PHASE B — GV SIDE: XÁC NHẬN DẠY MODULE
# Mục tiêu:
#   1. Tạo bảng thong_bao (nếu chưa có)
#   2. Trang /giang-vien/phan-cong — GV xem & xác nhận module
#   3. Sau xác nhận → check nếu đủ GV → KH lên 'san_sang'
# ══════════════════════════════════════════════════

```
Bạn là senior Laravel developer.
Phase B bắt đầu sau khi Phase A đã PASS checklist.
KHÔNG sửa lại bất cứ thứ gì của Phase A.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC B1 — MIGRATION: Tạo bảng thong_bao
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Kiểm tra xem bảng thong_bao đã tồn tại chưa bằng:
  php artisan migrate:status | grep thong_bao

Nếu chưa có → tạo migration:
  php artisan make:migration create_thong_bao_table

  up():
    Schema::create('thong_bao', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('nguoi_nhan_id');
        $table->string('tieu_de', 255);
        $table->text('noi_dung');
        $table->enum('loai', ['phan_cong','xac_nhan_gv','mo_lop','he_thong'])
              ->default('he_thong');
        $table->string('url', 500)->nullable();   // redirect khi click
        $table->boolean('da_doc')->default(0);
        $table->timestamps();

        $table->foreign('nguoi_nhan_id')
              ->references('ma_nguoi_dung')
              ->on('nguoi_dung')
              ->onDelete('cascade');

        $table->index(['nguoi_nhan_id', 'da_doc']);
    });

  down(): Schema::dropIfExists('thong_bao');

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC B2 — MODEL ThongBao
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Models/ThongBao.php

  protected $table    = 'thong_bao';
  protected $fillable = ['nguoi_nhan_id','tieu_de','noi_dung','loai','url','da_doc'];

  public function nguoiNhan()
  {
      return $this->belongsTo(NguoiDung::class, 'nguoi_nhan_id', 'ma_nguoi_dung');
  }

  // Scope: chưa đọc
  public function scopeChuaDoc($q) { return $q->where('da_doc', 0); }

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC B3 — HELPER SERVICE: ThongBaoService
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Services/ThongBaoService.php

  class ThongBaoService
  {
      /**
       * Gửi thông báo phân công cho GV
       * Được gọi trong kichHoatMau() ở Phase A
       */
      public static function guiPhanCongGV(
          GiangVien $gv,
          ModuleHoc $module,
          KhoaHoc   $khoaHoc
      ): void {
          ThongBao::create([
              'nguoi_nhan_id' => $gv->nguoi_dung_id,
              'tieu_de'       => "Bạn được phân công dạy module: {$module->ten_module}",
              'noi_dung'      => "Khóa học: {$khoaHoc->ten_khoa_hoc}\n"
                               . "Module: {$module->ten_module} (Mã: {$module->ma_module})\n"
                               . "Dự kiến khai giảng: "
                               . ($khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? '—')
                               . "\nVui lòng vào mục \"Xác nhận phân công\" để xác nhận dạy.",
              'loai'          => 'phan_cong',
              'url'           => route('giang-vien.phan-cong.index'),
          ]);
      }

      /**
       * Gửi thông báo cho Admin khi TẤT CẢ GV xác nhận
       */
      public static function guiSanSangChoAdmin(KhoaHoc $khoaHoc): void
      {
          $admins = NguoiDung::where('vai_tro', 'admin')->get();
          foreach ($admins as $admin) {
              ThongBao::create([
                  'nguoi_nhan_id' => $admin->ma_nguoi_dung,
                  'tieu_de'       => "✅ Lớp học sẵn sàng: {$khoaHoc->ten_khoa_hoc}",
                  'noi_dung'      => "Tất cả giảng viên đã xác nhận dạy cho khóa học "
                                   . "\"{$khoaHoc->ten_khoa_hoc}\".\n"
                                   . "Bạn có thể xác nhận mở lớp chính thức.",
                  'loai'          => 'xac_nhan_gv',
                  'url'           => route('admin.khoa-hoc.show', $khoaHoc->id),
              ]);
          }
      }
  }

QUAN TRỌNG: Quay lại method kichHoatMau() (Phase A) và thêm lệnh gửi thông báo
vào BÊN TRONG DB::transaction(), SAU khi tạo PhanCongModuleGiangVien:

  // Gửi thông báo GV (thêm sau vòng foreach phân công)
  $giangVienObj = GiangVien::find($giangVienId);
  ThongBaoService::guiPhanCongGV($giangVienObj, $module, $khoaHoc);

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC B4 — CONTROLLER: GiangVienPhanCongController
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Http/Controllers/GiangVien/PhanCongController.php
Namespace: App\Http\Controllers\GiangVien

  MIDDLEWARE: auth + check vai_tro = 'giang_vien'

  ### index():
    $giangVien = auth()->user()->giangVien;
    if (!$giangVien) {
        return redirect()->route('dashboard')
            ->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
    }

    $phanCongs = PhanCongModuleGiangVien::with([
            'moduleHoc.khoaHoc.monHoc',
        ])
        ->where('giao_vien_id', $giangVien->id)
        ->orderByRaw("FIELD(trang_thai, 'cho_xac_nhan', 'da_nhan', 'tu_choi')")
        ->orderBy('created_at', 'desc')
        ->get();

    return view('pages.giang-vien.phan-cong.index', compact('phanCongs'));

  ### xacNhan(Request $request, $phanCongId):
    $giangVien = auth()->user()->giangVien;
    $phanCong  = PhanCongModuleGiangVien::where('id', $phanCongId)
        ->where('giao_vien_id', $giangVien->id)
        ->firstOrFail();

    if ($phanCong->trang_thai !== 'cho_xac_nhan') {
        return back()->with('error', 'Phân công này đã được xử lý rồi.');
    }

    $validated = $request->validate([
        'hanh_dong' => 'required|in:da_nhan,tu_choi',
        'ghi_chu'   => 'nullable|string|max:500',
    ], [
        'hanh_dong.required' => 'Vui lòng chọn hành động.',
        'hanh_dong.in'       => 'Hành động không hợp lệ.',
    ]);

    DB::transaction(function () use ($phanCong, $validated) {
        $phanCong->update([
            'trang_thai' => $validated['hanh_dong'],
            'ghi_chu'    => $validated['ghi_chu'],
        ]);

        if ($validated['hanh_dong'] === 'da_nhan') {
            // Kiểm tra xem TẤT CẢ module của KH đã có GV xác nhận chưa
            $khoaHoc    = $phanCong->khoaHoc()->with('moduleHocs.phanCongGiangViens')->first();
            $tongModule = $khoaHoc->moduleHocs->count();
            $daXacNhan  = $khoaHoc->moduleHocs->filter(
                fn($m) => $m->phanCongGiangViens
                    ->where('trang_thai', 'da_nhan')->count() > 0
            )->count();

            if ($tongModule > 0 && $daXacNhan >= $tongModule) {
                $khoaHoc->update(['trang_thai_van_hanh' => 'san_sang']);
                ThongBaoService::guiSanSangChoAdmin($khoaHoc);
            }
        }
    });

    $msg = $validated['hanh_dong'] === 'da_nhan'
        ? 'Đã xác nhận dạy module thành công!'
        : 'Đã từ chối phân công.';

    return back()->with('success', $msg);

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC B5 — ROUTES (thêm vào web.php)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Tìm group route của giảng viên (hoặc tạo mới):

  // Giảng viên routes
  Route::middleware(['auth'])->prefix('giang-vien')->name('giang-vien.')->group(function () {
      // Xác nhận phân công
      Route::get('/phan-cong',             [PhanCongController::class, 'index'])
           ->name('phan-cong.index');
      Route::post('/phan-cong/{id}/xac-nhan', [PhanCongController::class, 'xacNhan'])
           ->name('phan-cong.xac-nhan');
  });

  Thêm use App\Http\Controllers\GiangVien\PhanCongController; ở đầu web.php

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC B6 — VIEW: resources/views/pages/giang-vien/phan-cong/index.blade.php
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

@extends('layouts.app')
@section('title', 'Xác nhận phân công giảng dạy')

Layout tổng thể:
  [HEADER]: "Xác nhận phân công giảng dạy"
  [FLASH]:  session('success') + session('error')

  [TABS]: 3 tab lọc theo trang_thai
    - "Chờ xác nhận" (badge đỏ = số lượng)
    - "Đã xác nhận"
    - "Đã từ chối"

  [BẢNG — tab "Chờ xác nhận"]:
    Cột: # | Khóa học | Module | Ngày dự kiến khai giảng | Ngày phân công | Hành động

    Cột Hành động:
      Form POST → giang-vien.phan-cong.xac-nhan (với {id})
      @csrf
      Input hidden name="hanh_dong" (thay đổi qua JS khi bấm nút)

      Textarea name="ghi_chu" placeholder="Ghi chú (nếu từ chối)..." rows=2 class="form-control form-control-sm mb-2"

      2 nút:
        ① <button type="submit" name="hanh_dong" value="da_nhan"
               class="btn btn-success btn-sm me-1"
               onclick="return confirm('Xác nhận dạy module này?')">
             <i class="fas fa-check me-1"></i> Xác nhận dạy
           </button>
        ② <button type="submit" name="hanh_dong" value="tu_choi"
               class="btn btn-outline-danger btn-sm"
               onclick="return confirm('Bạn chắc chắn từ chối?')">
             <i class="fas fa-times me-1"></i> Từ chối
           </button>

  [BẢNG — tab "Đã xác nhận"]:
    Cột: # | Khóa học | Module | Ngày khai giảng | Ngày xác nhận | Badge "Đã xác nhận"
    (chỉ đọc, không có form)

  [BẢNG — tab "Đã từ chối"]:
    Cột: # | Khóa học | Module | Ghi chú | Badge "Đã từ chối"

  [EMPTY STATE]: nếu không có phân công nào → alert-info "Chưa có phân công nào."

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
YÊU CẦU OUTPUT PHASE B:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. File migration thong_bao hoàn chỉnh
2. File app/Models/ThongBao.php hoàn chỉnh
3. File app/Services/ThongBaoService.php hoàn chỉnh
4. Đoạn bổ sung vào kichHoatMau() (Phase A) để gửi thông báo GV
5. File app/Http/Controllers/GiangVien/PhanCongController.php hoàn chỉnh
6. Đoạn route thêm vào web.php (ghi rõ vị trí)
7. File view pages/giang-vien/phan-cong/index.blade.php hoàn chỉnh
8. Danh sách file tạo/sửa với đường dẫn đầy đủ

✅ CHECKLIST PHASE B:
  ☐ Migration thong_bao chạy thành công
  ☐ Admin kích hoạt KH → GV nhận thông báo (bản ghi trong DB)
  ☐ GV đăng nhập → /giang-vien/phan-cong → thấy danh sách module chờ xác nhận
  ☐ GV bấm "Xác nhận dạy" → trang_thai = 'da_nhan'
  ☐ GV bấm "Từ chối" → trang_thai = 'tu_choi', ghi_chu lưu đúng
  ☐ GV xác nhận module cuối cùng → KH tự động lên trang_thai_van_hanh = 'san_sang'
  ☐ Khi KH lên 'san_sang' → admin nhận thông báo (bản ghi thong_bao cho admin)
  ☐ Tab "Chờ xác nhận" / "Đã xác nhận" / "Từ chối" hoạt động đúng
  ☐ Module đã xử lý → không còn nút hành động
  ☐ GV không thuộc phân công → không thấy → 404

══ DỪNG. CHẠY CHECKLIST. BÁO KẾT QUẢ. MỚI SANG PHASE C ══
```

---

# ══════════════════════════════════════════════════
# PHASE C — ADMIN SIDE: NHẬN THÔNG BÁO & MỞ LỚP CHÍNH THỨC
# Mục tiêu:
#   1. Badge thông báo chưa đọc trên navbar
#   2. Trang /admin/thong-bao + đánh dấu đã đọc
#   3. Admin xác nhận mở lớp → trang_thai_van_hanh = 'dang_day'
#   4. Unlock section thêm học sinh
# ══════════════════════════════════════════════════

```
Bạn là senior Laravel developer.
Phase C bắt đầu sau khi Phase B đã PASS checklist.
KHÔNG sửa lại bất cứ thứ gì của Phase A, B.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC C1 — BADGE THÔNG BÁO TRÊN NAVBAR
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: resources/views/layouts/app.blade.php  (hoặc partial navbar)

Tìm vị trí navbar (thường là phần user menu bên phải).
Thêm icon chuông TRƯỚC user dropdown:

```blade
@auth
@php
    $soTBChuaDoc = \App\Models\ThongBao::where('nguoi_nhan_id', auth()->id())
        ->where('da_doc', 0)
        ->count();
@endphp
<li class="nav-item me-2 position-relative">
    <a class="nav-link p-2" href="{{ route('thong-bao.index') }}" title="Thông báo">
        <i class="fas fa-bell fs-5"></i>
        @if($soTBChuaDoc > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size:10px">
            {{ $soTBChuaDoc > 99 ? '99+' : $soTBChuaDoc }}
        </span>
        @endif
    </a>
</li>
@endauth
```

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC C2 — CONTROLLER: ThongBaoController (dùng chung Admin + GV)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Http/Controllers/ThongBaoController.php

  ### index():
    $thongBaos = ThongBao::where('nguoi_nhan_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    // Đánh dấu tất cả là đã đọc khi vào trang
    ThongBao::where('nguoi_nhan_id', auth()->id())
        ->where('da_doc', 0)
        ->update(['da_doc' => 1]);

    return view('pages.thong-bao.index', compact('thongBaos'));

  ### docMot($id):
    $tb = ThongBao::where('id', $id)
        ->where('nguoi_nhan_id', auth()->id())
        ->firstOrFail();
    $tb->update(['da_doc' => 1]);

    return redirect($tb->url ?? route('thong-bao.index'));

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC C3 — CONTROLLER: Admin xác nhận mở lớp chính thức
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: app/Http/Controllers/Admin/KhoaHocManagementController.php

Thêm method xacNhanMoLop($id):

  public function xacNhanMoLop($id)
  {
      $khoaHoc = KhoaHoc::with([
          'moduleHocs.phanCongGiangViens'
      ])->findOrFail($id);

      // Guard: chỉ KH đang ở trạng thái 'san_sang'
      if ($khoaHoc->trang_thai_van_hanh !== 'san_sang') {
          return redirect()->route('admin.khoa-hoc.show', $id)
              ->with('error', 'Khóa học chưa sẵn sàng để mở lớp. Vui lòng chờ giảng viên xác nhận.');
      }

      // Kiểm tra lại: tất cả module đều có GV da_nhan
      $tongModule = $khoaHoc->moduleHocs->count();
      $daXacNhan  = $khoaHoc->moduleHocs->filter(
          fn($m) => $m->phanCongGiangViens->where('trang_thai', 'da_nhan')->count() > 0
      )->count();

      if ($daXacNhan < $tongModule) {
          return redirect()->route('admin.khoa-hoc.show', $id)
              ->with('error', 'Vẫn còn module chưa được giảng viên xác nhận.');
      }

      DB::transaction(function () use ($khoaHoc) {
          $khoaHoc->update(['trang_thai_van_hanh' => 'dang_day']);

          // Thông báo cho tất cả GV được phân công
          $giangVienIds = $khoaHoc->moduleHocs
              ->flatMap(fn($m) => $m->phanCongGiangViens->where('trang_thai', 'da_nhan'))
              ->pluck('giao_vien_id')
              ->unique();

          $giangViens = GiangVien::whereIn('id', $giangVienIds)->get();
          foreach ($giangViens as $gv) {
              ThongBao::create([
                  'nguoi_nhan_id' => $gv->nguoi_dung_id,
                  'tieu_de'       => "🎉 Lớp học đã mở: {$khoaHoc->ten_khoa_hoc}",
                  'noi_dung'      => "Admin đã xác nhận mở lớp \"{$khoaHoc->ten_khoa_hoc}\". "
                                   . "Lớp bắt đầu từ "
                                   . ($khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? '—') . ".",
                  'loai'          => 'mo_lop',
                  'url'           => route('giang-vien.phan-cong.index'),
              ]);
          }
      });

      return redirect()->route('admin.khoa-hoc.show', $id)
          ->with('success', 'Đã mở lớp học chính thức! Bạn có thể bắt đầu thêm học sinh.');
  }

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC C4 — ROUTES (thêm vào web.php)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// Thông báo (dùng chung mọi vai_tro)
Route::middleware(['auth'])->group(function () {
    Route::get('/thong-bao',        [ThongBaoController::class, 'index'])  ->name('thong-bao.index');
    Route::get('/thong-bao/{id}',   [ThongBaoController::class, 'docMot']) ->name('thong-bao.doc-mot');
});

// Admin — mở lớp chính thức (thêm vào trong group prefix admin/khoa-hoc)
Route::post('/{id}/xac-nhan-mo-lop', [KhoaHocManagementController::class, 'xacNhanMoLop'])
     ->name('khoa-hoc.xac-nhan-mo-lop');

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC C5 — NÂNG CẤP show.blade.php (Admin KH)
      Thêm 2 block:
      ① Card trạng thái 'san_sang' với nút "Xác nhận mở lớp"
      ② Card 'dang_day' — section thêm học sinh (placeholder)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: resources/views/pages/admin/khoa-hoc/khoa-hoc/show.blade.php

### THÊM BLOCK ① — Sau progress bar, khi trang_thai_van_hanh = 'san_sang':

```blade
@if($khoaHoc->trang_thai_van_hanh === 'san_sang')
<div class="card border-primary shadow-sm mb-4" id="section-mo-lop">
    <div class="card-header bg-primary text-white py-3">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-flag-checkered me-2"></i>
            Tất cả giảng viên đã xác nhận — Sẵn sàng mở lớp!
        </h6>
    </div>
    <div class="card-body">
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle me-2"></i>
            <strong>{{ $tongModule }}/{{ $tongModule }}</strong> module đã có giảng viên xác nhận dạy.
            Bạn có thể xác nhận mở lớp học chính thức.
        </div>

        {{-- Tóm tắt thông tin lớp --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="border rounded p-3 text-center bg-light">
                    <div class="text-muted small mb-1">Ngày khai giảng</div>
                    <div class="fw-bold text-primary fs-5">
                        {{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? '—' }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center bg-light">
                    <div class="text-muted small mb-1">Ngày kết thúc</div>
                    <div class="fw-bold text-secondary fs-5">
                        {{ $khoaHoc->ngay_ket_thuc_du_kien?->format('d/m/Y') ?? '—' }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center bg-light">
                    <div class="text-muted small mb-1">Tổng module</div>
                    <div class="fw-bold text-success fs-5">{{ $tongModule }}</div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.khoa-hoc.xac-nhan-mo-lop', $khoaHoc->id) }}"
              method="POST"
              onsubmit="return confirm('Xác nhận mở lớp chính thức? Thao tác này không thể hoàn tác.')">
            @csrf
            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold">
                <i class="fas fa-rocket me-2"></i>
                Xác nhận mở lớp chính thức
            </button>
            <small class="text-muted ms-3">
                Sau khi xác nhận, bạn có thể bắt đầu thêm học sinh vào lớp.
            </small>
        </form>
    </div>
</div>
@endif
```

### THÊM BLOCK ② — Khi trang_thai_van_hanh = 'dang_day' → unlock section học sinh:

```blade
@if($khoaHoc->trang_thai_van_hanh === 'dang_day')
<div class="card border-success shadow-sm mb-4" id="section-hoc-sinh">
    <div class="card-header bg-success text-white py-3">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-user-graduate me-2"></i>
            Lớp đang hoạt động — Quản lý học sinh
        </h6>
    </div>
    <div class="card-body">
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle me-2"></i>
            Lớp học đã được mở chính thức từ
            <strong>{{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') }}</strong>.
        </div>

        {{-- Placeholder — Phase sau sẽ implement --}}
        <div class="text-center py-4 border rounded bg-light">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h6 class="text-muted">Chức năng thêm học sinh đang được phát triển</h6>
            <p class="small text-muted mb-0">
                Sẽ triển khai ở phase tiếp theo: tìm kiếm + thêm học sinh vào lớp,
                xem danh sách học sinh, gửi thông báo khai giảng.
            </p>
        </div>
    </div>
</div>
@endif
```

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
BƯỚC C6 — VIEW: Trang thông báo
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

File: resources/views/pages/thong-bao/index.blade.php

@extends('layouts.app')
@section('title', 'Thông báo')

Layout:
  [HEADER]: "Thông báo của bạn"
  [FLASH]:  session('success'), session('error')

  [DANH SÁCH]:
    @forelse($thongBaos as $tb)
    <a href="{{ route('thong-bao.doc-mot', $tb->id) }}"
       class="list-group-item list-group-item-action {{ $tb->da_doc ? '' : 'list-group-item-warning' }}">
        <div class="d-flex w-100 justify-content-between">
            <h6 class="mb-1 fw-bold">
                @if(!$tb->da_doc)
                    <span class="badge bg-danger me-1" style="font-size:9px">Mới</span>
                @endif
                {{ $tb->tieu_de }}
            </h6>
            <small class="text-muted">{{ $tb->created_at->diffForHumans() }}</small>
        </div>
        <p class="mb-1 small text-muted" style="white-space: pre-line">{{ $tb->noi_dung }}</p>
    </a>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="fas fa-bell-slash fa-3x mb-3"></i>
        <p>Chưa có thông báo nào.</p>
    </div>
    @endforelse

    {{ $thongBaos->links() }}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
YÊU CẦU OUTPUT PHASE C:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Đoạn blade badge chuông thêm vào navbar (ghi rõ TÌM đoạn nào → THAY bằng gì)
2. File app/Http/Controllers/ThongBaoController.php hoàn chỉnh
3. Method xacNhanMoLop() hoàn chỉnh trong KhoaHocManagementController
4. Đoạn route mới trong web.php (ghi rõ vị trí)
5. Đoạn blade 2 block (san_sang + dang_day) thêm vào show.blade.php
6. File view pages/thong-bao/index.blade.php hoàn chỉnh
7. Danh sách file tạo/sửa với đường dẫn đầy đủ

✅ CHECKLIST PHASE C:
  ☐ Badge chuông trên navbar hiện số thông báo chưa đọc
  ☐ Click chuông → /thong-bao → danh sách thông báo
  ☐ Thông báo chưa đọc nổi bật (màu vàng), đã đọc = trắng
  ☐ Click 1 thông báo → đánh dấu đã đọc → redirect đến url
  ☐ Vào /thong-bao → đánh dấu TẤT CẢ đã đọc
  ☐ KH ở 'san_sang' → show page hiện card "Xác nhận mở lớp" màu xanh dương
  ☐ KH ở 'san_sang' → KHÔNG hiện card kích hoạt (cho_mo) nữa
  ☐ Admin bấm "Xác nhận mở lớp" → trang_thai_van_hanh = 'dang_day'
  ☐ Sau khi mở lớp → GV nhận thông báo "Lớp đã mở"
  ☐ KH ở 'dang_day' → show page hiện card "Quản lý học sinh" (placeholder)
  ☐ KH ở 'cho_giang_vien' → chỉ hiện progress bar, KHÔNG có nút xác nhận

══ DỪNG. CHẠY CHECKLIST. BÁO KẾT QUẢ. ══
```

---

## 📋 QUY TẮC BẮT BUỘC CHO GEMINI (áp dụng tất cả phase)

```
1.  Chỉ làm 1 phase một lúc. Dừng sau phase, đợi confirm checklist.
2.  Sửa file cũ: "TÌM đoạn X → THAY bằng Y". Ghi rõ dòng hoặc cấu trúc cần tìm.
3.  File mới: 100% đầy đủ. Không dùng // TODO, // implement later, placeholder code.
4.  Validation messages: 100% tiếng Việt.
5.  Mọi DB write: bọc trong DB::transaction().
6.  Eager loading: dùng with() đúng chỗ, không để N+1 query.
7.  Conflict với code cũ: báo cụ thể, developer quyết định, không tự ý xử lý.
8.  Cuối mỗi phase: liệt kê TOÀN BỘ file đã tạo/sửa với đường dẫn đầy đủ.
9.  Không thêm feature ngoài scope phase đang làm — ghi chú để bàn sau.
10. Import/use statement: thêm đủ vào đầu mỗi file controller.

► BẮT ĐẦU PHASE A. Sau khi confirm PASS → làm Phase B → Phase C.
```

---

## 🗺️ TỔNG HỢP FLOW NGHIỆP VỤ (để Gemini nắm toàn cảnh)

```
[Admin] Tạo KH mẫu + setup module
    ↓ trang_thai_van_hanh = 'cho_mo'
    ↓
[Admin] Vào /admin/khoa-hoc/{id}#section-kich-hoat
        → Xem block "Mở lớp dự kiến" (3 bước quy trình)
        → Chọn GV cho từng module (hiển thị: Tên [Trình độ] (Chuyên ngành))
        → Điền ngày khai giảng + kết thúc dự kiến
        → Bấm "Xác nhận kích hoạt"
    ↓ trang_thai_van_hanh = 'cho_giang_vien'
    ↓ phan_cong tạo với trang_thai = 'cho_xac_nhan'
    ↓ Thông báo gửi cho từng GV
    ↓
[GiangVien] Đăng nhập → thấy badge chuông đỏ
            → Vào /giang-vien/phan-cong
            → Xem tab "Chờ xác nhận": danh sách module cần xác nhận
            → Bấm "Xác nhận dạy" (có thể nhập ghi chú)
    ↓ phan_cong.trang_thai = 'da_nhan'
    ↓ Nếu ĐÂY LÀ GV CUỐI CÙNG xác nhận:
    ↓   → KH tự động lên trang_thai_van_hanh = 'san_sang'
    ↓   → Thông báo gửi cho TẤT CẢ Admin
    ↓
[Admin] Thấy badge chuông + thông báo "Lớp học sẵn sàng"
        → Vào /admin/khoa-hoc/{id}
        → Thấy card "Xác nhận mở lớp" (màu xanh dương)
        → Xem tóm tắt: ngày khai giảng, kết thúc, tổng module
        → Bấm "Xác nhận mở lớp chính thức"
    ↓ trang_thai_van_hanh = 'dang_day'
    ↓ Thông báo gửi cho tất cả GV được phân công
    ↓
[Admin] Thấy card "Quản lý học sinh" (dang_day)
        → Bắt đầu thêm học sinh vào lớp [Phase tiếp theo]
```
