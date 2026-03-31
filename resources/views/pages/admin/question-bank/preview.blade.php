@extends('layouts.app')

@section('title', 'Xem trước import câu hỏi')

@php
    $summary = $preview['summary'];
    $duplicateCount = ($summary['duplicate_file'] ?? 0) + ($summary['duplicate_db'] ?? 0);
    $errorCount = (int) ($summary['error'] ?? 0);
    $manualReviewCount = (int) ($summary['needs_review'] ?? 0);
    $issueCount = $duplicateCount + $errorCount;
    $profileLabel = match ($preview['profile'] ?? null) {
        'question_bank_mcq' => 'Mẫu Excel mới',
        'question_bank_mcq_csv' => 'CSV legacy tương thích',
        'question_document_docx' => 'Tài liệu Word .docx',
        'question_document_pdf_text' => 'PDF text-based',
        default => 'Không xác định',
    };
    $courseTypeLabel = $preview['course_type_label'] ?? 'Chưa xác định';
    $courseTypeColor = $preview['course_type_color'] ?? 'secondary';
@endphp

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-muted small mb-1">
                        <i class="fas fa-home me-1"></i> Admin > Kiểm tra Online > Ngân hàng câu hỏi > Xem trước import
                    </div>
                    <h3 class="fw-bold mb-0 text-primary">Xem trước dữ liệu import</h3>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.export-preview', ['scope' => 'all']) }}" class="btn btn-outline-success px-4">
                        <i class="fas fa-file-excel me-2"></i> Xuất toàn bộ
                    </a>
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.export-preview', ['scope' => 'valid']) }}" class="btn btn-outline-primary px-4 {{ $summary['valid'] === 0 ? 'disabled' : '' }}">
                        <i class="fas fa-check-circle me-2"></i> Xuất hợp lệ
                    </a>
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.export-preview', ['scope' => 'error']) }}" class="btn btn-outline-warning px-4 {{ $issueCount === 0 ? 'disabled' : '' }}">
                        <i class="fas fa-exclamation-triangle me-2"></i> Xuất dòng lỗi
                    </a>
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn btn-light border px-4">
                        <i class="fas fa-times me-2"></i> Hủy bỏ
                    </a>
                </div>
            </div>
        </div>
    </div>

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

    <div class="card vip-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 text-center">
                <div class="col-xl-3 col-md-6">
                    <div class="bg-light rounded-4 p-3 h-100 border">
                        <div class="text-muted text-uppercase small fw-bold mb-2">Khóa học</div>
                        <div class="fw-bold text-primary">{{ $preview['khoa_hoc_ten'] }}</div>
                        <div class="mt-2">
                            <span class="badge bg-{{ $courseTypeColor }} rounded-pill px-3">{{ $courseTypeLabel }}</span>
                        </div>
                        @if(!empty($preview['module_hoc_ten']))
                            <div class="small text-muted mt-1">Module: {{ $preview['module_hoc_ten'] }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="bg-light rounded-4 p-3 h-100 border">
                        <div class="text-muted text-uppercase small fw-bold mb-2">File nguồn</div>
                        <div class="fw-bold text-break text-primary">{{ $preview['original_name'] ?? 'Không rõ tên file' }}</div>
                        <div class="small text-muted mt-1">{{ strtoupper($preview['source_format'] ?? 'n/a') }} - {{ $profileLabel }}</div>
                    </div>
                </div>
                <div class="col-xl col-md-4">
                    <div class="bg-white rounded-4 p-3 h-100 border shadow-sm">
                        <div class="text-muted text-uppercase small fw-bold mb-1">Tổng cộng</div>
                        <div class="h2 fw-bold mb-0 text-dark">{{ $summary['total'] }}</div>
                    </div>
                </div>
                <div class="col-xl col-md-4">
                    <div class="bg-success-subtle rounded-4 p-3 h-100 border border-success-subtle shadow-sm">
                        <div class="text-success text-uppercase small fw-bold mb-1">Hợp lệ</div>
                        <div class="h2 fw-bold mb-0 text-success">{{ $summary['valid'] }}</div>
                    </div>
                </div>
                <div class="col-xl col-md-4">
                    <div class="bg-warning-subtle rounded-4 p-3 h-100 border border-warning-subtle shadow-sm">
                        <div class="text-warning-emphasis text-uppercase small fw-bold mb-1">Trùng lặp</div>
                        <div class="h2 fw-bold mb-0 text-warning-emphasis">{{ $duplicateCount }}</div>
                    </div>
                </div>
                <div class="col-xl col-md-6">
                    <div class="bg-danger-subtle rounded-4 p-3 h-100 border border-danger-subtle shadow-sm">
                        <div class="text-danger text-uppercase small fw-bold mb-1">Lỗi dữ liệu</div>
                        <div class="h2 fw-bold mb-0 text-danger">{{ $errorCount }}</div>
                    </div>
                </div>
                <div class="col-xl col-md-6">
                    <div class="bg-info-subtle rounded-4 p-3 h-100 border border-info-subtle shadow-sm">
                        <div class="text-info text-uppercase small fw-bold mb-1">Cần kiểm tra</div>
                        <div class="h2 fw-bold mb-0 text-info">{{ $manualReviewCount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card vip-card border-0">
        <div class="card-body p-4">
            <div class="table-responsive rounded-4 border overflow-hidden" style="max-height: 620px; overflow-y: auto;">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="70" class="text-center border-0">STT</th>
                            <th width="90" class="text-center border-0">Dòng</th>
                            <th width="180" class="border-0">Trạng thái</th>
                            <th class="border-0">Nội dung câu hỏi</th>
                            <th width="320" class="border-0">Đáp án</th>
                            <th width="220" class="border-0">Đáp án đúng</th>
                            <th width="260" class="border-0">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($preview['data'] as $index => $row)
                            @php
                                $rowStatus = $row['status'];
                                $validationStatus = $row['validation_status'] ?? $rowStatus;
                                $badgeClass = match ($rowStatus) {
                                    'hop_le' => 'bg-success',
                                    'trung_lap_trong_file', 'trung_lap_trong_he_thong' => 'bg-warning text-dark',
                                    default => 'bg-danger',
                                };
                                $statusLabel = match ($validationStatus) {
                                    'hop_le' => 'Hợp lệ',
                                    'trung_trong_file' => 'Trùng trong file',
                                    'trung_trong_he_thong' => 'Trùng hệ thống',
                                    'thieu_cau_hoi', 'thieu_noi_dung' => 'Thiếu câu hỏi',
                                    'thieu_dap_an' => 'Thiếu đáp án',
                                    'khong_du_4_dap_an' => 'Không đủ 4 đáp án',
                                    'trung_dap_an' => 'Trùng đáp án',
                                    'khong_xac_dinh_dap_an_dung' => 'Chưa xác định ĐA đúng',
                                    'dap_an_dung_khong_khop' => 'ĐA đúng không khớp',
                                    'nhieu_hon_mot_dap_an_dung' => 'Nhiều ĐA đúng',
                                    'khong_ho_tro_loai_cau_hoi' => 'Chưa hỗ trợ loại',
                                    'khong_ho_tro_pdf_scan' => 'PDF scan chưa hỗ trợ',
                                    'sai_dinh_dang' => 'Sai định dạng',
                                    default => 'Lỗi dữ liệu',
                                };
                            @endphp
                            <tr class="{{ $rowStatus === 'hop_le' ? '' : 'bg-light bg-opacity-50' }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center fw-bold text-muted">{{ $row['line'] ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 w-100">{{ $statusLabel }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row['noi_dung_cau_hoi'] ?: 'Dòng trống' }}</div>
                                </td>
                                <td class="small">
                                    @forelse(($row['answers'] ?? []) as $answerIndex => $answer)
                                        <div class="mb-1 d-flex gap-2">
                                            <span class="fw-bold text-primary">{{ $answer['ky_hieu'] ?? ('Đáp án ' . ($answerIndex + 1)) }}.</span>
                                            <span class="{{ !empty($answer['is_dap_an_dung']) ? 'fw-bold text-success' : '' }}">
                                                {{ $answer['noi_dung'] ?: '---' }}
                                            </span>
                                            @if(!empty($answer['is_dap_an_dung']))
                                                <i class="fas fa-check text-success small align-self-center"></i>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-muted fst-italic">---</span>
                                    @endforelse
                                </td>
                                <td class="small">
                                    <span class="badge bg-{{ !empty($row['dap_an_dung']) ? 'success' : 'warning text-dark' }} rounded-pill px-3 py-2">
                                        {{ $row['dap_an_dung'] ?: 'Chưa xác định' }}
                                    </span>
                                </td>
                                <td class="small {{ $row['note'] ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    {{ $row['note'] ?: 'Sẵn sàng để import.' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-20"></i>
                                    <p class="mb-0">Không có dữ liệu xem trước.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mt-5 p-4 bg-light rounded-4 border-0">
                <div class="text-muted small flex-grow-1" style="max-width: 860px;">
                    <i class="fas fa-info-circle me-1 text-primary"></i>
                    Chỉ các dòng có trạng thái <span class="badge bg-success rounded-pill px-2">Hợp lệ</span> mới được lưu vào hệ thống.
                    Nếu parser chưa chắc đáp án đúng, dòng bị trùng, hoặc dữ liệu chưa đủ 4 đáp án, bạn có thể xuất ra file Excel chuẩn để sửa tay rồi import lại.
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.export-preview', ['scope' => 'all']) }}" class="btn btn-outline-success px-4">
                        <i class="fas fa-file-excel me-2"></i> Xuất Excel
                    </a>
                    <form action="{{ route('admin.kiem-tra-online.cau-hoi.confirm-import') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn vip-btn vip-btn-primary px-5 shadow-sm" @disabled($summary['valid'] === 0)>
                            <i class="fas fa-check-circle me-2"></i> Xác nhận import {{ $summary['valid'] }} câu hợp lệ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
