@extends('layouts.app', ['title' => 'Chi tiết đề thi'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Chi tiết đề thi</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->tieu_de }}</p>
        </div>
        <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-outline-primary">Quay lại danh sách</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card vip-card">
                <div class="card-header border-0"><h5 class="mb-0 fw-semibold">Thông tin & duyệt</h5></div>
                <div class="card-body">
                    <div class="small text-muted mb-2">Khóa học</div>
                    <div class="mb-3">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</div>
                    <div class="small text-muted mb-2">Module</div>
                    <div class="mb-3">{{ $baiKiemTra->moduleHoc->ten_module ?? 'Dùng chung' }}</div>
                    <div class="small text-muted mb-2">Chế độ thi</div>
                    <div class="mb-3"><span class="badge {{ $baiKiemTra->co_giam_sat ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-light text-dark border' }}">{{ $baiKiemTra->co_giam_sat ? 'Giám sát nâng cao' : 'Bài thường' }}</span></div>
                    <div class="small text-muted mb-2">Loại nội dung</div>
                    <div class="mb-3">{{ $baiKiemTra->content_mode_label }}</div>
                    @if($baiKiemTra->co_giam_sat)
                        <div class="small text-muted mb-2">Quy tắc giám sát</div>
                        <div class="mb-3 small">
                            <div>{{ $baiKiemTra->bat_buoc_fullscreen ? 'Yêu cầu fullscreen' : 'Không bắt buộc fullscreen' }}</div>
                            <div>{{ $baiKiemTra->bat_buoc_camera ? 'Yêu cầu camera' : 'Không bắt buộc camera' }}</div>
                            <div>Ngưỡng vi phạm: {{ $baiKiemTra->so_lan_vi_pham_toi_da }}</div>
                            <div>Snapshot: {{ $baiKiemTra->chu_ky_snapshot_giay }} giây</div>
                        </div>
                    @endif

                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.approve', $baiKiemTra->id) }}" method="POST" class="mb-3">
                        @csrf
                        <textarea name="ghi_chu_duyet" rows="3" class="form-control mb-2" placeholder="Ghi chú duyệt">{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                        <button type="submit" class="btn btn-success w-100">Duyệt đề thi</button>
                    </form>

                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.reject', $baiKiemTra->id) }}" method="POST" class="mb-3">
                        @csrf
                        <textarea name="ghi_chu_duyet" rows="3" class="form-control mb-2" placeholder="Lý do từ chối" required>{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                        <button type="submit" class="btn btn-outline-danger w-100">Từ chối đề thi</button>
                    </form>

                    @if($baiKiemTra->trang_thai_duyet === 'da_duyet' && $baiKiemTra->trang_thai_phat_hanh !== 'phat_hanh')
                        <form action="{{ route('admin.kiem-tra-online.phe-duyet.publish', $baiKiemTra->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">Phát hành cho học viên</button>
                        </form>
                    @endif
                    @if($baiKiemTra->trang_thai_phat_hanh === 'phat_hanh')
                        <form action="{{ route('admin.kiem-tra-online.phe-duyet.close', $baiKiemTra->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-dark w-100">Đóng đề thi</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card mb-4">
                @php
                    $approvalQuestionCount = $baiKiemTra->is_free_essay && filled($baiKiemTra->mo_ta)
                        ? 1
                        : $baiKiemTra->chiTietCauHois->count();
                @endphp
                <div class="card-header border-0"><h5 class="mb-0 fw-semibold">Danh sách câu hỏi ({{ $approvalQuestionCount }})</h5></div>
                <div class="card-body">
                    @if($baiKiemTra->is_free_essay && filled($baiKiemTra->mo_ta))
                        <div class="border rounded-3 p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <strong>Câu 1. Đề bài tự luận</strong>
                                    <div class="text-muted small mb-3">Học viên nộp một bài viết tổng. Giảng viên chấm tay sau khi học viên nộp.</div>
                                    <div class="text-dark" style="white-space: pre-wrap; line-height: 1.7;">{!! nl2br(e($baiKiemTra->mo_ta)) !!}</div>
                                </div>
                                <span class="badge bg-light text-dark border">{{ number_format((float) $baiKiemTra->tong_diem, 2) }} điểm</span>
                            </div>
                        </div>
                    @else
                        @forelse($baiKiemTra->chiTietCauHois as $index => $chiTiet)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between gap-3">
                                    <div><strong>Câu {{ $index + 1 }}.</strong> {!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Không rõ nội dung')) !!}</div>
                                    <span class="badge bg-light text-dark">{{ number_format((float) $chiTiet->diem_so, 2) }} điểm</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">Đề thi hiện chưa có câu hỏi.</div>
                        @endforelse
                    @endif
                </div>
            </div>

            <div class="card vip-card">
                <div class="card-header border-0"><h5 class="mb-0 fw-semibold">Bài làm & hậu kiểm</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Học viên</th>
                                    <th>Lần làm</th>
                                    <th>Giám sát</th>
                                    <th>Vi phạm</th>
                                    <th class="text-end pe-3">Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($baiKiemTra->baiLams as $baiLam)
                                    <tr>
                                        <td class="ps-3">{{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</td>
                                        <td>#{{ $baiLam->lan_lam_thu }}</td>
                                        <td><span class="badge bg-{{ $baiLam->trang_thai_giam_sat_color }}">{{ $baiLam->trang_thai_giam_sat_label }}</span></td>
                                        <td>{{ (int) $baiLam->tong_so_vi_pham }}</td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('admin.kiem-tra-online.phe-duyet.attempt.show', $baiLam->id) }}" class="btn btn-sm btn-outline-primary">Xem hậu kiểm</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Chưa có bài làm nào.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
