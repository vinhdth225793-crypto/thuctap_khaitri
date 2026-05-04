@extends('layouts.app', ['title' => 'Danh sách bài kiểm tra'])

@section('content')
@php
    $scopeMap = [
        'module'   => ['label' => 'Theo module',   'icon' => 'fa-layer-group'],
        'buoi_hoc' => ['label' => 'Theo buổi học', 'icon' => 'fa-calendar-day'],
        'cuoi_khoa'=> ['label' => 'Cuối khóa',     'icon' => 'fa-flag-checkered'],
    ];

    $approvalMap = [
        'nhap'      => ['label' => 'Nháp',     'class' => 'secondary', 'icon' => 'fa-pen'],
        'cho_duyet' => ['label' => 'Chờ duyệt', 'class' => 'warning',   'icon' => 'fa-hourglass-half'],
        'da_duyet'  => ['label' => 'Đã duyệt',  'class' => 'success',   'icon' => 'fa-check-circle'],
        'tu_choi'   => ['label' => 'Từ chối',   'class' => 'danger',    'icon' => 'fa-times-circle'],
    ];

    $publishMap = [
        'nhap'      => ['label' => 'Chưa phát hành', 'class' => 'secondary', 'icon' => 'fa-eye-slash'],
        'phat_hanh' => ['label' => 'Đang phát hành', 'class' => 'success',   'icon' => 'fa-broadcast-tower'],
        'dong'      => ['label' => 'Đã đóng',        'class' => 'info',      'icon' => 'fa-lock'],
    ];

    $hasFilter = $filters['search'] !== ''
        || $filters['pham_vi']
        || $filters['trang_thai_duyet']
        || $filters['trang_thai_phat_hanh'];
@endphp

<div class="container-fluid admin-page-x bkt-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome bkt-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-file-signature"></i></div>
        <div class="apx-welcome-text">
            <div class="bkt-tag-row">
                <span class="bkt-loai-badge">
                    <i class="fas fa-clipboard-check"></i> QUẢN LÝ BÀI KIỂM TRA
                </span>
                <span class="bkt-status-badge">
                    <i class="fas fa-layer-group"></i>
                    {{ $stats['tong'] }} đề
                </span>
                @if($stats['cho_duyet'] > 0)
                    <span class="bkt-pending-badge">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $stats['cho_duyet'] }} chờ duyệt
                    </span>
                @endif
            </div>
            <h4>Bài kiểm tra của tôi</h4>
            <p>
                <span><i class="fas fa-sliders-h"></i> {{ $stats['nhap'] }} cần cấu hình</span>
                <span class="bkt-sep">·</span>
                <span><i class="fas fa-broadcast-tower"></i> {{ $stats['phat_hanh'] }} đang phát hành</span>
                <span class="bkt-sep">·</span>
                <span><i class="fas fa-hourglass-half"></i> {{ $stats['cho_duyet'] }} chờ duyệt</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-light text-primary fw-bold shadow-sm bkt-create-btn">
                <i class="fas fa-marker me-1"></i> Chấm tự luận
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- Hint card --}}
    <div class="bkt-hint">
        <div class="bkt-hint-icon"><i class="fas fa-lightbulb"></i></div>
        <div>
            <strong>Đường vào tạo đề mới:</strong>
            Tạo đề mới từ <em>Lộ trình giảng dạy</em> → <em>Vào dạy</em> → <em>Tạo bài kiểm tra</em>.
            Trang này dùng để quản lý, cấu hình lại và phát hành các đề đã có.
        </div>
        <a href="{{ route('giang-vien.khoa-hoc') }}" class="bkt-hint-cta">
            <i class="fas fa-arrow-right"></i> Đến lộ trình
        </a>
    </div>

    {{-- ========== ① Tổng quan ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan đề kiểm tra</h2>
                    <p>Bốn chỉ số nhanh để bạn nắm tình trạng các đề kiểm tra đang quản lý.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $stats['tong'] }}</strong> tổng đề</span>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-file-signature"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['tong'] }}</strong>
                        <small>Tổng đề kiểm tra</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-sliders-h"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['nhap'] }}</strong>
                        <small>Cần cấu hình</small>
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
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-broadcast-tower"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['phat_hanh'] }}</strong>
                        <small>Đang phát hành</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Bộ lọc ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-filter"></i> Bộ lọc danh sách</h2>
                    <p>Tìm theo tên đề, mã khóa học, module hoặc lọc theo trạng thái.</p>
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

        <div class="bkt-filter-card">
            <form method="GET" action="{{ route('giang-vien.bai-kiem-tra.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="bkt-flabel">Tìm kiếm</label>
                    <div class="bkt-input-icon">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="{{ $filters['search'] }}"
                               class="form-control" placeholder="Tên đề, mã khóa học, tên module...">
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="bkt-flabel">Phạm vi</label>
                    <select name="pham_vi" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($scopeMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['pham_vi'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="bkt-flabel">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($approvalMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['trang_thai_duyet'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="bkt-flabel">Phát hành</label>
                    <select name="trang_thai_phat_hanh" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($publishMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['trang_thai_phat_hanh'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fas fa-search me-1"></i> Lọc danh sách
                    </button>
                    <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left me-1"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </section>

    {{-- ========== ③ Danh sách đề ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách đề kiểm tra</h2>
                    <p>Cấu hình câu hỏi, phát hành cho học viên hoặc xóa đề chưa có lượt làm.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $baiKiemTras->total() }}</strong> kết quả</span>
            </div>
        </header>

        <div class="bkt-table-wrap">
            @if($baiKiemTras->isEmpty())
                <div class="bkt-empty">
                    <div class="bkt-empty-icon"><i class="fas fa-folder-open"></i></div>
                    <h5>Chưa có bài kiểm tra phù hợp</h5>
                    <p>Hãy tạo đề mới từ lớp học hoặc bỏ bớt điều kiện lọc để xem thêm đề.</p>
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-primary fw-bold">
                        <i class="fas fa-arrow-right me-1"></i> Đến lộ trình giảng dạy
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 bkt-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Đề kiểm tra</th>
                                <th>Phạm vi</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Dữ liệu đề</th>
                                <th>Lịch mở / đóng</th>
                                <th class="text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($baiKiemTras as $bkt)
                                @php
                                    $scope    = $scopeMap[$bkt->pham_vi]            ?? ['label' => 'Khác', 'icon' => 'fa-circle-question'];
                                    $approval = $approvalMap[$bkt->trang_thai_duyet] ?? ['label' => 'Không rõ', 'class' => 'secondary', 'icon' => 'fa-circle-question'];
                                    $publish  = $publishMap[$bkt->trang_thai_phat_hanh] ?? ['label' => 'Không rõ', 'class' => 'secondary', 'icon' => 'fa-circle-question'];

                                    $idColor = $bkt->id % 6;
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
                                            <div class="bkt-thumb" style="background: {{ $gradients[$idColor] }};">
                                                <i class="fas fa-file-signature"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="bkt-name">{{ $bkt->tieu_de }}</div>
                                                <div class="bkt-meta">
                                                    <span><i class="fas fa-graduation-cap"></i> {{ $bkt->khoaHoc->ma_khoa_hoc ?? 'KH' }}</span>
                                                    <span class="bkt-meta-sep">·</span>
                                                    <span>{{ $bkt->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }}</span>
                                                </div>
                                                <div class="bkt-meta-sub">
                                                    @if($bkt->moduleHoc)
                                                        <i class="fas fa-cube"></i> {{ $bkt->moduleHoc->ma_module }} · {{ $bkt->moduleHoc->ten_module }}
                                                    @elseif($bkt->lichHoc)
                                                        <i class="far fa-calendar-alt"></i> Buổi {{ $bkt->lichHoc->buoi_so }} · {{ optional($bkt->lichHoc->ngay_hoc)->format('d/m/Y') }}
                                                    @else
                                                        <i class="fas fa-globe"></i> Đề dùng chung toàn khóa
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="bkt-scope-pill">
                                            <i class="fas {{ $scope['icon'] }}"></i> {{ $scope['label'] }}
                                        </span>
                                        <div class="bkt-attempts">
                                            <i class="fas fa-redo"></i> Số lần được làm: <strong>{{ $bkt->so_lan_duoc_lam }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="bkt-status-pill is-{{ $approval['class'] }}">
                                            <i class="fas {{ $approval['icon'] }}"></i> {{ $approval['label'] }}
                                        </span>
                                        <div class="mt-2">
                                            <span class="bkt-status-pill is-{{ $publish['class'] }}">
                                                <i class="fas {{ $publish['icon'] }}"></i> {{ $publish['label'] }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="bkt-stat-line">
                                            <i class="fas fa-list-ol text-primary"></i>
                                            <strong>{{ $bkt->chi_tiet_cau_hois_count }}</strong> câu hỏi
                                        </div>
                                        <div class="bkt-stat-line">
                                            <i class="fas fa-star text-warning"></i>
                                            <strong>{{ number_format((float) $bkt->tong_diem, 2) }}</strong> điểm
                                        </div>
                                        <div class="bkt-stat-line muted">
                                            <i class="fas fa-users"></i>
                                            {{ $bkt->bai_lams_count }} lượt làm
                                        </div>
                                    </td>
                                    <td>
                                        <div class="bkt-time-line">
                                            <span class="bkt-time-label"><i class="fas fa-door-open text-success"></i> Mở:</span>
                                            <strong>{{ optional($bkt->ngay_mo)->format('d/m/Y H:i') ?? '—' }}</strong>
                                        </div>
                                        <div class="bkt-time-line">
                                            <span class="bkt-time-label"><i class="fas fa-door-closed text-danger"></i> Đóng:</span>
                                            <strong>{{ optional($bkt->ngay_dong)->format('d/m/Y H:i') ?? '—' }}</strong>
                                        </div>
                                        <div class="bkt-time-line muted">
                                            <i class="far fa-clock"></i> Cập nhật {{ optional($bkt->updated_at)->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="bkt-actions">
                                            <a href="{{ route('giang-vien.bai-kiem-tra.edit', $bkt->id) }}"
                                               class="bkt-action-btn" title="Cấu hình đề">
                                                <i class="fas fa-sliders-h"></i>
                                            </a>

                                            @if($bkt->trang_thai_duyet === 'da_duyet' && $bkt->trang_thai_phat_hanh !== 'phat_hanh')
                                                <form action="{{ route('giang-vien.bai-kiem-tra.publish', $bkt->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Phát hành đề kiểm tra này cho học viên làm bài?')">
                                                    @csrf
                                                    <button type="submit" class="bkt-action-btn success" title="Phát hành">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($bkt->has_essay_questions && $bkt->bai_lams_count > 0)
                                                <a href="{{ route('giang-vien.cham-diem.index') }}"
                                                   class="bkt-action-btn dark" title="Chấm tự luận">
                                                    <i class="fas fa-marker"></i>
                                                </a>
                                            @endif

                                            @if($bkt->bai_lams_count === 0 && in_array($bkt->trang_thai_duyet, ['nhap', 'cho_duyet', 'tu_choi']))
                                                <form action="{{ route('giang-vien.bai-kiem-tra.destroy', $bkt->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Xác nhận xóa đề này? Thao tác không thể hoàn tác.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bkt-action-btn danger" title="Xóa đề">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($baiKiemTras->hasPages())
                    <div class="bkt-pagination">
                        {{ $baiKiemTras->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ (chỉ trang này) ===== */
    .bkt-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .bkt-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .bkt-page .apx-section-title h2 i { color: #dc2626; }
    .bkt-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .bkt-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .bkt-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }

    .bkt-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .bkt-loai-badge {
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

    .bkt-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .bkt-status-badge i { font-size: 0.65rem; opacity: 0.85; }

    .bkt-pending-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        animation: bktPendingPulse 1.6s ease-out infinite;
    }
    @keyframes bktPendingPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }

    .apx-welcome.bkt-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.bkt-welcome p i { color: #fef3c7; margin-right: 4px; }
    .bkt-sep { opacity: 0.5; }

    .bkt-create-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* ===== Hint card ===== */
    .bkt-hint {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 18px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #bfdbfe;
        border-left: 4px solid #1d4ed8;
        border-radius: 12px;
        font-size: 0.85rem;
        color: #1e3a8a;
        margin-bottom: 18px;
    }
    .bkt-hint-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: #fff;
        color: #1d4ed8;
        display: grid; place-items: center;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(29,78,216,0.15);
    }
    .bkt-hint em { font-style: normal; font-weight: 800; color: #1d4ed8; }
    .bkt-hint-cta {
        margin-left: auto;
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px;
        background: #1d4ed8;
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        border-radius: 999px;
        text-decoration: none;
        transition: all 0.18s ease;
        white-space: nowrap;
    }
    .bkt-hint-cta:hover {
        background: #1e3a8a;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(29,78,216,0.3);
    }

    /* ===== Filter card ===== */
    .bkt-filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .bkt-flabel {
        display: block;
        font-weight: 800;
        color: #1e3a8a;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .bkt-input-icon {
        position: relative;
    }
    .bkt-input-icon i {
        position: absolute;
        left: 12px; top: 50%; transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }
    .bkt-input-icon input { padding-left: 34px; }

    /* ===== Bảng đề kiểm tra ===== */
    .bkt-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .bkt-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .bkt-table thead th {
        padding: 12px 10px;
        font-weight: 800;
        border-bottom: 1px solid #bfdbfe;
    }
    .bkt-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .bkt-table tbody tr:last-child td { border-bottom: 0; }
    .bkt-table tbody tr:hover { background: #f8fafc; }

    .bkt-thumb {
        flex-shrink: 0;
        width: 44px; height: 44px;
        border-radius: 12px;
        color: #fff;
        display: grid; place-items: center;
        font-size: 1.05rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .bkt-name {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        margin-bottom: 4px;
    }
    .bkt-meta {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        display: flex; flex-wrap: wrap; gap: 5px;
    }
    .bkt-meta i { color: #1d4ed8; margin-right: 3px; }
    .bkt-meta-sep { opacity: 0.5; }
    .bkt-meta-sub {
        margin-top: 4px;
        font-size: 0.74rem;
        color: #475569;
        font-weight: 600;
    }
    .bkt-meta-sub i { color: #1d4ed8; margin-right: 4px; }

    /* Scope pill */
    .bkt-scope-pill {
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
    .bkt-attempts {
        margin-top: 6px;
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
    }
    .bkt-attempts i { color: #0ea5e9; margin-right: 4px; }

    /* Status pill */
    .bkt-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px;
        font-size: 0.7rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .bkt-status-pill i { font-size: 0.62rem; }

    .bkt-status-pill.is-success   { background: #dcfce7; color: #16a34a; }
    .bkt-status-pill.is-warning   { background: #fef3c7; color: #c2410c; }
    .bkt-status-pill.is-info      { background: #e0f2fe; color: #0369a1; }
    .bkt-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .bkt-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    /* Stat lines */
    .bkt-stat-line {
        font-size: 0.78rem;
        color: #1e293b;
        font-weight: 600;
        line-height: 1.45;
    }
    .bkt-stat-line i { margin-right: 5px; font-size: 0.75rem; }
    .bkt-stat-line.muted { color: #94a3b8; font-weight: 500; }

    /* Time lines */
    .bkt-time-line {
        font-size: 0.78rem;
        color: #1e293b;
        line-height: 1.5;
    }
    .bkt-time-line strong { color: #0f172a; }
    .bkt-time-label { color: #64748b; font-weight: 600; margin-right: 4px; }
    .bkt-time-label i { margin-right: 3px; }
    .bkt-time-line.muted { color: #94a3b8; font-size: 0.72rem; }
    .bkt-time-line.muted i { margin-right: 4px; }

    /* Action buttons */
    .bkt-actions {
        display: inline-flex;
        gap: 4px;
        flex-wrap: nowrap;
    }

    .bkt-action-btn {
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
        padding: 0;
    }
    .bkt-action-btn:hover {
        border-color: #1d4ed8;
        background: #1d4ed8;
        color: #fff;
        transform: translateY(-1px);
    }
    .bkt-action-btn.success { color: #16a34a; }
    .bkt-action-btn.success:hover { background: #16a34a; border-color: #16a34a; color: #fff; }
    .bkt-action-btn.danger { color: #dc2626; }
    .bkt-action-btn.danger:hover { background: #dc2626; border-color: #dc2626; color: #fff; }
    .bkt-action-btn.dark { color: #1e293b; }
    .bkt-action-btn.dark:hover { background: #1e293b; border-color: #1e293b; color: #fff; }

    /* Pagination */
    .bkt-pagination {
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: center;
    }
    .bkt-pagination nav { margin: 0; }

    /* Empty state */
    .bkt-empty {
        padding: 60px 30px;
        text-align: center;
    }

    .bkt-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #1d4ed8;
        font-size: 2rem;
    }

    .bkt-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .bkt-empty p {
        font-size: 0.9rem; color: #94a3b8;
        max-width: 440px; margin: 0 auto 20px;
        line-height: 1.55;
    }

    .min-w-0 { min-width: 0; }

    /* Responsive */
    @media (max-width: 991.98px) {
        .bkt-hint { flex-wrap: wrap; }
        .bkt-hint-cta { margin-left: 0; }
        .bkt-table { font-size: 0.85rem; }
        .bkt-table thead th, .bkt-table tbody td { padding: 10px 8px; }
        .bkt-thumb { width: 38px; height: 38px; font-size: 0.92rem; }
    }
</style>
@endsection
