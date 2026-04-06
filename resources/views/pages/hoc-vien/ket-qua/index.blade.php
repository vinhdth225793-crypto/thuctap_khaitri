@extends('layouts.app', ['title' => 'Kết quả học tập chi tiết'])

@section('content')
<div class="container-fluid">
    {{-- Header & Stats --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-chart-line text-primary me-2"></i>
                Kết quả học tập chi tiết
            </h3>
            <p class="text-muted mb-0">Theo dõi điểm số, chuyên cần và đánh giá từng khóa học.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex gap-2">
                <div class="bg-white p-2 px-3 rounded-4 shadow-xs border text-center">
                    <div class="smaller text-muted text-uppercase fw-bold">Khóa học</div>
                    <div class="fw-bold text-dark fs-5">{{ $stats['tong_khoa_hoc'] }}</div>
                </div>
                <div class="bg-white p-2 px-3 rounded-4 shadow-xs border text-center">
                    <div class="smaller text-success text-uppercase fw-bold">Đã đạt</div>
                    <div class="fw-bold text-success fs-5">{{ $stats['khoa_hoc_dat'] }}</div>
                </div>
                <div class="bg-white p-2 px-3 rounded-4 shadow-xs border text-center">
                    <div class="smaller text-primary text-uppercase fw-bold">ĐTB Chung</div>
                    <div class="fw-bold text-primary fs-5">{{ number_format($stats['diem_trung_binh_chung'] ?: 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    @forelse($resultsByCourse as $courseId => $data)
        @php
            $khoaHoc = $data['khoa_hoc'];
            $courseResult = $data['course_result'];
            $moduleResults = $data['module_results'];
            $examResults = $data['exam_results'];
        @endphp

        <div class="card vip-card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-soft text-primary rounded-3 p-2 me-3">
                            <i class="fas fa-graduation-cap fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                            <div class="smaller text-muted mt-1">
                                Mã KH: <span class="fw-bold">{{ $khoaHoc->ma_khoa_hoc }}</span> | 
                                Hình thức đánh giá: <span class="fw-bold text-primary">{{ $khoaHoc->phuong_thuc_danh_gia_label }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        @if($courseResult)
                            <div class="text-end me-3">
                                <div class="smaller text-muted text-uppercase fw-bold">Tổng kết</div>
                                <div class="fw-extrabold text-primary fs-4">{{ number_format($courseResult->diem_tong_ket ?: 0, 2) }}</div>
                            </div>
                            <span class="badge bg-{{ $courseResult->trang_thai === 'dat' ? 'success' : ($courseResult->trang_thai === 'khong_dat' ? 'danger' : 'info') }}-soft text-{{ $courseResult->trang_thai === 'dat' ? 'success' : ($courseResult->trang_thai === 'khong_dat' ? 'danger' : 'info') }} border px-3 py-2 rounded-pill">
                                <i class="fas fa-{{ $courseResult->trang_thai === 'dat' ? 'check-circle' : ($courseResult->trang_thai === 'khong_dat' ? 'times-circle' : 'clock') }} me-1"></i>
                                {{ $courseResult->trang_thai === 'dat' ? 'ĐẠT' : ($courseResult->trang_thai === 'khong_dat' ? 'CHƯA ĐẠT' : 'ĐANG HỌC') }}
                            </span>
                        @else
                            <span class="badge bg-secondary-soft text-secondary border px-3 py-2 rounded-pill">CHƯA CÓ KẾT QUẢ</span>
                        @endif
                        <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $khoaHoc->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fas fa-info-circle me-1"></i> Chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    {{-- Left: Breakdown --}}
                    <div class="col-lg-4 border-end bg-light-subtle p-4">
                        <h6 class="fw-bold text-uppercase smaller text-muted mb-4">Phân tích thành phần</h6>
                        
                        <div class="d-flex flex-column gap-4">
                            {{-- Chuyên cần --}}
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold small text-dark"><i class="fas fa-user-check text-success me-2"></i>Chuyên cần</div>
                                    <span class="fw-bold text-dark">{{ $courseResult ? number_format($courseResult->diem_diem_danh ?: 0, 2) : '--' }}/10</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $courseResult ? ($courseResult->ty_le_tham_du ?: 0) : 0 }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="smaller text-muted">Tỉ lệ tham dự: {{ $courseResult ? number_format($courseResult->ty_le_tham_du ?: 0, 1) : 0 }}%</span>
                                    <span class="smaller text-muted">Mặt: {{ $courseResult ? $courseResult->so_buoi_tham_du : 0 }}/{{ $courseResult ? $courseResult->tong_so_buoi : 0 }}</span>
                                </div>
                            </div>

                            {{-- Bài kiểm tra --}}
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold small text-dark"><i class="fas fa-file-signature text-primary me-2"></i>Điểm kiểm tra</div>
                                    <span class="fw-bold text-dark">{{ $courseResult ? number_format($courseResult->diem_kiem_tra ?: 0, 2) : '--' }}/10</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $courseResult ? (($courseResult->diem_kiem_tra ?: 0) * 10) : 0 }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="smaller text-muted">Trọng số: {{ (float) $khoaHoc->ty_trong_kiem_tra }}%</span>
                                    <span class="smaller text-muted">Hoàn thành: {{ $courseResult ? $courseResult->so_bai_kiem_tra_hoan_thanh : 0 }} bài</span>
                                </div>
                            </div>

                            {{-- Nhận xét --}}
                            @if($courseResult && $courseResult->nhan_xet_giang_vien)
                                <div class="bg-white p-3 rounded-3 border border-dashed mt-2">
                                    <div class="fw-bold small text-dark mb-2"><i class="fas fa-comment-dots text-warning me-2"></i>Nhận xét từ hệ thống/giảng viên:</div>
                                    <p class="smaller text-muted mb-0 italic">"{{ $courseResult->nhan_xet_giang_vien }}"</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Details --}}
                    <div class="col-lg-8 p-0">
                        <div class="p-4 border-bottom bg-white">
                            <ul class="nav nav-pills nav-pills-soft gap-2" id="courseTab-{{ $khoaHoc->id }}" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-pill px-4" id="modules-tab-{{ $khoaHoc->id }}" data-bs-toggle="tab" data-bs-target="#modules-{{ $khoaHoc->id }}" type="button" role="tab">
                                        <i class="fas fa-layer-group me-2"></i>Theo Module
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill px-4" id="exams-tab-{{ $khoaHoc->id }}" data-bs-toggle="tab" data-bs-target="#exams-{{ $khoaHoc->id }}" type="button" role="tab">
                                        <i class="fas fa-pen-alt me-2"></i>Từng bài thi
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content" id="courseTabContent-{{ $khoaHoc->id }}">
                            {{-- Tab Module --}}
                            <div class="tab-pane fade show active" id="modules-{{ $khoaHoc->id }}" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light border-0">
                                            <tr>
                                                <th class="ps-4 py-3 border-0 smaller text-muted text-uppercase">Module</th>
                                                <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Chuyên cần</th>
                                                <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Điểm thi</th>
                                                <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Tổng kết</th>
                                                <th class="pe-4 py-3 border-0 smaller text-muted text-uppercase text-end">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($moduleResults as $mResult)
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="fw-bold text-dark">{{ $mResult->moduleHoc->ten_module ?? 'N/A' }}</div>
                                                        <div class="smaller text-muted">Mã: {{ $mResult->moduleHoc->ma_module ?? '---' }}</div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="fw-bold">{{ number_format($mResult->diem_diem_danh ?: 0, 1) }}</div>
                                                        <div class="smaller text-muted">{{ $mResult->ty_le_tham_du }}%</div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="fw-bold">{{ number_format($mResult->diem_kiem_tra ?: 0, 1) }}</div>
                                                        <div class="smaller text-muted">{{ $mResult->so_bai_kiem_tra_hoan_thanh }} bài</div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="fw-extrabold text-primary">{{ number_format($mResult->diem_tong_ket ?: 0, 2) }}</div>
                                                    </td>
                                                    <td class="pe-4 text-end">
                                                        <span class="badge bg-{{ $mResult->trang_thai === 'hoan_thanh' ? 'success' : 'info' }}-soft text-{{ $mResult->trang_thai === 'hoan_thanh' ? 'success' : 'info' }} rounded-pill">
                                                            {{ $mResult->trang_thai === 'hoan_thanh' ? 'HOÀN THÀNH' : 'ĐANG HỌC' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted italic">
                                                        Chưa có dữ liệu kết quả từng module.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab Exams --}}
                            <div class="tab-pane fade" id="exams-{{ $khoaHoc->id }}" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light border-0">
                                            <tr>
                                                <th class="ps-4 py-3 border-0 smaller text-muted text-uppercase">Bài kiểm tra</th>
                                                <th class="py-3 border-0 smaller text-muted text-uppercase">Module / Phạm vi</th>
                                                <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Điểm cao nhất</th>
                                                <th class="py-3 border-0 smaller text-muted text-uppercase text-center">Kết quả</th>
                                                <th class="pe-4 py-3 border-0 smaller text-muted text-uppercase text-end">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($examResults as $eResult)
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="fw-bold text-dark">{{ $eResult->baiKiemTra->tieu_de }}</div>
                                                        <div class="smaller text-muted">Cập nhật: {{ $eResult->cap_nhat_luc?->format('d/m/Y H:i') }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border">
                                                            {{ $eResult->moduleHoc ? $eResult->moduleHoc->ten_module : 'Cuối khóa' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="fw-extrabold text-dark fs-5">{{ number_format($eResult->diem_kiem_tra ?: 0, 2) }}</div>
                                                        <div class="smaller text-muted">Lần thứ {{ $eResult->chi_tiet['lan_lam_thu'] ?? 1 }}</div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $eResult->trang_thai === 'dat' ? 'success' : 'danger' }}-soft text-{{ $eResult->trang_thai === 'dat' ? 'success' : 'danger' }} rounded-pill">
                                                            {{ $eResult->trang_thai === 'dat' ? 'ĐẠT' : 'KHÔNG ĐẠT' }}
                                                        </span>
                                                    </td>
                                                    <td class="pe-4 text-end">
                                                        <a href="{{ route('hoc-vien.bai-kiem-tra.show', $eResult->bai_kiem_tra_id) }}" class="btn btn-icon-xs text-primary border rounded-circle">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted italic">
                                                        Chưa có dữ liệu bài kiểm tra nào được ghi nhận.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <div class="bg-white d-inline-block p-5 rounded-4 shadow-sm">
                <i class="fas fa-book-reader fa-4x text-light mb-4"></i>
                <h4 class="fw-bold text-dark">Bạn chưa tham gia khóa học nào</h4>
                <p class="text-muted mb-4">Hãy đăng ký tham gia các khóa học để bắt đầu lộ trình học tập của mình.</p>
                <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">
                    Khám phá khóa học
                </a>
            </div>
        </div>
    @endforelse
</div>

<style>
    .nav-pills-soft .nav-link {
        color: #64748b;
        font-weight: 600;
        font-size: 0.875rem;
        border: 1px solid transparent;
    }
    .nav-pills-soft .nav-link:hover {
        background-color: #f1f5f9;
    }
    .nav-pills-soft .nav-link.active {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border-color: rgba(13, 110, 253, 0.2);
    }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
</style>
@endsection
