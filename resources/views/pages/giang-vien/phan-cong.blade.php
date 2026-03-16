@extends('layouts.app')

@section('title', 'Quản lý phân công giảng dạy')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i> Quản lý phân công giảng dạy</h3>
            <p class="text-muted">Xem và phản hồi các yêu cầu phân công giảng dạy các module học tập.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-body p-0">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-fill" id="phanCongTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-3 fw-bold" id="cho-xn-tab" data-bs-toggle="tab" data-bs-target="#cho-xn" type="button" role="tab">
                                <i class="fas fa-clock me-1 text-warning"></i> Chờ xác nhận
                                @if($phanCongChoXacNhan->count() > 0)
                                    <span class="badge bg-danger ms-1 rounded-pill">{{ $phanCongChoXacNhan->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3 fw-bold" id="da-nhan-tab" data-bs-toggle="tab" data-bs-target="#da-nhan" type="button" role="tab">
                                <i class="fas fa-check-circle me-1 text-success"></i> Đang dạy
                                <span class="badge bg-secondary ms-1 rounded-pill">{{ $phanCongDaNhan->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3 fw-bold" id="lich-su-tab" data-bs-toggle="tab" data-bs-target="#lich-su" type="button" role="tab">
                                <i class="fas fa-history me-1 text-muted"></i> Lịch sử / Từ chối
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-4" id="phanCongTabsContent">
                        <!-- Tab 1: Chờ xác nhận -->
                        <div class="tab-pane fade show active" id="cho-xn" role="tabpanel">
                            @forelse($phanCongChoXacNhan as $pc)
                                <div class="card mb-3 border shadow-sm hover-shadow transition-all">
                                    <div class="card-body p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-8 border-end">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge bg-primary me-2">{{ $pc->moduleHoc->ma_module }}</span>
                                                    <h5 class="fw-bold mb-0 text-dark">{{ $pc->moduleHoc->ten_module }}</h5>
                                                </div>
                                                <p class="mb-1 text-dark">
                                                    <i class="fas fa-graduation-cap me-1 text-muted"></i> 
                                                    Khóa học: <strong>{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</strong>
                                                </p>
                                                <div class="d-flex flex-wrap gap-3 small text-muted mb-3">
                                                    <span><i class="fas fa-layer-group me-1"></i> Ngành: {{ $pc->moduleHoc->khoaHoc->nhomNganh->ten_nhom_nganh }}</span>
                                                    <span>
                                                        <i class="fas fa-clock me-1"></i> 
                                                        @php
                                                            $h = intdiv($pc->moduleHoc->thoi_luong_du_kien, 60);
                                                            $m = $pc->moduleHoc->thoi_luong_du_kien % 60;
                                                        @endphp
                                                        {{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}
                                                    </span>
                                                    <span><i class="fas fa-calendar-alt me-1"></i> Ngày PC: {{ $pc->ngay_phan_cong ? $pc->ngay_phan_cong->format('d/m/Y') : $pc->created_at->format('d/m/Y') }}</span>
                                                </div>
                                                @if($pc->ghi_chu)
                                                    <div class="bg-light p-2 rounded border-start border-warning border-4 small">
                                                        <i class="fas fa-sticky-note me-1"></i> <strong>Ghi chú từ Admin:</strong> {{ $pc->ghi_chu }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                <div class="d-flex flex-column gap-2 ps-md-3">
                                                    <form action="{{ route('giang-vien.phan-cong.xac-nhan', $pc->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">
                                                            <i class="fas fa-check me-1"></i> Xác nhận nhận dạy
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('giang-vien.phan-cong.tu-choi', $pc->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn từ chối phân công này?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger w-100 fw-bold">
                                                            <i class="fas fa-times me-1"></i> Từ chối yêu cầu
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted bg-light rounded">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Hiện không có yêu cầu phân công nào đang chờ bạn xác nhận.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Tab 2: Đang dạy -->
                        <div class="tab-pane fade" id="da-nhan" role="tabpanel">
                            @forelse($phanCongDaNhan as $pc)
                                <div class="card mb-3 border shadow-sm">
                                    <div class="card-body p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-9">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge bg-success me-2">{{ $pc->moduleHoc->ma_module }}</span>
                                                    <h5 class="fw-bold mb-0">{{ $pc->moduleHoc->ten_module }}</h5>
                                                </div>
                                                <p class="mb-1 text-dark">
                                                    <i class="fas fa-graduation-cap me-1 text-muted"></i> 
                                                    Khóa học: <strong>{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</strong>
                                                </p>
                                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                                    <span><i class="fas fa-layer-group me-1"></i> Ngành: {{ $pc->moduleHoc->khoaHoc->nhomNganh->ten_nhom_nganh }}</span>
                                                    <span>
                                                        <i class="fas fa-clock me-1"></i> 
                                                        @php
                                                            $h = intdiv($pc->moduleHoc->thoi_luong_du_kien, 60);
                                                            $m = $pc->moduleHoc->thoi_luong_du_kien % 60;
                                                        @endphp
                                                        {{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}
                                                    </span>
                                                    <span class="text-success"><i class="fas fa-calendar-check me-1"></i> Đã tiếp nhận</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                                <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm px-4 fw-bold">
                                                    <i class="fas fa-folder-open me-1"></i> Vào lớp học
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted bg-light rounded">
                                    <i class="fas fa-chalkboard fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Bạn chưa có module nào đang giảng dạy.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Tab 3: Lịch sử -->
                        <div class="tab-pane fade" id="lich-su" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle border">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3">Module / Mã</th>
                                            <th>Khóa học</th>
                                            <th class="text-center">Ngày phân công</th>
                                            <th class="text-center">Trạng thái</th>
                                            <th>Ghi chú phản hồi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lichSu as $pc)
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="fw-bold">{{ $pc->moduleHoc->ten_module }}</div>
                                                    <code class="small text-muted">{{ $pc->moduleHoc->ma_module }}</code>
                                                </td>
                                                <td><small class="fw-bold text-dark">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</small></td>
                                                <td class="text-center small text-muted">{{ $pc->ngay_phan_cong ? $pc->ngay_phan_cong->format('d/m/Y') : $pc->created_at->format('d/m/Y') }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger rounded-pill px-3">Đã từ chối</span>
                                                </td>
                                                <td><small class="text-muted italic">{{ $pc->ghi_chu ?: 'Không có phản hồi' }}</small></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted small italic">Không có lịch sử phân công bị từ chối.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bootstrap tab handling with URL hash
        const hash = window.location.hash;
        if (hash) {
            const triggerEl = document.querySelector(`.nav-link[data-bs-target="${hash}"]`);
            if (triggerEl) {
                bootstrap.Tab.getOrCreateInstance(triggerEl).show();
            }
        }

        const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabEls.forEach(el => {
            el.addEventListener('shown.bs.tab', function(event) {
                window.location.hash = event.target.getAttribute('data-bs-target');
            });
        });
    });
</script>

<style>
    .hover-shadow:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        transform: translateY(-2px);
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
    }
    .nav-tabs .nav-link:hover {
        color: #0d6efd;
        background-color: #f8f9fa;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background: transparent;
        border-bottom: 3px solid #0d6efd;
    }
</style>
@endsection
