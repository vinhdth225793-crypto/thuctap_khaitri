@extends('layouts.app')

@section('title', 'Xem trước dữ liệu Import')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-muted small mb-2">
            <i class="fas fa-home me-1"></i> Admin > Ngân hàng câu hỏi > Import > Xem trước
        </div>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary"><i class="fas fa-file-import me-2"></i>Xem trước dữ liệu chuẩn bị Import</h5>
                    <div>
                        <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn btn-light border">Hủy bỏ</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <div class="row text-center">
                            <div class="col-md-2 border-end">
                                <div class="small text-muted text-uppercase fw-bold">Khóa học</div>
                                <div class="fw-bold text-dark">{{ $preview['khoa_hoc_ten'] }}</div>
                            </div>
                            <div class="col-md-2 border-end">
                                <div class="small text-muted text-uppercase fw-bold">Tổng số dòng</div>
                                <div class="h4 mb-0 fw-bold">{{ $preview['summary']['total'] }}</div>
                            </div>
                            <div class="col-md-2 border-end">
                                <div class="small text-success text-uppercase fw-bold">Hợp lệ (Sẽ lưu)</div>
                                <div class="h4 mb-0 fw-bold text-success">{{ $preview['summary']['valid'] }}</div>
                            </div>
                            <div class="col-md-2 border-end">
                                <div class="small text-warning text-uppercase fw-bold">Trùng lặp</div>
                                <div class="h4 mb-0 fw-bold text-warning">{{ $preview['summary']['duplicate_file'] + $preview['summary']['duplicate_db'] }}</div>
                            </div>
                            <div class="col-md-2">
                                <div class="small text-danger text-uppercase fw-bold">Lỗi dữ liệu</div>
                                <div class="h4 mb-0 fw-bold text-danger">{{ $preview['summary']['error'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered align-middle small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="50" class="text-center">STT</th>
                                    <th width="120">Trạng thái</th>
                                    <th>Nội dung câu hỏi</th>
                                    <th>Đáp án</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview['data'] as $index => $row)
                                <tr class="{{ $row['status'] === 'hop_le' ? '' : 'table-light' }}">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        @if($row['status'] === 'hop_le')
                                            <span class="badge bg-success w-100">Hợp lệ</span>
                                        @elseif($row['status'] === 'trung_lap_trong_file' || $row['status'] === 'trung_lap_trong_he_thong')
                                            <span class="badge bg-warning text-dark w-100">Trùng lặp</span>
                                        @else
                                            <span class="badge bg-danger w-100">Lỗi</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $row['noi_dung_cau_hoi'] }}</div>
                                    </td>
                                    <td>
                                        <div><span class="text-success fw-bold">Đúng:</span> {{ $row['dap_an_dung'] }}</div>
                                        <div><span class="text-muted small">Sai 1:</span> {{ $row['dap_an_sai_1'] }}</div>
                                        <div><span class="text-muted small">Sai 2:</span> {{ $row['dap_an_sai_2'] }}</div>
                                        <div><span class="text-muted small">Sai 3:</span> {{ $row['dap_an_sai_3'] }}</div>
                                    </td>
                                    <td class="text-danger small italic">
                                        {{ $row['note'] }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>Chỉ những dòng có trạng thái <span class="badge bg-success">Hợp lệ</span> mới được lưu vào hệ thống.
                        </div>
                        <form action="{{ route('admin.kiem-tra-online.cau-hoi.confirm-import') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg px-5" {{ $preview['summary']['valid'] == 0 ? 'disabled' : '' }}>
                                <i class="fas fa-check-circle me-2"></i>Xác nhận Import ({{ $preview['summary']['valid'] }} câu)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
