@extends('layouts.app')

@section('title', 'Ngân hàng câu hỏi')

@push('styles')
<style>
    :root {
        --qbank-primary: #4361ee;
        --qbank-secondary: #3f37c9;
        --qbank-accent: #4cc9f0;
        --qbank-bg-light: #f5f7fb;
        --qbank-card-shadow: 0 10px 25px rgba(67, 97, 238, 0.05);
        --qbank-border: rgba(67, 97, 238, 0.08);
    }

    .question-bank-page {
        background-color: var(--qbank-bg-light);
        min-height: 100vh;
        padding-bottom: 3rem;
    }

    /* Hero Section */
    .qbank-hero {
        background: linear-gradient(135deg, var(--qbank-primary), var(--qbank-secondary));
        border-radius: 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(20, 58, 82, 0.2);
    }

    .qbank-hero::before {
        content: "";
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(240, 180, 41, 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    .qbank-hero-kicker {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.8;
    }

    .qbank-top-action {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
    }

    .qbank-top-action:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        transform: translateY(-2px);
    }

    .qbank-view-toggle {
        background: rgba(0, 0, 0, 0.15);
        padding: 4px;
        border-radius: 12px;
    }

    .qbank-view-toggle .btn {
        border-radius: 10px;
        padding: 6px 16px;
        color: rgba(255, 255, 255, 0.8);
        border: none;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .qbank-view-toggle .btn.active {
        background: #fff;
        color: var(--qbank-primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Stats Cards */
    .qbank-stat-card {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        padding: 1.25rem;
        height: 100%;
        transition: transform 0.3s ease;
    }

    .qbank-stat-card:hover {
        background: rgba(255, 255, 255, 0.12);
    }

    .qbank-stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .qbank-stat-label {
        font-size: 0.8rem;
        font-weight: 600;
        opacity: 0.7;
        text-transform: uppercase;
    }

    /* Filter Card */
    .qbank-filter-card {
        border: none;
        border-radius: 20px;
        box-shadow: var(--qbank-card-shadow);
        background: #fff;
    }

    .form-label {
        font-weight: 600;
        color: var(--qbank-primary);
        font-size: 0.85rem;
    }

    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.6rem 1rem;
        border-color: #e2e8f0;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--qbank-secondary);
        box-shadow: 0 0 0 3px rgba(31, 111, 120, 0.1);
    }

    /* Summary Chips */
    .qbank-summary-chip {
        display: inline-flex;
        align-items: center;
        background: #fff;
        border: 1px solid var(--qbank-border);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
    }

    .qbank-summary-chip b {
        color: var(--qbank-primary);
        margin: 0 4px;
    }

    /* Question Cards (Detail View) */
    .question-item-card {
        border: 1px solid var(--qbank-border);
        border-radius: 20px;
        background: #fff;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .question-item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(20, 58, 82, 0.08);
        border-color: var(--qbank-secondary);
    }

    .qcard-header {
        padding: 1.25rem;
        border-bottom: 1px dashed var(--qbank-border);
        background: rgba(248, 250, 252, 0.5);
    }

    .qcard-body {
        padding: 1.5rem;
        flex-grow: 1;
    }

    .qcard-footer {
        padding: 1.25rem;
        background: #fcfcfd;
        border-top: 1px solid var(--qbank-border);
    }

    .qcard-code {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--qbank-secondary);
        background: rgba(31, 111, 120, 0.08);
        padding: 4px 10px;
        border-radius: 6px;
    }

    .qcard-content {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.6;
        margin-bottom: 1.25rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .answer-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 10px;
        margin-bottom: 1.5rem;
    }

    .answer-pill {
        display: flex;
        align-items: flex-start;
        padding: 10px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        font-size: 0.9rem;
        color: #475569;
        transition: all 0.2s;
    }

    .answer-pill.correct {
        background: #ecfdf5;
        border-color: #10b981;
        color: #065f46;
        font-weight: 600;
    }

    .answer-pill.correct .answer-letter {
        background: #10b981;
        color: #fff;
    }

    .answer-letter {
        width: 24px;
        height: 24px;
        min-width: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        border-radius: 6px;
        margin-right: 10px;
        font-weight: 700;
        font-size: 0.75rem;
    }

    /* Badge Styles */
    .badge-soft {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .badge-soft-primary { background: #e0f2fe; color: #0369a1; }
    .badge-soft-success { background: #dcfce7; color: #15803d; }
    .badge-soft-warning { background: #fef3c7; color: #92400e; }
    .badge-soft-danger { background: #fee2e2; color: #b91c1c; }
    .badge-soft-secondary { background: #f1f5f9; color: #475569; }

    /* Compact View Cards */
    .qbank-set-card {
        border: none;
        border-radius: 22px;
        box-shadow: var(--qbank-card-shadow);
        transition: all 0.3s ease;
        height: 100%;
        border: 1px solid transparent;
    }

    .qbank-set-card:hover {
        transform: translateY(-8px);
        border-color: var(--qbank-secondary);
    }

    .qbank-preview-item {
        background: #f8fafc;
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 8px;
        border: 1px solid #edf2f7;
    }

    .qbank-preview-text {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--qbank-primary);
    }

    /* Pagination Customization */
    .pagination {
        gap: 5px;
    }

    .page-item .page-link {
        border-radius: 8px;
        border: none;
        padding: 10px 16px;
        color: #475569;
        font-weight: 600;
    }

    .page-item.active .page-link {
        background-color: var(--qbank-secondary);
        box-shadow: 0 4px 10px rgba(31, 111, 120, 0.2);
    }

    @media (max-width: 768px) {
        .qbank-hero { padding: 1.5rem !important; }
        .answer-list { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $viewMode = $viewMode ?? 'compact';
    $compactUrl = route('admin.kiem-tra-online.cau-hoi.index', array_merge(request()->query(), ['view_mode' => 'compact']));
    $detailUrl = route('admin.kiem-tra-online.cau-hoi.index', array_merge(request()->query(), ['view_mode' => 'detail']));
    $questionBankSummaries = $questionBankSummaries ?? collect();
    $totalQuestionSets = $questionBankSummaries->count();
    $totalQuestions = $questionBankSummaries->sum('total_questions');
    $totalReadyQuestions = $questionBankSummaries->sum('ready_questions');
    $totalReusableQuestions = $questionBankSummaries->sum('reusable_questions');
    
    $activeFilterCount = collect([
        request('search'), request('khoa_hoc_id'), request('module_hoc_id'),
        request('loai_cau_hoi'), request('kieu_dap_an'), request('muc_do'),
        request('trang_thai'), request('co_the_tai_su_dung')
    ])->filter(fn ($value) => filled($value))->count();

    $selectedCourse = filled(request('khoa_hoc_id')) ? $khoaHocs->firstWhere('id', (int) request('khoa_hoc_id')) : null;
    $selectedModule = filled(request('module_hoc_id')) ? $modules->firstWhere('id', (int) request('module_hoc_id')) : null;
@endphp

<div class="container-fluid question-bank-page pt-4">
    <!-- Hero Section -->
    <div class="qbank-hero p-4 p-xl-5 mb-5">
        <div class="row g-4 align-items-center">
            <div class="col-xl-7">
                <div class="qbank-hero-kicker mb-2">Admin / Kiểm tra Online</div>
                <h1 class="display-6 fw-bold mb-3">Ngân hàng câu hỏi</h1>
                <p class="lead opacity-80 mb-4">
                    Quản lý tập trung, quét nhanh và tổ chức ngân hàng câu hỏi theo từng khóa học và module một cách chuyên nghiệp.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.create') }}" class="btn btn-light px-4 py-2 fw-bold">
                        <i class="fas fa-plus-circle me-2 text-primary"></i> Thêm câu hỏi
                    </a>
                    <button type="button" class="btn qbank-top-action px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-file-import me-2"></i> Import từ file
                    </button>
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.template') }}" class="btn qbank-top-action px-4 py-2 fw-bold">
                        <i class="fas fa-download me-2"></i> File mẫu
                    </a>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="qbank-stat-card">
                            <div class="qbank-stat-label">Tổng bộ</div>
                            <div class="qbank-stat-value">{{ $totalQuestionSets }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="qbank-stat-card">
                            <div class="qbank-stat-label">Tổng câu hỏi</div>
                            <div class="qbank-stat-value">{{ $totalQuestions }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="qbank-stat-card">
                            <div class="qbank-stat-label">Sẵn sàng</div>
                            <div class="qbank-stat-value text-success">{{ $totalReadyQuestions }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="qbank-stat-card">
                            <div class="qbank-stat-label">Tái sử dụng</div>
                            <div class="qbank-stat-value text-info">{{ $totalReusableQuestions }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Filters Section -->
    <div class="card qbank-filter-card mb-5">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fas fa-sliders-h me-2"></i> Bộ lọc & Tìm kiếm
                </h5>
                <div class="qbank-view-toggle d-flex">
                    <a href="{{ $compactUrl }}" class="btn {{ $viewMode === 'compact' ? 'active' : '' }}">
                        <i class="fas fa-th-large me-1"></i> Tổng quan
                    </a>
                    <a href="{{ $detailUrl }}" class="btn {{ $viewMode === 'detail' ? 'active' : '' }}">
                        <i class="fas fa-list me-1"></i> Chi tiết
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" method="GET" class="row g-3">
                <input type="hidden" name="view_mode" value="{{ $viewMode }}">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label">Từ khóa</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Mã, nội dung..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label">Khóa học</label>
                    <select name="khoa_hoc_id" id="filter-khoa-hoc" class="form-select">
                        <option value="">Tất cả khóa học</option>
                        @foreach($khoaHocs as $khoaHoc)
                            <option value="{{ $khoaHoc->id }}" @selected((string) request('khoa_hoc_id') === (string) $khoaHoc->id)>
                                {{ $khoaHoc->ten_khoa_hoc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label">Module</label>
                    <select name="module_hoc_id" id="filter-module-hoc" class="form-select">
                        <option value="">Tất cả module</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" data-course-id="{{ $module->khoa_hoc_id }}" @selected((string) request('module_hoc_id') === (string) $module->id)>
                                {{ $module->ten_module }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label">Loại & Kiểu</label>
                    <div class="d-flex gap-2">
                        <select name="loai_cau_hoi" class="form-select">
                            <option value="">Loại</option>
                            @foreach($questionTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('loai_cau_hoi') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="kieu_dap_an" class="form-select">
                            <option value="">Kiểu</option>
                            @foreach($answerModeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('kieu_dap_an') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-8 col-lg-12 d-flex justify-content-between align-items-end">
                    <div class="d-flex gap-3">
                        <div style="min-width: 150px;">
                            <label class="form-label">Mức độ</label>
                            <select name="muc_do" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach($difficultyOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('muc_do') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="min-width: 150px;">
                            <label class="form-label">Trạng thái</label>
                            <select name="trang_thai" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('trang_thai') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.kiem-tra-online.cau-hoi.index', ['view_mode' => $viewMode]) }}" class="btn btn-light border px-4">
                            Đặt lại
                        </a>
                        <button type="submit" class="btn btn-primary px-5 fw-bold">
                            Áp dụng bộ lọc
                        </button>
                    </div>
                </div>
            </form>

            @if($activeFilterCount > 0)
                <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2">
                    <span class="text-muted small align-self-center me-2">Đang lọc:</span>
                    @if(request('search')) <span class="qbank-summary-chip">Tìm: <b>{{ request('search') }}</b></span> @endif
                    @if($selectedCourse) <span class="qbank-summary-chip">Khóa: <b>{{ $selectedCourse->ten_khoa_hoc }}</b></span> @endif
                    @if($selectedModule) <span class="qbank-summary-chip">Module: <b>{{ $selectedModule->ten_module }}</b></span> @endif
                    @if(request('muc_do')) <span class="qbank-summary-chip">Mức: <b>{{ $difficultyOptions[request('muc_do')] }}</b></span> @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Content Area -->
    @if($viewMode === 'compact')
        <div class="row g-4">
            @forelse($questionBankSummaries as $summary)
                @php
                    $detailQuery = array_merge(request()->except('page', 'view_mode', 'khoa_hoc_id', 'module_hoc_id'), [
                        'view_mode' => 'detail',
                        'khoa_hoc_id' => $summary['khoa_hoc_id'],
                    ]);
                    if (!empty($summary['module_hoc_id'])) $detailQuery['module_hoc_id'] = $summary['module_hoc_id'];
                @endphp
                <div class="col-md-6 col-xxl-4">
                    <div class="card qbank-set-card border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-primary rounded-pill px-3">{{ $summary['total_questions'] }} câu</span>
                                <div class="text-end">
                                    <span class="qcard-code small">{{ $summary['khoa_hoc_ma'] }}</span>
                                </div>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $summary['group_label'] }}</h5>
                            <p class="text-muted small mb-4">{{ $summary['module_hoc_ten'] ?? 'Dùng chung toàn khóa' }}</p>

                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-2 text-center border">
                                        <div class="text-muted small fw-600 uppercase" style="font-size: 0.65rem;">Trắc nghiệm</div>
                                        <div class="fw-bold text-primary">{{ $summary['objective_questions'] }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-2 text-center border">
                                        <div class="text-muted small fw-600 uppercase" style="font-size: 0.65rem;">Tự luận</div>
                                        <div class="fw-bold text-primary">{{ $summary['essay_questions'] }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="qbank-preview-list mb-4">
                                @forelse($summary['preview_questions'] as $preview)
                                    <div class="qbank-preview-item">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-primary fw-bold small" style="font-size: 0.7rem;">{{ $preview['code'] }}</span>
                                            <span class="badge bg-{{ $preview['status_color'] }} opacity-75" style="font-size: 0.6rem;">{{ $preview['status_label'] }}</span>
                                        </div>
                                        <div class="qbank-preview-text text-truncate">{{ strip_tags($preview['content']) }}</div>
                                    </div>
                                @empty
                                    <div class="text-center py-3 text-muted italic small">Chưa có câu hỏi</div>
                                @endforelse
                                @if($summary['remaining_preview_count'] > 0)
                                    <div class="text-center small text-muted mt-2">và {{ $summary['remaining_preview_count'] }} câu hỏi khác...</div>
                                @endif
                            </div>

                            <a href="{{ route('admin.kiem-tra-online.cau-hoi.index', $detailQuery) }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                                Xem chi tiết bộ này <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3 opacity-20"></i>
                    <p class="text-muted">Không tìm thấy bộ ngân hàng nào phù hợp.</p>
                </div>
            @endforelse
        </div>
    @else
        <div class="row g-4">
            @forelse($cauHois as $index => $item)
                <div class="col-12 col-xl-6">
                    <div class="question-item-card">
                        <div class="qcard-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="qcard-code">{{ $item->ma_cau_hoi }}</span>
                                <span class="badge badge-soft badge-soft-primary">{{ $item->loai_cau_hoi_label }}</span>
                                <span class="badge badge-soft {{ $item->muc_do === 'kho' ? 'badge-soft-danger' : ($item->muc_do === 'de' ? 'badge-soft-success' : 'badge-soft-warning') }}">
                                    {{ $item->muc_do_label }}
                                </span>
                            </div>
                            <span class="badge bg-{{ $item->trang_thai_color }} rounded-pill px-3">{{ $item->trang_thai_label }}</span>
                        </div>
                        <div class="qcard-body">
                            <div class="qcard-content">
                                {!! Str::limit(strip_tags($item->noi_dung), 300) !!}
                            </div>
                            
                            @if($item->loai_cau_hoi === \App\Models\NganHangCauHoi::LOAI_TRAC_NGHIEM)
                                <div class="answer-list">
                                    @foreach($item->dapAns as $dIndex => $dapAn)
                                        <div class="answer-pill {{ $dapAn->is_dap_an_dung ? 'correct' : '' }}">
                                            <div class="answer-letter">{{ chr(65 + $dIndex) }}</div>
                                            <div class="answer-text">{{ $dapAn->noi_dung }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-light p-3 rounded-3 mb-4 font-italic text-muted small">
                                    <i class="fas fa-info-circle me-1"></i> Đây là câu hỏi tự luận, giảng viên sẽ chấm điểm thủ công.
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 text-muted small mt-auto">
                                <span title="Khóa học"><i class="fas fa-graduation-cap me-1"></i> {{ $item->khoaHoc->ten_khoa_hoc }}</span>
                                @if($item->moduleHoc)
                                    <span title="Module"><i class="fas fa-layer-group me-1"></i> {{ $item->moduleHoc->ten_module }}</span>
                                @endif
                                <span title="Người tạo"><i class="fas fa-user-edit me-1"></i> {{ $item->nguoiTao->ho_ten ?? 'N/A' }}</span>
                                <span title="Ngày tạo"><i class="fas fa-clock me-1"></i> {{ $item->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <div class="qcard-footer d-flex justify-content-between align-items-center">
                            <div>
                                @if($item->co_the_tai_su_dung)
                                    <span class="text-success fw-bold small"><i class="fas fa-recycle me-1"></i> Có thể tái sử dụng</span>
                                @else
                                    <span class="text-muted small"><i class="fas fa-lock me-1"></i> Dùng 1 lần</span>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.kiem-tra-online.cau-hoi.edit', $item->id) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                                    <i class="fas fa-edit me-1"></i> Sửa
                                </a>
                                <form action="{{ route('admin.kiem-tra-online.cau-hoi.toggle-status', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                        <i class="fas fa-eye{{ $item->trang_thai === 'san_sang' ? '-slash' : '' }} me-1"></i>
                                        {{ $item->trang_thai === 'san_sang' ? 'Ẩn' : 'Hiện' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.kiem-tra-online.cau-hoi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa câu hỏi này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Không tìm thấy câu hỏi nào phù hợp.</p>
                </div>
            @endforelse
        </div>

        @if($cauHois->hasPages())
            <div class="mt-5 d-flex justify-content-center">
                {{ $cauHois->links() }}
            </div>
        @endif
    @endif
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.kiem-tra-online.cau-hoi.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import câu hỏi từ tài liệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Chọn Khóa học <span class="text-danger">*</span></label>
                            <select name="khoa_hoc_id" id="import-khoa-hoc" class="form-select" required>
                                <option value="">--- Chọn khóa học ---</option>
                                @foreach($khoaHocs as $khoaHoc)
                                    <option value="{{ $khoaHoc->id }}">{{ $khoaHoc->ten_khoa_hoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chọn Module (tùy chọn)</label>
                            <select name="module_hoc_id" id="import-module-hoc" class="form-select">
                                <option value="">Dùng chung khóa học</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" data-course-id="{{ $module->khoa_hoc_id }}">
                                        {{ $module->ten_module }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 p-3 mb-4 small">
                        <div class="fw-bold mb-2 text-primary"><i class="fas fa-info-circle me-2"></i> Lưu ý định dạng file:</div>
                        <ul class="mb-0 ps-3">
                            <li><b>Word (.docx):</b> Hỗ trợ tốt nhất dạng câu hỏi có số thứ tự và đáp án A. B. C. D.</li>
                            <li><b>Excel (.xlsx):</b> Sử dụng file mẫu để đạt kết quả tốt nhất.</li>
                            <li><b>PDF:</b> Chỉ hỗ trợ PDF dạng text (không phải ảnh scan).</li>
                        </ul>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Chọn tệp tin</label>
                        <input type="file" name="file_import" class="form-control" accept=".docx,.pdf,.xlsx,.csv,.txt" required>
                        <div class="form-text mt-2 text-muted">Dung lượng tối đa 10MB. Hệ thống sẽ cho phép xem trước trước khi lưu chính thức.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold">
                        Tiếp tục phân tích <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const courseSelect = document.getElementById('filter-khoa-hoc');
        const moduleSelect = document.getElementById('filter-module-hoc');
        const importCourseSelect = document.getElementById('import-khoa-hoc');
        const importModuleSelect = document.getElementById('import-module-hoc');

        const syncModules = (selectedCourse, targetSelect) => {
            if (!targetSelect) return;
            Array.from(targetSelect.options).forEach((option) => {
                if (!option.value) { option.hidden = false; return; }
                option.hidden = selectedCourse !== '' && option.dataset.courseId !== selectedCourse;
            });
            const selectedOption = targetSelect.options[targetSelect.selectedIndex];
            if (selectedOption && selectedOption.hidden) targetSelect.value = '';
        };

        if (courseSelect && moduleSelect) {
            courseSelect.addEventListener('change', () => syncModules(courseSelect.value, moduleSelect));
            syncModules(courseSelect.value, moduleSelect);
        }

        if (importCourseSelect && importModuleSelect) {
            importCourseSelect.addEventListener('change', () => syncModules(importCourseSelect.value, importModuleSelect));
            syncModules(importCourseSelect.value, importModuleSelect);
        }
    });
</script>
@endpush
@endsection
