@extends('layouts.app', ['title' => 'Phê duyệt bài kiểm tra'])

@section('content')
@php
    $baseQuery = \App\Models\BaiKiemTra::query();
    $stats = [
        'tong'      => (clone $baseQuery)->count(),
        'cho_duyet' => (clone $baseQuery)->where('trang_thai_duyet', 'cho_duyet')->count(),
        'da_duyet'  => (clone $baseQuery)->where('trang_thai_duyet', 'da_duyet')->count(),
        'phat_hanh' => (clone $baseQuery)->where('trang_thai_phat_hanh', 'phat_hanh')->count(),
        'tu_choi'   => (clone $baseQuery)->where('trang_thai_duyet', 'tu_choi')->count(),
    ];

    $hasFilter = request()->filled('search')
        || request()->filled('trang_thai_duyet')
        || request()->filled('trang_thai_phat_hanh');
@endphp

<div class="container-fluid admin-page-x pdi-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome pdi-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-clipboard-check"></i></div>
        <div class="apx-welcome-text">
            <div class="pdi-tag-row">
                <span class="pdi-loai-badge">
                    <i class="fas fa-shield-halved"></i> PHÊ DUYỆT ĐỀ THI
                </span>
                <span class="pdi-status-badge">
                    <i class="fas fa-layer-group"></i>
                    {{ $stats['tong'] }} đề
                </span>
                @if($stats['cho_duyet'] > 0)
                    <span class="pdi-pending-badge">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $stats['cho_duyet'] }} chờ duyệt
                    </span>
                @endif
            </div>
            <h4>Phê duyệt bài kiểm tra</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $stats['da_duyet'] }} đã duyệt</span>
                <span class="pdi-sep">·</span>
                <span><i class="fas fa-broadcast-tower"></i> {{ $stats['phat_hanh'] }} đang phát hành</span>
                <span class="pdi-sep">·</span>
                <span><i class="fas fa-times-circle"></i> {{ $stats['tu_choi'] }} bị từ chối</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn btn-light text-primary fw-bold shadow-sm pdi-create-btn">
                <i class="fas fa-database me-1"></i> Ngân hàng câu hỏi
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
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan đề thi</h2>
                    <p>Bốn chỉ số nhanh giúp admin quản lý hàng đợi phê duyệt.</p>
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
                        <small>Tổng đề thi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['cho_duyet'] }}</strong>
                        <small>Chờ phê duyệt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['da_duyet'] }}</strong>
                        <small>Đã duyệt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
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
                    <p>Tìm theo tiêu đề, mô tả hoặc lọc theo trạng thái duyệt / phát hành.</p>
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

        <div class="pdi-filter-card">
            <form method="GET" action="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="pdi-flabel">Tìm kiếm</label>
                    <div class="pdi-input-icon">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control" placeholder="Tiêu đề, mô tả đề thi...">
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="pdi-flabel">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        @foreach(['nhap' => 'Nháp', 'cho_duyet' => 'Chờ duyệt', 'da_duyet' => 'Đã duyệt', 'tu_choi' => 'Từ chối'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai_duyet') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="pdi-flabel">Trạng thái phát hành</label>
                    <select name="trang_thai_phat_hanh" class="form-select">
                        <option value="">Tất cả phát hành</option>
                        @foreach(['nhap' => 'Nháp', 'phat_hanh' => 'Phát hành', 'dong' => 'Đóng'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai_phat_hanh') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold flex-fill">
                        <i class="fas fa-search me-1"></i> Lọc
                    </button>
                    <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-outline-secondary" title="Đặt lại">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </section>

    {{-- ========== ③ Danh sách đề thi ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách đề thi cần xử lý</h2>
                    <p>Bấm "Chi tiết" để xem đề và quyết định duyệt / từ chối / phát hành.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $baiKiemTras->total() }}</strong> kết quả</span>
            </div>
        </header>

        <div class="pdi-table-wrap">
            @if($baiKiemTras->isEmpty())
                <div class="pdi-empty">
                    <div class="pdi-empty-icon"><i class="fas fa-inbox"></i></div>
                    <h5>Không tìm thấy bài kiểm tra nào</h5>
                    <p>Thử đổi bộ lọc hoặc từ khóa tìm kiếm để xem nhiều đề thi hơn.</p>
                    @if($hasFilter)
                        <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-outline-secondary fw-bold">
                            <i class="fas fa-rotate-left me-1"></i> Bỏ bộ lọc
                        </a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 pdi-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Đề thi</th>
                                <th>Khóa học / Module</th>
                                <th>GV tạo</th>
                                <th class="text-center">Câu hỏi</th>
                                <th class="text-center">Duyệt</th>
                                <th class="text-center">Phát hành</th>
                                <th class="pe-4 text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($baiKiemTras as $bkt)
                                @php
                                    $duyetClass = match($bkt->trang_thai_duyet) {
                                        'da_duyet'  => 'is-success',
                                        'cho_duyet' => 'is-warning',
                                        'tu_choi'   => 'is-danger',
                                        default     => 'is-secondary',
                                    };
                                    $duyetIcon = match($bkt->trang_thai_duyet) {
                                        'da_duyet'  => 'fa-check-circle',
                                        'cho_duyet' => 'fa-hourglass-half',
                                        'tu_choi'   => 'fa-times-circle',
                                        default     => 'fa-pen',
                                    };
                                    $phClass = match($bkt->trang_thai_phat_hanh) {
                                        'phat_hanh' => 'is-success',
                                        'dong'      => 'is-danger',
                                        default     => 'is-secondary',
                                    };
                                    $phIcon = match($bkt->trang_thai_phat_hanh) {
                                        'phat_hanh' => 'fa-broadcast-tower',
                                        'dong'      => 'fa-lock',
                                        default     => 'fa-eye-slash',
                                    };
                                    $idColor = $bkt->id % 6;
                                    $gradients = [
                                        'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                                        'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                                        'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                                        'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                                        'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                                        'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                                    ];
                                    $gvName = $bkt->nguoiTao->ho_ten ?? 'Không rõ';
                                    $gvInitial = mb_strtoupper(mb_substr(trim($gvName), 0, 1, 'UTF-8'), 'UTF-8');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="pdi-thumb" style="background: {{ $gradients[$idColor] }};">
                                                <i class="fas fa-file-signature"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="pdi-name">{{ $bkt->tieu_de }}</div>
                                                <div class="pdi-tags">
                                                    <span class="pdi-tag-soft is-info">
                                                        <i class="far fa-clock"></i> {{ $bkt->thoi_gian_lam_bai }} phút
                                                    </span>
                                                    <span class="pdi-tag-soft">
                                                        <i class="fas fa-tag"></i> {{ $bkt->loai_noi_dung_label }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="pdi-course">{{ $bkt->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }}</div>
                                        <div class="pdi-module">
                                            <i class="fas fa-cube"></i>
                                            {{ $bkt->moduleHoc->ten_module ?? 'Đề cuối khóa / dùng chung' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="pdi-avatar" style="background: {{ $gradients[($bkt->nguoi_tao_id ?? 0) % 6] }};">
                                                {{ $gvInitial ?: '?' }}
                                            </div>
                                            <div>
                                                <div class="pdi-gv-name">{{ $gvName }}</div>
                                                <div class="pdi-gv-time">
                                                    <i class="far fa-clock"></i>
                                                    {{ $bkt->updated_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($bkt->content_mode_key === 'tu_luan_tu_do')
                                            <span class="pdi-tag-soft is-info">
                                                <i class="fas fa-pen-fancy"></i> Tự luận
                                            </span>
                                        @else
                                            <div class="pdi-q-count">{{ $bkt->chi_tiet_cau_hois_count }}</div>
                                            <div class="pdi-q-label">câu hỏi</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="pdi-status-pill {{ $duyetClass }}">
                                            <i class="fas {{ $duyetIcon }}"></i> {{ $bkt->trang_thai_duyet_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="pdi-status-pill {{ $phClass }}">
                                            <i class="fas {{ $phIcon }}"></i> {{ $bkt->trang_thai_phat_hanh_label }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('admin.kiem-tra-online.phe-duyet.show', $bkt->id) }}"
                                           class="pdi-detail-btn">
                                            <i class="fas fa-magnifying-glass"></i>
                                            <span>Chi tiết</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($baiKiemTras->hasPages())
                    <div class="pdi-pagination">
                        {{ $baiKiemTras->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .pdi-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .pdi-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .pdi-page .apx-section-title h2 i { color: #dc2626; }
    .pdi-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .pdi-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .pdi-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .pdi-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .pdi-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff; font-size: 0.7rem; font-weight: 800;
        letter-spacing: 1px; border-radius: 999px;
    }
    .pdi-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff; font-size: 0.72rem; font-weight: 700;
        border-radius: 999px;
    }
    .pdi-status-badge i { font-size: 0.65rem; opacity: 0.85; }
    .pdi-pending-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f; font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
        animation: pdiPendingPulse 1.6s ease-out infinite;
    }
    @keyframes pdiPendingPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }
    .apx-welcome.pdi-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.pdi-welcome p i { color: #fef3c7; margin-right: 4px; }
    .pdi-sep { opacity: 0.5; }
    .pdi-create-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* ===== Filter card ===== */
    .pdi-filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .pdi-flabel {
        display: block;
        font-weight: 800;
        color: #7f1d1d;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .pdi-input-icon { position: relative; }
    .pdi-input-icon i {
        position: absolute;
        left: 12px; top: 50%; transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }
    .pdi-input-icon input { padding-left: 34px; }

    /* ===== Bảng ===== */
    .pdi-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }
    .pdi-table thead {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #7f1d1d;
        letter-spacing: 0.5px;
    }
    .pdi-table thead th {
        padding: 12px 10px;
        font-weight: 800;
        border-bottom: 1px solid #fecaca;
    }
    .pdi-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .pdi-table tbody tr:last-child td { border-bottom: 0; }
    .pdi-table tbody tr:hover { background: #fafafa; }

    .pdi-thumb {
        flex-shrink: 0;
        width: 44px; height: 44px;
        border-radius: 12px;
        color: #fff;
        display: grid; place-items: center;
        font-size: 1.05rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .pdi-name {
        font-size: 0.94rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
        margin-bottom: 6px;
    }
    .pdi-tags {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    .pdi-tag-soft {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .pdi-tag-soft.is-info { background: #eff6ff; color: #1d4ed8; }
    .pdi-tag-soft i { font-size: 0.62rem; opacity: 0.85; }

    .pdi-course {
        font-size: 0.86rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        max-width: 240px;
    }
    .pdi-module {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 3px;
    }
    .pdi-module i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .pdi-avatar {
        flex-shrink: 0;
        width: 36px; height: 36px;
        border-radius: 50%;
        color: #fff;
        font-weight: 800;
        font-size: 0.82rem;
        display: grid; place-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .pdi-gv-name {
        font-size: 0.84rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .pdi-gv-time {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 2px;
    }
    .pdi-gv-time i { color: #1d4ed8; margin-right: 4px; font-size: 0.62rem; }

    .pdi-q-count {
        font-size: 1.15rem;
        font-weight: 900;
        color: #1d4ed8;
        line-height: 1;
    }
    .pdi-q-label {
        font-size: 0.7rem;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 2px;
        text-transform: uppercase;
    }

    /* Status pill */
    .pdi-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .pdi-status-pill i { font-size: 0.62rem; }
    .pdi-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .pdi-status-pill.is-warning   { background: #fef3c7; color: #b45309; animation: pdiPulse 1.6s ease-out infinite; }
    .pdi-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .pdi-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
    @keyframes pdiPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.4); }
        50%      { box-shadow: 0 0 0 4px rgba(251, 191, 36, 0); }
    }

    /* Detail button */
    .pdi-detail-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 16px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.8rem;
        font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);
        transition: all 0.2s ease;
    }
    .pdi-detail-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.32);
        color: #fff;
    }
    .pdi-detail-btn i { font-size: 0.72rem; }

    /* Pagination */
    .pdi-pagination {
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: center;
    }
    .pdi-pagination nav { margin: 0; }

    /* Empty state */
    .pdi-empty {
        padding: 60px 30px;
        text-align: center;
    }
    .pdi-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #dc2626;
        font-size: 2rem;
    }
    .pdi-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .pdi-empty p {
        font-size: 0.9rem; color: #94a3b8;
        max-width: 460px; margin: 0 auto 20px;
        line-height: 1.55;
    }

    .min-w-0 { min-width: 0; }

    @media (max-width: 991.98px) {
        .pdi-table { font-size: 0.85rem; }
        .pdi-table thead th, .pdi-table tbody td { padding: 10px 8px; }
        .pdi-thumb { width: 38px; height: 38px; font-size: 0.92rem; }
        .pdi-avatar { width: 32px; height: 32px; font-size: 0.74rem; }
    }
</style>
@endsection
