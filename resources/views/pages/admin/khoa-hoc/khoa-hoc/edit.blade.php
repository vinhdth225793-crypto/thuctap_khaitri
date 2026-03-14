@extends('layouts.app')

@section('title', 'Chỉnh sửa Khóa học mẫu')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}">{{ $khoaHoc->ma_khoa_hoc }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold"><i class="fas fa-edit me-2 text-warning"></i> Chỉnh sửa Khóa học mẫu</h4>
            <p class="text-muted small">Cập nhật nội dung chương trình học mẫu. Các thay đổi tại đây sẽ áp dụng cho các lớp mở từ mẫu này trong tương lai.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.khoa-hoc.update', $khoaHoc->id) }}" method="POST" enctype="multipart/form-data" id="mainForm">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Cột trái: Thông tin chính -->
            <div class="col-lg-8">
                <div class="vip-card mb-4">
                    <div class="vip-card-header">
                        <h5 class="vip-card-title small fw-bold text-uppercase">1. Thông tin chung</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Môn học <span class="text-danger">*</span></label>
                                <select name="mon_hoc_id" class="form-select vip-form-control @error('mon_hoc_id') is-invalid @enderror" required>
                                    @foreach($monHocs as $mh)
                                        <option value="{{ $mh->id }}" {{ old('mon_hoc_id', $khoaHoc->mon_hoc_id) == $mh->id ? 'selected' : '' }}>
                                            {{ $mh->ten_mon_hoc }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('mon_hoc_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mã khóa học mẫu (Chỉ đọc)</label>
                                <input type="text" class="form-control bg-light" value="{{ $khoaHoc->ma_khoa_hoc }}" readonly>
                                <div class="form-text smaller italic">Mã khóa học mẫu không được phép thay đổi để tránh lỗi hệ thống.</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Tên khóa học mẫu <span class="text-danger">*</span></label>
                                <input type="text" name="ten_khoa_hoc" class="form-control vip-form-control @error('ten_khoa_hoc') is-invalid @enderror" value="{{ old('ten_khoa_hoc', $khoaHoc->ten_khoa_hoc) }}" required>
                                @error('ten_khoa_hoc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small fw-bold d-block">Cấp độ *</label>
                                <div class="d-flex gap-4">
                                    @foreach(['co_ban' => 'Cơ bản', 'trung_binh' => 'Trung bình', 'nang_cao' => 'Nâng cao'] as $val => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="cap_do" id="cd_{{ $val }}" value="{{ $val }}" {{ old('cap_do', $khoaHoc->cap_do) === $val ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="cd_{{ $val }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Trạng thái hiển thị</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="trang_thai" value="0">
                                    <input class="form-check-input" type="checkbox" name="trang_thai" id="trang_thai" value="1" {{ old('trang_thai', $khoaHoc->trang_thai) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="trang_thai">Cho phép sử dụng mẫu này để mở lớp</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Mô tả ngắn</label>
                                <textarea name="mo_ta_ngan" id="mo_ta_ngan" class="form-control vip-form-control" rows="2" maxlength="500">{{ old('mo_ta_ngan', $khoaHoc->mo_ta_ngan) }}</textarea>
                                <div class="text-end smaller text-muted" id="char-counter">0/500</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Nội dung chi tiết chương trình</label>
                                <textarea name="mo_ta_chi_tiet" class="form-control vip-form-control" rows="6">{{ old('mo_ta_chi_tiet', $khoaHoc->mo_ta_chi_tiet) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- THÔNG BÁO VỀ MODULE --}}
                <div class="alert alert-warning border-0 shadow-sm small mb-4">
                    <i class="fas fa-info-circle me-2"></i> 
                    <strong>Lưu ý về Module:</strong> Để thêm hoặc xóa Module, vui lòng thực hiện tại trang <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="fw-bold">Chi tiết khóa học</a>. Trang này tập trung chỉnh sửa thông tin mô tả chung.
                </div>
            </div>

            <!-- Cột phải: Media & Settings -->
            <div class="col-lg-4">
                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header bg-white border-bottom py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Ảnh đại diện mẫu</h5>
                    </div>
                    <div class="vip-card-body p-4 text-center">
                        <div class="image-preview-wrapper mb-3 border rounded overflow-hidden bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                            <img id="preview-img" src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}" class="img-fluid" style="max-height: 100%;">
                        </div>
                        <input type="file" name="hinh_anh" id="hinh_anh" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text smaller italic mt-2">Hỗ trợ JPG, PNG. Để trống nếu không muốn đổi ảnh.</div>
                    </div>
                </div>

                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header bg-white border-bottom py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Ghi chú nội bộ</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <textarea name="ghi_chu_noi_bo" class="form-control vip-form-control" rows="4" placeholder="Ghi chú dành riêng cho ban quản lý...">{{ old('ghi_chu_noi_bo', $khoaHoc->ghi_chu_noi_bo) }}</textarea>
                    </div>
                </div>

                <div class="sticky-top" style="top: 1rem;">
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-warning py-3 fw-bold shadow text-white border-0">
                            <i class="fas fa-save me-2"></i> CẬP NHẬT THAY ĐỔI
                        </button>
                        <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-outline-secondary py-2 fw-bold">
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
    const descTextarea = document.getElementById('mo_ta_ngan');
    const charCounter = document.getElementById('char-counter');

    // Character counter
    function updateCounter() {
        const length = descTextarea.value.length;
        charCounter.textContent = `${length}/500`;
        if (length >= 500) charCounter.classList.add('text-danger');
        else charCounter.classList.remove('text-danger');
    }
    descTextarea.addEventListener('input', updateCounter);
    updateCounter();

    // Image preview
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
