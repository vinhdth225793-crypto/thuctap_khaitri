@extends('layouts.app', ['title' => 'Lập phiếu xét duyệt kết quả'])

@section('content')
@php
    $selectedIdSet = collect($selectedIds)->map(fn ($id) => (int) $id)->all();
    $isFinalMode = $mode === \App\Models\PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE;
@endphp

<div class="container-fluid py-4">
    {{-- Header & Stats --}}
    <div class="row g-4 mb-4 align-items-center">
        <div class="col-xl-7">
            <div class="d-flex align-items-center gap-3 mb-3">
                <a href="{{ route('giang-vien.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-sm btn-white shadow-sm border rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-arrow-left text-muted"></i>
                </a>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc') }}">Khóa học</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc.show', $khoaHoc->id) }}">{{ $khoaHoc->ten_khoa_hoc }}</a></li>
                        <li class="breadcrumb-item active">Xét duyệt kết quả</li>
                    </ol>
                </nav>
            </div>
            <h2 class="fw-bold mb-1 text-dark">
                <i class="fas fa-file-signature text-primary me-2"></i>Phiếu xét duyệt cuối khóa
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>Công thức: <strong>80% Kiểm tra + 20% Điểm danh</strong>. 
                Vui lòng chọn bài kiểm tra và nhấn "Cập nhật preview" trước khi gửi.
            </p>
        </div>
        <div class="col-xl-5">
            <div class="row g-3">
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-body p-3 text-center bg-white border-bottom border-primary border-4">
                            <div class="small text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Học viên</div>
                            <div class="fs-4 fw-bold text-dark">{{ $preview['summary']['student_count'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-body p-3 text-center bg-white border-bottom border-info border-4">
                            <div class="small text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Đủ dữ liệu</div>
                            <div class="fs-4 fw-bold text-info">{{ $preview['summary']['ready_count'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-body p-3 text-center bg-white border-bottom border-success border-4">
                            <div class="small text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Tạm đạt</div>
                            <div class="fs-4 fw-bold text-success">{{ $preview['summary']['passed_count'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    {{-- Configuration Form --}}
    <form method="GET" action="{{ route('giang-vien.xet-duyet-ket-qua.show', $khoaHoc->id) }}" id="reviewForm" class="mb-4">
        @csrf
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-layer-group text-primary me-2"></i>Phương án xét duyệt</h6>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <div class="d-flex flex-column gap-3">
                            <label class="selection-box border rounded-4 p-3 mb-0 transition-all cursor-pointer @if($isFinalMode) active bg-primary-soft border-primary @endif" data-target="final-exams-container">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="form-check m-0 p-0">
                                        <input class="form-check-input ms-0 mt-1 mode-selector" type="radio" name="phuong_an" value="final_exam_attendance" {{ $isFinalMode ? 'checked' : '' }}>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-bold d-block text-dark mb-1">Cuối khóa + điểm danh</span>
                                        <span class="small text-muted d-block lh-sm">Sử dụng điểm từ một bài kiểm tra cuối khóa.</span>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="selection-box border rounded-4 p-3 mb-0 transition-all cursor-pointer @if(!$isFinalMode) active bg-primary-soft border-primary @endif" data-target="component-exams-container">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="form-check m-0 p-0">
                                        <input class="form-check-input ms-0 mt-1 mode-selector" type="radio" name="phuong_an" value="selected_exams_attendance" {{ ! $isFinalMode ? 'checked' : '' }}>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-bold d-block text-dark mb-1">Module/buổi + điểm danh</span>
                                        <span class="small text-muted d-block lh-sm">Tính trung bình từ nhiều bài thành phần.</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                {{-- Final Exams Section --}}
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden exam-section @if(!$isFinalMode) d-none @endif" id="final-exams-container">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-trophy text-warning me-2"></i>Chọn bài kiểm tra cuối khóa</h6>
                        <span class="badge bg-soft-warning text-warning rounded-pill px-2">Bắt buộc chọn 1</span>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <div class="row g-2 exam-selection-list custom-scrollbar" style="max-height: 220px; overflow-y: auto;">
                            @forelse($examGroups['final_exams'] as $exam)
                                <div class="col-md-6">
                                    <label class="d-flex gap-3 align-items-start p-3 rounded-4 hover-bg-light border cursor-pointer h-100 transition-all">
                                        <div class="form-check p-0 m-0">
                                            <input class="form-check-input ms-0 mt-1" type="radio" name="bai_kiem_tra_ids[]" value="{{ $exam->id }}" {{ in_array((int) $exam->id, $selectedIdSet, true) ? 'checked' : '' }} @if(!$isFinalMode) disabled @endif>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block text-dark small">{{ $exam->tieu_de }}</span>
                                            <span class="smaller text-muted d-block mt-1">
                                                Thang điểm: <strong>{{ number_format((float) $exam->tong_diem, 1) }}</strong>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fas fa-calendar-times fa-3x text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted mb-0">Chưa có bài kiểm tra cuối khóa nào đã phát hành.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Component Exams Section --}}
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden exam-section @if($isFinalMode) d-none @endif" id="component-exams-container">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-tasks text-info me-2"></i>Chọn các bài module / buổi học</h6>
                        <span class="badge bg-soft-info text-info rounded-pill px-2">Chọn ít nhất 1</span>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <div class="row g-2 exam-selection-list custom-scrollbar" style="max-height: 220px; overflow-y: auto;">
                            @forelse($examGroups['selectable_exams'] as $exam)
                                <div class="col-md-6">
                                    <label class="d-flex gap-3 align-items-start p-3 rounded-4 hover-bg-light border cursor-pointer h-100 transition-all">
                                        <div class="form-check p-0 m-0">
                                            <input class="form-check-input ms-0 mt-1" type="checkbox" name="bai_kiem_tra_ids[]" value="{{ $exam->id }}" {{ in_array((int) $exam->id, $selectedIdSet, true) ? 'checked' : '' }} @if($isFinalMode) disabled @endif>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block text-dark small">{{ $exam->tieu_de }}</span>
                                            <span class="smaller text-muted mt-1 d-block">
                                                {{ $exam->moduleHoc?->ten_module ?? 'Toàn khóa' }} 
                                                <span class="badge bg-light text-muted fw-normal ms-1">{{ $exam->loai_bai_kiem_tra_label }}</span>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted mb-0">Chưa có bài kiểm tra thành phần nào đã phát hành.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4 overflow-hidden border-start border-4 border-primary">
            <div class="card-body p-4">
                <div class="row align-items-end g-3">
                    <div class="col-lg-8">
                        <label class="form-label fw-bold text-dark small"><i class="fas fa-comment-dots me-1"></i>Ghi chú gửi Admin phê duyệt</label>
                        <textarea name="ghi_chu" rows="2" class="form-control border-2 shadow-none rounded-3" placeholder="Nhập ghi chú quan trọng nếu có cho kỳ xét duyệt này...">{{ old('ghi_chu', $latestTicket?->ghi_chu) }}</textarea>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2 rounded-3 shadow-sm">
                                <i class="fas fa-sync-alt me-2"></i>CẬP NHẬT PREVIEW
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white p-3 border-0 d-flex gap-3 flex-wrap justify-content-center">
                <button type="submit" id="btn-save-draft" formmethod="POST" formaction="{{ route('giang-vien.xet-duyet-ket-qua.store-draft', $khoaHoc->id) }}" class="btn btn-outline-secondary px-4 fw-bold rounded-pill">
                    <i class="fas fa-save me-2"></i>LƯU NHÁP
                </button>
                <button type="submit" id="btn-submit-final" formmethod="POST" formaction="{{ route('giang-vien.xet-duyet-ket-qua.submit', $khoaHoc->id) }}" class="btn btn-success px-5 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-paper-plane me-2"></i>GỬI ADMIN PHÊ DUYỆT
                </button>
            </div>
        </div>
    </form>

    {{-- Result Preview Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Bảng kết quả tạm tính</h5>
                <p class="small text-muted mb-0">
                    <i class="fas fa-check-circle text-success me-1"></i>Số bài đang dùng: <strong>{{ $preview['summary']['selected_exam_count'] }}</strong> 
                    <span class="mx-2">|</span>
                    <i class="fas fa-calculator text-primary me-1"></i>Phương án: <span class="badge bg-light text-primary border fw-bold">{{ $isFinalMode ? 'Cuối khóa' : 'Thành phần' }}</span>
                </p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted smaller text-uppercase fw-bold">
                        <th class="ps-4 border-0 py-3">Học viên</th>
                        <th class="text-center border-0 py-3">Điểm danh (20%)</th>
                        <th class="text-center border-0 py-3">Kiểm tra (80%)</th>
                        <th class="text-center border-0 py-3">Tổng điểm xét</th>
                        <th class="text-center border-0 py-3">Kết quả</th>
                        <th class="border-0 py-3">Chi tiết điểm thành phần</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preview['students'] as $row)
                        @php
                            $student = $row['student'];
                            $finalScore = $row['diem_xet_duyet'];
                            $resultStatus = $row['ket_qua'];
                            $badgeClass = match($resultStatus) {
                                'dat' => 'bg-soft-success text-success',
                                'khong_dat' => 'bg-soft-danger text-danger',
                                default => 'bg-soft-secondary text-secondary'
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-sm bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                        {{ substr($student?->ho_ten ?? 'H', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $student?->ho_ten ?? 'N/A' }}</div>
                                        <div class="smaller text-muted">{{ $student?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">{{ $row['attendance']['diem_diem_danh'] !== null ? number_format($row['attendance']['diem_diem_danh'], 2) : '--' }}</div>
                                <div class="smaller text-muted bg-light d-inline-block px-2 rounded-pill">
                                    {{ $row['attendance']['so_buoi_tham_du'] }}/{{ $row['attendance']['tong_so_buoi'] }} buổi
                                </div>
                            </td>
                            <td class="text-center fw-bold text-dark">
                                {{ $row['diem_kiem_tra'] !== null ? number_format($row['diem_kiem_tra'], 2) : '--' }}
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-primary fs-5">{{ $finalScore !== null ? number_format($finalScore, 2) : '--' }}</div>
                                @if($row['missing_exam_count'] > 0)
                                    <div class="smaller text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i>Thiếu {{ $row['missing_exam_count'] }} bài</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 fw-bold" style="letter-spacing: 0.5px;">
                                    {{ $resultStatus === 'dat' ? 'ĐẠT' : ($resultStatus === 'khong_dat' ? 'KHÔNG ĐẠT' : 'CHƯA XÉT') }}
                                </span>
                            </td>
                            <td style="min-width: 280px; font-size: 0.8rem;">
                                <div class="p-2 bg-light rounded-3">
                                    @forelse($row['exam_rows'] as $examRow)
                                        <div class="d-flex justify-content-between align-items-center py-1 @if(!$loop->last) border-bottom border-white @endif">
                                            <span class="text-muted text-truncate me-2" style="max-width: 180px;">{{ $examRow['tieu_de'] }}</span>
                                            <span class="fw-bold {{ $examRow['diem'] !== null ? 'text-dark' : 'text-danger' }}">
                                                {{ $examRow['diem'] !== null ? number_format($examRow['diem'], 2) : 'vắng' }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted smaller italic py-1">Chưa chọn bài dữ liệu.</div>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-users-slash fa-3x opacity-25 mb-3"></i>
                                <p class="mb-0">Chưa có học viên nào tham gia khóa học này.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- History Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-0">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-secondary me-2"></i>Lịch sử các phiếu xét duyệt</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr class="smaller text-muted text-uppercase fw-bold">
                        <th class="ps-4 py-3">Mã phiếu</th>
                        <th class="py-3">Phương án</th>
                        <th class="text-center py-3">Học viên</th>
                        <th class="text-center py-3">Trạng thái</th>
                        <th class="py-3 text-end pe-4">Thời gian cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">#{{ $ticket->id }}</td>
                            <td>
                                <div class="small text-dark">{{ $ticket->phuong_an_label }}</div>
                                @if($ticket->ghi_chu || $ticket->reject_reason)
                                    <div class="smaller text-muted text-truncate" style="max-width: 300px;">
                                        {{ $ticket->reject_reason ?: $ticket->ghi_chu }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $ticket->chi_tiets_count }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $ticket->trang_thai_color }} rounded-pill px-2">{{ $ticket->trang_thai_label }}</span>
                            </td>
                            <td class="small text-muted text-end pe-4">{{ $ticket->updated_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu lịch sử.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .avatar-sm { font-size: 1rem; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.08); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.12); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.12); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.12); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.12); }
    .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.12); }
    .smaller { font-size: 0.75rem; }
    .transition-all { transition: all 0.2s ease; }
    .cursor-pointer { cursor: pointer; }
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .selection-box:hover { border-color: #0d6efd; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .selection-box.active { border-width: 2px !important; }
    
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #bbb; }
    
    .exam-section { transition: all 0.3s ease; }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioModeSelectors = document.querySelectorAll('.mode-selector');
    const examSections = document.querySelectorAll('.exam-section');
    const selectionBoxes = document.querySelectorAll('.selection-box');
    const btnSubmit = document.getElementById('btn-submit-final');
    const btnDraft = document.getElementById('btn-save-draft');

    function toggleSections() {
        const selectedMode = document.querySelector('.mode-selector:checked').value;
        
        selectionBoxes.forEach(box => {
            if (box.dataset.target === (selectedMode === 'final_exam_attendance' ? 'final-exams-container' : 'component-exams-container')) {
                box.classList.add('active', 'bg-primary-soft', 'border-primary');
            } else {
                box.classList.remove('active', 'bg-primary-soft', 'border-primary');
            }
        });

        examSections.forEach(section => {
            const inputs = section.querySelectorAll('input');
            if (section.id === (selectedMode === 'final_exam_attendance' ? 'final-exams-container' : 'component-exams-container')) {
                section.classList.remove('d-none');
                inputs.forEach(input => input.disabled = false);
            } else {
                section.classList.add('d-none');
                inputs.forEach(input => input.disabled = true);
            }
        });
    }

    radioModeSelectors.forEach(radio => {
        radio.addEventListener('change', toggleSections);
    });

    // Initial toggle
    toggleSections();

    // Validation before submit
    function validateSelection(e) {
        const selectedMode = document.querySelector('.mode-selector:checked').value;
        const targetContainer = document.getElementById(selectedMode === 'final_exam_attendance' ? 'final-exams-container' : 'component-exams-container');
        const checkedCount = targetContainer.querySelectorAll('input:checked').length;

        if (checkedCount === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất một bài kiểm tra để tiếp tục.');
            return false;
        }

        if (this.id === 'btn-submit-final' && !confirm('Gửi phiếu xét duyệt kết quả này cho Admin phê duyệt?')) {
            e.preventDefault();
            return false;
        }
    }

    if (btnSubmit) btnSubmit.addEventListener('click', validateSelection);
    if (btnDraft) btnDraft.addEventListener('click', validateSelection);
});
</script>
@endpush
@endsection
