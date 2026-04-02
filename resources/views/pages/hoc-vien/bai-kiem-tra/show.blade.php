@extends('layouts.app', ['title' => 'Làm bài kiểm tra'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.bai-kiem-tra') }}">Bài kiểm tra</a></li>
                    @if($baiKiemTra->lich_hoc_id)
                        <li class="breadcrumb-item"><a href="{{ route('hoc-vien.buoi-hoc.show', $baiKiemTra->lich_hoc_id) }}">Buổi {{ $baiKiemTra->lichHoc->buoi_so ?: '#' }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ $baiKiemTra->tieu_de }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">{{ $baiKiemTra->tieu_de }}</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Chưa xác định khóa học' }}</p>
        </div>

        <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Danh sách bài kiểm tra
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card vip-card h-100">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Thông tin bài kiểm tra</h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Trạng thái mở bài</span>
                        <span class="badge bg-{{ $baiKiemTra->access_status_color }}">{{ $baiKiemTra->access_status_label }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Loại bài</span>
                        <strong>{{ $baiKiemTra->loai_bai_kiem_tra_label }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Loại nội dung</span>
                        <strong>{{ $baiKiemTra->loai_noi_dung_label }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Thời gian làm bài</span>
                        <strong>{{ $baiKiemTra->thoi_gian_lam_bai }} phút</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Tổng điểm</span>
                        <strong>{{ number_format((float) $baiKiemTra->tong_diem, 2) }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Số câu hỏi</span>
                        <strong>{{ $baiKiemTra->question_count }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Số lần được làm</span>
                        <strong>{{ $baiKiemTra->so_lan_duoc_lam }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Đã sử dụng</span>
                        <strong>{{ $attemptsUsed }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Còn lại</span>
                        <strong>{{ $remainingAttempts }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Ngày mở</span>
                        <strong>{{ $baiKiemTra->ngay_mo ? $baiKiemTra->ngay_mo->format('d/m/Y H:i') : 'Mở ngay' }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Ngày đóng</span>
                        <strong>{{ $baiKiemTra->ngay_dong ? $baiKiemTra->ngay_dong->format('d/m/Y H:i') : 'Chưa đặt lịch đóng' }}</strong>
                    </div>
                    @if($baiKiemTra->lich_hoc_id)
                        <div class="info-row">
                            <span class="label">Buổi học liên quan</span>
                            <a href="{{ route('hoc-vien.buoi-hoc.show', $baiKiemTra->lich_hoc_id) }}" class="fw-semibold">Mở buổi học</a>
                        </div>
                    @endif

                    @if($baiLam)
                        <hr>
                        <div class="info-row">
                            <span class="label">Trạng thái bài làm</span>
                            <span class="badge bg-{{ $baiLam->trang_thai_color }}">{{ $baiLam->trang_thai_label }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Lần làm</span>
                            <strong>{{ $baiLam->lan_lam_thu }}</strong>
                        </div>
                        <div class="info-row">
                            <span class="label">Bắt đầu lúc</span>
                            <strong>{{ $baiLam->bat_dau_luc ? $baiLam->bat_dau_luc->format('d/m/Y H:i') : 'Chưa bắt đầu' }}</strong>
                        </div>
                        <div class="info-row">
                            <span class="label">Nộp lúc</span>
                            <strong>{{ $baiLam->nop_luc ? $baiLam->nop_luc->format('d/m/Y H:i') : 'Chưa nộp' }}</strong>
                        </div>
                        <div class="info-row">
                            <span class="label">Điểm hiện tại</span>
                            <strong>{{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 2) : 'Đang chờ chấm' }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Nội dung bài kiểm tra</h5>
                    @if(!$baiLam && $baiKiemTra->can_student_start)
                        <form action="{{ route('hoc-vien.bai-kiem-tra.bat-dau', $baiKiemTra->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Bắt đầu làm bài</button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if($baiKiemTra->mo_ta)
                        <div class="description-box mb-4">
                            {!! nl2br(e($baiKiemTra->mo_ta)) !!}
                        </div>
                    @endif

                    @if($baiLam && $baiLam->is_submitted)
                        <div class="alert alert-success">
                            Bạn đã nộp bài vào lúc {{ $baiLam->nop_luc?->format('d/m/Y H:i') }}.
                            @if($baiLam->need_manual_grading)
                                Bài làm đang chờ giảng viên chấm tự luận.
                            @endif
                        </div>
                    @elseif(!$baiLam && !$baiKiemTra->can_student_start)
                        <div class="alert alert-secondary">
                            Bài kiểm tra này hiện {{ strtolower($baiKiemTra->access_status_label) }}. Bạn sẽ có thể bắt đầu khi bài kiểm tra đến thời gian mở.
                        </div>
                    @elseif(!$baiLam)
                        <div class="alert alert-primary">
                            Bạn chưa bắt đầu bài kiểm tra này. Khi bấm bắt đầu, hệ thống sẽ tạo lần làm bài mới cho bạn.
                        </div>
                    @endif

                    @if($baiLam && $baiLam->can_resume)
                        <form action="{{ route('hoc-vien.bai-kiem-tra.nop', $baiKiemTra->id) }}" method="POST">
                            @csrf

                            @if($cauHoiHienThi->isEmpty())
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nội dung bài làm</label>
                                    <textarea name="noi_dung_bai_lam" rows="14" class="form-control" required>{{ old('noi_dung_bai_lam', $baiLam->noi_dung_bai_lam) }}</textarea>
                                </div>
                            @else
                                @foreach($cauHoiHienThi as $index => $chiTiet)
                                    @php
                                        $answerDetail = $baiLam->chiTietTraLois->firstWhere('chi_tiet_bai_kiem_tra_id', $chiTiet->id);
                                        $question = $chiTiet->cauHoi;
                                    @endphp
                                    <div class="question-card mb-4">
                                        <div class="d-flex justify-content-between gap-3 mb-3">
                                            <div class="fw-semibold">Câu {{ $index + 1 }}. {!! nl2br(e($question->noi_dung ?? 'Không rõ nội dung')) !!}</div>
                                            <span class="badge bg-light text-dark">{{ number_format((float) $chiTiet->diem_so, 2) }} điểm</span>
                                        </div>

                                        @if($question->loai_cau_hoi === 'trac_nghiem')
                                            @foreach($question->dapAns as $dapAn)
                                                <label class="answer-option">
                                                    <input type="radio" name="answers[{{ $chiTiet->id }}][dap_an_cau_hoi_id]" value="{{ $dapAn->id }}" @checked(old('answers.' . $chiTiet->id . '.dap_an_cau_hoi_id', $answerDetail->dap_an_cau_hoi_id ?? null) == $dapAn->id)>
                                                    <span><strong>{{ $dapAn->ky_hieu }}.</strong> {{ $dapAn->noi_dung }}</span>
                                                </label>
                                            @endforeach
                                        @else
                                            <textarea name="answers[{{ $chiTiet->id }}][cau_tra_loi_text]" rows="6" class="form-control" placeholder="Nhập câu trả lời của bạn...">{{ old('answers.' . $chiTiet->id . '.cau_tra_loi_text', $answerDetail->cau_tra_loi_text ?? '') }}</textarea>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div class="text-muted small">
                                    Hãy kiểm tra lại câu trả lời trước khi nộp bài. Sau khi nộp, bài làm sẽ được chuyển sang trạng thái chấm điểm.
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i>Nộp bài
                                </button>
                            </div>
                        </form>
                    @elseif($baiLam && $baiLam->is_submitted)
                        @if($baiLam->chiTietTraLois->isNotEmpty())
                            @foreach($baiLam->chiTietTraLois as $index => $chiTiet)
                                <div class="question-card mb-4">
                                    <div class="d-flex justify-content-between gap-3 mb-3">
                                        <div class="fw-semibold">Câu {{ $index + 1 }}. {!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Không rõ nội dung')) !!}</div>
                                        <span class="badge bg-light text-dark">{{ number_format((float) ($chiTiet->chiTietBaiKiemTra->diem_so ?? 0), 2) }} điểm</span>
                                    </div>
                                    @if($chiTiet->cauHoi->loai_cau_hoi === 'trac_nghiem')
                                        <div class="submitted-box">
                                            Đáp án đã chọn: {{ $chiTiet->dapAn->ky_hieu ?? 'Chưa chọn' }} - {{ $chiTiet->dapAn->noi_dung ?? 'Không có' }}<br>
                                            Kết quả: {{ $chiTiet->is_dung ? 'Đúng' : 'Sai / chưa có đáp án' }}<br>
                                            Điểm: {{ number_format((float) ($chiTiet->diem_tu_dong ?? 0), 2) }}
                                        </div>
                                    @else
                                        <div class="submitted-box mb-2">{!! nl2br(e($chiTiet->cau_tra_loi_text ?: 'Không có câu trả lời.')) !!}</div>
                                        <div class="small text-muted">Điểm tự luận: {{ $chiTiet->diem_tu_luan !== null ? number_format((float) $chiTiet->diem_tu_luan, 2) : 'Đang chờ chấm' }}</div>
                                        @if($chiTiet->nhan_xet)
                                            <div class="small text-muted">Nhận xét: {{ $chiTiet->nhan_xet }}</div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="submitted-box">
                                {!! nl2br(e($baiLam->noi_dung_bai_lam ?: 'Không có nội dung bài làm.')) !!}
                            </div>
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
    .submitted-box,
    .question-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 1.25rem;
        line-height: 1.8;
    }

    .submitted-box {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .answer-option {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.85rem 1rem;
        margin-bottom: 0.75rem;
        background: #fff;
    }
</style>
@endpush
@endsection
