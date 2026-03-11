@extends('layouts.app')

@section('title', 'Giảng Viên - Cài Đặt Hệ Thống')

@section('content')
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <strong><i class="fas fa-exclamation-circle me-2"></i> Có lỗi xảy ra!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i> Giảng Viên Hiển Thị Trên Trang Chủ
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i> Chọn những giảng viên nổi bật để hiển thị trên trang chủ. Những giảng viên được chọn sẽ xuất hiện trong phần "Giảng Viên Nổi Bật".
                    </div>

                    <form action="{{ route('admin.settings.instructors.save') }}" method="POST">
                        @csrf

                        @if($instructors->count() > 0)
                        <div class="table-responsive mb-4">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">
                                            <input type="checkbox" class="form-check-input" id="select-all" />
                                        </th>
                                        <th style="width: 60px;">Ảnh</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Chuyên ngành</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($instructors as $gv)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input instructor-checkbox"
                                                   name="instructors[]" value="{{ $gv->id }}"
                                                   {{ $gv->hien_thi_trang_chu ? 'checked' : '' }} />
                                        </td>
                                        <td>
                                            @if($gv->avatar_url)
                                                <img src="{{ $gv->avatar_url }}" alt="{{ $gv->nguoiDung ? $gv->nguoiDung->ho_ten : 'N/A' }}"
                                                     class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;">
                                            @else
                                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center text-muted"
                                                     style="width: 40px; height: 40px; border: 1px solid #ddd;">
                                                    <i class="fas fa-user-tie"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $gv->nguoiDung ? $gv->nguoiDung->ho_ten : 'N/A' }}</td>
                                        <td>{{ $gv->nguoiDung ? $gv->nguoiDung->email : 'N/A' }}</td>
                                        <td>{{ $gv->chuyen_nganh ?? 'N/A' }}</td>
                                        <td>
                                            @if($gv->hien_thi_trang_chu)
                                                <span class="badge bg-success">Hiển thị</span>
                                            @else
                                                <span class="badge bg-secondary">Ẩn</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.settings') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-save me-2"></i> Lưu Chọn Giảng Viên
                            </button>
                        </div>
                        @else
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> Chưa có giảng viên nào trong hệ thống. Vui lòng tạo giảng viên trước.
                        </div>
                        <div class="text-center">
                            <a href="{{ route('admin.settings') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Quay lại
                            </a>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    const instructorCheckboxes = document.querySelectorAll('.instructor-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            instructorCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update select-all checkbox when individual checkboxes change
        instructorCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(instructorCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(instructorCheckboxes).some(cb => cb.checked);

                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
    }

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert-success').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
</script>
@endpush

@endsection