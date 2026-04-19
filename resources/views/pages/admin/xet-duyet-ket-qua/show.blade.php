@extends('layouts.app', ['title' => 'Chi tiet phieu xet duyet'])

@section('content')
@php
    $canStartReview = $phieu->trang_thai === \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED;
    $canApprove = in_array($phieu->trang_thai, [
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
    ], true);
    $canFinalize = in_array($phieu->trang_thai, [
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_APPROVED,
    ], true);
    $firstDetail = $phieu->chiTiets->first();
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('admin.xet-duyet-ket-qua.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left me-1"></i> Quay lai danh sach
            </a>
            <h2 class="fw-bold mb-1">Phieu #{{ $phieu->id }}</h2>
            <p class="text-muted mb-0">{{ $phieu->khoaHoc?->ten_khoa_hoc }} - {{ $phieu->phuong_an_label }}</p>
        </div>
        <div class="text-end">
            <span class="badge bg-{{ $phieu->trang_thai_color }} px-3 py-2">{{ $phieu->trang_thai_label }}</span>
            <div class="small text-muted mt-2">Giang vien: {{ $phieu->nguoiLap?->ho_ten }}</div>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="bg-white border rounded-3 p-3 h-100">
                <h5 class="fw-bold mb-3">Thong tin xet duyet</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="small text-muted text-uppercase fw-bold">Ty trong kiem tra</div>
                        <div class="fs-5 fw-bold">{{ number_format((float) $phieu->ty_trong_kiem_tra, 0) }}%</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted text-uppercase fw-bold">Ty trong diem danh</div>
                        <div class="fs-5 fw-bold">{{ number_format((float) $phieu->ty_trong_diem_danh, 0) }}%</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted text-uppercase fw-bold">Diem dat</div>
                        <div class="fs-5 fw-bold">{{ number_format((float) $phieu->diem_dat, 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted text-uppercase fw-bold">Hoc vien</div>
                        <div class="fs-5 fw-bold">{{ $phieu->chiTiets->count() }}</div>
                    </div>
                </div>
                @if($phieu->ghi_chu)
                    <div class="alert alert-light border mt-3 mb-0">
                        <strong>Ghi chu giang vien:</strong> {{ $phieu->ghi_chu }}
                    </div>
                @endif
                @if($firstDetail)
                    <div class="mt-3">
                        <div class="small text-muted text-uppercase fw-bold mb-2">Bai kiem tra duoc chon</div>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach(($firstDetail->chi_tiet_bai_kiem_tra ?? []) as $examRow)
                                <span class="badge text-bg-light border px-3 py-2">{{ $examRow['tieu_de'] ?? 'Bai kiem tra' }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bg-white border rounded-3 p-3 h-100">
                <h5 class="fw-bold mb-3">Xu ly phieu</h5>
                <div class="d-grid gap-2">
                    @if($canStartReview)
                        <form method="POST" action="{{ route('admin.xet-duyet-ket-qua.reviewing', $phieu) }}">
                            @csrf
                            <button class="btn btn-info text-white w-100" type="submit">Chuyen sang dang xem</button>
                        </form>
                    @endif
                    @if($canApprove)
                        <form method="POST" action="{{ route('admin.xet-duyet-ket-qua.approve', $phieu) }}">
                            @csrf
                            <button class="btn btn-primary w-100" type="submit">Duyet phieu</button>
                        </form>
                    @endif
                    @if($canFinalize)
                        <form method="POST" action="{{ route('admin.xet-duyet-ket-qua.finalize', $phieu) }}" onsubmit="return confirm('Chot chinh thuc va ghi vao ho so hoc tap?')">
                            @csrf
                            <textarea name="ghi_chu_duyet" rows="2" class="form-control mb-2" placeholder="Ghi chu chot ho so..."></textarea>
                            <button class="btn btn-success w-100" type="submit">Chot ket qua chinh thuc</button>
                        </form>
                    @endif
                    @if($canApprove)
                        <form method="POST" action="{{ route('admin.xet-duyet-ket-qua.reject', $phieu) }}">
                            @csrf
                            <textarea name="reject_reason" rows="2" class="form-control mb-2" placeholder="Ly do tu choi..." required></textarea>
                            <button class="btn btn-outline-danger w-100" type="submit">Tu choi va tra ve</button>
                        </form>
                    @endif
                    @if(! $canApprove && ! $canFinalize && ! $canStartReview)
                        <div class="text-muted small">Phieu hien tai khong con thao tac xu ly.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Hoc vien</th>
                        <th class="text-center">Diem danh</th>
                        <th class="text-center">Kiem tra</th>
                        <th class="text-center">Xet duyet</th>
                        <th class="text-center">Ket qua</th>
                        <th>Chi tiet bai lam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($phieu->chiTiets as $detail)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $detail->hocVien?->ho_ten ?? 'Khong ro hoc vien' }}</div>
                                <div class="small text-muted">{{ $detail->hocVien?->email }}</div>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold">{{ $detail->diem_chuyen_can !== null ? number_format((float) $detail->diem_chuyen_can, 2) : '--' }}</div>
                                <div class="small text-muted">{{ $detail->so_buoi_tham_du }}/{{ $detail->tong_so_buoi }} buoi</div>
                            </td>
                            <td class="text-center fw-bold">{{ $detail->diem_kiem_tra !== null ? number_format((float) $detail->diem_kiem_tra, 2) : '--' }}</td>
                            <td class="text-center">
                                <div class="fw-bold text-primary fs-5">{{ $detail->diem_xet_duyet !== null ? number_format((float) $detail->diem_xet_duyet, 2) : '--' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $detail->ket_qua_color }} rounded-pill px-3">{{ $detail->ket_qua_label }}</span>
                            </td>
                            <td style="min-width: 300px;">
                                @foreach(($detail->chi_tiet_bai_kiem_tra ?? []) as $examRow)
                                    <div class="d-flex justify-content-between gap-3 border-bottom py-1 small">
                                        <span>{{ $examRow['tieu_de'] ?? 'Bai kiem tra' }}</span>
                                        <strong>{{ isset($examRow['diem']) && $examRow['diem'] !== null ? number_format((float) $examRow['diem'], 2) : '--' }}</strong>
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Phieu chua co chi tiet hoc vien.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
