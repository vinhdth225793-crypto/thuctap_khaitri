@extends('layouts.app', ['title' => 'Quan ly ket qua hoc tap'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Quan ly diem toan khoa</h2>
            <p class="text-muted mb-0">Chon khoa hoc de xem diem tong ket, module, bai kiem tra va tung lan lam bai.</p>
        </div>
        <a href="{{ route('admin.xet-duyet-ket-qua.index') }}" class="btn btn-primary">
            <i class="fas fa-file-signature me-1"></i> Phieu xet duyet
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Khoa hoc</th>
                            <th>Trang thai</th>
                            <th class="text-center">Hoc vien</th>
                            <th class="text-end pe-4">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $course->ten_khoa_hoc }}</div>
                                    <div class="small text-muted">{{ $course->ma_khoa_hoc }} - {{ $course->phuong_thuc_danh_gia_label }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border">{{ $course->trang_thai_van_hanh }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold">{{ $course->hoc_vien_khoa_hocs_count }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.ket-qua.show', $course->id) }}" class="btn btn-sm btn-primary">
                                        Xem ket qua
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">Chua co khoa hoc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
