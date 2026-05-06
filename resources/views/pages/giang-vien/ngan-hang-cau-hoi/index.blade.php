@extends('layouts.app', ['title' => 'Ngân hàng câu hỏi'])

@section('content')
<div class="container-fluid admin-page-x nhch-page">
    {{-- Welcome banner xanh --}}
    <div class="apx-welcome nhch-welcome">
        <div class="apx-welcome-icon">
            <i class="fas fa-database"></i>
        </div>
        <div class="apx-welcome-text">
            <div class="nhch-tag-row">
                <span class="nhch-loai-badge"><i class="fas fa-book"></i> NGÂN HÀNG CÂU HỎI</span>
                <span class="nhch-status-badge">
                    <i class="fas fa-user"></i> Của tôi: <strong>{{ $countCuaToi }}</strong>
                </span>
                <span class="nhch-status-badge">
                    <i class="fas fa-globe-asia"></i> Đã công bố: <strong>{{ $countCongBo }}</strong>
                </span>
            </div>
            <h4>Ngân hàng câu hỏi</h4>
            <p>
                Quản lý câu hỏi do bạn tạo + xem những câu hỏi admin đã công bố cho mọi giảng viên.
                Câu hỏi được tái sử dụng cho nhiều bài kiểm tra.
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Bài kiểm tra</span>
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="nhch-tabs">
        <a href="{{ route('giang-vien.ngan-hang-cau-hoi.index', ['tab' => 'cua-toi']) }}"
           class="nhch-tab {{ $tab === 'cua-toi' ? 'is-active' : '' }}">
            <i class="fas fa-user"></i> Câu hỏi của tôi
            <span class="nhch-tab__count">{{ $countCuaToi }}</span>
        </a>
        <a href="{{ route('giang-vien.ngan-hang-cau-hoi.index', ['tab' => 'cong-bo']) }}"
           class="nhch-tab {{ $tab === 'cong-bo' ? 'is-active' : '' }}">
            <i class="fas fa-globe-asia"></i> Đã công bố (mọi GV)
            <span class="nhch-tab__count">{{ $countCongBo }}</span>
        </a>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="nhch-filter">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="nhch-filter__row">
            <div class="nhch-filter__field">
                <i class="fas fa-search"></i>
                <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Tìm theo nội dung hoặc mã câu hỏi…" class="form-control form-control-sm">
            </div>
            <select name="loai_cau_hoi" class="form-select form-select-sm" style="max-width: 160px;">
                <option value="">Tất cả loại</option>
                @foreach($questionTypeOptions as $val => $label)
                    <option value="{{ $val }}" @selected($loaiCauHoi === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="muc_do" class="form-select form-select-sm" style="max-width: 140px;">
                <option value="">Mức độ</option>
                @foreach($difficultyOptions as $val => $label)
                    <option value="{{ $val }}" @selected($mucDo === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @if($khoaHocs->isNotEmpty())
                <select name="khoa_hoc_id" class="form-select form-select-sm" style="max-width: 220px;">
                    <option value="">Mọi khoá học</option>
                    @foreach($khoaHocs as $kh)
                        <option value="{{ $kh->id }}" @selected((int) $khoaHocId === (int) $kh->id)>{{ $kh->ten_khoa_hoc }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary btn-sm fw-bold">
                <i class="fas fa-filter"></i> Lọc
            </button>
            @if($keyword || $loaiCauHoi || $mucDo || $khoaHocId)
                <a href="{{ route('giang-vien.ngan-hang-cau-hoi.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-rotate-left"></i> Xoá lọc
                </a>
            @endif
        </div>
    </form>

    {{-- Bulk action toolbar — chỉ hiện khi đang ở tab "Của tôi" và có select --}}
    @if($tab === 'cua-toi' && $cauHois->isNotEmpty())
        <form id="nhch-bulk-form" action="{{ route('giang-vien.ngan-hang-cau-hoi.bulk-toggle-public') }}" method="POST" class="nhch-bulk-toolbar d-none">
            @csrf
            <div class="nhch-bulk-toolbar__main">
                <span class="nhch-bulk-toolbar__count">
                    <i class="fas fa-check-square"></i> Đã chọn <strong id="nhch-bulk-count">0</strong> câu hỏi
                </span>
            </div>
            <div class="nhch-bulk-toolbar__actions">
                <button type="submit" name="action" value="publish" class="btn btn-sm btn-success fw-bold" onclick="return confirm('Công bố các câu hỏi đã chọn cho mọi giảng viên?')">
                    <i class="fas fa-globe-asia me-1"></i> Công bố đã chọn
                </button>
                <button type="submit" name="action" value="unpublish" class="btn btn-sm btn-outline-secondary fw-bold" onclick="return confirm('Thu hồi công bố các câu hỏi đã chọn?')">
                    <i class="fas fa-lock me-1"></i> Thu hồi
                </button>
                <button type="button" class="btn btn-sm btn-link text-decoration-none" id="nhch-bulk-clear">
                    Bỏ chọn
                </button>
            </div>
        </form>
    @endif

    {{-- List --}}
    @if($cauHois->isEmpty())
        <div class="nhch-empty">
            <i class="fas fa-folder-open"></i>
            <h5>{{ $tab === 'cua-toi' ? 'Bạn chưa có câu hỏi nào' : 'Chưa có câu hỏi đã công bố' }}</h5>
            <p>
                @if($tab === 'cua-toi')
                    Tạo bài kiểm tra mới và import câu hỏi để bắt đầu xây dựng ngân hàng riêng của bạn.
                @else
                    Khi admin công bố một câu hỏi cho mọi giảng viên, nó sẽ xuất hiện ở đây.
                @endif
            </p>
            <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="btn btn-primary fw-bold">
                <i class="fas fa-plus me-1"></i> Tạo bài kiểm tra (kèm câu hỏi mới)
            </a>
        </div>
    @else
        <div class="nhch-list">
            @foreach($cauHois as $cauHoi)
                @php
                    $isMine = (int) $cauHoi->nguoi_tao_id === (int) auth()->user()->id;
                    $loaiClass = $cauHoi->is_essay ? 'is-essay' : 'is-mcq';
                    $mucDoClass = match($cauHoi->muc_do) {
                        'de' => 'is-easy',
                        'kho' => 'is-hard',
                        default => 'is-medium',
                    };
                @endphp
                <div class="nhch-card {{ $tab === 'cua-toi' && $isMine ? 'has-checkbox' : '' }}">
                    @if($tab === 'cua-toi' && $isMine)
                        <label class="nhch-card__checkbox">
                            <input type="checkbox" class="nhch-card-check" form="nhch-bulk-form" name="ids[]" value="{{ $cauHoi->id }}">
                            <span></span>
                        </label>
                    @endif
                    <div class="nhch-card__head">
                        <div class="nhch-card__main">
                            <div class="nhch-card__meta">
                                <span class="nhch-pill nhch-pill--type {{ $loaiClass }}">
                                    <i class="fas fa-{{ $cauHoi->is_essay ? 'pen-fancy' : 'list-check' }}"></i>
                                    {{ $cauHoi->loai_cau_hoi_label }}
                                </span>
                                <span class="nhch-pill nhch-pill--difficulty {{ $mucDoClass }}">
                                    {{ $cauHoi->muc_do_label }}
                                </span>
                                @if($cauHoi->is_cong_bo)
                                    <span class="nhch-pill nhch-pill--public">
                                        <i class="fas fa-globe-asia"></i> Công bố
                                    </span>
                                @else
                                    <span class="nhch-pill nhch-pill--private">
                                        <i class="fas fa-lock"></i> Riêng tư
                                    </span>
                                @endif
                                @if($isMine)
                                    <span class="nhch-pill nhch-pill--mine">
                                        <i class="fas fa-user"></i> Của bạn
                                    </span>
                                @endif
                                <span class="nhch-pill nhch-pill--code">
                                    <i class="fas fa-hashtag"></i> {{ $cauHoi->ma_cau_hoi }}
                                </span>
                            </div>
                            <div class="nhch-card__content">{{ \Illuminate\Support\Str::limit(strip_tags($cauHoi->noi_dung), 240) }}</div>
                            <div class="nhch-card__sub">
                                <span><i class="fas fa-graduation-cap"></i> {{ $cauHoi->khoaHoc?->ten_khoa_hoc ?? 'Chưa gắn khoá' }}</span>
                                @if($cauHoi->moduleHoc)
                                    <span class="nhch-sep">·</span>
                                    <span><i class="fas fa-cube"></i> {{ $cauHoi->moduleHoc->ten_module }}</span>
                                @endif
                                <span class="nhch-sep">·</span>
                                <span><i class="far fa-user"></i> {{ $cauHoi->nguoiTao->ho_ten ?? 'N/A' }}</span>
                                @if($cauHoi->is_cong_bo && $cauHoi->cong_bo_luc)
                                    <span class="nhch-sep">·</span>
                                    <span><i class="fas fa-clock"></i> Công bố {{ $cauHoi->cong_bo_luc->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="nhch-card__actions">
                            <a href="{{ route('giang-vien.ngan-hang-cau-hoi.show', $cauHoi->id) }}" class="btn btn-sm btn-outline-primary fw-bold" title="Xem chi tiết + đáp án">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($isMine)
                                <form action="{{ route('giang-vien.ngan-hang-cau-hoi.toggle-public', $cauHoi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $cauHoi->is_cong_bo ? 'Thu hồi công bố? Câu hỏi trở về riêng tư.' : 'Công bố cho mọi giảng viên cùng dùng?' }}');">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm fw-bold {{ $cauHoi->is_cong_bo ? 'btn-success' : 'btn-outline-success' }}" title="{{ $cauHoi->is_cong_bo ? 'Thu hồi công bố' : 'Công bố cho mọi GV' }}">
                                        <i class="fas fa-{{ $cauHoi->is_cong_bo ? 'globe-asia' : 'share' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('giang-vien.ngan-hang-cau-hoi.destroy', $cauHoi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xoá câu hỏi này khỏi ngân hàng của bạn?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" title="Xoá khỏi ngân hàng">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($cauHois->hasPages())
            <div class="mt-3">{{ $cauHois->links('pagination::bootstrap-5') }}</div>
        @endif
    @endif
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .nhch-page { padding-bottom: 24px; }

    .nhch-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .nhch-tag-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 8px; }
    .nhch-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; background: #fff; color: #1d4ed8;
        font-size: 0.72rem; font-weight: 800; letter-spacing: 0.4px;
        border-radius: 999px; text-transform: uppercase;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }
    .nhch-status-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px; background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4); color: #fff;
        font-size: 0.74rem; font-weight: 700; border-radius: 999px;
    }
    .nhch-status-badge strong { color: #fef3c7; }

    /* Tabs */
    .nhch-tabs {
        display: flex;
        gap: 6px;
        margin: 14px 0;
        border-bottom: 2px solid #e2e8f0;
    }
    .nhch-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px;
        font-weight: 700; font-size: 0.88rem;
        color: #64748b; text-decoration: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.18s ease;
    }
    .nhch-tab:hover { color: #b91c1c; }
    .nhch-tab.is-active {
        color: #b91c1c;
        border-bottom-color: #dc2626;
    }
    .nhch-tab__count {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.74rem;
        padding: 2px 9px;
        border-radius: 999px;
        font-weight: 800;
    }
    .nhch-tab.is-active .nhch-tab__count {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* Filter */
    .nhch-filter {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 14px;
    }
    .nhch-filter__row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .nhch-filter__field {
        position: relative;
        flex: 1;
        min-width: 220px;
    }
    .nhch-filter__field i {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .nhch-filter__field input {
        padding-left: 32px !important;
    }

    /* List */
    .nhch-list { display: flex; flex-direction: column; gap: 10px; }

    .nhch-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        transition: box-shadow 0.18s ease, transform 0.18s ease;
    }
    .nhch-card:hover {
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        transform: translateY(-1px);
    }

    .nhch-card__head {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }
    .nhch-card__main { flex: 1; min-width: 0; }
    .nhch-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 8px;
    }

    .nhch-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px;
        font-size: 0.7rem; font-weight: 700;
        border-radius: 999px;
        letter-spacing: 0.2px;
    }
    .nhch-pill i { font-size: 0.62rem; }
    .nhch-pill--type.is-mcq { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    .nhch-pill--type.is-essay { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .nhch-pill--difficulty.is-easy { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .nhch-pill--difficulty.is-medium { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .nhch-pill--difficulty.is-hard { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .nhch-pill--public { background: #d1fae5; color: #065f46; border: 1px solid #10b981; font-weight: 800; }
    .nhch-pill--private { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .nhch-pill--mine { background: linear-gradient(135deg, #4361ee, #2f46c9); color: #fff; }
    .nhch-pill--code { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-family: monospace; }

    .nhch-card__content {
        font-size: 0.92rem;
        color: #1e293b;
        line-height: 1.55;
        margin-bottom: 8px;
    }

    .nhch-card__sub {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 0.78rem;
        color: #64748b;
    }
    .nhch-card__sub i { color: #94a3b8; margin-right: 3px; }
    .nhch-card__sub .nhch-sep { color: #cbd5e1; }

    .nhch-card__actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .nhch-empty {
        padding: 60px 24px;
        text-align: center;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        color: #64748b;
    }
    .nhch-empty i { font-size: 3rem; opacity: 0.3; margin-bottom: 14px; display: block; }
    .nhch-empty h5 { color: #1e293b; font-weight: 800; margin-bottom: 8px; }
    .nhch-empty p { max-width: 480px; margin: 0 auto 18px; }

    /* Checkbox bulk select */
    .nhch-card.has-checkbox {
        position: relative;
        padding-left: 48px;
    }
    .nhch-card__checkbox {
        position: absolute;
        left: 14px;
        top: 16px;
        cursor: pointer;
        margin: 0;
    }
    .nhch-card__checkbox input { display: none; }
    .nhch-card__checkbox span {
        display: block;
        width: 22px; height: 22px;
        background: #fff;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        position: relative;
        transition: all 0.18s ease;
    }
    .nhch-card__checkbox input:checked + span {
        background: #10b981;
        border-color: #10b981;
    }
    .nhch-card__checkbox input:checked + span::after {
        content: '✓';
        position: absolute;
        top: -3px; left: 3px;
        color: #fff;
        font-weight: 800;
        font-size: 1rem;
    }
    .nhch-card.has-checkbox:has(input:checked) {
        border-color: #10b981;
        background: #ecfdf5;
    }

    /* Bulk toolbar */
    .nhch-bulk-toolbar {
        position: sticky;
        top: 8px;
        z-index: 50;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(29, 78, 216, 0.32);
        color: #fff;
    }
    .nhch-bulk-toolbar.d-none { display: none !important; }
    .nhch-bulk-toolbar__main { flex: 1; min-width: 0; }
    .nhch-bulk-toolbar__count {
        font-size: 0.92rem;
        font-weight: 700;
    }
    .nhch-bulk-toolbar__count strong {
        background: #fff;
        color: #1d4ed8;
        padding: 2px 10px;
        border-radius: 999px;
        margin: 0 4px;
    }
    .nhch-bulk-toolbar__actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .nhch-bulk-toolbar__actions .btn-link {
        color: #fff;
        text-decoration: underline;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toolbar = document.getElementById('nhch-bulk-form');
    const counter = document.getElementById('nhch-bulk-count');
    const clearBtn = document.getElementById('nhch-bulk-clear');
    const checkboxes = document.querySelectorAll('.nhch-card-check');
    if (!toolbar || !counter) return;

    function refresh() {
        const checked = document.querySelectorAll('.nhch-card-check:checked');
        counter.textContent = checked.length;
        toolbar.classList.toggle('d-none', checked.length === 0);
    }

    checkboxes.forEach(cb => cb.addEventListener('change', refresh));
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            checkboxes.forEach(cb => cb.checked = false);
            refresh();
        });
    }
    refresh();
});
</script>
@endsection
