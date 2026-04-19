@extends('layouts.app', ['title' => 'Quản lý kết quả học tập'])

@section('content')
<div class="container-fluid">
    {{-- Breadcrumb --}}
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-white p-3 rounded-4 shadow-xs border">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}" class="text-decoration-none"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc') }}" class="text-decoration-none">Lộ trình dạy</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc.show', $phanCong->id) }}" class="text-decoration-none">Phiên điều hành</a></li>
                    <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">Quản lý kết quả</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Header --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-md me-4" style="width: 64px; height: 64px; font-size: 1.5rem;">
                    <i class="fas fa-poll-h"></i>
                </div>
                <div>
                    <h2 class="fw-extrabold mb-1 text-dark letter-spacing-tight">Bảng kết quả học tập</h2>
                    <div class="d-flex align-items-center gap-2 flex-wrap text-muted">
                        <span class="small">Khóa: <span class="fw-bold text-primary">{{ $khoaHoc->ten_khoa_hoc }}</span></span>
                        <span class="text-silver">|</span>
                        <span class="small">Module: <span class="fw-bold text-dark">{{ $phanCong->moduleHoc->ten_module }}</span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm" id="btn-refresh-all">
                <i class="fas fa-sync-alt me-2"></i> TÍNH TOÁN LẠI TOÀN BỘ
            </button>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card vip-card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="results-table">
                <thead class="bg-light border-0">
                    <tr>
                        <th class="ps-4 py-3 border-0 smaller text-muted text-uppercase">Học viên</th>
                        <th class="py-3 border-0 smaller text-muted text-uppercase text-center" title="Điểm danh quy đổi thang 10">Điểm quá trình (A)</th>
                        <th class="py-3 border-0 smaller text-muted text-uppercase text-center" title="Trung bình cộng bài nhỏ và bài lớn">Điểm thi (B)</th>
                        <th class="py-3 border-0 smaller text-muted text-uppercase text-center" title="(A * Trọng số A) + (B * Trọng số B)">Tổng kết Module</th>
                        <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Trạng thái chốt</th>
                        <th class="pe-4 py-3 border-0 smaller text-muted text-uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentResults as $data)
                        @php
                            $student = $data['student'];
                            $moduleResult = $data['module_result'] ?? null;
                            $breakdown = $data['breakdown'];
                            $summary = $breakdown['summary'];
                        @endphp
                        <tr class="student-row" data-student-id="{{ $student->ma_nguoi_dung }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary-soft text-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $student->ho_ten }}</div>
                                        <div class="smaller text-muted">{{ $student->email }}</div>
                                    </div>
                                </div>
                                <button class="btn btn-link btn-xs text-primary p-0 mt-1 btn-toggle-details" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $student->ma_nguoi_dung }}">
                                    <i class="fas fa-chevron-down me-1"></i> Xem chi tiết bảng điểm
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">{{ number_format($summary['process_score'] ?: 0, 2) }}</div>
                                <div class="smaller text-muted">TS: {{ $breakdown['weights']['attendance'] }}%</div>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">{{ number_format($summary['module_exam_score'] ?: 0, 2) }}</div>
                                <div class="smaller text-muted">TS: {{ $breakdown['weights']['assessment'] }}%</div>
                                @if($summary['avg_small_exam_score'] !== null || $summary['large_exam_score'] !== null)
                                    <button class="btn btn-xs btn-outline-info rounded-pill py-0 px-2 mt-1" 
                                            data-bs-toggle="tooltip" 
                                            title="TB bài nhỏ: {{ $summary['avg_small_exam_score'] ?: '--' }} | Bài lớn: {{ $summary['large_exam_score'] ?: '--' }}">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="fw-extrabold text-primary fs-5">{{ number_format($summary['final_score'] ?: 0, 2) }}</div>
                                @if($moduleResult && $moduleResult->da_chot)
                                    <div class="smaller text-success"><i class="fas fa-check-double me-1"></i>Đã chốt: {{ number_format((float) $moduleResult->diem_giang_vien_chot, 2) }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($moduleResult)
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge bg-{{ $moduleResult->da_chot ? 'success' : 'secondary' }}-soft text-{{ $moduleResult->da_chot ? 'success' : 'secondary' }} rounded-pill px-3">
                                            {{ $moduleResult->da_chot ? 'ĐÃ CHỐT ĐIỂM' : 'CHƯA CHỐT' }}
                                        </span>
                                        @if($moduleResult->da_chot)
                                            <span class="smaller text-muted italic">Duyệt: {{ $moduleResult->trang_thai_duyet_label }}</span>
                                            
                                            @if($moduleResult->trang_thai_duyet !== \App\Models\KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET)
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0 mt-1 btn-mo-chot" 
                                                        data-result-id="{{ $moduleResult->id }}"
                                                        data-student-name="{{ $student->ho_ten }}">
                                                    <i class="fas fa-unlock me-1"></i> Mở chốt
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary">Chưa khởi tạo</span>
                                @endif
                            </td>
                            <td class="pe-4">
                                <div class="d-flex flex-column gap-2">
                                    <form method="POST" action="{{ route('giang-vien.khoa-hoc.ket-qua.chot', $phanCong->id) }}">
                                        @csrf
                                        <input type="hidden" name="hoc_vien_id" value="{{ $student->ma_nguoi_dung }}">
                                        <div class="input-group input-group-sm mb-1">
                                            <input type="text" name="ghi_chu_chot" class="form-control" placeholder="Ghi chú chốt..." value="{{ $moduleResult?->ghi_chu_chot }}">
                                            <button type="submit" class="btn {{ $moduleResult?->da_chot ? 'btn-warning' : 'btn-success' }}" 
                                                    onclick="return confirm('{{ $moduleResult?->da_chot ? 'Cập nhật lại điểm đã chốt và gửi admin duyệt lại?' : 'Xác nhận chốt bảng điểm này và gửi Admin phê duyệt?' }}')">
                                                <i class="fas {{ $moduleResult?->da_chot ? 'fa-sync-alt' : 'fa-lock' }}"></i>
                                            </button>
                                        </div>
                                    </form>
                                    
                                    @if($moduleResult)
                                        <div class="d-flex gap-1">
                                            <select class="form-select form-select-sm rounded-pill select-status" data-result-id="{{ $moduleResult->id }}" style="max-width: 110px;">
                                                <option value="dang_hoc" {{ $moduleResult->trang_thai === 'dang_hoc' ? 'selected' : '' }}>Đang học</option>
                                                <option value="hoan_thanh" {{ $moduleResult->trang_thai === 'hoan_thanh' ? 'selected' : '' }}>Hoàn thành</option>
                                                <option value="dat" {{ $moduleResult->trang_thai === 'dat' ? 'selected' : '' }}>Đạt</option>
                                                <option value="khong_dat" {{ $moduleResult->trang_thai === 'khong_dat' ? 'selected' : '' }}>Không đạt</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill btn-save-comment-row" data-result-id="{{ $moduleResult->id }}" title="Lưu nhận xét">
                                                <i class="fas fa-comment-dots"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" class="input-comment-hidden" id="comment-{{ $moduleResult->id }}" value="{{ $moduleResult->nhan_xet_giang_vien }}">
                                    @endif
                                </div>
                            </td>
                        </tr>
                        {{-- Collapsible Details Row --}}
                        <tr class="collapse-row">
                            <td colspan="6" class="p-0 border-0">
                                <div class="collapse bg-light-subtle" id="details-{{ $student->ma_nguoi_dung }}">
                                    <div class="p-4 border-bottom">
                                        <div class="row">
                                            {{-- Attendance breakdown --}}
                                            <div class="col-md-4 border-end">
                                                <h6 class="fw-bold smaller text-muted text-uppercase mb-3"><i class="fas fa-calendar-check me-2"></i>Chi tiết chuyên cần</h6>
                                                <div class="p-3 bg-white rounded-3 border shadow-xs">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Tổng số buổi:</span>
                                                        <span class="fw-bold">{{ $breakdown['attendance']['tong_so_buoi'] }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Số buổi tham gia:</span>
                                                        <span class="fw-bold text-success">{{ $breakdown['attendance']['so_buoi_tham_du'] }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Tỷ lệ hiện diện:</span>
                                                        <span class="fw-bold text-primary">{{ $breakdown['attendance']['ty_le_tham_du'] }}%</span>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between">
                                                        <span class="fw-bold">Điểm quá trình (thang 10):</span>
                                                        <span class="fw-bold text-danger fs-5">{{ number_format($breakdown['attendance']['diem_diem_danh'] ?: 0, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Exams breakdown --}}
                                            <div class="col-md-8 ps-4">
                                                <h6 class="fw-bold smaller text-muted text-uppercase mb-3"><i class="fas fa-file-invoice me-2"></i>Điểm các bài kiểm tra</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered bg-white mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Tên bài kiểm tra</th>
                                                                <th class="text-center">Loại</th>
                                                                <th class="text-center">Điểm số</th>
                                                                <th class="text-center">Trạng thái</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($breakdown['exam_results'] as $er)
                                                                <tr>
                                                                    <td>{{ $er['tieu_de'] }}</td>
                                                                    <td class="text-center small">
                                                                        @if($er['loai'] === 'cuoi_module')
                                                                            <span class="badge bg-danger">Bài lớn</span>
                                                                        @else
                                                                            <span class="badge bg-info">Bài nhỏ</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center fw-bold text-primary">{{ number_format($er['diem'], 2) }}</td>
                                                                    <td class="text-center small">{{ $er['trang_thai'] }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot class="bg-light fw-bold">
                                                            <tr>
                                                                <td colspan="2" class="text-end text-muted small">Trung bình bài nhỏ:</td>
                                                                <td class="text-center">{{ number_format($summary['avg_small_exam_score'] ?: 0, 2) }}</td>
                                                                <td></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2" class="text-end text-muted small">Điểm bài lớn:</td>
                                                                <td class="text-center">{{ number_format($summary['large_exam_score'] ?: 0, 2) }}</td>
                                                                <td></td>
                                                            </tr>
                                                            <tr class="table-primary text-primary">
                                                                <td colspan="2" class="text-end">TRUNG BÌNH KIỂM TRẠ (B):</td>
                                                                <td class="text-center fs-6">{{ number_format($summary['module_exam_score'] ?: 0, 2) }}</td>
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Không có học viên nào trong khóa học này.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
{{-- Modal Nhận xét --}}
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Nhận xét giảng viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-result-id">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nội dung nhận xét</label>
                    <textarea class="form-control rounded-3" id="modal-comment-text" rows="4" placeholder="Nhập nhận xét về quá trình học tập của học viên..."></textarea>
                </div>
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
                <h5 class="fw-bold">Mở khóa chốt điểm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="result_id" id="unlock-result-id">
                <div class="alert alert-info smaller">
                    <i class="fas fa-info-circle me-2"></i> Mở khóa chốt điểm cho học viên <strong id="unlock-student-name"></strong>. Sau khi mở khóa, giảng viên có thể cập nhật lại điểm và chốt lại để gửi admin duyệt.
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Lý do mở chốt <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-3" name="ly_do" id="unlock-reason" rows="3" placeholder="Nhập lý do cần mở chốt điểm..." required></textarea>
                </div>
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
    
    // Mở modal mở chốt
    $('.btn-mo-chot').on('click', function() {
        const resultId = $(this).data('result-id');
        const studentName = $(this).data('student-name');
        
        $('#unlock-result-id').val(resultId);
        $('#unlock-student-name').text(studentName);
        $('#unlock-reason').val('');
        unlockModal.show();
    });

    // Mở modal nhận xét
    $('.btn-save-comment-row').on('click', function() {
        const resultId = $(this).data('result-id');
        const currentComment = $(`#comment-${resultId}`).val();
        
        $('#modal-result-id').val(resultId);
        $('#modal-comment-text').val(currentComment);
        commentModal.show();
    });

    // Lưu nhận xét từ modal
    $('#btn-modal-save-comment').on('click', function() {
        const $btn = $(this);
        const resultId = $('#modal-result-id').val();
        const comment = $('#modal-comment-text').val();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Đang lưu...');
        
        updateResult(resultId, { nhan_xet: comment }, function() {
            $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Lưu nhận xét');
            $(`#comment-${resultId}`).val(comment); // Cập nhật input hidden
            commentModal.hide();
            
            // Thông báo (tùy chọn)
            alert('Đã lưu nhận xét thành công.');
        });
    });

    // Lưu trạng thái khi thay đổi
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
            data: {
                _token: '{{ csrf_token() }}',
                result_id: resultId,
                ...data
            },
            success: function(response) {
                if (callback) callback();
            },
            error: function(xhr) {
                alert('Có lỗi xảy ra: ' + xhr.responseJSON.message);
                if (callback) callback();
            }
        });
    }

    $('#btn-refresh-all').on('click', function() {
        if (confirm('Hệ thống sẽ tính toán lại toàn bộ điểm dựa trên dữ liệu bài thi mới nhất. Tiếp tục?')) {
            location.reload();
        }
    });
});
</script>
@endpush

<style>
    .bg-secondary-soft { background-color: #f1f5f9; color: #64748b; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .letter-spacing-tight { letter-spacing: -0.02em; }
    .italic { font-style: italic; }
</style>
@endsection
