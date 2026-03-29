@extends('layouts.app')

@section('title', 'Xem trước import câu hỏi')

@php
    $summary = $preview['summary'];
    $duplicateCount = $summary['duplicate_file'] + $summary['duplicate_db'];
    $manualReviewCount = $summary['needs_review'] ?? 0;
    $otherErrorCount = max(0, $summary['error'] - $manualReviewCount);
    $profileLabel = match ($preview['profile'] ?? null) {
        'question_bank_mcq' => 'Mẫu Excel mới',
        'question_bank_mcq_csv' => 'CSV legacy tương thích',
        'question_document_docx' => 'Tài liệu Word .docx',
        'question_document_pdf_text' => 'PDF text-based',
        default => 'Không xác định',
    };
@endphp

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-muted small mb-1">
                        <i class="fas fa-home me-1"></i> Admin > Kiểm tra Online > Ngân hàng câu hỏi > Preview import
                    </div>
                    <h3 class="fw-bold mb-0 text-primary">Xem trước dữ liệu import</h3>
                </div>
                <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn vip-btn btn-light border px-4">
                    <i class="fas fa-times me-2"></i> Hủy bỏ
                </a>
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
            <div class="row g-4 text-center">
                <div class="col-lg-3 col-md-6">
                    <div class="bg-light rounded-4 p-3 h-100 border">
                        <div class="text-muted text-uppercase small fw-bold mb-2">Khóa học</div>
                        <div class="fw-bold text-primary">{{ $preview['khoa_hoc_ten'] }}</div>
                        @if(!empty($preview['module_hoc_ten']))
                            <div class="small text-muted mt-1 opacity-75">Module: {{ $preview['module_hoc_ten'] }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="bg-light rounded-4 p-3 h-100 border">
                        <div class="text-muted text-uppercase small fw-bold mb-2">File nguồn</div>
                        <div class="fw-bold text-break text-primary">{{ $preview['original_name'] ?? 'Không rõ tên file' }}</div>
                        <div class="small text-muted mt-1 opacity-75">{{ strtoupper($preview['source_format'] ?? 'n/a') }} - {{ $profileLabel }}</div>
                    </div>
                </div>
                
                <div class="col-lg col-md-4">
                    <div class="bg-white rounded-4 p-3 h-100 border shadow-sm">
                        <div class="text-muted text-uppercase small fw-bold mb-1">Tổng cộng</div>
                        <div class="h2 fw-bold mb-0 text-dark">{{ $summary['total'] }}</div>
                    </div>
                </div>
                <div class="col-lg col-md-4">
                    <div class="bg-success-subtle rounded-4 p-3 h-100 border border-success-subtle shadow-sm">
                        <div class="text-success text-uppercase small fw-bold mb-1">Hợp lệ</div>
                        <div class="h2 fw-bold mb-0 text-success">{{ $summary['valid'] }}</div>
                    </div>
                </div>
                <div class="col-lg col-md-4">
                    <div class="bg-warning-subtle rounded-4 p-3 h-100 border border-warning-subtle shadow-sm">
                        <div class="text-warning-emphasis text-uppercase small fw-bold mb-1">Trùng / Lỗi</div>
                        <div class="h2 fw-bold mb-0 text-warning-emphasis">{{ $duplicateCount }}</div>
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
                            <th width="160" class="border-0">Trạng thái</th>
                            <th class="border-0">Nội dung câu hỏi</th>
                            <th width="320" class="border-0">Đáp án</th>
                            <th width="220" class="border-0">Đúng</th>
                            <th width="250" class="border-0">Ghi chú</th>
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
                                    'thieu_noi_dung' => 'Thiếu nội dung',
                                    'thieu_dap_an' => 'Thiếu đáp án',
                                    'it_hon_so_dap_an_toi_thieu' => 'Thiếu SL đáp án',
                                    'khong_xac_dinh_dap_an_dung' => 'Chưa có ĐA đúng',
                                    'nhieu_hon_mot_dap_an_dung' => 'Nhiều ĐA đúng',
                                    'khong_ho_tro_loai_cau_hoi' => 'Chưa hỗ trợ loại',
                                    'sai_dinh_dang' => 'Sai định dạng',
                                    default => 'Lỗi dữ liệu',
                                };
                            @endphp
                            <tr class="{{ $rowStatus === 'hop_le' ? '' : 'bg-light bg-opacity-50' }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center fw-bold text-muted">{{ $row['line'] ?? '-' }}</td>
                                <td><span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 w-100">{{ $statusLabel }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row['noi_dung_cau_hoi'] ?: 'Dòng trống' }}</div>
                                </td>
                                <td class="small">
                                    @foreach(($row['answers'] ?? []) as $answerIndex => $answer)
                                        <div class="mb-1 d-flex gap-2">
                                            <span class="fw-bold text-primary">{{ $answer['ky_hieu'] ?? ('Đáp án ' . ($answerIndex + 1)) }}.</span>
                                            <span class="{{ !empty($answer['is_dap_an_dung']) ? 'fw-bold text-success' : '' }}">
                                                {{ $answer['noi_dung'] ?: '---' }}
                                            </span>
                                            @if(!empty($answer['is_dap_an_dung']))
                                                <i class="fas fa-check text-success small align-self-center"></i>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if(empty($row['answers'] ?? []))
                                        <span class="text-muted italic">---</span>
                                    @endif
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
                                    <p>Không có dữ liệu xem trước.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mt-5 p-4 bg-light rounded-4 border-0">
                <div class="text-muted small flex-grow-1" style="max-width: 800px;">
                    <i class="fas fa-info-circle me-1 text-primary"></i> 
                    Chỉ các dòng có trạng thái <span class="badge bg-success rounded-pill px-2">Hợp lệ</span> mới được lưu vào hệ thống. Các dòng chưa xác định được đáp án đúng hoặc sai định dạng sẽ được giữ lại ở preview để admin kiểm tra.
                </div>
                <form action="{{ route('admin.confirm-import') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn vip-btn vip-btn-primary px-5 shadow-sm" @disabled($summary['valid'] === 0)>
                        <i class="fas fa-check-circle me-2"></i> Xác nhận import {{ $summary['valid'] }} câu hợp lệ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
