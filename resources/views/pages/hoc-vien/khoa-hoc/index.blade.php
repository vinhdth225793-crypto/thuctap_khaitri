@extends('layouts.app')

@section('title', 'Khóa học của tôi')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Khóa học của tôi</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-graduation-cap fa-lg"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">Lộ trình học tập</h3>
                    <div class="text-muted small mt-1">Danh sách các khóa học bạn đang tham gia</div>
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        @forelse($khoaHocs as $kh)
            @php $khoa = $kh->khoaHoc; @endphp
            <div class="col-xl-4 col-lg-6 mb-4">
                <div class="card vip-card border-0 shadow-sm h-100 overflow-hidden hover-lift">
                    <div class="position-relative">
                        <img src="{{ $khoa->hinh_anh ? asset($khoa->hinh_anh) : asset('images/default-course.svg') }}" 
                             class="card-img-top object-fit-cover" style="height: 180px;">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge {{ $kh->trang_thai_badge }} shadow-sm px-3 py-2">
                                {{ $kh->trang_thai_label }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-2">
                            <span class="badge bg-primary-soft text-primary small border-0">
                                {{ $khoa->nhomNganh->ten_nhom_nganh ?? 'Chưa phân nhóm' }}
                            </span>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-3 line-clamp-2" style="height: 3rem;">{{ $khoa->ten_khoa_hoc }}</h5>
                        
                        <div class="row g-2 mb-4 small text-muted">
                            <div class="col-6"><i class="far fa-calendar-alt me-1"></i> Khai giảng:</div>
                            <div class="col-6 text-end fw-bold text-dark">{{ $khoa->ngay_khai_giang?->format('d/m/Y') ?: '—' }}</div>
                            
                            <div class="col-6"><i class="fas fa-signal me-1"></i> Trình độ:</div>
                            <div class="col-6 text-end fw-bold text-dark text-capitalize">{{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$khoa->cap_do] ?? 'N/A' }}</div>
                        </div>

                        <div class="d-grid">
                            <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $khoa->id) }}" class="btn btn-primary fw-bold py-2 shadow-sm">
                                <i class="fas fa-rocket me-2"></i> VÀO HỌC NGAY
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="bg-light d-inline-block p-4 rounded-circle mb-3">
                    <i class="fas fa-book-reader fa-3x text-muted opacity-50"></i>
                </div>
                <h5 class="text-muted">Bạn chưa tham gia khóa học nào.</h5>
                <p class="text-muted small">Hãy liên hệ với quản trị viên để được ghi danh vào lớp học.</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
