@extends('layouts.app')

@section('title', 'Quản lý Thư viện tài nguyên')

@section('content')
@php
    $baseQuery = \App\Models\TaiNguyenBuoiHoc::query();
    $stats = [
        'tong'       => (clone $baseQuery)->count(),
        'cho_duyet'  => (clone $baseQuery)->where('trang_thai_duyet', 'cho_duyet')->count(),
        'da_duyet'   => (clone $baseQuery)->where('trang_thai_duyet', 'da_duyet')->count(),
        'tu_choi'    => (clone $baseQuery)->where('trang_thai_duyet', 'tu_choi')->count(),
    ];
    $hasFilter = request()->filled('trang_thai_duyet') || request()->filled('loai_tai_nguyen');
@endphp

<div class="container-fluid admin-page-x tv-page">
    <div class="apx-welcome tv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-university"></i></div>
        <div class="apx-welcome-text">
            <div class="tv-tag-row">
                <span class="tv-loai-badge"><i class="fas fa-folder-tree"></i> THƯ VIỆN HỆ THỐNG</span>
                <span class="tv-status-badge"><i class="fas fa-layer-group"></i> {{ $stats['tong'] }} tài nguyên</span>
                @if($stats['cho_duyet'] > 0)
                    <span class="tv-pending-badge"><i class="fas fa-hourglass-half"></i> {{ $stats['cho_duyet'] }} chờ duyệt</span>
                @endif
            </div>
            <h4>Quản lý thư viện tài nguyên</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $stats['da_duyet'] }} đã duyệt</span>
                <span class="tv-sep">·</span>
                <span><i class="fas fa-times-circle"></i> {{ $stats['tu_choi'] }} từ chối</span>
                <span class="tv-sep">·</span>
                <span><i class="fas fa-shield-halved"></i> Duyệt và quản lý tài nguyên từ giảng viên</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle"><i class="fas fa-arrow-left"></i> <span>Dashboard</span></a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">1</span><div><h2><i class="fas fa-chart-pie"></i> Tổng quan tài nguyên</h2><p>Bốn chỉ số nhanh cho thư viện hệ thống.</p></div></div>
        </header>
        <div class="row g-3">
            <div class="col-md-3 col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-folder-tree"></i></div><div class="aps-text"><strong>{{ $stats['tong'] }}</strong><small>Tổng tài nguyên</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-warning"><div class="aps-icon"><i class="fas fa-hourglass-half"></i></div><div class="aps-text"><strong>{{ $stats['cho_duyet'] }}</strong><small>Chờ duyệt</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-success"><div class="aps-icon"><i class="fas fa-circle-check"></i></div><div class="aps-text"><strong>{{ $stats['da_duyet'] }}</strong><small>Đã duyệt</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-danger"><div class="aps-icon"><i class="fas fa-times-circle"></i></div><div class="aps-text"><strong>{{ $stats['tu_choi'] }}</strong><small>Từ chối</small></div></div></div>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">2</span><div><h2><i class="fas fa-filter"></i> Bộ lọc</h2><p>Lọc theo trạng thái duyệt và loại tài nguyên.</p></div></div>
            @if($hasFilter)<div class="apx-section-meta"><span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;"><i class="fas fa-filter"></i> Đang lọc</span></div>@endif
        </header>
        <div class="tv-filter-card">
            <form action="{{ route('admin.thu-vien.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="tv-flabel">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="nhap"          {{ request('trang_thai_duyet')=='nhap'?'selected':'' }}>Nháp</option>
                        <option value="cho_duyet"     {{ request('trang_thai_duyet')=='cho_duyet'?'selected':'' }}>Chờ duyệt</option>
                        <option value="da_duyet"      {{ request('trang_thai_duyet')=='da_duyet'?'selected':'' }}>Đã duyệt</option>
                        <option value="can_chinh_sua" {{ request('trang_thai_duyet')=='can_chinh_sua'?'selected':'' }}>Cần sửa</option>
                        <option value="tu_choi"       {{ request('trang_thai_duyet')=='tu_choi'?'selected':'' }}>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="tv-flabel">Loại tài nguyên</label>
                    <select name="loai_tai_nguyen" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả loại</option>
                        <option value="video"     {{ request('loai_tai_nguyen')=='video'?'selected':'' }}>Video</option>
                        <option value="pdf"       {{ request('loai_tai_nguyen')=='pdf'?'selected':'' }}>PDF</option>
                        <option value="link_ngoai"{{ request('loai_tai_nguyen')=='link_ngoai'?'selected':'' }}>Link ngoài</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('admin.thu-vien.index') }}" class="btn btn-outline-secondary fw-bold"><i class="fas fa-rotate-left me-1"></i> Đặt lại</a>
                </div>
            </form>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">3</span><div><h2><i class="fas fa-list"></i> Danh sách tài nguyên</h2><p>Phân loại theo loại tài liệu, xem trước nội dung trực tiếp và duyệt/xóa nhanh.</p></div></div>
            <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ $taiNguyens->total() }}</strong> tài nguyên</span></div>
        </header>

        @if($taiNguyens->isEmpty())
            <div class="tv-empty">
                <div class="tv-empty-icon"><i class="fas fa-folder-open"></i></div>
                <h5>Không tìm thấy tài nguyên nào</h5>
                <p>Thử thay đổi bộ lọc hoặc chờ giảng viên gửi tài nguyên mới lên hệ thống.</p>
            </div>
        @else
            @php
                $duyetMeta = [
                    'nhap'          => ['label' => 'Nháp',     'class' => 'is-secondary'],
                    'cho_duyet'     => ['label' => 'Chờ duyệt','class' => 'is-warning'],
                    'da_duyet'      => ['label' => 'Đã duyệt', 'class' => 'is-success'],
                    'can_chinh_sua' => ['label' => 'Cần sửa',  'class' => 'is-info'],
                    'tu_choi'       => ['label' => 'Từ chối',  'class' => 'is-danger'],
                ];

                $groupCounts = [
                    'all'      => $taiNguyens->count(),
                    'video'    => $taiNguyens->where('loai_tai_nguyen', 'video')->count(),
                    'pdf'      => $taiNguyens->where('loai_tai_nguyen', 'pdf')->count(),
                    'doc'      => $taiNguyens->whereIn('loai_tai_nguyen', ['word', 'powerpoint', 'excel'])->count(),
                    'image'    => $taiNguyens->where('loai_tai_nguyen', 'image')->count(),
                    'audio'    => $taiNguyens->where('loai_tai_nguyen', 'audio')->count(),
                    'archive'  => $taiNguyens->where('loai_tai_nguyen', 'archive')->count(),
                    'link'     => $taiNguyens->where('loai_tai_nguyen', 'link_ngoai')->count(),
                    'other'    => $taiNguyens->whereNotIn('loai_tai_nguyen', ['video','pdf','word','powerpoint','excel','image','audio','archive','link_ngoai'])->count(),
                ];

                $tabs = [
                    'all'     => ['label' => 'Tất cả',   'icon' => 'fa-folder-tree', 'color' => '#dc2626'],
                    'video'   => ['label' => 'Video',    'icon' => 'fa-play-circle', 'color' => '#dc2626'],
                    'pdf'     => ['label' => 'PDF',      'icon' => 'fa-file-pdf',    'color' => '#dc2626'],
                    'doc'     => ['label' => 'Tài liệu', 'icon' => 'fa-file-lines',  'color' => '#16a34a'],
                    'image'   => ['label' => 'Hình ảnh', 'icon' => 'fa-image',       'color' => '#0ea5e9'],
                    'audio'   => ['label' => 'Audio',    'icon' => 'fa-volume-high', 'color' => '#7c3aed'],
                    'archive' => ['label' => 'File nén', 'icon' => 'fa-file-zipper', 'color' => '#475569'],
                    'link'    => ['label' => 'Link',     'icon' => 'fa-link',        'color' => '#0ea5e9'],
                    'other'   => ['label' => 'Khác',     'icon' => 'fa-file',        'color' => '#64748b'],
                ];

                $typeToGroup = function($loai) {
                    return match($loai) {
                        'video' => 'video',
                        'pdf'   => 'pdf',
                        'word', 'powerpoint', 'excel' => 'doc',
                        'image' => 'image',
                        'audio' => 'audio',
                        'archive' => 'archive',
                        'link_ngoai' => 'link',
                        default => 'other',
                    };
                };
            @endphp

            {{-- Tabs phân loại --}}
            <div class="atv-tabs" id="atvTypeTabs">
                @foreach($tabs as $key => $meta)
                    @if($groupCounts[$key] > 0 || $key === 'all')
                        <button type="button"
                                class="atv-tab {{ $key === 'all' ? 'is-active' : '' }}"
                                data-group="{{ $key }}"
                                style="--tab-color: {{ $meta['color'] }};">
                            <i class="fas {{ $meta['icon'] }}"></i>
                            <span>{{ $meta['label'] }}</span>
                            <span class="atv-tab-count">{{ $groupCounts[$key] }}</span>
                        </button>
                    @endif
                @endforeach
            </div>

            <div class="atv-wrap" id="atvLibraryWrap">
                {{-- Left: list --}}
                <div class="atv-list">
                    @foreach($taiNguyens as $tn)
                        @php
                            $loaiBadge = match($tn->loai_tai_nguyen) {
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
                            $duyet = $duyetMeta[$tn->trang_thai_duyet] ?? ['label' => 'Khác', 'class' => 'is-secondary'];
                        @endphp
                        <button type="button"
                                class="atv-item {{ $loop->first ? 'is-active' : '' }}"
                                data-target="atvPreview-{{ $tn->id }}"
                                data-group="{{ $typeToGroup($tn->loai_tai_nguyen) }}">
                            <div class="atv-thumb" style="background: {{ $loaiBadge['color'] }};">
                                <i class="fas {{ $loaiBadge['icon'] }}"></i>
                            </div>
                            <div class="atv-text">
                                <div class="atv-meta-line">
                                    <span class="atv-type" style="color: {{ $loaiBadge['color'] }};">
                                        <i class="fas {{ $loaiBadge['icon'] }}"></i> {{ $loaiBadge['label'] }}
                                    </span>
                                    <span class="atv-status-pill {{ $duyet['class'] }}">{{ $duyet['label'] }}</span>
                                </div>
                                <div class="atv-title">{{ $tn->tieu_de }}</div>
                                <div class="atv-uploader-row">
                                    <i class="fas fa-user"></i> {{ $tn->nguoiTao->ho_ten ?? 'N/A' }}
                                    <span class="atv-sep">·</span>
                                    {{ $tn->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                            <i class="fas fa-chevron-right atv-arrow"></i>
                        </button>
                    @endforeach
                </div>

                {{-- Right: preview --}}
                <div class="atv-preview-area">
                    @foreach($taiNguyens as $tn)
                        @php
                            $loaiBadge = match($tn->loai_tai_nguyen) {
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
                            $duyet = $duyetMeta[$tn->trang_thai_duyet] ?? ['label' => 'Khác', 'class' => 'is-secondary'];

                            $youtubeId = null;
                            if ($tn->is_external && !empty($tn->link_ngoai)) {
                                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $tn->link_ngoai, $m)) {
                                    $youtubeId = $m[1];
                                }
                            }

                            $sizeLabel = null;
                            if ($tn->file_size) {
                                if ($tn->file_size >= 1048576) $sizeLabel = number_format($tn->file_size / 1048576, 1) . ' MB';
                                elseif ($tn->file_size >= 1024) $sizeLabel = number_format($tn->file_size / 1024, 0) . ' KB';
                                else $sizeLabel = $tn->file_size . ' B';
                            }

                            $sourceText = $tn->is_external
                                ? (parse_url((string) $tn->link_ngoai, PHP_URL_HOST) ?: 'Liên kết ngoài')
                                : ($tn->file_name ?? 'Tệp nội bộ');
                        @endphp
                        <div id="atvPreview-{{ $tn->id }}" class="atv-preview-pane {{ $loop->first ? 'is-active' : '' }}">
                            <div class="atv-preview">
                                @if($tn->loai_tai_nguyen === 'video' && !$tn->is_external && $tn->file_url)
                                    <video controls preload="metadata" class="atv-media">
                                        <source src="{{ $tn->file_url }}">
                                    </video>
                                @elseif($youtubeId)
                                    <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" allowfullscreen frameborder="0" class="atv-media"></iframe>
                                @elseif($tn->loai_tai_nguyen === 'image' && $tn->file_url)
                                    <img src="{{ $tn->file_url }}" alt="{{ $tn->tieu_de }}" class="atv-media">
                                @elseif($tn->loai_tai_nguyen === 'pdf' && $tn->file_url)
                                    <iframe src="{{ $tn->file_url }}" class="atv-media atv-pdf" frameborder="0"></iframe>
                                @elseif($tn->loai_tai_nguyen === 'audio' && $tn->file_url)
                                    <div class="atv-fallback audio-tone">
                                        <i class="fas fa-volume-high"></i>
                                        <audio controls preload="metadata" class="atv-audio">
                                            <source src="{{ $tn->file_url }}">
                                        </audio>
                                    </div>
                                @elseif($tn->is_external && !empty($tn->link_ngoai))
                                    <div class="atv-fallback link-tone">
                                        <i class="fas fa-link"></i>
                                        <h5>Liên kết ngoài</h5>
                                        <p>{{ $sourceText }}</p>
                                        <a href="{{ $tn->link_ngoai }}" target="_blank" rel="noopener" class="atv-fallback-link">
                                            <i class="fas fa-up-right-from-square"></i> Mở liên kết
                                        </a>
                                    </div>
                                @else
                                    <div class="atv-fallback">
                                        <i class="fas {{ $loaiBadge['icon'] }}"></i>
                                        <h5>{{ $tn->loai_label }}</h5>
                                        <p>Không có bản xem trước cho loại tài nguyên này.</p>
                                        @if($tn->file_url)
                                            <a href="{{ $tn->file_url }}" target="_blank" rel="noopener" class="atv-fallback-link">
                                                <i class="fas fa-download"></i> Tải xuống
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                <span class="atv-type-badge" style="background: {{ $loaiBadge['color'] }};">
                                    <i class="fas {{ $loaiBadge['icon'] }}"></i> {{ $loaiBadge['label'] }}
                                </span>
                            </div>

                            <div class="atv-info">
                                <div class="atv-info-tags">
                                    <span class="atv-status-pill {{ $duyet['class'] }}">{{ $duyet['label'] }}</span>
                                    <span class="atv-soft-tag"><i class="fas fa-tag"></i> {{ $tn->loai_label }}</span>
                                </div>

                                <h5 class="atv-info-title">{{ $tn->tieu_de }}</h5>
                                @if($tn->mo_ta)
                                    <p class="atv-info-desc">{{ $tn->mo_ta }}</p>
                                @endif

                                <div class="atv-info-meta">
                                    <span><i class="fas fa-user"></i> {{ $tn->nguoiTao->ho_ten ?? 'N/A' }} ({{ $tn->vai_tro_nguoi_tao ?? '—' }})</span>
                                    <span class="atv-sep">·</span>
                                    <span><i class="fas fa-file"></i> {{ \Illuminate\Support\Str::limit($sourceText, 50) }}</span>
                                    @if($sizeLabel)
                                        <span class="atv-sep">·</span>
                                        <span><i class="fas fa-weight-hanging"></i> {{ $sizeLabel }}</span>
                                    @endif
                                    <span class="atv-sep">·</span>
                                    <span><i class="far fa-calendar-alt"></i>
                                        @if($tn->ngay_gui_duyet)
                                            Gửi: {{ $tn->ngay_gui_duyet->format('d/m/Y H:i') }}
                                        @else
                                            Tạo: {{ $tn->created_at->format('d/m/Y H:i') }}
                                        @endif
                                    </span>
                                </div>

                                @if($tn->ghi_chu_admin)
                                    <div class="atv-feedback">
                                        <div class="atv-feedback-title"><i class="fas fa-message"></i> Ghi chú đã gửi cho giảng viên</div>
                                        <p>{{ $tn->ghi_chu_admin }}</p>
                                    </div>
                                @endif

                                <div class="atv-buttons">
                                    @if($tn->file_url)
                                        <a href="{{ $tn->file_url }}" target="_blank" rel="noopener" class="atv-btn-secondary">
                                            <i class="fas fa-up-right-from-square"></i> Mở file
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.thu-vien.show', $tn->id) }}" class="atv-btn-primary">
                                        <i class="fas fa-magnifying-glass"></i> Xem chi tiết &amp; Duyệt
                                    </a>
                                    <button type="button" class="atv-btn-danger"
                                            onclick="if(confirm('Xóa tài nguyên này khỏi hệ thống?')) document.getElementById('delete-form-{{ $tn->id }}').submit();">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                    <form id="delete-form-{{ $tn->id }}" action="{{ route('admin.thu-vien.destroy', $tn->id) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($taiNguyens->hasPages())
                <div class="tv-pagination mt-3">{{ $taiNguyens->links('pagination::bootstrap-5') }}</div>
            @endif
        @endif
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .tv-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .tv-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .tv-page .apx-section-title h2 i { color: #dc2626; }
    .tv-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .tv-page .apx-meta-pill strong { color: #b91c1c; }

    .tv-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .tv-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .tv-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .tv-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .tv-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: tvPulse 1.6s ease-out infinite; }
    @keyframes tvPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.tv-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.tv-welcome p i { color: #fef3c7; margin-right: 4px; }
    .tv-sep { opacity: 0.5; }

    .tv-filter-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; }
    .tv-flabel { display: block; font-weight: 800; color: #7f1d1d; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }

    .tv-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
    .tv-table thead { background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%); font-size: 0.7rem; text-transform: uppercase; color: #7f1d1d; letter-spacing: 0.5px; }
    .tv-table thead th { padding: 12px 10px; font-weight: 800; border-bottom: 1px solid #fecaca; }
    .tv-table tbody td { padding: 14px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .tv-table tbody tr:last-child td { border-bottom: 0; }
    .tv-table tbody tr:hover { background: #fafafa; }

    .tv-thumb { flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px; color: #fff; display: grid; place-items: center; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .tv-name { font-size: 0.92rem; font-weight: 800; color: #0f172a; line-height: 1.3; }
    .tv-meta { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .tv-meta i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .tv-avatar { flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; color: #fff; font-weight: 800; font-size: 0.82rem; display: grid; place-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .tv-uploader { font-size: 0.84rem; font-weight: 700; color: #0f172a; }
    .tv-uploader-sub { font-size: 0.72rem; color: #94a3b8; font-weight: 600; margin-top: 2px; }
    .tv-uploader-sub i { color: #1d4ed8; margin-right: 4px; font-size: 0.62rem; }

    .tv-loai-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: #eff6ff; color: #1d4ed8; font-size: 0.72rem; font-weight: 800; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; }
    .tv-loai-pill i { font-size: 0.62rem; }

    .tv-status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; font-size: 0.72rem; font-weight: 800; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; }
    .tv-status-pill i { font-size: 0.62rem; }
    .tv-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .tv-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .tv-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .tv-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .tv-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .tv-time { font-size: 0.82rem; font-weight: 700; color: #0f172a; }
    .tv-time i { color: #dc2626; margin-right: 4px; font-size: 0.7rem; }
    .tv-time-sub { font-size: 0.7rem; color: #94a3b8; font-weight: 600; margin-top: 2px; }

    .tv-actions { display: inline-flex; gap: 4px; }
    .tv-action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; color: #1d4ed8; display: grid; place-items: center; cursor: pointer; font-size: 0.78rem; text-decoration: none; transition: all 0.18s ease; padding: 0; }
    .tv-action-btn:hover { border-color: #dc2626; background: #dc2626; color: #fff; transform: translateY(-1px); }
    .tv-action-btn.danger { color: #dc2626; }

    .tv-pagination { padding: 14px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; }
    .tv-pagination nav { margin: 0; }

    .tv-empty { padding: 60px 30px; text-align: center; }
    .tv-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .tv-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .tv-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }

    /* ===== Tabs phân loại ===== */
    .atv-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
        padding: 14px 16px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }
    .atv-tab {
        --tab-color: #dc2626;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .atv-tab i { font-size: 0.78rem; opacity: 0.85; color: var(--tab-color); }
    .atv-tab:hover { background: #fef2f2; border-color: var(--tab-color); color: var(--tab-color); }
    .atv-tab.is-active {
        background: var(--tab-color);
        border-color: var(--tab-color);
        color: #fff;
        box-shadow: 0 4px 12px color-mix(in srgb, var(--tab-color) 25%, transparent);
    }
    .atv-tab.is-active i { color: #fff; opacity: 1; }
    .atv-tab-count {
        display: inline-grid; place-items: center;
        min-width: 22px; padding: 2px 7px;
        background: #f1f5f9; color: #64748b;
        font-size: 0.7rem; font-weight: 800; border-radius: 999px;
    }
    .atv-tab.is-active .atv-tab-count {
        background: rgba(255,255,255,0.25);
        color: #fff;
    }
    .atv-empty-filter {
        padding: 40px 20px;
        text-align: center;
        color: #94a3b8;
    }
    .atv-empty-filter i { font-size: 1.8rem; opacity: 0.5; display: block; margin-bottom: 10px; color: #dc2626; }
    .atv-empty-filter p { font-size: 0.86rem; margin: 0; }

    /* ===== Wrap interactive ===== */
    .atv-wrap {
        display: grid;
        grid-template-columns: 340px 1fr;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        min-height: 540px;
    }
    @media (max-width: 991.98px) {
        .atv-wrap { grid-template-columns: 1fr; }
    }

    .atv-list {
        background: #fafafa;
        border-right: 1px solid #fecaca;
        max-height: 720px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #fecaca #fef2f2;
    }
    .atv-list::-webkit-scrollbar { width: 6px; }
    .atv-list::-webkit-scrollbar-track { background: #fef2f2; }
    .atv-list::-webkit-scrollbar-thumb { background: #fecaca; border-radius: 999px; }
    @media (max-width: 991.98px) {
        .atv-list { border-right: 0; border-bottom: 1px solid #fecaca; max-height: 400px; }
    }

    .atv-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 12px 14px;
        background: transparent;
        border: 0;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .atv-item:hover { background: #fef2f2; }
    .atv-item.is-active {
        background: #fff;
        border-left: 3px solid #dc2626;
        padding-left: 11px;
    }
    .atv-item.is-active .atv-arrow { color: #dc2626; opacity: 1; transform: translateX(0); }

    .atv-thumb {
        flex-shrink: 0;
        width: 40px; height: 40px;
        border-radius: 10px;
        color: #fff;
        display: grid; place-items: center;
        font-size: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .atv-text { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
    .atv-meta-line { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .atv-type {
        font-size: 0.66rem; font-weight: 800;
        letter-spacing: 0.4px; text-transform: uppercase;
    }
    .atv-type i { font-size: 0.58rem; margin-right: 3px; }
    .atv-status-pill { padding: 2px 8px; font-size: 0.62rem; font-weight: 800; border-radius: 999px; }
    .atv-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .atv-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .atv-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .atv-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .atv-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .atv-title {
        font-size: 0.86rem; font-weight: 800; color: #0f172a;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .atv-uploader-row {
        font-size: 0.72rem; color: #94a3b8; font-weight: 600;
    }
    .atv-uploader-row i { color: #1d4ed8; margin-right: 4px; font-size: 0.62rem; }
    .atv-sep { opacity: 0.5; margin: 0 3px; }
    .atv-arrow {
        color: #cbd5e1;
        font-size: 0.78rem;
        opacity: 0.6;
        transform: translateX(-4px);
        transition: all 0.18s ease;
        flex-shrink: 0;
    }

    /* Preview area */
    .atv-preview-area { padding: 18px 20px; background: #fff; }
    .atv-preview-pane { display: none; }
    .atv-preview-pane.is-active { display: block; animation: atvFadeIn 0.2s ease; }
    @keyframes atvFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .atv-preview {
        position: relative;
        background: #0f172a;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 16/9;
        margin-bottom: 16px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    }
    .atv-media { width: 100%; height: 100%; object-fit: cover; display: block; border: 0; }
    .atv-pdf { background: #fff; }

    .atv-fallback {
        width: 100%; height: 100%;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        color: #fff; text-align: center;
        padding: 22px;
        background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
    }
    .atv-fallback i { font-size: 3rem; margin-bottom: 14px; opacity: 0.95; }
    .atv-fallback h5 { font-size: 1rem; font-weight: 800; margin: 0 0 4px; }
    .atv-fallback p { font-size: 0.85rem; margin: 0 0 12px; opacity: 0.85; word-break: break-all; }
    .atv-fallback.audio-tone {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        gap: 14px;
    }
    .atv-fallback.link-tone { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); }
    .atv-audio { width: 100%; max-width: 360px; }
    .atv-fallback-link {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.4);
        color: #fff;
        font-size: 0.82rem; font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .atv-fallback-link:hover { background: rgba(255,255,255,0.3); color: #fff; }

    .atv-type-badge {
        position: absolute; top: 10px; left: 10px;
        padding: 4px 12px;
        color: #fff;
        font-size: 0.7rem; font-weight: 800;
        letter-spacing: 0.5px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        display: inline-flex; align-items: center; gap: 5px;
    }
    .atv-type-badge i { font-size: 0.62rem; }

    /* Info area */
    .atv-info-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
    .atv-soft-tag {
        display: inline-flex; align-items: center;
        padding: 3px 10px;
        background: #f1f5f9; color: #475569;
        font-size: 0.7rem; font-weight: 800; border-radius: 999px;
    }
    .atv-soft-tag i { margin-right: 4px; font-size: 0.62rem; }

    .atv-info-title {
        font-size: 1.15rem; font-weight: 800; color: #0f172a;
        line-height: 1.35; margin: 0 0 8px;
    }
    .atv-info-desc {
        font-size: 0.88rem; color: #475569;
        line-height: 1.6; margin: 0 0 12px;
        white-space: pre-wrap;
    }
    .atv-info-meta {
        display: flex; flex-wrap: wrap; gap: 6px;
        align-items: center;
        font-size: 0.78rem; color: #64748b; font-weight: 600;
        margin-bottom: 14px;
        padding: 10px 12px;
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
    }
    .atv-info-meta i { color: #dc2626; margin-right: 4px; font-size: 0.7rem; }

    .atv-feedback {
        margin-bottom: 14px;
        padding: 10px 14px;
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        border-radius: 8px;
    }
    .atv-feedback-title {
        font-size: 0.78rem; font-weight: 800; color: #92400e;
        margin-bottom: 4px;
    }
    .atv-feedback-title i { color: #f59e0b; margin-right: 5px; }
    .atv-feedback p {
        font-size: 0.86rem; color: #78350f;
        margin: 0; line-height: 1.5;
    }

    .atv-buttons { display: flex; flex-wrap: wrap; gap: 8px; }
    .atv-btn-primary, .atv-btn-secondary, .atv-btn-danger {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 18px;
        font-size: 0.84rem; font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        border: 0; cursor: pointer;
        transition: all 0.18s ease;
    }
    .atv-btn-primary {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);
    }
    .atv-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(220, 38, 38, 0.32); color: #fff; }
    .atv-btn-secondary {
        background: #fff; color: #475569;
        border: 1px solid #e2e8f0;
    }
    .atv-btn-secondary:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .atv-btn-danger {
        background: #fff; color: #dc2626;
        border: 1px solid #fecaca;
    }
    .atv-btn-danger:hover { background: #dc2626; border-color: #dc2626; color: #fff; }
    .atv-btn-primary i, .atv-btn-secondary i, .atv-btn-danger i { font-size: 0.78rem; }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrap = document.getElementById('atvLibraryWrap');
    if (!wrap) return;

    const items = wrap.querySelectorAll('.atv-item');
    const panes = wrap.querySelectorAll('.atv-preview-pane');
    const tabs  = document.querySelectorAll('#atvTypeTabs .atv-tab');
    const list  = wrap.querySelector('.atv-list');

    function pauseAllExcept(activeId) {
        panes.forEach(p => {
            if (p.id !== activeId) {
                p.querySelectorAll('video, audio').forEach(m => { try { m.pause(); } catch(e){} });
                p.querySelectorAll('iframe[src*="youtube.com"]').forEach(f => {
                    const src = f.src; f.src = src;
                });
            }
        });
    }

    function activateItem(item) {
        if (!item) return;
        const target = item.dataset.target;
        panes.forEach(p => p.classList.toggle('is-active', p.id === target));
        pauseAllExcept(target);
        items.forEach(i => i.classList.remove('is-active'));
        item.classList.add('is-active');
    }

    items.forEach(btn => {
        btn.addEventListener('click', function () {
            activateItem(this);
        });
    });

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const group = this.dataset.group;
            tabs.forEach(t => t.classList.remove('is-active'));
            this.classList.add('is-active');

            let firstVisible = null;
            items.forEach(item => {
                const visible = (group === 'all' || item.dataset.group === group);
                item.style.display = visible ? '' : 'none';
                if (visible && !firstVisible) firstVisible = item;
            });

            const oldEmpty = list.querySelector('.atv-empty-filter');
            if (oldEmpty) oldEmpty.remove();

            if (firstVisible) {
                const currentActive = wrap.querySelector('.atv-item.is-active');
                if (!currentActive || currentActive.style.display === 'none') {
                    activateItem(firstVisible);
                }
            } else {
                const empty = document.createElement('div');
                empty.className = 'atv-empty-filter';
                empty.innerHTML = '<i class="fas fa-folder-open"></i><p>Không có tài nguyên thuộc loại này.</p>';
                list.appendChild(empty);
                panes.forEach(p => p.classList.remove('is-active'));
            }
        });
    });
});
</script>
@endpush
@endsection
