@extends('layouts.app')

@section('title', 'Thêm module mới')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quản lý khóa học</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.module-hoc.index') }}">Module học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Thêm module mới</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="vip-card">
                <div class="vip-card-header">
                    <h5 class="vip-card-title fw-bold">
                        <i class="fas fa-plus-circle me-2 text-primary"></i> Thêm module học tập mới
                    </h5>
                </div>
                <div class="vip-card-body p-4">
                    <form action="{{ route('admin.module-hoc.store') }}" method="POST">
                        @csrf
                        
                        <!-- Khóa học -->
                        <div class="mb-4">
                            <label for="khoa_hoc_id" class="form-label small fw-bold">Khóa học <span class="text-danger">*</span></label>
                            <select name="khoa_hoc_id" id="khoa_hoc_id" class="form-select vip-form-control @error('khoa_hoc_id') is-invalid @enderror" required>
                                <option value="">-- Chọn khóa học --</option>
                                @php
                                    $currentMonHoc = null;
                                @endphp
                                @foreach($khoaHocs as $kh)
                                    @if($currentMonHoc != $kh->monHoc->ten_mon_hoc)
                                        @if($currentMonHoc !== null) </optgroup> @endif
                                        @php $currentMonHoc = $kh->monHoc->ten_mon_hoc; @endphp
                                        <optgroup label="Môn học: {{ $currentMonHoc }}">
                                    @endif
                                    <option value="{{ $kh->id }}" {{ old('khoa_hoc_id', $khoaHocId) == $kh->id ? 'selected' : '' }}>
                                        [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
                                    </option>
                                @endforeach
                                @if($currentMonHoc !== null) </optgroup> @endif
                            </select>
                            @error('khoa_hoc_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tên module -->
                        <div class="mb-4">
                            <label for="ten_module" class="form-label small fw-bold">Tên module <span class="text-danger">*</span></label>
                            <input type="text" name="ten_module" id="ten_module" class="form-control vip-form-control @error('ten_module') is-invalid @enderror" value="{{ old('ten_module') }}" required placeholder="Ví dụ: Giới thiệu cơ bản về Python">
                            @error('ten_module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Thứ tự -->
                            <div class="col-md-6 mb-4">
                                <label for="thu_tu_module" class="form-label small fw-bold">Thứ tự hiển thị <span class="text-danger">*</span></label>
                                <input type="number" name="thu_tu_module" id="thu_tu_module" class="form-control vip-form-control @error('thu_tu_module') is-invalid @enderror" value="{{ old('thu_tu_module', $thuTuGoiY) }}" min="1" required>
                                <div class="form-text smaller text-muted">Thứ tự hiển thị trong khóa học (1, 2, 3...)</div>
                                @error('thu_tu_module')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Thời lượng -->
                            <div class="col-md-6 mb-4">
                                <label for="thoi_luong_du_kien" class="form-label small fw-bold">Thời lượng dự kiến (phút)</label>
                                <input type="number" name="thoi_luong_du_kien" id="thoi_luong_du_kien" class="form-control vip-form-control @error('thoi_luong_du_kien') is-invalid @enderror" value="{{ old('thoi_luong_du_kien') }}" min="1" max="600" placeholder="90">
                                <div class="form-text smaller text-muted">Đơn vị: phút. VD: 90 = 1 giờ 30 phút</div>
                                @error('thoi_luong_du_kien')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Mô tả -->
                        <div class="mb-4">
                            <label for="mo_ta" class="form-label small fw-bold">Mô tả nội dung</label>
                            <textarea name="mo_ta" id="mo_ta" class="form-control vip-form-control @error('mo_ta') is-invalid @enderror" rows="4" placeholder="Nhập mô tả chi tiết về nội dung học tập của module này...">{{ old('mo_ta') }}</textarea>
                            @error('mo_ta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Trạng thái -->
                        <div class="mb-4">
                            <div class="form-check form-switch custom-switch">
                                <input class="form-check-input" type="checkbox" name="trang_thai" id="trang_thai" value="1" {{ old('trang_thai', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small ms-2" for="trang_thai">Kích hoạt hoạt động</label>
                            </div>
                        </div>

                        <!-- Mã module (Readonly display) -->
                        <div class="mb-4 p-3 bg-light rounded border border-dashed">
                            <label class="form-label small fw-bold mb-1 d-block text-muted">Mã module dự kiến</label>
                            <span class="text-primary fw-bold" id="ma_module_preview">Tự sinh sau khi chọn khóa học và nhập thứ tự</span>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.module-hoc.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">
                                <i class="fas fa-save me-1"></i> Lưu module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-dashed { border-style: dashed !important; }
    .custom-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
</style>
@endsection
