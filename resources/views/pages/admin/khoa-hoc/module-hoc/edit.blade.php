@extends('layouts.app')

@section('title', 'Chỉnh sửa module: ' . $moduleHoc->ten_module)

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
                    <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
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
                        <i class="fas fa-edit me-2 text-warning"></i> Chỉnh sửa module
                    </h5>
                </div>
                <div class="vip-card-body p-4">
                    <form action="{{ route('admin.module-hoc.update', $moduleHoc->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <!-- Mã module (Readonly) -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Mã module</label>
                                <input type="text" class="form-control vip-form-control bg-light" value="{{ $moduleHoc->ma_module }}" readonly>
                            </div>
                            
                            <!-- Khóa học (Readonly Display) -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Khóa học</label>
                                <input type="text" class="form-control vip-form-control bg-light" value="[{{ $moduleHoc->khoaHoc->ma_khoa_hoc }}] {{ $moduleHoc->khoaHoc->ten_khoa_hoc }}" readonly>
                                <input type="hidden" name="khoa_hoc_id" value="{{ $moduleHoc->khoa_hoc_id }}">
                            </div>
                        </div>

                        <!-- Tên module -->
                        <div class="mb-4">
                            <label for="ten_module" class="form-label small fw-bold">Tên module <span class="text-danger">*</span></label>
                            <input type="text" name="ten_module" id="ten_module" class="form-control vip-form-control @error('ten_module') is-invalid @enderror" value="{{ old('ten_module', $moduleHoc->ten_module) }}" required>
                            @error('ten_module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Thứ tự -->
                            <div class="col-md-6 mb-4">
                                <label for="thu_tu_module" class="form-label small fw-bold">Thứ tự hiển thị <span class="text-danger">*</span></label>
                                <input type="number" name="thu_tu_module" id="thu_tu_module" class="form-control vip-form-control @error('thu_tu_module') is-invalid @enderror" value="{{ old('thu_tu_module', $moduleHoc->thu_tu_module) }}" min="1" required>
                                <div class="form-text smaller text-muted">Thứ tự trong khóa học. Nếu đổi, mã module sẽ tự cập nhật.</div>
                                @error('thu_tu_module')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Thời lượng -->
                            <div class="col-md-6 mb-4">
                                <label for="thoi_luong_du_kien" class="form-label small fw-bold">Thời lượng dự kiến (phút)</label>
                                <input type="number" name="thoi_luong_du_kien" id="thoi_luong_du_kien" class="form-control vip-form-control @error('thoi_luong_du_kien') is-invalid @enderror" value="{{ old('thoi_luong_du_kien', $moduleHoc->thoi_luong_du_kien) }}" min="1" max="600">
                                @error('thoi_luong_du_kien')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Mô tả -->
                        <div class="mb-4">
                            <label for="mo_ta" class="form-label small fw-bold">Mô tả nội dung</label>
                            <textarea name="mo_ta" id="mo_ta" class="form-control vip-form-control @error('mo_ta') is-invalid @enderror" rows="4">{{ old('mo_ta', $moduleHoc->mo_ta) }}</textarea>
                            @error('mo_ta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Trạng thái -->
                        <div class="mb-4">
                            <div class="form-check form-switch custom-switch">
                                <input class="form-check-input" type="checkbox" name="trang_thai" id="trang_thai" value="1" {{ old('trang_thai', $moduleHoc->trang_thai) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small ms-2" for="trang_thai">Kích hoạt hoạt động</label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.module-hoc.show', $moduleHoc->id) }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-warning px-5 fw-bold text-white">
                                <i class="fas fa-save me-1"></i> Cập nhật module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
</style>
@endsection
