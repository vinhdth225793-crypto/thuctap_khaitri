@extends('layouts.app')

@php
    $selectedQuestionType = old('loai_cau_hoi', $cauHoi->loai_cau_hoi ?: \App\Models\NganHangCauHoi::LOAI_TRAC_NGHIEM);
    $selectedAnswerMode = old('kieu_dap_an', $cauHoi->kieu_dap_an ?: \App\Models\NganHangCauHoi::KIEU_MOT_DAP_AN);

    $answerRows = collect(old('dap_ans', []))
        ->map(fn ($answer, $key) => [
            'key' => (string) $key,
            'ky_hieu' => $answer['ky_hieu'] ?? '',
            'noi_dung' => $answer['noi_dung'] ?? '',
        ])
        ->values();

    if ($answerRows->isEmpty() && $cauHoi->relationLoaded('dapAns') && $cauHoi->dapAns->isNotEmpty()) {
        $answerRows = $cauHoi->dapAns->values()->map(fn ($answer, $index) => [
            'key' => (string) $index,
            'ky_hieu' => $answer->ky_hieu,
            'noi_dung' => $answer->noi_dung,
        ]);
    }

    if ($answerRows->isEmpty()) {
        $answerRows = collect(range(0, 3))->map(fn ($index) => [
            'key' => (string) $index,
            'ky_hieu' => chr(65 + $index),
            'noi_dung' => '',
        ]);
    }

    $selectedCorrectKey = old('correct_answer_key');
    if ($selectedCorrectKey === null && $cauHoi->exists && $cauHoi->is_single_correct) {
        $selectedCorrectKey = (string) $cauHoi->dapAns->search(fn ($answer) => (bool) $answer->is_dap_an_dung);
    }

    $selectedCorrectKeys = collect(old('correct_answer_keys', []))
        ->map(fn ($key) => (string) $key)
        ->values()
        ->all();

    if ($selectedCorrectKeys === [] && $cauHoi->exists && $cauHoi->is_multiple_correct) {
        $selectedCorrectKeys = $cauHoi->dapAns
            ->filter(fn ($answer) => (bool) $answer->is_dap_an_dung)
            ->keys()
            ->map(fn ($key) => (string) $key)
            ->values()
            ->all();
    }

    $selectedTrueFalse = old('dap_an_dung_sai');
    if ($selectedTrueFalse === null && $cauHoi->exists && $cauHoi->is_true_false) {
        $correctTrueFalse = optional($cauHoi->dapAns->firstWhere('is_dap_an_dung', true))->noi_dung;
        $selectedTrueFalse = $correctTrueFalse === 'Sai' ? 'sai' : 'dung';
    }

    $nextAnswerIndex = $answerRows->count();
@endphp

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-muted small mb-1">
                <i class="fas fa-home me-1"></i> Admin > Kiểm tra Online > Ngân hàng câu hỏi > {{ $title }}
            </div>
            <h3 class="fw-bold mb-0">{{ $title }}</h3>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ $action }}" method="POST" id="question-form">
                @csrf
                @if($method === 'PUT')
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Khóa học <span class="text-danger">*</span></label>
                        <select name="khoa_hoc_id" id="khoa_hoc_id" class="form-select @error('khoa_hoc_id') is-invalid @enderror" required>
                            <option value="">Chọn khóa học</option>
                            @foreach($khoaHocs as $khoaHoc)
                                <option value="{{ $khoaHoc->id }}" @selected((string) old('khoa_hoc_id', $cauHoi->khoa_hoc_id) === (string) $khoaHoc->id)>
                                    [{{ $khoaHoc->ma_khoa_hoc }}] {{ $khoaHoc->ten_khoa_hoc }}
                                </option>
                            @endforeach
                        </select>
                        @error('khoa_hoc_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Module</label>
                        <select name="module_hoc_id" id="module_hoc_id" class="form-select @error('module_hoc_id') is-invalid @enderror">
                            <option value="">Dùng chung toàn khóa</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->id }}" data-course-id="{{ $module->khoa_hoc_id }}" @selected((string) old('module_hoc_id', $cauHoi->module_hoc_id) === (string) $module->id)>
                                    [{{ $module->ma_module }}] {{ $module->ten_module }}
                                </option>
                            @endforeach
                        </select>
                        @error('module_hoc_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã câu hỏi</label>
                        <input type="text" name="ma_cau_hoi" class="form-control @error('ma_cau_hoi') is-invalid @enderror" value="{{ old('ma_cau_hoi', $cauHoi->ma_cau_hoi) }}" placeholder="Để trống để hệ thống tự sinh">
                        @error('ma_cau_hoi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Loại câu hỏi <span class="text-danger">*</span></label>
                        <select name="loai_cau_hoi" id="loai_cau_hoi" class="form-select @error('loai_cau_hoi') is-invalid @enderror" required>
                            @foreach($questionTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedQuestionType === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('loai_cau_hoi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4" id="answer-mode-wrapper">
                        <label class="form-label fw-semibold">Kiểu đáp án <span class="text-danger">*</span></label>
                        <select name="kieu_dap_an" id="kieu_dap_an" class="form-select @error('kieu_dap_an') is-invalid @enderror">
                            @foreach($answerModeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedAnswerMode === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kieu_dap_an')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nội dung câu hỏi <span class="text-danger">*</span></label>
                        <textarea name="noi_dung_cau_hoi" rows="4" class="form-control @error('noi_dung_cau_hoi') is-invalid @enderror" placeholder="Nhập nội dung câu hỏi..." required>{{ old('noi_dung_cau_hoi', $cauHoi->noi_dung_cau_hoi) }}</textarea>
                        @error('noi_dung_cau_hoi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mức độ <span class="text-danger">*</span></label>
                        <select name="muc_do" class="form-select @error('muc_do') is-invalid @enderror" required>
                            @foreach($difficultyOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('muc_do', $cauHoi->muc_do ?: 'trung_binh') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('muc_do')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Điểm mặc định <span class="text-danger">*</span></label>
                        <input type="number" name="diem_mac_dinh" min="0.25" step="0.25" class="form-control @error('diem_mac_dinh') is-invalid @enderror" value="{{ old('diem_mac_dinh', $cauHoi->diem_mac_dinh ?: 1) }}" required>
                        @error('diem_mac_dinh')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                        <select name="trang_thai" class="form-select @error('trang_thai') is-invalid @enderror" required>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('trang_thai', $cauHoi->trang_thai ?: \App\Models\NganHangCauHoi::TRANG_THAI_NHAP) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('trang_thai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="co_the_tai_su_dung" value="0">
                            <input type="checkbox" name="co_the_tai_su_dung" value="1" class="form-check-input" id="co_the_tai_su_dung" @checked((bool) old('co_the_tai_su_dung', $cauHoi->co_the_tai_su_dung ?? true))>
                            <label class="form-check-label fw-semibold" for="co_the_tai_su_dung">
                                Cho phép tái sử dụng câu hỏi này trong các đề khác
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div id="objective-panel" class="mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">Quản lý đáp án</h5>
                            <div class="text-muted small">Chọn đúng đáp án theo kiểu câu hỏi đang cấu hình.</div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-answer-btn">
                            <i class="fas fa-plus me-1"></i> Thêm đáp án
                        </button>
                    </div>

                    <div id="structured-answer-panel">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="80" class="text-center">Ký hiệu</th>
                                        <th width="140" class="text-center">Đáp án đúng</th>
                                        <th>Nội dung đáp án</th>
                                        <th width="70" class="text-center">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody id="answer-list" data-next-index="{{ $nextAnswerIndex }}">
                                    @foreach($answerRows as $row)
                                        <tr class="answer-row" data-row-key="{{ $row['key'] }}">
                                            <td>
                                                <input type="text" name="dap_ans[{{ $row['key'] }}][ky_hieu]" class="form-control answer-key" value="{{ $row['ky_hieu'] }}" readonly>
                                            </td>
                                            <td class="text-center">
                                                <div class="single-correct-control">
                                                    <input type="radio" name="correct_answer_key" value="{{ $row['key'] }}" class="form-check-input" @checked((string) $selectedCorrectKey === (string) $row['key'])>
                                                </div>
                                                <div class="multiple-correct-control d-none">
                                                    <input type="checkbox" name="correct_answer_keys[]" value="{{ $row['key'] }}" class="form-check-input" @checked(in_array((string) $row['key'], $selectedCorrectKeys, true))>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="dap_ans[{{ $row['key'] }}][noi_dung]" class="form-control" value="{{ $row['noi_dung'] }}" placeholder="Nhập nội dung đáp án">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-answer-btn">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @error('dap_ans')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        @error('correct_answer_key')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        @error('correct_answer_keys')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="true-false-panel" class="d-none">
                        <div class="alert alert-light border mb-0">
                            <div class="fw-semibold mb-2">Đáp án cố định</div>
                            <div class="text-muted small mb-3">Loại câu hỏi này luôn có 2 đáp án là “Đúng” và “Sai”.</div>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="dap_an_dung_sai" id="dap-an-dung" value="dung" @checked($selectedTrueFalse === 'dung')>
                                    <label class="form-check-label" for="dap-an-dung">Đúng là đáp án chính xác</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="dap_an_dung_sai" id="dap-an-sai" value="sai" @checked($selectedTrueFalse === 'sai')>
                                    <label class="form-check-label" for="dap-an-sai">Sai là đáp án chính xác</label>
                                </div>
                            </div>
                            @error('dap_an_dung_sai')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Gợi ý trả lời</label>
                        <textarea name="goi_y_tra_loi" rows="4" class="form-control @error('goi_y_tra_loi') is-invalid @enderror" placeholder="Gợi ý ngắn cho người ra đề hoặc học viên (nếu cần)">{{ old('goi_y_tra_loi', $cauHoi->goi_y_tra_loi) }}</textarea>
                        @error('goi_y_tra_loi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Giải thích đáp án</label>
                        <textarea name="giai_thich_dap_an" rows="4" class="form-control @error('giai_thich_dap_an') is-invalid @enderror" placeholder="Giải thích vì sao đáp án đúng">{{ old('giai_thich_dap_an', $cauHoi->giai_thich_dap_an) }}</textarea>
                        @error('giai_thich_dap_an')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn btn-light border">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Lưu câu hỏi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="answer-row-template">
    <tr class="answer-row" data-row-key="__INDEX__">
        <td>
            <input type="text" name="dap_ans[__INDEX__][ky_hieu]" class="form-control answer-key" value="__LABEL__" readonly>
        </td>
        <td class="text-center">
            <div class="single-correct-control">
                <input type="radio" name="correct_answer_key" value="__INDEX__" class="form-check-input">
            </div>
            <div class="multiple-correct-control d-none">
                <input type="checkbox" name="correct_answer_keys[]" value="__INDEX__" class="form-check-input">
            </div>
        </td>
        <td>
            <input type="text" name="dap_ans[__INDEX__][noi_dung]" class="form-control" placeholder="Nhập nội dung đáp án">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-answer-btn">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const courseSelect = document.getElementById('khoa_hoc_id');
        const moduleSelect = document.getElementById('module_hoc_id');
        const questionTypeSelect = document.getElementById('loai_cau_hoi');
        const answerModeSelect = document.getElementById('kieu_dap_an');
        const answerModeWrapper = document.getElementById('answer-mode-wrapper');
        const objectivePanel = document.getElementById('objective-panel');
        const structuredAnswerPanel = document.getElementById('structured-answer-panel');
        const trueFalsePanel = document.getElementById('true-false-panel');
        const addAnswerButton = document.getElementById('add-answer-btn');
        const answerList = document.getElementById('answer-list');
        const answerTemplate = document.getElementById('answer-row-template');

        const syncModules = () => {
            const selectedCourse = courseSelect.value;

            Array.from(moduleSelect.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = selectedCourse !== '' && option.dataset.courseId !== selectedCourse;
            });

            const selectedOption = moduleSelect.options[moduleSelect.selectedIndex];
            if (selectedOption && selectedOption.hidden) {
                moduleSelect.value = '';
            }
        };

        const syncAnswerPanels = () => {
            const isEssay = questionTypeSelect.value === '{{ \App\Models\NganHangCauHoi::LOAI_TU_LUAN }}';
            const answerMode = answerModeSelect.value;

            answerModeWrapper.classList.toggle('d-none', isEssay);
            objectivePanel.classList.toggle('d-none', isEssay);

            if (isEssay) {
                return;
            }

            const isTrueFalse = answerMode === '{{ \App\Models\NganHangCauHoi::KIEU_DUNG_SAI }}';
            const isMultipleCorrect = answerMode === '{{ \App\Models\NganHangCauHoi::KIEU_NHIEU_DAP_AN }}';

            structuredAnswerPanel.classList.toggle('d-none', isTrueFalse);
            trueFalsePanel.classList.toggle('d-none', !isTrueFalse);
            addAnswerButton.classList.toggle('d-none', isTrueFalse);

            document.querySelectorAll('.single-correct-control').forEach((element) => {
                element.classList.toggle('d-none', isMultipleCorrect);
            });

            document.querySelectorAll('.multiple-correct-control').forEach((element) => {
                element.classList.toggle('d-none', !isMultipleCorrect);
            });
        };

        const createAnswerRow = () => {
            const nextIndex = Number(answerList.dataset.nextIndex || 0);
            const visibleCount = answerList.querySelectorAll('.answer-row').length;
            const label = String.fromCharCode(65 + visibleCount);

            const html = answerTemplate.innerHTML
                .replaceAll('__INDEX__', nextIndex)
                .replaceAll('__LABEL__', label);

            answerList.insertAdjacentHTML('beforeend', html);
            answerList.dataset.nextIndex = String(nextIndex + 1);
            syncAnswerPanels();
        };

        const removeAnswerRow = (button) => {
            const row = button.closest('.answer-row');
            if (!row) {
                return;
            }

            if (answerList.querySelectorAll('.answer-row').length <= 2) {
                return;
            }

            row.remove();
        };

        courseSelect.addEventListener('change', syncModules);
        questionTypeSelect.addEventListener('change', syncAnswerPanels);
        answerModeSelect.addEventListener('change', syncAnswerPanels);
        addAnswerButton.addEventListener('click', createAnswerRow);

        answerList.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-answer-btn');
            if (!removeButton) {
                return;
            }

            removeAnswerRow(removeButton);
        });

        syncModules();
        syncAnswerPanels();
    });
</script>
@endpush
@endsection
