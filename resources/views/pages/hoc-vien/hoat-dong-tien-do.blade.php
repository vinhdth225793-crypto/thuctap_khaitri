@extends('layouts.app', ['title' => 'Hoat dong va tien do hoc tap'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Hoat dong &amp; tien do hoc tap</h2>
            <p class="text-muted mb-0">Theo doi buoi hoc sap toi, tai lieu moi, chuyen can va tien do theo tung khoa hoc.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('hoc-vien.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn vip-btn vip-btn-primary">
                <i class="fas fa-book-open me-2"></i>Khoa hoc cua toi
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 summary-card">
                <div class="card-body">
                    <div class="summary-icon text-primary"><i class="fas fa-calendar-day"></i></div>
                    <div class="summary-label">Buoi hoc sap toi</div>
                    <div class="summary-value">{{ $dashboardStats['buoi_hoc_sap_toi'] }}</div>
                    <div class="summary-note">{{ $dashboardStats['buoi_hoc_hom_nay'] }} buoi dien ra hom nay</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 summary-card">
                <div class="card-body">
                    <div class="summary-icon text-success"><i class="fas fa-folder-open"></i></div>
                    <div class="summary-label">Tai lieu moi</div>
                    <div class="summary-value">{{ $dashboardStats['tai_lieu_moi_7_ngay'] }}</div>
                    <div class="summary-note">{{ $dashboardStats['tai_lieu_cong_khai'] }} tai lieu cong khai</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 summary-card">
                <div class="card-body">
                    <div class="summary-icon text-warning"><i class="fas fa-user-check"></i></div>
                    <div class="summary-label">Ty le chuyen can</div>
                    <div class="summary-value">{{ $chuyenCanTongQuan['ty_le_tham_du'] }}%</div>
                    <div class="summary-note">{{ $chuyenCanTongQuan['tong'] }} lan diem danh da ghi nhan</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card vip-card h-100 summary-card">
                <div class="card-body">
                    <div class="summary-icon text-info"><i class="fas fa-chart-line"></i></div>
                    <div class="summary-label">Tien do tong quan</div>
                    <div class="summary-value">{{ $dashboardStats['tien_do_tong_quan'] }}%</div>
                    <div class="summary-note">{{ $dashboardStats['tong_buoi_hoan_thanh'] }}/{{ $dashboardStats['tong_buoi_hoc'] }} buoi da hoan thanh</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card vip-card h-100">
                <div class="card-header border-0">
                    <h5 class="mb-1 fw-semibold">Hoat dong gan day</h5>
                    <p class="text-muted small mb-0">Tong hop tu diem danh, tai lieu moi va cac buoi hoc da hoan thanh.</p>
                </div>
                <div class="card-body">
                    @forelse($hoatDongGanDay as $hoatDong)
                        <div class="activity-item">
                            <div class="activity-icon bg-{{ $hoatDong['color'] }}">
                                <i class="fas {{ $hoatDong['icon'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark">{{ $hoatDong['title'] }}</div>
                                <div class="text-muted small">{{ $hoatDong['description'] ?: 'Khong co mo ta bo sung' }}</div>
                                <div class="small text-muted mt-1">
                                    {{ $hoatDong['meta'] ?: 'Chua cap nhat' }}
                                    @if(!empty($hoatDong['sort_at']))
                                        • {{ $hoatDong['sort_at']->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="empty-icon mb-3"><i class="fas fa-stream"></i></div>
                            <h6 class="fw-semibold">Chua co hoat dong nao de hien thi</h6>
                            <p class="text-muted mb-0">Hoat dong se xuat hien sau khi hoc vien bat dau hoc, diem danh hoac giang vien cong khai tai lieu.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card vip-card h-100">
                <div class="card-header border-0">
                    <h5 class="mb-1 fw-semibold">Chuyen can tong quan</h5>
                    <p class="text-muted small mb-0">Su dung du lieu diem danh da duoc giang vien ghi nhan.</p>
                </div>
                <div class="card-body">
                    <div class="attendance-progress mb-4">
                        <div class="d-flex justify-content-between small mb-2">
                            <span>Ty le tham du</span>
                            <strong>{{ $chuyenCanTongQuan['ty_le_tham_du'] }}%</strong>
                        </div>
                        <div class="progress progress-thin">
                            <div class="progress-bar bg-success progress-live"
                                 role="progressbar"
                                 style="width: {{ $chuyenCanTongQuan['ty_le_tham_du'] }}%;"
                                 aria-valuenow="{{ $chuyenCanTongQuan['ty_le_tham_du'] }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="attendance-box attendance-success">
                                <div class="attendance-value">{{ $chuyenCanTongQuan['co_mat'] }}</div>
                                <div class="attendance-label">Co mat</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="attendance-box attendance-warning">
                                <div class="attendance-value">{{ $chuyenCanTongQuan['vao_tre'] }}</div>
                                <div class="attendance-label">Vao tre</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="attendance-box attendance-danger">
                                <div class="attendance-value">{{ $chuyenCanTongQuan['vang_mat'] }}</div>
                                <div class="attendance-label">Vang mat</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-semibold mb-3">Diem danh gan day</h6>
                    @forelse($diemDanhGanDay as $diemDanh)
                        @php
                            $trangThaiLabel = match ($diemDanh->trang_thai) {
                                'co_mat' => 'Co mat',
                                'vao_tre' => 'Vao tre',
                                'vang_mat' => 'Vang mat',
                                default => 'Chua ro',
                            };

                            $trangThaiColor = match ($diemDanh->trang_thai) {
                                'co_mat' => 'success',
                                'vao_tre' => 'warning',
                                'vang_mat' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <div class="attendance-log">
                            <div class="d-flex justify-content-between gap-2">
                                <div class="fw-semibold text-dark">
                                    {{ optional(optional($diemDanh->lichHoc)->khoaHoc)->ten_khoa_hoc ?: 'Chua xac dinh khoa hoc' }}
                                </div>
                                <span class="badge bg-{{ $trangThaiColor }}">{{ $trangThaiLabel }}</span>
                            </div>
                            <div class="small text-muted">
                                {{ optional(optional($diemDanh->lichHoc)->moduleHoc)->ten_module ?: 'Chua gan module' }}
                            </div>
                            <div class="small text-muted">
                                {{ optional(optional($diemDanh->lichHoc)->ngay_hoc)->format('d/m/Y') ?: 'Chua co ngay hoc' }}
                                @if(optional($diemDanh->updated_at))
                                    • {{ $diemDanh->updated_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <div class="empty-icon mb-3"><i class="fas fa-user-check"></i></div>
                            <p class="text-muted mb-0">Chua co du lieu diem danh de thong ke.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card vip-card h-100">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Buoi hoc sap toi</h5>
                        <p class="text-muted small mb-0">Cac buoi hoc thuoc khoa hoc dang hoc.</p>
                    </div>
                    <span class="badge bg-light text-primary">{{ $dashboardStats['buoi_hoc_sap_toi'] }}</span>
                </div>
                <div class="card-body">
                    @forelse($buoiSapToi as $lichHoc)
                        <div class="list-block">
                            <div class="d-flex justify-content-between gap-2">
                                <div class="fw-semibold text-dark">{{ $lichHoc->khoaHoc->ten_khoa_hoc }}</div>
                                <span class="badge bg-{{ $lichHoc->hinh_thuc_color }}">{{ $lichHoc->hinh_thuc_label }}</span>
                            </div>
                            <div class="small text-muted">{{ $lichHoc->moduleHoc->ten_module ?? 'Chua gan module' }}</div>
                            <div class="small text-muted">
                                {{ $lichHoc->ngay_hoc->format('d/m/Y') }}
                                • {{ $lichHoc->gio_bat_dau ?: '--:--' }}
                                @if($lichHoc->gio_ket_thuc)
                                    - {{ $lichHoc->gio_ket_thuc }}
                                @endif
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
        </div>

        <div class="col-lg-6">
            <div class="card vip-card h-100">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-semibold">Tai lieu moi duoc cong khai</h5>
                        <p class="text-muted small mb-0">Chi hien thi tai lieu giang vien da mo cho hoc vien.</p>
                    </div>
                    <span class="badge bg-light text-success">{{ $dashboardStats['tai_lieu_moi_7_ngay'] }} moi</span>
                </div>
                <div class="card-body">
                    @forelse($taiLieuMoi as $taiLieu)
                        <div class="list-block">
                            <div class="d-flex justify-content-between gap-2 align-items-start">
                                <div class="fw-semibold text-dark">{{ $taiLieu->tieu_de }}</div>
                                <span class="badge bg-{{ $taiLieu->loai_color }}">{{ $taiLieu->loai_label }}</span>
                            </div>
                            <div class="small text-muted">
                                {{ optional(optional($taiLieu->lichHoc)->khoaHoc)->ten_khoa_hoc ?: 'Chua xac dinh khoa hoc' }}
                            </div>
                            <div class="small text-muted">
                                {{ optional(optional($taiLieu->lichHoc)->moduleHoc)->ten_module ?: 'Chua gan module' }}
                                • {{ optional($taiLieu->created_at)->diffForHumans() }}
                            </div>
                            <p class="small text-muted mb-0 mt-2">
                                {{ \Illuminate\Support\Str::limit($taiLieu->mo_ta ?: $taiLieu->file_status_message, 100) }}
                            </p>
                            <div class="d-flex flex-wrap gap-2 mt-3">
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
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <div class="empty-icon mb-3"><i class="fas fa-folder-open"></i></div>
                            <p class="text-muted mb-0">Chua co tai lieu moi duoc cong khai.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card vip-card">
        <div class="card-header border-0">
            <h5 class="mb-1 fw-semibold">Tien do theo khoa hoc</h5>
            <p class="text-muted small mb-0">Tien do toi thieu duoc tinh theo so buoi da hoan thanh tren tong so buoi da len lich.</p>
        </div>
        <div class="card-body">
            @if($tienDoKhoaHoc->isNotEmpty())
                <div class="row g-4">
                    @foreach($tienDoKhoaHoc as $dong)
                        @php
                            $khoaHoc = $dong['khoa_hoc'];
                            $ghiDanh = $dong['ghi_danh'];
                        @endphp
                        <div class="col-xl-6">
                            <div class="course-progress-card h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <h5 class="fw-semibold mb-1">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                                        <div class="small text-muted">{{ $khoaHoc->ma_khoa_hoc ?: 'Chua co ma khoa hoc' }}</div>
                                        <div class="small text-muted">
                                            {{ optional($khoaHoc->nhomNganh)->ten_nhom_nganh ?: 'Chua cap nhat nhom nganh' }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $ghiDanh->trang_thai_badge }}">{{ $ghiDanh->trang_thai_label }}</span>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span>{{ $dong['buoi_hoan_thanh'] }}/{{ $dong['tong_buoi'] }} buoi da hoc</span>
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
                                </div>

                                <div class="row g-3 small">
                                    <div class="col-sm-6">
                                        <div class="info-chip">
                                            <span class="label">Buoi online</span>
                                            <strong>{{ $dong['buoi_online'] }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-chip">
                                            <span class="label">Ty le tham du</span>
                                            <strong>{{ $dong['ty_le_tham_du'] !== null ? $dong['ty_le_tham_du'] . '%' : 'Chua co' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="info-chip success-chip">
                                            <span class="label">Co mat</span>
                                            <strong>{{ $dong['co_mat'] }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="info-chip warning-chip">
                                            <span class="label">Vao tre</span>
                                            <strong>{{ $dong['vao_tre'] }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="info-chip danger-chip">
                                            <span class="label">Vang mat</span>
                                            <strong>{{ $dong['vang_mat'] }}</strong>
                                        </div>
                                    </div>
                                    @if($dong['ket_qua_hoc_tap'])
                                        <div class="col-sm-4">
                                            <div class="info-chip">
                                                <span class="label">Diem chuyen can</span>
                                                <strong>{{ $dong['ket_qua_hoc_tap']->diem_diem_danh !== null ? number_format((float) $dong['ket_qua_hoc_tap']->diem_diem_danh, 2) : 'Chua co' }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="info-chip">
                                                <span class="label">Diem kiem tra</span>
                                                <strong>{{ $dong['ket_qua_hoc_tap']->diem_kiem_tra !== null ? number_format((float) $dong['ket_qua_hoc_tap']->diem_kiem_tra, 2) : 'Chua co' }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="info-chip success-chip">
                                                <span class="label">Tong ket</span>
                                                <strong>{{ $dong['ket_qua_hoc_tap']->diem_tong_ket !== null ? number_format((float) $dong['ket_qua_hoc_tap']->diem_tong_ket, 2) : 'Dang tinh' }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($dong['buoi_sap_toi'])
                                    <div class="next-session mt-3">
                                        <div class="small text-muted">Buoi hoc ke tiep</div>
                                        <div class="fw-semibold text-dark">
                                            {{ $dong['buoi_sap_toi']->ngay_hoc->format('d/m/Y') }}
                                            • {{ $dong['buoi_sap_toi']->gio_bat_dau ?: '--:--' }}
                                        </div>
                                        <div class="small text-muted">{{ $dong['buoi_sap_toi']->hinh_thuc_label }}</div>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $khoaHoc->id) }}" class="btn btn-sm btn-outline-primary">
                                        Xem chi tiet khoa hoc
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-icon mb-3"><i class="fas fa-book-reader"></i></div>
                    <h6 class="fw-semibold">Chua co du lieu tien do</h6>
                    <p class="text-muted mb-0">Khi hoc vien duoc ghi danh va khoa hoc co lich hoc, tien do se xuat hien tai day.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .summary-card .card-body {
        padding: 1.5rem;
    }

    .summary-icon {
        font-size: 1.45rem;
        margin-bottom: 1rem;
    }

    .summary-label {
        color: #64748b;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.4rem;
    }

    .summary-value {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.05;
        margin-bottom: 0.35rem;
    }

    .summary-note {
        color: #64748b;
        font-size: 0.92rem;
    }

    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #eef2f7;
    }

    .activity-item:first-child {
        padding-top: 0;
    }

    .activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .activity-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 14px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .attendance-box {
        border-radius: 16px;
        padding: 1rem;
        text-align: center;
    }

    .attendance-success {
        background: #ecfdf5;
        color: #047857;
    }

    .attendance-warning {
        background: #fff7ed;
        color: #c2410c;
    }

    .attendance-danger {
        background: #fef2f2;
        color: #b91c1c;
    }

    .attendance-value {
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 0.25rem;
    }

    .attendance-label {
        font-size: 0.85rem;
    }

    .attendance-log,
    .list-block {
        padding: 1rem 0;
        border-bottom: 1px solid #eef2f7;
    }

    .attendance-log:first-of-type,
    .list-block:first-of-type {
        padding-top: 0;
    }

    .attendance-log:last-child,
    .list-block:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .course-progress-card {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1.25rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .progress-thin {
        height: 8px;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .info-chip {
        height: 100%;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.85rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .info-chip .label {
        color: #64748b;
    }

    .success-chip {
        background: #ecfdf5;
        border-color: #a7f3d0;
    }

    .warning-chip {
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .danger-chip {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .next-session {
        border-top: 1px dashed #cbd5e1;
        padding-top: 1rem;
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
