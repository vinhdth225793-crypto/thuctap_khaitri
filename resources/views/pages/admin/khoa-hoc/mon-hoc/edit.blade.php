@extends('layouts.app')

@section('title', 'Chỉnh sửa môn học')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="vip-card">
                <div class="vip-card-header">
                    <h5 class="vip-card-title">
                        <i class="fas fa-edit"></i> Chỉnh sửa môn học
                    </h5>
                </div>
                <div class="vip-card-body">
                    <form action="{{ route('admin.mon-hoc.update', $monHoc->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-code"></i> Mã môn học
                            </label>
                            <input 
                                type="text" 
                                class="form-control vip-form-control" 
                                value="{{ $monHoc->ma_mon_hoc }}"
                                readonly
                            >
                            <small class="text-muted">Mã môn học không thể thay đổi</small>
                        </div>

                        <div class="mb-3">
                            <label for="ten_mon_hoc" class="form-label">
                                <i class="fas fa-book"></i> Tên môn học <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control vip-form-control @error('ten_mon_hoc') is-invalid @enderror" 
                                id="ten_mon_hoc"
                                name="ten_mon_hoc"
                                value="{{ old('ten_mon_hoc', $monHoc->ten_mon_hoc) }}"
                                required
                            >
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
                            >{{ old('mo_ta', $monHoc->mo_ta) }}</textarea>
                            @error('mo_ta')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="hinh_anh" class="form-label">
                                <i class="fas fa-image"></i> Hình ảnh
                            </label>
                            @if($monHoc->hinh_anh)
                                <div class="mb-2">
                                    <img src="{{ asset($monHoc->hinh_anh) }}" alt="{{ $monHoc->ten_mon_hoc }}" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            @endif
                            <input 
                                type="file" 
                                class="form-control vip-form-control @error('hinh_anh') is-invalid @enderror" 
                                id="hinh_anh"
                                name="hinh_anh"
                                accept="image/*"
                            >
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> 
                                Chỉnh sửa để thay đổi hình ảnh
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
                                <i class="fas fa-save"></i> Cập nhật môn học
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
