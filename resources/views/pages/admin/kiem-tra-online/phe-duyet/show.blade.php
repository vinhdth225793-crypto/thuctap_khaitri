@extends('layouts.app', ['title' => 'Chi tiet bai kiem tra'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $baiKiemTra->tieu_de }}</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Khong ro khoa hoc' }} • {{ $baiKiemTra->moduleHoc->ten_module ?? 'Khong gan module' }}</p>
        </div>
        <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-outline-primary">Quay lai</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card vip-card h-100">
                <div class="card-body">
                    <div class="mb-3"><strong>Trang thai duyet:</strong> {{ $baiKiemTra->trang_thai_duyet_label }}</div>
                    <div class="mb-3"><strong>Trang thai phat hanh:</strong> {{ $baiKiemTra->trang_thai_phat_hanh_label }}</div>
                    <div class="mb-3"><strong>Loai noi dung:</strong> {{ $baiKiemTra->loai_noi_dung_label }}</div>
                    <div class="mb-3"><strong>Tong diem:</strong> {{ number_format((float) $baiKiemTra->tong_diem, 2) }}</div>
                    <div class="mb-3"><strong>So lan duoc lam:</strong> {{ $baiKiemTra->so_lan_duoc_lam }}</div>
                    <div class="mb-4"><strong>GV tao:</strong> {{ $baiKiemTra->nguoiTao->ho_ten ?? 'Khong ro' }}</div>

                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.approve', $baiKiemTra->id) }}" method="POST" class="mb-3">
                        @csrf
                        <textarea name="ghi_chu_duyet" rows="3" class="form-control mb-2" placeholder="Ghi chu duyet (neu can)">{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                        <button type="submit" class="btn btn-success w-100">Duyet de</button>
                    </form>

                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.reject', $baiKiemTra->id) }}" method="POST" class="mb-3">
                        @csrf
                        <textarea name="ghi_chu_duyet" rows="3" class="form-control mb-2" placeholder="Ly do tu choi" required>{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                        <button type="submit" class="btn btn-outline-danger w-100">Tu choi de</button>
                    </form>

                    <div class="d-grid gap-2">
                        <form action="{{ route('admin.kiem-tra-online.phe-duyet.publish', $baiKiemTra->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">Phat hanh cho hoc vien</button>
                        </form>
                        <form action="{{ route('admin.kiem-tra-online.phe-duyet.close', $baiKiemTra->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light border w-100">Dong bai kiem tra</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card mb-4">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Danh sach cau hoi</h5>
                </div>
                <div class="card-body">
                    @forelse($baiKiemTra->chiTietCauHois as $index => $chiTiet)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">Cau {{ $index + 1 }}. {!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Khong ro noi dung')) !!}</div>
                                    <div class="small text-muted mt-1">{{ $chiTiet->cauHoi->loai_cau_hoi_label ?? 'Khong ro loai' }} • {{ number_format((float) $chiTiet->diem_so, 2) }} diem</div>
                                </div>
                            </div>
                            @if(optional($chiTiet->cauHoi)->loai_cau_hoi === 'trac_nghiem')
                                <ul class="mt-3 mb-0">
                                    @foreach($chiTiet->cauHoi->dapAns as $dapAn)
                                        <li>
                                            {{ $dapAn->ky_hieu }}. {{ $dapAn->noi_dung }}
                                            @if($dapAn->is_dap_an_dung)
                                                <strong class="text-success">(Dung)</strong>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">De nay chua co cau hoi.</div>
                    @endforelse
                </div>
            </div>

            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Lich su bai lam</h5>
                </div>
                <div class="card-body">
                    @forelse($baiKiemTra->baiLams as $baiLam)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-semibold">{{ $baiLam->hocVien->ho_ten ?? 'Hoc vien' }}</div>
                            <div class="small text-muted">Lan {{ $baiLam->lan_lam_thu }} • {{ $baiLam->trang_thai_label }} • {{ $baiLam->nop_luc?->format('d/m/Y H:i') ?? 'Chua nop' }}</div>
                            <div class="small text-muted">Diem: {{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 2) : 'Chua cham' }}</div>
                        </div>
                    @empty
                        <div class="text-muted">Chua co bai lam nao.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
