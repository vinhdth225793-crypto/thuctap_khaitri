@extends('layouts.app')

@section('title', 'Cài đặt hệ thống')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cogs me-2"></i> Cài đặt hệ thống
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Thông tin liên hệ</h5>
                                    <p class="card-text text-muted">Quản lý hotline và email liên hệ</p>
                                    <a href="{{ route('admin.settings.contact') }}" class="btn btn-primary">
                                        <i class="fas fa-arrow-right me-2"></i> Truy cập
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-share-alt fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Mạng xã hội</h5>
                                    <p class="card-text text-muted">Quản lý liên kết Facebook và Zalo</p>
                                    <a href="{{ route('admin.settings.social') }}" class="btn btn-success">
                                        <i class="fas fa-arrow-right me-2"></i> Truy cập
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-chalkboard-teacher fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Giảng viên</h5>
                                    <p class="card-text text-muted">Chọn giảng viên hiển thị trên trang chủ</p>
                                    <a href="{{ route('admin.settings.instructors') }}" class="btn btn-info">
                                        <i class="fas fa-arrow-right me-2"></i> Truy cập
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-images fa-3x text-warning mb-3"></i>
                                    <h5 class="card-title">Banner trang chủ</h5>
                                    <p class="card-text text-muted">Quản lý ảnh slider hiển thị trên trang chủ</p>
                                    <a href="{{ route('admin.settings.banners.index') }}" class="btn btn-warning text-white">
                                        <i class="fas fa-arrow-right me-2"></i> Truy cập
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
