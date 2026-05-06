@extends('layouts.app')

@section('title', 'Chi tiết tài nguyên')

@section('content')
@php
    $duyetMeta = [
        'nhap'          => ['label' => 'Nháp',     'class' => 'is-secondary', 'icon' => 'fa-pen'],
        'cho_duyet'     => ['label' => 'Chờ duyệt','class' => 'is-warning',   'icon' => 'fa-hourglass-half'],
        'da_duyet'      => ['label' => 'Đã duyệt', 'class' => 'is-success',   'icon' => 'fa-check-circle'],
        'can_chinh_sua' => ['label' => 'Cần sửa',  'class' => 'is-info',      'icon' => 'fa-pen-to-square'],
        'tu_choi'       => ['label' => 'Từ chối',  'class' => 'is-danger',    'icon' => 'fa-times-circle'],
    ];
    $duyet = $duyetMeta[$taiNguyen->trang_thai_duyet] ?? ['label' => 'Khác', 'class' => 'is-secondary', 'icon' => 'fa-circle-question'];

    $loaiBadge = match($taiNguyen->loai_tai_nguyen) {
        'video' => ['icon' => 'fa-play-circle', 'label' => 'VIDEO', 'color' => '#dc2626'],
        'pdf'   => ['icon' => 'fa-file-pdf',    'label' => 'PDF',   'color' => '#dc2626'],
        'image' => ['icon' => 'fa-image',       'label' => 'ẢNH',   'color' => '#0ea5e9'],
        'word'  => ['icon' => 'fa-file-word',   'label' => 'WORD',  'color' => '#1d4ed8'],
        'powerpoint' => ['icon' => 'fa-file-powerpoint', 'label' => 'PPT', 'color' => '#d97706'],
        'excel' => ['icon' => 'fa-file-excel',  'label' => 'EXCEL', 'color' => '#16a34a'],
        'audio' => ['icon' => 'fa-volume-high', 'label' => 'AUDIO', 'color' => '#7c3aed'],
        'archive' => ['icon' => 'fa-file-zipper', 'label' => 'ARCHIVE', 'color' => '#475569'],
        'link_ngoai' => ['icon' => 'fa-link',   'label' => 'LINK',  'color' => '#0ea5e9'],
        default => ['icon' => 'fa-file',        'label' => 'FILE',  'color' => '#64748b'],
    };

    $youtubeId = null;
    if ($taiNguyen->is_external && !empty($taiNguyen->link_ngoai)) {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $taiNguyen->link_ngoai, $m)) {
            $youtubeId = $m[1];
        }
    }

    $sizeLabel = null;
    if ($taiNguyen->file_size) {
        if ($taiNguyen->file_size >= 1048576) $sizeLabel = number_format($taiNguyen->file_size / 1048576, 1) . ' MB';
        elseif ($taiNguyen->file_size >= 1024) $sizeLabel = number_format($taiNguyen->file_size / 1024, 0) . ' KB';
        else $sizeLabel = $taiNguyen->file_size . ' B';
    }

    $sourceText = $taiNguyen->is_external
        ? (parse_url((string) $taiNguyen->link_ngoai, PHP_URL_HOST) ?: 'Liên kết ngoài')
        : ($taiNguyen->file_name ?? 'Tệp nội bộ');
@endphp

<div class="container-fluid admin-page-x atvs-page">
    {{-- Welcome banner --}}
    <div class="apx-welcome atvs-welcome">
        <div class="apx-welcome-icon"><i class="fas {{ $loaiBadge['icon'] }}"></i></div>
        <div class="apx-welcome-text">
            <div class="atvs-tag-row">
                <span class="atvs-loai-badge"><i class="fas fa-shield-halved"></i> PHÊ DUYỆT TÀI NGUYÊN</span>
                <span class="atvs-status-pill {{ $duyet['class'] }}">
                    <i class="fas {{ $duyet['icon'] }}"></i> {{ $duyet['label'] }}
                </span>
                <span class="atvs-type-tag" style="background: {{ $loaiBadge['color'] }};">
                    <i class="fas {{ $loaiBadge['icon'] }}"></i> {{ $loaiBadge['label'] }}
                </span>
            </div>
            <h4>{{ $taiNguyen->tieu_de }}</h4>
            <p>
                <span><i class="fas fa-user"></i> {{ $taiNguyen->nguoiTao->ho_ten ?? 'N/A' }} ({{ $taiNguyen->vai_tro_nguoi_tao ?? '—' }})</span>
                <span class="atvs-sep">·</span>
                <span><i class="far fa-calendar-alt"></i>
                    @if($taiNguyen->ngay_gui_duyet)
                        Gửi: {{ $taiNguyen->ngay_gui_duyet->format('d/m/Y H:i') }}
                    @else
                        Tạo: {{ $taiNguyen->created_at->format('d/m/Y H:i') }}
                    @endif
                </span>
                @if($sizeLabel)
                    <span class="atvs-sep">·</span>
                    <span><i class="fas fa-weight-hanging"></i> {{ $sizeLabel }}</span>
                @endif
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.thu-vien.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Danh sách</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        {{-- LEFT: Preview + thông tin --}}
        <div class="col-lg-8">
            {{-- ① Xem trước nội dung --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">1</span>
                        <div>
                            <h2><i class="fas fa-eye"></i> Xem trước tài nguyên</h2>
                            <p>Nội dung file được hiển thị trực tiếp để admin kiểm tra trước khi duyệt.</p>
                        </div>
                    </div>
                </header>

                <div class="atvs-preview">
                    @if($taiNguyen->file_url || $taiNguyen->is_external)
                        <iframe
                            src="{{ route('admin.thu-vien.preview', $taiNguyen->id) }}"
                            class="atvs-media atvs-pdf"
                            frameborder="0"
                            title="Xem trước {{ $taiNguyen->tieu_de }}">
                        </iframe>
                    @else
                        <div class="atvs-fallback">
                            <i class="fas {{ $loaiBadge['icon'] }}"></i>
                            <h5>{{ $taiNguyen->loai_label }}</h5>
                            <p>Tài nguyên này chưa có file đính kèm hoặc liên kết.</p>
                        </div>
                    @endif

                    <span class="atvs-type-badge" style="background: {{ $loaiBadge['color'] }};">
                        <i class="fas {{ $loaiBadge['icon'] }}"></i> {{ $loaiBadge['label'] }}
                    </span>
                </div>

                @if($taiNguyen->file_url)
                    <div class="atvs-file-bar">
                        <div class="atvs-file-info">
                            <i class="fas fa-link"></i>
                            <span class="atvs-file-url">{{ $taiNguyen->file_url }}</span>
                        </div>
                        <a href="{{ $taiNguyen->file_url }}" target="_blank" rel="noopener" class="atvs-file-btn">
                            <i class="fas fa-up-right-from-square"></i> Mở
                        </a>
                    </div>
                @endif
            </section>

            {{-- ② Mô tả --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">2</span>
                        <div>
                            <h2><i class="fas fa-align-left"></i> Mô tả &amp; ghi chú</h2>
                            <p>Mô tả do giảng viên ghi cho tài nguyên này.</p>
                        </div>
                    </div>
                </header>

                <div class="atvs-desc-card">
                    <p class="atvs-desc-text">{{ $taiNguyen->mo_ta ?: 'Không có mô tả.' }}</p>
                </div>
            </section>
        </div>

        {{-- RIGHT: Sidebar info + form duyệt --}}
        <div class="col-lg-4">
            {{-- Info --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">3</span>
                        <div>
                            <h2><i class="fas fa-circle-info"></i> Thông tin</h2>
                            <p>Chi tiết tài nguyên và người tạo.</p>
                        </div>
                    </div>
                </header>

                <div class="atvs-info-card">
                    <div class="atvs-info-row">
                        <span class="atvs-info-label">Trạng thái</span>
                        <span class="atvs-status-pill {{ $duyet['class'] }}">
                            <i class="fas {{ $duyet['icon'] }}"></i> {{ $duyet['label'] }}
                        </span>
                    </div>
                    <div class="atvs-info-row">
                        <span class="atvs-info-label">Loại tài liệu</span>
                        <strong>{{ $taiNguyen->loai_label }}</strong>
                    </div>
                    <div class="atvs-info-row">
                        <span class="atvs-info-label">Phạm vi</span>
                        <strong>{{ $taiNguyen->pham_vi_su_dung }}</strong>
                    </div>
                    <div class="atvs-info-row">
                        <span class="atvs-info-label">Người tạo</span>
                        <strong>{{ $taiNguyen->nguoiTao->ho_ten ?? 'N/A' }}</strong>
                    </div>
                    <div class="atvs-info-row">
                        <span class="atvs-info-label">Vai trò</span>
                        <strong>{{ $taiNguyen->vai_tro_nguoi_tao ?? '—' }}</strong>
                    </div>
                    <div class="atvs-info-row">
                        <span class="atvs-info-label">Tên file</span>
                        <strong class="text-end" style="word-break: break-all;">{{ \Illuminate\Support\Str::limit($sourceText, 32) }}</strong>
                    </div>
                    @if($sizeLabel)
                        <div class="atvs-info-row">
                            <span class="atvs-info-label">Dung lượng</span>
                            <strong>{{ $sizeLabel }}</strong>
                        </div>
                    @endif
                    <div class="atvs-info-row">
                        <span class="atvs-info-label">Ngày tạo</span>
                        <strong>{{ $taiNguyen->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="atvs-info-row">
                        <span class="atvs-info-label">Ngày gửi duyệt</span>
                        <strong>{{ $taiNguyen->ngay_gui_duyet ? $taiNguyen->ngay_gui_duyet->format('d/m/Y H:i') : '—' }}</strong>
                    </div>
                    @if($taiNguyen->nguoiDuyet)
                        <div class="atvs-info-row">
                            <span class="atvs-info-label">Người duyệt</span>
                            <strong>{{ $taiNguyen->nguoiDuyet->ho_ten }}</strong>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Form duyệt --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">4</span>
                        <div>
                            <h2><i class="fas fa-gavel"></i> Thực hiện phê duyệt</h2>
                            <p>Chọn quyết định và ghi chú phản hồi cho giảng viên.</p>
                        </div>
                    </div>
                </header>

                <form action="{{ route('admin.thu-vien.duyet', $taiNguyen->id) }}" method="POST" class="atvs-form-card">
                    @csrf

                    <label class="atvs-flabel"><i class="fas fa-check"></i> Quyết định <span class="text-danger">*</span></label>
                    <div class="atvs-decision-grid">
                        <label class="atvs-decision-item is-success">
                            <input type="radio" name="trang_thai_duyet" value="da_duyet" {{ $taiNguyen->trang_thai_duyet === 'da_duyet' ? 'checked' : '' }} required>
                            <div class="atvs-decision-icon"><i class="fas fa-check-circle"></i></div>
                            <strong>Đồng ý duyệt</strong>
                            <small>Tài nguyên hợp lệ, cho phép sử dụng.</small>
                        </label>
                        <label class="atvs-decision-item is-info">
                            <input type="radio" name="trang_thai_duyet" value="can_chinh_sua" {{ $taiNguyen->trang_thai_duyet === 'can_chinh_sua' ? 'checked' : '' }}>
                            <div class="atvs-decision-icon"><i class="fas fa-pen-to-square"></i></div>
                            <strong>Yêu cầu sửa</strong>
                            <small>Có vấn đề nhỏ cần GV chỉnh.</small>
                        </label>
                        <label class="atvs-decision-item is-danger">
                            <input type="radio" name="trang_thai_duyet" value="tu_choi" {{ $taiNguyen->trang_thai_duyet === 'tu_choi' ? 'checked' : '' }}>
                            <div class="atvs-decision-icon"><i class="fas fa-times-circle"></i></div>
                            <strong>Từ chối</strong>
                            <small>Không đạt, không cho dùng.</small>
                        </label>
                    </div>

                    <label class="atvs-flabel mt-3"><i class="fas fa-comment-dots"></i> Ghi chú phản hồi</label>
                    <textarea name="ghi_chu_admin" class="form-control atvs-textarea" rows="4" placeholder="Nhập lý do nếu từ chối hoặc yêu cầu sửa đổi...">{{ old('ghi_chu_admin', $taiNguyen->ghi_chu_admin) }}</textarea>

                    <button type="submit" class="atvs-submit-btn">
                        <i class="fas fa-save"></i> Cập nhật phê duyệt
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .atvs-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .atvs-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .atvs-page .apx-section-title h2 i { color: #dc2626; }

    /* Welcome banner */
    .atvs-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .atvs-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .atvs-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .atvs-type-tag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; border-radius: 999px; box-shadow: 0 2px 8px rgba(0,0,0,0.18); }
    .atvs-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.72rem; font-weight: 800; border-radius: 999px;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .atvs-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .atvs-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .atvs-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .atvs-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .atvs-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
    .apx-welcome.atvs-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.atvs-welcome p i { color: #fef3c7; margin-right: 4px; }
    .atvs-sep { opacity: 0.5; }

    /* Preview */
    .atvs-preview {
        position: relative;
        background: #0f172a;
        border-radius: 14px;
        overflow: hidden;
        aspect-ratio: 16/9;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.16);
    }
    .atvs-media { width: 100%; height: 100%; object-fit: cover; display: block; border: 0; }
    .atvs-pdf { background: #fff; }
    .atvs-fallback {
        width: 100%; height: 100%;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        color: #fff; text-align: center;
        padding: 24px;
        background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
    }
    .atvs-fallback i { font-size: 3.6rem; margin-bottom: 16px; opacity: 0.95; }
    .atvs-fallback h5 { font-size: 1.1rem; font-weight: 800; margin: 0 0 6px; }
    .atvs-fallback p { font-size: 0.9rem; margin: 0 0 14px; opacity: 0.85; word-break: break-all; max-width: 480px; }
    .atvs-fallback.audio-tone { background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); gap: 14px; }
    .atvs-fallback.link-tone  { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); }
    .atvs-audio { width: 100%; max-width: 420px; }
    .atvs-fallback-link {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 18px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.4);
        color: #fff;
        font-size: 0.86rem; font-weight: 700;
        border-radius: 8px; text-decoration: none;
        transition: all 0.18s ease;
    }
    .atvs-fallback-link:hover { background: rgba(255,255,255,0.3); color: #fff; transform: translateY(-1px); }

    .atvs-type-badge {
        position: absolute; top: 12px; left: 12px;
        padding: 4px 12px;
        color: #fff;
        font-size: 0.7rem; font-weight: 800;
        letter-spacing: 0.5px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        display: inline-flex; align-items: center; gap: 5px;
    }
    .atvs-type-badge i { font-size: 0.62rem; }

    /* File URL bar */
    .atvs-file-bar {
        margin-top: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: #fafafa;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }
    .atvs-file-info { flex: 1; display: flex; align-items: center; gap: 8px; min-width: 0; }
    .atvs-file-info i { color: #1d4ed8; font-size: 0.78rem; flex-shrink: 0; }
    .atvs-file-url {
        font-family: monospace;
        font-size: 0.78rem;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .atvs-file-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.78rem; font-weight: 700;
        border-radius: 8px; text-decoration: none;
        transition: all 0.18s ease;
        flex-shrink: 0;
    }
    .atvs-file-btn:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }

    /* Description card */
    .atvs-desc-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .atvs-desc-text {
        margin: 0;
        font-size: 0.9rem;
        color: #1e293b;
        line-height: 1.65;
        white-space: pre-wrap;
    }

    /* Info card */
    .atvs-info-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
    }
    .atvs-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px dashed #f1f5f9;
        flex-wrap: wrap;
    }
    .atvs-info-row:last-child { border-bottom: 0; }
    .atvs-info-label {
        font-size: 0.74rem;
        font-weight: 800;
        color: #7f1d1d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .atvs-info-row strong {
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
    }

    /* Form */
    .atvs-form-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .atvs-flabel {
        display: block;
        font-weight: 800;
        color: #7f1d1d;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 8px;
    }
    .atvs-flabel i { color: #dc2626; margin-right: 5px; }

    .atvs-decision-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .atvs-decision-item {
        display: grid;
        grid-template-columns: 36px 1fr;
        grid-template-rows: auto auto;
        column-gap: 12px;
        padding: 12px 14px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.18s ease;
        position: relative;
    }
    .atvs-decision-item input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .atvs-decision-icon {
        grid-row: 1 / 3;
        width: 36px; height: 36px;
        border-radius: 10px;
        display: grid; place-items: center;
        font-size: 1.1rem;
        align-self: center;
    }
    .atvs-decision-item strong { font-size: 0.9rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .atvs-decision-item small { font-size: 0.74rem; color: #64748b; line-height: 1.3; }

    .atvs-decision-item.is-success .atvs-decision-icon { background: #dcfce7; color: #16a34a; }
    .atvs-decision-item.is-info    .atvs-decision-icon { background: #cffafe; color: #0e7490; }
    .atvs-decision-item.is-danger  .atvs-decision-icon { background: #fee2e2; color: #dc2626; }

    .atvs-decision-item:hover { background: #fafafa; }
    .atvs-decision-item.is-success:has(input:checked) { background: #f0fdf4; border-color: #16a34a; box-shadow: 0 4px 12px rgba(22,163,74,0.15); }
    .atvs-decision-item.is-info:has(input:checked)    { background: #ecfeff; border-color: #0e7490; box-shadow: 0 4px 12px rgba(14,116,144,0.15); }
    .atvs-decision-item.is-danger:has(input:checked)  { background: #fef2f2; border-color: #dc2626; box-shadow: 0 4px 12px rgba(220,38,38,0.15); }

    .atvs-textarea {
        margin-bottom: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.86rem;
    }
    .atvs-textarea:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
    }

    .atvs-submit-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 11px 16px;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: #fff;
        font-size: 0.92rem;
        font-weight: 800;
        border-radius: 10px;
        border: 0;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
        transition: all 0.2s ease;
    }
    .atvs-submit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(22, 163, 74, 0.35);
    }
</style>
@endsection
