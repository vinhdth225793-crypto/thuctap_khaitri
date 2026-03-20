@extends('layouts.app', ['title' => 'Lam bai kiem tra'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.bai-kiem-tra') }}">Bai kiem tra</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $baiKiemTra->tieu_de }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">{{ $baiKiemTra->tieu_de }}</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Chua xac dinh khoa hoc' }}</p>
        </div>

        <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Danh sach bai kiem tra
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card vip-card h-100">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Thong tin bai kiem tra</h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Trang thai mo bai</span>
                        <span class="badge bg-{{ $baiKiemTra->access_status_color }}">{{ $baiKiemTra->access_status_label }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Pham vi</span>
                        <strong>{{ $baiKiemTra->pham_vi_label }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Thoi gian lam bai</span>
                        <strong>{{ $baiKiemTra->thoi_gian_lam_bai }} phut</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Module</span>
                        <strong>{{ $baiKiemTra->moduleHoc->ten_module ?? 'Chua gan module' }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Ngay mo</span>
                        <strong>{{ $baiKiemTra->ngay_mo ? $baiKiemTra->ngay_mo->format('d/m/Y H:i') : 'Mo ngay' }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Ngay dong</span>
                        <strong>{{ $baiKiemTra->ngay_dong ? $baiKiemTra->ngay_dong->format('d/m/Y H:i') : 'Chua dat lich dong' }}</strong>
                    </div>

                    @if($baiLam)
                        <hr>
                        <div class="info-row">
                            <span class="label">Trang thai bai lam</span>
                            <span class="badge bg-{{ $baiLam->trang_thai_color }}">{{ $baiLam->trang_thai_label }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Bat dau luc</span>
                            <strong>{{ $baiLam->bat_dau_luc ? $baiLam->bat_dau_luc->format('d/m/Y H:i') : 'Chua bat dau' }}</strong>
                        </div>
                        <div class="info-row">
                            <span class="label">Nop luc</span>
                            <strong>{{ $baiLam->nop_luc ? $baiLam->nop_luc->format('d/m/Y H:i') : 'Chua nop' }}</strong>
                        </div>
                    @endif

                    <div class="alert alert-info mt-4 mb-0">
                        <strong>Luu y:</strong> Phien ban hien tai chua co ngan hang cau hoi rieng.
                        Hoc vien lam bai bang cach doc de bai/mo ta va nop noi dung bai lam dang tu luan.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Noi dung bai kiem tra</h5>
                </div>
                <div class="card-body">
                    @if($baiKiemTra->mo_ta)
                        <div class="description-box mb-4">
                            {!! nl2br(e($baiKiemTra->mo_ta)) !!}
                        </div>
                    @else
                        <div class="alert alert-warning">
                            Bai kiem tra nay chua co mo ta chi tiet. Ban co the lien he giang vien neu can them huong dan.
                        </div>
                    @endif

                    @if($baiLam && $baiLam->is_submitted)
                        <div class="alert alert-success">
                            Ban da nop bai vao luc {{ $baiLam->nop_luc?->format('d/m/Y H:i') }}.
                            Duoi day la noi dung bai lam da nop.
                        </div>

                        <div class="submitted-box">
                            {!! nl2br(e($baiLam->noi_dung_bai_lam ?: 'Khong co noi dung bai lam.')) !!}
                        </div>
                    @elseif(!$baiKiemTra->can_student_start && !$baiLam)
                        <div class="alert alert-secondary mb-0">
                            Bai kiem tra nay hien {{ strtolower($baiKiemTra->access_status_label) }}.
                            Ban se co the bat dau lam bai khi bai kiem tra du dieu kien mo.
                        </div>
                    @else
                        @if(!$baiLam)
                            <div class="alert alert-primary d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    Ban chua bat dau bai kiem tra nay. Khi bat dau, he thong se tao ban ghi bai lam cho ban.
                                </div>
                                <form action="{{ route('hoc-vien.bai-kiem-tra.bat-dau', $baiKiemTra->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Bat dau lam bai</button>
                                </form>
                            </div>
                        @endif

                        @if($baiLam)
                            <form action="{{ route('hoc-vien.bai-kiem-tra.nop', $baiKiemTra->id) }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                <div class="mb-3">
                                    <label for="noi_dung_bai_lam" class="form-label fw-semibold">Noi dung bai lam</label>
                                    <textarea
                                        id="noi_dung_bai_lam"
                                        name="noi_dung_bai_lam"
                                        rows="16"
                                        class="form-control vip-form-control @error('noi_dung_bai_lam') is-invalid @enderror"
                                        placeholder="Nhap noi dung bai lam cua ban tai day..."
                                        required>{{ old('noi_dung_bai_lam', $baiLam->noi_dung_bai_lam) }}</textarea>
                                    @error('noi_dung_bai_lam')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div class="text-muted small">
                                        Ban nen kiem tra lai ky noi dung truoc khi nop bai. Sau khi nop, bai lam se chuyen sang trang thai da nop.
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-2"></i>Nop bai
                                    </button>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 0;
        border-bottom: 1px solid #eef2f7;
    }

    .info-row:first-child {
        padding-top: 0;
    }

    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-row .label {
        color: #64748b;
        font-size: 0.92rem;
    }

    .description-box,
    .submitted-box {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 1.25rem;
        line-height: 1.8;
        white-space: normal;
    }

    .submitted-box {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }
</style>
@endpush
@endsection
