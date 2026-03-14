@extends('layouts.app')

@section('title', 'Chỉnh sửa module: ' . $moduleHoc->ten_module)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.module-hoc.index') }}">Module học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold"><i class="fas fa-edit me-2 text-warning"></i> Chỉnh sửa: {{ $moduleHoc->ten_module }}</h4>
            <p class="text-muted small">Cập nhật thông tin chi tiết của module. Nếu thay đổi thứ tự, mã module sẽ tự động được cập nhật lại.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="vip-card shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Cập nhật thông tin</h5>
                </div>
                <div class="vip-card-body p-4">
                    <form action="{{ route('admin.module-hoc.update', $moduleHoc->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-dark">Khóa học (Cố định)</label>
                                <input type="text" class="form-control bg-light" value="[{{ $moduleHoc->khoaHoc->ma_khoa_hoc }}] {{ $moduleHoc->khoaHoc->ten_khoa_hoc }}" readonly>
                                <input type="hidden" name="khoa_hoc_id" value="{{ $moduleHoc->khoa_hoc_id }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Mã module hiện tại</label>
                                <input type="text" class="form-control bg-light" value="{{ $moduleHoc->ma_module }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Thứ tự module <span class="text-danger">*</span></label>
                                <input type="number" name="thu_tu_module" class="form-control vip-form-control @error('thu_tu_module') is-invalid @enderror" 
                                       value="{{ old('thu_tu_module', $moduleHoc->thu_tu_module) }}" min="1" required>
                                @error('thu_tu_module') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-dark">Tên module <span class="text-danger">*</span></label>
                                <input type="text" name="ten_module" class="form-control vip-form-control @error('ten_module') is-invalid @enderror" 
                                       value="{{ old('ten_module', $moduleHoc->ten_module) }}" required>
                                @error('ten_module') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Thời lượng dự kiến (phút)</label>
                                <input type="number" name="thoi_luong_du_kien" class="form-control vip-form-control @error('thoi_luong_du_kien') is-invalid @enderror" 
                                       value="{{ old('thoi_luong_du_kien', $moduleHoc->thoi_luong_du_kien) }}" min="1" max="600">
                                @error('thoi_luong_du_kien') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-4">
                                    <input type="hidden" name="trang_thai" value="0">
                                    <input class="form-check-input" type="checkbox" name="trang_thai" id="trang_thai" value="1" {{ old('trang_thai', $moduleHoc->trang_thai) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold" for="trang_thai">Kích hoạt module</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Mô tả nội dung</label>
                                <textarea name="mo_ta" class="form-control vip-form-control" rows="4">{{ old('mo_ta', $moduleHoc->mo_ta) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-white border-0">
                                <i class="fas fa-save me-2"></i> CẬP NHẬT
                            </button>
                            <a href="{{ route('admin.module-hoc.show', $moduleHoc->id) }}" class="btn btn-outline-secondary px-4 fw-bold">
                                HỦY BỎ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.1); border-color: #ffc107; }
</style>
@endsection
