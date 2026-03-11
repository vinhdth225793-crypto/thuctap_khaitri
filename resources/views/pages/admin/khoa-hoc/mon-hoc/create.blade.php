@extends('layouts.app')

@section('title', 'Thêm môn học')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="vip-card">
                <div class="vip-card-header">
                    <h5 class="vip-card-title">
                        <i class="fas fa-plus"></i> Thêm môn học mới
                    </h5>
                </div>
                <div class="vip-card-body">
                    <form action="{{ route('admin.mon-hoc.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation">
                        @csrf

                        <div class="mb-3">
                            <label for="ten_mon_hoc" class="form-label">
                                <i class="fas fa-book"></i> Tên môn học <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control vip-form-control @error('ten_mon_hoc') is-invalid @enderror" 
                                id="ten_mon_hoc"
                                name="ten_mon_hoc"
                                placeholder="VD: Lập trình Python cơ bản"
                                value="{{ old('ten_mon_hoc') }}"
                                required
                            >
                            <small class="text-muted">Mã môn học sẽ được tạo tự động</small>
                            @error('ten_mon_hoc')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mo_ta" class="form-label">
                                <i class="fas fa-align-left"></i> Mô tả
                            </label>
                            <textarea 
                                class="form-control vip-form-control @error('mo_ta') is-invalid @enderror" 
                                id="mo_ta"
                                name="mo_ta"
                                rows="4"
                                placeholder="Nhập mô tả về môn học..."
                            >{{ old('mo_ta') }}</textarea>
                            @error('mo_ta')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="hinh_anh" class="form-label">
                                <i class="fas fa-image"></i> Hình ảnh
                            </label>
                            <input 
                                type="file" 
                                class="form-control vip-form-control @error('hinh_anh') is-invalid @enderror" 
                                id="hinh_anh"
                                name="hinh_anh"
                                accept="image/*"
                            >
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> 
                                Hỗ trợ JPEG, PNG, JPG, GIF (Tối đa 2MB)
                            </small>
                            @error('hinh_anh')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.mon-hoc.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Thêm môn học
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show file name when selected
    document.getElementById('hinh_anh').addEventListener('change', function(e) {
        const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'Chọn tệp';
        this.parentElement.querySelector('label').textContent = fileName;
    });
</script>
@endpush
@endsection
