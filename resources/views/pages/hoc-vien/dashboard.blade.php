@extends('layouts.app', ['title' => 'Dashboard Hoc Vien'])

@section('content')
@php
    $hasBaiKiemTraRoute = Route::has('hoc-vien.bai-kiem-tra');
    $hasKetQuaRoute = Route::has('hoc-vien.ket-qua');
@endphp

<div class="container-fluid">
    <div class="card vip-card border-0 mb-4 student-hero">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge rounded-pill bg-light text-primary">Hoc vien</span>
                        <span class="badge rounded-pill bg-light text-success">
                            {{ $dashboardStats['khoa_hoc_dang_hoc'] }} khoa hoc dang hoc
                        </span>
                        @if($dashboardStats['yeu_cau_dang_cho_duyet'] > 0)
                            <span class="badge rounded-pill bg-light text-warning">
                                {{ $dashboardStats['yeu_cau_dang_cho_duyet'] }} yeu cau cho duyet
                            </span>
                        @endif
                    </div>

                    <h2 class="fw-bold mb-2">Chao mung tro lai, {{ auth()->user()->ho_ten }}!</h2>

                    @if($dashboardStats['khoa_hoc_dang_hoc'] > 0)
                        <p class="mb-0 hero-text">
                            Hom nay ban co {{ $dashboardStats['buoi_hoc_hom_nay'] }} buoi hoc,
                            {{ $dashboardStats['buoi_hoc_sap_toi'] }} buoi sap toi
                            va {{ $dashboardStats['tai_lieu_moi_7_ngay'] }} tai lieu moi trong 7 ngay gan day.
                        </p>
                    @else
                        <p class="mb-0 hero-text">
                            Ban chua co khoa hoc dang hoc. Hay vao danh sach khoa hoc co the tham gia de gui yeu cau vao lop.
                        </p>
                    @endif
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="hero-box ms-lg-auto">
                        <i class="fas fa-user-graduate"></i>
                        <div class="small mt-3 text-white-50">Tien do tong quan</div>
                        <div class="fs-3 fw-bold">{{ $dashboardStats['tien_do_tong_quan'] }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card vip-card stat-panel h-100">
                <div class="card-body">
                    <div class="stat-icon text-primary"><i class="fas fa-book-open"></i></div>
                    <div class="stat-label">Khoa hoc dang hoc</div>
                    <div class="stat-value">{{ $dashboardStats['khoa_hoc_dang_hoc'] }}</div>
                    <div class="stat-note">{{ $tienDoKhoaHoc->count() }} khoa hoc da ghi danh</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card vip-card stat-panel h-100">
                <div class="card-body">
                    <div class="stat-icon text-info"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-label">Buoi hoc sap toi</div>
                    <div class="stat-value">{{ $dashboardStats['buoi_hoc_sap_toi'] }}</div>
                    <div class="stat-note">{{ $dashboardStats['buoi_hoc_hom_nay'] }} buoi dien ra hom nay</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card vip-card stat-panel h-100">
                <div class="card-body">
                    <div class="stat-icon text-success"><i class="fas fa-folder-open"></i></div>
                    <div class="stat-label">Tai lieu cong khai</div>
                    <div class="stat-value">{{ $dashboardStats['tai_lieu_cong_khai'] }}</div>
                    <div class="stat-note">{{ $dashboardStats['buoi_co_tai_lieu'] }} buoi da co tai lieu</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card vip-card stat-panel h-100">
                <div class="card-body">
                    <div class="stat-icon text-warning"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-label">Tien do tong quan</div>
                    <div class="stat-value">{{ $dashboardStats['tien_do_tong_quan'] }}%</div>
                    <div class="stat-note">
                        {{ $dashboardStats['tong_buoi_hoan_thanh'] }}/{{ $dashboardStats['tong_buoi_hoc'] }} buoi da hoan thanh
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="card vip-card h-100">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Tien do theo khoa hoc</h5>
                        <p class="text-muted small mb-0">Du lieu duoc tinh tu cac buoi hoc da len lich trong tung khoa.</p>
                    </div>
                    <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn vip-btn vip-btn-primary btn-sm">
                        Xem khoa hoc cua toi
                    </a>
                </div>
                <div class="card-body">
                    @if($tienDoKhoaHoc->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table align-middle dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Khoa hoc</th>
                                        <th>Nhom nganh</th>
                                        <th>Tien do</th>
                                        <th>Buoi ke tiep</th>
                                        <th>Trang thai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tienDoKhoaHoc as $dong)
                                        @php
                                            $khoaHoc = $dong['khoa_hoc'];
                                            $ghiDanh = $dong['ghi_danh'];
                                            $buoiSapToiTheoKhoa = $dong['buoi_sap_toi'];
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $khoaHoc->ten_khoa_hoc }}</div>
                                                <div class="small text-muted">{{ $khoaHoc->ma_khoa_hoc ?: 'Chua co ma khoa hoc' }}</div>
                                                <div class="small text-muted">
                                                    Tham gia:
                                                    {{ optional($ghiDanh->ngay_tham_gia)->format('d/m/Y') ?: optional($ghiDanh->created_at)->format('d/m/Y') }}
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ optional($khoaHoc->nhomNganh)->ten_nhom_nganh ?: 'Chua cap nhat' }}</div>
                                                <div class="small text-muted">{{ $dong['buoi_online'] }} buoi online</div>
                                            </td>
                                            <td style="min-width: 220px;">
                                                <div class="d-flex justify-content-between small mb-2">
                                                    <span>{{ $dong['buoi_hoan_thanh'] }}/{{ $dong['tong_buoi'] }} buoi</span>
                                                    <strong>{{ $dong['tien_do'] }}%</strong>
                                                </div>
                                                <div class="progress progress-thin">
                                                    <div class="progress-bar bg-primary progress-live"
                                                         role="progressbar"
                                                         style="width: {{ $dong['tien_do'] }}%;"
                                                         aria-valuenow="{{ $dong['tien_do'] }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($buoiSapToiTheoKhoa)
                                                    <div class="fw-semibold text-dark">{{ $buoiSapToiTheoKhoa->ngay_hoc->format('d/m/Y') }}</div>
                                                    <div class="small text-muted">
                                                        {{ $buoiSapToiTheoKhoa->gio_bat_dau ?: '--:--' }}
                                                        @if($buoiSapToiTheoKhoa->gio_ket_thuc)
                                                            - {{ $buoiSapToiTheoKhoa->gio_ket_thuc }}
                                                        @endif
                                                    </div>
                                                    <div class="small text-muted">{{ $buoiSapToiTheoKhoa->hinh_thuc_label }}</div>
                                                @else
                                                    <span class="text-muted small">Chua co buoi hoc sap toi</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $ghiDanh->trang_thai_badge }}">{{ $ghiDanh->trang_thai_label }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="empty-icon mb-3"><i class="fas fa-book-reader"></i></div>
                            <h5 class="fw-semibold">Ban chua co khoa hoc nao</h5>
                            <p class="text-muted mb-3">Tien do hoc tap se xuat hien tai day khi ban duoc them vao khoa hoc.</p>
                            <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn vip-btn vip-btn-primary">
                                Xem khoa hoc co the tham gia
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card vip-card mb-4">
                <div class="card-header border-0">
                    <h5 class="mb-1 fw-semibold">Buoi hoc sap toi</h5>
                    <p class="text-muted small mb-0">Chi hien thi cac buoi thuoc khoa hoc dang hoc.</p>
                </div>
                <div class="card-body">
                    @forelse($buoiSapToi as $lichHoc)
                        <div class="upcoming-item">
                            <div class="fw-semibold text-dark">{{ $lichHoc->khoaHoc->ten_khoa_hoc }}</div>
                            <div class="small text-muted">{{ $lichHoc->moduleHoc->ten_module ?? 'Chua gan module' }}</div>
                            <div class="small text-muted">
                                {{ $lichHoc->ngay_hoc->format('d/m/Y') }} •
                                {{ $lichHoc->gio_bat_dau ?: '--:--' }}
                                @if($lichHoc->gio_ket_thuc)
                                    - {{ $lichHoc->gio_ket_thuc }}
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="badge bg-{{ $lichHoc->hinh_thuc_color }}">{{ $lichHoc->hinh_thuc_label }}</span>
                                <span class="badge bg-{{ $lichHoc->trang_thai_color }}">{{ $lichHoc->trang_thai_label }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $lichHoc->khoa_hoc_id) }}" class="btn btn-sm btn-outline-primary">
                                    Xem khoa hoc
                                </a>
                                @if($lichHoc->can_join_online)
                                    <a href="{{ $lichHoc->link_online }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                        Vao phong hoc
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <div class="empty-icon mb-3"><i class="fas fa-calendar-check"></i></div>
                            <p class="text-muted mb-0">Chua co buoi hoc sap toi.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Thao tac nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn btn-outline-primary w-100 quick-btn">
                                <i class="fas fa-book-open mb-2"></i>
                                <span>Khoa hoc cua toi</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn btn-outline-success w-100 quick-btn">
                                <i class="fas fa-door-open mb-2"></i>
                                <span>Xin vao lop</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('hoc-vien.profile') }}" class="btn btn-outline-warning w-100 quick-btn">
                                <i class="fas fa-user mb-2"></i>
                                <span>Ho so ca nhan</span>
                            </a>
                        </div>
                        <div class="col-6">
                            @if($hasKetQuaRoute)
                                <a href="{{ route('hoc-vien.ket-qua') }}" class="btn btn-outline-info w-100 quick-btn">
                                    <i class="fas fa-chart-column mb-2"></i>
                                    <span>Ket qua hoc tap</span>
                                </a>
                            @else
                                <button type="button" class="btn btn-outline-secondary w-100 quick-btn" disabled>
                                    <i class="fas fa-chart-column mb-2"></i>
                                    <span>Ket qua hoc tap</span>
                                </button>
                            @endif
                        </div>
                        <div class="col-12">
                            @if($hasBaiKiemTraRoute)
                                <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="btn btn-outline-dark w-100 quick-btn">
                                    <i class="fas fa-list-check mb-2"></i>
                                    <span>Bai kiem tra</span>
                                </a>
                            @else
                                <button type="button" class="btn btn-outline-secondary w-100 quick-btn" disabled>
                                    <i class="fas fa-list-check mb-2"></i>
                                    <span>Bai kiem tra se mo o phase sau</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card vip-card">
        <div class="card-header border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-semibold">Tai lieu moi duoc mo</h5>
                <p class="text-muted small mb-0">Chi lay cac tai lieu giang vien da cong khai cho hoc vien.</p>
            </div>
            <span class="badge bg-light text-success">{{ $dashboardStats['tai_lieu_moi_7_ngay'] }} moi / 7 ngay</span>
        </div>
        <div class="card-body">
            @if($taiLieuMoi->isNotEmpty())
                <div class="row g-3">
                    @foreach($taiLieuMoi as $taiLieu)
                        <div class="col-xl-4 col-md-6">
                            <div class="resource-box h-100">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-{{ $taiLieu->loai_color }}">{{ $taiLieu->loai_label }}</span>
                                    <span class="badge bg-light text-{{ $taiLieu->nguon_hien_thi_color }} border">
                                        {{ $taiLieu->nguon_hien_thi_label }}
                                    </span>
                                </div>

                                <h6 class="fw-semibold text-dark">{{ $taiLieu->tieu_de }}</h6>
                                <p class="text-muted small mb-3">
                                    {{ \Illuminate\Support\Str::limit($taiLieu->mo_ta ?: $taiLieu->file_status_message, 120) }}
                                </p>

                                <div class="small text-muted mb-1">
                                    <i class="fas fa-book me-1"></i>
                                    {{ optional(optional($taiLieu->lichHoc)->khoaHoc)->ten_khoa_hoc ?: 'Chua xac dinh khoa hoc' }}
                                </div>
                                <div class="small text-muted mb-1">
                                    <i class="fas fa-layer-group me-1"></i>
                                    {{ optional(optional($taiLieu->lichHoc)->moduleHoc)->ten_module ?: 'Chua gan module' }}
                                </div>
                                <div class="small text-muted mb-3">
                                    <i class="far fa-clock me-1"></i>
                                    {{ optional($taiLieu->created_at)->diffForHumans() }}
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-auto">
                                    <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', optional($taiLieu->lichHoc)->khoa_hoc_id) }}" class="btn btn-sm btn-outline-primary">
                                        Xem trong khoa hoc
                                    </a>
                                    @if($taiLieu->is_external)
                                        <a href="{{ $taiLieu->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                            Mo lien ket
                                        </a>
                                    @elseif($taiLieu->is_downloadable)
                                        <a href="{{ $taiLieu->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                            Xem file
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                            File khong kha dung
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-icon mb-3"><i class="fas fa-folder-open"></i></div>
                    <h5 class="fw-semibold">Chua co tai lieu cong khai moi</h5>
                    <p class="text-muted mb-0">Khi giang vien dang va bat hien thi tai lieu cho buoi hoc, chung se xuat hien tai day.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .student-hero {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #0f766e 100%);
        color: #fff;
    }

    .hero-text {
        color: rgba(255, 255, 255, 0.86);
        line-height: 1.7;
    }

    .hero-box {
        width: 170px;
        min-height: 170px;
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.16);
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
    }

    .hero-box i {
        font-size: 2.8rem;
    }

    .stat-panel .card-body {
        padding: 1.5rem;
    }

    .stat-icon {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.1;
        margin-bottom: 0.35rem;
    }

    .stat-note {
        color: #64748b;
        font-size: 0.92rem;
    }

    .dashboard-table thead th {
        border-top: none;
        color: #475569;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .progress-thin {
        height: 8px;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .upcoming-item {
        padding: 1rem 0;
        border-bottom: 1px solid #eef2f7;
    }

    .upcoming-item:first-child {
        padding-top: 0;
    }

    .upcoming-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .quick-btn {
        min-height: 104px;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        gap: 0.25rem;
    }

    .resource-box {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1.25rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        display: flex;
        flex-direction: column;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .resource-box:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.08);
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto;
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bars = document.querySelectorAll('.progress-live');

        if (!bars.length || typeof IntersectionObserver === 'undefined') {
            return;
        }

        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const bar = entry.target;
                const finalWidth = bar.style.width;
                bar.style.width = '0%';

                window.setTimeout(() => {
                    bar.style.width = finalWidth;
                }, 120);

                currentObserver.unobserve(bar);
            });
        }, { threshold: 0.3 });

        bars.forEach((bar) => observer.observe(bar));
    });
</script>
@endpush
@endsection
