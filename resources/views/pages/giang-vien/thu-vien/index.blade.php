@extends('layouts.app')

@section('title', 'Thư viện tài nguyên của tôi')

@section('content')
@php
    $approvalMeta = [
        'nhap' => ['label' => 'Nháp', 'class' => 'is-secondary'],
        'cho_duyet' => ['label' => 'Chờ duyệt', 'class' => 'is-warning'],
        'da_duyet' => ['label' => 'Đã duyệt', 'class' => 'is-success'],
        'can_chinh_sua' => ['label' => 'Cần chỉnh sửa', 'class' => 'is-info'],
        'tu_choi' => ['label' => 'Từ chối', 'class' => 'is-danger'],
    ];

    $scopeMeta = [
        'ca_nhan' => ['label' => 'Cá nhân', 'class' => 'is-slate'],
        'khoa_hoc' => ['label' => 'Trong khóa học', 'class' => 'is-blue'],
        'cong_khai' => ['label' => 'Công khai hệ thống', 'class' => 'is-green'],
    ];

    $typeOptions = [
        'video' => 'Video bài giảng',
        'pdf' => 'Tài liệu PDF',
        'word' => 'Tài liệu Word',
        'powerpoint' => 'Slide PowerPoint',
        'excel' => 'Bảng tính Excel',
        'image' => 'Hình ảnh',
        'audio' => 'Âm thanh',
        'archive' => 'File nén',
        'link_ngoai' => 'Liên kết ngoài',
        'tai_lieu_khac' => 'Tài liệu khác',
    ];

    $activeFilterCount = collect($filters ?? [])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="container-fluid admin-page-x tv-page">
    <div class="apx-welcome tv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-books"></i></div>
        <div class="apx-welcome-text">
            <div class="tv-tag-row">
                <span class="tv-library-badge">
                    <i class="fas fa-folder-open"></i> THƯ VIỆN GIẢNG VIÊN
                </span>
                <span class="tv-status-badge">
                    <i class="fas fa-database"></i> {{ $summary['total_count'] }} tài nguyên đang lưu
                </span>
                @if($activeFilterCount > 0)
                    <span class="tv-filter-badge">
                        <i class="fas fa-filter"></i> {{ $activeFilterCount }} bộ lọc đang bật
                    </span>
                @endif
            </div>
            <h4>Kho tài nguyên cá nhân để tái sử dụng cho nhiều buổi học và khóa học</h4>
            <p>
                <span><i class="fas fa-cloud-arrow-up"></i> Lưu file hoặc liên kết ngoài vào thư viện riêng</span>
                <span class="tv-sep">·</span>
                <span><i class="fas fa-circle-check"></i> Theo dõi trạng thái duyệt và mức sẵn sàng sử dụng</span>
                <span class="tv-sep">·</span>
                <span><i class="fas fa-share-nodes"></i> Chọn phạm vi cá nhân, khóa học hoặc công khai hệ thống</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Về dashboard</span>
            </a>
            <a href="{{ route('giang-vien.thu-vien.create') }}" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Thêm tài nguyên
            </a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-line"></i> Tổng quan thư viện</h2>
                    <p>Theo dõi nhanh số lượng tài nguyên, trạng thái duyệt và khả năng tái sử dụng trong giảng dạy.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $summary['ready_count'] }}</strong> sẵn sàng dùng</span>
                <span class="apx-meta-pill"><strong>{{ $summary['pending_count'] }}</strong> chờ duyệt</span>
            </div>
        </header>

        <div class="apx-section-body">
            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-md-6">
                    <div class="apx-stat tone-primary">
                        <div class="aps-icon"><i class="fas fa-folder-tree"></i></div>
                        <div class="aps-text">
                            <strong>{{ $summary['total_count'] }}</strong>
                            <small>Tổng tài nguyên</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="apx-stat tone-success">
                        <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                        <div class="aps-text">
                            <strong>{{ $summary['ready_count'] }}</strong>
                            <small>Sẵn sàng sử dụng</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="apx-stat tone-warning">
                        <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="aps-text">
                            <strong>{{ $summary['pending_count'] }}</strong>
                            <small>Đang chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="apx-stat tone-info">
                        <div class="aps-icon"><i class="fas fa-earth-asia"></i></div>
                        <div class="aps-text">
                            <strong>{{ $summary['public_count'] }}</strong>
                            <small>Công khai hệ thống</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tv-mini-grid">
                <div class="tv-mini-metric">
                    <span>Nháp</span>
                    <strong>{{ $summary['status_counts']['nhap'] }}</strong>
                </div>
                <div class="tv-mini-metric">
                    <span>Đã duyệt</span>
                    <strong>{{ $summary['status_counts']['da_duyet'] }}</strong>
                </div>
                <div class="tv-mini-metric">
                    <span>Cần chỉnh sửa</span>
                    <strong>{{ $summary['status_counts']['can_chinh_sua'] }}</strong>
                </div>
                <div class="tv-mini-metric">
                    <span>Từ chối</span>
                    <strong>{{ $summary['status_counts']['tu_choi'] }}</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-4">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">2</span>
                        <div>
                            <h2><i class="fas fa-sliders"></i> Bộ lọc thư viện</h2>
                            <p>Tìm nhanh tài nguyên theo từ khóa, loại, phạm vi sử dụng và trạng thái duyệt.</p>
                        </div>
                    </div>
                </header>

                <div class="apx-section-body">
                    <form method="GET" action="{{ route('giang-vien.thu-vien.index') }}" class="tv-filter-card">
                        <div class="mb-3">
                            <label class="tv-field-label"><i class="fas fa-magnifying-glass"></i> Từ khóa</label>
                            <div class="input-group tv-input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text"
                                       name="keyword"
                                       value="{{ $filters['keyword'] }}"
                                       class="form-control"
                                       placeholder="Tiêu đề, mô tả, file, liên kết...">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="tv-field-label"><i class="fas fa-shapes"></i> Loại tài nguyên</label>
                            <select name="loai_tai_nguyen" class="form-select">
                                <option value="">Tất cả loại</option>
                                @foreach($typeOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($filters['loai_tai_nguyen'] === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="tv-field-label"><i class="fas fa-layer-group"></i> Phạm vi sử dụng</label>
                            <select name="pham_vi_su_dung" class="form-select">
                                <option value="">Tất cả phạm vi</option>
                                <option value="ca_nhan" @selected($filters['pham_vi_su_dung'] === 'ca_nhan')>Cá nhân</option>
                                <option value="khoa_hoc" @selected($filters['pham_vi_su_dung'] === 'khoa_hoc')>Trong khóa học</option>
                                <option value="cong_khai" @selected($filters['pham_vi_su_dung'] === 'cong_khai')>Công khai hệ thống</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="tv-field-label"><i class="fas fa-stamp"></i> Trạng thái duyệt</label>
                            <select name="trang_thai_duyet" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                @foreach(['nhap' => 'Nháp', 'cho_duyet' => 'Chờ duyệt', 'da_duyet' => 'Đã duyệt', 'can_chinh_sua' => 'Cần chỉnh sửa', 'tu_choi' => 'Từ chối'] as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['trang_thai_duyet'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="tv-filter-actions">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="fas fa-filter me-1"></i> Áp dụng
                            </button>
                            <a href="{{ route('giang-vien.thu-vien.index') }}" class="btn btn-light border fw-bold px-4">
                                <i class="fas fa-rotate-left me-1"></i> Xóa lọc
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <div class="tv-side-stack">
                <aside class="tv-side-card">
                    <h3 class="tv-side-title">Phân bố tài nguyên</h3>
                    <div class="tv-info-grid">
                        <div class="tv-info-chip">
                            <small>Cá nhân</small>
                            <strong>{{ $summary['scope_counts']['ca_nhan'] }}</strong>
                        </div>
                        <div class="tv-info-chip">
                            <small>Trong khóa học</small>
                            <strong>{{ $summary['scope_counts']['khoa_hoc'] }}</strong>
                        </div>
                        <div class="tv-info-chip">
                            <small>Công khai</small>
                            <strong>{{ $summary['scope_counts']['cong_khai'] }}</strong>
                        </div>
                        <div class="tv-info-chip">
                            <small>Bộ lọc bật</small>
                            <strong>{{ $activeFilterCount }}</strong>
                        </div>
                    </div>
                </aside>

                <aside class="tv-side-card">
                    <h3 class="tv-side-title">Gợi ý sử dụng</h3>
                    <ul class="tv-side-list">
                        <li><i class="fas fa-check-circle"></i><span>Đặt tiêu đề rõ ràng để dễ tìm lại khi tái sử dụng cho bài giảng sau.</span></li>
                        <li><i class="fas fa-check-circle"></i><span>Tài nguyên cần chia sẻ rộng nên chọn phạm vi phù hợp ngay từ đầu để giảm thao tác chỉnh sửa.</span></li>
                        <li><i class="fas fa-check-circle"></i><span>Nếu tài nguyên bị yêu cầu chỉnh sửa, bạn có thể cập nhật lại rồi gửi duyệt ngay từ danh sách này.</span></li>
                    </ul>
                </aside>
            </div>
        </div>

        <div class="col-lg-8">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">3</span>
                        <div>
                            <h2><i class="fas fa-photo-film"></i> Danh sách tài nguyên</h2>
                            <p>Quản lý từng tài nguyên bằng thẻ trực quan, xem nhanh metadata và thao tác ngay trên cùng một màn hình.</p>
                        </div>
                    </div>
                    <div class="apx-section-meta">
                        <span class="apx-meta-pill"><strong>{{ $taiNguyens->total() }}</strong> tài nguyên</span>
                        @if($activeFilterCount > 0)
                            <span class="apx-meta-pill"><strong>{{ $activeFilterCount }}</strong> bộ lọc bật</span>
                        @endif
                    </div>
                </header>

                <div class="apx-section-body">
                    @if($taiNguyens->count() > 0)
                        @php
                            // Đếm theo nhóm loại
                            $groupCounts = [
                                'all'      => $taiNguyens->count(),
                                'video'    => $taiNguyens->where('loai_tai_nguyen', 'video')->count(),
                                'pdf'      => $taiNguyens->where('loai_tai_nguyen', 'pdf')->count(),
                                'doc'      => $taiNguyens->whereIn('loai_tai_nguyen', ['word', 'powerpoint', 'excel'])->count(),
                                'image'    => $taiNguyens->where('loai_tai_nguyen', 'image')->count(),
                                'audio'    => $taiNguyens->where('loai_tai_nguyen', 'audio')->count(),
                                'archive'  => $taiNguyens->where('loai_tai_nguyen', 'archive')->count(),
                                'link'     => $taiNguyens->where('loai_tai_nguyen', 'link_ngoai')->count(),
                                'other'    => $taiNguyens->whereIn('loai_tai_nguyen', ['tai_lieu_khac'])->count() + $taiNguyens->whereNotIn('loai_tai_nguyen', ['video','pdf','word','powerpoint','excel','image','audio','archive','link_ngoai'])->count(),
                            ];

                            $tabs = [
                                'all'     => ['label' => 'Tất cả',     'icon' => 'fa-folder-tree',  'color' => '#1d4ed8'],
                                'video'   => ['label' => 'Video',      'icon' => 'fa-play-circle',  'color' => '#dc2626'],
                                'pdf'     => ['label' => 'PDF',        'icon' => 'fa-file-pdf',     'color' => '#dc2626'],
                                'doc'     => ['label' => 'Tài liệu',   'icon' => 'fa-file-lines',   'color' => '#16a34a'],
                                'image'   => ['label' => 'Hình ảnh',   'icon' => 'fa-image',        'color' => '#0ea5e9'],
                                'audio'   => ['label' => 'Audio',      'icon' => 'fa-volume-high',  'color' => '#7c3aed'],
                                'archive' => ['label' => 'File nén',   'icon' => 'fa-file-zipper',  'color' => '#475569'],
                                'link'    => ['label' => 'Link',       'icon' => 'fa-link',         'color' => '#0ea5e9'],
                                'other'   => ['label' => 'Khác',       'icon' => 'fa-file',         'color' => '#64748b'],
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
                        <div class="tn-tl-tabs" id="tnTypeTabs">
                            @foreach($tabs as $key => $meta)
                                @if($groupCounts[$key] > 0 || $key === 'all')
                                    <button type="button"
                                            class="tn-tl-tab {{ $key === 'all' ? 'is-active' : '' }}"
                                            data-group="{{ $key }}"
                                            style="--tab-color: {{ $meta['color'] }};">
                                        <i class="fas {{ $meta['icon'] }}"></i>
                                        <span>{{ $meta['label'] }}</span>
                                        <span class="tn-tl-tab-count">{{ $groupCounts[$key] }}</span>
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        <div class="tn-tl-wrap" id="tnLibraryWrap">
                            {{-- Left: list of resources --}}
                            <div class="tn-tl-list">
                                @foreach($taiNguyens as $tn)
                                    @php
                                        $approvalKey = $tn->trang_thai_duyet ?: 'nhap';
                                        $approval = $approvalMeta[$approvalKey] ?? ['label' => 'Khác', 'class' => 'is-secondary'];

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
                                    @endphp
                                    <button type="button"
                                            class="tn-tl-item {{ $loop->first ? 'is-active' : '' }}"
                                            data-target="tnPreview-{{ $tn->id }}"
                                            data-group="{{ $typeToGroup($tn->loai_tai_nguyen) }}">
                                        <div class="tn-tl-thumb" style="background: {{ $loaiBadge['color'] }};">
                                            <i class="fas {{ $loaiBadge['icon'] }}"></i>
                                        </div>
                                        <div class="tn-tl-text">
                                            <div class="tn-tl-meta-line">
                                                <span class="tn-tl-type" style="color: {{ $loaiBadge['color'] }};">
                                                    <i class="fas {{ $loaiBadge['icon'] }}"></i> {{ $loaiBadge['label'] }}
                                                </span>
                                                <span class="tn-tl-status-pill {{ $approval['class'] }}">{{ $approval['label'] }}</span>
                                            </div>
                                            <div class="tn-tl-title">{{ $tn->tieu_de }}</div>
                                            <div class="tn-tl-date">
                                                <i class="far fa-calendar-alt"></i> {{ $tn->created_at->format('d/m/Y') }}
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-right tn-tl-arrow"></i>
                                    </button>
                                @endforeach
                            </div>

                            {{-- Right: preview pane --}}
                            <div class="tn-tl-preview-area">
                                @foreach($taiNguyens as $tn)
                                    @php
                                        $approvalKey = $tn->trang_thai_duyet ?: 'nhap';
                                        $approval = $approvalMeta[$approvalKey] ?? ['label' => 'Khác', 'class' => 'is-secondary'];
                                        $scopeKey = $tn->pham_vi_su_dung ?: 'ca_nhan';
                                        $scope = $scopeMeta[$scopeKey] ?? ['label' => 'Khác', 'class' => 'is-slate'];

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

                                        $youtubeId = null;
                                        if ($tn->is_external && !empty($tn->link_ngoai)) {
                                            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $tn->link_ngoai, $m)) {
                                                $youtubeId = $m[1];
                                            }
                                        }

                                        $sizeLabel = null;
                                        if ($tn->file_size) {
                                            if ($tn->file_size >= 1048576) {
                                                $sizeLabel = number_format($tn->file_size / 1048576, 1) . ' MB';
                                            } elseif ($tn->file_size >= 1024) {
                                                $sizeLabel = number_format($tn->file_size / 1024, 0) . ' KB';
                                            } else {
                                                $sizeLabel = $tn->file_size . ' B';
                                            }
                                        }

                                        $sourceText = $tn->is_external
                                            ? (parse_url((string) $tn->link_ngoai, PHP_URL_HOST) ?: 'Liên kết ngoài')
                                            : ($tn->file_name ?: $tn->original_file_name ?: 'Tệp nội bộ');
                                    @endphp
                                    <div id="tnPreview-{{ $tn->id }}" class="tn-tl-preview-pane {{ $loop->first ? 'is-active' : '' }}">
                                        {{-- Preview area --}}
                                        <div class="tn-tl-preview">
                                            @if($tn->loai_tai_nguyen === 'video' && !$tn->is_external && $tn->file_url)
                                                <video controls preload="metadata" class="tn-tl-media">
                                                    <source src="{{ $tn->file_url }}">
                                                </video>
                                            @elseif($youtubeId)
                                                <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" allowfullscreen frameborder="0" class="tn-tl-media"></iframe>
                                            @elseif($tn->loai_tai_nguyen === 'image' && $tn->file_url)
                                                <img src="{{ $tn->file_url }}" alt="{{ $tn->tieu_de }}" class="tn-tl-media">
                                            @elseif($tn->loai_tai_nguyen === 'pdf' && $tn->file_url)
                                                <iframe src="{{ $tn->file_url }}" class="tn-tl-media tn-tl-pdf" frameborder="0"></iframe>
                                            @elseif($tn->loai_tai_nguyen === 'audio' && $tn->file_url)
                                                <div class="tn-tl-fallback audio-tone">
                                                    <i class="fas fa-volume-high"></i>
                                                    <audio controls preload="metadata" class="tn-tl-audio">
                                                        <source src="{{ $tn->file_url }}">
                                                    </audio>
                                                </div>
                                            @elseif($tn->is_external && !empty($tn->link_ngoai))
                                                <div class="tn-tl-fallback link-tone">
                                                    <i class="fas fa-link"></i>
                                                    <h5>Liên kết ngoài</h5>
                                                    <p>{{ $sourceText }}</p>
                                                    <a href="{{ $tn->link_ngoai }}" target="_blank" rel="noopener" class="tn-tl-fallback-link">
                                                        <i class="fas fa-up-right-from-square"></i> Mở liên kết
                                                    </a>
                                                </div>
                                            @else
                                                <div class="tn-tl-fallback">
                                                    <i class="fas {{ $loaiBadge['icon'] }}"></i>
                                                    <h5>{{ $tn->loai_label }}</h5>
                                                    <p>Không có bản xem trước cho loại tài nguyên này.</p>
                                                    @if($tn->file_url)
                                                        <a href="{{ $tn->file_url }}" target="_blank" rel="noopener" class="tn-tl-fallback-link">
                                                            <i class="fas fa-download"></i> Tải xuống
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                            <span class="tn-tl-type-badge" style="background: {{ $loaiBadge['color'] }};">
                                                <i class="fas {{ $loaiBadge['icon'] }}"></i> {{ $loaiBadge['label'] }}
                                            </span>
                                        </div>

                                        {{-- Info area --}}
                                        <div class="tn-tl-info">
                                            <div class="tn-tl-info-tags">
                                                <span class="tv-status-pill {{ $scope['class'] }}">{{ $scope['label'] }}</span>
                                                <span class="tv-status-pill {{ $approval['class'] }}">{{ $approval['label'] }}</span>
                                                @if($tn->trang_thai_xu_ly && $tn->trang_thai_xu_ly !== 'khong_ap_dung')
                                                    @php
                                                        $processing = match($tn->trang_thai_xu_ly) {
                                                            'san_sang' => ['label' => 'Sẵn sàng', 'class' => 'is-success'],
                                                            'dang_xu_ly' => ['label' => 'Đang xử lý', 'class' => 'is-primary'],
                                                            'cho_xu_ly' => ['label' => 'Chờ xử lý', 'class' => 'is-warning'],
                                                            'loi_xu_ly' => ['label' => 'Lỗi xử lý', 'class' => 'is-danger'],
                                                            default => ['label' => 'Không cần xử lý', 'class' => 'is-secondary'],
                                                        };
                                                    @endphp
                                                    <span class="tv-status-pill {{ $processing['class'] }}">{{ $processing['label'] }}</span>
                                                @endif
                                            </div>

                                            <h5 class="tn-tl-info-title">{{ $tn->tieu_de }}</h5>
                                            <p class="tn-tl-info-desc">{{ $tn->mo_ta ?: $tn->file_status_message ?: 'Chưa có mô tả cho tài nguyên này.' }}</p>

                                            <div class="tn-tl-info-meta">
                                                <span><i class="fas fa-database"></i> {{ $tn->nguon_hien_thi_label }}</span>
                                                <span class="tn-tl-sep">·</span>
                                                <span><i class="fas fa-file"></i> {{ \Illuminate\Support\Str::limit($sourceText, 50) }}</span>
                                                @if($sizeLabel)
                                                    <span class="tn-tl-sep">·</span>
                                                    <span><i class="fas fa-weight-hanging"></i> {{ $sizeLabel }}</span>
                                                @endif
                                                <span class="tn-tl-sep">·</span>
                                                <span><i class="far fa-calendar-alt"></i> {{ $tn->created_at->format('d/m/Y H:i') }}</span>
                                            </div>

                                            @if($tn->ghi_chu_admin)
                                                <div class="tn-tl-feedback">
                                                    <div class="tn-tl-feedback-title"><i class="fas fa-message"></i> Phản hồi từ admin</div>
                                                    <p>{{ $tn->ghi_chu_admin }}</p>
                                                </div>
                                            @endif

                                            <div class="tn-tl-buttons">
                                                @if($tn->file_url)
                                                    <a href="{{ $tn->file_url }}" target="_blank" rel="noopener" class="tn-tl-btn-secondary">
                                                        <i class="fas fa-up-right-from-square"></i> Mở file
                                                    </a>
                                                @endif

                                                <a href="{{ route('giang-vien.thu-vien.edit', $tn->id) }}" class="tn-tl-btn-primary">
                                                    <i class="fas fa-pen"></i> Xem chi tiết / Sửa
                                                </a>

                                                @if(in_array($approvalKey, ['nhap', 'can_chinh_sua'], true))
                                                    <form action="{{ route('giang-vien.thu-vien.gui-duyet', $tn->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="tn-tl-btn-success">
                                                            <i class="fas fa-paper-plane"></i> Gửi duyệt
                                                        </button>
                                                    </form>
                                                @endif

                                                <button type="button" class="tn-tl-btn-danger"
                                                        onclick="if(confirm('Bạn có chắc muốn xóa tài nguyên này?')) document.getElementById('tn-delete-{{ $tn->id }}').submit();">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                                <form id="tn-delete-{{ $tn->id }}" action="{{ route('giang-vien.thu-vien.destroy', $tn->id) }}" method="POST" class="d-none">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($taiNguyens->hasPages())
                            <div class="tv-pagination-wrap mt-4">
                                {{ $taiNguyens->links() }}
                            </div>
                        @endif
                    @else
                        <div class="tv-empty-state">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h3>{{ $activeFilterCount > 0 ? 'Không có tài nguyên khớp bộ lọc' : 'Thư viện của bạn chưa có tài nguyên nào' }}</h3>
                            <p>
                                {{ $activeFilterCount > 0
                                    ? 'Hãy thử nới lỏng bộ lọc hoặc xóa bớt điều kiện để xem thêm tài nguyên.'
                                    : 'Bạn có thể bắt đầu bằng cách tải lên file đầu tiên hoặc thêm một liên kết ngoài để dùng lại trong các bài giảng sau.' }}
                            </p>
                            @if($activeFilterCount > 0)
                                <a href="{{ route('giang-vien.thu-vien.index') }}" class="btn btn-light border fw-bold px-4">
                                    <i class="fas fa-rotate-left me-1"></i> Xóa bộ lọc
                                </a>
                            @else
                                <a href="{{ route('giang-vien.thu-vien.create') }}" class="btn btn-primary fw-bold px-4">
                                    <i class="fas fa-plus me-1"></i> Thêm tài nguyên đầu tiên
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

@include('pages.giang-vien.thu-vien.partials.shared-styles')

<style>
    /* ===== Tabs phân loại ===== */
    .tn-tl-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 0 0 14px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }
    .tn-tl-tab {
        --tab-color: #1d4ed8;
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
    .tn-tl-tab i { font-size: 0.78rem; opacity: 0.85; color: var(--tab-color); }
    .tn-tl-tab:hover {
        background: #f8fafc;
        border-color: var(--tab-color);
        color: var(--tab-color);
    }
    .tn-tl-tab.is-active {
        background: var(--tab-color);
        border-color: var(--tab-color);
        color: #fff;
        box-shadow: 0 4px 12px color-mix(in srgb, var(--tab-color) 25%, transparent);
    }
    .tn-tl-tab.is-active i { color: #fff; opacity: 1; }
    .tn-tl-tab-count {
        display: inline-grid;
        place-items: center;
        min-width: 22px;
        padding: 2px 7px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 800;
        border-radius: 999px;
    }
    .tn-tl-tab.is-active .tn-tl-tab-count {
        background: rgba(255,255,255,0.25);
        color: #fff;
    }

    /* Empty state khi filter không có kết quả */
    .tn-tl-empty-filter {
        padding: 40px 20px;
        text-align: center;
        color: #94a3b8;
    }
    .tn-tl-empty-filter i { font-size: 1.8rem; opacity: 0.5; display: block; margin-bottom: 10px; color: #1d4ed8; }
    .tn-tl-empty-filter p { font-size: 0.86rem; margin: 0; }

    /* ===== Interactive list + preview cho thư viện ===== */
    .tn-tl-wrap {
        display: grid;
        grid-template-columns: 320px 1fr;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        min-height: 540px;
    }
    @media (max-width: 991.98px) {
        .tn-tl-wrap { grid-template-columns: 1fr; }
    }

    .tn-tl-list {
        background: #fafafa;
        border-right: 1px solid #e2e8f0;
        max-height: 720px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }
    .tn-tl-list::-webkit-scrollbar { width: 6px; }
    .tn-tl-list::-webkit-scrollbar-track { background: #f1f5f9; }
    .tn-tl-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    @media (max-width: 991.98px) {
        .tn-tl-list { border-right: 0; border-bottom: 1px solid #e2e8f0; max-height: 400px; }
    }

    .tn-tl-item {
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
    .tn-tl-item:hover {
        background: #eff6ff;
    }
    .tn-tl-item.is-active {
        background: #fff;
        border-left: 3px solid #1d4ed8;
        padding-left: 11px;
    }
    .tn-tl-item.is-active .tn-tl-arrow { color: #1d4ed8; opacity: 1; transform: translateX(0); }

    .tn-tl-thumb {
        flex-shrink: 0;
        width: 40px; height: 40px;
        border-radius: 10px;
        color: #fff;
        display: grid; place-items: center;
        font-size: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .tn-tl-text {
        flex: 1; min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .tn-tl-meta-line {
        display: flex; align-items: center; gap: 6px;
        flex-wrap: wrap;
    }
    .tn-tl-type {
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }
    .tn-tl-type i { font-size: 0.58rem; margin-right: 3px; }
    .tn-tl-status-pill {
        padding: 2px 8px;
        font-size: 0.62rem;
        font-weight: 800;
        border-radius: 999px;
    }
    .tn-tl-title {
        font-size: 0.86rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .tn-tl-date {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 600;
    }
    .tn-tl-date i { color: #1d4ed8; margin-right: 4px; font-size: 0.62rem; }
    .tn-tl-arrow {
        color: #cbd5e1;
        font-size: 0.78rem;
        opacity: 0.6;
        transform: translateX(-4px);
        transition: all 0.18s ease;
        flex-shrink: 0;
    }

    /* Preview area */
    .tn-tl-preview-area { padding: 18px 20px; background: #fff; }
    .tn-tl-preview-pane { display: none; }
    .tn-tl-preview-pane.is-active { display: block; animation: tnFadeIn 0.2s ease; }
    @keyframes tnFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .tn-tl-preview {
        position: relative;
        background: #0f172a;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 16/9;
        margin-bottom: 16px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    }
    .tn-tl-media {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
        border: 0;
    }
    .tn-tl-pdf { background: #fff; }

    .tn-tl-fallback {
        width: 100%; height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-align: center;
        padding: 22px;
        background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
    }
    .tn-tl-fallback i { font-size: 3rem; margin-bottom: 14px; opacity: 0.95; }
    .tn-tl-fallback h5 { font-size: 1rem; font-weight: 800; margin: 0 0 4px; }
    .tn-tl-fallback p { font-size: 0.85rem; margin: 0 0 12px; opacity: 0.85; word-break: break-all; }
    .tn-tl-fallback.audio-tone {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        gap: 14px;
    }
    .tn-tl-fallback.link-tone {
        background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
    }
    .tn-tl-audio { width: 100%; max-width: 360px; }
    .tn-tl-fallback-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.4);
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .tn-tl-fallback-link:hover {
        background: rgba(255,255,255,0.3);
        color: #fff;
    }

    .tn-tl-type-badge {
        position: absolute;
        top: 10px; left: 10px;
        padding: 4px 12px;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .tn-tl-type-badge i { font-size: 0.62rem; }

    /* Info area below preview */
    .tn-tl-info-tags {
        display: flex; flex-wrap: wrap; gap: 6px;
        margin-bottom: 10px;
    }

    .tn-tl-info-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin: 0 0 8px;
    }
    .tn-tl-info-desc {
        font-size: 0.88rem;
        color: #475569;
        line-height: 1.6;
        margin: 0 0 12px;
        white-space: pre-wrap;
    }
    .tn-tl-info-meta {
        display: flex; flex-wrap: wrap; gap: 6px;
        align-items: center;
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 14px;
        padding: 10px 12px;
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
    }
    .tn-tl-info-meta i { color: #1d4ed8; margin-right: 4px; font-size: 0.7rem; }
    .tn-tl-sep { opacity: 0.5; }

    .tn-tl-feedback {
        margin-bottom: 14px;
        padding: 10px 14px;
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        border-radius: 8px;
    }
    .tn-tl-feedback-title {
        font-size: 0.78rem;
        font-weight: 800;
        color: #92400e;
        margin-bottom: 4px;
    }
    .tn-tl-feedback-title i { color: #f59e0b; margin-right: 5px; }
    .tn-tl-feedback p {
        font-size: 0.86rem;
        color: #78350f;
        margin: 0;
        line-height: 1.5;
    }

    .tn-tl-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .tn-tl-btn-primary, .tn-tl-btn-secondary, .tn-tl-btn-success, .tn-tl-btn-danger {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        font-size: 0.84rem;
        font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        border: 0;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .tn-tl-btn-primary {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.22);
    }
    .tn-tl-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(29, 78, 216, 0.32);
        color: #fff;
    }
    .tn-tl-btn-secondary {
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .tn-tl-btn-secondary:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }
    .tn-tl-btn-success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.22);
    }
    .tn-tl-btn-success:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(22, 163, 74, 0.32);
        color: #fff;
    }
    .tn-tl-btn-danger {
        background: #fff;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .tn-tl-btn-danger:hover {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }
    .tn-tl-btn-primary i, .tn-tl-btn-secondary i, .tn-tl-btn-success i, .tn-tl-btn-danger i {
        font-size: 0.78rem;
    }

    /* Local status pill colors */
    .tv-status-pill { display: inline-flex; align-items: center; padding: 3px 10px; font-size: 0.7rem; font-weight: 800; border-radius: 999px; }
    .tv-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .tv-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .tv-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .tv-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .tv-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
    .tv-status-pill.is-primary   { background: #eff6ff; color: #1d4ed8; }
    .tv-status-pill.is-slate     { background: #f1f5f9; color: #475569; }
    .tv-status-pill.is-blue      { background: #eff6ff; color: #1d4ed8; }
    .tv-status-pill.is-green     { background: #dcfce7; color: #166534; }

    .tn-tl-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .tn-tl-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .tn-tl-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .tn-tl-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .tn-tl-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrap = document.getElementById('tnLibraryWrap');
    if (!wrap) return;

    const items = wrap.querySelectorAll('.tn-tl-item');
    const panes = wrap.querySelectorAll('.tn-tl-preview-pane');
    const tabs  = document.querySelectorAll('#tnTypeTabs .tn-tl-tab');
    const list  = wrap.querySelector('.tn-tl-list');

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
        panes.forEach(p => {
            p.classList.toggle('is-active', p.id === target);
        });
        pauseAllExcept(target);
        items.forEach(i => i.classList.remove('is-active'));
        item.classList.add('is-active');
    }

    // Click item to preview
    items.forEach(btn => {
        btn.addEventListener('click', function () {
            activateItem(this);
        });
    });

    // Tab filter
    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const group = this.dataset.group;
            tabs.forEach(t => t.classList.remove('is-active'));
            this.classList.add('is-active');

            // Show/hide items
            let firstVisible = null;
            items.forEach(item => {
                const visible = (group === 'all' || item.dataset.group === group);
                item.style.display = visible ? '' : 'none';
                if (visible && !firstVisible) firstVisible = item;
            });

            // Remove old empty notice
            const oldEmpty = list.querySelector('.tn-tl-empty-filter');
            if (oldEmpty) oldEmpty.remove();

            if (firstVisible) {
                // Switch preview to first visible item if current active not in group
                const currentActive = wrap.querySelector('.tn-tl-item.is-active');
                if (!currentActive || currentActive.style.display === 'none') {
                    activateItem(firstVisible);
                }
            } else {
                // No items, show empty notice
                const empty = document.createElement('div');
                empty.className = 'tn-tl-empty-filter';
                empty.innerHTML = '<i class="fas fa-folder-open"></i><p>Không có tài nguyên thuộc loại này.</p>';
                list.appendChild(empty);
                // Hide all preview panes
                panes.forEach(p => p.classList.remove('is-active'));
            }
        });
    });
});
</script>
@endpush
@endsection
