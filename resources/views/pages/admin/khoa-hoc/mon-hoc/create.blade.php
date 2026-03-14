@extends('layouts.app')

@section('title', 'Thêm môn học mới')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mon-hoc.index') }}">Môn học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i> Thêm môn học mới</h4>
            <p class="text-muted small">Tạo danh mục môn học mới để phân loại các khóa học trong hệ thống.</p>
        </div>
    </div>

    <form action="{{ route('admin.mon-hoc.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Cột trái: Thông tin chính -->
            <div class="col-lg-8">
                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header bg-white border-bottom py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Thông tin cơ bản</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-dark">Tên môn học <span class="text-danger">*</span></label>
                                <input type="text" name="ten_mon_hoc" class="form-control vip-form-control @error('ten_mon_hoc') is-invalid @enderror" 
                                       value="{{ old('ten_mon_hoc') }}" required placeholder="Ví dụ: Lập trình PHP & Laravel">
                                <div class="form-text smaller italic">Mã môn học sẽ được hệ thống tự động sinh từ tên này.</div>
                                @error('ten_mon_hoc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Mô tả môn học</label>
                                <textarea name="mo_ta" class="form-control vip-form-control @error('mo_ta') is-invalid @enderror" 
                                          rows="6" placeholder="Mô tả tóm tắt về lĩnh vực hoặc kiến thức của môn học này...">{{ old('mo_ta') }}</textarea>
                                @error('mo_ta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Media & Actions -->
            <div class="col-lg-4">
                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header bg-white border-bottom py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Hình ảnh đại diện</h5>
                    </div>
                    <div class="vip-card-body p-4 text-center">
                        <div class="image-preview-wrapper mb-3 border rounded overflow-hidden bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <img id="preview-img" src="{{ asset('images/default-course.svg') }}" class="img-fluid d-none" style="max-height: 100%;">
                            <div id="preview-placeholder" class="text-muted">
                                <i class="fas fa-image fa-4x opacity-25"></i>
                                <p class="small mb-0 mt-2">Xem trước hình ảnh</p>
                            </div>
                        </div>
                        <input type="file" name="hinh_anh" id="hinh_anh" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text smaller italic mt-2">Hỗ trợ JPG, PNG. Tối đa 2MB.</div>
                        @error('hinh_anh') <div class="text-danger smaller mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="sticky-top" style="top: 1rem;">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-3 fw-bold shadow border-0">
                            <i class="fas fa-save me-2"></i> LƯU MÔN HỌC
                        </button>
                        <a href="{{ route('admin.mon-hoc.index') }}" class="btn btn-outline-secondary py-2 fw-bold">
                            HỦY BỎ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imgInput = document.getElementById('hinh_anh');
    const previewImg = document.getElementById('preview-img');
    const placeholder = document.getElementById('preview-placeholder');

    imgInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('d-none');
                placeholder.classList.add('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            previewImg.classList.add('d-none');
            placeholder.classList.remove('d-none');
        }
    });
});
</script>

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
    .italic { font-style: italic; }
</style>
@endsection
