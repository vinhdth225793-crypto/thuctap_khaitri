@extends('layouts.app', ['title' => 'Bảng điểm bài kiểm tra'])

@push('styles')
<style>
    .scoreboard-page {
        --score-ink: #162033;
        --score-muted: #64748b;
        --score-line: rgba(15, 23, 42, 0.08);
        --score-blue: #2563eb;
        --score-teal: #0f766e;
        --score-amber: #d97706;
        color: var(--score-ink);
    }

    .score-hero {
        background:
            radial-gradient(circle at 12% 18%, rgba(56, 189, 248, 0.32), transparent 28%),
            radial-gradient(circle at 88% 10%, rgba(34, 197, 94, 0.20), transparent 24%),
            linear-gradient(135deg, #10233f 0%, #155e75 55%, #0f766e 100%);
        border-radius: 26px;
        color: #fff;
        overflow: hidden;
        padding: 28px;
        position: relative;
    }

    .score-hero::after {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        content: "";
        height: 170px;
        position: absolute;
        right: -42px;
        top: -55px;
        width: 170px;
    }

    .score-hero .text-muted {
        color: rgba(255, 255, 255, 0.72) !important;
    }

    .score-hero-actions {
        position: relative;
        z-index: 1;
    }

    .score-metric-card {
        background: #fff;
        border: 1px solid var(--score-line);
        border-radius: 20px;
        box-shadow: 0 16px 38px rgba(15, 23, 42, 0.07);
        height: 100%;
        overflow: hidden;
        position: relative;
    }

    .score-metric-card::before {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.16), rgba(20, 184, 166, 0.12));
        content: "";
        height: 100%;
        position: absolute;
        right: -38px;
        top: -36px;
        transform: rotate(12deg);
        width: 96px;
    }

    .score-filter-card,
    .course-score-section {
        border: 1px solid var(--score-line);
        border-radius: 22px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
    }

    .course-score-section {
        background: #fff;
        overflow: hidden;
    }

    .course-score-header {
        background: linear-gradient(135deg, #f8fafc, #eef7ff);
        border-bottom: 1px solid var(--score-line);
        padding: 22px 24px;
    }

    .course-code-pill {
        align-items: center;
        background: #0f172a;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 0.75rem;
        font-weight: 800;
        gap: 8px;
        letter-spacing: 0.03em;
        padding: 7px 12px;
        text-transform: uppercase;
    }

    .course-stat-chip {
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 14px;
        min-width: 112px;
        padding: 10px 12px;
        overflow: hidden;
    }

    .course-stat-chip .small {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .module-score-block {
        padding: 22px 24px 26px;
    }

    .module-score-block + .module-score-block {
        border-top: 1px dashed rgba(15, 23, 42, 0.13);
    }

    .module-marker {
        align-items: center;
        background: rgba(37, 99, 235, 0.10);
        border-radius: 16px;
        color: var(--score-blue);
        display: inline-flex;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .score-exam-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .score-exam-card {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid rgba(15, 23, 42, 0.09);
        border-radius: 19px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
        display: flex;
        flex-direction: column;
        min-height: 342px;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .score-exam-card--trac-nghiem {
        background: linear-gradient(180deg, #fffdf0 0%, #fff9e6 100%);
        border-color: rgba(217, 119, 6, 0.15);
    }

    .score-exam-card--tu-luan {
        background: linear-gradient(180deg, #fff9f9 0%, #fff0f0 100%);
        border-color: rgba(220, 38, 38, 0.15);
    }

    .score-exam-card--trac-nghiem:hover {
        border-color: rgba(217, 119, 6, 0.4);
    }

    .score-exam-card--tu-luan:hover {
        border-color: rgba(220, 38, 38, 0.4);
    }

    .score-exam-card:hover {
        border-color: rgba(37, 99, 235, 0.32);
        box-shadow: 0 22px 46px rgba(15, 23, 42, 0.13);
        transform: translateY(-3px);
    }

    .score-exam-card-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 16px;
    }

    .score-exam-title {
        font-size: 0.98rem;
        font-weight: 800;
        line-height: 1.35;
        min-height: 42px;
    }

    .score-info-line {
        align-items: flex-start;
        color: var(--score-muted);
        display: flex;
        font-size: 0.8rem;
        gap: 8px;
        line-height: 1.35;
    }

    .score-info-line i {
        color: var(--score-blue);
        margin-top: 2px;
        width: 14px;
    }

    .score-mini-stats {
        display: grid;
        gap: 8px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: auto;
    }

    .score-mini-stat {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.07);
        border-radius: 14px;
        padding: 9px 10px;
    }

    .score-mini-stat strong {
        display: block;
        font-size: 1.02rem;
        line-height: 1.1;
    }

    .score-mini-stat span {
        color: var(--score-muted);
        display: block;
        font-size: 0.72rem;
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .score-metric-card .card-body {
        padding: 20px;
    }

    .score-metric-card .text-muted {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .score-metric-card .display-6 {
        font-size: clamp(1.5rem, 5vw, 2.2rem);
        word-break: break-all;
    }

    .score-card-actions {
        border-top: 1px solid rgba(37, 99, 235, 0.12);
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }

    .score-list-link {
        align-items: center;
        background: #eef6ff;
        border: 0;
        color: #174ea6;
        display: flex;
        font-weight: 800;
        gap: 8px;
        justify-content: center;
        padding: 12px 16px;
        text-decoration: none;
        width: 100%;
    }

    .score-list-link:hover {
        background: #dff0ff;
        color: #0f3f8c;
    }

    .score-export-link {
        background: #ecfdf5;
        border-left: 1px solid rgba(4, 120, 87, 0.18);
        color: #047857;
    }

    .score-export-link:hover {
        background: #d1fae5;
        color: #065f46;
    }

    .score-modal-header {
        background:
            radial-gradient(circle at 10% 20%, rgba(125, 211, 252, 0.22), transparent 28%),
            linear-gradient(135deg, #10233f 0%, #1d4ed8 58%, #0f766e 100%);
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

    .score-empty-state {
        background:
            radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.12), transparent 32%),
            #fff;
        border: 1px dashed rgba(15, 23, 42, 0.18);
        border-radius: 22px;
        padding: 54px 24px;
    }

    @media (max-width: 1599.98px) {
        .score-exam-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 1199.98px) {
        .score-exam-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .score-exam-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .score-hero,
        .course-score-header,
        .module-score-block {
            padding: 18px;
        }

        .score-exam-grid {
            grid-template-columns: 1fr;
        }

        .score-card-actions {
            grid-template-columns: 1fr;
        }

        .score-export-link {
            border-left: 0;
            border-top: 1px solid rgba(4, 120, 87, 0.18);
        }
    }
</style>
@endpush

@section('content')
@php
    $examTypeMap = [
        'module' => ['label' => 'Theo module', 'class' => 'info', 'icon' => 'fa-layer-group'],
        'buoi_hoc' => ['label' => 'Theo buổi học', 'class' => 'primary', 'icon' => 'fa-calendar-day'],
        'cuoi_khoa' => ['label' => 'Cuối khóa', 'class' => 'dark', 'icon' => 'fa-flag-checkered'],
    ];

    $gradingStatusMap = [
        'chua_cham' => ['label' => 'Chưa chấm', 'class' => 'secondary'],
        'cho_cham' => ['label' => 'Chờ chấm', 'class' => 'warning'],
        'da_cham' => ['label' => 'Đã chấm', 'class' => 'success'],
    ];
@endphp

<div class="container-fluid scoreboard-page">
    <div class="score-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-8">
                <div class="course-code-pill bg-white text-dark mb-3">
                    <i class="fas fa-chart-column text-primary"></i>
                    Bảng điểm thông minh
                </div>
                <h2 class="fw-bold mb-2">Bảng điểm bài kiểm tra</h2>
                <p class="text-muted mb-0">
                    Điểm được gom theo từng khóa học và module. Mỗi thẻ là một bài kiểm tra, bấm nút danh sách để mở cửa sổ học viên ngay trong trang.
                </p>
            </div>
            <div class="col-xl-4">
                <div class="score-hero-actions d-flex gap-2 justify-content-xl-end flex-wrap">
                    <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="btn btn-light">
                        <i class="fas fa-clipboard-list me-1"></i> Quản lý đề thi
                    </a>
                    <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-user-edit me-1"></i> Chấm tự luận
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="score-metric-card">
                <div class="card-body position-relative">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Lượt đã nộp</div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="display-6 fw-bold mb-0 text-truncate">{{ $stats['tong_luot_nop'] }}</div>
                        <i class="fas fa-file-circle-check text-primary fs-3 flex-shrink-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="score-metric-card">
                <div class="card-body position-relative">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Đã chấm</div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="display-6 fw-bold mb-0 text-truncate">{{ $stats['da_cham'] }}</div>
                        <i class="fas fa-circle-check text-success fs-3 flex-shrink-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="score-metric-card">
                <div class="card-body position-relative">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Chờ chấm</div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="display-6 fw-bold mb-0 text-truncate">{{ $stats['cho_cham'] }}</div>
                        <i class="fas fa-hourglass-half text-warning fs-3 flex-shrink-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="score-metric-card">
                <div class="card-body position-relative">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Điểm trung bình</div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="display-6 fw-bold mb-0 text-truncate">{{ number_format((float) $stats['diem_trung_binh'], 2) }}</div>
                        <i class="fas fa-chart-line text-info fs-3 flex-shrink-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="score-filter-card card border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('giang-vien.diem-kiem-tra.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label fw-bold">Tìm học viên / đề</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Tên học viên, email, tên đề...">
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-bold">Bài kiểm tra</label>
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
                    <label class="form-label fw-bold">Loại kiểm tra</label>
                    <select name="loai_bai_kiem_tra" class="form-select">
                        <option value="">Tất cả loại</option>
                        @foreach($examTypeMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['loai_bai_kiem_tra'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fw-bold">Trạng thái chấm</label>
                    <select name="trang_thai_cham" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($gradingStatusMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['trang_thai_cham'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="{{ route('giang-vien.diem-kiem-tra.index') }}" class="btn btn-outline-secondary" title="Đặt lại">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Điểm theo khóa học và module</h4>
            <div class="text-muted small">Một hàng trên màn hình rộng hiển thị tối đa 5 thẻ bài kiểm tra.</div>
        </div>
        <div class="badge text-bg-light border px-3 py-2">
            {{ $totalExamCards }} bài kiểm tra phù hợp
        </div>
    </div>

    @forelse($scoreboardCourses as $course)
        <section class="course-score-section mb-4">
            <div class="course-score-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div class="course-code-pill mb-3">
                            <i class="fas fa-graduation-cap"></i>
                            {{ $course['code'] }}
                        </div>
                        <h4 class="fw-bold mb-1">{{ $course['title'] }}</h4>
                        <div class="text-muted small">Các bài kiểm tra đã có lượt nộp được chia theo từng module trong khóa.</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="course-stat-chip">
                            <div class="fw-bold fs-5">{{ $course['exam_count'] }}</div>
                            <div class="small text-muted">Bài kiểm tra</div>
                        </div>
                        <div class="course-stat-chip">
                            <div class="fw-bold fs-5">{{ $course['student_count'] }}</div>
                            <div class="small text-muted">Học viên</div>
                        </div>
                        <div class="course-stat-chip">
                            <div class="fw-bold fs-5">{{ $course['attempt_count'] }}</div>
                            <div class="small text-muted">Lượt làm</div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($course['modules'] as $moduleGroup)
                <div class="module-score-block">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="module-marker">
                                <i class="fas {{ $moduleGroup['key'] === 'final' ? 'fa-flag-checkered' : 'fa-layer-group' }}"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">{{ $moduleGroup['title'] }}</h5>
                                <div class="small text-muted">{{ $moduleGroup['subtitle'] }}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-light text-primary border px-3 py-2">{{ $moduleGroup['exam_count'] }} đề</span>
                            <span class="badge text-bg-light border px-3 py-2">{{ $moduleGroup['attempt_count'] }} lượt làm</span>
                        </div>
                    </div>

                    <div class="score-exam-grid">
                        @foreach($moduleGroup['exams'] as $card)
                            @php
                                $exam = $card['exam'];
                                $examType = $examTypeMap[$exam?->loai_bai_kiem_tra] ?? ['label' => 'Khác', 'class' => 'secondary', 'icon' => 'fa-circle-question'];
                                $studentModalId = 'scoreStudentsModal-' . $card['id'];
                                
                                $cardColorClass = '';
                                if ($exam) {
                                    if ($exam->loai_noi_dung === 'trac_nghiem') {
                                        $cardColorClass = 'score-exam-card--trac-nghiem';
                                    } elseif ($exam->loai_noi_dung === 'tu_luan') {
                                        $cardColorClass = 'score-exam-card--tu-luan';
                                    }
                                }
                            @endphp

                            @continue(!$exam)

                            <article class="score-exam-card {{ $cardColorClass }}">
                                <div class="score-exam-card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                        <span class="badge text-bg-{{ $examType['class'] }}">
                                            <i class="fas {{ $examType['icon'] }} me-1"></i>{{ $examType['label'] }}
                                        </span>
                                        @if($exam->co_giam_sat)
                                            <span class="badge text-bg-warning">
                                                <i class="fas fa-shield-halved me-1"></i>Giám sát
                                            </span>
                                        @endif
                                    </div>

                                    <div class="score-exam-title mb-3">{{ $exam->tieu_de }}</div>

                                    <div class="d-grid gap-2 mb-3">
                                        <div class="score-info-line">
                                            <i class="fas fa-book-open"></i>
                                            <span>{{ $exam->content_mode_label }} · {{ number_format((float) ($exam->tong_diem ?? 10), 2) }} điểm</span>
                                        </div>
                                        <div class="score-info-line">
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
                                        <div class="score-info-line">
                                            <i class="fas fa-clock"></i>
                                            <span>Nộp gần nhất: {{ optional($card['last_submitted_at'])->format('d/m/Y H:i') ?? 'Chưa có' }}</span>
                                        </div>
                                    </div>

                                    <div class="score-mini-stats">
                                        <div class="score-mini-stat">
                                            <strong>{{ $card['student_count'] }}</strong>
                                            <span>Học viên</span>
                                        </div>
                                        <div class="score-mini-stat">
                                            <strong>{{ $card['attempt_count'] }}</strong>
                                            <span>Lượt làm</span>
                                        </div>
                                        <div class="score-mini-stat">
                                            <strong>{{ $card['graded_count'] }}</strong>
                                            <span>Đã chấm</span>
                                        </div>
                                        <div class="score-mini-stat">
                                            <strong>{{ $card['average_score'] !== null ? number_format((float) $card['average_score'], 2) : '--' }}</strong>
                                            <span>Điểm TB</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="score-card-actions">
                                    <button class="score-list-link" type="button" data-bs-toggle="modal" data-bs-target="#{{ $studentModalId }}">
                                        <i class="fas fa-window-maximize"></i>
                                        <span>Danh sách</span>
                                    </button>
                                    <a href="{{ route('giang-vien.diem-kiem-tra.bao-cao', $exam->id) }}" class="score-list-link score-export-link">
                                        <i class="fas fa-file-excel"></i>
                                        <span>Xuất báo cáo</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>
    @empty
        <div class="score-empty-state text-center text-muted">
            <i class="fas fa-chart-simple fa-3x mb-3 d-block opacity-25"></i>
            <div class="fw-bold text-dark mb-2">Chưa có điểm bài kiểm tra phù hợp</div>
            <div class="small mb-0">Hãy đổi bộ lọc hoặc chờ học viên nộp bài kiểm tra.</div>
        </div>
    @endforelse

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
@endsection
