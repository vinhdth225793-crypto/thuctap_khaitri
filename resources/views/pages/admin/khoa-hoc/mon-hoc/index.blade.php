@extends('layouts.app')

@section('title', 'Quản lý Nhóm ngành')

@section('content')
<div class="container-fluid nhom-nganh-page">
    {{-- ========== Welcome banner: giới thiệu mục đích trang ========== --}}
    <div class="nn-welcome">
        <div class="nn-welcome-icon"><i class="fas fa-layer-group"></i></div>
        <div class="nn-welcome-text">
            <h4>Quản lý Nhóm ngành đào tạo</h4>
            <p>
                Nhóm ngành là <strong>lớp phân loại lớn nhất</strong> của hệ thống đào tạo. Mỗi khóa học đều thuộc về
                một nhóm ngành để học viên dễ tìm kiếm và admin dễ tổ chức nội dung. Tại đây bạn có thể
                <strong>tạo, sửa, kích hoạt/tạm dừng</strong> các nhóm ngành.
            </p>
        </div>
        <div class="nn-welcome-cta">
            <button type="button" id="nnViewModeBtn" class="nn-view-toggle" data-mode="full" aria-label="Đổi chế độ hiển thị">
                <i class="fas fa-compress-alt"></i>
                <span>Thu gọn</span>
            </button>
            <a href="{{ route('admin.nhom-nganh.create') }}" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Thêm nhóm ngành mới
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- ========== ① Tổng quan ========== --}}
    <section class="nn-section" data-collapsible>
        <header class="nn-section-head">
            <div class="nn-section-title">
                <span class="nn-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan nhóm ngành</h2>
                    <p>Bốn chỉ số nhanh giúp bạn nắm tình hình nhóm ngành đào tạo.</p>
                </div>
            </div>
            <div class="nn-section-meta">
                <span class="nn-meta-pill"><strong>{{ number_format($stats['tong']) }}</strong> nhóm</span>
            </div>
        </header>

        <div class="nn-section-body">
            <div class="row g-3">
                <div class="col-xl-3 col-md-6">
                    <div class="nn-stat tone-primary">
                        <div class="ns-icon"><i class="fas fa-shapes"></i></div>
                        <div class="ns-text">
                            <strong>{{ number_format($stats['tong']) }}</strong>
                            <small>Tổng nhóm ngành</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="nn-stat tone-success">
                        <div class="ns-icon"><i class="fas fa-circle-check"></i></div>
                        <div class="ns-text">
                            <strong>{{ number_format($stats['hoat_dong']) }}</strong>
                            <small>Đang hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="nn-stat tone-warning">
                        <div class="ns-icon"><i class="fas fa-pause-circle"></i></div>
                        <div class="ns-text">
                            <strong>{{ number_format($stats['tam_dung']) }}</strong>
                            <small>Tạm dừng</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="nn-stat tone-info">
                        <div class="ns-icon"><i class="fas fa-book-open"></i></div>
                        <div class="ns-text">
                            <strong>{{ number_format($stats['tong_khoa_hoc']) }}</strong>
                            <small>{{ $stats['co_khoa_hoc'] }}/{{ $stats['tong'] }} nhóm có khóa học</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Hướng dẫn 3 bước (collapsible) ========== --}}
    <details class="nn-guide" open>
        <summary>
            <span class="ng-icon"><i class="fas fa-circle-info"></i></span>
            <div>
                <strong>Hướng dẫn nhanh — 3 bước cần làm tại trang này</strong>
                <small>Click vào biểu tượng để mở/đóng phần hướng dẫn.</small>
            </div>
            <i class="fas fa-chevron-down ng-toggle"></i>
        </summary>
        <div class="nn-guide-body">
            <div class="ng-step">
                <span class="ng-step-num">1</span>
                <div>
                    <strong>Tạo nhóm ngành mới</strong>
                    <small>Nhấn "Thêm nhóm ngành mới" → đặt tên (vd: <em>Tiếng Anh</em>, <em>Tin học</em>, <em>Kế toán</em>) → upload ảnh đại diện.</small>
                </div>
            </div>
            <div class="ng-step">
                <span class="ng-step-num">2</span>
                <div>
                    <strong>Tạo khóa học thuộc nhóm</strong>
                    <small>Vào mục <a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a> → tạo khóa và chọn đúng nhóm ngành đã tạo.</small>
                </div>
            </div>
            <div class="ng-step">
                <span class="ng-step-num">3</span>
                <div>
                    <strong>Bật / tắt hiển thị nhóm ngành</strong>
                    <small>Dùng nút <i class="fas fa-power-off"></i> ở từng dòng để <strong>kích hoạt</strong> hoặc <strong>tạm dừng</strong>. Nhóm tạm dừng sẽ ẩn khỏi trang chủ.</small>
                </div>
            </div>
        </div>
    </details>

    {{-- ========== ② Tìm kiếm & danh sách ========== --}}
    <section class="nn-section" data-collapsible>
        <header class="nn-section-head">
            <div class="nn-section-title">
                <span class="nn-section-num">2</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách nhóm ngành</h2>
                    <p>Tìm theo tên/mã, lọc theo trạng thái và thực hiện thao tác quản lý.</p>
                </div>
            </div>
            <div class="nn-section-meta">
                <span class="nn-meta-pill"><strong>{{ $nhomNganhs->total() }}</strong> kết quả</span>
            </div>
        </header>

        <div class="nn-section-body">

    <div class="vip-card mb-3 border-0 shadow-sm nn-filter-card">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.nhom-nganh.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <label class="nn-field-label">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control"
                               placeholder="Nhập tên hoặc mã nhóm ngành..." value="{{ $search ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="nn-field-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select vip-form-control">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="1" {{ (string) ($trangThai ?? '') === '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ (string) ($trangThai ?? '') === '0' ? 'selected' : '' }}>Tạm dừng</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="nn-field-label invisible">Lọc</label>
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i> Lọc dữ liệu</button>
                </div>
                <div class="col-md-2">
                    <label class="nn-field-label invisible">Đặt lại</label>
                    <a href="{{ route('admin.nhom-nganh.index') }}" class="btn btn-light w-100 fw-bold border"><i class="fas fa-rotate-left me-1"></i> Đặt lại</a>
                </div>
            </form>
            @if(filled($search) || (string) ($trangThai ?? '') !== '')
                <div class="nn-active-filters mt-3">
                    <span class="text-muted small me-2">Đang lọc:</span>
                    @if(filled($search))
                        <span class="nn-filter-tag">Từ khóa: <strong>{{ $search }}</strong></span>
                    @endif
                    @if((string) ($trangThai ?? '') !== '')
                        <span class="nn-filter-tag">Trạng thái: <strong>{{ $trangThai === '1' ? 'Hoạt động' : 'Tạm dừng' }}</strong></span>
                    @endif
                    <span class="text-muted small">Tìm thấy <strong class="text-primary">{{ $nhomNganhs->total() }}</strong> kết quả.</span>
                </div>
            @endif
        </div>
    </div>

    <div class="vip-card shadow-sm border-0">
        <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="vip-card-title small fw-bold text-uppercase mb-0">
                <i class="fas fa-table-list me-2 text-primary"></i> Danh sách các nhóm ngành đào tạo
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                {{ $nhomNganhs->total() }} nhóm
            </span>
        </div>
        <div class="vip-card-body p-0">
            @if($nhomNganhs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 nn-table">
                        <thead class="bg-light smaller text-muted text-uppercase">
                            <tr>
                                <th class="ps-4 text-center" width="60">STT</th>
                                <th width="80">Hình ảnh</th>
                                <th width="120">Mã nhóm</th>
                                <th>Tên nhóm ngành</th>
                                <th class="text-center" width="120">Số khóa học</th>
                                <th class="text-center" width="120">Trạng thái</th>
                                <th class="pe-4 text-center" width="180">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nhomNganhs as $index => $item)
                                <tr>
                                    <td class="text-center ps-4 text-muted small">{{ $nhomNganhs->firstItem() + $index }}</td>
                                    <td>
                                        <div class="nn-thumb">
                                            @if($item->hinh_anh)
                                                <img src="{{ asset($item->hinh_anh) }}" alt="{{ $item->ten_nhom_nganh }}">
                                            @else
                                                <i class="fas fa-layer-group"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td><code class="nn-code">{{ $item->ma_nhom_nganh }}</code></td>
                                    <td>
                                        <a href="{{ route('admin.nhom-nganh.show', $item->id) }}" class="fw-bold text-dark text-decoration-none nn-name-link">
                                            {{ $item->ten_nhom_nganh }}
                                        </a>
                                        @if($item->mo_ta)
                                            <div class="smaller text-muted italic text-truncate" style="max-width: 320px;">{{ $item->mo_ta }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php $count = $item->khoa_hocs_count ?? $item->khoaHocs()->count(); @endphp
                                        @if($count > 0)
                                            <span class="badge bg-info-soft text-info rounded-pill px-3 border border-info shadow-xs">
                                                <i class="fas fa-book me-1"></i> {{ $count }} khóa
                                            </span>
                                        @else
                                            <span class="text-muted small italic">Chưa có</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->trang_thai)
                                            <span class="badge bg-success-soft text-success px-3 border border-success">
                                                <i class="fas fa-check-circle me-1"></i> Hoạt động
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-soft text-secondary px-3 border border-secondary">
                                                <i class="fas fa-pause-circle me-1"></i> Tạm dừng
                                            </span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.nhom-nganh.show', $item->id) }}" class="btn btn-sm btn-outline-primary action-btn" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.nhom-nganh.edit', $item->id) }}" class="btn btn-sm btn-outline-warning action-btn" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.nhom-nganh.toggle-status', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary action-btn" title="{{ $item->trang_thai ? 'Tạm dừng' : 'Kích hoạt' }}">
                                                    <i class="fas fa-power-off"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-danger action-btn" onclick="confirmDelete({{ $item->id }}, '{{ $item->ten_nhom_nganh }}')" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $nhomNganhs->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="nn-empty">
                    <div class="nn-empty-icon"><i class="fas fa-folder-open"></i></div>
                    <h5>{{ filled($search) || $trangThai !== '' ? 'Không có nhóm ngành phù hợp bộ lọc' : 'Chưa có nhóm ngành nào' }}</h5>
                    <p class="text-muted">
                        @if(filled($search) || $trangThai !== '')
                            Thử tìm với từ khóa khác hoặc bỏ bộ lọc.
                        @else
                            Bắt đầu bằng cách tạo nhóm ngành đầu tiên — đây là bước nền tảng trước khi tạo khóa học.
                        @endif
                    </p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        @if(filled($search) || $trangThai !== '')
                            <a href="{{ route('admin.nhom-nganh.index') }}" class="btn btn-light border">
                                <i class="fas fa-rotate-left me-1"></i> Bỏ bộ lọc
                            </a>
                        @endif
                        <a href="{{ route('admin.nhom-nganh.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Thêm nhóm ngành mới
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
        </div> {{-- /nn-section-body --}}
    </section>
</div>

<div class="modal fade shadow" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Xác nhận xóa nhóm ngành</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-danger"><i class="fas fa-trash-alt fa-3x opacity-25"></i></div>
                <p class="mb-1 fw-bold fs-5">Xóa nhóm ngành: <span id="deleteItemName" class="text-danger"></span>?</p>
                <p class="text-muted small mb-0">Tất cả khóa học, module và lịch dạy liên quan sẽ bị xóa vĩnh viễn.</p>
            </div>
            <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Đồng ý xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(id, name) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/nhom-nganh/${id}`;
        document.getElementById('deleteItemName').textContent = name;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Toggle chế độ "Thu gọn / Tổng thể" cho cả trang
    (function () {
        const btn = document.getElementById('nnViewModeBtn');
        if (!btn) return;
        const sections = document.querySelectorAll('.nn-section[data-collapsible]');
        const STORAGE_KEY = 'nnViewMode';

        const applyMode = (mode) => {
            const isCompact = mode === 'compact';
            sections.forEach(s => s.classList.toggle('is-collapsed', isCompact));
            btn.dataset.mode = mode;
            btn.querySelector('span').textContent = isCompact ? 'Tổng thể' : 'Thu gọn';
            btn.querySelector('i').className = isCompact ? 'fas fa-expand-alt' : 'fas fa-compress-alt';
        };

        // Khôi phục mode đã lưu
        applyMode(localStorage.getItem(STORAGE_KEY) || 'full');

        btn.addEventListener('click', () => {
            const next = btn.dataset.mode === 'compact' ? 'full' : 'compact';
            applyMode(next);
            localStorage.setItem(STORAGE_KEY, next);
        });
    })();
</script>
@endpush

<style>
    .nhom-nganh-page { display: flex; flex-direction: column; gap: 6px; }

    /* ===== Welcome banner — xanh dương chủ đạo ===== */
    .nn-welcome {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 22px 26px;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%);
        color: #fff;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22);
    }

    .nn-welcome::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }

    .nn-welcome::after {
        content: '';
        position: absolute;
        bottom: -90px;
        right: 80px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
    }

    .nn-welcome-icon {
        flex-shrink: 0;
        width: 60px; height: 60px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        backdrop-filter: blur(8px);
        color: #fff;
        border-radius: 14px;
        display: grid; place-items: center;
        font-size: 1.5rem;
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.18);
        z-index: 1;
    }

    .nn-welcome-text { flex: 1; min-width: 0; z-index: 1; }
    .nn-welcome-text h4 { font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0 0 6px; }
    .nn-welcome-text p { margin: 0; font-size: 0.88rem; color: rgba(255, 255, 255, 0.92); line-height: 1.6; }
    .nn-welcome-text strong { color: #fef3c7; font-weight: 700; }

    .nn-welcome-cta { flex-shrink: 0; z-index: 1; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

    /* Nút "Thu gọn / Tổng thể" */
    .nn-view-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 700;
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .nn-view-toggle:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.6);
        transform: translateY(-1px);
    }
    .nn-view-toggle i { font-size: 0.78rem; }

    /* ===== Section: wrapper ===== */
    .nn-section {
        margin-bottom: 18px;
    }

    /* Section heading */
    .nn-section-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        border: 1px solid #bfdbfe;
        border-left: 4px solid #1d4ed8;
        border-radius: 10px;
        transition: all 0.25s ease;
    }

    .nn-section-title { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }

    .nn-section-num {
        flex-shrink: 0;
        width: 32px; height: 32px;
        border-radius: 10px;
        background: linear-gradient(135deg, #4361ee 0%, #2f46c9 100%);
        color: #fff;
        display: grid; place-items: center;
        font-weight: 900; font-size: 0.9rem;
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
    }

    .nn-section-title h2 { font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 2px; display: inline-flex; align-items: center; gap: 8px; }
    .nn-section-title h2 i { font-size: 0.9rem; }
    .nn-section-title p { margin: 0; font-size: 0.78rem; color: #64748b; }

    .nn-section-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .nn-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        background: #fff;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        color: #4361ee;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .nn-meta-pill strong { font-weight: 800; color: #1d4ed8; }

    .nn-section-body {
        animation: nnFadeIn 0.25s ease;
        overflow: hidden;
    }

    @keyframes nnFadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Trạng thái thu gọn — toàn cục qua nút */
    .nn-section.is-collapsed .nn-section-body {
        display: none;
    }

    .nn-section.is-collapsed .nn-section-head {
        margin-bottom: 0;
        opacity: 0.85;
    }

    /* Số trên section-num đổi màu theo brand mới */
    .nn-section-num {
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%) !important;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25) !important;
    }

    .nn-section-title h2 i { color: #1d4ed8 !important; }

    /* ===== Stats ===== */
    .nn-stat {
        display: flex; align-items: center; gap: 14px;
        padding: 16px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        height: 100%;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .nn-stat::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: var(--ns-color, #4361ee);
        transform: scaleY(0);
        transform-origin: top center;
        transition: transform 0.2s ease;
    }

    .nn-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
        border-color: var(--ns-color, #4361ee);
    }
    .nn-stat:hover::before { transform: scaleY(1); }

    .nn-stat.tone-primary  { --ns-color: #0f766e; }
    .nn-stat.tone-success  { --ns-color: #16a34a; }
    .nn-stat.tone-warning  { --ns-color: #d97706; }
    .nn-stat.tone-info     { --ns-color: #0ea5e9; }

    .ns-icon {
        flex-shrink: 0;
        width: 48px; height: 48px;
        border-radius: 12px;
        display: grid; place-items: center;
        background: color-mix(in srgb, var(--ns-color) 12%, white);
        color: var(--ns-color);
        font-size: 1.15rem;
        transition: all 0.2s ease;
    }

    .nn-stat:hover .ns-icon { background: var(--ns-color); color: #fff; transform: scale(1.05); }

    .ns-text strong {
        display: block;
        font-size: 1.6rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
    }
    .ns-text small {
        display: block;
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    /* ===== Guide ===== */
    .nn-guide {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 18px;
        transition: box-shadow 0.2s ease;
    }
    .nn-guide[open] { box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05); }

    .nn-guide summary {
        list-style: none;
        cursor: pointer;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .nn-guide summary::-webkit-details-marker { display: none; }

    .ng-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
        display: grid; place-items: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .nn-guide summary > div { flex: 1; min-width: 0; }
    .nn-guide summary strong { display: block; font-size: 0.92rem; font-weight: 800; color: #0f172a; }
    .nn-guide summary small { display: block; font-size: 0.75rem; color: #64748b; margin-top: 2px; }

    .ng-toggle {
        font-size: 0.8rem;
        color: #94a3b8;
        transition: transform 0.25s ease;
    }
    .nn-guide[open] .ng-toggle { transform: rotate(180deg); color: #4361ee; }

    .nn-guide-body {
        padding: 0 18px 18px 18px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        border-top: 1px dashed #e2e8f0;
        padding-top: 14px;
    }

    .ng-step {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 12px;
        background: #f8fafc;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .ng-step:hover { background: #eef2ff; }

    .ng-step-num {
        flex-shrink: 0;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4361ee 0%, #2f46c9 100%);
        color: #fff;
        display: grid; place-items: center;
        font-weight: 800;
        font-size: 0.85rem;
    }

    .ng-step strong { display: block; font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
    .ng-step small { display: block; font-size: 0.76rem; color: #64748b; line-height: 1.45; }
    .ng-step small a { color: #4361ee; font-weight: 600; }
    .ng-step small em { color: #1d4ed8; font-style: normal; font-weight: 600; }

    /* ===== Filter ===== */
    .nn-filter-card { border-radius: 12px; }

    .nn-field-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .nn-active-filters {
        display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }

    .nn-filter-tag {
        display: inline-flex; align-items: center;
        padding: 3px 10px;
        background: #eef2ff;
        color: #4361ee;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    /* ===== Table ===== */
    .nn-table tbody tr { transition: background 0.15s ease; }
    .nn-table tbody tr:hover { background: #f8fafc; }

    .nn-thumb {
        width: 56px; height: 56px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        border: 1px solid #99f6e4;
        display: grid; place-items: center;
        overflow: hidden;
        color: #0f766e;
        font-size: 1.4rem;
    }

    .nn-thumb img {
        width: 100%; height: 100%;
        object-fit: cover;
    }

    .nn-code {
        display: inline-block;
        padding: 3px 10px;
        background: #eef2ff;
        color: #1d4ed8;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .nn-name-link:hover { color: #1d4ed8 !important; }

    .action-btn {
        width: 32px; height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        padding: 0;
        transition: all 0.18s ease;
    }
    .action-btn:hover { transform: translateY(-2px); }

    /* ===== Empty state ===== */
    .nn-empty {
        text-align: center;
        padding: 50px 30px;
    }

    .nn-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 16px;
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #14b8a6;
        font-size: 2rem;
    }

    .nn-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .nn-empty p { font-size: 0.88rem; max-width: 440px; margin: 0 auto 18px; line-height: 1.55; }

    /* ===== Helpers ===== */
    .smaller { font-size: 0.75rem; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .object-fit-cover { object-fit: cover; }
    .italic { font-style: italic; }
    .vip-form-control:focus { box-shadow: none; border-color: #0d6efd; }

    /* ===== Responsive ===== */
    @media (max-width: 991.98px) {
        .nn-welcome { flex-direction: column; text-align: center; }
        .nn-welcome::before { display: none; }
        .nn-guide-body { grid-template-columns: 1fr; }
    }

    @media (max-width: 720px) {
        .nn-welcome-icon { width: 48px; height: 48px; font-size: 1.2rem; }
        .nn-welcome-text h4 { font-size: 0.95rem; }
        .nn-welcome-text p { font-size: 0.78rem; }
        .ns-text strong { font-size: 1.3rem; }
    }
</style>
@endsection
