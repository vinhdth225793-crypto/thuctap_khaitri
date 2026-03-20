@extends('layouts.app', ['title' => 'Cham diem tu luan'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Cham diem tu luan</h2>
            <p class="text-muted mb-0">Danh sach bai lam dang cho giang vien cham tay.</p>
        </div>
    </div>

    <div class="card vip-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Hoc vien</th>
                        <th>Bai kiem tra</th>
                        <th>Khoa / module</th>
                        <th>Nop luc</th>
                        <th>Lan lam</th>
                        <th class="text-end">Cham bai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baiLams as $baiLam)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $baiLam->hocVien->ho_ten ?? 'Hoc vien' }}</div>
                                <div class="small text-muted">{{ $baiLam->hocVien->email ?? 'Khong co email' }}</div>
                            </td>
                            <td>{{ $baiLam->baiKiemTra->tieu_de }}</td>
                            <td>
                                <div class="fw-semibold">{{ $baiLam->baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Khong ro khoa hoc' }}</div>
                                <div class="small text-muted">{{ $baiLam->baiKiemTra->moduleHoc->ten_module ?? 'Khong gan module' }}</div>
                            </td>
                            <td>{{ $baiLam->nop_luc?->format('d/m/Y H:i') ?? 'Chua nop' }}</td>
                            <td>{{ $baiLam->lan_lam_thu }}</td>
                            <td class="text-end">
                                <a href="{{ route('giang-vien.cham-diem.show', $baiLam->id) }}" class="btn btn-sm btn-primary">Mo bai cham</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Khong co bai lam nao dang cho cham.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($baiLams->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $baiLams->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
