@extends('layouts.app', ['title' => 'Quản lý kết quả học tập'])

@section('content')
@php
    // Stats nhanh
    $totalStudents = count($studentResults);
    $lockedCount = collect($studentResults)
        ->filter(fn ($r) => optional($r['module_result'])->da_chot)
        ->count();
    $passingCount = collect($studentResults)
        ->filter(fn ($r) => ($r['breakdown']['summary']['final_score'] ?? 0) >= 5)
        ->count();
    $avgScore = $totalStudents > 0
        ? round(collect($studentResults)->avg(fn ($r) => $r['breakdown']['summary']['final_score'] ?? 0), 2)
        : 0;
@endphp

<div class="container-fluid admin-page-x kq-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome kq-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-poll-h"></i></div>
        <div class="apx-welcome-text">
            <div class="kq-tag-row">
                <span class="kq-loai-badge">
                    <i class="fas fa-square-poll-vertical"></i> QUẢN LÝ KẾT QUẢ
                </span>
                <span class="kq-status-badge">
                    <i class="fas fa-percentage"></i>
                    Công thức: A × {{ $studentResults[0]['breakdown']['weights']['attendance'] ?? 20 }}% + B × {{ $studentResults[0]['breakdown']['weights']['assessment'] ?? 80 }}%
                </span>
                @if($lockedCount > 0)
                    <span class="kq-status-badge">
                        <i class="fas fa-lock"></i>
                        {{ $lockedCount }}/{{ $totalStudents }} đã chốt điểm
                    </span>
                @endif
            </div>
            <h4>Bảng kết quả module — {{ $phanCong->moduleHoc->ten_module }}</h4>
            <p>
                <span><i class="fas fa-graduation-cap"></i> {{ $khoaHoc->ten_khoa_hoc }}</span>
                <span class="kq-sep">·</span>
                <span><i class="fas fa-barcode"></i> {{ $khoaHoc->ma_khoa_hoc }}</span>
                <span class="kq-sep">·</span>
                <span><i class="fas fa-cube"></i> {{ $phanCong->moduleHoc->ma_module }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.khoa-hoc.show', $phanCong->id) }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Về khóa học</span>
            </a>
            <button type="button" class="btn btn-light text-primary fw-bold shadow-sm" id="btn-refresh-all">
                <i class="fas fa-sync-alt me-1"></i> Tính lại điểm
            </button>
        </div>
    </div>

    @include('components.alert')

    {{-- ========== ① Tổng quan ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan kết quả</h2>
                    <p>Bốn chỉ số nhanh — số học viên, đã chốt, đạt điểm, và điểm trung bình lớp.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $totalStudents }}</strong> HV</span>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-users"></i></div>
                    <div class="aps-text">
                        <strong>{{ $totalStudents }}</strong>
                        <small>Tổng học viên</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-lock"></i></div>
                    <div class="aps-text">
                        <strong>{{ $lockedCount }}</strong>
                        <small>Đã chốt điểm</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-trophy"></i></div>
                    <div class="aps-text">
                        <strong>{{ $passingCount }}</strong>
                        <small>Tạm đạt (≥ 5)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="aps-text">
                        <strong>{{ number_format($avgScore, 2) }}</strong>
                        <small>Điểm TB lớp</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Bảng kết quả ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-table"></i> Bảng điểm chi tiết từng học viên</h2>
                    <p>Click "Xem chi tiết" để xem điểm danh & điểm bài kiểm tra. Chốt điểm để gửi admin duyệt.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $totalStudents }}</strong> dòng dữ liệu</span>
            </div>
        </header>

        <div class="kq-table-wrap">
            <table class="table table-hover align-middle mb-0 kq-table" id="results-table">
                <thead>
                    <tr>
                        <th class="ps-4">Học viên</th>
                        <th class="text-center">Quá trình (A)</th>
                        <th class="text-center">Kiểm tra (B)</th>
                        <th class="text-center">Tổng kết</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentResults as $data)
                        @php
                            $student = $data['student'];
                            $moduleResult = $data['module_result'] ?? null;
                            $breakdown = $data['breakdown'];
                            $summary = $breakdown['summary'];
                            $sId = $student->ma_nguoi_dung ?? 0;
                            $colorIdx = $sId % 6;
                            $gradients = [
                                'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                                'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                                'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                                'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                                'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                                'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                            ];
                            $finalScore = $summary['final_score'] ?: 0;
                        @endphp
                        <tr class="student-row" data-student-id="{{ $student->ma_nguoi_dung }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="kq-student-avatar" style="background: {{ $gradients[$colorIdx] }};">
                                        {{ mb_strtoupper(mb_substr($student->ho_ten ?? 'H', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="kq-student-name">{{ $student->ho_ten }}</div>
                                        <div class="kq-student-email">{{ $student->email }}</div>
                                        <button class="kq-toggle-detail btn-toggle-details" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#details-{{ $student->ma_nguoi_dung }}">
                                            <i class="fas fa-chevron-down"></i>
                                            <span>Xem chi tiết</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="kq-score-cell">
                                    <strong>{{ number_format($summary['process_score'] ?: 0, 2) }}</strong>
                                    <small>Trọng số {{ $breakdown['weights']['attendance'] }}%</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="kq-score-cell">
                                    <strong>{{ number_format($summary['module_exam_score'] ?: 0, 2) }}</strong>
                                    <small>Trọng số {{ $breakdown['weights']['assessment'] }}%</small>
                                    @if($summary['avg_small_exam_score'] !== null || $summary['large_exam_score'] !== null)
                                        <button class="kq-info-btn"
                                                data-bs-toggle="tooltip"
                                                title="Bài nhỏ TB: {{ $summary['avg_small_exam_score'] ?: '—' }} | Bài lớn: {{ $summary['large_exam_score'] ?: '—' }}">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="kq-final-score {{ $finalScore >= 5 ? 'is-pass' : ($finalScore > 0 ? 'is-fail' : '') }}">
                                    {{ number_format($finalScore, 2) }}
                                </div>
                                @if($moduleResult && $moduleResult->da_chot)
                                    <div class="kq-locked-mark">
                                        <i class="fas fa-check-double"></i>
                                        Đã chốt: {{ number_format((float) $moduleResult->diem_giang_vien_chot, 2) }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($moduleResult)
                                    <div class="kq-status-stack">
                                        @if($moduleResult->da_chot)
                                            <span class="kq-pill is-success">
                                                <i class="fas fa-lock"></i> Đã chốt
                                            </span>
                                            <small class="kq-pill-sub">{{ $moduleResult->trang_thai_duyet_label }}</small>
                                            @if($moduleResult->trang_thai_duyet !== \App\Models\KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET)
                                                <button type="button"
                                                        class="kq-mini-btn is-danger btn-mo-chot"
                                                        data-result-id="{{ $moduleResult->id }}"
                                                        data-student-name="{{ $student->ho_ten }}">
                                                    <i class="fas fa-unlock"></i> Mở chốt
                                                </button>
                                            @endif
                                        @else
                                            <span class="kq-pill is-secondary">
                                                <i class="fas fa-circle-dot"></i> Chưa chốt
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="kq-pill is-secondary">
                                        <i class="fas fa-circle"></i> Chưa khởi tạo
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4">
                                <div class="kq-actions">
                                    <form method="POST" action="{{ route('giang-vien.khoa-hoc.ket-qua.chot', $phanCong->id) }}" class="kq-chot-form">
                                        @csrf
                                        <input type="hidden" name="hoc_vien_id" value="{{ $student->ma_nguoi_dung }}">
                                        <div class="kq-chot-row">
                                            <input type="text" name="ghi_chu_chot" class="form-control form-control-sm"
                                                   placeholder="Ghi chú chốt..." value="{{ $moduleResult?->ghi_chu_chot }}">
                                            <button type="submit"
                                                    class="kq-chot-btn {{ $moduleResult?->da_chot ? 'is-update' : 'is-lock' }}"
                                                    onclick="return confirm('{{ $moduleResult?->da_chot ? 'Cập nhật điểm đã chốt và gửi admin duyệt lại?' : 'Xác nhận chốt bảng điểm này và gửi admin phê duyệt?' }}')"
                                                    title="{{ $moduleResult?->da_chot ? 'Cập nhật chốt' : 'Chốt điểm' }}">
                                                <i class="fas {{ $moduleResult?->da_chot ? 'fa-sync-alt' : 'fa-lock' }}"></i>
                                            </button>
                                        </div>
                                    </form>

                                    @if($moduleResult)
                                        <div class="kq-status-row">
                                            <select class="form-select form-select-sm select-status" data-result-id="{{ $moduleResult->id }}">
                                                <option value="dang_hoc" {{ $moduleResult->trang_thai === 'dang_hoc' ? 'selected' : '' }}>Đang học</option>
                                                <option value="hoan_thanh" {{ $moduleResult->trang_thai === 'hoan_thanh' ? 'selected' : '' }}>Hoàn thành</option>
                                                <option value="dat" {{ $moduleResult->trang_thai === 'dat' ? 'selected' : '' }}>Đạt</option>
                                                <option value="khong_dat" {{ $moduleResult->trang_thai === 'khong_dat' ? 'selected' : '' }}>Không đạt</option>
                                            </select>
                                            <button class="kq-comment-btn btn-save-comment-row"
                                                    data-result-id="{{ $moduleResult->id }}"
                                                    title="Nhận xét giảng viên">
                                                <i class="fas fa-comment-dots"></i>
                                            </button>
                                            <input type="hidden" class="input-comment-hidden" id="comment-{{ $moduleResult->id }}" value="{{ $moduleResult->nhan_xet_giang_vien }}">
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        {{-- Collapsible details --}}
                        <tr class="kq-detail-row">
                            <td colspan="6" class="p-0 border-0">
                                <div class="collapse" id="details-{{ $student->ma_nguoi_dung }}">
                                    <div class="kq-detail-body">
                                        <div class="row g-3">
                                            {{-- Attendance --}}
                                            <div class="col-md-4">
                                                <div class="kq-detail-card">
                                                    <div class="kq-detail-head">
                                                        <i class="fas fa-calendar-check"></i>
                                                        <strong>Chuyên cần</strong>
                                                    </div>
                                                    <div class="kq-detail-rows">
                                                        <div class="kq-detail-row-item">
                                                            <span>Tổng số buổi</span>
                                                            <strong>{{ $breakdown['attendance']['tong_so_buoi'] }}</strong>
                                                        </div>
                                                        <div class="kq-detail-row-item">
                                                            <span>Đã tham gia</span>
                                                            <strong class="text-success">{{ $breakdown['attendance']['so_buoi_tham_du'] }}</strong>
                                                        </div>
                                                        <div class="kq-detail-row-item">
                                                            <span>Tỷ lệ hiện diện</span>
                                                            <strong class="text-primary">{{ $breakdown['attendance']['ty_le_tham_du'] }}%</strong>
                                                        </div>
                                                        <div class="kq-detail-row-item kq-detail-row-final">
                                                            <span>Điểm quá trình</span>
                                                            <strong>{{ number_format($breakdown['attendance']['diem_diem_danh'] ?: 0, 2) }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Exams --}}
                                            <div class="col-md-8">
                                                <div class="kq-detail-card">
                                                    <div class="kq-detail-head">
                                                        <i class="fas fa-file-invoice"></i>
                                                        <strong>Điểm bài kiểm tra</strong>
                                                    </div>
                                                    <div class="kq-exam-table-wrap">
                                                        <table class="table table-sm align-middle mb-0 kq-exam-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Tên bài</th>
                                                                    <th class="text-center">Loại</th>
                                                                    <th class="text-center">Điểm</th>
                                                                    <th class="text-center">Trạng thái</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($breakdown['exam_results'] as $er)
                                                                    <tr>
                                                                        <td>{{ $er['tieu_de'] }}</td>
                                                                        <td class="text-center">
                                                                            @if($er['loai'] === 'cuoi_module')
                                                                                <span class="badge bg-danger">Bài lớn</span>
                                                                            @else
                                                                                <span class="badge bg-info text-white">Bài nhỏ</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center fw-bold text-primary">{{ number_format($er['diem'], 2) }}</td>
                                                                        <td class="text-center small text-muted">{{ $er['trang_thai'] }}</td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="4" class="text-center text-muted py-3">Chưa có bài kiểm tra nào.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="2" class="text-end text-muted">TB bài nhỏ</td>
                                                                    <td class="text-center fw-bold">{{ number_format($summary['avg_small_exam_score'] ?: 0, 2) }}</td>
                                                                    <td></td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" class="text-end text-muted">Bài lớn</td>
                                                                    <td class="text-center fw-bold">{{ number_format($summary['large_exam_score'] ?: 0, 2) }}</td>
                                                                    <td></td>
                                                                </tr>
                                                                <tr class="kq-exam-final">
                                                                    <td colspan="2" class="text-end">Điểm kiểm tra (B)</td>
                                                                    <td class="text-center">{{ number_format($summary['module_exam_score'] ?: 0, 2) }}</td>
                                                                    <td></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="kq-empty">
                                    <i class="fas fa-users-slash"></i>
                                    <p>Không có học viên nào trong khóa học này.</p>
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

@push('scripts')
{{-- Modal Nhận xét --}}
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="fas fa-comment-dots text-primary me-2"></i> Nhận xét giảng viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-result-id">
                <label class="form-label small fw-bold text-muted">Nội dung nhận xét</label>
                <textarea class="form-control rounded-3" id="modal-comment-text" rows="4"
                          placeholder="Nhập nhận xét về quá trình học tập của học viên..."></textarea>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btn-modal-save-comment">
                    <i class="fas fa-save me-1"></i> Lưu nhận xét
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Mở chốt --}}
<div class="modal fade" id="unlockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-mo-chot" method="POST" action="{{ route('giang-vien.khoa-hoc.ket-qua.mo-chot', $phanCong->id) }}" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="fas fa-unlock text-danger me-2"></i> Mở khóa chốt điểm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="result_id" id="unlock-result-id">
                <div class="alert alert-info smaller">
                    <i class="fas fa-info-circle me-2"></i>
                    Mở khóa chốt điểm cho học viên <strong id="unlock-student-name"></strong>.
                    Sau khi mở khóa, giảng viên có thể cập nhật lại điểm và chốt lại để gửi admin duyệt.
                </div>
                <label class="form-label small fw-bold text-muted">Lý do mở chốt <span class="text-danger">*</span></label>
                <textarea class="form-control rounded-3" name="ly_do" id="unlock-reason" rows="3"
                          placeholder="Nhập lý do cần mở chốt điểm..." required></textarea>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4">
                    <i class="fas fa-unlock me-1"></i> Xác nhận mở chốt
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(function() {
    const commentModal = new bootstrap.Modal(document.getElementById('commentModal'));
    const unlockModal = new bootstrap.Modal(document.getElementById('unlockModal'));

    // Toggle chevron icon trong nút "Xem chi tiết"
    $('.btn-toggle-details').on('click', function() {
        const $icon = $(this).find('i.fas');
        setTimeout(() => {
            const target = $($(this).data('bs-target'));
            if (target.hasClass('show')) {
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $(this).find('span').text('Đóng chi tiết');
            } else {
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $(this).find('span').text('Xem chi tiết');
            }
        }, 50);
    });

    $('.btn-mo-chot').on('click', function() {
        const resultId = $(this).data('result-id');
        const studentName = $(this).data('student-name');
        $('#unlock-result-id').val(resultId);
        $('#unlock-student-name').text(studentName);
        $('#unlock-reason').val('');
        unlockModal.show();
    });

    $('.btn-save-comment-row').on('click', function() {
        const resultId = $(this).data('result-id');
        const currentComment = $(`#comment-${resultId}`).val();
        $('#modal-result-id').val(resultId);
        $('#modal-comment-text').val(currentComment);
        commentModal.show();
    });

    $('#btn-modal-save-comment').on('click', function() {
        const $btn = $(this);
        const resultId = $('#modal-result-id').val();
        const comment = $('#modal-comment-text').val();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Đang lưu...');
        updateResult(resultId, { nhan_xet: comment }, function() {
            $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Lưu nhận xét');
            $(`#comment-${resultId}`).val(comment);
            commentModal.hide();
            alert('Đã lưu nhận xét thành công.');
        });
    });

    $('.select-status').on('change', function() {
        const $el = $(this);
        const resultId = $el.data('result-id');
        const status = $el.val();
        updateResult(resultId, { trang_thai: status });
    });

    function updateResult(resultId, data, callback) {
        $.ajax({
            url: "{{ route('giang-vien.khoa-hoc.ket-qua.update', $phanCong->id) }}",
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', result_id: resultId, ...data },
            success: function() { if (callback) callback(); },
            error: function(xhr) {
                alert('Có lỗi xảy ra: ' + (xhr.responseJSON?.message ?? xhr.statusText));
                if (callback) callback();
            }
        });
    }

    $('#btn-refresh-all').on('click', function() {
        if (confirm('Hệ thống sẽ tính toán lại toàn bộ điểm dựa trên dữ liệu bài thi mới nhất. Tiếp tục?')) {
            location.reload();
        }
    });

    // Tooltip
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endpush

<style>
    /* ===== Welcome banner xanh dương ===== */
    .kq-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }

    .kq-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .kq-loai-badge {
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

    .kq-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .kq-status-badge i { font-size: 0.65rem; opacity: 0.85; }

    .apx-welcome.kq-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.kq-welcome p i { color: #fef3c7; margin-right: 4px; }
    .kq-sep { opacity: 0.5; }

    /* ===== Bảng kết quả ===== */
    .kq-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .kq-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .kq-table thead th {
        padding: 14px 10px;
        font-weight: 800;
        border-bottom: 1px solid #bfdbfe;
    }
    .kq-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
    }
    .kq-table .kq-detail-row td {
        padding: 0;
        background: #f8fafc;
    }
    .kq-table tbody .student-row:hover { background: #f8fafc; }

    /* Avatar HV */
    .kq-student-avatar {
        flex-shrink: 0;
        width: 44px; height: 44px;
        border-radius: 12px;
        color: #fff;
        display: grid; place-items: center;
        font-weight: 800;
        font-size: 1.05rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .kq-student-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .kq-student-email {
        font-size: 0.78rem;
        color: #64748b;
        margin-top: 2px;
    }

    .kq-toggle-detail {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        margin-top: 6px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .kq-toggle-detail:hover {
        background: #1d4ed8;
        color: #fff;
        border-color: #1d4ed8;
    }
    .kq-toggle-detail i { font-size: 0.65rem; }

    /* Score cell */
    .kq-score-cell {
        position: relative;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
    }
    .kq-score-cell strong {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }
    .kq-score-cell small {
        margin-top: 4px;
        font-size: 0.68rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .kq-info-btn {
        margin-top: 4px;
        width: 22px; height: 22px;
        border-radius: 50%;
        background: #eff6ff;
        border: 0;
        color: #1d4ed8;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .kq-info-btn:hover { background: #1d4ed8; color: #fff; }

    /* Final score */
    .kq-final-score {
        font-size: 1.5rem;
        font-weight: 900;
        color: #1d4ed8;
        line-height: 1;
    }
    .kq-final-score.is-pass { color: #16a34a; }
    .kq-final-score.is-fail { color: #dc2626; }

    .kq-locked-mark {
        margin-top: 6px;
        font-size: 0.72rem;
        color: #16a34a;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Status pill */
    .kq-status-stack {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .kq-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .kq-pill.is-success   { background: #dcfce7; color: #16a34a; }
    .kq-pill.is-secondary { background: #f1f5f9; color: #64748b; }
    .kq-pill.is-warning   { background: #fef3c7; color: #c2410c; }
    .kq-pill i { font-size: 0.62rem; }

    .kq-pill-sub {
        font-size: 0.68rem;
        color: #64748b;
        font-style: italic;
    }

    .kq-mini-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px;
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 999px;
        border: 1px solid;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .kq-mini-btn.is-danger {
        background: #fff;
        border-color: #dc2626;
        color: #dc2626;
    }
    .kq-mini-btn.is-danger:hover {
        background: #dc2626;
        color: #fff;
    }

    /* Actions */
    .kq-actions { display: flex; flex-direction: column; gap: 8px; min-width: 220px; }

    .kq-chot-row {
        display: flex;
        gap: 4px;
    }
    .kq-chot-row .form-control {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.82rem;
    }
    .kq-chot-row .form-control:focus {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
    }

    .kq-chot-btn {
        flex-shrink: 0;
        width: 36px; height: 36px;
        border: 0;
        border-radius: 8px;
        color: #fff;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }
    .kq-chot-btn.is-lock {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    }
    .kq-chot-btn.is-update {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    }
    .kq-chot-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    .kq-status-row {
        display: flex;
        gap: 4px;
        align-items: center;
    }
    .kq-status-row .form-select {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.78rem;
        padding: 4px 24px 4px 10px;
    }
    .kq-status-row .form-select:focus {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
    }

    .kq-comment-btn {
        flex-shrink: 0;
        width: 32px; height: 32px;
        border-radius: 8px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        cursor: pointer;
        font-size: 0.78rem;
        transition: all 0.2s ease;
    }
    .kq-comment-btn:hover {
        background: #1d4ed8;
        color: #fff;
        border-color: #1d4ed8;
    }

    /* ===== Detail collapse ===== */
    .kq-detail-body {
        padding: 18px 22px;
        background: #f8fafc;
        border-top: 2px dashed #bfdbfe;
    }

    .kq-detail-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        height: 100%;
    }

    .kq-detail-head {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-bottom: 1px solid #bfdbfe;
        font-size: 0.85rem;
    }
    .kq-detail-head i { color: #1d4ed8; }
    .kq-detail-head strong { color: #0f172a; font-weight: 800; }

    .kq-detail-rows { padding: 12px 16px; }

    .kq-detail-row-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 0;
        font-size: 0.85rem;
        border-bottom: 1px dashed #f1f5f9;
    }
    .kq-detail-row-item:last-child { border-bottom: 0; }
    .kq-detail-row-item span { color: #64748b; font-weight: 600; }
    .kq-detail-row-item strong { color: #0f172a; font-weight: 800; }

    .kq-detail-row-final {
        margin-top: 6px;
        padding-top: 10px !important;
        border-top: 2px solid #bfdbfe !important;
        border-bottom: 0 !important;
    }
    .kq-detail-row-final strong {
        font-size: 1.2rem;
        color: #1d4ed8 !important;
    }

    /* Exam table */
    .kq-exam-table-wrap { padding: 0; }
    .kq-exam-table { font-size: 0.82rem; margin: 0; }
    .kq-exam-table thead {
        background: #f8fafc;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.4px;
    }
    .kq-exam-table th { padding: 8px 10px; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
    .kq-exam-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }
    .kq-exam-table tfoot td { background: #f8fafc; padding: 8px 10px; font-weight: 700; }
    .kq-exam-final {
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%) !important;
        color: #fff;
    }
    .kq-exam-final td {
        background: transparent !important;
        color: #fff !important;
        font-weight: 800 !important;
        font-size: 0.95rem;
    }

    /* Empty */
    .kq-empty {
        padding: 30px 20px;
        text-align: center;
    }
    .kq-empty i {
        font-size: 2.4rem;
        color: #cbd5e1;
        display: block;
        margin-bottom: 12px;
    }
    .kq-empty p {
        color: #94a3b8;
        font-size: 0.88rem;
        margin: 0;
    }

    /* Soft bg */
    .bg-secondary-soft { background: #f1f5f9; color: #64748b; }
    .bg-primary-soft { background: rgba(29, 78, 216, 0.08); }
    .bg-success-soft { background: rgba(22, 163, 74, 0.1); }
    .bg-info-soft { background: rgba(8, 145, 178, 0.1); }
    .bg-danger-soft { background: rgba(220, 38, 38, 0.1); }

    .min-w-0 { min-width: 0; }
    .flex-1 { flex: 1; }

    @media (max-width: 991.98px) {
        .kq-actions { min-width: 200px; }
    }

    @media (max-width: 720px) {
        .kq-table thead th { font-size: 0.65rem; padding: 10px 6px; }
        .kq-table tbody td { padding: 10px 6px; }
        .kq-final-score { font-size: 1.2rem; }
    }
</style>
@endsection
