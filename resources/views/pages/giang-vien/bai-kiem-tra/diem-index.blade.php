@extends('layouts.app', ['title' => 'Bảng điểm bài kiểm tra'])

@section('content')
@php
    $examTypeMap = [
        'module'    => ['label' => 'Theo module',    'icon' => 'fa-layer-group'],
        'buoi_hoc'  => ['label' => 'Theo buổi học',  'icon' => 'fa-calendar-day'],
        'cuoi_khoa' => ['label' => 'Cuối khóa',      'icon' => 'fa-flag-checkered'],
    ];

    $gradingStatusMap = [
        'chua_cham' => ['label' => 'Chưa chấm', 'class' => 'secondary'],
        'cho_cham'  => ['label' => 'Chờ chấm',  'class' => 'warning'],
        'da_cham'   => ['label' => 'Đã chấm',   'class' => 'success'],
    ];

    $hasFilter = $filters['search'] !== ''
        || $filters['bai_kiem_tra_id']
        || $filters['loai_bai_kiem_tra']
        || $filters['trang_thai_cham'];
@endphp

<div class="container-fluid admin-page-x dkt-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome dkt-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-chart-column"></i></div>
        <div class="apx-welcome-text">
            <div class="dkt-tag-row">
                <span class="dkt-loai-badge">
                    <i class="fas fa-clipboard-check"></i> BẢNG ĐIỂM BÀI KIỂM TRA
                </span>
                <span class="dkt-status-badge">
                    <i class="fas fa-file-circle-check"></i>
                    {{ $stats['tong_luot_nop'] }} lượt nộp
                </span>
                @if($stats['cho_cham'] > 0)
                    <span class="dkt-pending-badge">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $stats['cho_cham'] }} chờ chấm
                    </span>
                @endif
            </div>
            <h4>Bảng điểm thông minh</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $stats['da_cham'] }} đã chấm</span>
                <span class="dkt-sep">·</span>
                <span><i class="fas fa-chart-line"></i> Điểm TB: {{ number_format((float) $stats['diem_trung_binh'], 2) }}</span>
                <span class="dkt-sep">·</span>
                <span><i class="fas fa-layer-group"></i> {{ $totalExamCards }} bài kiểm tra phù hợp</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="apx-view-toggle">
                <i class="fas fa-clipboard-list"></i> <span>Quản lý đề thi</span>
            </a>
            <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-light text-primary fw-bold shadow-sm dkt-create-btn">
                <i class="fas fa-marker me-1"></i> Chấm tự luận
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
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan kết quả</h2>
                    <p>Bốn chỉ số nhanh giúp bạn nắm tình trạng chấm bài và mức điểm trung bình.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $stats['tong_luot_nop'] }}</strong> lượt nộp</span>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-file-circle-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['tong_luot_nop'] }}</strong>
                        <small>Tổng lượt đã nộp</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['da_cham'] }}</strong>
                        <small>Đã chấm xong</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="aps-text">
                        <strong>{{ $stats['cho_cham'] }}</strong>
                        <small>Đang chờ chấm</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="aps-text">
                        <strong>{{ number_format((float) $stats['diem_trung_binh'], 2) }}</strong>
                        <small>Điểm trung bình</small>
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
                    <h2><i class="fas fa-filter"></i> Bộ lọc bảng điểm</h2>
                    <p>Tìm theo học viên, đề kiểm tra hoặc lọc theo loại đề / trạng thái chấm.</p>
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

        <div class="dkt-filter-card">
            <form method="GET" action="{{ route('giang-vien.diem-kiem-tra.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label class="dkt-flabel">Tìm học viên / đề</label>
                    <div class="dkt-input-icon">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="{{ $filters['search'] }}"
                               class="form-control" placeholder="Tên học viên, email, tên đề...">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="dkt-flabel">Bài kiểm tra</label>
                    <select name="bai_kiem_tra_id" class="form-select">
                        <option value="">Tất cả bài kiểm tra</option>
                        @foreach($examOptions as $exam)
                            <option value="{{ $exam->id }}" @selected((int) $filters['bai_kiem_tra_id'] === (int) $exam->id)>
                                {{ $exam->tieu_de }}{{ $exam->khoaHoc ? ' - ' . $exam->khoaHoc->ma_khoa_hoc : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="dkt-flabel">Loại kiểm tra</label>
                    <select name="loai_bai_kiem_tra" class="form-select">
                        <option value="">Tất cả loại</option>
                        @foreach($examTypeMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['loai_bai_kiem_tra'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="dkt-flabel">Trạng thái chấm</label>
                    <select name="trang_thai_cham" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($gradingStatusMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['trang_thai_cham'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold flex-fill">
                        <i class="fas fa-search me-1"></i> Lọc
                    </button>
                    <a href="{{ route('giang-vien.diem-kiem-tra.index') }}" class="btn btn-outline-secondary" title="Đặt lại">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </section>

    {{-- ========== ③ Bảng điểm theo khóa học và module ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div>
                    <h2><i class="fas fa-chart-bar"></i> Điểm theo khóa học & module</h2>
                    <p>Mỗi thẻ là một bài kiểm tra. Bấm "Danh sách" để xem chi tiết học viên trong cửa sổ riêng.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $totalExamCards }}</strong> đề phù hợp</span>
            </div>
        </header>

        @forelse($scoreboardCourses as $course)
            <section class="dkt-course-block">
                <div class="dkt-course-head">
                    <div class="dkt-course-info">
                        <div class="dkt-course-pill">
                            <i class="fas fa-graduation-cap"></i>
                            {{ $course['code'] }}
                        </div>
                        <h4 class="dkt-course-title">{{ $course['title'] }}</h4>
                        <div class="dkt-course-sub">Các bài kiểm tra đã có lượt nộp được chia theo từng module trong khóa.</div>
                    </div>
                    <div class="dkt-course-stats">
                        <div class="dkt-stat-chip">
                            <strong>{{ $course['exam_count'] }}</strong>
                            <small>Bài kiểm tra</small>
                        </div>
                        <div class="dkt-stat-chip">
                            <strong>{{ $course['student_count'] }}</strong>
                            <small>Học viên</small>
                        </div>
                        <div class="dkt-stat-chip">
                            <strong>{{ $course['attempt_count'] }}</strong>
                            <small>Lượt làm</small>
                        </div>
                    </div>
                </div>

                @foreach($course['modules'] as $moduleGroup)
                    <div class="dkt-module-block">
                        <div class="dkt-module-head">
                            <div class="dkt-module-info">
                                <div class="dkt-module-icon">
                                    <i class="fas {{ $moduleGroup['key'] === 'final' ? 'fa-flag-checkered' : 'fa-layer-group' }}"></i>
                                </div>
                                <div>
                                    <h5 class="dkt-module-title">{{ $moduleGroup['title'] }}</h5>
                                    <div class="dkt-module-sub">{{ $moduleGroup['subtitle'] }}</div>
                                </div>
                            </div>
                            <div class="dkt-module-tags">
                                <span class="dkt-tag-soft is-primary"><i class="fas fa-file-signature"></i> {{ $moduleGroup['exam_count'] }} đề</span>
                                <span class="dkt-tag-soft"><i class="fas fa-users"></i> {{ $moduleGroup['attempt_count'] }} lượt</span>
                            </div>
                        </div>

                        <div class="dkt-exam-grid">
                            @foreach($moduleGroup['exams'] as $card)
                                @php
                                    $exam = $card['exam'];
                                    $examType = $examTypeMap[$exam?->loai_bai_kiem_tra] ?? ['label' => 'Khác', 'icon' => 'fa-circle-question'];
                                    $studentModalId = 'scoreStudentsModal-' . $card['id'];

                                    $cardTone = 'default';
                                    if ($exam) {
                                        if ($exam->loai_noi_dung === 'trac_nghiem')      $cardTone = 'tn';
                                        elseif ($exam->loai_noi_dung === 'tu_luan')      $cardTone = 'tl';
                                    }

                                    $avg = $card['average_score'] !== null ? (float) $card['average_score'] : null;
                                    $avgClass = $avg === null ? 'is-muted' : ($avg >= 5 ? 'is-success' : 'is-danger');
                                @endphp

                                @continue(!$exam)

                                <article class="dkt-exam-card tone-{{ $cardTone }}">
                                    <div class="dkt-exam-card-top">
                                        <span class="dkt-type-pill">
                                            <i class="fas {{ $examType['icon'] }}"></i> {{ $examType['label'] }}
                                        </span>
                                        @if($exam->co_giam_sat)
                                            <span class="dkt-watch-pill" title="Có giám sát">
                                                <i class="fas fa-shield-halved"></i> Giám sát
                                            </span>
                                        @endif
                                    </div>

                                    <h6 class="dkt-exam-title">{{ $exam->tieu_de }}</h6>

                                    <div class="dkt-info-list">
                                        <div class="dkt-info-line">
                                            <i class="fas fa-book-open"></i>
                                            <span>{{ $exam->content_mode_label }} · {{ number_format((float) ($exam->tong_diem ?? 10), 2) }} điểm</span>
                                        </div>
                                        <div class="dkt-info-line">
                                            <i class="fas fa-location-dot"></i>
                                            <span>
                                                @if($exam->lichHoc)
                                                    Buổi {{ $exam->lichHoc->buoi_so }} · {{ optional($exam->lichHoc->ngay_hoc)->format('d/m/Y') }}
                                                @elseif($exam->moduleHoc)
                                                    {{ $exam->moduleHoc->ma_module }} · {{ $exam->moduleHoc->ten_module }}
                                                @else
                                                    Đề tổng kết toàn khóa
                                                @endif
                                            </span>
                                        </div>
                                        <div class="dkt-info-line">
                                            <i class="far fa-clock"></i>
                                            <span>Nộp gần nhất: {{ optional($card['last_submitted_at'])->format('d/m/Y H:i') ?? 'Chưa có' }}</span>
                                        </div>
                                    </div>

                                    <div class="dkt-mini-stats">
                                        <div class="dkt-mini-stat">
                                            <strong>{{ $card['student_count'] }}</strong>
                                            <small>Học viên</small>
                                        </div>
                                        <div class="dkt-mini-stat">
                                            <strong>{{ $card['attempt_count'] }}</strong>
                                            <small>Lượt làm</small>
                                        </div>
                                        <div class="dkt-mini-stat">
                                            <strong>{{ $card['graded_count'] }}</strong>
                                            <small>Đã chấm</small>
                                        </div>
                                        <div class="dkt-mini-stat dkt-avg {{ $avgClass }}">
                                            <strong>{{ $avg !== null ? number_format($avg, 2) : '—' }}</strong>
                                            <small>Điểm TB</small>
                                        </div>
                                    </div>

                                    <div class="dkt-card-actions">
                                        <button class="dkt-act-btn primary" type="button" data-bs-toggle="modal" data-bs-target="#{{ $studentModalId }}">
                                            <i class="fas fa-window-maximize"></i>
                                            <span>Danh sách</span>
                                        </button>
                                        <a href="{{ route('giang-vien.diem-kiem-tra.bao-cao', $exam->id) }}" class="dkt-act-btn success">
                                            <i class="fas fa-file-excel"></i>
                                            <span>Xuất Excel</span>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </section>
        @empty
            <div class="dkt-empty">
                <div class="dkt-empty-icon"><i class="fas fa-chart-simple"></i></div>
                <h5>Chưa có điểm bài kiểm tra phù hợp</h5>
                <p>Hãy đổi điều kiện lọc hoặc chờ học viên nộp bài để dữ liệu xuất hiện ở đây.</p>
                @if($hasFilter)
                    <a href="{{ route('giang-vien.diem-kiem-tra.index') }}" class="btn btn-outline-secondary fw-bold">
                        <i class="fas fa-rotate-left me-1"></i> Bỏ bộ lọc
                    </a>
                @endif
            </div>
        @endforelse
    </section>

    {{-- Modal stack giữ nguyên --}}
    <div class="score-modal-stack">
        @foreach($scoreboardCourses as $course)
            @foreach($course['modules'] as $moduleGroup)
                @foreach($moduleGroup['exams'] as $card)
                    @include('pages.giang-vien.bai-kiem-tra.partials.diem-student-modal', [
                        'card' => $card,
                        'gradingStatusMap' => $gradingStatusMap,
                    ])
                @endforeach
            @endforeach
        @endforeach
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .dkt-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .dkt-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .dkt-page .apx-section-title h2 i { color: #dc2626; }
    .dkt-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .dkt-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .dkt-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }

    .dkt-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .dkt-loai-badge {
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

    .dkt-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .dkt-status-badge i { font-size: 0.65rem; opacity: 0.85; }

    .dkt-pending-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        animation: dktPendingPulse 1.6s ease-out infinite;
    }
    @keyframes dktPendingPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }

    .apx-welcome.dkt-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.dkt-welcome p i { color: #fef3c7; margin-right: 4px; }
    .dkt-sep { opacity: 0.5; }

    .dkt-create-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* ===== Filter card ===== */
    .dkt-filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .dkt-flabel {
        display: block;
        font-weight: 800;
        color: #7f1d1d;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .dkt-input-icon { position: relative; }
    .dkt-input-icon i {
        position: absolute;
        left: 12px; top: 50%; transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }
    .dkt-input-icon input { padding-left: 34px; }

    /* ===== Course block ===== */
    .dkt-course-block {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .dkt-course-head {
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        padding: 18px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #fef2f2 100%);
        border-bottom: 1px solid #fecaca;
    }
    .dkt-course-info { flex: 1; min-width: 0; }
    .dkt-course-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px;
        background: #dc2626;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-radius: 999px;
        margin-bottom: 8px;
    }
    .dkt-course-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px;
    }
    .dkt-course-sub {
        font-size: 0.78rem;
        color: #64748b;
    }
    .dkt-course-stats {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .dkt-stat-chip {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 8px 14px;
        min-width: 96px;
    }
    .dkt-stat-chip strong {
        display: block;
        font-size: 1.15rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.1;
    }
    .dkt-stat-chip small {
        display: block;
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }

    /* ===== Module block ===== */
    .dkt-module-block {
        padding: 18px 20px;
    }
    .dkt-module-block + .dkt-module-block {
        border-top: 1px dashed #e2e8f0;
    }
    .dkt-module-head {
        display: flex;
        gap: 14px;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .dkt-module-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .dkt-module-icon {
        width: 40px; height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1d4ed8;
        display: grid; place-items: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .dkt-module-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 2px;
    }
    .dkt-module-sub {
        font-size: 0.75rem;
        color: #64748b;
    }
    .dkt-module-tags {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .dkt-tag-soft {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .dkt-tag-soft.is-primary { background: #eff6ff; color: #1d4ed8; }
    .dkt-tag-soft i { font-size: 0.66rem; opacity: 0.85; }

    /* ===== Exam grid ===== */
    .dkt-exam-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
    @media (max-width: 1599.98px) { .dkt-exam-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (max-width: 1199.98px) { .dkt-exam-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width:  991.98px) { .dkt-exam-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width:  575.98px) { .dkt-exam-grid { grid-template-columns: 1fr; } }

    .dkt-exam-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        transition: all 0.18s ease;
        min-height: 320px;
    }
    .dkt-exam-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 28px rgba(29, 78, 216, 0.12);
        border-color: #93c5fd;
    }
    .dkt-exam-card.tone-tn {
        background: linear-gradient(180deg, #fffdf0 0%, #fef9c3 100%);
        border-color: #fde68a;
    }
    .dkt-exam-card.tone-tn:hover { border-color: #f59e0b; }
    .dkt-exam-card.tone-tl {
        background: linear-gradient(180deg, #fff1f2 0%, #fee2e2 100%);
        border-color: #fecaca;
    }
    .dkt-exam-card.tone-tl:hover { border-color: #dc2626; }

    .dkt-exam-card-top {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
    }
    .dkt-type-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-radius: 999px;
    }
    .dkt-watch-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: #fef3c7;
        color: #b45309;
        font-size: 0.7rem;
        font-weight: 800;
        border-radius: 999px;
    }

    .dkt-exam-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin: 0;
        min-height: 38px;
    }

    .dkt-info-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .dkt-info-line {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 0.78rem;
        color: #475569;
        line-height: 1.4;
    }
    .dkt-info-line i {
        color: #1d4ed8;
        margin-top: 3px;
        width: 14px;
        flex-shrink: 0;
        font-size: 0.7rem;
    }

    .dkt-mini-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        margin-top: auto;
    }
    .dkt-mini-stat {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 8px;
    }
    .dkt-mini-stat strong {
        display: block;
        font-size: 0.95rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.1;
    }
    .dkt-mini-stat small {
        display: block;
        font-size: 0.66rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .dkt-mini-stat.dkt-avg.is-success strong { color: #16a34a; }
    .dkt-mini-stat.dkt-avg.is-danger  strong { color: #dc2626; }
    .dkt-mini-stat.dkt-avg.is-muted   strong { color: #94a3b8; }

    .dkt-card-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .dkt-act-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .dkt-act-btn.primary {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .dkt-act-btn.primary:hover {
        background: #1d4ed8;
        color: #fff;
        border-color: #1d4ed8;
        transform: translateY(-1px);
    }
    .dkt-act-btn.success {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }
    .dkt-act-btn.success:hover {
        background: #16a34a;
        color: #fff;
        border-color: #16a34a;
        transform: translateY(-1px);
    }

    /* ===== Empty state ===== */
    .dkt-empty {
        padding: 60px 30px;
        text-align: center;
        background: #fff;
        border: 1px dashed #fecaca;
        border-radius: 14px;
    }
    .dkt-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #dc2626;
        font-size: 2rem;
    }
    .dkt-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .dkt-empty p {
        font-size: 0.9rem; color: #94a3b8;
        max-width: 440px; margin: 0 auto 20px;
        line-height: 1.55;
    }

    /* Modal header (giữ partial cũ) */
    .score-modal-header {
        background:
            radial-gradient(circle at 10% 20%, rgba(125, 211, 252, 0.22), transparent 28%),
            linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 58%, #4361ee 100%);
        color: #fff;
    }
    .score-modal-header .btn-close {
        filter: invert(1) grayscale(100%);
        opacity: 0.85;
    }
    .score-modal-stat {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 16px;
        padding: 12px 14px;
        overflow: hidden;
    }
    .score-modal-stat .text-muted {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .score-modal-student-avatar {
        align-items: center;
        background: #e0f2fe;
        border-radius: 16px;
        color: #0369a1;
        display: inline-flex;
        flex: 0 0 42px;
        font-weight: 800;
        height: 42px;
        justify-content: center;
        width: 42px;
    }
    .score-modal-stack .score-students-modal:not(.show) {
        display: none !important;
    }
</style>
@endsection
