@extends('layouts.app')

@section('title', 'Tạo khóa học mẫu')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tạo mẫu</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold"><i class="fas fa-copy me-2 text-info"></i> Tạo khóa học mẫu <span class="badge bg-info ms-2 fs-6 shadow-sm">Template</span></h4>
            <p class="text-muted small">Chuẩn bị nội dung chương trình học. Giảng viên và lịch dạy sẽ được thiết lập sau khi mở lớp thực tế.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.khoa-hoc.store') }}" method="POST" enctype="multipart/form-data" id="mainForm">
        @csrf
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
                                <label class="form-label small fw-bold">Nhóm ngành <span class="text-danger">*</span></label>
                                <select name="nhom_nganh_id" class="form-select vip-form-control @error('nhom_nganh_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn nhóm ngành --</option>
                                    @foreach($nhomNganhs as $item)
                                        <option value="{{ $item->id }}" {{ old('nhom_nganh_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->ten_nhom_nganh }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nhom_nganh_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mã khóa học mẫu <span class="text-danger">*</span></label>
                                <input type="text" name="ma_khoa_hoc" class="form-control vip-form-control @error('ma_khoa_hoc') is-invalid @enderror" value="{{ old('ma_khoa_hoc') }}" required placeholder="VD: PHP-MAU, JAVA-MAU">
                                <div class="form-text smaller italic">Mã này sẽ là prefix. VD: PHP-MAU → lớp mở sẽ là PHP-MAU-K01, PHP-MAU-K02...</div>
                                @error('ma_khoa_hoc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Tên khóa học mẫu <span class="text-danger">*</span></label>
                                <input type="text" name="ten_khoa_hoc" class="form-control vip-form-control @error('ten_khoa_hoc') is-invalid @enderror" value="{{ old('ten_khoa_hoc') }}" required placeholder="Ví dụ: Lập trình PHP & Laravel từ cơ bản đến nâng cao">
                                @error('ten_khoa_hoc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small fw-bold d-block">Cấp độ *</label>
                                <div class="d-flex gap-4">
                                    @foreach(['co_ban' => 'Cơ bản', 'trung_binh' => 'Trung bình', 'nang_cao' => 'Nâng cao'] as $val => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="cap_do" id="cd_{{ $val }}" value="{{ $val }}" {{ old('cap_do', 'co_ban') === $val ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="cd_{{ $val }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('cap_do') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Mô tả ngắn</label>
                                <textarea name="mo_ta_ngan" id="mo_ta_ngan" class="form-control vip-form-control @error('mo_ta_ngan') is-invalid @enderror" rows="2" maxlength="500" placeholder="Tóm tắt chương trình học (tối đa 500 ký tự)">{{ old('mo_ta_ngan') }}</textarea>
                                <div class="text-end smaller text-muted" id="char-counter">0/500</div>
                                @error('mo_ta_ngan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Nội dung chi tiết chương trình</label>
                                <textarea name="mo_ta_chi_tiet" class="form-control vip-form-control" rows="6" placeholder="Mục tiêu, lộ trình chi tiết và yêu cầu đầu vào của khóa học...">{{ old('mo_ta_chi_tiet') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Modules (Dynamic List) -->
                <div class="vip-card mb-4 border-0 shadow-sm">
                    <div class="vip-card-header d-flex justify-content-between align-items-center">
                        <h5 class="vip-card-title small fw-bold text-uppercase">2. Cấu trúc Module học tập</h5>
                        <span class="badge bg-dark px-3" id="module-count">1 module</span>
                    </div>
                    <div class="vip-card-body p-4">
                        <div id="module-container">
                            @php $modulesOld = old('modules', [['ten_module'=>'', 'thoi_luong_du_kien'=>'', 'mo_ta'=>'']]) @endphp
                            @foreach($modulesOld as $i => $mod)
                                <div class="module-item border rounded p-3 mb-3 bg-white shadow-xs position-relative" data-index="{{ $i }}">
                                    <div class="d-flex gap-3 align-items-start">
                                        <div class="mt-2 text-muted handle" style="cursor: grab;">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="row g-2">
                                                <div class="col-md-7">
                                                    <label class="smaller fw-bold text-muted mb-1">Tên module *</label>
                                                    <input type="text" name="modules[{{ $i }}][ten_module]" class="form-control form-control-sm vip-form-control" value="{{ $mod['ten_module'] }}" required placeholder="VD: Giới thiệu về Laravel">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="smaller fw-bold text-muted mb-1">Thời lượng (phút)</label>
                                                    <input type="number" name="modules[{{ $i }}][thoi_luong_du_kien]" class="form-control form-control-sm vip-form-control" value="{{ $mod['thoi_luong_du_kien'] }}" placeholder="90" min="1">
                                                </div>
                                                <div class="col-12">
                                                    <label class="smaller fw-bold text-muted mb-1 mt-2">Mô tả nội dung module</label>
                                                    <input type="text" name="modules[{{ $i }}][mo_ta]" class="form-control form-control-sm vip-form-control" value="{{ $mod['mo_ta'] }}" placeholder="Tóm tắt các bài học trong module này...">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pt-4">
                                            <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-remove-module" title="Xóa module">
                                                <i class="fas fa-times-circle fa-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="btn-add-module" class="btn btn-outline-secondary btn-sm fw-bold px-4 py-2 mt-2">
                            <i class="fas fa-plus me-1"></i> Thêm module mới
                        </button>
                        <p class="smaller text-muted mt-3 mb-0 italic">
                            <i class="fas fa-info-circle me-1"></i> Lớp học thực tế sẽ copy cấu trúc module này và gán giảng viên sau.
                        </p>
                    </div>
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
                            <img id="preview-img" src="{{ asset('images/default-course.svg') }}" class="img-fluid d-none" style="max-height: 100%;">
                            <div id="preview-placeholder" class="text-muted">
                                <i class="fas fa-image fa-3x opacity-25"></i>
                                <p class="small mb-0 mt-2">Xem trước hình ảnh</p>
                            </div>
                        </div>
                        <input type="file" name="hinh_anh" id="hinh_anh" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text smaller italic mt-2">Hỗ trợ JPG, PNG. Tối đa 2MB.</div>
                        @error('hinh_anh') <div class="text-danger smaller mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header bg-white border-bottom py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Ghi chú nội bộ</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <textarea name="ghi_chu_noi_bo" class="form-control vip-form-control" rows="4" placeholder="Ghi chú dành riêng cho ban quản lý, không hiển thị cho học viên...">{{ old('ghi_chu_noi_bo') }}</textarea>
                    </div>
                </div>

                <div class="sticky-top" style="top: 1rem;">
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary py-3 fw-bold shadow border-0">
                            <i class="fas fa-save me-2"></i> LƯU KHÓA HỌC MẪU
                        </button>
                        <a href="{{ route('admin.khoa-hoc.index') }}" class="btn btn-outline-secondary py-2 fw-bold">
                            HỦY BỎ
                        </a>
                    </div>
                    <div class="alert alert-warning mt-3 small border-0 shadow-sm" style="background-color: #fff9db;">
                        <i class="fas fa-exclamation-circle me-1 text-warning"></i> <strong>Lưu ý:</strong> Sau khi lưu, bạn có thể vào trang chi tiết để "Mở lớp" và gán giảng viên.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Template for new module row (hidden) -->
<template id="module-template">
    <div class="module-item border rounded p-3 mb-3 bg-white shadow-xs position-relative">
        <div class="d-flex gap-3 align-items-start">
            <div class="mt-2 text-muted handle" style="cursor: grab;">
                <i class="fas fa-grip-vertical"></i>
            </div>
            <div class="flex-fill">
                <div class="row g-2">
                    <div class="col-md-7">
                        <label class="smaller fw-bold text-muted mb-1">Tên module *</label>
                        <input type="text" name="modules[__INDEX__][ten_module]" class="form-control form-control-sm vip-form-control" required placeholder="Tên module">
                    </div>
                    <div class="col-md-5">
                        <label class="smaller fw-bold text-muted mb-1">Thời lượng (phút)</label>
                        <input type="number" name="modules[__INDEX__][thoi_luong_du_kien]" class="form-control form-control-sm vip-form-control" placeholder="90" min="1">
                    </div>
                    <div class="col-12">
                        <label class="smaller fw-bold text-muted mb-1 mt-2">Mô tả nội dung module</label>
                        <input type="text" name="modules[__INDEX__][mo_ta]" class="form-control form-control-sm vip-form-control" placeholder="...">
                    </div>
                </div>
            </div>
            <div class="pt-4">
                <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-remove-module" title="Xóa module">
                    <i class="fas fa-times-circle fa-lg"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('module-container');
    const btnAdd = document.getElementById('btn-add-module');
    const template = document.getElementById('module-template').innerHTML;
    const countBadge = document.getElementById('module-count');
    const descTextarea = document.getElementById('mo_ta_ngan');
    const charCounter = document.getElementById('char-counter');

    function updateRenumbering() {
        const items = container.querySelectorAll('.module-item');
        items.forEach((item, index) => {
            item.dataset.index = index;
            item.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/modules\[\d+\]/, `modules[${index}]`));
                }
            });
        });
        countBadge.textContent = `${items.length} module`;
    }

    btnAdd.addEventListener('click', () => {
        const index = container.querySelectorAll('.module-item').length;
        const newHtml = template.replace(/__INDEX__/g, index);
        const div = document.createElement('div');
        div.innerHTML = newHtml;
        container.appendChild(div.firstElementChild);
        updateRenumbering();
    });

    container.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-module')) {
            const items = container.querySelectorAll('.module-item');
            if (items.length <= 1) {
                alert('Khóa học phải có ít nhất 1 module.');
                return;
            }
            if (confirm('Bạn muốn xóa module này?')) {
                e.target.closest('.module-item').remove();
                updateRenumbering();
            }
        }
    });

    // Character counter for short description
    descTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCounter.textContent = `${length}/500`;
        if (length >= 500) charCounter.classList.add('text-danger');
        else charCounter.classList.remove('text-danger');
    });

    // Image preview
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

    updateRenumbering();
});
</script>

<style>
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
    .handle:active { cursor: grabbing; }
    .sticky-top { z-index: 100; }
</style>
@endsection
