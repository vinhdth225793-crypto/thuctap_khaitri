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
                        <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Chuyên cần</th>
                        <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Điểm thi</th>
                        <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Tổng kết</th>
                        <th class="py-3 border-0 smaller text-muted text-uppercase">Trạng thái (Giảng viên chốt)</th>
                        <th class="pe-4 py-3 border-0 smaller text-muted text-uppercase">Nhận xét & Lưu ý</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentResults as $data)
                        @php
                            $student = $data['student'];
                            $cResult = $data['course_result'];
                            $moduleResult = $data['module_result'] ?? null;
                            $mResults = $data['module_results'];
                            $eResults = $data['exam_results'];
                            $attemptsByExam = $data['attempts_by_exam'] ?? collect();
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
                                    <i class="fas fa-chevron-down me-1"></i> Xem chi tiết bài thi
                                </button>
                            </td>
                            <td class="text-center">
                                @if($moduleResult)
                                    <div class="fw-bold">{{ number_format($moduleResult->diem_diem_danh ?: 0, 1) }}</div>
                                    <div class="smaller text-muted">{{ $moduleResult->ty_le_tham_du }}% ({{ $moduleResult->so_buoi_tham_du }}/{{ $moduleResult->tong_so_buoi }})</div>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($moduleResult)
                                    <div class="fw-bold">{{ number_format($moduleResult->diem_kiem_tra ?: 0, 1) }}</div>
                                    <div class="smaller text-muted">{{ $moduleResult->so_bai_kiem_tra_hoan_thanh }} bai</div>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($moduleResult)
                                    <div class="fw-extrabold text-primary fs-5">{{ number_format($moduleResult->diem_tong_ket ?: 0, 2) }}</div>
                                    @if($moduleResult->diem_giang_vien_chot !== null)
                                        <div class="smaller text-success">Da chot: {{ number_format((float) $moduleResult->diem_giang_vien_chot, 2) }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($moduleResult)
                                    <div class="d-grid gap-1">
                                        <span class="badge bg-{{ $moduleResult->trang_thai_chot === 'da_chot' ? 'success' : 'secondary' }}-soft text-{{ $moduleResult->trang_thai_chot === 'da_chot' ? 'success' : 'secondary' }}">
                                            {{ $moduleResult->trang_thai_chot_label }}
                                        </span>
                                        <span class="badge bg-light text-dark border">{{ $moduleResult->trang_thai_duyet_label }}</span>
                                        <select class="form-select form-select-sm rounded-pill select-status" data-result-id="{{ $moduleResult->id }}">
                                            <option value="dang_hoc" {{ $moduleResult->trang_thai === 'dang_hoc' ? 'selected' : '' }}>Dang hoc</option>
                                            <option value="hoan_thanh" {{ $moduleResult->trang_thai === 'hoan_thanh' ? 'selected' : '' }}>Hoan thanh</option>
                                            <option value="dat" {{ $moduleResult->trang_thai === 'dat' ? 'selected' : '' }}>Dat</option>
                                            <option value="khong_dat" {{ $moduleResult->trang_thai === 'khong_dat' ? 'selected' : '' }}>Chua dat</option>
                                        </select>
                                    </div>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary">Chưa khởi tạo</span>
                                @endif
                            </td>
                            <td class="pe-4">
                                @if($moduleResult)
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control rounded-start-pill input-comment"
                                               value="{{ $moduleResult->nhan_xet_giang_vien }}"
                                               placeholder="Nhập nhận xét..."
                                               data-result-id="{{ $moduleResult->id }}">
                                        <button class="btn btn-primary rounded-end-pill btn-save-comment" data-result-id="{{ $moduleResult->id }}">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('giang-vien.khoa-hoc.ket-qua.chot', $phanCong->id) }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="hoc_vien_id" value="{{ $student->ma_nguoi_dung }}">
                                    <input type="text" name="ghi_chu_chot" class="form-control form-control-sm mb-2" placeholder="Ghi chu chot diem..." value="{{ $moduleResult?->ghi_chu_chot }}">
                                    <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('Chot diem module nay va gui admin duyet?')">
                                        <i class="fas fa-lock me-1"></i>{{ $moduleResult?->trang_thai_chot === 'da_chot' ? 'Chot lai diem module' : 'Chot diem module' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        {{-- Collapsible Details Row --}}
                        <tr class="collapse-row">
                            <td colspan="6" class="p-0 border-0">
                                <div class="collapse bg-light-subtle" id="details-{{ $student->ma_nguoi_dung }}">
                                    <div class="p-4 border-bottom">
                                        <div class="row">
                                            {{-- Modules breakdown --}}
                                            <div class="col-md-6 border-end">
                                                <h6 class="fw-bold smaller text-muted text-uppercase mb-3">Kết quả theo Module</h6>
                                                <div class="list-group list-group-flush bg-transparent">
                                                    @forelse($mResults as $mr)
                                                        <div class="list-group-item bg-transparent px-0 border-0 d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="small fw-bold text-dark">{{ $mr->moduleHoc->ten_module ?? 'Module' }}</div>
                                                                <div class="smaller text-muted">TB: {{ number_format($mr->diem_tong_ket ?: 0, 2) }}</div>
                                                            </div>
                                                            <span class="badge bg-{{ $mr->trang_thai === 'hoan_thanh' ? 'success' : 'info' }}-soft text-{{ $mr->trang_thai === 'hoan_thanh' ? 'success' : 'info' }} rounded-pill">
                                                                {{ $mr->trang_thai === 'hoan_thanh' ? 'Hoàn thành' : 'Đang học' }}
                                                            </span>
                                                        </div>
                                                    @empty
                                                        <div class="smaller text-muted italic">Chưa có dữ liệu module.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                            {{-- Exams breakdown --}}
                                            <div class="col-md-6 ps-4">
                                                <h6 class="fw-bold smaller text-muted text-uppercase mb-3">Điểm các bài thi</h6>
                                                <div class="list-group list-group-flush bg-transparent">
                                                    @forelse($eResults as $er)
                                                        <div class="list-group-item bg-transparent px-0 border-0 d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="small fw-bold text-dark">{{ $er->baiKiemTra->tieu_de }}</div>
                                                                <div class="smaller text-muted">Phạm vi: {{ $er->baiKiemTra->pham_vi_label }}</div>
                                                                @if($er->attempt_strategy_used)
                                                                    <div class="smaller text-primary">Cach tinh: {{ $er->attempt_strategy_used }}</div>
                                                                @endif
                                                                @foreach(($attemptsByExam[$er->bai_kiem_tra_id] ?? collect()) as $attempt)
                                                                    <div class="smaller text-muted">
                                                                        Lan {{ $attempt->lan_lam_thu }}:
                                                                        {{ $attempt->diem_so !== null ? number_format((float) $attempt->diem_so, 2) : 'cho cham' }}
                                                                        @if(in_array((int) $attempt->id, array_map('intval', $er->source_attempt_ids ?: []), true) || (int) $attempt->id === (int) $er->source_attempt_id)
                                                                            <span class="badge bg-primary-soft text-primary">chinh thuc</span>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="fw-bold text-primary">{{ number_format($er->diem_kiem_tra ?: 0, 2) }}</div>
                                                                <span class="smaller badge bg-{{ $er->trang_thai === 'dat' ? 'success' : 'danger' }}-soft text-{{ $er->trang_thai === 'dat' ? 'success' : 'danger' }}">
                                                                    {{ $er->trang_thai === 'dat' ? 'Đạt' : 'Trượt' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="smaller text-muted italic">Chưa có bài thi nào được hoàn thành.</div>
                                                    @endforelse
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
<script>
$(function() {
    // Lưu trạng thái khi thay đổi
    $('.select-status').on('change', function() {
        const $el = $(this);
        const resultId = $el.data('result-id');
        const status = $el.val();
        updateResult(resultId, { trang_thai: status });
    });

    // Lưu nhận xét khi nhấn nút
    $('.btn-save-comment').on('click', function() {
        const $btn = $(this);
        const resultId = $btn.data('result-id');
        const $input = $(`.input-comment[data-result-id="${resultId}"]`);
        const comment = $input.val();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        updateResult(resultId, { nhan_xet: comment }, function() {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i>');
        });
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
                // Có thể show toast thông báo thành công
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
            location.reload(); // Tạm thời reload để trigger refresh từ controller nếu cần, 
            // hoặc ta gọi một route refresh riêng.
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
