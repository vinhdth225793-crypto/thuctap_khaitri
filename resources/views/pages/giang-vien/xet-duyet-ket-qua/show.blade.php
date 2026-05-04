@extends('layouts.app', ['title' => 'Lập phiếu xét duyệt kết quả'])

@section('content')
@php
    $selectedIdSet = collect($selectedIds)->map(fn ($id) => (int) $id)->all();
    $isFinalMode = $mode === \App\Models\PhieuXetDuyetKetQua::PHUONG_AN_FINAL_EXAM_ATTENDANCE;
    $totalSelected = count($selectedIdSet);
@endphp

<div class="container-fluid admin-page-x xd-page">
    {{-- ========== Welcome banner ========== --}}
    <div class="apx-welcome xd-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-file-signature"></i></div>
        <div class="apx-welcome-text">
            <div class="xd-tag-row">
                <span class="xd-loai-badge">
                    <i class="fas fa-clipboard-check"></i> XÉT DUYỆT KẾT QUẢ
                </span>
                <span class="xd-status-badge">
                    <i class="fas fa-percentage"></i>
                    Công thức: 80% Kiểm tra + 20% Điểm danh
                </span>
                @if($latestTicket)
                    <span class="xd-status-badge">
                        <i class="fas fa-history"></i>
                        Phiếu mới nhất: #{{ $latestTicket->id }}
                    </span>
                @endif
            </div>
            <h4>Phiếu xét duyệt cuối khóa — {{ $khoaHoc->ten_khoa_hoc }}</h4>
            <p>
                <span><i class="fas fa-barcode"></i> {{ $khoaHoc->ma_khoa_hoc }}</span>
                <span class="xd-sep">·</span>
                <span><i class="fas fa-users"></i> {{ $preview['summary']['student_count'] }} học viên</span>
                <span class="xd-sep">·</span>
                <span><i class="fas fa-circle-check"></i> {{ $preview['summary']['ready_count'] }} đủ dữ liệu</span>
                <span class="xd-sep">·</span>
                <span><i class="fas fa-trophy"></i> {{ $preview['summary']['passed_count'] }} tạm đạt</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.khoa-hoc.show', $khoaHoc->id) }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Về khóa học</span>
            </a>
            <a href="{{ route('giang-vien.khoa-hoc.ket-qua', $phanCong->id) }}" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="fas fa-poll-h me-1"></i> Quản lý kết quả
            </a>
        </div>
    </div>

    @include('components.alert')

    <form method="GET" action="{{ route('giang-vien.xet-duyet-ket-qua.show', $khoaHoc->id) }}" id="reviewForm">

        {{-- ========== ① Tổng quan ========== --}}
        <section class="apx-section xd-overview-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">1</span>
                    <div>
                        <h2><i class="fas fa-chart-pie"></i> Tổng quan xét duyệt</h2>
                        <p>Số liệu nhanh giúp bạn đánh giá phiếu xét duyệt trước khi gửi admin.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill"><strong>{{ $isFinalMode ? 'Cuối khóa' : 'Thành phần' }}</strong></span>
                </div>
            </header>

            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-primary">
                        <div class="aps-icon"><i class="fas fa-users"></i></div>
                        <div class="aps-text">
                            <strong>{{ $preview['summary']['student_count'] }}</strong>
                            <small>Tổng học viên</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-info">
                        <div class="aps-icon"><i class="fas fa-database"></i></div>
                        <div class="aps-text">
                            <strong>{{ $preview['summary']['ready_count'] }}</strong>
                            <small>Đủ dữ liệu chấm</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-success">
                        <div class="aps-icon"><i class="fas fa-trophy"></i></div>
                        <div class="aps-text">
                            <strong>{{ $preview['summary']['passed_count'] }}</strong>
                            <small>Tạm đạt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="apx-stat tone-warning">
                        <div class="aps-icon"><i class="fas fa-list-check"></i></div>
                        <div class="aps-text">
                            <strong>{{ $preview['summary']['selected_exam_count'] }}</strong>
                            <small>Bài đang dùng</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========== ② Phương án xét duyệt ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">2</span>
                    <div>
                        <h2><i class="fas fa-balance-scale"></i> Chọn phương án xét duyệt</h2>
                        <p>Chọn 1 trong 2 cách tính điểm cuối khóa cho học viên.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill xd-required-pill"><strong>Bắt buộc</strong></span>
                </div>
            </header>

            <div class="xd-mode-grid">
                <label class="xd-mode-card {{ $isFinalMode ? 'is-active' : '' }}" data-target="final-exams-container">
                    <input class="form-check-input mode-selector" type="radio" name="phuong_an"
                           value="final_exam_attendance" {{ $isFinalMode ? 'checked' : '' }}>
                    <div class="xd-mode-icon"><i class="fas fa-trophy"></i></div>
                    <div class="xd-mode-info">
                        <strong>Cuối khóa + điểm danh</strong>
                        <small>Sử dụng điểm từ <em>1 bài kiểm tra cuối khóa</em>.</small>
                    </div>
                    <span class="xd-mode-tick"><i class="fas fa-check-circle"></i></span>
                </label>

                <label class="xd-mode-card {{ !$isFinalMode ? 'is-active' : '' }}" data-target="component-exams-container">
                    <input class="form-check-input mode-selector" type="radio" name="phuong_an"
                           value="selected_exams_attendance" {{ !$isFinalMode ? 'checked' : '' }}>
                    <div class="xd-mode-icon"><i class="fas fa-tasks"></i></div>
                    <div class="xd-mode-info">
                        <strong>Module / buổi + điểm danh</strong>
                        <small>Trung bình từ <em>nhiều bài thành phần</em>.</small>
                    </div>
                    <span class="xd-mode-tick"><i class="fas fa-check-circle"></i></span>
                </label>
            </div>
        </section>

        {{-- ========== ③ Chọn bài kiểm tra ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">3</span>
                    <div>
                        <h2><i class="fas fa-clipboard-list"></i> Chọn bài kiểm tra</h2>
                        <p>Tích chọn bài kiểm tra phù hợp với phương án vừa chọn ở mục 2.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill"><strong>{{ $totalSelected }}</strong> đã chọn</span>
                </div>
            </header>

            {{-- Final exams --}}
            <div class="xd-exam-card exam-section {{ !$isFinalMode ? 'd-none' : '' }}" id="final-exams-container">
                <div class="xd-exam-head">
                    <span><i class="fas fa-trophy text-warning"></i> Bài kiểm tra cuối khóa</span>
                    <span class="xd-exam-pill is-warning">Bắt buộc chọn 1</span>
                </div>
                <div class="xd-exam-body custom-scrollbar">
                    <div class="row g-2">
                        @forelse($examGroups['final_exams'] as $exam)
                            <div class="col-md-6">
                                <label class="xd-exam-item {{ in_array((int) $exam->id, $selectedIdSet, true) ? 'is-checked' : '' }}">
                                    <input class="form-check-input mt-1" type="radio" name="bai_kiem_tra_ids[]"
                                           value="{{ $exam->id }}"
                                           {{ in_array((int) $exam->id, $selectedIdSet, true) ? 'checked' : '' }}
                                           @if(!$isFinalMode) disabled @endif>
                                    <div class="xd-exam-info">
                                        <strong>{{ $exam->tieu_de }}</strong>
                                        <small>Thang điểm <strong>{{ number_format((float) $exam->tong_diem, 1) }}</strong></small>
                                    </div>
                                </label>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="xd-empty">
                                    <i class="fas fa-calendar-times"></i>
                                    <p>Chưa có bài kiểm tra cuối khóa nào đã phát hành.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Component exams --}}
            <div class="xd-exam-card exam-section {{ $isFinalMode ? 'd-none' : '' }}" id="component-exams-container">
                <div class="xd-exam-head">
                    <span><i class="fas fa-tasks text-info"></i> Bài kiểm tra module / buổi học</span>
                    <span class="xd-exam-pill is-info">Chọn ít nhất 1</span>
                </div>
                <div class="xd-exam-body custom-scrollbar">
                    <div class="row g-2">
                        @forelse($examGroups['selectable_exams'] as $exam)
                            <div class="col-md-6">
                                <label class="xd-exam-item {{ in_array((int) $exam->id, $selectedIdSet, true) ? 'is-checked' : '' }}">
                                    <input class="form-check-input mt-1" type="checkbox" name="bai_kiem_tra_ids[]"
                                           value="{{ $exam->id }}"
                                           {{ in_array((int) $exam->id, $selectedIdSet, true) ? 'checked' : '' }}
                                           @if($isFinalMode) disabled @endif>
                                    <div class="xd-exam-info">
                                        <strong>{{ $exam->tieu_de }}</strong>
                                        <small>
                                            {{ $exam->moduleHoc?->ten_module ?? 'Toàn khóa' }}
                                            <span class="badge bg-light text-muted fw-normal ms-1">{{ $exam->loai_bai_kiem_tra_label }}</span>
                                        </small>
                                    </div>
                                </label>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="xd-empty">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>Chưa có bài kiểm tra thành phần nào đã phát hành.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- ========== ④ Ghi chú & gửi duyệt ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">4</span>
                    <div>
                        <h2><i class="fas fa-paper-plane"></i> Ghi chú & gửi admin phê duyệt</h2>
                        <p>Thêm ghi chú nếu cần, lưu nháp hoặc gửi phiếu cho admin xem xét cuối cùng.</p>
                    </div>
                </div>
            </header>

            <div class="xd-submit-card">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-8">
                        <label class="xd-field-label">
                            <i class="fas fa-comment-dots"></i> Ghi chú gửi admin
                        </label>
                        <textarea name="ghi_chu" rows="2" class="form-control"
                                  placeholder="Nhập ghi chú quan trọng nếu có cho kỳ xét duyệt này...">{{ old('ghi_chu', $latestTicket?->ghi_chu) }}</textarea>
                    </div>
                    <div class="col-lg-4">
                        <button type="submit" class="btn btn-primary fw-bold w-100 py-2">
                            <i class="fas fa-sync-alt me-2"></i> Cập nhật preview
                        </button>
                    </div>
                </div>

                <div class="xd-submit-actions">
                    <button type="submit" id="btn-save-draft" formmethod="POST"
                            formaction="{{ route('giang-vien.xet-duyet-ket-qua.store-draft', $khoaHoc->id) }}"
                            class="btn btn-outline-secondary fw-bold px-4">
                        <i class="fas fa-save me-2"></i> Lưu nháp
                    </button>
                    <button type="submit" id="btn-submit-final" formmethod="POST"
                            formaction="{{ route('giang-vien.xet-duyet-ket-qua.submit', $khoaHoc->id) }}"
                            class="btn btn-success fw-bold px-5 xd-submit-btn">
                        <i class="fas fa-paper-plane me-2"></i> Gửi admin phê duyệt
                    </button>
                </div>
            </div>
        </section>
    </form>

    {{-- ========== ⑤ Bảng kết quả tạm tính ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">5</span>
                <div>
                    <h2><i class="fas fa-table"></i> Bảng kết quả tạm tính</h2>
                    <p>Preview điểm cuối khóa của từng học viên dựa trên cấu hình hiện tại.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $preview['summary']['selected_exam_count'] }}</strong> bài đang dùng</span>
            </div>
        </header>

        <div class="xd-table-wrap">
            <table class="table table-hover align-middle mb-0 xd-table">
                <thead>
                    <tr>
                        <th class="ps-4">Học viên</th>
                        <th class="text-center">Điểm danh (20%)</th>
                        <th class="text-center">Kiểm tra (80%)</th>
                        <th class="text-center">Tổng điểm xét</th>
                        <th class="text-center">Kết quả</th>
                        <th>Chi tiết điểm</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preview['students'] as $row)
                        @php
                            $student = $row['student'];
                            $finalScore = $row['diem_xet_duyet'];
                            $resultStatus = $row['ket_qua'];
                            $badgeClass = match($resultStatus) {
                                'dat' => 'bg-soft-success text-success',
                                'khong_dat' => 'bg-soft-danger text-danger',
                                default => 'bg-soft-secondary text-secondary'
                            };
                            $sId = $student?->ma_nguoi_dung ?? 0;
                            $colorIdx = $sId % 6;
                            $gradients = [
                                'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                                'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                                'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                                'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                                'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                                'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                            ];
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="xd-student-avatar" style="background: {{ $gradients[$colorIdx] }};">
                                        {{ mb_strtoupper(mb_substr($student?->ho_ten ?? 'H', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark">{{ $student?->ho_ten ?? 'N/A' }}</div>
                                        <div class="smaller text-muted">{{ $student?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">{{ $row['attendance']['diem_diem_danh'] !== null ? number_format($row['attendance']['diem_diem_danh'], 2) : '—' }}</div>
                                <div class="smaller text-muted">
                                    {{ $row['attendance']['so_buoi_tham_du'] }}/{{ $row['attendance']['tong_so_buoi'] }} buổi
                                </div>
                            </td>
                            <td class="text-center fw-bold text-dark">
                                {{ $row['diem_kiem_tra'] !== null ? number_format($row['diem_kiem_tra'], 2) : '—' }}
                            </td>
                            <td class="text-center">
                                <div class="xd-final-score">{{ $finalScore !== null ? number_format($finalScore, 2) : '—' }}</div>
                                @if($row['missing_exam_count'] > 0)
                                    <div class="smaller text-danger fw-bold mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>Thiếu {{ $row['missing_exam_count'] }} bài
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 fw-bold xd-result-badge">
                                    {{ $resultStatus === 'dat' ? '✓ ĐẠT' : ($resultStatus === 'khong_dat' ? '✗ KHÔNG ĐẠT' : '· CHƯA XÉT') }}
                                </span>
                            </td>
                            <td>
                                <div class="xd-exam-detail">
                                    @forelse($row['exam_rows'] as $examRow)
                                        <div class="xd-exam-detail-row">
                                            <span class="text-truncate">{{ $examRow['tieu_de'] }}</span>
                                            <strong class="{{ $examRow['diem'] !== null ? 'text-dark' : 'text-danger' }}">
                                                {{ $examRow['diem'] !== null ? number_format($examRow['diem'], 2) : 'vắng' }}
                                            </strong>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted small italic">Chưa chọn bài.</div>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="xd-empty">
                                    <i class="fas fa-users-slash"></i>
                                    <p>Chưa có học viên nào tham gia khóa học này.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ========== ⑥ Lịch sử các phiếu ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">6</span>
                <div>
                    <h2><i class="fas fa-history"></i> Lịch sử phiếu xét duyệt</h2>
                    <p>{{ $tickets->count() }} phiếu gần nhất bạn đã lập cho khóa này.</p>
                </div>
            </div>
        </header>

        <div class="xd-table-wrap">
            <table class="table align-middle mb-0 xd-table">
                <thead>
                    <tr>
                        <th class="ps-4">Mã phiếu</th>
                        <th>Phương án</th>
                        <th class="text-center">Học viên</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-4">Cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">#{{ $ticket->id }}</td>
                            <td>
                                <div class="text-dark fw-semibold">{{ $ticket->phuong_an_label }}</div>
                                @if($ticket->ghi_chu || $ticket->reject_reason)
                                    <div class="smaller text-muted text-truncate" style="max-width: 320px;">
                                        {{ $ticket->reject_reason ?: $ticket->ghi_chu }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $ticket->chi_tiets_count }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $ticket->trang_thai_color }} rounded-pill px-3 py-2 fw-bold">
                                    {{ $ticket->trang_thai_label }}
                                </span>
                            </td>
                            <td class="text-end pe-4 text-muted small">
                                {{ $ticket->updated_at?->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="xd-empty">
                                    <i class="fas fa-folder-open"></i>
                                    <p>Chưa có phiếu xét duyệt nào trong lịch sử.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Welcome banner cho trang xét duyệt ===== */
    .xd-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }

    .xd-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .xd-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
        border-radius: 999px;
    }

    .xd-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .xd-status-badge i { font-size: 0.65rem; opacity: 0.85; }

    .apx-welcome.xd-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.xd-welcome p i { color: #fef3c7; margin-right: 4px; }
    .xd-sep { opacity: 0.5; }

    .xd-required-pill {
        background: #fee2e2 !important;
        border-color: #fca5a5 !important;
        color: #b91c1c !important;
    }
    .xd-required-pill strong { color: #b91c1c !important; }

    .xd-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        border-color: #bfdbfe;
        border-left-color: #1d4ed8;
    }

    .xd-page .apx-section-num {
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%);
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
    }

    .xd-page .apx-section-title h2,
    .xd-page .apx-section-title h2 i {
        color: #1d4ed8;
    }

    /* ===== Mode card ===== */
    .xd-mode-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .xd-mode-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .xd-mode-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 8px 22px rgba(29, 78, 216, 0.08);
        transform: translateY(-2px);
    }
    .xd-mode-card.is-active {
        border-color: #1d4ed8;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        box-shadow: 0 10px 24px rgba(29, 78, 216, 0.12);
    }

    .xd-mode-card input[type="radio"] {
        flex-shrink: 0;
        margin: 0 !important;
        width: 20px;
        height: 20px;
    }

    .xd-mode-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #fff;
        display: grid; place-items: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 6px 14px rgba(29, 78, 216, 0.25);
    }

    .xd-mode-info { flex: 1; min-width: 0; }
    .xd-mode-info strong {
        display: block;
        font-size: 0.95rem;
        color: #0f172a;
        font-weight: 800;
        margin-bottom: 4px;
    }
    .xd-mode-info small {
        display: block;
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.45;
    }
    .xd-mode-info em { color: #1d4ed8; font-style: normal; font-weight: 700; }

    .xd-mode-tick {
        position: absolute;
        top: 14px; right: 14px;
        font-size: 1.2rem;
        color: #1d4ed8;
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.25s ease;
    }
    .xd-mode-card.is-active .xd-mode-tick {
        opacity: 1;
        transform: scale(1);
    }

    /* ===== Exam selection card ===== */
    .xd-exam-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .xd-exam-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .xd-exam-head > span:first-child {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.92rem;
    }

    .xd-exam-pill {
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .xd-exam-pill.is-warning { background: #fef3c7; color: #c2410c; }
    .xd-exam-pill.is-info    { background: #e0f2fe; color: #0369a1; }

    .xd-exam-body {
        padding: 14px;
        max-height: 280px;
        overflow-y: auto;
    }

    .xd-exam-item {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 12px;
        background: #f8fafc;
        border: 1.5px solid transparent;
        border-radius: 10px;
        cursor: pointer;
        height: 100%;
        transition: all 0.2s ease;
    }
    .xd-exam-item:hover { background: #eff6ff; border-color: #bfdbfe; }
    .xd-exam-item.is-checked {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #1d4ed8;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.1);
    }

    .xd-exam-item input[type="radio"],
    .xd-exam-item input[type="checkbox"] {
        flex-shrink: 0;
        margin: 0 !important;
        width: 18px;
        height: 18px;
    }

    .xd-exam-info { flex: 1; min-width: 0; }
    .xd-exam-info strong {
        display: block;
        font-size: 0.85rem;
        color: #0f172a;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 4px;
    }
    .xd-exam-info small {
        display: block;
        font-size: 0.75rem;
        color: #64748b;
    }

    /* ===== Submit card ===== */
    .xd-submit-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #1d4ed8;
        border-radius: 14px;
        padding: 22px;
    }

    .xd-field-label {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.78rem;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .xd-field-label i { color: #1d4ed8; font-size: 0.78rem; }

    .xd-submit-card textarea.form-control {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
    }
    .xd-submit-card textarea.form-control:focus {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.12);
    }

    .xd-submit-actions {
        display: flex;
        gap: 14px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed #e2e8f0;
    }

    .xd-submit-btn {
        position: relative;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        border: 0 !important;
        color: #fff !important;
        overflow: hidden;
    }
    .xd-submit-btn::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: xdBtnShine 3s ease-in-out infinite;
    }
    @keyframes xdBtnShine {
        0%, 60% { left: -100%; }
        100%    { left: 130%; }
    }
    .xd-submit-btn:hover {
        background: linear-gradient(135deg, #15803d 0%, #14532d 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(22, 163, 74, 0.4);
        color: #fff !important;
    }

    /* ===== Result table ===== */
    .xd-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .xd-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .xd-table thead th {
        padding: 12px 10px;
        font-weight: 800;
        border-bottom: 1px solid #e2e8f0;
    }
    .xd-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
    }
    .xd-table tbody tr:last-child td { border-bottom: 0; }
    .xd-table tbody tr:hover { background: #f8fafc; }

    .xd-student-avatar {
        flex-shrink: 0;
        width: 40px; height: 40px;
        border-radius: 12px;
        color: #fff;
        display: grid; place-items: center;
        font-weight: 800;
        font-size: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .xd-final-score {
        font-size: 1.4rem;
        font-weight: 900;
        color: #1d4ed8;
        line-height: 1;
    }

    .xd-result-badge {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .xd-exam-detail {
        padding: 8px 12px;
        background: #f8fafc;
        border-radius: 8px;
        font-size: 0.78rem;
        min-width: 240px;
    }
    .xd-exam-detail-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 4px 0;
        border-bottom: 1px solid #fff;
    }
    .xd-exam-detail-row:last-child { border-bottom: 0; }
    .xd-exam-detail-row span {
        color: #64748b;
        max-width: 160px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-right: 8px;
    }

    /* ===== Empty state ===== */
    .xd-empty {
        text-align: center;
        padding: 30px 20px;
    }
    .xd-empty i {
        font-size: 2.4rem;
        color: #cbd5e1;
        margin-bottom: 12px;
        display: block;
    }
    .xd-empty p {
        color: #94a3b8;
        font-size: 0.88rem;
        margin: 0;
    }

    /* ===== Soft backgrounds ===== */
    .bg-soft-success { background-color: rgba(22, 163, 74, 0.12); }
    .bg-soft-danger { background-color: rgba(220, 38, 38, 0.12); }
    .bg-soft-warning { background-color: rgba(217, 119, 6, 0.12); }
    .bg-soft-info { background-color: rgba(8, 145, 178, 0.12); }
    .bg-soft-secondary { background-color: rgba(100, 116, 139, 0.12); }

    /* Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Responsive */
    @media (max-width: 991.98px) {
        .xd-mode-grid { grid-template-columns: 1fr; }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioModeSelectors = document.querySelectorAll('.mode-selector');
    const examSections = document.querySelectorAll('.exam-section');
    const modeCards = document.querySelectorAll('.xd-mode-card');
    const btnSubmit = document.getElementById('btn-submit-final');
    const btnDraft = document.getElementById('btn-save-draft');

    function syncCheckedState() {
        document.querySelectorAll('.xd-exam-item').forEach(item => {
            const input = item.querySelector('input');
            if (input && input.checked) item.classList.add('is-checked');
            else item.classList.remove('is-checked');
        });
    }

    function toggleSections() {
        const selected = document.querySelector('.mode-selector:checked')?.value;
        if (!selected) return;
        const targetId = selected === 'final_exam_attendance' ? 'final-exams-container' : 'component-exams-container';

        modeCards.forEach(card => {
            card.classList.toggle('is-active', card.dataset.target === targetId);
        });

        examSections.forEach(section => {
            const inputs = section.querySelectorAll('input');
            const isTarget = section.id === targetId;
            section.classList.toggle('d-none', !isTarget);
            inputs.forEach(input => input.disabled = !isTarget);
        });

        syncCheckedState();
    }

    radioModeSelectors.forEach(radio => radio.addEventListener('change', toggleSections));
    document.querySelectorAll('.xd-exam-item input').forEach(input => {
        input.addEventListener('change', syncCheckedState);
    });

    toggleSections();

    function validateSelection(e) {
        const selected = document.querySelector('.mode-selector:checked').value;
        const target = document.getElementById(selected === 'final_exam_attendance' ? 'final-exams-container' : 'component-exams-container');
        const checkedCount = target.querySelectorAll('input:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất một bài kiểm tra để tiếp tục.');
            return false;
        }
        if (this.id === 'btn-submit-final' && !confirm('Gửi phiếu xét duyệt kết quả này cho admin phê duyệt?')) {
            e.preventDefault();
            return false;
        }
    }

    if (btnSubmit) btnSubmit.addEventListener('click', validateSelection);
    if (btnDraft) btnDraft.addEventListener('click', validateSelection);
});
</script>
@endpush
@endsection
