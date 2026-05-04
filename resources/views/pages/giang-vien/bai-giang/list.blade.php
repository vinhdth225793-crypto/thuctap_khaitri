@extends('layouts.app')

@section('title', 'Quản lý Bài giảng')

@section('content')
@php
    $userId = auth()->user()->id;
    $bgQuery = \App\Models\BaiGiang::where('nguoi_tao_id', $userId);
    $stats = [
        'tong'       => (clone $bgQuery)->count(),
        'da_duyet'   => (clone $bgQuery)->where('trang_thai_duyet', 'da_duyet')->count(),
        'cho_duyet'  => (clone $bgQuery)->where('trang_thai_duyet', 'cho_duyet')->count(),
        'cong_bo'    => (clone $bgQuery)->where('trang_thai_cong_bo', 'da_cong_bo')->count(),
        'nhap'       => (clone $bgQuery)->whereIn('trang_thai_duyet', ['nhap', 'can_chinh_sua'])->count(),
    ];
@endphp

<div class="container-fluid admin-page-x bg-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome bg-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-chalkboard"></i></div>
        <div class="apx-welcome-text">
            <div class="bg-tag-row">
                <span class="bg-loai-badge">
                    <i class="fas fa-book-open"></i> QUẢN LÝ BÀI GIẢNG
                </span>
                <span class="bg-status-badge">
                    <i class="fas fa-layer-group"></i>
                    {{ $stats['tong'] }} bài giảng
                </span>
                @if($stats['cho_duyet'] > 0)
                    <span class="bg-pending-badge">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $stats['cho_duyet'] }} chờ duyệt
                    </span>
                @endif
            </div>
            <h4>Bài giảng của tôi</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $stats['da_duyet'] }} đã duyệt</span>
                <span class="bg-sep">·</span>
                <span><i class="fas fa-eye"></i> {{ $stats['cong_bo'] }} đang công bố</span>
                <span class="bg-sep">·</span>
                <span><i class="fas fa-pen"></i> {{ $stats['nhap'] }} nháp & cần sửa</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('giang-vien.bai-giang.create') }}" class="btn btn-light text-primary fw-bold shadow-sm bg-create-btn">
                <i class="fas fa-plus me-1"></i> Tạo bài giảng mới
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- ========== ① Tổng quan ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan bài giảng</h2>
                    <p>Bốn chỉ số nhanh giúp bạn nắm được tình trạng các bài giảng.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $stats['tong'] }}</strong> tổng số</span>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-book-open"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['tong'] }}</strong>
                        <small>Tổng bài giảng</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['da_duyet'] }}</strong>
                        <small>Đã được duyệt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['cho_duyet'] }}</strong>
                        <small>Chờ admin duyệt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-eye"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['cong_bo'] }}</strong>
                        <small>Đang công bố</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Danh sách bài giảng ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách bài giảng</h2>
                    <p>Quản lý, chỉnh sửa, gửi duyệt và công bố bài giảng cho học viên.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $baiGiangs->total() }}</strong> kết quả</span>
            </div>
        </header>

        <div class="bg-table-wrap">
            @if($baiGiangs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 bg-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Bài giảng</th>
                                <th>Khóa học / Module</th>
                                <th class="text-center">Loại</th>
                                <th class="text-center">Trạng thái duyệt</th>
                                <th class="text-center">Công bố</th>
                                <th class="pe-4 text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($baiGiangs as $bg)
                                @php
                                    $duyetColor = match($bg->trang_thai_duyet) {
                                        'da_duyet'      => 'success',
                                        'cho_duyet'     => 'warning',
                                        'can_chinh_sua' => 'info',
                                        'tu_choi'       => 'danger',
                                        default         => 'secondary',
                                    };
                                    $duyetLabel = match($bg->trang_thai_duyet) {
                                        'da_duyet'      => 'Đã duyệt',
                                        'cho_duyet'     => 'Chờ duyệt',
                                        'can_chinh_sua' => 'Cần sửa',
                                        'tu_choi'       => 'Từ chối',
                                        default         => 'Nháp',
                                    };
                                    $duyetIcon = match($bg->trang_thai_duyet) {
                                        'da_duyet'      => 'fa-check-circle',
                                        'cho_duyet'     => 'fa-hourglass-half',
                                        'can_chinh_sua' => 'fa-pen-to-square',
                                        'tu_choi'       => 'fa-times-circle',
                                        default         => 'fa-pen',
                                    };
                                    $loaiIcon = match($bg->loai_bai_giang) {
                                        'live'    => 'fa-video',
                                        'tai_lieu', 'document' => 'fa-file-lines',
                                        'video'   => 'fa-play-circle',
                                        default   => 'fa-book-open',
                                    };
                                    $idColor = $bg->id % 6;
                                    $gradients = [
                                        'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                                        'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                                        'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                                        'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                                        'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                                        'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                                    ];
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="bg-thumb" style="background: {{ $gradients[$idColor] }};">
                                                <i class="fas {{ $loaiIcon }}"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="bg-name">{{ $bg->tieu_de }}</div>
                                                <div class="bg-meta">
                                                    @if($bg->lichHoc)
                                                        <span><i class="far fa-calendar-alt"></i> Buổi {{ $bg->lichHoc->buoi_so }}</span>
                                                        <span class="bg-meta-sep">·</span>
                                                        <span>{{ $bg->lichHoc->ngay_hoc?->format('d/m/Y') }}</span>
                                                    @else
                                                        <span><i class="fas fa-layer-group"></i> Chung cho module</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="bg-course-name">{{ $bg->moduleHoc->ten_module ?? 'N/A' }}</div>
                                        <div class="bg-course-sub"><i class="fas fa-graduation-cap"></i> {{ $bg->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="bg-loai-pill">
                                            <i class="fas {{ $loaiIcon }}"></i> {{ $bg->loai_bai_giang }}
                                        </span>
                                        @if($bg->isLive() && $bg->phongHocLive)
                                            <div class="bg-platform-tag">
                                                <i class="fas fa-broadcast-tower"></i> {{ $bg->phongHocLive->platform_label }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="bg-status-pill is-{{ $duyetColor }}">
                                            <i class="fas {{ $duyetIcon }}"></i> {{ $duyetLabel }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($bg->trang_thai_cong_bo === 'da_cong_bo')
                                            <span class="bg-status-pill is-success">
                                                <i class="fas fa-eye"></i> Đã công bố
                                            </span>
                                        @else
                                            <span class="bg-status-pill is-secondary">
                                                <i class="fas fa-eye-slash"></i> Đang ẩn
                                            </span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="bg-actions">
                                            <a href="{{ route('giang-vien.bai-giang.edit', $bg->id) }}" class="bg-action-btn" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($bg->isLive() && $bg->phongHocLive)
                                                <a href="{{ route('giang-vien.live-room.show', $bg->id) }}" class="bg-action-btn dark" title="Phòng học live">
                                                    <i class="fas fa-video"></i>
                                                </a>
                                            @endif
                                            @if(in_array($bg->trang_thai_duyet, ['nhap', 'can_chinh_sua']))
                                                <form action="{{ route('giang-vien.bai-giang.gui-duyet', $bg->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="bg-action-btn success" title="Gửi duyệt"
                                                            onclick="return confirm('Gửi bài giảng này cho admin duyệt?')">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" class="bg-action-btn danger"
                                                    onclick="if(confirm('Xác nhận xóa bài giảng \'{{ addslashes($bg->tieu_de) }}\'?')) document.getElementById('delete-form-{{ $bg->id }}').submit();"
                                                    title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $bg->id }}" action="{{ route('giang-vien.bai-giang.destroy', $bg->id) }}" method="POST" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($baiGiangs->hasPages())
                    <div class="bg-pagination">
                        {{ $baiGiangs->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="bg-empty">
                    <div class="bg-empty-icon"><i class="fas fa-chalkboard"></i></div>
                    <h5>Bạn chưa có bài giảng nào</h5>
                    <p>Hãy bắt đầu tạo bài giảng đầu tiên để chia sẻ nội dung học tập với học viên.</p>
                    <a href="{{ route('giang-vien.bai-giang.create') }}" class="btn btn-primary fw-bold">
                        <i class="fas fa-plus me-1"></i> Tạo bài giảng đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Welcome banner xanh dương ===== */
    .bg-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }

    .bg-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .bg-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
        border-radius: 999px;
    }

    .bg-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .bg-status-badge i { font-size: 0.65rem; opacity: 0.85; }

    .bg-pending-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        animation: bgPendingPulse 1.6s ease-out infinite;
    }
    @keyframes bgPendingPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }

    .apx-welcome.bg-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.bg-welcome p i { color: #fef3c7; margin-right: 4px; }
    .bg-sep { opacity: 0.5; }

    .bg-create-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* ===== Bảng bài giảng ===== */
    .bg-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .bg-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .bg-table thead th {
        padding: 12px 10px;
        font-weight: 800;
        border-bottom: 1px solid #bfdbfe;
    }
    .bg-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .bg-table tbody tr:last-child td { border-bottom: 0; }
    .bg-table tbody tr:hover { background: #f8fafc; }

    /* Thumb icon */
    .bg-thumb {
        flex-shrink: 0;
        width: 44px; height: 44px;
        border-radius: 12px;
        color: #fff;
        display: grid; place-items: center;
        font-size: 1.05rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .bg-name {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        margin-bottom: 4px;
    }
    .bg-meta {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        display: flex; flex-wrap: wrap; gap: 5px;
    }
    .bg-meta i { color: #1d4ed8; margin-right: 3px; }
    .bg-meta-sep { opacity: 0.5; }

    /* Cột khóa học */
    .bg-course-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .bg-course-sub {
        margin-top: 3px;
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
    }
    .bg-course-sub i { color: #1d4ed8; margin-right: 4px; }

    /* Loại badge */
    .bg-loai-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .bg-platform-tag {
        margin-top: 4px;
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 600;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .bg-platform-tag i { color: #16a34a; font-size: 0.65rem; }

    /* Status pill */
    .bg-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px;
        font-size: 0.7rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .bg-status-pill i { font-size: 0.62rem; }

    .bg-status-pill.is-success   { background: #dcfce7; color: #16a34a; }
    .bg-status-pill.is-warning   { background: #fef3c7; color: #c2410c; }
    .bg-status-pill.is-info      { background: #e0f2fe; color: #0369a1; }
    .bg-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .bg-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    /* Action buttons */
    .bg-actions {
        display: inline-flex;
        gap: 4px;
        flex-wrap: nowrap;
    }

    .bg-action-btn {
        width: 32px; height: 32px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #1d4ed8;
        display: grid; place-items: center;
        cursor: pointer;
        font-size: 0.78rem;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .bg-action-btn:hover {
        border-color: #1d4ed8;
        background: #1d4ed8;
        color: #fff;
        transform: translateY(-1px);
    }
    .bg-action-btn.success { color: #16a34a; }
    .bg-action-btn.success:hover { background: #16a34a; border-color: #16a34a; color: #fff; }
    .bg-action-btn.danger { color: #dc2626; }
    .bg-action-btn.danger:hover { background: #dc2626; border-color: #dc2626; color: #fff; }
    .bg-action-btn.dark { color: #1e293b; }
    .bg-action-btn.dark:hover { background: #1e293b; border-color: #1e293b; color: #fff; }

    /* Pagination */
    .bg-pagination {
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: center;
    }
    .bg-pagination nav { margin: 0; }

    /* Empty state */
    .bg-empty {
        padding: 60px 30px;
        text-align: center;
    }

    .bg-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #1d4ed8;
        font-size: 2rem;
    }

    .bg-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .bg-empty p {
        font-size: 0.9rem; color: #94a3b8;
        max-width: 440px; margin: 0 auto 20px;
        line-height: 1.55;
    }

    .min-w-0 { min-width: 0; }

    /* Responsive */
    @media (max-width: 991.98px) {
        .bg-table { font-size: 0.85rem; }
        .bg-table thead th, .bg-table tbody td { padding: 10px 8px; }
        .bg-thumb { width: 38px; height: 38px; font-size: 0.92rem; }
    }
</style>
@endsection
