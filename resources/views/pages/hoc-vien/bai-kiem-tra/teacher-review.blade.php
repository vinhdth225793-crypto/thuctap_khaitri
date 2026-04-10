@extends('layouts.app', ['title' => 'Chấm bài tự luận'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Chấm bài của {{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</h2>
            <p class="text-muted mb-0">{{ $baiLam->baiKiemTra->tieu_de }} • Lần làm {{ $baiLam->lan_lam_thu }}</p>
        </div>
        <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-outline-primary">Quay lại danh sách</a>
    </div>

    @php
    $reviewFlowAlertClass = match ($baiLam->baiKiemTra->content_mode_key) {
        'tu_luan_tu_do' => 'alert-warning',
        'tu_luan_theo_cau' => 'alert-info',
        'hon_hop' => 'alert-primary',
        default => 'alert-secondary',
    };
    $reviewFlowMessage = match ($baiLam->baiKiemTra->content_mode_key) {
        'tu_luan_tu_do' => 'De nay duoc cham theo diem tong cua toan bai viet. Giảng viên khong can chia diem theo tung cau rieng le.',
        'tu_luan_theo_cau' => 'De nay gom cac cau tu luan theo tung muc. Hay cham diem va nhan xet tren tung cau tra loi.',
        'hon_hop' => 'De nay gom ca phan trac nghiem va tu luan. Phan trac nghiem da co diem tu dong, con phan tu luan can giang vien cham tay.',
        default => 'Ban dang xem man cham bai cho de trac nghiem/tong hop hien co.',
    };
@endphp

<div class="d-flex flex-wrap gap-2 mb-3">
    <span class="badge bg-light text-dark border">{{ $baiLam->baiKiemTra->content_mode_label }}</span>
    <span class="badge bg-light text-dark border">Trạng thái: {{ $baiLam->trang_thai_label }}</span>
</div>

<div class="alert {{ $reviewFlowAlertClass }} mb-4">
    <div class="fw-semibold mb-1">Flow cham bai {{ $baiLam->baiKiemTra->content_mode_label }}</div>
    <div class="small mb-0">{{ $reviewFlowMessage }}</div>
</div>

<div class="row g-4">
        <div class="col-lg-7">
            <div class="card vip-card">
                <div class="card-header border-0"><h5 class="mb-0 fw-semibold">Bài làm</h5></div>
                <div class="card-body">
                    <form action="{{ route('giang-vien.cham-diem.store', $baiLam->id) }}" method="POST">
                        @csrf

                        @if($baiLam->chiTietTraLois->isEmpty())
                            @if($baiLam->baiKiemTra->mo_ta)
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Đề bài / hướng dẫn cho học viên</label>
                                    <div class="border rounded-3 bg-light p-3">{!! nl2br(e($baiLam->baiKiemTra->mo_ta)) !!}</div>
                                </div>
                            @endif

                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between gap-3 mb-3">
                                    <div class="fw-semibold">Bài làm tổng của học viên</div>
                                    <span class="badge bg-light text-dark">{{ number_format((float) $baiLam->baiKiemTra->tong_diem, 2) }} điểm</span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Nội dung bài làm</label>
                                    <div class="border rounded-3 bg-light p-3">{!! nl2br(e($baiLam->noi_dung_bai_lam ?: 'Học viên chưa có nội dung bài làm.')) !!}</div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Điểm tự luận</label>
                                        <input
                                            type="number"
                                            step="0.25"
                                            min="0"
                                            max="{{ $baiLam->baiKiemTra->tong_diem }}"
                                            name="overall_grade[diem_tu_luan]"
                                            value="{{ old('overall_grade.diem_tu_luan', $baiLam->tong_diem_tu_luan ?? $baiLam->diem_so) }}"
                                            class="form-control"
                                        >
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label small fw-semibold">Nhận xét tổng</label>
                                        <textarea name="overall_grade[nhan_xet]" rows="4" class="form-control">{{ old('overall_grade.nhan_xet', $baiLam->nhan_xet) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @else
                            @foreach($baiLam->chiTietTraLois as $index => $chiTiet)
                                <div class="border rounded-3 p-3 mb-3">
                                    <div class="d-flex justify-content-between gap-3 mb-2">
                                        <div class="fw-semibold">Câu {{ $index + 1 }}. {!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Không rõ nội dung')) !!}</div>
                                        <span class="badge bg-light text-dark">{{ number_format((float) ($chiTiet->chiTietBaiKiemTra->diem_so ?? 0), 2) }} điểm</span>
                                    </div>
                                    @if($chiTiet->cauHoi->loai_cau_hoi === 'trac_nghiem')
                                        <div class="small text-muted mb-2">Trả lời của học viên: {{ $chiTiet->dapAn->ky_hieu ?? 'Chưa chọn' }} - {{ $chiTiet->dapAn->noi_dung ?? 'Không có' }}</div>
                                        <div class="small {{ $chiTiet->is_dung ? 'text-success' : 'text-danger' }}">
                                            {{ $chiTiet->is_dung ? 'Đã đúng' : 'Sai / chưa có đáp án' }} • {{ number_format((float) ($chiTiet->diem_tu_dong ?? 0), 2) }} điểm tự động
                                        </div>
                                    @else
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Câu trả lời của học viên</label>
                                            <div class="border rounded-3 bg-light p-3">{!! nl2br(e($chiTiet->cau_tra_loi_text ?: 'Học viên chưa trả lời.')) !!}</div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Điểm tự luận</label>
                                                <input type="number" step="0.25" min="0" max="{{ $chiTiet->chiTietBaiKiemTra->diem_so ?? 0 }}" name="grades[{{ $chiTiet->id }}][diem_tu_luan]" value="{{ old('grades.' . $chiTiet->id . '.diem_tu_luan', $chiTiet->diem_tu_luan) }}" class="form-control">
                                            </div>
                                            <div class="col-md-9">
                                                <label class="form-label small fw-semibold">Nhận xét</label>
                                                <textarea name="grades[{{ $chiTiet->id }}][nhan_xet]" rows="3" class="form-control">{{ old('grades.' . $chiTiet->id . '.nhan_xet', $chiTiet->nhan_xet) }}</textarea>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        <button type="submit" class="btn btn-primary">Lưu kết quả chấm</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card vip-card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Giám sát</h5>
                    <span class="badge bg-{{ $baiLam->trang_thai_giam_sat_color }}">{{ $baiLam->trang_thai_giam_sat_label }}</span>
                </div>
                <div class="card-body">
                    @if(!$baiLam->baiKiemTra->co_giam_sat)
                        <div class="alert alert-secondary mb-0">Bài làm này không áp dụng giám sát nâng cao.</div>
                    @else
                        <div class="row g-3 mb-4">
                            <div class="col-6"><div class="border rounded-3 p-3"><div class="small text-muted mb-1">Tổng vi phạm</div><strong>{{ (int) $baiLam->tong_so_vi_pham }}</strong></div></div>
                            <div class="col-6"><div class="border rounded-3 p-3"><div class="small text-muted mb-1">Tab switch</div><strong>{{ $surveillanceSummary['tab_switch'] ?? 0 }}</strong></div></div>
                            <div class="col-6"><div class="border rounded-3 p-3"><div class="small text-muted mb-1">Fullscreen exit</div><strong>{{ $surveillanceSummary['fullscreen_exit'] ?? 0 }}</strong></div></div>
                            <div class="col-6"><div class="border rounded-3 p-3"><div class="small text-muted mb-1">Camera off</div><strong>{{ $surveillanceSummary['camera_off'] ?? 0 }}</strong></div></div>
                        </div>

                        <form action="{{ route('giang-vien.cham-diem.surveillance', $baiLam->id) }}" method="POST" class="border rounded-3 p-3 mb-4 bg-light">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Trạng thái hậu kiểm</label>
                                <select name="trang_thai_giam_sat" class="form-select">
                                    @foreach($reviewStatusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('trang_thai_giam_sat', $baiLam->trang_thai_giam_sat) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Ghi chú hậu kiểm</label>
                                <textarea name="ghi_chu_giam_sat" rows="3" class="form-control">{{ old('ghi_chu_giam_sat', $baiLam->ghi_chu_giam_sat) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-primary">Cập nhật hậu kiểm</button>
                        </form>

                        <h6 class="fw-bold mb-3">Timeline vi phạm</h6>
                        <div class="d-flex flex-column gap-2 mb-4">
                            @forelse($baiLam->giamSatLogs as $log)
                                <div class="border rounded-3 p-3 bg-white">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $log->loai_su_kien_label }}</div>
                                            @if($log->mo_ta)
                                                <div class="small text-muted">{{ $log->mo_ta }}</div>
                                            @endif
                                        </div>
                                        <div class="text-end small">
                                            <span class="badge bg-{{ $log->badge_color }}">{{ $log->la_vi_pham ? 'Vi phạm' : 'Log' }}</span>
                                            <div class="text-muted mt-1">{{ $log->created_at?->format('d/m/Y H:i:s') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small">Chưa có log giám sát nào.</div>
                            @endforelse
                        </div>

                        <h6 class="fw-bold mb-3">Snapshot</h6>
                        <div class="row g-3">
                            @forelse($baiLam->giamSatSnapshots as $snapshot)
                                <div class="col-sm-6">
                                    @if($snapshot->file_url)
                                        <a href="{{ $snapshot->file_url }}" target="_blank" rel="noopener">
                                            <img src="{{ $snapshot->file_url }}" alt="Snapshot giám sát" class="img-fluid rounded-3 border">
                                        </a>
                                    @else
                                        <div class="border rounded-3 p-4 text-center text-muted small">Snapshot lỗi</div>
                                    @endif
                                    <div class="small text-muted mt-2">{{ $snapshot->captured_at?->format('d/m/Y H:i:s') }}</div>
                                </div>
                            @empty
                                <div class="text-muted small">Chưa có snapshot nào.</div>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
