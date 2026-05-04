@extends('layouts.app', ['title' => 'Quản lý kết quả học tập'])

@section('content')
@php
    // Phân nhóm khóa học theo trạng thái vận hành
    $groupTitles = [
        'dang_day'        => ['label' => 'Đang dạy',         'icon' => 'fa-play-circle',     'color' => '#16a34a'],
        'san_sang'        => ['label' => 'Sẵn sàng',         'icon' => 'fa-circle-check',    'color' => '#0ea5e9'],
        'cho_giang_vien'  => ['label' => 'Chờ giảng viên',   'icon' => 'fa-clock',           'color' => '#f59e0b'],
        'hoan_thanh'      => ['label' => 'Đã hoàn thành',    'icon' => 'fa-trophy',          'color' => '#7c3aed'],
        'tam_hoan'        => ['label' => 'Tạm hoãn',         'icon' => 'fa-pause-circle',    'color' => '#94a3b8'],
        'huy'             => ['label' => 'Đã hủy',           'icon' => 'fa-circle-xmark',    'color' => '#dc2626'],
    ];

    $grouped = $courses->groupBy(fn ($c) => $c->trang_thai_van_hanh ?? 'khac');

    $stats = [
        'tong'        => $courses->count(),
        'dang_day'    => $grouped->get('dang_day', collect())->count(),
        'san_sang'    => $grouped->get('san_sang', collect())->count(),
        'hoan_thanh'  => $grouped->get('hoan_thanh', collect())->count(),
        'tong_hv'     => $courses->sum('hoc_vien_khoa_hocs_count'),
    ];

    $search = request('q', '');
    $filterGroup = request('group', '');

    if ($search !== '') {
        $courses = $courses->filter(fn ($c) =>
            stripos($c->ten_khoa_hoc ?? '', $search) !== false
            || stripos($c->ma_khoa_hoc ?? '', $search) !== false
        );
        $grouped = $courses->groupBy(fn ($c) => $c->trang_thai_van_hanh ?? 'khac');
    }
    if ($filterGroup) {
        $grouped = $grouped->only([$filterGroup]);
    }

    $hasFilter = $search !== '' || $filterGroup !== '';
@endphp

<div class="container-fluid admin-page-x kqht-page">
    <div class="apx-welcome kqht-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-chart-column"></i></div>
        <div class="apx-welcome-text">
            <div class="kqht-tag-row">
                <span class="kqht-loai-badge"><i class="fas fa-trophy"></i> KẾT QUẢ HỌC TẬP</span>
                <span class="kqht-status-badge"><i class="fas fa-graduation-cap"></i> {{ $stats['tong'] }} khóa học</span>
                @if($stats['dang_day'] > 0)
                    <span class="kqht-active-badge"><i class="fas fa-play-circle"></i> {{ $stats['dang_day'] }} đang dạy</span>
                @endif
            </div>
            <h4>Quản lý điểm toàn khóa</h4>
            <p>
                <span><i class="fas fa-users"></i> {{ $stats['tong_hv'] }} học viên</span>
                <span class="kqht-sep">·</span>
                <span><i class="fas fa-circle-check"></i> {{ $stats['san_sang'] }} sẵn sàng</span>
                <span class="kqht-sep">·</span>
                <span><i class="fas fa-trophy"></i> {{ $stats['hoan_thanh'] }} hoàn thành</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.xet-duyet-ket-qua.index') }}" class="btn btn-light text-primary fw-bold shadow-sm kqht-create-btn">
                <i class="fas fa-stamp me-1"></i> Phiếu xét duyệt
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- ① Tổng quan --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan khóa học</h2>
                    <p>Bốn chỉ số nhanh giúp bạn nắm tình trạng kết quả từng khóa.</p>
                </div>
            </div>
        </header>
        <div class="row g-3">
            <div class="col-md-3 col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-graduation-cap"></i></div><div class="aps-text"><strong>{{ $stats['tong'] }}</strong><small>Tổng khóa học</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-success"><div class="aps-icon"><i class="fas fa-play-circle"></i></div><div class="aps-text"><strong>{{ $stats['dang_day'] }}</strong><small>Đang dạy</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-info"><div class="aps-icon"><i class="fas fa-circle-check"></i></div><div class="aps-text"><strong>{{ $stats['san_sang'] }}</strong><small>Sẵn sàng / Chờ GV</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-violet"><div class="aps-icon"><i class="fas fa-trophy"></i></div><div class="aps-text"><strong>{{ $stats['hoan_thanh'] }}</strong><small>Đã hoàn thành</small></div></div></div>
        </div>
    </section>

    {{-- ② Bộ lọc + filter group pills --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-filter"></i> Tìm kiếm &amp; nhóm</h2>
                    <p>Tìm theo tên/mã khóa học hoặc lọc theo trạng thái vận hành.</p>
                </div>
            </div>
            @if($hasFilter)
                <div class="apx-section-meta">
                    <span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">
                        <i class="fas fa-filter"></i> Đang lọc
                    </span>
                </div>
            @endif
        </header>

        <div class="kqht-filter-card">
            <form method="GET" class="row g-3 align-items-end mb-3">
                <div class="col-md-7">
                    <label class="kqht-flabel">Tìm kiếm</label>
                    <div class="kqht-input-icon">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Tên khóa học hoặc mã khóa học...">
                    </div>
                </div>
                @if($filterGroup)
                    <input type="hidden" name="group" value="{{ $filterGroup }}">
                @endif
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold flex-fill">
                        <i class="fas fa-search me-1"></i> Tìm
                    </button>
                    <a href="{{ route('admin.ket-qua.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left me-1"></i> Xóa lọc
                    </a>
                </div>
            </form>

            <div class="kqht-pill-row">
                <a href="{{ route('admin.ket-qua.index', array_filter(['q' => $search])) }}"
                   class="kqht-pill {{ !$filterGroup ? 'is-active' : '' }}">
                    <i class="fas fa-folder-tree"></i>
                    <span>Tất cả</span>
                    <span class="kqht-pill-count">{{ $courses->count() }}</span>
                </a>
                @foreach($groupTitles as $key => $meta)
                    @php $count = $courses->where('trang_thai_van_hanh', $key)->count(); @endphp
                    @if($count > 0)
                        <a href="{{ route('admin.ket-qua.index', array_filter(['q' => $search, 'group' => $key])) }}"
                           class="kqht-pill {{ $filterGroup === $key ? 'is-active' : '' }}"
                           style="--pill-color: {{ $meta['color'] }};">
                            <i class="fas {{ $meta['icon'] }}"></i>
                            <span>{{ $meta['label'] }}</span>
                            <span class="kqht-pill-count">{{ $count }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- ③ Danh sách khóa học gom theo trạng thái --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div>
                    <h2><i class="fas fa-folder-tree"></i> Khóa học theo nhóm trạng thái</h2>
                    <p>Mỗi nhóm có header riêng, bấm "Xem kết quả" để mở chi tiết khóa.</p>
                </div>
            </div>
        </header>

        @if($grouped->isEmpty() || $grouped->flatten()->isEmpty())
            <div class="kqht-empty">
                <div class="kqht-empty-icon"><i class="fas fa-folder-open"></i></div>
                <h5>Không tìm thấy khóa học</h5>
                <p>Thử bỏ bộ lọc hoặc đổi từ khóa tìm kiếm để xem thêm.</p>
            </div>
        @else
            @foreach($grouped as $statusKey => $items)
                @php
                    $meta = $groupTitles[$statusKey] ?? ['label' => 'Khác', 'icon' => 'fa-circle-question', 'color' => '#64748b'];
                @endphp
                <div class="kqht-group" style="--g-color: {{ $meta['color'] }};">
                    <div class="kqht-group-head">
                        <div class="kqht-group-icon"><i class="fas {{ $meta['icon'] }}"></i></div>
                        <div class="kqht-group-info">
                            <h5>{{ $meta['label'] }}</h5>
                            <small>{{ $items->count() }} khóa học</small>
                        </div>
                        <span class="kqht-group-count">{{ $items->count() }}</span>
                    </div>

                    <div class="kqht-course-grid">
                        @foreach($items as $course)
                            @php
                                $idColor = $course->id % 6;
                                $gradients = [
                                    'linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)',
                                    'linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)',
                                    'linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)',
                                ];
                            @endphp
                            <div class="kqht-course-card">
                                <div class="kqht-course-head">
                                    <div class="kqht-course-icon" style="background: {{ $gradients[$idColor] }};">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="kqht-course-meta">
                                        <div class="kqht-course-code">
                                            <i class="fas fa-fingerprint"></i> {{ $course->ma_khoa_hoc }}
                                        </div>
                                        <div class="kqht-course-name">{{ $course->ten_khoa_hoc }}</div>
                                    </div>
                                </div>

                                <div class="kqht-course-tags">
                                    <span class="kqht-soft-tag"><i class="fas fa-scale-balanced"></i> {{ $course->phuong_thuc_danh_gia_label }}</span>
                                    <span class="kqht-soft-tag is-primary"><i class="fas fa-users"></i> {{ $course->hoc_vien_khoa_hocs_count }} HV</span>
                                </div>

                                <div class="kqht-course-actions">
                                    <a href="{{ route('admin.ket-qua.show', $course->id) }}" class="kqht-btn-primary">
                                        <i class="fas fa-chart-line"></i> Xem kết quả
                                    </a>
                                    @if($course->id)
                                        <a href="{{ route('admin.xet-duyet-ket-qua.index', ['khoa_hoc_id' => $course->id]) }}" class="kqht-btn-secondary" title="Xem phiếu xét duyệt">
                                            <i class="fas fa-stamp"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .kqht-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .kqht-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .kqht-page .apx-section-title h2 i { color: #dc2626; }
    .kqht-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .kqht-page .apx-meta-pill strong { color: #b91c1c; }

    .kqht-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .kqht-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .kqht-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .kqht-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .kqht-active-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: #dcfce7; color: #166534; font-size: 0.72rem; font-weight: 800; border-radius: 999px; }
    .apx-welcome.kqht-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.kqht-welcome p i { color: #fef3c7; margin-right: 4px; }
    .kqht-sep { opacity: 0.5; }
    .kqht-create-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    /* Filter card */
    .kqht-filter-card {
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 14px; padding: 18px 20px;
    }
    .kqht-flabel {
        display: block; font-weight: 800; color: #7f1d1d;
        font-size: 0.78rem; text-transform: uppercase;
        letter-spacing: 0.4px; margin-bottom: 6px;
    }
    .kqht-input-icon { position: relative; }
    .kqht-input-icon i {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%); color: #94a3b8;
        font-size: 0.85rem; pointer-events: none;
    }
    .kqht-input-icon input { padding-left: 34px; }

    /* Filter pills */
    .kqht-pill-row {
        display: flex; flex-wrap: wrap; gap: 6px;
        padding-top: 12px;
        border-top: 1px dashed #fecaca;
    }
    .kqht-pill {
        --pill-color: #1d4ed8;
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        color: #475569;
        font-size: 0.8rem; font-weight: 700;
        border-radius: 999px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .kqht-pill i { color: var(--pill-color); font-size: 0.74rem; }
    .kqht-pill:hover {
        background: #fafafa;
        border-color: var(--pill-color);
        color: var(--pill-color);
    }
    .kqht-pill.is-active {
        background: var(--pill-color);
        border-color: var(--pill-color);
        color: #fff;
        box-shadow: 0 4px 12px color-mix(in srgb, var(--pill-color) 25%, transparent);
    }
    .kqht-pill.is-active i { color: #fff; }
    .kqht-pill-count {
        display: inline-grid; place-items: center;
        min-width: 22px; padding: 1px 7px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.68rem; font-weight: 800;
        border-radius: 999px;
    }
    .kqht-pill.is-active .kqht-pill-count {
        background: rgba(255,255,255,0.25);
        color: #fff;
    }

    /* Group header */
    .kqht-group {
        margin-bottom: 18px;
        --g-color: #1d4ed8;
    }
    .kqht-group-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: color-mix(in srgb, var(--g-color) 8%, white);
        border: 1px solid color-mix(in srgb, var(--g-color) 30%, white);
        border-left: 4px solid var(--g-color);
        border-radius: 10px;
        margin-bottom: 12px;
    }
    .kqht-group-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: var(--g-color);
        color: #fff;
        display: grid; place-items: center;
        font-size: 1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px color-mix(in srgb, var(--g-color) 30%, transparent);
    }
    .kqht-group-info { flex: 1; min-width: 0; }
    .kqht-group-info h5 {
        margin: 0; font-size: 0.96rem; font-weight: 800;
        color: #0f172a;
    }
    .kqht-group-info small {
        font-size: 0.74rem; color: #64748b; font-weight: 600;
    }
    .kqht-group-count {
        padding: 4px 14px;
        background: var(--g-color);
        color: #fff;
        font-size: 0.86rem; font-weight: 900;
        border-radius: 999px;
        box-shadow: 0 2px 8px color-mix(in srgb, var(--g-color) 30%, transparent);
    }

    /* Course grid */
    .kqht-course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 12px;
    }
    .kqht-course-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .kqht-course-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(15,23,42,0.08);
        border-color: #fecaca;
    }
    .kqht-course-head { display: flex; gap: 10px; align-items: flex-start; }
    .kqht-course-icon {
        flex-shrink: 0;
        width: 42px; height: 42px;
        border-radius: 10px;
        color: #fff;
        display: grid; place-items: center;
        font-size: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .kqht-course-meta { flex: 1; min-width: 0; }
    .kqht-course-code {
        font-size: 0.7rem; color: #64748b;
        font-weight: 700; margin-bottom: 3px;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .kqht-course-code i { color: #1d4ed8; margin-right: 4px; font-size: 0.62rem; }
    .kqht-course-name {
        font-size: 0.92rem; font-weight: 800;
        color: #0f172a; line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .kqht-course-tags { display: flex; flex-wrap: wrap; gap: 5px; }
    .kqht-soft-tag {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: #f1f5f9; color: #475569;
        font-size: 0.7rem; font-weight: 700;
        border-radius: 999px;
    }
    .kqht-soft-tag.is-primary { background: #eff6ff; color: #1d4ed8; }
    .kqht-soft-tag i { font-size: 0.62rem; opacity: 0.85; }

    .kqht-course-actions { display: flex; gap: 6px; margin-top: auto; }
    .kqht-btn-primary {
        flex: 1;
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        padding: 8px 14px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.82rem; font-weight: 800;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(220,38,38,0.22);
        transition: all 0.18s ease;
    }
    .kqht-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220,38,38,0.32);
        color: #fff;
    }
    .kqht-btn-secondary {
        display: inline-grid; place-items: center;
        width: 36px; height: 36px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.86rem;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .kqht-btn-secondary:hover {
        background: #fef2f2; border-color: #fecaca; color: #dc2626;
    }

    .kqht-empty { padding: 60px 30px; text-align: center; background: #fff; border: 1px dashed #fecaca; border-radius: 14px; }
    .kqht-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .kqht-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .kqht-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }
</style>
@endsection
