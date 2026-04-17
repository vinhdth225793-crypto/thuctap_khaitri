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
                        <span class="label">Chế độ thi</span>
                        <span class="badge {{ $baiKiemTra->co_giam_sat ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-light text-dark border' }}">
                            {{ $baiKiemTra->co_giam_sat ? 'Giám sát nâng cao' : 'Bài thường' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Loại bài</span>
                        <strong>{{ $baiKiemTra->loai_bai_kiem_tra_label }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Loại nội dung</span>
                        <strong>{{ $baiKiemTra->content_mode_label }}</strong>
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
                    @if($baiKiemTra->co_giam_sat)
                        <div class="info-row">
                            <span class="label">Quy tắc giám sát</span>
                            <div class="text-end small">
                                <div>{{ $baiKiemTra->bat_buoc_fullscreen ? 'Yêu cầu fullscreen' : 'Không bắt buộc fullscreen' }}</div>
                                <div>{{ $baiKiemTra->bat_buoc_camera ? 'Yêu cầu camera' : 'Không bắt buộc camera' }}</div>
                                <div>Ngưỡng vi phạm: {{ $baiKiemTra->so_lan_vi_pham_toi_da }}</div>
                            </div>
                        </div>
                    @endif
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
                        @if($canStartNewAttempt)
                            <div class="alert alert-info">
                                Bạn vẫn còn <strong>{{ $remainingAttempts }}</strong> lượt làm. Có thể bấm <strong>{{ $baiKiemTra->co_giam_sat ? 'Pre-check để làm lại' : 'Làm lần tiếp theo' }}</strong> để tạo lần làm mới.
                            </div>
                        @endif
                        @if($baiKiemTra->co_giam_sat)
                            <div class="info-row">
                                <span class="label">Trạng thái giám sát</span>
                                <span class="badge bg-{{ $baiLam->trang_thai_giam_sat_color }}">{{ $baiLam->trang_thai_giam_sat_label }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Tổng vi phạm</span>
                                <strong id="currentViolationCount">{{ (int) $baiLam->tong_so_vi_pham }}</strong>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Nội dung bài kiểm tra</h5>
                    @if($canStartNewAttempt)
                        @if($baiKiemTra->co_giam_sat)
                            @if($precheckState)
                                <div class="d-flex gap-2">
                                    <a href="{{ route('hoc-vien.bai-kiem-tra.precheck', $baiKiemTra->id) }}" class="btn btn-outline-warning">Kiểm tra lại pre-check</a>
                                    <form action="{{ route('hoc-vien.bai-kiem-tra.bat-dau', $baiKiemTra->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">{{ $baiLam ? 'Làm lần tiếp theo' : 'Bắt đầu làm bài' }}</button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('hoc-vien.bai-kiem-tra.precheck', $baiKiemTra->id) }}" class="btn btn-warning">
                                    {{ $baiLam ? 'Pre-check để làm lại' : 'Kiểm tra trước khi thi' }}
                                </a>
                            @endif
                        @else
                            <form action="{{ route('hoc-vien.bai-kiem-tra.bat-dau', $baiKiemTra->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">{{ $baiLam ? 'Làm lần tiếp theo' : 'Bắt đầu làm bài' }}</button>
                            </form>
                        @endif
                    @endif
                </div>
                <div class="card-body">
                    @if($baiKiemTra->mo_ta)
                        <div class="description-box mb-4">
                            {!! nl2br(e($baiKiemTra->mo_ta)) !!}
                        </div>
                    @endif

                    @php
                        $contentModeAlertClass = match ($baiKiemTra->content_mode_key) {
                            'tu_luan_tu_do' => 'alert-warning',
                            'tu_luan_theo_cau' => 'alert-info',
                            'hon_hop' => 'alert-primary',
                            default => 'alert-secondary',
                        };
                        $contentModeDescription = match ($baiKiemTra->content_mode_key) {
                            'tu_luan_tu_do' => 'Bạn sẽ làm một bài viết tổng theo đề bài và hướng dẫn bên trên. Hệ thống không yêu cầu chọn câu hỏi từ ngân hàng cho flow này.',
                            'tu_luan_theo_cau' => 'Bạn trả lời lần lượt từng câu tự luận trong đề. Kết quả sẽ chờ giảng viên chấm tay sau khi nộp bài.',
                            'hon_hop' => 'Đề này có cả phần trắc nghiệm và tự luận. Trắc nghiệm được chấm tự động, còn phần tự luận sẽ chờ giảng viên chấm tay.',
                            default => 'Đề này chỉ sử dụng câu hỏi trắc nghiệm và hệ thống sẽ chấm tự động sau khi bạn nộp bài.',
                        };
                    @endphp

                    <div class="alert {{ $contentModeAlertClass }} d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-semibold mb-1">Chế độ {{ $baiKiemTra->content_mode_label }}</div>
                            <div class="small mb-0">{{ $contentModeDescription }}</div>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $baiKiemTra->content_mode_label }}</span>
                    </div>

                    @if($baiLam && $baiLam->is_submitted)
                        <div class="alert alert-success">
                            Bạn đã nộp bài vào lúc {{ $baiLam->nop_luc?->format('d/m/Y H:i') }}.
                            @if($baiLam->need_manual_grading)
                                Bài làm đang chờ giảng viên chấm tự luận.
                            @endif
                        </div>
                        @if($baiKiemTra->co_giam_sat)
                            <div class="alert {{ $baiLam->trang_thai_giam_sat === 'binh_thuong' ? 'alert-info' : 'alert-warning' }}">
                                Trạng thái hậu kiểm hiện tại: <strong>{{ $baiLam->trang_thai_giam_sat_label }}</strong>.
                                @if($baiLam->tong_so_vi_pham > 0)
                                    Hệ thống đã ghi nhận {{ $baiLam->tong_so_vi_pham }} vi phạm trong quá trình làm bài.
                                @endif
                            </div>
                        @endif
                    @elseif(!$baiLam && !$baiKiemTra->can_student_start)
                        <div class="alert alert-secondary">
                            Bài kiểm tra này hiện {{ strtolower($baiKiemTra->access_status_label) }}. Bạn sẽ có thể bắt đầu khi bài kiểm tra đến thời gian mở.
                        </div>
                    @elseif(!$baiLam && $baiKiemTra->co_giam_sat && $precheckState)
                        <div class="alert alert-success">
                            Bạn đã hoàn tất pre-check. Kết quả này sẽ được giữ trong một khoảng thời gian ngắn để bắt đầu bài thi.
                        </div>
                    @elseif(!$baiLam && $baiKiemTra->co_giam_sat)
                        <div class="alert alert-primary">
                            Bài thi này yêu cầu giám sát. Bạn cần hoàn tất bước pre-check trước khi bắt đầu làm bài.
                        </div>
                    @elseif(!$baiLam)
                        <div class="alert alert-primary">
                            Bạn chưa bắt đầu bài kiểm tra này. Khi bấm bắt đầu, hệ thống sẽ tạo lần làm bài mới cho bạn.
                        </div>
                    @endif

                    @if($baiLam && $baiLam->can_resume)
                        @if($baiKiemTra->co_giam_sat)
                            <div class="surveillance-live-bar mb-4">
                                <div class="surveillance-pill">
                                    <span class="surveillance-label">Vi phạm</span>
                                    <strong id="liveViolationCount">{{ (int) $baiLam->tong_so_vi_pham }}</strong> / {{ $baiKiemTra->so_lan_vi_pham_toi_da }}
                                </div>
                                <div class="surveillance-pill">
                                    <span class="surveillance-label">Fullscreen</span>
                                    <strong id="fullscreenStatusText">{{ $baiKiemTra->bat_buoc_fullscreen ? 'Chờ kích hoạt' : 'Không bắt buộc' }}</strong>
                                </div>
                                <div class="surveillance-pill">
                                    <span class="surveillance-label">Camera</span>
                                    <strong id="cameraStatusText">{{ $baiKiemTra->bat_buoc_camera ? 'Chờ kích hoạt' : 'Không bắt buộc' }}</strong>
                                </div>
                            </div>
                            <div id="surveillanceAlertArea" class="mb-3"></div>
                            <div class="camera-preview-shell {{ $baiKiemTra->bat_buoc_camera ? '' : 'd-none' }}">
                                <div class="camera-preview-header">
                                    <span>Camera giám sát</span>
                                    <span id="cameraStatusBadge" class="badge bg-secondary">Đang chờ</span>
                                </div>
                                <video id="surveillanceCameraPreview" autoplay muted playsinline></video>
                            </div>
                        @endif

                        <form action="{{ route('hoc-vien.bai-kiem-tra.nop', $baiKiemTra->id) }}" method="POST" id="examSubmissionForm">
                            @csrf
                            <input type="hidden" name="tu_dong_nop" value="0" id="autoSubmitInput">

                            @if($cauHoiHienThi->isEmpty())
                                <div class="alert alert-info">
                                    Đây là bài <strong>tự luận tự do</strong>. Bạn làm bài theo đề bài và hướng dẫn bên trên, sau đó giảng viên sẽ chấm điểm tổng.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nội dung bài làm</label>
                                    <textarea name="noi_dung_bai_lam" rows="14" class="form-control">{{ old('noi_dung_bai_lam', $baiLam->noi_dung_bai_lam) }}</textarea>
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
                                        @php
                                            $isWrong = !$chiTiet->is_dung;
                                        @endphp
                                        <div class="submitted-box {{ $isWrong ? 'is-wrong' : 'is-correct' }}">
                                            <div class="fw-bold mb-1">
                                                @if($isWrong)
                                                    <i class="fas fa-times-circle me-1"></i> Kết quả: Sai / chưa có đáp án
                                                @else
                                                    <i class="fas fa-check-circle me-1"></i> Kết quả: Đúng
                                                @endif
                                            </div>
                                            Đáp án đã chọn: {{ $chiTiet->dapAn->ky_hieu ?? 'Chưa chọn' }} - {{ $chiTiet->dapAn->noi_dung ?? 'Không có' }}<br>
                                            Điểm: {{ number_format((float) ($chiTiet->diem_tu_dong ?? 0), 2) }}
                                        </div>
                                    @else
                                        @php
                                            $isGraded = $chiTiet->diem_tu_luan !== null;
                                            $isWrongEssay = $isGraded && $chiTiet->diem_tu_luan == 0;
                                        @endphp
                                        <div class="submitted-box mb-2 {{ $isWrongEssay ? 'is-wrong' : '' }}">
                                            {!! nl2br(e($chiTiet->cau_tra_loi_text ?: 'Không có câu trả lời.')) !!}
                                        </div>
                                        <div class="small text-muted">Điểm tự luận: {{ $isGraded ? number_format((float) $chiTiet->diem_tu_luan, 2) : 'Đang chờ chấm' }}</div>
                                        @if($chiTiet->nhan_xet)
                                            <div class="small text-muted">Nhận xét: {{ $chiTiet->nhan_xet }}</div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        @else
                            @php
                                $isGradedTotal = $baiLam->diem_so !== null;
                                $isWrongTotal = $isGradedTotal && $baiLam->diem_so == 0;
                            @endphp
                            <div class="submitted-box {{ $isWrongTotal ? 'is-wrong' : '' }}">
                                {!! nl2br(e($baiLam->noi_dung_bai_lam ?: 'Không có nội dung bài làm.')) !!}
                            </div>
                            <div class="small text-muted mt-3">Điểm tự luận: {{ $isGradedTotal ? number_format((float) $baiLam->diem_so, 2) : 'Đang chờ chấm' }}</div>
                            @if($baiLam->nhan_xet)
                                <div class="small text-muted mt-1">Nhận xét: {{ $baiLam->nhan_xet }}</div>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($baiKiemTra->co_giam_sat && $baiLam && $baiLam->can_resume)
    <div class="surveillance-overlay" id="surveillanceOverlay">
        <div class="surveillance-overlay-card">
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle mb-3">Giám sát nâng cao</span>
            <h4 class="fw-bold mb-2">Kích hoạt môi trường thi</h4>
            <p class="text-muted mb-4">
                Bài thi này chỉ được tiếp tục khi môi trường giám sát đã sẵn sàng.
                @if($baiKiemTra->bat_buoc_fullscreen)
                    Bạn cần bật toàn màn hình.
                @endif
                @if($baiKiemTra->bat_buoc_camera)
                    Bạn cần bật camera để hệ thống chụp snapshot định kỳ.
                @endif
            </p>
            <ul class="small text-muted text-start mb-4">
                <li>Không chuyển tab hoặc rời khỏi cửa sổ đang thi.</li>
                <li>Không tắt camera trong quá trình làm bài nếu bài thi yêu cầu.</li>
                <li>Mọi vi phạm đều được ghi log để giảng viên và admin hậu kiểm.</li>
            </ul>
            <button type="button" class="btn btn-primary btn-lg" id="activateSurveillanceBtn">
                Kích hoạt giám sát và tiếp tục làm bài
            </button>
            <div class="small text-danger mt-3 d-none" id="surveillanceOverlayError"></div>
        </div>
    </div>
@endif

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

    .submitted-box.is-wrong {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .submitted-box.is-correct {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
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

    .surveillance-live-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
    }

    .surveillance-pill {
        border: 1px solid #e2e8f0;
        background: #fff7ed;
        border-radius: 14px;
        padding: 0.9rem 1rem;
    }

    .surveillance-label {
        display: block;
        font-size: 0.75rem;
        color: #9a3412;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.35rem;
    }

    .camera-preview-shell {
        width: 220px;
        margin-left: auto;
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        overflow: hidden;
        background: #0f172a;
        color: #fff;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
    }

    .camera-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.7rem 0.85rem;
        font-size: 0.8rem;
        background: rgba(15, 23, 42, 0.92);
    }

    #surveillanceCameraPreview {
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: cover;
        display: block;
        background: #020617;
    }

    .surveillance-overlay {
        position: fixed;
        inset: 0;
        z-index: 1200;
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .surveillance-overlay-card {
        width: min(560px, 100%);
        background: #fff;
        border-radius: 26px;
        padding: 2rem;
        box-shadow: 0 35px 80px rgba(15, 23, 42, 0.28);
        text-align: center;
    }
</style>
@endpush

@if($baiKiemTra->co_giam_sat && $baiLam && $baiLam->can_resume)
    @include('pages.hoc-vien.bai-kiem-tra._surveillance-script')
@endif
@endsection
