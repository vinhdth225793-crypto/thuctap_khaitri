@extends('layouts.app')

@section('title', 'Thêm module mới')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.module-hoc.index') }}">Module học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i> Thêm module mới</h4>
            <p class="text-muted small">Tạo nội dung bài học mới cho một khóa học cụ thể. Mã module sẽ được tự động sinh dựa trên mã khóa học và thứ tự.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="vip-card shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Thông tin module</h5>
                </div>
                <div class="vip-card-body p-4">
                    <form action="{{ route('admin.module-hoc.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-dark">Chọn khóa học <span class="text-danger">*</span></label>
                                <select name="khoa_hoc_id" id="khoa_hoc_id" class="form-select vip-form-control @error('khoa_hoc_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn khóa học --</option>
                                    @php
                                        $groupedKhoaHocs = $khoaHocs->groupBy(fn($kh) => $kh->nhomNganh->ten_nhom_nganh ?? 'Khác');
                                    @endphp
                                    @foreach($groupedKhoaHocs as $tenNhomNganh => $items)
                                        <optgroup label="Ngành: {{ $tenNhomNganh }}">
                                            @foreach($items as $kh)
                                                <option value="{{ $kh->id }}" {{ old('khoa_hoc_id', $khoaHocId) == $kh->id ? 'selected' : '' }}>
                                                    [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('khoa_hoc_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-dark">Tên module <span class="text-danger">*</span></label>
                                <input type="text" name="ten_module" class="form-control vip-form-control @error('ten_module') is-invalid @enderror" 
                                       value="{{ old('ten_module') }}" required placeholder="Ví dụ: Tổng quan về biến và kiểu dữ liệu">
                                @error('ten_module') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Thứ tự module <span class="text-danger">*</span></label>
                                <input type="number" name="thu_tu_module" class="form-control vip-form-control @error('thu_tu_module') is-invalid @enderror" 
                                       value="{{ old('thu_tu_module', $thuTuGoiY) }}" min="1" required>
                                <div class="form-text smaller italic">Thứ tự hiển thị (1, 2, 3...)</div>
                                @error('thu_tu_module') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Mã module dự kiến</label>
                                <input type="text" id="ma_module_preview" class="form-control bg-light" value="Tự sinh sau khi chọn KH & thứ tự" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Thời lượng dự kiến (phút)</label>
                                <input type="number" name="thoi_luong_du_kien" class="form-control vip-form-control @error('thoi_luong_du_kien') is-invalid @enderror" 
                                       value="{{ old('thoi_luong_du_kien', 90) }}" min="1" max="600">
                                <div class="form-text smaller italic">VD: 90 phút = 1h 30p</div>
                                @error('thoi_luong_du_kien') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Mô tả nội dung module</label>
                                <textarea name="mo_ta" class="form-control vip-form-control" rows="4" placeholder="Tóm tắt các kiến thức sẽ đạt được trong module này...">{{ old('mo_ta') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="trang_thai" value="0">
                                    <input class="form-check-input" type="checkbox" name="trang_thai" id="trang_thai" value="1" {{ old('trang_thai', true) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold" for="trang_thai">Kích hoạt module ngay khi tạo</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> LƯU MODULE
                            </button>
                            <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-outline-secondary px-4 fw-bold">
                                HỦY BỎ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // No JS needed for ma_module preview since it's handled on server
</script>
@endpush

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
    .italic { font-style: italic; }
</style>
@endsection
