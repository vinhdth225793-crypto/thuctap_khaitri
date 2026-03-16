@extends('layouts.app')

@section('title', 'Chỉnh sửa Nhóm ngành: ' . $nhomNganh->ten_nhom_nganh)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mon-hoc.index') }}">Nhóm ngành</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold"><i class="fas fa-edit me-2 text-warning"></i> Chỉnh sửa Nhóm ngành</h4>
            <p class="text-muted small">Cập nhật thông tin chi tiết cho nhóm ngành <strong>{{ $nhomNganh->ten_nhom_nganh }}</strong>.</p>
        </div>
    </div>

    <form action="{{ route('admin.mon-hoc.update', $nhomNganh->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Cột trái: Thông tin chính -->
            <div class="col-lg-8">
                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header bg-white border-bottom py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Thông tin cơ bản</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Mã nhóm ngành (Chỉ đọc)</label>
                                <input type="text" class="form-control bg-light" value="{{ $nhomNganh->ma_nhom_nganh }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Tên nhóm ngành <span class="text-danger">*</span></label>
                                <input type="text" name="ten_nhom_nganh" class="form-control vip-form-control @error('ten_nhom_nganh') is-invalid @enderror" 
                                       value="{{ old('ten_nhom_nganh', $nhomNganh->ten_nhom_nganh) }}" required>
                                @error('ten_nhom_nganh') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Mô tả chi tiết</label>
                                <textarea name="mo_ta" class="form-control vip-form-control @error('mo_ta') is-invalid @enderror" 
                                          rows="5" placeholder="Mô tả tóm tắt về nhóm ngành này...">{{ old('mo_ta', $nhomNganh->mo_ta) }}</textarea>
                                @error('mo_ta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="trang_thai" value="0">
                                    <input class="form-check-input" type="checkbox" name="trang_thai" id="trang_thai" value="1" {{ old('trang_thai', $nhomNganh->trang_thai) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold" for="trang_thai">Kích hoạt nhóm ngành</label>
                                </div>
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
                            <img id="preview-img" src="{{ $nhomNganh->hinh_anh ? asset($nhomNganh->hinh_anh) : asset('images/default-course.svg') }}" class="img-fluid" style="max-height: 100%;">
                        </div>
                        <input type="file" name="hinh_anh" id="hinh_anh" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text smaller italic mt-2">Hỗ trợ JPG, PNG. Để trống nếu không muốn đổi ảnh.</div>
                        @error('hinh_anh') <div class="text-danger smaller mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="sticky-top" style="top: 1rem;">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning py-3 fw-bold shadow text-white border-0">
                            <i class="fas fa-save me-2"></i> CẬP NHẬT THAY ĐỔI
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

    imgInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.1); border-color: #ffc107; }
</style>
@endsection
