@extends('layouts.app', ['title' => 'Bai kiem tra cua hoc vien'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Bai kiem tra cua toi</h2>
            <p class="text-muted mb-0">Danh sach bai kiem tra thuoc cac khoa hoc ban dang tham gia.</p>
        </div>

        <a href="{{ route('hoc-vien.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Ve dashboard
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 stat-card-lite">
                <div class="card-body">
                    <div class="stat-label">Tong bai kiem tra</div>
                    <div class="stat-value">{{ $stats['tong'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 stat-card-lite">
                <div class="card-body">
                    <div class="stat-label">Dang mo</div>
                    <div class="stat-value text-success">{{ $stats['dang_mo'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 stat-card-lite">
                <div class="card-body">
                    <div class="stat-label">Sap mo</div>
                    <div class="stat-value text-warning">{{ $stats['sap_mo'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 stat-card-lite">
                <div class="card-body">
                    <div class="stat-label">Da nop</div>
                    <div class="stat-value text-primary">{{ $stats['da_nop'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($baiKiemTras as $baiKiemTra)
            @php
                $baiLam = $baiKiemTra->baiLams->first();
            @endphp
            <div class="col-xl-4 col-lg-6">
                <div class="card vip-card h-100 test-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1">{{ $baiKiemTra->tieu_de }}</h5>
                                <div class="small text-muted">
                                    {{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Chua xac dinh khoa hoc' }}
                                </div>
                            </div>
                            <span class="badge bg-{{ $baiKiemTra->access_status_color }}">{{ $baiKiemTra->access_status_label }}</span>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-dark border">{{ $baiKiemTra->pham_vi_label }}</span>
                            <span class="badge bg-light text-dark border">{{ $baiKiemTra->thoi_gian_lam_bai }} phut</span>
                            @if($baiLam)
                                <span class="badge bg-{{ $baiLam->trang_thai_color }}">{{ $baiLam->trang_thai_label }}</span>
                            @endif
                        </div>

                        <div class="small text-muted mb-2">
                            <i class="fas fa-layer-group me-1"></i>
                            {{ $baiKiemTra->moduleHoc->ten_module ?? 'Chua gan module' }}
                        </div>

                        @if($baiKiemTra->lichHoc)
                            <div class="small text-muted mb-2">
                                <i class="fas fa-calendar-day me-1"></i>
                                Buoi {{ $baiKiemTra->lichHoc->buoi_so ?: '#' }} -
                                {{ optional($baiKiemTra->lichHoc->ngay_hoc)->format('d/m/Y') ?: 'Chua co ngay hoc' }}
                            </div>
                        @endif

                        <div class="small text-muted mb-2">
                            <i class="far fa-clock me-1"></i>
                            Mo:
                            {{ $baiKiemTra->ngay_mo ? $baiKiemTra->ngay_mo->format('d/m/Y H:i') : 'Mo ngay' }}
                        </div>
                        <div class="small text-muted mb-3">
                            <i class="fas fa-hourglass-end me-1"></i>
                            Dong:
                            {{ $baiKiemTra->ngay_dong ? $baiKiemTra->ngay_dong->format('d/m/Y H:i') : 'Chua dat lich dong' }}
                        </div>

                        <p class="text-muted small mb-4">
                            {{ \Illuminate\Support\Str::limit($baiKiemTra->mo_ta ?: 'Chua co mo ta cho bai kiem tra nay.', 130) }}
                        </p>

                        <div class="mt-auto d-flex flex-wrap gap-2">
                            <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="btn btn-outline-primary">
                                Xem chi tiet
                            </a>

                            @if($baiLam && $baiLam->is_submitted)
                                <span class="btn btn-success disabled">Da nop bai</span>
                            @elseif($baiKiemTra->can_student_start)
                                <form action="{{ route('hoc-vien.bai-kiem-tra.bat-dau', $baiKiemTra->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        {{ $baiLam ? 'Tiep tuc lam bai' : 'Bat dau lam bai' }}
                                    </button>
                                </form>
                            @else
                                <span class="btn btn-secondary disabled">{{ $baiKiemTra->access_status_label }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card vip-card">
                    <div class="card-body text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="fas fa-file-circle-question"></i>
                        </div>
                        <h5 class="fw-semibold">Chua co bai kiem tra nao</h5>
                        <p class="text-muted mb-0">Khi giang vien tao bai kiem tra cho khoa hoc ban tham gia, danh sach se xuat hien tai day.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    .stat-card-lite .card-body {
        padding: 1.5rem;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.05;
        color: #0f172a;
    }

    .test-card {
        border: 1px solid #e2e8f0;
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: #eff6ff;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
</style>
@endpush
@endsection
