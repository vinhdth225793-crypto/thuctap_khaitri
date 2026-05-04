@extends('layouts.app', ['title' => 'Chi tiết bài kiểm tra'])

@section('content')
@php
    $isFreeEssayApproval = $baiKiemTra->content_mode_key === 'tu_luan_tu_do';
    $approvalQuestionCount = $isFreeEssayApproval && filled($baiKiemTra->mo_ta)
        ? 1
        : $baiKiemTra->chiTietCauHois->count();

    $submissionCount = $baiKiemTra->baiLams->count();
    $completedSubmissionCount = $baiKiemTra->baiLams->where('trang_thai', 'hoan_thanh')->count();
    $gradedSubmissionCount = $baiKiemTra->baiLams->filter(fn ($baiLam) => $baiLam->diem_so !== null)->count();

    $approvalStatusClass = match($baiKiemTra->trang_thai_duyet) {
        'da_duyet' => 'is-success',
        'cho_duyet' => 'is-warning',
        'tu_choi' => 'is-danger',
        default => 'is-secondary',
    };

    $publishStatusClass = match($baiKiemTra->trang_thai_phat_hanh) {
        'phat_hanh' => 'is-success',
        'dong' => 'is-danger',
        default => 'is-secondary',
    };

    $contentStatusClass = $isFreeEssayApproval ? 'is-info' : 'is-blue';
@endphp

<div class="container-fluid admin-page-x approval-page">
    <div class="apx-welcome approval-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-file-invoice"></i></div>
        <div class="apx-welcome-text">
            <div class="approval-tag-row">
                <span class="approval-chip">
                    <i class="fas fa-shield-alt"></i> PHÊ DUYỆT ĐỀ THI
                </span>
                <span class="approval-status-pill {{ $approvalStatusClass }}">
                    <i class="fas fa-circle-check"></i> {{ $baiKiemTra->trang_thai_duyet_label }}
                </span>
                <span class="approval-status-pill {{ $publishStatusClass }}">
                    <i class="fas fa-paper-plane"></i> {{ $baiKiemTra->trang_thai_phat_hanh_label }}
                </span>
                <span class="approval-status-pill {{ $contentStatusClass }}">
                    <i class="fas {{ $isFreeEssayApproval ? 'fa-pen-nib' : 'fa-list-check' }}"></i> {{ $baiKiemTra->loai_noi_dung_label }}
                </span>
            </div>
            <h4>{{ $baiKiemTra->tieu_de }}</h4>
            <p>
                <span><i class="fas fa-graduation-cap"></i> {{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Chưa gán khóa học' }}</span>
                <span class="approval-sep">·</span>
                <span><i class="fas fa-cubes"></i> {{ $baiKiemTra->moduleHoc->ten_module ?? 'Dùng chung toàn khóa' }}</span>
                <span class="approval-sep">·</span>
                <span><i class="fas fa-user-tie"></i> {{ $baiKiemTra->nguoiTao->ho_ten ?? 'N/A' }}</span>
                <span class="approval-sep">·</span>
                <span><i class="far fa-clock"></i> {{ $baiKiemTra->thoi_gian_lam_bai }} phút</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Về danh sách duyệt</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan đề thi</h2>
                    <p>Nhìn nhanh cấu trúc đề, mức độ hoàn thiện và lượng bài làm để quyết định duyệt hoặc yêu cầu chỉnh sửa.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $completedSubmissionCount }}</strong> bài đã nộp</span>
                <span class="apx-meta-pill"><strong>{{ $gradedSubmissionCount }}</strong> bài đã chấm</span>
            </div>
        </header>

        <div class="apx-section-body">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-primary">
                        <div class="aps-icon"><i class="fas fa-list-ol"></i></div>
                        <div class="aps-text">
                            <strong>{{ $approvalQuestionCount }}</strong>
                            <small>{{ $isFreeEssayApproval ? 'Nội dung chính' : 'Câu hỏi' }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-success">
                        <div class="aps-icon"><i class="fas fa-star"></i></div>
                        <div class="aps-text">
                            <strong>{{ number_format((float) $baiKiemTra->tong_diem, 2) }}</strong>
                            <small>Tổng điểm</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-warning">
                        <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="aps-text">
                            <strong>{{ $baiKiemTra->thoi_gian_lam_bai }}</strong>
                            <small>Phút làm bài</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-info">
                        <div class="aps-icon"><i class="fas fa-file-signature"></i></div>
                        <div class="aps-text">
                            <strong>{{ $submissionCount }}</strong>
                            <small>Lượt làm bài</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-4">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">2</span>
                        <div>
                            <h2><i class="fas fa-clipboard-check"></i> Thông tin và phê duyệt</h2>
                            <p>Kiểm tra metadata, theo dõi trạng thái hiện tại và xử lý duyệt, từ chối hoặc phát hành.</p>
                        </div>
                    </div>
                </header>

                <div class="apx-section-body">
                    <div class="approval-info-card">
                        <h3 class="approval-card-title">Thông tin chung</h3>
                        <div class="approval-kv-list">
                            <div class="approval-kv">
                                <span>Khóa học</span>
                                <strong>{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</strong>
                            </div>
                            <div class="approval-kv">
                                <span>Module</span>
                                <strong>{{ $baiKiemTra->moduleHoc->ten_module ?? 'Dùng chung' }}</strong>
                            </div>
                            <div class="approval-kv">
                                <span>Người tạo</span>
                                <strong>{{ $baiKiemTra->nguoiTao->ho_ten ?? 'N/A' }}</strong>
                            </div>
                            <div class="approval-kv">
                                <span>Số lần làm bài</span>
                                <strong>{{ $baiKiemTra->so_lan_duoc_lam }} lần</strong>
                            </div>
                            <div class="approval-kv">
                                <span>Ngày tạo</span>
                                <strong>{{ $baiKiemTra->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="approval-info-card">
                        <h3 class="approval-card-title">Trạng thái hiện tại</h3>
                        <div class="approval-status-grid">
                            <div class="approval-status-box {{ $approvalStatusClass }}">
                                <small>Duyệt</small>
                                <strong>{{ $baiKiemTra->trang_thai_duyet_label }}</strong>
                            </div>
                            <div class="approval-status-box {{ $publishStatusClass }}">
                                <small>Phát hành</small>
                                <strong>{{ $baiKiemTra->trang_thai_phat_hanh_label }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="approval-action-card">
                        <h3 class="approval-card-title">Thao tác phê duyệt</h3>

                        @if($baiKiemTra->trang_thai_duyet === 'da_duyet')
                            <div class="mb-3">
                                <label class="approval-form-label">Ghi chú duyệt</label>
                                <textarea rows="3" class="form-control" readonly>{{ $baiKiemTra->ghi_chu_duyet }}</textarea>
                            </div>
                            <button type="button" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm" disabled>
                                <i class="fas fa-check-double me-2"></i> Đã duyệt đề thi
                            </button>
                        @else
                            <form action="{{ route('admin.kiem-tra-online.phe-duyet.approve', $baiKiemTra->id) }}" method="POST" class="mb-3" onsubmit="return confirm('Bạn chắc chắn muốn duyệt đề thi này?')">
                                @csrf
                                <div class="mb-3">
                                    <label class="approval-form-label">Ghi chú duyệt (nếu có)</label>
                                    <textarea name="ghi_chu_duyet"
                                              rows="3"
                                              class="form-control"
                                              placeholder="Nội dung nhắn gửi đến giảng viên...">{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i> Duyệt đề thi
                                </button>
                            </form>
                        @endif

                        <button class="btn btn-outline-danger w-100 fw-bold py-2 rounded-3 mb-3" data-bs-toggle="collapse" data-bs-target="#rejectCollapse">
                            <i class="fas fa-times-circle me-2"></i> Từ chối đề thi
                        </button>

                        <div class="collapse" id="rejectCollapse">
                            <form action="{{ route('admin.kiem-tra-online.phe-duyet.reject', $baiKiemTra->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn từ chối đề thi này?')">
                                @csrf
                                <div class="approval-reject-shell">
                                    <label class="approval-form-label text-danger">Lý do từ chối (bắt buộc)</label>
                                    <textarea name="ghi_chu_duyet"
                                              rows="3"
                                              class="form-control border-danger border-opacity-25 mb-3"
                                              placeholder="Nhập lý do cần sửa đổi..."
                                              required>{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                                    <button type="submit" class="btn btn-danger w-100 fw-bold">
                                        Xác nhận từ chối
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="approval-action-divider">
                            @if($baiKiemTra->trang_thai_duyet === 'da_duyet' && $baiKiemTra->trang_thai_phat_hanh !== 'phat_hanh')
                                <form action="{{ route('admin.kiem-tra-online.phe-duyet.publish', $baiKiemTra->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-3">
                                        <i class="fas fa-paper-plane me-2"></i> Phát hành đến học viên
                                    </button>
                                </form>
                            @endif

                            @if($baiKiemTra->trang_thai_phat_hanh === 'phat_hanh')
                                <form action="{{ route('admin.kiem-tra-online.phe-duyet.close', $baiKiemTra->id) }}" method="POST" onsubmit="return confirm('Bạn muốn đóng đề thi này? Học viên sẽ không thể làm bài nữa.');">
                                    @csrf
                                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2 rounded-3 shadow-sm">
                                        <i class="fas fa-lock me-2"></i> Đóng đề thi
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-xl-8">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">3</span>
                        <div>
                            <h2><i class="fas {{ $isFreeEssayApproval ? 'fa-pen-nib' : 'fa-list-ol' }}"></i> Nội dung đề thi</h2>
                            <p>Xem nhanh cấu trúc câu hỏi, đáp án đúng và độ khó trước khi ra quyết định phê duyệt.</p>
                        </div>
                    </div>
                    <div class="apx-section-meta">
                        <span class="apx-meta-pill"><strong>{{ $approvalQuestionCount }}</strong> {{ $isFreeEssayApproval ? 'nội dung' : 'câu hỏi' }}</span>
                    </div>
                </header>

                <div class="apx-section-body">
                    @if($isFreeEssayApproval)
                        <div class="approval-free-essay">
                            <div class="approval-free-head">
                                <div class="approval-free-icon">
                                    <i class="fas fa-pen-nib"></i>
                                </div>
                                <div>
                                    <h3>Nội dung đề tự luận tự do</h3>
                                    <p>Đây là đề tự luận không tách thành danh sách câu hỏi trắc nghiệm.</p>
                                </div>
                            </div>

                            <div class="approval-free-body">
                                <label class="approval-form-label">Đề bài / hướng dẫn</label>
                                <div class="approval-free-content">{!! nl2br(e($baiKiemTra->mo_ta ?: 'Không có nội dung hướng dẫn.')) !!}</div>
                            </div>

                            <div class="approval-note is-success">
                                <i class="fas fa-star"></i>
                                <div>
                                    <strong>{{ number_format((float) $baiKiemTra->tong_diem, 2) }} điểm</strong>
                                    <span>Tổng điểm chấm cho bài tự luận tự do.</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="approval-question-list approval-scroll">
                            @forelse($baiKiemTra->chiTietCauHois as $index => $chiTiet)
                                @php
                                    $difficultyClass = match(optional($chiTiet->cauHoi)->muc_do) {
                                        'de' => 'is-success',
                                        'trung_binh' => 'is-warning',
                                        default => 'is-danger',
                                    };
                                @endphp

                                <article class="approval-question-card">
                                    <div class="approval-question-head">
                                        <div class="approval-question-num">{{ $index + 1 }}</div>
                                        <div class="approval-question-main">
                                            <h3 class="approval-question-title">{!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Nội dung không xác định')) !!}</h3>
                                            <div class="approval-question-badges">
                                                <span class="approval-status-pill is-secondary">
                                                    <i class="fas fa-layer-group"></i> {{ $chiTiet->cauHoi->loai_cau_hoi_label ?? 'N/A' }}
                                                </span>
                                                <span class="approval-status-pill is-blue">
                                                    <i class="fas fa-star"></i> {{ number_format((float) $chiTiet->diem_so, 2) }} điểm
                                                </span>
                                                <span class="approval-status-pill {{ $difficultyClass }}">
                                                    <i class="fas fa-signal"></i> {{ $chiTiet->cauHoi->muc_do_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    @if(optional($chiTiet->cauHoi)->loai_cau_hoi === 'trac_nghiem')
                                        <div class="row g-2 mt-1">
                                            @foreach($chiTiet->cauHoi->dapAns as $dapAn)
                                                <div class="col-md-6">
                                                    <div class="approval-answer-card {{ $dapAn->is_dap_an_dung ? 'is-correct' : '' }}">
                                                        <div class="approval-answer-key">{{ $dapAn->ky_hieu }}</div>
                                                        <div class="approval-answer-body">
                                                            <div class="approval-answer-text">{{ $dapAn->noi_dung }}</div>
                                                        </div>
                                                        @if($dapAn->is_dap_an_dung)
                                                            <i class="fas fa-check-circle text-success fs-5"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif(optional($chiTiet->cauHoi)->loai_cau_hoi === 'tu_luan')
                                        <div class="approval-note is-info mt-3">
                                            <i class="fas fa-info-circle"></i>
                                            <div>
                                                <strong>Câu hỏi tự luận</strong>
                                                <span>Học viên sẽ trả lời bằng văn bản và giảng viên chấm sau.</span>
                                            </div>
                                        </div>
                                    @endif
                                </article>
                            @empty
                                <div class="approval-empty">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <h3>Đề thi này hiện chưa có nội dung câu hỏi</h3>
                                    <p>Hãy kiểm tra lại cấu hình đề hoặc yêu cầu giảng viên bổ sung nội dung trước khi duyệt.</p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
            </section>

            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">4</span>
                        <div>
                            <h2><i class="fas fa-chart-column"></i> Thống kê và bài làm</h2>
                            <p>Theo dõi danh sách học viên đã làm bài, trạng thái nộp và mức độ hoàn thành chấm điểm.</p>
                        </div>
                    </div>
                    <div class="apx-section-meta">
                        <span class="apx-meta-pill"><strong>{{ $submissionCount }}</strong> lượt làm</span>
                    </div>
                </header>

                <div class="apx-section-body">
                    <div class="approval-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4 py-3">Học viên</th>
                                        <th class="py-3 text-center">Lần thi</th>
                                        <th class="py-3 text-center">Nộp lúc</th>
                                        <th class="py-3 text-center">Trạng thái</th>
                                        <th class="pe-4 py-3 text-end">Điểm số</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @forelse($baiKiemTra->baiLams as $baiLam)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark small">{{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</div>
                                                <div class="text-muted smaller">{{ $baiLam->hocVien->email ?? 'N/A' }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border rounded-pill px-2 fw-bold">#{{ $baiLam->lan_lam_thu }}</span>
                                            </td>
                                            <td class="text-center small text-muted">
                                                {{ $baiLam->nop_luc?->format('d/m/Y H:i') ?? '—' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $baiLam->trang_thai === 'hoan_thanh' ? 'success' : 'warning' }} rounded-pill px-2">
                                                    {{ $baiLam->trang_thai_label }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <span class="fw-bold fs-6 {{ $baiLam->diem_so !== null ? 'text-primary' : 'text-muted' }}">
                                                    {{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 2) : 'Chưa chấm' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted small">
                                                Chưa có dữ liệu bài làm cho đề thi này.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@include('pages.admin.kiem-tra-online.phe-duyet.partials.shared-styles')
@endsection
