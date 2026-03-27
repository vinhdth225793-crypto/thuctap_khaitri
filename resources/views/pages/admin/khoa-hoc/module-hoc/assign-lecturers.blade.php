@extends('layouts.app')

@section('title', 'Phân công giảng viên')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.module-hoc.index') }}">Quản lý học phần</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.module-hoc.show', $module->id) }}">{{ $module->ma_module }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Phân công giảng viên</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="vip-card">
                <div class="vip-card-header">
                    <h5 class="vip-card-title">Phân công giảng viên cho module: {{ $module->ten_module }}</h5>
                </div>
                <div class="vip-card-body">
                    <form action="{{ route('admin.module-hoc.store-assignments', $module->id) }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Thông tin module</label>
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Mã module:</strong> {{ $module->ma_module }}</li>
                                    <li class="list-group-item"><strong>Khóa học:</strong> {{ $module->khoaHoc->ten_khoa_hoc }}</li>
                                    <li class="list-group-item"><strong>Nhóm ngành:</strong> {{ $module->khoaHoc->nhomNganh->ten_nhom_nganh }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <label for="ghi_chu" class="form-label fw-bold">Ghi chú phân công</label>
                                <textarea name="ghi_chu" id="ghi_chu" class="form-control" rows="3" placeholder="Nhập ghi chú nếu có..."></textarea>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Chọn giảng viên <span class="text-danger">*</span></label>
                            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3">
                                @foreach($giangViens as $gv)
                                    <div class="col">
                                        <div class="card h-100 lecturer-select-card {{ in_array($gv->id, $assignedLecturerIds) ? 'bg-light opacity-75' : '' }}">
                                            <div class="card-body text-center p-3">
                                                <img src="{{ $gv->avatar_url ? asset($gv->avatar_url) : asset('images/default-avatar.svg') }}" 
                                                     class="rounded-circle mb-2" style="width: 60px; height: 60px; object-fit: cover;">
                                                <h6 class="card-title mb-1 smaller">{{ $gv->nguoiDung->ho_ten ?? 'N/A' }}</h6>
                                                <p class="text-muted smaller mb-2">{{ $gv->hoc_vi }}</p>
                                                
                                                @if(in_array($gv->id, $assignedLecturerIds))
                                                    <span class="badge bg-secondary">Đã phân công</span>
                                                @else
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input class="form-check-input" type="checkbox" name="giang_vien_ids[]" value="{{ $gv->id }}" id="gv_{{ $gv->id }}">
                                                        <label class="form-check-label ms-2" for="gv_{{ $gv->id }}">Chọn</label>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('giang_vien_ids')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-user-plus"></i> Xác nhận phân công
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .lecturer-select-card {
        transition: transform 0.2s;
        cursor: pointer;
    }
    .lecturer-select-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .smaller {
        font-size: 0.85rem;
    }
</style>
@endsection
