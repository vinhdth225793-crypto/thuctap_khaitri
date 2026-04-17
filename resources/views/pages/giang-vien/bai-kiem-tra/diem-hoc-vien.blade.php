@extends('layouts.app', ['title' => 'Danh sách học viên làm bài'])

@push('styles')
<style>
    .attempt-page {
        --attempt-ink: #172033;
        --attempt-muted: #64748b;
        --attempt-line: rgba(15, 23, 42, 0.08);
        --attempt-blue: #2563eb;
        --attempt-green: #0f766e;
        color: var(--attempt-ink);
    }

    .attempt-hero {
        background:
            radial-gradient(circle at 12% 15%, rgba(125, 211, 252, 0.28), transparent 28%),
            radial-gradient(circle at 88% 18%, rgba(45, 212, 191, 0.20), transparent 24%),
            linear-gradient(135deg, #10233f 0%, #1d4ed8 58%, #0f766e 100%);
        border-radius: 26px;
        color: #fff;
        overflow: hidden;
        padding: 28px;
        position: relative;
    }

    .attempt-hero::after {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        content: "";
        height: 170px;
        position: absolute;
        right: -44px;
        top: -56px;
        width: 170px;
    }

    .attempt-hero .text-muted {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    .attempt-hero-content {
        position: relative;
        z-index: 1;
    }

    .attempt-pill {
        align-items: center;
        background: rgba(255, 255, 255, 0.13);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.76rem;
        font-weight: 800;
        gap: 8px;
        letter-spacing: 0.03em;
        padding: 7px 12px;
        text-transform: uppercase;
    }

    .attempt-card {
        background: #fff;
        border: 1px solid var(--attempt-line);
        border-radius: 20px;
        box-shadow: 0 16px 38px rgba(15, 23, 42, 0.07);
        height: 100%;
    }

    .attempt-info-box {
        background: linear-gradient(135deg, #f8fafc, #eef7ff);
        border: 1px solid var(--attempt-line);
        border-radius: 18px;
        padding: 14px 16px;
    }

    .attempt-table-card {
        border: 1px solid var(--attempt-line);
        border-radius: 22px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .attempt-student-avatar {
        align-items: center;
        background: #e0f2fe;
        border-radius: 16px;
        color: #0369a1;
        display: inline-flex;
        flex: 0 0 42px;
        font-weight: 800;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .attempt-empty-state {
        background:
            radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.12), transparent 32%),
            #fff;
        border: 1px dashed rgba(15, 23, 42, 0.18);
        border-radius: 22px;
        padding: 54px 24px;
    }
</style>
@endpush

@section('content')
@php
    $examTypeMap = [
        'module' => ['label' => 'Theo module', 'class' => 'info'],
        'buoi_hoc' => ['label' => 'Theo buổi học', 'class' => 'primary'],
        'cuoi_khoa' => ['label' => 'Cuối khóa', 'class' => 'dark'],
    ];

    $gradingStatusMap = [
        'chua_cham' => ['label' => 'Chưa chấm', 'class' => 'secondary'],
        'cho_cham' => ['label' => 'Chờ chấm', 'class' => 'warning'],
        'da_cham' => ['label' => 'Đã chấm', 'class' => 'success'],
    ];

    $examType = $examTypeMap[$baiKiemTra->loai_bai_kiem_tra] ?? ['label' => 'Khác', 'class' => 'secondary'];
@endphp

<div class="container-fluid attempt-page">
    <div class="attempt-hero mb-4">
        <div class="attempt-hero-content">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <div class="attempt-pill mb-3">
                        <i class="fas fa-users"></i>
                        Danh sách học viên làm bài
                    </div>
                    <h2 class="fw-bold mb-2">{{ $baiKiemTra->tieu_de }}</h2>
                    <p class="text-muted mb-0">
                        Trang chi tiết các lượt làm bài của học viên, mở riêng từ thẻ bài kiểm tra để phần tổng quan không bị rối.
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('giang-vien.diem-kiem-tra.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Bảng điểm
                    </a>
                    <a href="{{ route('giang-vien.bai-kiem-tra.edit', $baiKiemTra->id) }}" class="btn btn-outline-light">
                        <i class="fas fa-pen-to-square me-1"></i> Xem đề
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="attempt-card card border-0">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Học viên</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="display-6 fw-bold mb-0">{{ $stats['hoc_vien'] }}</div>
                        <i class="fas fa-user-graduate text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="attempt-card card border-0">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Lượt nộp</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="display-6 fw-bold mb-0">{{ $stats['tong_luot_nop'] }}</div>
                        <i class="fas fa-file-circle-check text-info fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="attempt-card card border-0">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Đã chấm</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="display-6 fw-bold mb-0">{{ $stats['da_cham'] }}</div>
                        <i class="fas fa-circle-check text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="attempt-card card border-0">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Điểm trung bình</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="display-6 fw-bold mb-0">{{ number_format((float) $stats['diem_trung_binh'], 2) }}</div>
                        <i class="fas fa-chart-line text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="attempt-info-box mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="small text-muted fw-bold text-uppercase">Khóa học</div>
                <div class="fw-bold">{{ $baiKiemTra->khoaHoc?->ma_khoa_hoc ?? 'KH' }} - {{ $baiKiemTra->khoaHoc?->ten_khoa_hoc ?? 'Không rõ khóa học' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted fw-bold text-uppercase">Phạm vi</div>
                <div class="fw-bold">
                    <span class="badge text-bg-{{ $examType['class'] }}">{{ $examType['label'] }}</span>
                    <span class="ms-2">{{ $baiKiemTra->content_mode_label }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted fw-bold text-uppercase">Module / buổi học</div>
                <div class="fw-bold">
                    @if($baiKiemTra->lichHoc)
                        Buổi {{ $baiKiemTra->lichHoc->buoi_so }} - {{ optional($baiKiemTra->lichHoc->ngay_hoc)->format('d/m/Y') }}
                    @elseif($baiKiemTra->moduleHoc)
                        {{ $baiKiemTra->moduleHoc->ma_module }} - {{ $baiKiemTra->moduleHoc->ten_module }}
                    @else
                        Đề tổng kết toàn khóa
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="attempt-card card border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('giang-vien.diem-kiem-tra.hoc-vien', $baiKiemTra->id) }}" class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label fw-bold">Tìm học viên</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Tên học viên hoặc email...">
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-bold">Trạng thái chấm</label>
                    <select name="trang_thai_cham" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($gradingStatusMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['trang_thai_cham'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="{{ route('giang-vien.diem-kiem-tra.hoc-vien', $baiKiemTra->id) }}" class="btn btn-outline-secondary" title="Đặt lại">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="attempt-table-card card border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0">Danh sách lượt làm bài</h5>
            <span class="text-muted small">{{ $baiLams->total() }} lượt làm phù hợp</span>
        </div>
        <div class="card-body p-0">
            @if($baiLams->isEmpty())
                <div class="attempt-empty-state text-center text-muted m-4">
                    <i class="fas fa-user-slash fa-3x mb-3 d-block opacity-25"></i>
                    <div class="fw-bold text-dark mb-2">Chưa có học viên phù hợp</div>
                    <div class="small mb-0">Hãy đổi bộ lọc hoặc chờ học viên nộp bài.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Học viên</th>
                                <th class="text-center">Lần làm</th>
                                <th class="text-center">Điểm</th>
                                <th>Trạng thái</th>
                                <th>Nộp lúc</th>
                                <th>Người chấm</th>
                                <th class="text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($baiLams as $baiLam)
                                @php
                                    $studentName = $baiLam->hocVien->ho_ten ?? 'Không rõ học viên';
                                    $initial = mb_substr($studentName, 0, 1);
                                    $gradingStatus = $gradingStatusMap[$baiLam->trang_thai_cham] ?? ['label' => 'Không rõ', 'class' => 'secondary'];
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="attempt-student-avatar">{{ $initial }}</div>
                                            <div>
                                                <div class="fw-bold">{{ $studentName }}</div>
                                                <div class="small text-muted">{{ $baiLam->hocVien->email ?? 'Chưa có email' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-light border">#{{ $baiLam->lan_lam_thu }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($baiLam->diem_so !== null)
                                            <div class="fw-bold text-success fs-5">{{ number_format((float) $baiLam->diem_so, 2) }}</div>
                                            <div class="small text-muted">/ {{ number_format((float) ($baiKiemTra->tong_diem ?? 10), 2) }}</div>
                                        @else
                                            <span class="badge text-bg-warning">Chờ chấm</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $gradingStatus['class'] }}">{{ $gradingStatus['label'] }}</span>
                                        @if($baiKiemTra->co_giam_sat)
                                            <div class="small mt-2">
                                                <span class="badge bg-{{ $baiLam->trang_thai_giam_sat_color }}">{{ $baiLam->trang_thai_giam_sat_label }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ optional($baiLam->nop_luc)->format('d/m/Y H:i') ?? 'Chưa nộp' }}</div>
                                        <div class="small text-muted">Bắt đầu: {{ optional($baiLam->bat_dau_luc)->format('d/m/Y H:i') ?? 'Không rõ' }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $baiLam->nguoiCham->ho_ten ?? 'Chưa có' }}</div>
                                        <div class="small text-muted">{{ optional($baiLam->cham_luc)->format('d/m/Y H:i') ?? 'Chưa chấm' }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('giang-vien.cham-diem.show', $baiLam->id) }}" class="btn btn-sm {{ $baiLam->need_manual_grading ? 'btn-primary' : 'btn-outline-primary' }}">
                                            <i class="fas {{ $baiLam->need_manual_grading ? 'fa-marker' : 'fa-eye' }} me-1"></i>
                                            {{ $baiLam->need_manual_grading ? 'Chấm bài' : 'Xem bài' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($baiLams->hasPages())
            <div class="card-footer bg-white">
                {{ $baiLams->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
