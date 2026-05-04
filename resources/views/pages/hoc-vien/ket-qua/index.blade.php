@extends('layouts.app', ['title' => 'Kết quả học tập chi tiết'])

@section('content')
@php
    $tongKhoa  = $stats['tong_khoa_hoc']     ?? 0;
    $khoaDat   = $stats['khoa_hoc_dat']      ?? 0;
    $khoaTruot = $stats['khoa_hoc_truot']    ?? 0;
    $dtbChung  = $stats['diem_trung_binh_chung'] ?: 0;
@endphp

<div class="container-fluid admin-page-x kqhv-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome kqhv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-chart-line"></i></div>
        <div class="apx-welcome-text">
            <div class="kqhv-tag-row">
                <span class="kqhv-loai-badge">
                    <i class="fas fa-trophy"></i> KẾT QUẢ HỌC TẬP
                </span>
                <span class="kqhv-status-badge">
                    <i class="fas fa-graduation-cap"></i>
                    {{ $tongKhoa }} khóa học
                </span>
                @if($khoaDat > 0)
                    <span class="kqhv-success-badge">
                        <i class="fas fa-check-double"></i> {{ $khoaDat }} đã đạt
                    </span>
                @endif
            </div>
            <h4>Bảng điểm chi tiết của tôi</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $khoaDat }} đạt</span>
                <span class="kqhv-sep">·</span>
                <span><i class="fas fa-times-circle"></i> {{ $khoaTruot }} chưa đạt</span>
                <span class="kqhv-sep">·</span>
                <span><i class="fas fa-chart-line"></i> ĐTB chung: {{ number_format($dtbChung, 2) }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('hoc-vien.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn btn-light text-primary fw-bold shadow-sm kqhv-create-btn">
                <i class="fas fa-book-open me-1"></i> Khóa học của tôi
            </a>
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
                    <p>Bốn chỉ số nhanh cho biết trạng thái học tập của bạn.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $tongKhoa }}</strong> khóa học</span>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="aps-text">
                        <strong>{{ $tongKhoa }}</strong>
                        <small>Tổng khóa học</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text">
                        <strong>{{ $khoaDat }}</strong>
                        <small>Khóa đã đạt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-danger">
                    <div class="aps-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="aps-text">
                        <strong>{{ $khoaTruot }}</strong>
                        <small>Khóa chưa đạt</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="aps-text">
                        <strong>{{ number_format($dtbChung, 2) }}</strong>
                        <small>ĐTB chung</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== ② Chi tiết theo khóa học ========== --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-list"></i> Chi tiết theo từng khóa học</h2>
                    <p>Phân tích thành phần (chuyên cần / kiểm tra), điểm chốt và bảng điểm chi tiết.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $tongKhoa }}</strong> khóa</span>
            </div>
        </header>

        @forelse($resultsByCourse as $courseId => $data)
            @php
                $khoaHoc = $data['khoa_hoc'];
                $courseResult = $data['course_result'];
                $moduleResults = $data['module_results'];
                $examResults = $data['exam_results'];
                $displayFinalScore = $courseResult?->diem_giang_vien_chot ?? $courseResult?->diem_tong_ket;
                $officialApproval = data_get($courseResult?->calculation_metadata, 'course_approval_ticket');
                $hasOfficialApproval = $officialApproval && $courseResult?->trang_thai_duyet === \App\Models\KetQuaHocTap::TRANG_THAI_DUYET_DA_DUYET;

                $statusClass = match($courseResult?->trang_thai) {
                    'dat'        => 'is-success',
                    'khong_dat'  => 'is-danger',
                    null         => 'is-secondary',
                    default      => 'is-info',
                };
                $statusIcon = match($courseResult?->trang_thai) {
                    'dat'        => 'fa-check-circle',
                    'khong_dat'  => 'fa-times-circle',
                    null         => 'fa-circle-question',
                    default      => 'fa-clock',
                };
                $statusLabel = match($courseResult?->trang_thai) {
                    'dat'        => 'ĐẠT',
                    'khong_dat'  => 'CHƯA ĐẠT',
                    null         => 'CHƯA CÓ KQ',
                    default      => 'ĐANG HỌC',
                };

                $idColor = $khoaHoc->id % 6;
                $gradients = [
                    'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                    'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                    'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                    'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                    'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                    'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                ];
            @endphp

            <div class="kqhv-course-card">
                {{-- Course header --}}
                <div class="kqhv-course-head">
                    <div class="kqhv-course-info">
                        <div class="kqhv-course-icon" style="background: {{ $gradients[$idColor] }};">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h5 class="kqhv-course-title">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                            <div class="kqhv-course-meta">
                                <span><i class="fas fa-tag"></i> {{ $khoaHoc->ma_khoa_hoc }}</span>
                                <span class="kqhv-meta-sep">·</span>
                                <span><i class="fas fa-scale-balanced"></i> {{ $khoaHoc->phuong_thuc_danh_gia_label }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="kqhv-course-actions">
                        @if($courseResult)
                            <div class="kqhv-final-score">
                                <small>Tổng kết</small>
                                <strong>{{ number_format($displayFinalScore ?: 0, 2) }}</strong>
                                @if($courseResult->diem_giang_vien_chot !== null)
                                    <em><i class="fas fa-lock"></i> GV chốt</em>
                                @endif
                            </div>
                        @endif
                        <span class="kqhv-status-pill {{ $statusClass }}">
                            <i class="fas {{ $statusIcon }}"></i> {{ $statusLabel }}
                        </span>
                        <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $khoaHoc->id) }}" class="kqhv-detail-link">
                            <i class="fas fa-arrow-right"></i> Chi tiết
                        </a>
                    </div>
                </div>

                <div class="kqhv-course-body">
                    <div class="row g-0">
                        {{-- Left: Phân tích thành phần --}}
                        <div class="col-lg-4 kqhv-breakdown">
                            <h6 class="kqhv-block-title">
                                <i class="fas fa-puzzle-piece"></i> Phân tích thành phần
                            </h6>

                            {{-- Chuyên cần --}}
                            <div class="kqhv-component">
                                <div class="kqhv-comp-head">
                                    <span><i class="fas fa-user-check text-success"></i> Chuyên cần</span>
                                    <strong>{{ $courseResult ? number_format($courseResult->diem_diem_danh ?: 0, 2) : '--' }}/10</strong>
                                </div>
                                <div class="kqhv-progress">
                                    <div class="kqhv-progress-bar bg-success"
                                         style="width: {{ $courseResult ? ($courseResult->ty_le_tham_du ?: 0) : 0 }}%"></div>
                                </div>
                                <div class="kqhv-comp-foot">
                                    <span>Tỉ lệ: {{ $courseResult ? number_format($courseResult->ty_le_tham_du ?: 0, 1) : 0 }}%</span>
                                    <span>{{ $courseResult ? $courseResult->so_buoi_tham_du : 0 }}/{{ $courseResult ? $courseResult->tong_so_buoi : 0 }} buổi</span>
                                </div>
                            </div>

                            {{-- Bài kiểm tra --}}
                            <div class="kqhv-component">
                                <div class="kqhv-comp-head">
                                    <span><i class="fas fa-file-signature text-primary"></i> TB kiểm tra</span>
                                    <strong>{{ $courseResult ? number_format($courseResult->diem_kiem_tra ?: 0, 2) : '--' }}/10</strong>
                                </div>
                                <div class="kqhv-progress">
                                    <div class="kqhv-progress-bar bg-primary"
                                         style="width: {{ $courseResult ? (($courseResult->diem_kiem_tra ?: 0) * 10) : 0 }}%"></div>
                                </div>
                                <div class="kqhv-comp-foot">
                                    <span>Trọng số: {{ (float) $khoaHoc->ty_trong_kiem_tra }}%</span>
                                    <span>{{ $courseResult ? $courseResult->so_bai_kiem_tra_hoan_thanh : 0 }} bài</span>
                                </div>
                            </div>

                            {{-- Điểm GV chốt --}}
                            <div class="kqhv-component">
                                <div class="kqhv-comp-head">
                                    <span><i class="fas fa-lock text-warning"></i> Điểm GV chốt</span>
                                    <strong>{{ $courseResult?->diem_giang_vien_chot !== null ? number_format((float) $courseResult->diem_giang_vien_chot, 2) : '--' }}/10</strong>
                                </div>
                                <div class="kqhv-comp-foot kqhv-comp-foot-stack">
                                    <span><i class="far fa-circle"></i> {{ $courseResult?->trang_thai_chot_label ?? 'Chưa chốt' }}</span>
                                    <span><i class="far fa-circle"></i> {{ $courseResult?->trang_thai_duyet_label ?? 'Chưa gửi duyệt' }}</span>
                                </div>
                            </div>

                            @if($hasOfficialApproval)
                                <div class="kqhv-official">
                                    <div class="kqhv-official-title">
                                        <i class="fas fa-certificate"></i> Điểm xét duyệt chính thức
                                    </div>
                                    <div class="kqhv-official-row">
                                        <span>Admin đã chốt hồ sơ</span>
                                        <strong>{{ number_format((float) $displayFinalScore, 2) }}</strong>
                                    </div>
                                    <div class="kqhv-official-meta">
                                        Mode: {{ data_get($officialApproval, 'mode') }} ·
                                        {{ $courseResult->trang_thai === 'dat' ? 'Đạt' : ($courseResult->trang_thai === 'khong_dat' ? 'Không đạt' : 'Đang cập nhật') }}
                                    </div>
                                </div>
                            @endif

                            @if($courseResult && $courseResult->nhan_xet_giang_vien)
                                <div class="kqhv-comment">
                                    <div class="kqhv-comment-title">
                                        <i class="fas fa-comment-dots"></i> Nhận xét từ giảng viên
                                    </div>
                                    <p>"{{ $courseResult->nhan_xet_giang_vien }}"</p>
                                </div>
                            @endif
                        </div>

                        {{-- Right: Tabs Module / Exams --}}
                        <div class="col-lg-8 kqhv-detail">
                            <div class="kqhv-tabs">
                                <ul class="nav nav-pills gap-2" id="courseTab-{{ $khoaHoc->id }}" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="modules-tab-{{ $khoaHoc->id }}" data-bs-toggle="tab" data-bs-target="#modules-{{ $khoaHoc->id }}" type="button" role="tab">
                                            <i class="fas fa-layer-group me-1"></i> Theo Module
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="exams-tab-{{ $khoaHoc->id }}" data-bs-toggle="tab" data-bs-target="#exams-{{ $khoaHoc->id }}" type="button" role="tab">
                                            <i class="fas fa-pen-alt me-1"></i> Từng bài thi
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content" id="courseTabContent-{{ $khoaHoc->id }}">
                                {{-- Tab Module --}}
                                <div class="tab-pane fade show active" id="modules-{{ $khoaHoc->id }}" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0 kqhv-table">
                                            <thead>
                                                <tr>
                                                    <th class="ps-4">Module</th>
                                                    <th class="text-center">Chuyên cần</th>
                                                    <th class="text-center">Điểm thi</th>
                                                    <th class="text-center">Tổng kết</th>
                                                    <th class="pe-4 text-end">GV chốt / Hồ sơ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($moduleResults as $mResult)
                                                    @php
                                                        $mStatusClass = $mResult->trang_thai === 'hoan_thanh' ? 'is-success' : 'is-info';
                                                        $mStatusLabel = $mResult->trang_thai === 'hoan_thanh' ? 'HOÀN THÀNH' : 'ĐANG HỌC';
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="kqhv-module-name">{{ $mResult->moduleHoc->ten_module ?? 'N/A' }}</div>
                                                            <div class="kqhv-module-code">
                                                                <i class="fas fa-tag"></i> {{ $mResult->moduleHoc->ma_module ?? '---' }}
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="kqhv-cell-num">{{ number_format($mResult->diem_diem_danh ?: 0, 1) }}</div>
                                                            <div class="kqhv-cell-sub">{{ $mResult->ty_le_tham_du }}%</div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="kqhv-cell-num">{{ number_format($mResult->diem_kiem_tra ?: 0, 1) }}</div>
                                                            <div class="kqhv-cell-sub">{{ $mResult->so_bai_kiem_tra_hoan_thanh }} bài</div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="kqhv-cell-final">{{ number_format($mResult->diem_tong_ket ?: 0, 2) }}</div>
                                                        </td>
                                                        <td class="pe-4 text-end">
                                                            @if($mResult->diem_giang_vien_chot !== null)
                                                                <div class="kqhv-final-chot">{{ number_format((float) $mResult->diem_giang_vien_chot, 2) }}</div>
                                                            @else
                                                                <div class="kqhv-cell-sub">— chưa chốt</div>
                                                            @endif
                                                            <div class="kqhv-cell-sub">{{ $mResult->trang_thai_chot_label }} · {{ $mResult->trang_thai_duyet_label }}</div>
                                                            <span class="kqhv-status-pill {{ $mStatusClass }} mt-1">
                                                                <i class="fas {{ $mResult->trang_thai === 'hoan_thanh' ? 'fa-check-circle' : 'fa-clock' }}"></i> {{ $mStatusLabel }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-5 text-muted fst-italic">
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
                                    @php
                                        $examGroups = [
                                            'cuoi_khoa' => [
                                                'label' => 'Cuối khóa',
                                                'icon'  => 'fa-flag-checkered',
                                                'items' => $examResults->filter(fn ($result) => $result->baiKiemTra?->loai_bai_kiem_tra === 'cuoi_khoa' || $result->baiKiemTra?->pham_vi === 'cuoi_khoa'),
                                            ],
                                            'module' => [
                                                'label' => 'Theo Module',
                                                'icon'  => 'fa-layer-group',
                                                'items' => $examResults->filter(fn ($result) => in_array($result->baiKiemTra?->loai_bai_kiem_tra, ['module', 'cuoi_module'], true)),
                                            ],
                                            'buoi_hoc' => [
                                                'label' => 'Theo Buổi học',
                                                'icon'  => 'fa-calendar-day',
                                                'items' => $examResults->filter(fn ($result) => $result->baiKiemTra?->loai_bai_kiem_tra === 'buoi_hoc' || $result->baiKiemTra?->pham_vi === 'buoi_hoc'),
                                            ],
                                        ];
                                    @endphp
                                    <div class="kqhv-exam-groups">
                                        <div class="row g-3">
                                            @foreach($examGroups as $group)
                                                <div class="col-lg-4">
                                                    <div class="kqhv-group-card">
                                                        <div class="kqhv-group-title">
                                                            <i class="fas {{ $group['icon'] }}"></i> {{ $group['label'] }}
                                                        </div>
                                                        @forelse($group['items'] as $groupResult)
                                                            <div class="kqhv-group-line">
                                                                <span>{{ $groupResult->baiKiemTra?->tieu_de }}</span>
                                                                <strong>{{ $groupResult->diem_kiem_tra !== null ? number_format((float) $groupResult->diem_kiem_tra, 2) : '--' }}</strong>
                                                            </div>
                                                        @empty
                                                            <div class="kqhv-group-empty">— Chưa có dữ liệu —</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0 kqhv-table">
                                            <thead>
                                                <tr>
                                                    <th class="ps-4">Bài kiểm tra</th>
                                                    <th>Module / Phạm vi</th>
                                                    <th class="text-center">Điểm cao nhất</th>
                                                    <th class="text-center">Kết quả</th>
                                                    <th class="pe-4 text-end">Hành động</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($examResults as $eResult)
                                                    @php
                                                        $eStatusClass = $eResult->trang_thai === 'dat' ? 'is-success' : 'is-danger';
                                                        $eStatusLabel = $eResult->trang_thai === 'dat' ? 'ĐẠT' : 'KHÔNG ĐẠT';
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="kqhv-module-name">{{ $eResult->baiKiemTra->tieu_de }}</div>
                                                            <div class="kqhv-module-code">
                                                                <i class="far fa-clock"></i> Cập nhật: {{ $eResult->cap_nhat_luc?->format('d/m/Y H:i') }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="kqhv-tag-soft">
                                                                <i class="fas fa-cube"></i> {{ $eResult->moduleHoc ? $eResult->moduleHoc->ten_module : 'Cuối khóa' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="kqhv-cell-final">{{ number_format($eResult->diem_kiem_tra ?: 0, 2) }}</div>
                                                            @if($eResult->attempt_strategy_used)
                                                                <div class="kqhv-cell-strategy">{{ $eResult->attempt_strategy_used }}</div>
                                                            @endif
                                                            <div class="kqhv-cell-sub">Lần {{ $eResult->chi_tiet['lan_lam_thu'] ?? 1 }}</div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="kqhv-status-pill {{ $eStatusClass }}">
                                                                <i class="fas {{ $eResult->trang_thai === 'dat' ? 'fa-check-circle' : 'fa-times-circle' }}"></i> {{ $eStatusLabel }}
                                                            </span>
                                                        </td>
                                                        <td class="pe-4 text-end">
                                                            <a href="{{ route('hoc-vien.bai-kiem-tra.show', $eResult->bai_kiem_tra_id) }}"
                                                               class="kqhv-action-btn" title="Xem bài">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-5 text-muted fst-italic">
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
            <div class="kqhv-empty">
                <div class="kqhv-empty-icon"><i class="fas fa-book-reader"></i></div>
                <h5>Bạn chưa tham gia khóa học nào</h5>
                <p>Hãy đăng ký tham gia các khóa học để bắt đầu lộ trình học tập của mình.</p>
                <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn btn-primary fw-bold">
                    <i class="fas fa-arrow-right me-1"></i> Khám phá khóa học
                </a>
            </div>
        @endforelse
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .kqhv-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .kqhv-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .kqhv-page .apx-section-title h2 i { color: #dc2626; }
    .kqhv-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .kqhv-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .kqhv-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .kqhv-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .kqhv-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff; font-size: 0.7rem; font-weight: 800;
        letter-spacing: 1px; border-radius: 999px;
    }
    .kqhv-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff; font-size: 0.72rem; font-weight: 700;
        border-radius: 999px;
    }
    .kqhv-success-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
    }
    .apx-welcome.kqhv-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.kqhv-welcome p i { color: #fef3c7; margin-right: 4px; }
    .kqhv-sep { opacity: 0.5; }
    .kqhv-create-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    /* ===== Course card ===== */
    .kqhv-course-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }

    .kqhv-course-head {
        display: flex;
        gap: 14px;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        padding: 16px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #fef2f2 100%);
        border-bottom: 1px solid #fecaca;
    }
    .kqhv-course-info {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 0;
    }
    .kqhv-course-icon {
        flex-shrink: 0;
        width: 48px; height: 48px;
        border-radius: 12px;
        color: #fff;
        display: grid; place-items: center;
        font-size: 1.2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .kqhv-course-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px;
    }
    .kqhv-course-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .kqhv-course-meta i { color: #1d4ed8; margin-right: 4px; font-size: 0.7rem; }
    .kqhv-meta-sep { opacity: 0.5; }

    .kqhv-course-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .kqhv-final-score {
        text-align: right;
        padding-right: 4px;
    }
    .kqhv-final-score small {
        display: block;
        font-size: 0.66rem;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kqhv-final-score strong {
        display: block;
        font-size: 1.5rem;
        font-weight: 900;
        color: #1d4ed8;
        line-height: 1.1;
    }
    .kqhv-final-score em {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #16a34a;
        font-style: normal;
        margin-top: 3px;
    }
    .kqhv-final-score em i { font-size: 0.6rem; }

    /* Status pill */
    .kqhv-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .kqhv-status-pill i { font-size: 0.62rem; }
    .kqhv-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .kqhv-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .kqhv-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .kqhv-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .kqhv-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .kqhv-detail-link {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);
        transition: all 0.2s ease;
    }
    .kqhv-detail-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.32);
        color: #fff;
    }

    /* ===== Course body ===== */
    .kqhv-course-body { padding: 0; }

    /* Breakdown */
    .kqhv-breakdown {
        padding: 18px 20px;
        background: #fafafa;
        border-right: 1px solid #f1f5f9;
    }
    .kqhv-block-title {
        font-size: 0.74rem;
        font-weight: 800;
        color: #7f1d1d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 14px;
    }
    .kqhv-block-title i { color: #dc2626; margin-right: 5px; }

    .kqhv-component {
        margin-bottom: 16px;
    }
    .kqhv-comp-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
        font-size: 0.84rem;
    }
    .kqhv-comp-head span {
        font-weight: 700;
        color: #1e293b;
    }
    .kqhv-comp-head i { margin-right: 5px; font-size: 0.78rem; }
    .kqhv-comp-head strong {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
    }
    .kqhv-progress {
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 6px;
    }
    .kqhv-progress-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.6s ease;
    }
    .kqhv-progress-bar.bg-success { background: linear-gradient(90deg, #16a34a 0%, #15803d 100%); }
    .kqhv-progress-bar.bg-primary { background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 100%); }
    .kqhv-comp-foot {
        display: flex;
        justify-content: space-between;
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
    }
    .kqhv-comp-foot.kqhv-comp-foot-stack {
        flex-direction: column;
        gap: 3px;
    }
    .kqhv-comp-foot i { font-size: 0.55rem; opacity: 0.7; margin-right: 5px; }

    .kqhv-official {
        margin-top: 14px;
        padding: 12px 14px;
        background: linear-gradient(135deg, #ecfdf5 0%, #dcfce7 100%);
        border: 1px solid #a7f3d0;
        border-radius: 12px;
    }
    .kqhv-official-title {
        font-size: 0.78rem;
        font-weight: 800;
        color: #166534;
        margin-bottom: 8px;
    }
    .kqhv-official-title i { color: #16a34a; margin-right: 5px; }
    .kqhv-official-row {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 4px;
    }
    .kqhv-official-row span {
        font-size: 0.78rem;
        color: #047857;
    }
    .kqhv-official-row strong {
        font-size: 1.2rem;
        font-weight: 900;
        color: #166534;
    }
    .kqhv-official-meta {
        font-size: 0.7rem;
        color: #047857;
        font-weight: 600;
    }

    .kqhv-comment {
        margin-top: 12px;
        padding: 12px 14px;
        background: #fff;
        border: 1px dashed #fde68a;
        border-radius: 10px;
    }
    .kqhv-comment-title {
        font-size: 0.78rem;
        font-weight: 800;
        color: #b45309;
        margin-bottom: 6px;
    }
    .kqhv-comment-title i { color: #f59e0b; margin-right: 5px; }
    .kqhv-comment p {
        margin: 0;
        font-size: 0.82rem;
        color: #1e293b;
        font-style: italic;
        line-height: 1.55;
    }

    /* Detail tabs */
    .kqhv-detail { padding: 0; }
    .kqhv-tabs {
        padding: 14px 18px;
        background: #fff;
        border-bottom: 1px solid #fecaca;
    }
    .kqhv-tabs .nav-pills .nav-link {
        color: #64748b;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 7px 16px;
        border-radius: 999px;
        border: 1px solid transparent;
        transition: all 0.18s ease;
    }
    .kqhv-tabs .nav-pills .nav-link:hover {
        background: #fef2f2;
        color: #dc2626;
    }
    .kqhv-tabs .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        border-color: #dc2626;
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.2);
    }

    /* Detail table */
    .kqhv-table thead {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #7f1d1d;
        letter-spacing: 0.5px;
    }
    .kqhv-table thead th {
        padding: 12px 10px;
        font-weight: 800;
        border-bottom: 1px solid #fecaca;
        border-top: 0;
    }
    .kqhv-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .kqhv-table tbody tr:last-child td { border-bottom: 0; }
    .kqhv-table tbody tr:hover { background: #fafafa; }

    .kqhv-module-name {
        font-size: 0.86rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
    }
    .kqhv-module-code {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 3px;
    }
    .kqhv-module-code i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }
    .kqhv-cell-num {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
    }
    .kqhv-cell-sub {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 3px;
    }
    .kqhv-cell-final {
        font-size: 1.1rem;
        font-weight: 900;
        color: #1d4ed8;
        line-height: 1;
    }
    .kqhv-cell-strategy {
        font-size: 0.7rem;
        color: #1d4ed8;
        font-weight: 700;
        margin-top: 3px;
    }
    .kqhv-final-chot {
        font-size: 0.95rem;
        font-weight: 900;
        color: #16a34a;
    }
    .kqhv-tag-soft {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }
    .kqhv-tag-soft i { color: #1d4ed8; font-size: 0.62rem; }

    .kqhv-action-btn {
        display: inline-grid;
        place-items: center;
        width: 32px; height: 32px;
        border-radius: 8px;
        border: 1px solid #fecaca;
        background: #fff;
        color: #dc2626;
        font-size: 0.78rem;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .kqhv-action-btn:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
        transform: translateY(-1px);
    }

    /* Exam group cards */
    .kqhv-exam-groups {
        padding: 18px;
        background: #fafafa;
        border-bottom: 1px solid #f1f5f9;
    }
    .kqhv-group-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        height: 100%;
    }
    .kqhv-group-title {
        font-size: 0.76rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .kqhv-group-title i { color: #dc2626; margin-right: 5px; font-size: 0.74rem; }
    .kqhv-group-line {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 6px 0;
        border-bottom: 1px dashed #f1f5f9;
        font-size: 0.78rem;
    }
    .kqhv-group-line:last-child { border-bottom: 0; }
    .kqhv-group-line span {
        color: #475569;
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .kqhv-group-line strong { color: #1d4ed8; font-weight: 800; }
    .kqhv-group-empty {
        font-size: 0.78rem;
        color: #cbd5e1;
        font-style: italic;
        padding: 8px 0;
    }

    /* Empty */
    .kqhv-empty {
        padding: 70px 30px;
        text-align: center;
        background: #fff;
        border: 1px dashed #fecaca;
        border-radius: 14px;
    }
    .kqhv-empty-icon {
        width: 100px; height: 100px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #dc2626;
        font-size: 2.2rem;
    }
    .kqhv-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .kqhv-empty p {
        font-size: 0.92rem; color: #94a3b8;
        max-width: 460px; margin: 0 auto 20px;
        line-height: 1.55;
    }

    @media (max-width: 991.98px) {
        .kqhv-breakdown { border-right: 0; border-bottom: 1px solid #f1f5f9; }
        .kqhv-table { font-size: 0.85rem; }
        .kqhv-table thead th, .kqhv-table tbody td { padding: 10px 8px; }
    }
</style>
@endsection
