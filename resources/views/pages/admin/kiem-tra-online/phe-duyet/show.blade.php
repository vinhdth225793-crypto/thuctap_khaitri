@extends('layouts.app', ['title' => 'Chi tiết bài kiểm tra'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $baiKiemTra->tieu_de }}</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }} • {{ $baiKiemTra->moduleHoc->ten_module ?? 'Không gán module' }}</p>
        </div>
        <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-outline-primary">Quay lại</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card vip-card h-100">
                <div class="card-body">
                    <div class="mb-3"><strong>Trạng thái duyệt:</strong> {{ $baiKiemTra->trang_thai_duyet_label }}</div>
                    <div class="mb-3"><strong>Trạng thái phát hành:</strong> {{ $baiKiemTra->trang_thai_phat_hanh_label }}</div>
                    <div class="mb-3"><strong>Loại nội dung:</strong> {{ $baiKiemTra->loai_noi_dung_label }}</div>
                    <div class="mb-3"><strong>Tổng điểm:</strong> {{ number_format((float) $baiKiemTra->tong_diem, 2) }}</div>
                    <div class="mb-3"><strong>Số lần được làm:</strong> {{ $baiKiemTra->so_lan_duoc_lam }}</div>
                    <div class="mb-4"><strong>GV tạo:</strong> {{ $baiKiemTra->nguoiTao->ho_ten ?? 'Không rõ' }}</div>

                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.approve', $baiKiemTra->id) }}" method="POST" class="mb-3">
                        @csrf
                        <textarea name="ghi_chu_duyet" rows="3" class="form-control mb-2" placeholder="Ghi chú duyệt (nếu cần)">{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                        <button type="submit" class="btn btn-success w-100">Duyệt đề</button>
                    </form>

                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.reject', $baiKiemTra->id) }}" method="POST" class="mb-3">
                        @csrf
                        <textarea name="ghi_chu_duyet" rows="3" class="form-control mb-2" placeholder="Lý do từ chối" required>{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                        <button type="submit" class="btn btn-outline-danger w-100">Từ chối đề</button>
                    </form>

                    <div class="d-grid gap-2">
                        <form action="{{ route('admin.kiem-tra-online.phe-duyet.publish', $baiKiemTra->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">Phát hành cho học viên</button>
                        </form>
                        <form action="{{ route('admin.kiem-tra-online.phe-duyet.close', $baiKiemTra->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light border w-100">Đóng bài kiểm tra</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card mb-4">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Danh sách câu hỏi</h5>
                </div>
                <div class="card-body">
                    @forelse($baiKiemTra->chiTietCauHois as $index => $chiTiet)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">Câu {{ $index + 1 }}. {!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Không rõ nội dung')) !!}</div>
                                    <div class="small text-muted mt-1">{{ $chiTiet->cauHoi->loai_cau_hoi_label ?? 'Không rõ loại' }} • {{ number_format((float) $chiTiet->diem_so, 2) }} điểm</div>
                                </div>
                            </div>
                            @if(optional($chiTiet->cauHoi)->loai_cau_hoi === 'trac_nghiem')
                                <ul class="mt-3 mb-0">
                                    @foreach($chiTiet->cauHoi->dapAns as $dapAn)
                                        <li>
                                            {{ $dapAn->ky_hieu }}. {{ $dapAn->noi_dung }}
                                            @if($dapAn->is_dap_an_dung)
                                                <strong class="text-success">(Đúng)</strong>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">Đề này chưa có câu hỏi.</div>
                    @endforelse
                </div>
            </div>

            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Lịch sử bài làm</h5>
                </div>
                <div class="card-body">
                    @forelse($baiKiemTra->baiLams as $baiLam)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-semibold">{{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</div>
                            <div class="small text-muted">Lần {{ $baiLam->lan_lam_thu }} • {{ $baiLam->trang_thai_label }} • {{ $baiLam->nop_luc?->format('d/m/Y H:i') ?? 'Chưa nộp' }}</div>
                            <div class="small text-muted">Điểm: {{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 2) : 'Chưa chấm' }}</div>
                        </div>
                    @empty
                        <div class="text-muted">Chưa có bài làm nào.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
