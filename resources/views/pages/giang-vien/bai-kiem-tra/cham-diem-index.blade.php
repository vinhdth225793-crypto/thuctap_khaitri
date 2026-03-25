@extends('layouts.app', ['title' => 'Chấm điểm tự luận'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Chấm điểm tự luận</h2>
            <p class="text-muted mb-0">Danh sách bài làm đang chờ giảng viên chấm tay.</p>
        </div>
    </div>

    <div class="card vip-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Học viên</th>
                        <th>Bài kiểm tra</th>
                        <th>Khóa / module</th>
                        <th>Nộp lúc</th>
                        <th>Lần làm</th>
                        <th class="text-end">Chấm bài</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baiLams as $baiLam)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</div>
                                <div class="small text-muted">{{ $baiLam->hocVien->email ?? 'Không có email' }}</div>
                            </td>
                            <td>{{ $baiLam->baiKiemTra->tieu_de }}</td>
                            <td>
                                <div class="fw-semibold">{{ $baiLam->baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }}</div>
                                <div class="small text-muted">{{ $baiLam->baiKiemTra->moduleHoc->ten_module ?? 'Không gán module' }}</div>
                            </td>
                            <td>{{ $baiLam->nop_luc?->format('d/m/Y H:i') ?? 'Chưa nộp' }}</td>
                            <td>{{ $baiLam->lan_lam_thu }}</td>
                            <td class="text-end">
                                <a href="{{ route('giang-vien.cham-diem.show', $baiLam->id) }}" class="btn btn-sm btn-primary">Mở bài chấm</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Không có bài làm nào đang chờ chấm.</td>
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
