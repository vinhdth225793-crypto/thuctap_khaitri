@extends('layouts.app')

@section('title', 'Khóa học của tôi')

@section('content')
@php
    $hasFilter = false;
@endphp

<div class="container-fluid admin-page-x khv-page">
    <div class="apx-welcome khv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="apx-welcome-text">
            <div class="khv-tag-row">
                <span class="khv-loai-badge"><i class="fas fa-book-bookmark"></i> KHÓA HỌC CỦA TÔI</span>
                <span class="khv-status-badge"><i class="fas fa-layer-group"></i> {{ $stats['tong'] }} khóa</span>
                @if($stats['dang_hoc'] > 0)
                    <span class="khv-active-badge"><i class="fas fa-play-circle"></i> {{ $stats['dang_hoc'] }} đang học</span>
                @endif
            </div>
            <h4>Lộ trình học tập</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $stats['hoan_thanh'] }} hoàn thành</span>
                <span class="khv-sep">·</span>
                <span><i class="fas fa-pause-circle"></i> {{ $stats['ngung_hoc'] }} ngừng học</span>
                <span class="khv-sep">·</span>
                <span><i class="fas fa-list-check"></i> Danh sách các khóa học bạn đang tham gia</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('hoc-vien.dashboard') }}" class="apx-view-toggle"><i class="fas fa-arrow-left"></i> <span>Dashboard</span></a>
            <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn btn-light text-primary fw-bold shadow-sm khv-create-btn">
                <i class="fas fa-user-plus me-1"></i> Xin vào lớp
            </a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">1</span><div><h2><i class="fas fa-chart-pie"></i> Tổng quan lộ trình</h2><p>Bốn chỉ số nhanh về các khóa học bạn đã ghi danh.</p></div></div>
            <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ $stats['tong'] }}</strong> khóa</span></div>
        </header>
        <div class="row g-3">
            <div class="col-md-3 col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-book-open"></i></div><div class="aps-text"><strong>{{ $stats['tong'] }}</strong><small>Tổng khóa học</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-success"><div class="aps-icon"><i class="fas fa-play-circle"></i></div><div class="aps-text"><strong>{{ $stats['dang_hoc'] }}</strong><small>Đang học</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-info"><div class="aps-icon"><i class="fas fa-circle-check"></i></div><div class="aps-text"><strong>{{ $stats['hoan_thanh'] }}</strong><small>Đã hoàn thành</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-warning"><div class="aps-icon"><i class="fas fa-pause-circle"></i></div><div class="aps-text"><strong>{{ $stats['ngung_hoc'] }}</strong><small>Ngừng học</small></div></div></div>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">2</span><div><h2><i class="fas fa-list"></i> Danh sách khóa học</h2><p>Bấm "Vào học ngay" để mở chi tiết khóa và tiếp tục lộ trình.</p></div></div>
            <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ $khoaHocs->total() }}</strong> khóa</span></div>
        </header>

        @if($khoaHocs->isEmpty())
            <div class="khv-empty">
                <div class="khv-empty-icon"><i class="fas fa-book-reader"></i></div>
                <h5>Bạn chưa tham gia khóa học nào</h5>
                <p>Hãy bấm nút bên dưới để xem các khóa học có thể tham gia hoặc liên hệ admin để được ghi danh.</p>
                <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn btn-primary fw-bold">
                    <i class="fas fa-search me-1"></i> Xem khóa học có thể tham gia
                </a>
            </div>
        @else
            <div class="row g-4">
                @foreach($khoaHocs as $kh)
                    @php
                        $khoa = $kh->khoaHoc;
                        $idColor = $khoa->id % 6;
                        $gradients = [
                            'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                            'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                            'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                            'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                            'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                            'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                        ];
                        $tienDo = (int) ($khoa->tien_do_hoc_tap ?? 0);
                        $progressColor = $tienDo >= 100 ? 'success' : ($tienDo >= 50 ? 'primary' : 'warning');
                    @endphp
                    <div class="col-xl-4 col-lg-6">
                        <div class="khv-course-card">
                            <div class="khv-cover">
                                @if($khoa->hinh_anh)
                                    <img src="{{ asset($khoa->hinh_anh) }}" alt="{{ $khoa->ten_khoa_hoc }}">
                                @else
                                    <div class="khv-cover-fallback" style="background: {{ $gradients[$idColor] }};">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                @endif
                                <span class="khv-status-pill {{ $kh->trang_thai_badge ?? '' }}">{{ $kh->trang_thai_label }}</span>
                            </div>
                            <div class="khv-card-body">
                                <div class="khv-tag-line">
                                    <span class="khv-soft-tag"><i class="fas fa-tag"></i> {{ $khoa->nhomNganh->ten_nhom_nganh ?? 'Chưa phân nhóm' }}</span>
                                    <span class="khv-soft-tag is-info"><i class="fas fa-signal"></i> {{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$khoa->cap_do] ?? 'N/A' }}</span>
                                </div>
                                <h5 class="khv-title">{{ $khoa->ten_khoa_hoc }}</h5>
                                <div class="khv-code"><i class="fas fa-fingerprint"></i> {{ $khoa->ma_khoa_hoc }}</div>

                                <div class="khv-info-list">
                                    <div class="khv-info-row">
                                        <span><i class="far fa-calendar-alt"></i> Khai giảng</span>
                                        <strong>{{ $khoa->ngay_khai_giang?->format('d/m/Y') ?: '—' }}</strong>
                                    </div>
                                    <div class="khv-info-row">
                                        <span><i class="fas fa-user-check"></i> Ghi danh</span>
                                        <strong>{{ $kh->ngay_tham_gia?->format('d/m/Y') ?: '—' }}</strong>
                                    </div>
                                </div>

                                <div class="khv-progress-block">
                                    <div class="khv-progress-head">
                                        <span>Tiến độ học tập</span>
                                        <strong>{{ $tienDo }}%</strong>
                                    </div>
                                    <div class="khv-progress">
                                        <div class="khv-progress-bar bg-{{ $progressColor }}" style="width: {{ $tienDo }}%"></div>
                                    </div>
                                </div>

                                <div class="khv-actions">
                                    <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $khoa->id) }}" class="khv-btn-primary">
                                        <i class="fas fa-rocket"></i> Vào học ngay
                                    </a>
                                    <div class="khv-quick-links">
                                        <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $khoa->id) }}#lich-hoc" class="khv-quick-btn">
                                            <i class="far fa-calendar"></i> Lịch học
                                        </a>
                                        <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $khoa->id) }}#bai-kiem-tra" class="khv-quick-btn">
                                            <i class="fas fa-clipboard-check"></i> Kiểm tra
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($khoaHocs->hasPages())
                <div class="khv-pagination">{{ $khoaHocs->links('pagination::bootstrap-5') }}</div>
            @endif
        @endif
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .khv-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .khv-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .khv-page .apx-section-title h2 i { color: #dc2626; }
    .khv-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .khv-page .apx-meta-pill strong { color: #b91c1c; }

    .khv-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .khv-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .khv-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .khv-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .khv-active-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: #dcfce7; color: #166534; font-size: 0.72rem; font-weight: 800; border-radius: 999px; }
    .apx-welcome.khv-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.khv-welcome p i { color: #fef3c7; margin-right: 4px; }
    .khv-sep { opacity: 0.5; }
    .khv-create-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    .khv-course-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.25s ease;
    }
    .khv-course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
        border-color: #fecaca;
    }
    .khv-cover {
        position: relative;
        height: 160px;
        overflow: hidden;
        background: #f1f5f9;
    }
    .khv-cover img { width: 100%; height: 100%; object-fit: cover; }
    .khv-cover-fallback {
        width: 100%; height: 100%;
        display: grid; place-items: center;
        color: #fff; font-size: 3rem;
    }
    .khv-status-pill {
        position: absolute; top: 12px; right: 12px;
        padding: 5px 14px;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .khv-card-body { padding: 16px 18px; flex: 1; display: flex; flex-direction: column; }
    .khv-tag-line { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }
    .khv-soft-tag {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: #fef2f2; color: #dc2626;
        font-size: 0.7rem; font-weight: 800;
        border-radius: 999px;
    }
    .khv-soft-tag.is-info { background: #eff6ff; color: #1d4ed8; }
    .khv-soft-tag i { font-size: 0.62rem; }

    .khv-title {
        font-size: 1rem; font-weight: 800; color: #0f172a;
        line-height: 1.4; margin: 0 0 6px;
        display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.8em;
    }
    .khv-code {
        font-size: 0.74rem; color: #64748b; font-weight: 600;
        margin-bottom: 12px;
    }
    .khv-code i { color: #dc2626; margin-right: 4px; font-size: 0.66rem; }

    .khv-info-list {
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 14px;
    }
    .khv-info-row {
        display: flex; justify-content: space-between; align-items: center;
        gap: 8px; padding: 4px 0;
        font-size: 0.78rem;
    }
    .khv-info-row span { color: #64748b; font-weight: 600; }
    .khv-info-row span i { color: #1d4ed8; margin-right: 5px; font-size: 0.7rem; }
    .khv-info-row strong { color: #0f172a; font-weight: 800; }

    .khv-progress-block { margin-bottom: 14px; }
    .khv-progress-head {
        display: flex; justify-content: space-between;
        font-size: 0.78rem; margin-bottom: 5px;
    }
    .khv-progress-head span { color: #64748b; font-weight: 600; }
    .khv-progress-head strong { color: #0f172a; font-weight: 800; }
    .khv-progress {
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .khv-progress-bar { height: 100%; border-radius: 999px; transition: width 0.6s ease; }
    .khv-progress-bar.bg-success { background: linear-gradient(90deg, #16a34a 0%, #15803d 100%); }
    .khv-progress-bar.bg-primary { background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 100%); }
    .khv-progress-bar.bg-warning { background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); }

    .khv-actions { display: flex; flex-direction: column; gap: 8px; margin-top: auto; }
    .khv-btn-primary {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px 16px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff; font-size: 0.86rem; font-weight: 800;
        border-radius: 10px; text-decoration: none;
        box-shadow: 0 4px 12px rgba(220,38,38,0.22);
        transition: all 0.2s ease;
    }
    .khv-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220,38,38,0.32);
        color: #fff;
    }
    .khv-quick-links { display: flex; gap: 6px; }
    .khv-quick-btn {
        flex: 1;
        display: inline-flex; align-items: center; justify-content: center; gap: 5px;
        padding: 7px 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.74rem; font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .khv-quick-btn:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .khv-quick-btn i { font-size: 0.68rem; }

    .khv-pagination { padding: 18px; display: flex; justify-content: center; }
    .khv-pagination nav { margin: 0; }

    .khv-empty {
        padding: 60px 30px; text-align: center;
        background: #fff;
        border: 1px dashed #fecaca;
        border-radius: 14px;
    }
    .khv-empty-icon {
        width: 92px; height: 92px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #dc2626; font-size: 2.1rem;
    }
    .khv-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .khv-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto 20px; line-height: 1.55; }
</style>
@endsection
