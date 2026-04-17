@extends('layouts.app', ['title' => 'Cấu hình bài kiểm tra'])

@section('content')
@php
    $selectedQuestionIds = $baiKiemTra->chiTietCauHois->pluck('ngan_hang_cau_hoi_id')->map(fn ($id) => (int) $id)->all();
    $scoreByQuestionId = $baiKiemTra->chiTietCauHois->mapWithKeys(fn ($item) => [$item->ngan_hang_cau_hoi_id => $item->diem_so]);
    $currentScoringMode = old('che_do_tinh_diem', $baiKiemTra->che_do_tinh_diem ?? 'thu_cong');
    $currentContentMode = old('che_do_noi_dung', $preferredContentMode ?? $baiKiemTra->content_mode_key);
    $essayPrompt = old('mo_ta', $baiKiemTra->mo_ta);
    $freeEssayScore = old('tong_diem_tu_luan_tu_do', $baiKiemTra->chiTietCauHois->isEmpty() ? ($baiKiemTra->tong_diem ?: 10) : 10);
    $questionFilters = $questionFilters ?? [];
    $selectableQuestionIds = $selectableQuestionIds ?? [];
    $importedQuestionIds = collect(session('exam_imported_question_ids', []))->map(fn ($id) => (int) $id)->all();
    $viewErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $activeExamTab = in_array($activeTab ?? null, ['info', 'scoring', 'import', 'questions'], true)
        ? $activeTab
        : 'info';
    if ($currentContentMode === 'tu_luan_tu_do' && in_array($activeExamTab, ['import', 'questions'], true)) {
        $activeExamTab = 'scoring';
    }
    $questionFilterIsActive = filled($questionFilters['search'] ?? null)
        || filled($questionFilters['module_hoc_id'] ?? null)
        || filled($questionFilters['loai_cau_hoi'] ?? null)
        || filled($questionFilters['muc_do'] ?? null)
        || filled($questionFilters['trang_thai'] ?? null);
@endphp

<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-primary"><i class="fas fa-edit me-2"></i>{{ $baiKiemTra->tieu_de }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc') }}">Khóa học</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc.show', $baiKiemTra->khoa_hoc_id) }}">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc }}</a></li>
                    <li class="breadcrumb-item active">Cấu hình bài kiểm tra</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giang-vien.khoa-hoc.show', $baiKiemTra->khoa_hoc_id) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
            @if($baiKiemTra->trang_thai_duyet === 'nhap' || $baiKiemTra->trang_thai_duyet === 'tu_choi')
                <button type="submit" form="mainExamForm" name="action_after_save" value="submit_for_approval" class="btn btn-success rounded-pill px-3 shadow-sm" onclick="return confirm('Lưu cấu hình hiện tại và gửi bài kiểm tra này để quản trị viên duyệt?')">
                    <i class="fas fa-paper-plane me-1"></i> Lưu & gửi duyệt
                </button>
            @endif
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($viewErrors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0 small fw-bold">
                @foreach($viewErrors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Hidden Filter Form -->
    <form id="questionFilterForm" method="GET" action="{{ route('giang-vien.bai-kiem-tra.edit', $baiKiemTra->id) }}" class="d-none">
        <input type="hidden" name="tab" value="questions">
    </form>

    <div class="row g-4">
        <!-- Sidebar Info -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                <div class="card-body">
                    <h5 class="fw-bold mb-4 border-bottom pb-2">Thông tin tóm tắt</h5>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Phạm vi</span>
                            <span class="badge bg-soft-info text-info rounded-pill px-3 fw-bold">{{ $baiKiemTra->pham_vi_label }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Trạng thái duyệt</span>
                            <span class="badge bg-{{ $baiKiemTra->trang_thai_duyet === 'da_duyet' ? 'success' : ($baiKiemTra->trang_thai_duyet === 'cho_duyet' ? 'warning' : 'secondary') }} rounded-pill px-3">
                                {{ $baiKiemTra->trang_thai_duyet_label }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Số câu hỏi</span>
                            <span class="fw-bold text-primary fs-5" id="summaryQuestionCount">{{ count($selectedQuestionIds) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Tổng điểm</span>
                            <span class="fw-bold text-success fs-5">{{ number_format((float) $baiKiemTra->tong_diem, 2) }}</span>
                        </div>
                        @if($baiKiemTra->ghi_chu_duyet)
                            <div class="alert alert-warning py-2 px-3 small mb-0 rounded-3 border-0">
                                <i class="fas fa-exclamation-triangle me-1"></i> <strong>Ghi chú Admin:</strong><br>{{ $baiKiemTra->ghi_chu_duyet }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Submissions -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Bài làm gần đây</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($baiKiemTra->baiLams as $baiLam)
                            <div class="list-group-item border-0 px-3 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-semibold small text-truncate" style="max-width: 140px;">{{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</span>
                                    <span class="badge bg-{{ $baiLam->diem_so !== null ? 'soft-success text-success' : 'soft-secondary text-secondary' }} px-2">
                                        {{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 1) : 'Chưa chấm' }}
                                    </span>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="far fa-clock me-1"></i>{{ $baiLam->nop_luc?->diffForHumans() ?? 'Đang làm' }}
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">
                                <i class="fas fa-history fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">Chưa có bài làm nào</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if($baiKiemTra->baiLams->isNotEmpty())
                    <div class="card-footer bg-white border-0 text-center pb-3 pt-0">
                        <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả bài làm <i class="fas fa-chevron-right ms-1 small"></i></a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-0 border-0">
                    <ul class="nav nav-tabs nav-justified border-0 modern-tabs" id="examTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeExamTab === 'info' ? 'active' : '' }} py-3 fw-bold" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-selected="{{ $activeExamTab === 'info' ? 'true' : 'false' }}">
                                <i class="fas fa-info-circle me-2"></i>Cấu hình chính
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeExamTab === 'scoring' ? 'active' : '' }} py-3 fw-bold" id="scoring-tab" data-bs-toggle="tab" data-bs-target="#scoring" type="button" role="tab" aria-selected="{{ $activeExamTab === 'scoring' ? 'true' : 'false' }}">
                                <i class="fas fa-calculator me-2"></i>Thiết lập điểm
                            </button>
                        </li>
                        <li class="nav-item @if($currentContentMode === 'tu_luan_tu_do') d-none @endif" role="presentation" data-question-flow-tab>
                            <button class="nav-link {{ $activeExamTab === 'import' ? 'active' : '' }} py-3 fw-bold" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button" role="tab" aria-selected="{{ $activeExamTab === 'import' ? 'true' : 'false' }}">
                                <i class="fas fa-file-import me-2"></i>Import file
                            </button>
                        </li>
                        <li class="nav-item @if($currentContentMode === 'tu_luan_tu_do') d-none @endif" role="presentation" data-question-flow-tab>
                            <button class="nav-link {{ $activeExamTab === 'questions' ? 'active' : '' }} py-3 fw-bold" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button" role="tab" aria-selected="{{ $activeExamTab === 'questions' ? 'true' : 'false' }}">
                                <i class="fas fa-list-check me-2"></i>Ngân hàng câu hỏi
                            </button>
                        </li>
                    </ul>
                </div>
                
                <form action="{{ route('giang-vien.bai-kiem-tra.update', $baiKiemTra->id) }}" method="POST" id="mainExamForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body p-4">
                        <div class="tab-content" id="examTabsContent">
                            <!-- Tab 1: Thông tin chung -->
                            <div class="tab-pane fade {{ $activeExamTab === 'info' ? 'show active' : '' }}" id="info" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">Tiêu đề bài kiểm tra</label>
                                        <input type="text" name="tieu_de" value="{{ old('tieu_de', $baiKiemTra->tieu_de) }}" class="form-control form-control-lg border-2 shadow-none focus-primary rounded-3" placeholder="VD: Kiểm tra giữa kỳ Module 1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Thời gian làm bài (phút)</label>
                                        <div class="input-group input-group-lg border-2 shadow-none overflow-hidden rounded-3">
                                            <input type="number" min="1" max="300" name="thoi_gian_lam_bai" value="{{ old('thoi_gian_lam_bai', $baiKiemTra->thoi_gian_lam_bai) }}" class="form-control" required>
                                            <span class="input-group-text bg-white border-start-0 text-muted">phút</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Ngày mở đề</label>
                                        <input type="datetime-local" name="ngay_mo" value="{{ old('ngay_mo', optional($baiKiemTra->ngay_mo)->format('Y-m-d\\TH:i')) }}" class="form-control shadow-none rounded-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Ngày đóng đề</label>
                                        <input type="datetime-local" name="ngay_dong" value="{{ old('ngay_dong', optional($baiKiemTra->ngay_dong)->format('Y-m-d\\TH:i')) }}" class="form-control shadow-none rounded-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Số lần được phép làm bài</label>
                                        <select name="so_lan_duoc_lam" class="form-select shadow-none rounded-3">
                                            @for($i=1; $i<=10; $i++)
                                                <option value="{{ $i }}" @selected(old('so_lan_duoc_lam', $baiKiemTra->so_lan_duoc_lam) == $i)>{{ $i }} lần</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="h-100 d-flex flex-column justify-content-end gap-2">
                                            <div class="form-check form-switch p-0 ps-5 ms-0 custom-switch">
                                                <input type="checkbox" name="randomize_questions" value="1" class="form-check-input ms-n5" id="randQuest" @checked(old('randomize_questions', $baiKiemTra->randomize_questions))>
                                                <label class="form-check-label fw-semibold" for="randQuest">Xáo trộn thứ tự câu hỏi</label>
                                            </div>
                                            <div class="form-check form-switch p-0 ps-5 ms-0 custom-switch">
                                                <input type="checkbox" name="randomize_answers" value="1" class="form-check-input ms-n5" id="randAns" @checked(old('randomize_answers', $baiKiemTra->randomize_answers))>
                                                <label class="form-check-label fw-semibold" for="randAns">Xáo trộn thứ tự đáp án</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
    <div class="border rounded-4 p-4 bg-light" data-exam-content-mode-panel>
        <div class="row g-3 align-items-center">
            <div class="col-lg-4">
                <label class="form-label fw-bold mb-1" for="contentModeSelect">Loại nội dung bài kiểm tra</label>
                <div class="small text-muted">Chọn rõ flow để hệ thống khóa đúng loại câu hỏi và cách chấm bài.</div>
            </div>
            <div class="col-lg-8">
                <select name="che_do_noi_dung" id="contentModeSelect" class="form-select shadow-none rounded-3" data-exam-content-mode-select>
                    <option value="trac_nghiem" @selected($currentContentMode === 'trac_nghiem')>Trắc nghiệm</option>
                    <option value="tu_luan_tu_do" @selected($currentContentMode === 'tu_luan_tu_do')>Tự luận tự do</option>
                    <option value="tu_luan_theo_cau" @selected($currentContentMode === 'tu_luan_theo_cau')>Tự luận theo câu</option>
                    <option value="hon_hop" @selected($currentContentMode === 'hon_hop')>Hỗn hợp</option>
                </select>
                <div class="small text-muted mt-2" id="contentModeDescription"></div>
            </div>
        </div>
    </div>
</div>
<div class="col-12">
    <label class="form-label fw-bold">Mô tả / Hướng dẫn học viên</label>
    <textarea name="mo_ta" id="essayPromptInfoInput" rows="5" class="form-control shadow-none rounded-3" placeholder="Nhập hướng dẫn làm bài..." data-essay-prompt-input>{{ $essayPrompt }}</textarea>
    <div class="form-text">Với bài tự luận tự do, phần này chính là đề bài/hướng dẫn hiển thị cho học viên.</div>
    @error('mo_ta')
        <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
    @enderror
</div>
                                </div>
                                
                                <div class="mt-5 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-outline-primary btn-lg rounded-pill px-4 fw-bold shadow-sm">
                                        <i class="fas fa-save me-2"></i> Lưu thông tin
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm" onclick="document.getElementById('scoring-tab').click()">Tiếp theo: Thiết lập điểm <i class="fas fa-chevron-right ms-2"></i></button>
                                </div>
                            </div>

                            <!-- Tab 2: Ngân hàng câu hỏi -->
                            <div class="tab-pane fade {{ $activeExamTab === 'questions' ? 'show active' : '' }}" id="questions" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-lg-9 border-end pe-lg-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
                                            <div>
                                                <h5 class="fw-bold mb-0">Ngân hàng câu hỏi</h5>
                                                <p class="text-muted small mb-0">Chỉ hiển thị các câu hỏi thuộc phạm vi và trạng thái sẵn sàng.</p>
                                                <div class="small mt-2 d-none" id="packageSelectionInfo">
                                                    <span class="badge bg-soft-warning text-warning rounded-pill px-3 py-2 fw-bold" id="packageSelectionCounter">0/0 c&#226;u</span>
                                                    <span class="text-muted ms-2" id="packageSelectionRemaining">C&#242;n 0 c&#226;u c&#7847;n ch&#7885;n.</span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-light btn-sm border" id="btnToggleFilters">
                                                    <i class="fas fa-filter me-1 text-primary"></i> Bộ lọc
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                                        Chọn nhanh
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4">
                                                        <li><a class="dropdown-item select-by-level py-2" href="#" data-level="de"><i class="fas fa-laugh text-success me-2"></i>Chọn tất cả câu Dễ</a></li>
                                                        <li><a class="dropdown-item select-by-level py-2" href="#" data-level="trung_binh"><i class="fas fa-smile text-warning me-2"></i>Chọn tất cả câu Trung bình</a></li>
                                                        <li><a class="dropdown-item select-by-level py-2" href="#" data-level="kho"><i class="fas fa-angry text-danger me-2"></i>Chọn tất cả câu Khó</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item py-2" href="#" id="selectAllCurrentView"><i class="fas fa-check-double text-primary me-2"></i>Chọn tất cả trong trang này</a></li>
                                                        <li><a class="dropdown-item text-danger py-2" href="#" id="deselectAllCurrentView"><i class="fas fa-times-circle me-2"></i>Bỏ chọn tất cả</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card bg-light border-0 shadow-none mb-4 {{ $questionFilterIsActive ? '' : 'd-none' }}" id="filterSection">
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-muted text-uppercase">TÌM KIẾM</label>
                                                        <input type="text" name="question_search" value="{{ $questionFilters['search'] ?? '' }}" class="form-control form-control-sm shadow-none rounded-pill px-3" placeholder="Mã hoặc nội dung..." form="questionFilterForm">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted text-uppercase">LOẠI</label>
                                                        <select name="question_loai_cau_hoi" class="form-select form-select-sm shadow-none rounded-pill px-3" form="questionFilterForm">
                                                            <option value="">Tất cả loại</option>
                                                            @foreach($questionTypeOptions as $value => $label)
                                                                <option value="{{ $value }}" @selected(($questionFilters['loai_cau_hoi'] ?? null) === $value)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted text-uppercase">MỨC ĐỘ</label>
                                                        <select name="question_muc_do" class="form-select form-select-sm shadow-none rounded-pill px-3" form="questionFilterForm">
                                                            <option value="">Tất cả mức độ</option>
                                                            @foreach($difficultyOptions as $value => $label)
                                                                <option value="{{ $value }}" @selected(($questionFilters['muc_do'] ?? null) === $value)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 d-flex align-items-end gap-2">
                                                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1 rounded-pill" form="questionFilterForm">Lọc</button>
                                                        <a href="{{ route('giang-vien.bai-kiem-tra.edit', ['id' => $baiKiemTra->id, 'tab' => 'questions']) }}" class="btn btn-outline-secondary btn-sm rounded-circle" title="Xóa lọc"><i class="fas fa-undo"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="q-bank-container">
                                            <div class="question-grid" id="questionGrid">
                                                @forelse($availableQuestions as $question)
                                                    @php
                                                        $questionId = (int) $question->id;
                                                        $isSelected = in_array($questionId, old('question_ids', $selectedQuestionIds), true);
                                                        $isSelectable = in_array($questionId, $selectableQuestionIds, true);
                                                        $isImported = in_array($questionId, $importedQuestionIds, true);
                                                        $contentStrip = strip_tags($question->noi_dung);
                                                        // Get answers for JS preview
                                                        $ansData = $question->dapAns->map(fn($a) => ['id' => $a->id, 'text' => strip_tags($a->noi_dung), 'correct' => $a->is_dap_an_dung])->all();
                                                    @endphp
                                                    <div class="question-card-wrapper" data-level="{{ $question->muc_do }}" data-type="{{ $question->loai_cau_hoi }}">
                                                        <div class="question-card @if($isSelected) selected @endif @if($isImported) border-success @endif" 
                                                            data-id="{{ $questionId }}" 
                                                            data-answers="{{ json_encode($ansData) }}">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <span class="badge bg-light text-dark border small fw-bold">{{ $question->ma_cau_hoi }}</span>
                                                                    <span class="badge bg-soft-{{ $question->muc_do === 'de' ? 'success' : ($question->muc_do === 'trung_binh' ? 'warning' : 'danger') }} text-{{ $question->muc_do === 'de' ? 'success' : ($question->muc_do === 'trung_binh' ? 'warning' : 'danger') }} small px-2 rounded-pill">
                                                                        {{ $question->muc_do_label }}
                                                                    </span>
                                                                </div>
                                                                <div class="question-content-preview mb-3" title="{{ $contentStrip }}">
                                                                    {{ $contentStrip }}
                                                                </div>
                                                                <div class="d-flex gap-1 flex-wrap">
                                                                    <span class="badge badge-outline text-info small">{{ $question->loai_cau_hoi_label }}</span>
                                                                    @if($question->moduleHoc)
                                                                        <span class="badge badge-outline text-secondary small">{{ $question->moduleHoc->ma_module }}</span>
                                                                    @endif
                                                                    @if($isImported)
                                                                        <span class="badge bg-success small fw-bold">Vừa Import</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="card-footer d-flex justify-content-between align-items-center">
                                                                <button type="button" class="btn btn-sm btn-link text-info p-0 fw-bold text-decoration-none btn-view-detail" data-id="{{ $questionId }}">
                                                                    <i class="fas fa-eye me-1"></i>Xem chi tiết
                                                                </button>
                                                                <div class="form-check form-switch mb-0">
                                                                    <input type="checkbox" class="form-check-input question-checkbox" 
                                                                        name="question_ids[]" 
                                                                        value="{{ $questionId }}" 
                                                                        id="q_cb_{{ $questionId }}"
                                                                        data-id="{{ $questionId }}"
                                                                        data-code="{{ $question->ma_cau_hoi }}"
                                                                        data-content="{{ $contentStrip }}"
                                                                        data-score="{{ $question->diem_mac_dinh }}"
                                                                        @checked($isSelected) 
                                                                        @disabled(!$isSelectable && !$isSelected)>
                                                                    <label class="form-check-label small fw-bold cursor-pointer" for="q_cb_{{ $questionId }}">Chọn</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center py-5 text-muted w-100 grid-column-full bg-light rounded-4">
                                                        <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                                                        <p class="mb-0">Không tìm thấy câu hỏi phù hợp với bộ lọc hiện tại.</p>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Sidebar (Selected List) -->
                                    <div class="col-lg-3">
                                        <div class="sticky-sidebar">
                                            <div class="card border-0 shadow-sm overflow-hidden rounded-4">
                                                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                                                    <h6 class="fw-bold mb-0"><i class="fas fa-check-square me-2"></i>Đã chọn</h6>
                                                    <span class="badge bg-white text-primary rounded-pill fw-bold" id="selectedCountBadge">0</span>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="selection-sidebar-list" id="selectionSidebarList">
                                                        <!-- Dynamic List via JS -->
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-light border-0 py-3">
                                                    <div class="small d-none mb-3 p-3 rounded-3 bg-white border" id="sidebarSelectionHint"></div>
                                                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                        <span class="text-muted small fw-bold">Tổng điểm dự kiến:</span>
                                                        <span class="fw-bold text-primary fs-5" id="sidebarTotalScore">0.00</span>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" class="btn btn-primary fw-bold rounded-pill shadow-sm">
                                                            <i class="fas fa-save me-2"></i>Lưu cấu hình đề
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm border-0 rounded-pill" id="btnSidebarClear">
                                                            <i class="fas fa-trash-alt me-1"></i>Bỏ chọn tất cả
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-soft-primary mt-3 p-3 small rounded-4 border-0">
                                                <i class="fas fa-lightbulb me-2 text-primary"></i>
                                                <strong>M&#7865;o:</strong> Khi d&#249;ng <strong>G&#243;i &#273;i&#7875;m t&#7921; &#273;&#7897;ng</strong>, menu <strong>Ch&#7885;n nhanh</strong> s&#7869; l&#7845;y ng&#7851;u nhi&#234;n &#273;&#250;ng s&#7889; c&#226;u c&#242;n thi&#7871;u theo nh&#243;m b&#7841;n ch&#7885;n.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-5 d-flex justify-content-between border-top pt-4">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="document.getElementById('import-tab').click()"><i class="fas fa-chevron-left me-2"></i> Quay lại: Import</button>
                                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="document.getElementById('scoring-tab').click()">Tiếp theo: Thiết lập điểm <i class="fas fa-chevron-right ms-2"></i></button>
                                </div>
                            </div>

                            <!-- Tab 3: Import -->
                            <div class="tab-pane fade {{ $activeExamTab === 'import' ? 'show active' : '' }}" id="import" role="tabpanel">
                                <div class="text-center py-5">
                                    <div class="icon-circle bg-soft-success text-success mx-auto mb-4" style="width: 100px; height: 100px; font-size: 3rem;">
                                        <i class="fas fa-file-excel"></i>
                                    </div>
                                    <h4 class="fw-bold">Import câu hỏi từ file</h4>
                                    <p class="text-muted mb-5 max-w-500 mx-auto">Tải lên file câu hỏi để đưa vào ngân hàng. Sau khi import thành công, bạn có thể chọn chúng trong tab <strong>Ngân hàng câu hỏi</strong>.</p>
                                    
                                    <div class="max-w-500 mx-auto">
                                        <div class="border-dashed rounded-4 p-5 mb-4 bg-light cursor-pointer hover-bg-white transition-all" onclick="document.getElementById('importFile').click()" id="dropZone">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <p class="mb-0 fw-bold text-dark" id="fileNameDisplay">Kéo thả file vào đây hoặc nhấp để chọn</p>
                                            <span class="text-muted small">Hỗ trợ .xlsx, .docx, .pdf, .csv, .txt</span>
                                            <input type="file" id="importFile" class="d-none" accept=".xlsx,.docx,.pdf,.csv,.txt">
                                        </div>
                                        
                                        <div id="importPreviewArea" class="d-none mb-4 fade-in">
                                            <div class="alert alert-info text-start py-3 px-3 mb-3 border-0 shadow-sm rounded-4">
                                                <div id="importSummaryText" class="fw-bold text-primary"></div>
                                                <div id="importSummaryMeta" class="small text-muted mt-2"></div>
                                            </div>
                                            <div class="d-grid">
                                                <button type="button" class="btn btn-success btn-lg rounded-pill shadow-sm fw-bold" id="btnConfirmImport">
                                                    <i class="fas fa-check-circle me-2"></i>Xem preview & Xác nhận
                                                </button>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-center gap-3">
                                            <a href="{{ route('giang-vien.bai-kiem-tra.import-template') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                                                <i class="fas fa-download me-1"></i> Tải file mẫu .xlsx
                                            </a>
                                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm" id="btnUploadImport">
                                                <i class="fas fa-upload me-1"></i> Bắt đầu xử lý file
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 border-top pt-4">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Lưu ý quan trọng khi import:</h6>
                                    <div class="row text-muted small">
                                        <div class="col-md-6">
                                            <ul class="ps-3 mb-0">
                                                <li class="mb-2"><strong>Định dạng trắc nghiệm:</strong> Đáp án đúng nên được bôi đậm trong file DOCX hoặc đánh dấu X trong file Excel.</li>
                                                <li class="mb-2"><strong>Tự luận:</strong> Hệ thống sẽ nhận diện là tự luận nếu không có các lựa chọn đáp án đi kèm.</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="ps-3 mb-0">
                                                <li class="mb-2"><strong>Trùng lặp:</strong> Hệ thống sẽ cảnh báo nếu nội dung câu hỏi bị trùng với các câu đã có trong kho.</li>
                                                <li class="mb-2"><strong>Phạm vi:</strong> Câu hỏi import sẽ tự động gán vào Module/Khóa học hiện tại của bài kiểm tra.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 d-flex justify-content-between border-top pt-4">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="document.getElementById('scoring-tab').click()"><i class="fas fa-chevron-left me-2"></i> Quay lại: Thiết lập điểm</button>
                                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="document.getElementById('questions-tab').click()">Tiếp theo: Ngân hàng câu hỏi <i class="fas fa-chevron-right ms-2"></i></button>
                                </div>
                            </div>

                            <!-- Tab 4: Thiết lập điểm -->
                            <div class="tab-pane fade {{ $activeExamTab === 'scoring' ? 'show active' : '' }}" id="scoring" role="tabpanel">
                                <div id="structuredScoringArea" class="@if($currentContentMode === 'tu_luan_tu_do') d-none @endif">
                                <div class="mb-5">
                                    <h5 class="fw-bold mb-4">Chọn chế độ tính điểm</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="scoring-mode-option p-4 border-2 rounded-4 cursor-pointer @if($currentScoringMode === 'thu_cong') active @endif" data-mode="thu_cong">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="icon-circle bg-soft-primary text-primary">
                                                        <i class="fas fa-keyboard"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1">Thiết lập thủ công</h6>
                                                        <p class="text-muted small mb-0">Tự nhập điểm số cụ thể cho từng câu hỏi.</p>
                                                    </div>
                                                    <div class="ms-2">
                                                        <div class="form-check m-0 p-0">
                                                            <input type="radio" name="che_do_tinh_diem" value="thu_cong" class="form-check-input ms-0" @checked($currentScoringMode === 'thu_cong')>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="scoring-mode-option p-4 border-2 rounded-4 cursor-pointer @if($currentScoringMode === 'goi_diem') active @endif" data-mode="goi_diem">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="icon-circle bg-soft-success text-success">
                                                        <i class="fas fa-magic"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1">Gói điểm tự động</h6>
                                                        <p class="text-muted small mb-0">Chia đều tổng điểm cho số lượng câu đã chọn.</p>
                                                    </div>
                                                    <div class="ms-2">
                                                        <div class="form-check m-0 p-0">
                                                            <input type="radio" name="che_do_tinh_diem" value="goi_diem" class="form-check-input ms-0" @checked($currentScoringMode === 'goi_diem')>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gói điểm Area -->
                                <div id="packageScoringArea" class="mb-5 @if($currentScoringMode !== 'goi_diem') d-none @endif fade-in">
                                    <div class="bg-light rounded-4 p-4 mb-4 border border-dashed">
                                        <div class="row g-4 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Tổng điểm đích</label>
                                                <input type="number" step="0.25" min="1" name="tong_diem_goi_diem" value="{{ old('tong_diem_goi_diem', $baiKiemTra->tong_diem) }}" class="form-control form-control-lg shadow-none rounded-3 border-2">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Số câu muốn lấy từ kho</label>
                                                <input type="number" min="1" name="so_cau_goi_diem" value="{{ old('so_cau_goi_diem', $baiKiemTra->so_cau_goi_diem ?? count($selectedQuestionIds)) }}" class="form-control form-control-lg shadow-none rounded-3 border-2">
                                            </div>
                                            <div class="col-md-4">
                                                <div class="alert alert-soft-warning py-3 mb-0 rounded-4 border-0">
                                                    <i class="fas fa-lightbulb me-2"></i> Mỗi câu sẽ có: <strong id="previewPackageScore" class="fs-5 text-primary">?</strong> điểm.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <h6 class="fw-bold mb-3 small text-muted text-uppercase">Gợi ý nhanh:</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 apply-preset" data-total="10" data-count="10">10đ / 10 câu</button>
                                                <button type="button" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 apply-preset" data-total="10" data-count="20">10đ / 20 câu</button>
                                                <button type="button" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 apply-preset" data-total="30" data-count="30">30đ / 30 câu</button>
                                                <button type="button" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 apply-preset" data-total="100" data-count="50">100đ / 50 câu</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Thủ công Area -->
                                <div id="manualScoringArea" class="mb-5 @if($currentScoringMode !== 'thu_cong') d-none @endif fade-in">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0">Cấu hình điểm từng câu</h5>
                                        <span class="text-muted small">Cập nhật điểm theo yêu cầu cụ thể của từng câu hỏi.</span>
                                    </div>
                                    <div id="selectedQuestionsSummary" class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="ps-4" style="width: 80px;">STT</th>
                                                        <th>Nội dung câu hỏi</th>
                                                        <th class="pe-4" style="width: 180px;">Điểm số</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="selectedQuestionsList">
                                                    @foreach($baiKiemTra->chiTietCauHois as $index => $item)
                                                        <tr data-id="{{ $item->ngan_hang_cau_hoi_id }}">
                                                            <td class="fw-bold text-center ps-4">{{ $index + 1 }}</td>
                                                            <td>
                                                                <div class="small fw-bold text-primary">{{ $item->cauHoi->ma_cau_hoi }}</div>
                                                                <div class="text-muted small text-truncate" style="max-width: 500px;">{{ strip_tags($item->cauHoi->noi_dung) }}</div>
                                                            </td>
                                                            <td class="pe-4">
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" step="0.25" min="0.25" name="question_scores[{{ $item->ngan_hang_cau_hoi_id }}]" value="{{ $item->diem_so }}" class="form-control shadow-none rounded-start border-2 q-score-input">
                                                                    <span class="input-group-text bg-white border-2">đ</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-light fw-bold border-top-2">
                                                    <tr>
                                                        <td colspan="2" class="text-end ps-4">TỔNG ĐIỂM TOÀN ĐỀ:</td>
                                                        <td class="text-primary fs-4 pe-4" id="realtimeTotalScore">{{ number_format((float) $baiKiemTra->tong_diem, 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                <div id="freeEssayScoringArea" class="@if($currentContentMode !== 'tu_luan_tu_do') d-none @endif fade-in">
                                    <div class="border rounded-4 p-4 p-lg-5 bg-light mb-4">
                                        <div class="row g-4">
                                            <div class="col-lg-8">
                                                <div class="d-flex align-items-center gap-3 mb-3">
                                                    <div class="icon-circle bg-soft-info text-info">
                                                        <i class="fas fa-pen-nib"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="fw-bold mb-1">Đề kiểm tra tự luận</h5>
                                                        <p class="text-muted small mb-0">Nhập đề trực tiếp tại đây. Học viên sẽ thấy nội dung này ngay trước ô làm bài.</p>
                                                    </div>
                                                </div>

                                                <label class="form-label fw-bold" for="essayPromptScoringInput">Đề bài / yêu cầu làm bài</label>
                                                <textarea id="essayPromptScoringInput" rows="9" class="form-control shadow-none rounded-3 free-essay-prompt-textarea" placeholder="Ví dụ: Phân tích tình huống, trình bày luận điểm chính và nêu kết luận của bạn..." data-essay-prompt-input>{{ $essayPrompt }}</textarea>
                                                <div class="form-text">Ô này được đồng bộ với phần “Mô tả / Hướng dẫn học viên” ở tab đầu tiên.</div>
                                                @error('mo_ta')
                                                    <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="bg-white border rounded-4 p-4 h-100">
                                                    <label class="form-label fw-bold" for="freeEssayTotalScore">Tổng điểm bài tự luận</label>
                                                    <div class="input-group input-group-lg mb-2">
                                                        <input type="number" step="0.25" min="0.25" max="1000" name="tong_diem_tu_luan_tu_do" id="freeEssayTotalScore" value="{{ $freeEssayScore }}" class="form-control shadow-none rounded-start border-2 fw-bold">
                                                        <span class="input-group-text bg-white border-2">đ</span>
                                                    </div>
                                                    <div class="small text-muted">Điểm này dùng để chấm bài viết tổng của học viên.</div>
                                                    @error('tong_diem_tu_luan_tu_do')
                                                        <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                                                    @enderror

                                                    <hr class="my-4">

                                                    <h6 class="fw-bold mb-2">Muốn import câu hỏi bên ngoài?</h6>
                                                    <p class="text-muted small mb-3">Nếu đề gồm nhiều câu tự luận riêng lẻ, import file vào ngân hàng rồi chọn câu cho bài kiểm tra.</p>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-outline-primary rounded-pill fw-bold" id="btnUseImportedEssayQuestions">
                                                            <i class="fas fa-file-import me-2"></i>Import câu hỏi từ file
                                                        </button>
                                                        <button type="button" class="btn btn-light border rounded-pill fw-bold" id="btnOpenQuestionBankForEssay">
                                                            <i class="fas fa-list-check me-2"></i>Chọn câu tự luận có sẵn
                                                        </button>
                                                    </div>
                                                    <div class="alert alert-soft-info border-0 rounded-4 small mt-4 mb-0">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Khi dùng câu hỏi import/ngân hàng, hệ thống sẽ chuyển sang chế độ “Tự luận theo câu”.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 d-flex justify-content-between align-items-center flex-wrap gap-3 border-top pt-4">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="document.getElementById('info-tab').click()"><i class="fas fa-chevron-left me-2"></i> Quay lại: Thông tin & Cấu hình</button>
                                    <div class="d-flex gap-2">
                                        <button type="submit" id="btnScoringSave" class="btn {{ $currentContentMode === 'tu_luan_tu_do' ? 'btn-primary shadow-sm' : 'btn-outline-primary' }} rounded-pill px-4 fw-bold">
                                            <i class="fas fa-save me-2"></i> Lưu cấu hình
                                        </button>
                                        @if($baiKiemTra->trang_thai_duyet === 'nhap' || $baiKiemTra->trang_thai_duyet === 'tu_choi')
                                            <button type="submit" name="action_after_save" value="submit_for_approval" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="return confirm('Lưu cấu hình hiện tại và gửi bài kiểm tra này để quản trị viên duyệt?')">
                                                <i class="fas fa-paper-plane me-2"></i>Lưu & gửi duyệt
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm @if($currentContentMode === 'tu_luan_tu_do') d-none @endif" id="btnScoringNextImport" onclick="document.getElementById('import-tab').click()">
                                            Tiếp theo: Import <i class="fas fa-chevron-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chi tiết câu hỏi (Redesigned with Answer Preview) -->
<div class="modal fade" id="questionDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light py-3 px-4">
                <h5 class="modal-title fw-bold text-primary" id="detailModalCode">Chi tiết câu hỏi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailModalBody">
                <!-- Content will be injected by JS -->
            </div>
            <div class="modal-footer border-0 bg-light py-3 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-modal="modal" data-bs-dismiss="modal">Đóng</button>
                <div id="detailModalAction"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Import -->
<div class="modal fade" id="importPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-search me-2"></i>Xem trước kết quả Import</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="importPreviewContent">
                    <!-- Loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                <form id="confirmImportForm" action="{{ route('giang-vien.bai-kiem-tra.import-confirm', $baiKiemTra->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="preview_id" id="hiddenPreviewId">
                    <input type="hidden" name="preferred_mode" id="hiddenImportPreferredMode" value="{{ $currentContentMode }}">
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Xác nhận và Lưu vào kho</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modern UI Custom Styles */
    :root {
        --primary-soft: #eef2ff;
        --success-soft: #ecfdf5;
        --warning-soft: #fffbeb;
        --danger-soft: #fef2f2;
        --info-soft: #f0f9ff;
    }

    .max-w-500 { max-width: 500px; }
    .cursor-pointer { cursor: pointer; }
    .border-dashed { border: 2px dashed #dee2e6; }
    .transition-all { transition: all 0.3s ease; }
    .hover-bg-white:hover { background-color: #fff !important; border-color: var(--bs-primary) !important; }
    .focus-primary:focus { border-color: var(--bs-primary) !important; }
    .fade-in { animation: fadeIn 0.4s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .bg-soft-primary { background-color: var(--primary-soft); }
    .bg-soft-success { background-color: var(--success-soft); }
    .bg-soft-info { background-color: var(--info-soft); }
    .bg-soft-warning { background-color: var(--warning-soft); }
    .bg-soft-danger { background-color: var(--danger-soft); }
    
    .modern-tabs .nav-link {
        color: #6c757d;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
    }
    .modern-tabs .nav-link.active {
        color: var(--bs-primary);
        background: var(--primary-soft);
        border-bottom: 3px solid var(--bs-primary);
    }
    .modern-tabs .nav-link:hover:not(.active) {
        background-color: #f8f9fa;
    }
    
    .custom-switch .form-check-input { width: 2.5rem; height: 1.25rem; }
    
    .scoring-mode-option { transition: all 0.2s; border-color: #eee; }
    .scoring-mode-option:hover { background-color: #f8f9fa; border-color: #ccc !important; }
    .scoring-mode-option.active { border-color: var(--bs-primary) !important; background-color: var(--primary-soft); border-width: 2px !important; }
    .free-essay-prompt-textarea { min-height: 260px; resize: vertical; line-height: 1.65; }
    
    .icon-circle {
        width: 56px; height: 56px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
    }

    /* Redesigned Question Bank */
    .question-card {
        transition: all 0.2s; border: 1px solid #eee; border-radius: 16px;
        overflow: hidden; height: 100%; display: flex; flex-direction: column; background: #fff;
    }
    .question-card:hover { border-color: var(--bs-primary); box-shadow: 0 8px 20px rgba(0,0,0,0.06); transform: translateY(-3px); }
    .question-card.selected { border-color: var(--bs-primary); background-color: var(--primary-soft); box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1); }
    .question-card .card-body { padding: 1.25rem; flex-grow: 1; }
    .question-card .card-footer { padding: 0.75rem 1.25rem; background: #fafafa; border-top: 1px solid #f0f0f0; }
    
    .question-content-preview {
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; font-size: 0.95rem; line-height: 1.5; height: 2.85rem; color: #444; font-weight: 500;
    }
    .sticky-sidebar { position: sticky; top: 1.5rem; z-index: 10; }
    .badge-outline { border: 1px solid currentColor; background: transparent; font-weight: 600; }
    
    .question-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;
    }
    .q-bank-container { max-height: 750px; overflow-y: auto; padding: 0.5rem; scrollbar-width: thin; }
    .q-bank-container::-webkit-scrollbar { width: 5px; }
    .q-bank-container::-webkit-scrollbar-thumb { background-color: #ddd; border-radius: 10px; }

    .selection-sidebar-list { max-height: 420px; overflow-y: auto; border-radius: 0; }
    .selection-sidebar-list::-webkit-scrollbar { width: 4px; }
    .selection-sidebar-list::-webkit-scrollbar-thumb { background-color: #eee; }

    /* Modal Answer Styles */
    .ans-preview-item {
        padding: 0.75rem; border: 1px solid #eee; border-radius: 10px;
        margin-bottom: 0.5rem; transition: background 0.2s; display: flex; align-items: center; gap: 10px;
    }
    .ans-preview-item.correct { background-color: var(--success-soft); border-color: #bbf7d0; color: #166534; font-weight: 600; }
    .ans-preview-icon { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; }
    .correct .ans-preview-icon { border-color: #166534; background: #166534; color: #fff; }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // UI Elements Selection
    const scoringModeOptions = document.querySelectorAll('.scoring-mode-option');
    const packageArea = document.getElementById('packageScoringArea');
    const manualArea = document.getElementById('manualScoringArea');
    const structuredScoringArea = document.getElementById('structuredScoringArea');
    const freeEssayScoringArea = document.getElementById('freeEssayScoringArea');
    const contentModeSelect = document.getElementById('contentModeSelect');
    const contentModeDescription = document.getElementById('contentModeDescription');
    const essayPromptInputs = Array.from(document.querySelectorAll('[data-essay-prompt-input]'));
    const btnUseImportedEssayQuestions = document.getElementById('btnUseImportedEssayQuestions');
    const btnOpenQuestionBankForEssay = document.getElementById('btnOpenQuestionBankForEssay');
    const btnScoringSave = document.getElementById('btnScoringSave');
    const btnScoringNextImport = document.getElementById('btnScoringNextImport');
    const hiddenImportPreferredMode = document.getElementById('hiddenImportPreferredMode');
    const questionFlowTabItems = Array.from(document.querySelectorAll('[data-question-flow-tab]'));
    const selectAll = document.getElementById('selectAllQuestions');
    const selectedList = document.getElementById('selectedQuestionsList');
    const realtimeTotal = document.getElementById('realtimeTotalScore');
    const previewPackage = document.getElementById('previewPackageScore');
    const importedQuestionIds = @json($importedQuestionIds);

    const totalInput = document.querySelector('input[name="tong_diem_goi_diem"]');
    const countInput = document.querySelector('input[name="so_cau_goi_diem"]');

    const importFileInput = document.getElementById('importFile');
    const btnUpload = document.getElementById('btnUploadImport');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const importPreviewArea = document.getElementById('importPreviewArea');
    const summaryText = document.getElementById('importSummaryText');
    const summaryMeta = document.getElementById('importSummaryMeta');
    const btnConfirm = document.getElementById('btnConfirmImport');
    const hiddenPreviewId = document.getElementById('hiddenPreviewId');
    const importPreviewContent = document.getElementById('importPreviewContent');
    const importPreviewModal = new bootstrap.Modal(document.getElementById('importPreviewModal'));

    const sidebarList = document.getElementById('selectionSidebarList');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const summaryQuestionCount = document.getElementById('summaryQuestionCount');
    const sidebarTotalScore = document.getElementById('sidebarTotalScore');
    const packageSelectionInfo = document.getElementById('packageSelectionInfo');
    const packageSelectionCounter = document.getElementById('packageSelectionCounter');
    const packageSelectionRemaining = document.getElementById('packageSelectionRemaining');
    const sidebarSelectionHint = document.getElementById('sidebarSelectionHint');
    const filterSection = document.getElementById('filterSection');
    const btnToggleFilters = document.getElementById('btnToggleFilters');
    const questionDetailModal = new bootstrap.Modal(document.getElementById('questionDetailModal'));

    let latestImportPreview = null;

    // Helper Functions
    function getCheckboxes() {
        return Array.from(document.querySelectorAll('.question-checkbox'));
    }

    function activateTab(tabId) {
        const button = document.querySelector(`[data-bs-target="#${tabId}"]`);
        if (!button) return;
        bootstrap.Tab.getOrCreateInstance(button).show();
    }

    function getContentMode() {
        return contentModeSelect?.value || 'hon_hop';
    }

    function setContentMode(mode) {
        if (!contentModeSelect) return;
        contentModeSelect.value = mode;
        updateContentModeUI();
    }

    function syncEssayPrompt(source) {
        essayPromptInputs.forEach(input => {
            if (input !== source) input.value = source.value;
        });
    }

    function updateContentModeUI() {
        const mode = getContentMode();
        const isFreeEssay = mode === 'tu_luan_tu_do';
        const descriptions = {
            trac_nghiem: 'Bài chỉ dùng câu hỏi trắc nghiệm, có thể chia điểm tự động hoặc nhập điểm từng câu.',
            tu_luan_tu_do: 'Bài dùng một đề tự luận tổng. Nhập đề và tổng điểm ngay trong tab Thiết lập điểm.',
            tu_luan_theo_cau: 'Bài dùng các câu tự luận từ ngân hàng/import, giảng viên chấm từng câu sau khi học viên nộp.',
            hon_hop: 'Bài có cả trắc nghiệm và tự luận, cần chọn đủ hai loại câu hỏi trong ngân hàng.',
        };

        if (contentModeDescription) {
            contentModeDescription.textContent = descriptions[mode] || '';
        }

        if (hiddenImportPreferredMode) {
            hiddenImportPreferredMode.value = mode;
        }

        structuredScoringArea?.classList.toggle('d-none', isFreeEssay);
        freeEssayScoringArea?.classList.toggle('d-none', !isFreeEssay);
        questionFlowTabItems.forEach(item => item.classList.toggle('d-none', isFreeEssay));
        btnScoringNextImport?.classList.toggle('d-none', isFreeEssay);

        if (btnScoringSave) {
            btnScoringSave.classList.toggle('btn-primary', isFreeEssay);
            btnScoringSave.classList.toggle('shadow-sm', isFreeEssay);
            btnScoringSave.classList.toggle('btn-outline-primary', !isFreeEssay);
        }

        if (isFreeEssay) {
            const activeTabTarget = document.querySelector('#examTabs .nav-link.active')?.dataset.bsTarget;
            if (activeTabTarget === '#import' || activeTabTarget === '#questions') {
                activateTab('scoring');
            }

            scoringModeOptions.forEach(item => {
                item.classList.toggle('active', item.dataset.mode === 'thu_cong');
            });
            const manualRadio = document.querySelector('input[name="che_do_tinh_diem"][value="thu_cong"]');
            if (manualRadio) manualRadio.checked = true;
            packageArea?.classList.add('d-none');
            manualArea?.classList.add('d-none');
            return;
        }

        if (getScoringMode() === 'goi_diem') {
            packageArea?.classList.remove('d-none');
            manualArea?.classList.add('d-none');
        } else {
            packageArea?.classList.add('d-none');
            manualArea?.classList.remove('d-none');
            updateManualList();
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getScoringMode() {
        return document.querySelector('input[name="che_do_tinh_diem"]:checked')?.value || 'thu_cong';
    }

    function isPackageMode() {
        return getScoringMode() === 'goi_diem';
    }

    function getPackageTargetCount() {
        const parsed = parseInt(countInput?.value || 0, 10);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
    }

    function getSelectionStatus() {
        const selected = getCheckboxes().filter(cb => cb.checked).length;
        const target = getPackageTargetCount();

        return {
            selected,
            target,
            remaining: Math.max(target - selected, 0),
            overflow: Math.max(selected - target, 0),
        };
    }

    function shuffleArray(items) {
        const shuffled = [...items];
        for (let i = shuffled.length - 1; i > 0; i -= 1) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        return shuffled;
    }

    function updatePackageSelectionUI() {
        const { selected, target, remaining, overflow } = getSelectionStatus();
        const hasTarget = isPackageMode() && target > 0;

        if (selectedCountBadge) {
            selectedCountBadge.textContent = hasTarget ? `${selected}/${target}` : `${selected}`;
        }

        if (summaryQuestionCount) {
            summaryQuestionCount.textContent = hasTarget ? `${selected}/${target}` : `${selected}`;
        }

        if (!packageSelectionInfo || !packageSelectionCounter || !packageSelectionRemaining || !sidebarSelectionHint) {
            return;
        }

        if (!hasTarget) {
            packageSelectionInfo.classList.add('d-none');
            sidebarSelectionHint.classList.add('d-none');
            sidebarSelectionHint.innerHTML = '';
            return;
        }

        packageSelectionInfo.classList.remove('d-none');
        sidebarSelectionHint.classList.remove('d-none');
        packageSelectionCounter.textContent = `${selected}/${target} c\u00e2u`;

        if (overflow > 0) {
            packageSelectionCounter.className = 'badge bg-soft-danger text-danger rounded-pill px-3 py-2 fw-bold';
            packageSelectionRemaining.innerHTML = `\u0110ang d\u01b0 <strong>${overflow}</strong> c\u00e2u so v\u1edbi g\u00f3i \u0111i\u1ec3m.`;
            sidebarSelectionHint.innerHTML = `<i class="fas fa-exclamation-triangle text-danger me-2"></i>\u0110\u00e3 ch\u1ecdn <strong>${selected}/${target}</strong> c\u00e2u, \u0111ang d\u01b0 <strong>${overflow}</strong> c\u00e2u. B\u1ecf b\u1edbt c\u00e2u ho\u1eb7c d\u00f9ng Ch\u1ecdn nhanh l\u1ea1i \u0111\u1ec3 c\u00e2n \u0111\u00fang s\u1ed1 l\u01b0\u1ee3ng.`;
            return;
        }

        if (remaining > 0) {
            packageSelectionCounter.className = 'badge bg-soft-warning text-warning rounded-pill px-3 py-2 fw-bold';
            packageSelectionRemaining.innerHTML = `C\u00f2n <strong>${remaining}</strong> c\u00e2u c\u1ea7n ch\u1ecdn th\u00eam.`;
            sidebarSelectionHint.innerHTML = `<i class="fas fa-hourglass-half text-warning me-2"></i>Ti\u1ebfn \u0111\u1ed9 g\u00f3i \u0111i\u1ec3m: <strong>${selected}/${target}</strong> c\u00e2u. C\u00f2n <strong>${remaining}</strong> c\u00e2u c\u1ea7n ch\u1ecdn.`;
            return;
        }

        packageSelectionCounter.className = 'badge bg-soft-success text-success rounded-pill px-3 py-2 fw-bold';
        packageSelectionRemaining.textContent = '\u0110\u00e3 ch\u1ecdn \u0111\u1ee7 s\u1ed1 c\u00e2u theo g\u00f3i \u0111i\u1ec3m.';
        sidebarSelectionHint.innerHTML = `<i class="fas fa-check-circle text-success me-2"></i>\u0110\u00e3 ch\u1ecdn \u0111\u1ee7 <strong>${target}</strong> c\u00e2u theo g\u00f3i \u0111i\u1ec3m t\u1ef1 \u0111\u1ed9ng.`;
    }

    function applyQuickSelection(predicate) {
        const candidates = getCheckboxes()
            .filter(cb => !cb.disabled)
            .filter(cb => predicate(cb, cb.closest('.question-card-wrapper')));

        if (!isPackageMode()) {
            candidates.forEach(cb => {
                cb.checked = true;
            });
            updateSelectionUI();
            return;
        }

        const { remaining } = getSelectionStatus();
        if (remaining <= 0) {
            updatePackageSelectionUI();
            return;
        }

        shuffleArray(candidates.filter(cb => !cb.checked))
            .slice(0, remaining)
            .forEach(cb => {
                cb.checked = true;
            });

        updateSelectionUI();
    }

    // Question Selection Logic
    function updateSelectionUI() {
        const checkboxes = getCheckboxes();
        const checked = checkboxes.filter(cb => cb.checked);
        let totalScore = 0;
        let html = '';

        checked.forEach(cb => {
            const id = cb.dataset.id;
            const code = cb.dataset.code;
            const content = cb.dataset.content;
            const score = parseFloat(cb.dataset.score || 0);
            totalScore += score;

            html += `
                <div class="list-group-item border-0 border-bottom px-3 py-3 bg-transparent hover-bg-light transition-all">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 min-w-0 me-2">
                            <div class="fw-bold small text-primary">${escapeHtml(code)}</div>
                            <div class="text-muted small text-truncate-2">${escapeHtml(content)}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light text-danger p-0 rounded-circle remove-selection" data-id="${id}" style="width:24px; height:24px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

            const card = document.querySelector(`.question-card[data-id="${id}"]`);
            if (card) card.classList.add('selected');
        });

        // Unselected visual feedback
        checkboxes.filter(cb => !cb.checked).forEach(cb => {
            const card = document.querySelector(`.question-card[data-id="${cb.dataset.id}"]`);
            if (card) card.classList.remove('selected');
        });

        if (html === '') {
            html = '<div class="p-5 text-center text-muted small"><i class="fas fa-plus-circle fa-2x mb-2 opacity-25"></i><br>Hãy chọn câu hỏi từ ngân hàng</div>';
        }

        sidebarList.innerHTML = html;
        sidebarTotalScore.textContent = totalScore.toFixed(2);
        updatePackageSelectionUI();

        // Re-bind remove buttons
        document.querySelectorAll('.remove-selection').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const cb = document.querySelector(`.question-checkbox[data-id="${id}"]`);
                if (cb) {
                    cb.checked = false;
                    cb.dispatchEvent(new Event('change'));
                }
            });
        });

        updateManualList();
    }

    function updateManualList() {
        if (!selectedList) return;
        
        const currentData = {};
        document.querySelectorAll('.q-score-input').forEach(input => {
            const match = input.name.match(/\[(\d+)\]/);
            if (match) currentData[match[1]] = input.value;
        });

        let html = '';
        getCheckboxes().filter(cb => cb.checked).forEach((cb, index) => {
            const id = cb.value;
            const code = cb.dataset.code;
            const content = cb.dataset.content;
            const score = currentData[id] || cb.dataset.score || 1;

            html += `
                <tr data-id="${id}">
                    <td class="fw-bold text-center ps-4">${index + 1}</td>
                    <td>
                        <div class="small fw-bold text-primary">${escapeHtml(code)}</div>
                        <div class="text-muted small text-truncate" style="max-width: 500px;">${escapeHtml(content)}</div>
                    </td>
                    <td class="pe-4">
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.25" min="0.25" name="question_scores[${id}]" value="${escapeHtml(score)}" class="form-control shadow-none rounded-start border-2 q-score-input">
                            <span class="input-group-text bg-white border-2">đ</span>
                        </div>
                    </td>
                </tr>
            `;
        });

        if (html === '') {
            html = '<tr><td colspan="3" class="text-center py-5 text-muted bg-light border-0"><i class="fas fa-hand-pointer fa-2x mb-2 opacity-25"></i><br>Chọn câu hỏi tại tab Ngân hàng trước</td></tr>';
        }

        selectedList.innerHTML = html;
        document.querySelectorAll('.q-score-input').forEach(input => {
            input.addEventListener('input', calculateManualTotal);
        });
        calculateManualTotal();
    }

    function calculateManualTotal() {
        if (!realtimeTotal) return;
        let total = 0;
        document.querySelectorAll('.q-score-input').forEach(input => {
            total += parseFloat(input.value || 0);
        });
        realtimeTotal.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function calculatePackagePreview() {
        const total = parseFloat(totalInput?.value || 0);
        const count = parseInt(countInput?.value || 0, 10);
        if (previewPackage) {
            previewPackage.textContent = count > 0 ? (total / count).toFixed(2) : '?';
        }
    }

    // Question Detail Modal with Answer Preview
    window.viewQuestionDetail = function(id) {
        const cb = document.querySelector(`.question-checkbox[data-id="${id}"]`);
        if (!cb) return;

        const code = cb.dataset.code;
        const content = cb.dataset.content;
        const score = cb.dataset.score;
        const card = cb.closest('.question-card');
        const level = card.querySelector('.badge[class*="bg-soft-"]').textContent.trim();
        const type = card.querySelector('.badge-outline.text-info').textContent.trim();
        const answers = JSON.parse(card.dataset.answers || '[]');

        let answersHtml = '';
        if (answers.length > 0) {
            answersHtml = '<div class="mt-4"><h6 class="fw-bold mb-3"><i class="fas fa-list-ol me-2"></i>Lựa chọn đáp án:</h6>';
            answers.forEach((ans, idx) => {
                const letter = String.fromCharCode(65 + idx);
                answersHtml += `
                    <div class="ans-preview-item ${ans.correct ? 'correct' : ''}">
                        <div class="ans-preview-icon">${letter}</div>
                        <div class="flex-grow-1">${escapeHtml(ans.text)}</div>
                        ${ans.correct ? '<i class="fas fa-check-circle ms-auto"></i>' : ''}
                    </div>
                `;
            });
            answersHtml += '</div>';
        } else {
            answersHtml = `
                <div class="alert alert-soft-info mt-4 p-3 border-0 rounded-4">
                    <i class="fas fa-info-circle me-2"></i> Đây là câu hỏi <strong>${type}</strong>. Nội dung trả lời sẽ được giảng viên chấm điểm sau khi học viên nộp bài.
                </div>
            `;
        }

        document.getElementById('detailModalCode').innerHTML = `<i class="fas fa-file-alt me-2"></i>Mã câu hỏi: ${code}`;
        document.getElementById('detailModalBody').innerHTML = `
            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="badge bg-soft-info text-info rounded-pill px-3 py-2">${type}</span>
                <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-2">Mức độ: ${level}</span>
                <span class="badge bg-soft-success text-success rounded-pill px-3 py-2">Điểm chuẩn: ${score}đ</span>
            </div>
            <div class="p-4 bg-light rounded-4 border-0">
                <h6 class="fw-bold text-muted small text-uppercase mb-2">Nội dung câu hỏi:</h6>
                <div class="fs-5 fw-bold text-dark" style="white-space: pre-wrap; line-height: 1.6;">${escapeHtml(content)}</div>
            </div>
            ${answersHtml}
        `;

        const actionDiv = document.getElementById('detailModalAction');
        if (cb.checked) {
            actionDiv.innerHTML = `<button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" onclick="toggleQuestion(${id}, false)"><i class="fas fa-minus-circle me-1"></i> Bỏ chọn câu này</button>`;
        } else {
            actionDiv.innerHTML = `<button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="toggleQuestion(${id}, true)"><i class="fas fa-plus-circle me-1"></i> Chọn câu này</button>`;
        }

        questionDetailModal.show();
    };

    window.toggleQuestion = function(id, state) {
        const cb = document.querySelector(`.question-checkbox[data-id="${id}"]`);
        if (cb) {
            cb.checked = state;
            cb.dispatchEvent(new Event('change'));
            questionDetailModal.hide();
        }
    };

    contentModeSelect?.addEventListener('change', updateContentModeUI);

    essayPromptInputs.forEach(input => {
        input.addEventListener('input', function() {
            syncEssayPrompt(this);
        });
    });

    btnUseImportedEssayQuestions?.addEventListener('click', function() {
        setContentMode('tu_luan_theo_cau');
        activateTab('import');
    });

    btnOpenQuestionBankForEssay?.addEventListener('click', function() {
        setContentMode('tu_luan_theo_cau');
        activateTab('questions');
    });

    btnScoringNextImport?.addEventListener('click', function() {
        if (getContentMode() === 'tu_luan_tu_do') {
            setContentMode('tu_luan_theo_cau');
        }
    });

    // Global Events
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('question-checkbox')) {
            updateSelectionUI();
        }
    });

    document.querySelectorAll('.btn-view-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            viewQuestionDetail(this.dataset.id);
        });
    });

    if (btnToggleFilters) {
        btnToggleFilters.addEventListener('click', function() {
            filterSection.classList.toggle('d-none');
        });
    }

    // Quick selection logic
    document.querySelectorAll('.select-by-level').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const level = this.dataset.level;
            applyQuickSelection((cb, wrapper) => wrapper && wrapper.dataset.level === level);
        });
    });

    document.getElementById('selectAllCurrentView')?.addEventListener('click', function(e) {
        e.preventDefault();
        applyQuickSelection(() => true);
    });

    document.getElementById('deselectAllCurrentView')?.addEventListener('click', function(e) {
        e.preventDefault();
        getCheckboxes().forEach(cb => { cb.checked = false; });
        updateSelectionUI();
    });

    document.getElementById('btnSidebarClear')?.addEventListener('click', function() {
        if (confirm('Bạn có chắc chắn muốn bỏ chọn tất cả câu hỏi đã chọn?')) {
            getCheckboxes().forEach(cb => { cb.checked = false; });
            updateSelectionUI();
        }
    });

    // Scoring Mode Switch
    scoringModeOptions.forEach(option => {
        option.addEventListener('click', function() {
            const mode = this.dataset.mode;
            scoringModeOptions.forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            if (mode === 'goi_diem') {
                packageArea?.classList.remove('d-none');
                manualArea?.classList.add('d-none');
            } else {
                packageArea?.classList.add('d-none');
                manualArea?.classList.remove('d-none');
                updateManualList();
            }
            calculatePackagePreview();
            updatePackageSelectionUI();
        });
    });

    [totalInput, countInput].filter(Boolean).forEach(input => input.addEventListener('input', function() {
        calculatePackagePreview();
        updatePackageSelectionUI();
    }));

    document.querySelectorAll('.apply-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            if (totalInput) totalInput.value = this.dataset.total;
            if (countInput) countInput.value = this.dataset.count;
            calculatePackagePreview();
            updatePackageSelectionUI();
        });
    });

    // File Import Logic
    importFileInput?.addEventListener('change', function() {
        if (this.files.length > 0) {
            fileNameDisplay.innerHTML = `<i class="fas fa-file-medical text-primary me-2"></i> ${this.files[0].name}`;
            fileNameDisplay.classList.add('text-primary');
        }
    });

    btnUpload?.addEventListener('click', function() {
        const file = importFileInput.files[0];
        if (!file) { alert('Vui lòng chọn tệp tin trước khi xử lý.'); return; }
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        btnUpload.disabled = true;
        btnUpload.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Đang đọc file...';
        
        fetch('{{ route("giang-vien.bai-kiem-tra.import-preview", $baiKiemTra->id) }}', {
            method: 'POST',
            body: formData,
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Lỗi hệ thống khi đọc file.');
            return data;
        })
        .then(data => {
            btnUpload.disabled = false;
            btnUpload.innerHTML = '<i class="fas fa-upload me-1"></i> Bắt đầu xử lý file';
            importPreviewArea.classList.remove('d-none');
            summaryText.innerHTML = `<i class="fas fa-check-circle me-2"></i> Đã đọc tệp. Có <strong>${data.summary.valid}</strong> dòng hợp lệ sẵn sàng import.`;
            const reviewCount = Number(data.summary.needs_review || 0);
                        summaryMeta.innerHTML = `Định dạng: <strong>${(data.source_format || '').toUpperCase()}</strong>. Bản xem trước hiển thị rõ loại câu hỏi, ghi chú và gợi ý tự luận${reviewCount > 0 ? `. Có ${reviewCount} dòng cần rà soát thêm.` : '.'}`;
            hiddenPreviewId.value = data.preview_id;
            latestImportPreview = data;
        })
        .catch(err => {
            btnUpload.disabled = false;
            btnUpload.innerHTML = '<i class="fas fa-upload me-1"></i> Bắt đầu xử lý file';
            alert(err.message);
        });
    });

    btnConfirm?.addEventListener('click', function() {
        if (!latestImportPreview) return;
        const rows = (latestImportPreview.preview_rows || []).map(row => {
            const statusLabel = row.status === 'hop_le' ? 'Hợp lệ' : 'Cần rà soát';
            const typeLabel = row.question_type_label || row.question_type || 'Không rõ';
            const typeBadge = row.question_type === 'tu_luan' ? 'info' : 'primary';
            const noteHtml = [
                row.goi_y_tra_loi ? `<div class="small text-muted mt-1"><strong>Gợi ý:</strong> ${escapeHtml(row.goi_y_tra_loi)}</div>` : '',
                row.note ? `<div class="small text-warning mt-1"><strong>Lưu ý:</strong> ${escapeHtml(row.note)}</div>` : '',
            ].filter(Boolean).join('');

            return `
            <tr>
                <td class="small text-center text-muted">${row.line || '-'}</td>
                <td><span class="badge bg-${typeBadge}-subtle text-${typeBadge} border border-${typeBadge}-subtle small">${escapeHtml(typeLabel)}</span></td>
                <td class="small fw-bold">${escapeHtml(row.question)}</td>
                <td class="small text-success fw-bold">${escapeHtml(row.correct_answer || 'Cần xem lại')}</td>
                <td class="small">${noteHtml || '<span class="text-muted">Không có ghi chu them.</span>'}</td>
                <td><span class="badge bg-${row.status === 'hop_le' ? 'success' : 'warning text-dark'} small">${statusLabel}</span></td>
            </tr>
            `;
        }).join('');
        
        importPreviewContent.innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead class="bg-light"><tr><th>Dòng</th><th>Loại</th><th>Câu hỏi</th><th>Đáp án / Cách chấm</th><th>Ghi chú</th><th>TT</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
                ${latestImportPreview.remaining_preview_rows > 0 ? `<div class="alert alert-light border mt-2 py-1 px-2 small text-center text-muted">... va ${latestImportPreview.remaining_preview_rows} câu hỏi khác</div>` : ''}
            </div>
        `;
        importPreviewModal.show();
    });

    // Final Init
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab')) activateTab(params.get('tab'));
    else if (importedQuestionIds.length > 0) activateTab('questions');

    updateSelectionUI();
    calculatePackagePreview();
    updateContentModeUI();
});
</script>
@endpush
@endsection
