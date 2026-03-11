@extends('layouts.app')

@section('title', 'Mạng Xã Hội - Cài Đặt Hệ Thống')

@section('content')
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <strong><i class="fas fa-exclamation-circle me-2"></i> Có lỗi xảy ra!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-share-alt me-2"></i> Mạng Xã Hội
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.social.save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="facebook" class="form-label fw-bold">
                                        <i class="fab fa-facebook text-primary me-2"></i> Facebook
                                    </label>
                                    <input type="url" class="form-control" id="facebook" name="facebook"
                                           placeholder="https://facebook.com/yourpage" value="{{ $settings['facebook'] ?? '' }}">
                                    <small class="text-muted">Đường dẫn Facebook page của công ty</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="zalo" class="form-label fw-bold">
                                        <i class="fas fa-comments text-primary me-2"></i> Zalo
                                    </label>
                                    <input type="url" class="form-control" id="zalo" name="zalo"
                                           placeholder="https://zalo.me/..." value="{{ $settings['zalo'] ?? '' }}">
                                    <small class="text-muted">Đường dẫn Zalo của công ty</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.settings') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert-success').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
</script>
@endpush

@endsection