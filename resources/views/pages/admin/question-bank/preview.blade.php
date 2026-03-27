@extends('layouts.app')

@section('title', 'Xem trước import câu hỏi')

@php
    $summary = $preview['summary'];
    $duplicateCount = $summary['duplicate_file'] + $summary['duplicate_db'];
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
                    <h3 class="fw-bold mb-0">Xem trước dữ liệu import</h3>
                </div>
                <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn btn-light border">
                    <i class="fas fa-times me-1"></i> Hủy
                </a>
            </div>
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

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted text-uppercase small fw-semibold mb-1">Khóa học</div>
                        <div class="fw-bold">{{ $preview['khoa_hoc_ten'] }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted text-uppercase small fw-semibold mb-1">Tổng dòng</div>
                        <div class="display-6 fw-bold mb-0">{{ $summary['total'] }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="border rounded-3 p-3 h-100 bg-success-subtle border-success-subtle">
                        <div class="text-success text-uppercase small fw-semibold mb-1">Hợp lệ</div>
                        <div class="display-6 fw-bold mb-0 text-success">{{ $summary['valid'] }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="border rounded-3 p-3 h-100 bg-warning-subtle border-warning-subtle">
                        <div class="text-warning-emphasis text-uppercase small fw-semibold mb-1">Trùng lặp</div>
                        <div class="display-6 fw-bold mb-0 text-warning-emphasis">{{ $duplicateCount }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-danger-subtle border-danger-subtle">
                        <div class="text-danger text-uppercase small fw-semibold mb-1">Lỗi dữ liệu</div>
                        <div class="display-6 fw-bold mb-0 text-danger">{{ $summary['error'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 560px; overflow-y: auto;">
                <table class="table align-middle table-bordered mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="70" class="text-center">STT</th>
                            <th width="150">Trạng thái</th>
                            <th>Nội dung câu hỏi</th>
                            <th width="320">Đáp án</th>
                            <th width="260">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preview['data'] as $index => $row)
                            @php
                                $rowStatus = $row['status'];
                                $badgeClass = match ($rowStatus) {
                                    'hop_le' => 'bg-success',
                                    'trung_lap_trong_file', 'trung_lap_trong_he_thong' => 'bg-warning text-dark',
                                    default => 'bg-danger',
                                };
                                $statusLabel = match ($rowStatus) {
                                    'hop_le' => 'Hợp lệ',
                                    'trung_lap_trong_file' => 'Trùng trong file',
                                    'trung_lap_trong_he_thong' => 'Trùng trong hệ thống',
                                    default => 'Lỗi dữ liệu',
                                };
                            @endphp
                            <tr class="{{ $rowStatus === 'hop_le' ? '' : 'table-light' }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><span class="badge {{ $badgeClass }} w-100 py-2">{{ $statusLabel }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $row['noi_dung_cau_hoi'] ?: 'Dòng trống' }}</div>
                                </td>
                                <td class="small">
                                    <div><span class="fw-semibold text-success">Đúng:</span> {{ $row['dap_an_dung'] ?: '---' }}</div>
                                    <div><span class="text-muted">Sai 1:</span> {{ $row['dap_an_sai_1'] ?: '---' }}</div>
                                    <div><span class="text-muted">Sai 2:</span> {{ $row['dap_an_sai_2'] ?: '---' }}</div>
                                    <div><span class="text-muted">Sai 3:</span> {{ $row['dap_an_sai_3'] ?: '---' }}</div>
                                </td>
                                <td class="small {{ $row['note'] ? 'text-danger' : 'text-muted' }}">
                                    {{ $row['note'] ?: 'Sẵn sàng để import.' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Không có dữ liệu preview.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 p-3 bg-light rounded-3">
                <div class="text-muted">
                    Chỉ các dòng có trạng thái <span class="badge bg-success">Hợp lệ</span> mới được lưu vào hệ thống.
                </div>
                <form action="{{ route('admin.kiem-tra-online.cau-hoi.confirm-import') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success px-4" @disabled($summary['valid'] === 0)>
                        <i class="fas fa-check-circle me-1"></i> Xác nhận import {{ $summary['valid'] }} câu hợp lệ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
