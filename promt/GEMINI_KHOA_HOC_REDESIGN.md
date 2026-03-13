# 🎯 GEMINI AGENT — REDESIGN QUẢN LÝ KHÓA HỌC
**Dự án:** thuctap_khaitri · Laravel 11 · Bootstrap 5 · Blade · MySQL  
**Quy tắc:** Làm xong 1 phase → chạy hết checklist → báo kết quả → mới làm phase tiếp theo

---

## 📦 DATABASE HIỆN TẠI

```
TABLE khoa_hoc
  id, mon_hoc_id FK→mon_hoc.id (cascade)
  ma_khoa_hoc VARCHAR(50) UNIQUE
  ten_khoa_hoc VARCHAR(200)
  mo_ta_ngan VARCHAR(500) nullable, mo_ta_chi_tiet TEXT nullable
  hinh_anh VARCHAR(255) nullable
  cap_do ENUM('co_ban','trung_binh','nang_cao') default 'co_ban'
  tong_so_module INT default 0
  trang_thai BOOLEAN default 1
  timestamps

TABLE module_hoc
  id, khoa_hoc_id FK cascade, ma_module UNIQUE
  ten_module, mo_ta, thu_tu_module INT
  thoi_luong_du_kien INT nullable (phút), trang_thai BOOLEAN, timestamps

TABLE phan_cong_module_giang_vien
  id, khoa_hoc_id, module_hoc_id, giao_vien_id
  ngay_phan_cong DATETIME
  trang_thai ENUM('cho_xac_nhan','da_nhan','tu_choi') default 'da_nhan'
  ghi_chu TEXT nullable
  created_by FK→nguoi_dung.ma_nguoi_dung · timestamps
  UNIQUE(module_hoc_id, giao_vien_id)

Models: KhoaHoc, MonHoc, ModuleHoc, GiangVien, NguoiDung
Auth: NguoiDung · PK = ma_nguoi_dung · vai_tro = admin/giang_vien/hoc_vien
Layout: @extends('layouts.app')
CSS: vip-card · vip-card-header · vip-card-body · vip-form-control
Icons: Font Awesome 5
```

---

## 🏢 NGHIỆP VỤ MỚI — ĐỌC KỸ TRƯỚC KHI CODE

### Có 2 loại khóa học:

**📋 Loại 1 — KHÓA HỌC MẪU** `loai = 'mau'`
> Admin chuẩn bị sẵn khóa học (template) với đầy đủ module theo chương trình.
> **Chưa có GV, chưa có ngày khai giảng, chưa có học viên.**

Quy trình:
1. Admin tạo KH mẫu + setup tất cả module
2. KH nằm trạng thái **"Chờ mở"**
3. Khi đủ học viên đăng ký → Admin vào KH mẫu → bấm **"Kích hoạt thành lớp học"**:
   - Chọn GV cho từng module
   - Điền ngày khai giảng + ngày kết thúc
4. Hệ thống gửi thông báo GV xác nhận
5. **KH mẫu gốc giữ nguyên** để dùng lại đợt sau

**⚡ Loại 2 — KHÓA HỌC TRỰC TIẾP** `loai = 'truc_tiep'`
> Tạo lớp học ngay: setup module + chọn GV + điền ngày trong 1 lần tạo.

Quy trình:
1. Admin tạo KH + setup module + chọn GV + điền ngày → Submit
2. Hệ thống tạo phân công `trang_thai='cho_xac_nhan'`, gửi thông báo GV
3. Khi đủ GV xác nhận → KH tự chuyển "Sẵn sàng"

### Vòng đời trạng thái `trang_thai_van_hanh`:
```
cho_mo          → KH mẫu mới tạo, chưa kích hoạt
cho_giang_vien  → Đã phân công GV, đang chờ GV xác nhận  
san_sang        → Tất cả GV xác nhận, chờ khai giảng
dang_day        → Đang dạy  [Phase sau]
ket_thuc        → Kết thúc  [Phase sau]
```

### Trang index `/admin/khoa-hoc` — 2 Tab:
| Tab | Hiển thị |
|-----|----------|
| **"Khóa học đã tạo sẵn"** | Tất cả KH `loai='mau'` |
| **"Đang hoạt động"** | KH có `trang_thai_van_hanh` IN (`cho_giang_vien`, `san_sang`, `dang_day`) |

---

# ══════════════════════════════════
# PHASE 0 — MIGRATION
# ══════════════════════════════════

```
Bạn là senior Laravel developer.
Thêm các cột mới vào bảng khoa_hoc.

BƯỚC 1 — Tạo migration:
  php artisan make:migration add_loai_and_lifecycle_to_khoa_hoc_table

  up():
    Schema::table('khoa_hoc', function (Blueprint $table) {
        $table->enum('loai', ['mau','truc_tiep'])
              ->default('mau')->after('cap_do');
        $table->enum('trang_thai_van_hanh',
                     ['cho_mo','cho_giang_vien','san_sang','dang_day','ket_thuc'])
              ->default('cho_mo')->after('loai');
        $table->date('ngay_khai_giang')->nullable()->after('trang_thai_van_hanh');
        $table->date('ngay_ket_thuc_du_kien')->nullable()->after('ngay_khai_giang');
        $table->text('ghi_chu_noi_bo')->nullable()->after('ngay_ket_thuc_du_kien');
    });

  down():
    Schema::table('khoa_hoc', function (Blueprint $table) {
        $table->dropColumn(['loai','trang_thai_van_hanh','ngay_khai_giang',
                            'ngay_ket_thuc_du_kien','ghi_chu_noi_bo']);
    });

BƯỚC 2 — Sửa app/Models/KhoaHoc.php:

  Thêm vào $fillable:
    'loai','trang_thai_van_hanh','ngay_khai_giang','ngay_ket_thuc_du_kien','ghi_chu_noi_bo'

  Thêm $casts:
    'ngay_khai_giang'       => 'date',
    'ngay_ket_thuc_du_kien' => 'date',

  Thêm scopes:
    public function scopeMau($q)          { return $q->where('loai','mau'); }
    public function scopeTrucTiep($q)     { return $q->where('loai','truc_tiep'); }
    public function scopeDangHoatDong($q) {
        return $q->whereIn('trang_thai_van_hanh',['cho_giang_vien','san_sang','dang_day']);
    }

  Thêm accessor getTrangThaiVanHanhLabelAttribute(): array
    $map = [
      'cho_mo'         => ['label'=>'Chờ mở',          'color'=>'secondary','icon'=>'fa-pause-circle'],
      'cho_giang_vien' => ['label'=>'Chờ GV xác nhận', 'color'=>'warning',  'icon'=>'fa-clock'],
      'san_sang'       => ['label'=>'Sẵn sàng',         'color'=>'success',  'icon'=>'fa-check-circle'],
      'dang_day'       => ['label'=>'Đang dạy',          'color'=>'primary',  'icon'=>'fa-play-circle'],
      'ket_thuc'       => ['label'=>'Kết thúc',          'color'=>'dark',     'icon'=>'fa-flag-checkered'],
    ];
    return $map[$this->trang_thai_van_hanh]
        ?? ['label'=>'Không xác định','color'=>'secondary','icon'=>'fa-question'];

  Thêm accessor getLoaiLabelAttribute(): array
    return [
      'mau'       => ['label'=>'Khóa mẫu',  'color'=>'info'],
      'truc_tiep' => ['label'=>'Trực tiếp', 'color'=>'primary'],
    ][$this->loai] ?? ['label'=>'?','color'=>'secondary'];

  Thêm method isFullyAssigned(): bool
    if ($this->tong_so_module === 0) return false;
    $co = $this->moduleHocs()
        ->whereHas('phanCongGiangViens', fn($q) => $q->where('trang_thai','da_nhan'))
        ->count();
    return $co >= $this->tong_so_module;

  Thêm method checkAndUpdateTrangThai(): void
    if ($this->isFullyAssigned()) {
        $this->update(['trang_thai_van_hanh' => 'san_sang']);
    }

YÊU CẦU OUTPUT:
  1. File migration hoàn chỉnh
  2. Đoạn code thêm vào KhoaHoc.php

✅ CHECKLIST PHASE 0:
  ☐ php artisan migrate chạy không lỗi
  ☐ Bảng khoa_hoc có đủ 5 cột mới
  ☐ KH cũ: loai='mau', trang_thai_van_hanh='cho_mo'
  ☐ $kh->trang_thai_van_hanh_label trả về array đủ key label/color/icon
  ☐ $kh->loai_label trả về array đủ key label/color
  ☐ KhoaHoc::mau()->get() và ::dangHoatDong()->get() chạy không lỗi
  ☐ $kh->isFullyAssigned() trả về false với KH chưa có phân công
```
**══ DỪNG. CHECKLIST PHASE 0. BÁO KẾT QUẢ. MỚI LÀM PHASE 1 ══**

---

# ══════════════════════════════════
# PHASE 1 — INDEX: 2 TAB
# ══════════════════════════════════

```
Bạn là senior Laravel developer.
Viết lại index() và view index.blade.php.

BƯỚC 1 — Sửa KhoaHocManagementController::index():

  public function index(Request $request)
  {
      $tab    = $request->get('tab', 'mau');
      $search = trim($request->get('search', ''));

      $applySearch = fn($q) => $q->when($search,
          fn($q) => $q->where('ten_khoa_hoc','like',"%{$search}%")
                      ->orWhere('ma_khoa_hoc','like',"%{$search}%")
      );

      $khoaHocMau = KhoaHoc::with('monHoc')
          ->mau()->tap($applySearch)
          ->orderByDesc('created_at')
          ->paginate(12, ['*'], 'mau_page')
          ->appends($request->query());

      $khoaHocHoatDong = KhoaHoc::with('monHoc')
          ->dangHoatDong()->tap($applySearch)
          ->withCount([
              'moduleHocs as tong_module',
              'moduleHocs as module_da_nhan' => fn($q) =>
                  $q->whereHas('phanCongGiangViens',
                      fn($q) => $q->where('trang_thai','da_nhan')),
          ])
          ->orderByDesc('updated_at')
          ->paginate(12, ['*'], 'hd_page')
          ->appends($request->query());

      $stats = [
          'tong_mau'  => KhoaHoc::mau()->count(),
          'cho_mo'    => KhoaHoc::where('trang_thai_van_hanh','cho_mo')->count(),
          'hoat_dong' => KhoaHoc::dangHoatDong()->count(),
          'san_sang'  => KhoaHoc::where('trang_thai_van_hanh','san_sang')->count(),
      ];

      return view('pages.admin.khoa-hoc.khoa-hoc.index',
          compact('khoaHocMau','khoaHocHoatDong','stats','tab','search'));
  }

BƯỚC 2 — Viết lại view index.blade.php:
File: resources/views/pages/admin/khoa-hoc/khoa-hoc/index.blade.php

Layout:
  [Breadcrumb: Admin / Quản lý Khóa học]
  [Header: Tiêu đề | 2 nút tạo mới góc phải]
  [4 Stat Cards hàng ngang]
  [Search bar]
  [Nav Tabs]
  [Tab content]

2 nút tạo mới:
  <a href="{{ route('admin.khoa-hoc.create', ['loai'=>'mau']) }}" class="btn btn-outline-info me-2">
      <i class="fas fa-copy me-1"></i> Tạo khóa học mẫu
  </a>
  <a href="{{ route('admin.khoa-hoc.create', ['loai'=>'truc_tiep']) }}" class="btn btn-primary">
      <i class="fas fa-bolt me-1"></i> Tạo khóa học trực tiếp
  </a>

4 Stat cards (col-md-3):
  Card 1: fa-copy    | "Khóa học mẫu"    | $stats['tong_mau']  | bg-info
  Card 2: fa-pause   | "Chờ mở"          | $stats['cho_mo']    | bg-secondary
  Card 3: fa-play    | "Đang hoạt động"  | $stats['hoat_dong'] | bg-primary
  Card 4: fa-check   | "Sẵn sàng khai giảng" | $stats['san_sang'] | bg-success

Search bar:
  <form method="GET" action="{{ route('admin.khoa-hoc.index') }}" class="d-flex gap-2 mb-3">
      <input type="hidden" name="tab" value="{{ $tab }}">
      <input type="text" name="search" class="form-control" style="max-width:320px"
             placeholder="Tìm theo tên, mã khóa học..." value="{{ $search }}">
      <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Tìm</button>
      @if($search)
          <a href="{{ route('admin.khoa-hoc.index', ['tab'=>$tab]) }}"
             class="btn btn-outline-secondary">Xóa lọc</a>
      @endif
  </form>

Nav Tabs:
  <ul class="nav nav-tabs mb-0">
    <li class="nav-item">
      <a class="nav-link {{ $tab==='mau' ? 'active' : '' }}"
         href="{{ request()->fullUrlWithQuery(['tab'=>'mau','mau_page'=>1]) }}">
          <i class="fas fa-copy me-1"></i> Khóa học đã tạo sẵn
          <span class="badge bg-info ms-1">{{ $stats['tong_mau'] }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab==='hoat_dong' ? 'active' : '' }}"
         href="{{ request()->fullUrlWithQuery(['tab'=>'hoat_dong','hd_page'=>1]) }}">
          <i class="fas fa-play me-1"></i> Đang hoạt động
          <span class="badge bg-primary ms-1">{{ $stats['hoat_dong'] }}</span>
      </a>
    </li>
  </ul>

Tab "mau" (hiện khi $tab === 'mau'):
  Bảng cột: STT | Mã KH | Tên khóa học | Môn học | Cấp độ | Modules | Trạng thái | Ngày tạo | Thao tác

  Badge trạng thái:
    <span class="badge bg-{{ $kh->trang_thai_van_hanh_label['color'] }}">
        <i class="fas {{ $kh->trang_thai_van_hanh_label['icon'] }} me-1"></i>
        {{ $kh->trang_thai_van_hanh_label['label'] }}
    </span>

  Cột Thao tác:
    - "Xem" (btn-info btn-sm) → show
    - "Kích hoạt" (btn-success btn-sm, icon fa-rocket):
        CHỈ hiện khi trang_thai_van_hanh === 'cho_mo'
        href="{{ route('admin.khoa-hoc.show', $kh->id) }}#section-kich-hoat"
        title="Tạo lớp học từ template này"
    - "Sửa" (btn-warning btn-sm): CHỈ khi trang_thai_van_hanh === 'cho_mo'
    - "Xóa" (btn-danger btn-sm + confirm): CHỈ khi trang_thai_van_hanh === 'cho_mo'

  Pagination: {{ $khoaHocMau->links() }}
  Empty state nếu không có dữ liệu

Tab "hoat_dong" (hiện khi $tab === 'hoat_dong'):
  Bảng cột: STT | Mã KH | Tên | Môn học | Loại | GV xác nhận | Ngày khai giảng | Trạng thái | Thao tác

  Cột Loại:
    <span class="badge bg-{{ $kh->loai_label['color'] }}">{{ $kh->loai_label['label'] }}</span>

  Cột GV xác nhận:
    @php $t = $kh->tong_module ?? 0; $d = $kh->module_da_nhan ?? 0; @endphp
    <span class="badge bg-{{ $d >= $t && $t > 0 ? 'success' : 'warning' }}">
        {{ $d }}/{{ $t }} module
    </span>

  Cột Ngày khai giảng:
    {{ $kh->ngay_khai_giang?->format('d/m/Y') ?? '—' }}

  Cột Thao tác: chỉ nút "Xem chi tiết" (btn-info btn-sm)

  Pagination: {{ $khoaHocHoatDong->links() }}

YÊU CẦU OUTPUT:
  1. Method index() hoàn chỉnh
  2. Toàn bộ file view index.blade.php

✅ CHECKLIST PHASE 1:
  ☐ /admin/khoa-hoc không lỗi, render 2 tab
  ☐ 4 stat cards đúng số từ DB
  ☐ Click tab → URL đổi, tab highlight đúng, nội dung đúng
  ☐ Search → lọc kết quả, giữ tab đang chọn
  ☐ Pagination 2 tab độc lập (mau_page và hd_page)
  ☐ Nút Kích hoạt/Sửa/Xóa CHỈ hiện với KH có trang_thai='cho_mo'
  ☐ Tab Đang hoạt động: cột GV xác nhận hiển thị badge đúng màu
  ☐ Empty state hiển thị khi không có dữ liệu
```
**══ DỪNG. CHECKLIST PHASE 1. BÁO KẾT QUẢ. MỚI LÀM PHASE 2 ══**

---

# ══════════════════════════════════
# PHASE 2 — CREATE: FORM TẠO THEO LOẠI
# ══════════════════════════════════

```
Bạn là senior Laravel developer.
1 form dùng chung, render khác nhau theo $loai.

BƯỚC 1 — Sửa create():

  public function create(Request $request)
  {
      $loai = in_array($request->get('loai'),['mau','truc_tiep'])
                  ? $request->get('loai') : 'mau';

      $monHocs = MonHoc::where('trang_thai',true)->orderBy('ten_mon_hoc')->get();

      $khoaHocMauCoSan = KhoaHoc::with(
              'moduleHocs:id,khoa_hoc_id,ten_module,thu_tu_module,thoi_luong_du_kien,mo_ta'
          )
          ->mau()->where('tong_so_module','>',0)->orderBy('ten_khoa_hoc')
          ->get(['id','ten_khoa_hoc','ma_khoa_hoc','tong_so_module']);

      $giangViens = GiangVien::with('nguoiDung:ma_nguoi_dung,ho_ten')
          ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai',true))->get();

      $preselectedMonHocId = $request->get('mon_hoc_id');

      return view('pages.admin.khoa-hoc.khoa-hoc.create',
          compact('loai','monHocs','khoaHocMauCoSan','giangViens','preselectedMonHocId'));
  }

BƯỚC 2 — Sửa store():

  Validation rules:
    Rules CHUNG (áp dụng cho cả 2 loại):
      'loai'           => 'required|in:mau,truc_tiep'
      'mon_hoc_id'     => 'required|exists:mon_hoc,id'
      'ten_khoa_hoc'   => 'required|string|max:200'
      'cap_do'         => 'required|in:co_ban,trung_binh,nang_cao'
      'mo_ta_ngan'     => 'nullable|string|max:500'
      'mo_ta_chi_tiet' => 'nullable|string'
      'hinh_anh'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
      'ghi_chu_noi_bo' => 'nullable|string|max:1000'
      'modules'                       => 'required|array|min:1'
      'modules.*.ten_module'          => 'required|string|max:200'
      'modules.*.thoi_luong_du_kien'  => 'nullable|integer|min:1|max:600'
      'modules.*.mo_ta'               => 'nullable|string'

    Rules BỔ SUNG nếu loai='truc_tiep':
      'ngay_khai_giang'         => 'required|date|after_or_equal:today'
      'ngay_ket_thuc_du_kien'   => 'required|date|after:ngay_khai_giang'
      'modules.*.giang_vien_id' => 'required|exists:giang_vien,id'

    Rules BỔ SUNG nếu loai='mau':
      'ngay_khai_giang'         => 'nullable|date'
      'ngay_ket_thuc_du_kien'   => 'nullable|date|after:ngay_khai_giang'
      'modules.*.giang_vien_id' => 'nullable|exists:giang_vien,id'

    Messages: toàn bộ tiếng Việt rõ ràng

  Logic trong DB::transaction:
    1. Upload hinh_anh nếu có → store('images/khoa-hoc','public')
    2. Sinh ma_khoa_hoc (dùng lại logic cũ trong project)
    3. Tạo KhoaHoc:
         loai                = $request->loai
         trang_thai_van_hanh = loai='mau' ? 'cho_mo' : 'cho_giang_vien'
         ngay_khai_giang, ngay_ket_thuc_du_kien (nullable với KH mẫu)
         ghi_chu_noi_bo      = $request->ghi_chu_noi_bo
    4. Loop tạo module (thu_tu=index+1, sinh ma_module = maKH+'M'+pad(thuTu,2))
    5. Nếu loai='truc_tiep' và có giang_vien_id:
         Tạo PhanCongModuleGiangVien (trang_thai='cho_xac_nhan')
         // TODO Phase GV: notification
    6. Update tong_so_module
    7. Redirect → show với flash message phù hợp theo loại

BƯỚC 3 — Viết view create.blade.php:
File: resources/views/pages/admin/khoa-hoc/khoa-hoc/create.blade.php

  [HEADER thay đổi theo $loai]:
    loai='mau':
      H4: "Tạo khóa học mẫu" + badge bg-info "Template"
      p.text-muted: "Chuẩn bị sẵn nội dung khóa học. GV và lịch dạy sẽ setup sau khi có đủ học viên."
    loai='truc_tiep':
      H4: "Tạo khóa học trực tiếp" + badge bg-primary "Học ngay"
      p.text-muted: "Tạo lớp học với GV và ngày khai giảng ngay lập tức."

  [FORM: POST → route('admin.khoa-hoc.store'), enctype=multipart/form-data]
    @csrf
    <input type="hidden" name="loai" value="{{ $loai }}">

    Section 1 — Thông tin chung:
      Select Môn học * (preselected nếu có $preselectedMonHocId)
      Input Tên khóa học *
      Radio Cấp độ: Cơ bản | Trung bình | Nâng cao
      Textarea Mô tả ngắn (max 500)
      Textarea Mô tả chi tiết
      File upload Hình ảnh (jpg,png,max 2MB)
      Textarea Ghi chú nội bộ ("Chỉ admin thấy")

    Section 2 — Lịch học (CHỈ HIỆN khi loai='truc_tiep'):
      <div class="{{ $loai==='truc_tiep' ? '' : 'd-none' }}" id="section-lich-hoc">
        Card border-primary:
          Input date Ngày khai giảng * (min="{{ date('Y-m-d') }}")
          Input date Ngày kết thúc dự kiến *
      </div>

    Section 3 — Gợi ý copy từ KH mẫu:
      Alert border bg-light:
        Tiêu đề: "💡 Copy cấu trúc module từ khóa học mẫu có sẵn"
        Select dropdown:
          <option value="">-- Tự nhập module --</option>
          @foreach($khoaHocMauCoSan as $mau)
            <option value="{{ $mau->id }}"
                    data-modules="{{ $mau->moduleHocs->toJson() }}">
              [{{ $mau->ma_khoa_hoc }}] {{ $mau->ten_khoa_hoc }} ({{ $mau->tong_so_module }} module)
            </option>
          @endforeach
        Note: "Sau khi chọn, danh sách module bên dưới tự điền. Bạn có thể chỉnh sửa lại."

    Section 4 — Danh sách module (dynamic table):
      Card header: "Danh sách module" + badge id="module-count"

      Table header tùy $loai:
        loai='mau':       STT | Tên module * | TL (phút) | Mô tả | Xóa
        loai='truc_tiep': STT | Tên module * | TL (phút) | Mô tả | Giảng viên * | Xóa

      Render rows từ old():
        @php $modules = old('modules', [['ten_module'=>'','thoi_luong_du_kien'=>'','mo_ta'=>'','giang_vien_id'=>'']]) @endphp
        @foreach($modules as $i => $mod)
          <tr class="module-row" data-index="{{ $i }}">
            <td><span class="stt">{{ $i+1 }}</span></td>
            <td>
              <input type="text" name="modules[{{ $i }}][ten_module]"
                     class="form-control form-control-sm vip-form-control"
                     placeholder="Tên module *" value="{{ $mod['ten_module'] }}" required>
            </td>
            <td style="width:100px">
              <input type="number" name="modules[{{ $i }}][thoi_luong_du_kien]"
                     class="form-control form-control-sm vip-form-control"
                     placeholder="phút" min="1" max="600"
                     value="{{ $mod['thoi_luong_du_kien'] ?? '' }}">
            </td>
            <td>
              <input type="text" name="modules[{{ $i }}][mo_ta]"
                     class="form-control form-control-sm vip-form-control"
                     placeholder="Mô tả ngắn"
                     value="{{ $mod['mo_ta'] ?? '' }}">
            </td>
            @if($loai === 'truc_tiep')
            <td>
              <select name="modules[{{ $i }}][giang_vien_id]"
                      class="form-select form-select-sm vip-form-control" required>
                <option value="">-- Chọn GV --</option>
                @foreach($giangViens as $gv)
                  <option value="{{ $gv->id }}"
                          {{ ($mod['giang_vien_id']??'') == $gv->id ? 'selected':'' }}>
                    {{ $gv->nguoiDung->ho_ten ?? 'N/A' }}
                  </option>
                @endforeach
              </select>
            </td>
            @endif
            <td>
              <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row">
                <i class="fas fa-times"></i>
              </button>
            </td>
          </tr>
        @endforeach

      <button type="button" id="btn-add-module" class="btn btn-outline-secondary btn-sm mt-2">
          <i class="fas fa-plus me-1"></i> Thêm module
      </button>

    [JAVASCRIPT — viết 4 chức năng]:

      1. btn-add-module click:
         - Clone row cuối, cập nhật index trong name attributes
         - Clear tất cả values, cập nhật STT badge, cập nhật badge số module

      2. btn-remove-row click:
         - Nếu chỉ còn 1 row: alert("Phải có ít nhất 1 module") rồi return
         - Xóa row, renumber STT + reindex tất cả name attributes còn lại
         - Cập nhật badge số module

      3. copy-from-template change:
         - Nếu chọn option có value:
             confirm("Thay thế các module hiện tại bằng module từ khóa học này?")
             Nếu OK: xóa hết rows, parse JSON từ data-modules, render lại
             KHÔNG điền giang_vien_id khi copy (để admin tự chọn sau)
         - Nếu chọn "--Tự nhập--": không làm gì

      4. Form submit:
         - Đếm rows module hiện có
         - Nếu = 0: preventDefault + alert("Phải có ít nhất 1 module")

    [NÚT SUBMIT]:
      @if($loai === 'mau')
        <button type="submit" class="btn btn-info px-4">
            <i class="fas fa-save me-2"></i>Lưu khóa học mẫu
        </button>
      @else
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-paper-plane me-2"></i>Tạo và gửi thông báo GV
        </button>
      @endif
      <a href="{{ route('admin.khoa-hoc.index', ['tab'=> $loai==='mau' ? 'mau' : 'hoat_dong']) }}"
         class="btn btn-secondary ms-2">Hủy</a>

YÊU CẦU OUTPUT:
  1. Method create() và store() hoàn chỉnh trong controller
  2. Toàn bộ file view create.blade.php (HTML + JS)

✅ CHECKLIST PHASE 2:
  ☐ ?loai=mau → KHÔNG có section lịch học, KHÔNG có cột GV trong bảng module
  ☐ ?loai=truc_tiep → CÓ section lịch học + cột chọn GV
  ☐ Dropdown copy KH mẫu → module rows tự điền, cột GV để trống
  ☐ Nút "+" thêm row, STT tự tăng, badge số module cập nhật
  ☐ Nút "X" xóa row, renumber STT đúng, không xóa được nếu còn 1 row
  ☐ Submit 0 module → bị chặn với alert
  ☐ Tạo KH mẫu → DB: loai='mau', trang_thai_van_hanh='cho_mo', ngay_khai_giang=NULL
  ☐ Tạo KH trực tiếp → DB: loai='truc_tiep', trang_thai_van_hanh='cho_giang_vien'
  ☐ Tạo KH trực tiếp → bảng phan_cong có 1 row/module, trang_thai='cho_xac_nhan'
  ☐ KH mẫu: không cần ngày/GV → tạo thành công
  ☐ KH trực tiếp: thiếu ngày/GV → lỗi validation tiếng Việt rõ ràng
  ☐ Redirect sau tạo → trang show KH, flash message phù hợp theo loại
```
**══ DỪNG. CHECKLIST PHASE 2. BÁO KẾT QUẢ. MỚI LÀM PHASE 3 ══**

---

# ══════════════════════════════════
# PHASE 3 — SHOW: CHI TIẾT + KÍCH HOẠT
# ══════════════════════════════════

```
Bạn là senior Laravel developer.
Trang show là trung tâm quản lý. KH mẫu có form kích hoạt ngay trong trang này.

BƯỚC 1 — Sửa show():

  public function show($id)
  {
      $khoaHoc = KhoaHoc::with([
          'monHoc',
          'moduleHocs' => fn($q) => $q->orderBy('thu_tu_module'),
          'moduleHocs.phanCongGiangViens' => fn($q) => $q->orderByDesc('updated_at'),
          'moduleHocs.phanCongGiangViens.giangVien.nguoiDung',
      ])->findOrFail($id);

      $giangViens = GiangVien::with('nguoiDung:ma_nguoi_dung,ho_ten')
          ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai',true))->get();

      $tongModule = $khoaHoc->moduleHocs->count();
      $moduleCoGv = $khoaHoc->moduleHocs->filter(
          fn($m) => $m->phanCongGiangViens->where('trang_thai','da_nhan')->count() > 0
      )->count();

      return view('pages.admin.khoa-hoc.khoa-hoc.show',
          compact('khoaHoc','giangViens','tongModule','moduleCoGv'));
  }

BƯỚC 2 — Thêm method kichHoatMau():

  public function kichHoatMau(Request $request, $id)
  {
      $khoaHoc = KhoaHoc::with('moduleHocs')->findOrFail($id);

      // Guards
      if ($khoaHoc->loai !== 'mau')
          return redirect()->back()->with('error','Chỉ kích hoạt được khóa học mẫu.');
      if ($khoaHoc->trang_thai_van_hanh !== 'cho_mo')
          return redirect()->back()->with('error','Khóa học đã được kích hoạt trước đó.');

      $tongModule = $khoaHoc->moduleHocs->count();
      if ($tongModule === 0)
          return redirect()->back()->with('error',
              'Khóa học chưa có module. Vui lòng thêm module trước.');

      // Build dynamic GV rules
      $moduleIds = $khoaHoc->moduleHocs->pluck('id')->toArray();
      $gvRules   = collect($moduleIds)->mapWithKeys(
          fn($mid) => ["giang_viens.{$mid}" => 'required|exists:giang_vien,id']
      )->toArray();

      $request->validate(array_merge([
          'ngay_khai_giang'       => 'required|date|after_or_equal:today',
          'ngay_ket_thuc_du_kien' => 'required|date|after:ngay_khai_giang',
      ], $gvRules), [
          'ngay_khai_giang.required'       => 'Vui lòng chọn ngày khai giảng',
          'ngay_khai_giang.after_or_equal' => 'Ngày khai giảng phải từ hôm nay',
          'ngay_ket_thuc_du_kien.required' => 'Vui lòng chọn ngày kết thúc',
          'ngay_ket_thuc_du_kien.after'    => 'Ngày kết thúc phải sau ngày khai giảng',
          'giang_viens.*.required'         => 'Vui lòng chọn giảng viên cho tất cả module',
      ]);

      DB::transaction(function () use ($khoaHoc, $request, $moduleIds) {
          $khoaHoc->update([
              'ngay_khai_giang'       => $request->ngay_khai_giang,
              'ngay_ket_thuc_du_kien' => $request->ngay_ket_thuc_du_kien,
              'trang_thai_van_hanh'   => 'cho_giang_vien',
          ]);
          foreach ($moduleIds as $mid) {
              PhanCongModuleGiangVien::updateOrCreate(
                  ['module_hoc_id' => $mid, 'giao_vien_id' => $request->giang_viens[$mid]],
                  [
                      'khoa_hoc_id'    => $khoaHoc->id,
                      'trang_thai'     => 'cho_xac_nhan',
                      'ngay_phan_cong' => now(),
                      'created_by'     => auth()->user()->ma_nguoi_dung,
                  ]
              );
              // TODO Phase GV: gửi notification
          }
      });

      return redirect()->route('admin.khoa-hoc.show', $id)
          ->with('success', "Đã kích hoạt! Đang chờ {$tongModule} giảng viên xác nhận.");
  }

BƯỚC 3 — Thêm route vào web.php (trong prefix khoa-hoc):
  Route::post('/{id}/kich-hoat-mau', [KhoaHocManagementController::class, 'kichHoatMau'])
       ->name('khoa-hoc.kich-hoat-mau');

BƯỚC 4 — Viết view show.blade.php:
File: resources/views/pages/admin/khoa-hoc/khoa-hoc/show.blade.php

  [BREADCRUMB]: Admin / Khóa học / {{ $khoaHoc->ma_khoa_hoc }}

  [HEADER]:
    Trái: H4 tên KH + badge loai_label + badge trang_thai_van_hanh_label
    Phải: 3 nút actions (CHỈ hiện khi trang_thai_van_hanh='cho_mo'):
      - "Sửa" → edit
      - "Xóa" + confirm → delete
      - "Quay lại" (luôn hiện) → index?tab=mau

  [FLASH MESSAGES]: session('success'), session('error')

  [ROW THÔNG TIN (col-md-8 + col-md-4)]:
    Col-8: mô tả ngắn, mô tả chi tiết
           Nếu có ghi_chu_noi_bo: alert-light "📋 Ghi chú nội bộ: ..."
    Col-4 (Card sidebar):
      Ảnh KH hoặc placeholder
      ─────────────────────
      Mã KH:           {{ $khoaHoc->ma_khoa_hoc }}
      Môn học:         <a>{{ $khoaHoc->monHoc->ten_mon_hoc }}</a>
      Cấp độ:          badge
      Tổng module:     {{ $tongModule }}
      Ngày khai giảng: {{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? '─' }}
      Ngày kết thúc:   {{ $khoaHoc->ngay_ket_thuc_du_kien?->format('d/m/Y') ?? '─' }}
      Ngày tạo:        {{ $khoaHoc->created_at->format('d/m/Y') }}

  [PROGRESS BAR — CHỈ HIỆN khi trang_thai_van_hanh !== 'cho_mo']:
    @if($khoaHoc->trang_thai_van_hanh !== 'cho_mo')
      Card progress:
        Text: "{{ $moduleCoGv }}/{{ $tongModule }} module đã có giảng viên xác nhận"
        Progress bar: % = ($moduleCoGv/$tongModule*100)
          bg-success nếu đủ | bg-warning nếu chưa
        Nếu đủ: hiện "✅ Tất cả giảng viên đã xác nhận!" màu success
    @endif

  [BẢNG DANH SÁCH MODULE]:
    Card header: "Modules ({{ $tongModule }})" + nút Thêm module → create?khoa_hoc_id

    Cột: STT | Mã | Tên module | Thời lượng | Giảng viên | Trạng thái PC | Xem

    Cột Giảng viên + Trạng thái PC:
      @php $pc = $module->phanCongGiangViens->first(); @endphp
      Nếu có $pc:
        Tên: $pc->giangVien->nguoiDung->ho_ten
        Badge:
          da_nhan      → bg-success "Đã xác nhận"
          cho_xac_nhan → bg-warning "Chờ xác nhận"
          tu_choi      → bg-danger  "Từ chối"
      Nếu không:
        Badge bg-secondary "Chưa phân công"

    Cột Xem: btn-info btn-sm → module-hoc.show

  [CARD KÍCH HOẠT — CHỈ HIỆN khi loai='mau' VÀ trang_thai_van_hanh='cho_mo']:

    @if($khoaHoc->loai === 'mau' && $khoaHoc->trang_thai_van_hanh === 'cho_mo')
    <div class="card border-success mt-4" id="section-kich-hoat">
      <div class="card-header bg-success text-white">
        <i class="fas fa-rocket me-2"></i>
        Kích hoạt khóa học — Tạo lớp học từ template này
      </div>
      <div class="card-body">
        @if($tongModule === 0)
          [Alert warning: "Chưa có module. Thêm module trước." + link]
        @else
          <p class="text-muted">
            Điền lịch dạy và chọn giảng viên cho từng module.
            Sau khi xác nhận, hệ thống gửi thông báo đến các giảng viên.
          </p>

          <form action="{{ route('admin.khoa-hoc.kich-hoat-mau', $khoaHoc->id) }}" method="POST">
            @csrf

            [Row ngày (col-md-6 + col-md-6)]:
              Input date Ngày khai giảng * (min="{{ date('Y-m-d') }}")
              Input date Ngày kết thúc dự kiến *
              @error cho từng field

            [Bảng phân công GV]:
              H6: "Phân công giảng viên cho từng module"
              Table cột: STT | Tên module | Thời lượng | Giảng viên *

              @foreach($khoaHoc->moduleHocs as $module)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $module->ten_module }}<br><small class="text-muted">{{ $module->ma_module }}</small></td>
                  <td>[Xh Yp hoặc — nếu không có]</td>
                  <td>
                    <select name="giang_viens[{{ $module->id }}]" class="form-select form-select-sm" required>
                      <option value="">-- Chọn giảng viên --</option>
                      @foreach($giangViens as $gv)
                        <option value="{{ $gv->id }}"
                                {{ old("giang_viens.{$module->id}") == $gv->id ? 'selected':'' }}>
                          {{ $gv->nguoiDung->ho_ten ?? 'N/A' }}
                        </option>
                      @endforeach
                    </select>
                    @error("giang_viens.{$module->id}")
                      <div class="text-danger small">{{ $message }}</div>
                    @enderror
                  </td>
                </tr>
              @endforeach

            [Nút submit]:
              <button type="submit" class="btn btn-success px-4"
                      onclick="return confirm('Xác nhận kích hoạt? Hệ thống sẽ gửi thông báo đến các giảng viên.')">
                <i class="fas fa-rocket me-2"></i> Xác nhận kích hoạt
              </button>
          </form>
        @endif
      </div>
    </div>
    @endif

YÊU CẦU OUTPUT:
  1. Method show() và kichHoatMau() hoàn chỉnh trong controller
  2. Route mới cần thêm (ghi rõ vị trí trong web.php)
  3. Toàn bộ file view show.blade.php

✅ CHECKLIST PHASE 3:
  ☐ Trang show KH mẫu mới: badge "Chờ mở", có card kích hoạt ở cuối
  ☐ Trang show KH trực tiếp: badge "Chờ GV xác nhận", KHÔNG có card kích hoạt
  ☐ KH mẫu chưa có module: card kích hoạt hiện cảnh báo, không có form
  ☐ Form kích hoạt: điền đủ ngày + GV → submit → DB cập nhật đúng
  ☐ Sau kích hoạt: trang_thai_van_hanh='cho_giang_vien', bản ghi phan_cong tạo đúng
  ☐ Sau kích hoạt: card kích hoạt biến mất, progress bar hiển thị
  ☐ Progress bar: % đúng, màu warning khi chưa đủ, success khi đủ
  ☐ Nút Sửa/Xóa biến mất sau khi kích hoạt
  ☐ Bảng module: GV + badge "Chờ xác nhận" hiển thị đúng
  ☐ Thiếu GV trong form → lỗi tiếng Việt, giữ nguyên dữ liệu đã nhập
```
**══ DỪNG. CHECKLIST PHASE 3. FLOW HOÀN CHỈNH ══**

---

## 📋 QUY TẮC BẮT BUỘC CHO GEMINI

```
1.  Chỉ làm 1 phase một lúc. Dừng sau phase, đợi confirm checklist.
2.  Migration: php artisan make:migration — không tự đặt tên file.
3.  Sửa file cũ: "TÌM đoạn X → THAY bằng Y". Không rewrite toàn bộ file không cần thiết.
4.  File mới: 100% đầy đủ. Không dùng // TODO, // implement later, placeholder bất kỳ.
5.  Validation messages: 100% tiếng Việt.
6.  Mọi DB write: bọc trong DB::transaction().
7.  Không thêm feature ngoài scope phase đang làm — ghi chú để bàn sau.
8.  Eager loading: dùng with() đúng chỗ, không để N+1 query.
9.  Conflict với code cũ: báo cụ thể, developer quyết định, không tự ý xử lý.
10. Cuối mỗi phase: liệt kê danh sách file đã tạo/sửa với đường dẫn đầy đủ.

► BẮT ĐẦU PHASE 0. Sau khi tôi xác nhận PASS → làm Phase 1.
```
