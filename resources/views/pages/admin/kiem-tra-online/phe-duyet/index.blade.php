@extends('layouts.app', ['title' => 'Phe duyet bai kiem tra'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Phe duyet bai kiem tra</h2>
            <p class="text-muted mb-0">Admin kiem soat chat luong de thi truoc khi phat hanh cho hoc vien.</p>
        </div>
    </div>

    <div class="card vip-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tim kiem</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tieu de, mo ta...">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Trang thai duyet</label>
                    <select name="trang_thai_duyet" class="form-select">
                        <option value="">Tat ca</option>
                        @foreach(['nhap' => 'Nhap', 'cho_duyet' => 'Cho duyet', 'da_duyet' => 'Da duyet', 'tu_choi' => 'Tu choi'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai_duyet') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Trang thai phat hanh</label>
                    <select name="trang_thai_phat_hanh" class="form-select">
                        <option value="">Tat ca</option>
                        @foreach(['nhap' => 'Nhap', 'phat_hanh' => 'Phat hanh', 'dong' => 'Dong'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai_phat_hanh') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Loc du lieu</button>
                    <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-light border">Dat lai</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card vip-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>De thi</th>
                        <th>Khoa / module</th>
                        <th>GV tao</th>
                        <th>Loai</th>
                        <th>Cau hoi</th>
                        <th>Duyet</th>
                        <th>Phat hanh</th>
                        <th class="text-end">Chi tiet</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baiKiemTras as $baiKiemTra)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $baiKiemTra->tieu_de }}</div>
                                <div class="small text-muted">{{ $baiKiemTra->thoi_gian_lam_bai }} phut</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Khong ro khoa hoc' }}</div>
                                <div class="small text-muted">{{ $baiKiemTra->moduleHoc->ten_module ?? 'De cuoi khoa / dung chung' }}</div>
                            </td>
                            <td>{{ $baiKiemTra->nguoiTao->ho_ten ?? 'Khong ro' }}</td>
                            <td>{{ $baiKiemTra->loai_noi_dung_label }}</td>
                            <td>{{ $baiKiemTra->chi_tiet_cau_hois_count }}</td>
                            <td>{{ $baiKiemTra->trang_thai_duyet_label }}</td>
                            <td>{{ $baiKiemTra->trang_thai_phat_hanh_label }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.kiem-tra-online.phe-duyet.show', $baiKiemTra->id) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Chua co bai kiem tra nao.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($baiKiemTras->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $baiKiemTras->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
