@extends('layouts.app')

@section('title', 'Dashboard Giang vien')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2 fw-bold text-white">Chao mung, {{ auth()->user()->ho_ten }}!</h2>
                            <p class="text-white-50 mb-0">Ban co <span class="fw-bold text-white">{{ $stats['cho_xac_nhan'] }}</span> phan cong moi dang cho phan hoi.</p>
                        </div>
                        <div class="col-md-4 text-md-end d-none d-md-block">
                            <i class="fas fa-chalkboard-teacher fa-5x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase fw-bold">Dang giang day</div><div class="h4 mb-0 fw-bold">{{ $stats['dang_day'] }}</div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase fw-bold">Cho xac nhan</div><div class="h4 mb-0 fw-bold">{{ $stats['cho_xac_nhan'] }}</div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase fw-bold">Buoi sap toi</div><div class="h4 mb-0 fw-bold">{{ $stats['buoi_sap_toi'] }}</div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small text-uppercase fw-bold">Don cho duyet</div><div class="h4 mb-0 fw-bold">{{ $stats['don_xin_nghi_cho_duyet'] }}</div></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-paper-plane me-2 text-warning"></i>Phan cong moi</h5>
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-sm btn-link text-decoration-none">Xem tat ca</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4">Khoa hoc / Module</th>
                                    <th class="text-center">So buoi</th>
                                    <th class="text-center">Hanh dong</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($phanCongMoi as $pc)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $pc->moduleHoc->ten_module }}</div>
                                            <div class="smaller text-muted">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</div>
                                        </td>
                                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $pc->moduleHoc->so_buoi ?? 0 }} buoi</span></td>
                                        <td class="text-center pe-3">
                                            <div class="d-flex justify-content-center gap-1">
                                                <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $pc->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-success px-3 fw-bold">Xac nhan</button></form>
                                                <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-sm btn-outline-primary">Chi tiet</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted">Ban khong co phan cong moi nao.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3"><h5 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-alt me-2 text-primary"></i>Lich day va dieu chinh</h5></div>
                <div class="card-body p-4 text-center">
                    <div class="text-muted small mb-3">Admin sap lich theo khung chuan. Ban theo doi lich day va gui don xin nghi khi phat sinh ban viec.</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('giang-vien.lich-giang.index') }}" class="btn btn-primary w-100 fw-bold"><i class="fas fa-calendar-check me-2"></i>Xem lich day cua toi</a>
                        <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="btn btn-outline-warning w-100 fw-bold"><i class="fas fa-paper-plane me-2"></i>Theo doi don xin nghi</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3"><h5 class="mb-0 fw-bold text-dark"><i class="fas fa-rocket me-2 text-info"></i>Thao tac nhanh</h5></div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-6"><a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center border-2"><i class="fas fa-chalkboard-teacher fa-lg mb-2"></i><span class="small fw-bold">Khoa hoc</span></a></div>
                        <div class="col-6"><a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-outline-warning w-100 py-3 d-flex flex-column align-items-center border-2"><i class="fas fa-user-check fa-lg mb-2"></i><span class="small fw-bold">Cham diem</span></a></div>
                        <div class="col-12"><a href="{{ route('giang-vien.don-xin-nghi.create') }}" class="btn btn-outline-success w-100 py-3 d-flex align-items-center justify-content-center border-2 gap-2"><i class="fas fa-business-time fa-lg"></i><span class="small fw-bold">Gui don xin nghi</span></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .smaller { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
