@extends('layouts.app')

@section('title', 'Ngân hàng câu hỏi trắc nghiệm')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-muted small mb-2">
            <i class="fas fa-home me-1"></i> Admin > Kiểm tra Online > Ngân hàng câu hỏi
        </div>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary"><i class="fas fa-question-circle me-2"></i>Ngân hàng câu hỏi trắc nghiệm</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.kiem-tra-online.cau-hoi.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Thêm mới thủ công
                        </a>
                        <a href="{{ route('admin.kiem-tra-online.cau-hoi.template') }}" class="btn btn-outline-success">
                            <i class="fas fa-file-download me-1"></i>Tải file mẫu
                        </a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-1"></i>Import Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Bộ lọc -->
                    <form action="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Khóa học</label>
                            <select name="khoa_hoc_id" class="form-select" onchange="this.form.submit()">
                                <option value="">--- Tất cả khóa học ---</option>
                                @foreach($khoaHocs as $kh)
                                    <option value="{{ $kh->id }}" {{ request('khoa_hoc_id') == $kh->id ? 'selected' : '' }}>
                                        [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tìm kiếm nội dung</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Nhập nội dung câu hỏi..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn btn-light w-100">Xóa lọc</a>
                        </div>
                    </form>

                    <!-- Danh sách -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">STT</th>
                                    <th>Nội dung câu hỏi</th>
                                    <th width="200">Khóa học</th>
                                    <th>Đáp án</th>
                                    <th width="120" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cauHois as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $cauHois->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-bold text-dark mb-1">{{ Str::limit($item->noi_dung_cau_hoi, 150) }}</div>
                                        <small class="text-muted">Người tạo: {{ $item->nguoiTao->ho_ten ?? 'N/A' }} | {{ $item->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $item->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="text-success"><i class="fas fa-check-circle me-1"></i><strong>Đúng:</strong> {{ $item->dap_an_dung }}</div>
                                            <div class="text-danger"><i class="fas fa-times-circle me-1"></i><strong>Sai 1:</strong> {{ $item->dap_an_sai_1 }}</div>
                                            <div class="text-danger"><i class="fas fa-times-circle me-1"></i><strong>Sai 2:</strong> {{ $item->dap_an_sai_2 }}</div>
                                            <div class="text-danger"><i class="fas fa-times-circle me-1"></i><strong>Sai 3:</strong> {{ $item->dap_an_sai_3 }}</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.kiem-tra-online.cau-hoi.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.kiem-tra-online.cau-hoi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa câu hỏi này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Không tìm thấy câu hỏi nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="mt-4">
                        {{ $cauHois->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.kiem-tra-online.cau-hoi.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import câu hỏi từ Excel (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn khóa học <span class="text-danger">*</span></label>
                        <select name="khoa_hoc_id" class="form-select" required>
                            <option value="">--- Chọn khóa học ---</option>
                            @foreach($khoaHocs as $kh)
                                <option value="{{ $kh->id }}">{{ $kh->ten_khoa_hoc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file_import" class="form-control" accept=".csv" required>
                        <div class="form-text mt-2">
                            Hãy tải <a href="{{ route('admin.kiem-tra-online.cau-hoi.template') }}">file mẫu</a> để nhập đúng định dạng.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-eye me-1"></i>Xem trước dữ liệu
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
