@extends('layouts.app')

@section('title', 'Quản lý module')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Quản lý module</h3>
                <button class="btn btn-primary" disabled title="Sắp ra mắt">
                    <i class="fas fa-plus"></i> Thêm module mới
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-body text-center py-5">
                    <i class="fas fa-tools" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                    <h5 class="text-muted">Tính năng quản lý module</h5>
                    <p class="text-muted">Đang phát triển...</p>
                    <p class="text-muted small">Hãy tạo khóa học trước để có thể thêm module.</p>
                    <a href="{{ route('admin.mon-hoc.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left"></i> Quay lại Quản lý môn học
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
