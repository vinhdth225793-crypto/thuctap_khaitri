@extends('layouts.app', ['title' => 'Tìm Kiếm Giảng Viên'])

@section('content')
<div class="container py-4">
    <!-- Search Header -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-5 fw-bold mb-3">Kết Quả Tìm Kiếm Giảng Viên</h1>
            <p class="lead text-muted mb-4">
                @if($keyword)
                    Kết quả tìm kiếm cho "<strong>{{ $keyword }}</strong>"
                    @if($loaiSearch !== 'all')
                        trong chuyên ngành <strong>{{ $loaiSearch }}</strong>
                    @endif
                @else
                    Danh sách giảng viên
                @endif
            </p>

            <!-- Search Form -->
            <form action="{{ route('tim-giang-vien') }}" method="GET" class="mb-4">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control border-0" name="q" placeholder="Tìm kiếm tên hoặc chuyên ngành..." value="{{ request('q') }}">
                    <select class="form-select border-0" name="type">
                        <option value="all" {{ request('type') === 'all' ? 'selected' : '' }}>Tất cả chuyên ngành</option>
                        <option value="Lập trình" {{ request('type') === 'Lập trình' ? 'selected' : '' }}>Lập trình</option>
                        <option value="Thiết kế" {{ request('type') === 'Thiết kế' ? 'selected' : '' }}>Thiết kế</option>
                        <option value="Kinh doanh" {{ request('type') === 'Kinh doanh' ? 'selected' : '' }}>Kinh doanh</option>
                        <option value="Marketing" {{ request('type') === 'Marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="Khác" {{ request('type') === 'Khác' ? 'selected' : '' }}>Khác</option>
                    </select>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search me-2"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    @if($giangVien->count() > 0)
    <div class="row g-4 mb-5">
        @foreach($giangVien as $gv)
        <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <div class="card instructor-card h-100 shadow-sm rounded-4 overflow-hidden transition-all">
                <div class="instructor-image-wrapper position-relative">
                    @if($gv->avatar_url)
                        <img src="{{ $gv->avatar_url }}" alt="{{ $gv->nguoiDung->ho_ten }}" class="w-100 object-fit-cover" style="height: 250px;">
                    @else
                        <div class="bg-gradient-primary d-flex align-items-center justify-content-center text-white" style="height: 250px;">
                            <i class="fas fa-user-tie fa-5x opacity-50"></i>
                        </div>
                    @endif
                    <div class="specialist-badge position-absolute top-3 end-3">
                        <span class="badge bg-success">{{ $gv->chuyen_nganh }}</span>
                    </div>
                </div>
                
                <div class="instructor-info p-4">
                    <h5 class="card-title fw-bold mb-2">{{ $gv->nguoiDung->ho_ten }}</h5>
                    
                    <p class="specialist-text text-primary fw-semibold mb-2">
                        <i class="fas fa-star me-1"></i> {{ $gv->chuyen_nganh }}
                    </p>
                    
                    @if($gv->hoc_vi)
                    <p class="degree-text text-muted small mb-2">
                        <i class="fas fa-graduation-cap me-1"></i> {{ $gv->hoc_vi }}
                    </p>
                    @endif
                    
                    @if($gv->so_gio_day)
                    <p class="experience-text text-muted small mb-3">
                        <i class="fas fa-clock me-1"></i> {{ $gv->so_gio_day }} giờ giảng dạy
                    </p>
                    @endif
                    
                    @if($gv->mo_ta_ngan)
                    <p class="description-text text-muted small mb-3">{{ Str::limit($gv->mo_ta_ngan, 100) }}</p>
                    @endif
                    
                    @auth
                    <a href="#" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-message me-1"></i> Liên hệ
                    </a>
                    @else
                    <a href="{{ route('dang-ky') }}" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-1"></i> Đăng ký để liên hệ
                    </a>
                    @endauth
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($giangVien->hasPages())
    <div class="d-flex justify-content-center">
        {{ $giangVien->links('pagination::bootstrap-5') }}
    </div>
    @endif

    @else
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="alert alert-info text-center py-5 rounded-4" role="alert">
                <i class="fas fa-search fa-3x mb-3 d-block text-muted"></i>
                <h5 class="fw-bold">Không tìm thấy giảng viên</h5>
                <p class="text-muted mb-0">
                    Rất tiếc, không có giảng viên nào phù hợp với tiêu chí tìm kiếm của bạn.
                </p>
            </div>

            <div class="text-center mt-5">
                <p class="text-muted mb-3">Bạn có thể:</p>
                <a href="{{ route('home') }}" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-home me-2"></i> Quay về trang chủ
                </a>
                <a href="{{ route('tim-giang-vien') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-redo me-2"></i> Xóa bộ lọc
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    :root {
        --gradient-primary: linear-gradient(135deg, #4361ee 0%, #2f46c9 100%);
    }

    .bg-gradient-primary {
        background: var(--gradient-primary) !important;
    }

    .instructor-card {
        border: none;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        border-radius: 20px;
    }

    .instructor-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
    }

    .instructor-image-wrapper {
        overflow: hidden;
        border-radius: 20px 20px 0 0;
    }

    .instructor-image-wrapper img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .instructor-card:hover .instructor-image-wrapper img {
        transform: scale(1.05);
    }

    .specialist-badge {
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .instructor-info {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .instructor-info .btn {
        margin-top: auto;
    }

    .transition-all {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .object-fit-cover {
        object-fit: cover;
    }

    .input-group {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .input-group .form-control,
    .input-group .form-select {
        border: none !important;
        padding: 15px 20px;
        font-size: 1rem;
    }

    .input-group .btn {
        padding: 15px 30px;
        font-weight: 600;
        border: none;
    }

    @media (max-width: 768px) {
        .input-group {
            flex-direction: column;
        }

        .input-group .form-select {
            border-top: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });
</script>
@endpush

@endsection
