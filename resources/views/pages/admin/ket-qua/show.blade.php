@extends('layouts.app', ['title' => 'Chi tiết kết quả: ' . $khoa_hoc->ten_khoa_hoc])

@section('content')
@php
    $search = trim((string) request('q', ''));
    $statusFilter = request('trang_thai', '');
    $approvalFilter = request('duyet', '');
    $sort = request('sort', 'name'); // name | tongket_desc | tongket_asc | pending
    $page = max(1, (int) request('page', 1));
    $perPage = 25;

    $rows = collect($student_results);

    if ($search !== '') {
        $rows = $rows->filter(fn ($r) =>
            stripos($r['student']?->ho_ten ?? '', $search) !== false
            || stripos($r['student']?->email ?? '', $search) !== false
        );
    }
    if ($statusFilter !== '') {
        $rows = $rows->filter(fn ($r) => ($r['course_result']?->trang_thai ?? '') === $statusFilter);
    }
    if ($approvalFilter !== '') {
        $rows = $rows->filter(function ($r) use ($approvalFilter) {
            $modules = collect($r['module_results']);
            $modulePending = $modules->where('trang_thai_duyet', 'cho_duyet')->isNotEmpty();
            return match($approvalFilter) {
                'cho_duyet' => $modulePending,
                'da_duyet'  => $modules->where('trang_thai_duyet', 'da_duyet')->isNotEmpty() && !$modulePending,
                default     => true,
            };
        });
    }

    $rows = match($sort) {
        'tongket_desc' => $rows->sortByDesc(fn ($r) => $r['course_result']?->diem_giang_vien_chot ?? $r['course_result']?->diem_tong_ket ?? -1),
        'tongket_asc'  => $rows->sortBy(fn ($r) => $r['course_result']?->diem_giang_vien_chot ?? $r['course_result']?->diem_tong_ket ?? 999),
        'pending'      => $rows->sortByDesc(fn ($r) => collect($r['module_results'])->where('trang_thai_duyet', 'cho_duyet')->count()),
        default        => $rows->sortBy(fn ($r) => mb_strtolower($r['student']?->ho_ten ?? 'zz', 'UTF-8')),
    };

    $totalRows = $rows->count();
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    $page = min($page, $totalPages);
    $pageRows = $rows->forPage($page, $perPage)->values();
    $rangeFrom = $totalRows === 0 ? 0 : ($page - 1) * $perPage + 1;
    $rangeTo = min($page * $perPage, $totalRows);

    $hasFilter = $search !== '' || $statusFilter !== '' || $approvalFilter !== '';

    $queryBase = array_filter([
        'q' => $search,
        'trang_thai' => $statusFilter,
        'duyet' => $approvalFilter,
        'sort' => $sort,
    ], fn ($v) => $v !== '' && $v !== null);
@endphp

<div class="container-fluid admin-page-x kqs-page">
    <div class="apx-welcome kqs-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="apx-welcome-text">
            <div class="kqs-tag-row">
                <span class="kqs-loai-badge"><i class="fas fa-fingerprint"></i> {{ $khoa_hoc->ma_khoa_hoc }}</span>
                <span class="kqs-status-badge"><i class="fas fa-scale-balanced"></i> {{ $khoa_hoc->phuong_thuc_danh_gia_label }}</span>
                @if(($summary['pending_approval_count'] ?? 0) > 0)
                    <span class="kqs-pending-badge"><i class="fas fa-hourglass-half"></i> {{ $summary['pending_approval_count'] }} cần duyệt</span>
                @endif
            </div>
            <h4>{{ $khoa_hoc->ten_khoa_hoc }}</h4>
            <p>
                <span><i class="fas fa-users"></i> {{ $summary['student_count'] }} học viên</span>
                <span class="kqs-sep">·</span>
                <span><i class="fas fa-circle-check"></i> {{ $summary['course_result_count'] }} đã có điểm</span>
                @if($summary['average_score'] !== null)
                    <span class="kqs-sep">·</span>
                    <span><i class="fas fa-chart-line"></i> ĐTB: {{ number_format((float) $summary['average_score'], 2) }}</span>
                @endif
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.ket-qua.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Danh sách khóa</span>
            </a>
            <a href="{{ route('admin.xet-duyet-ket-qua.index', ['khoa_hoc_id' => $khoa_hoc->id]) }}" class="btn btn-light text-primary fw-bold shadow-sm kqs-cta-btn">
                <i class="fas fa-stamp me-1"></i> Phiếu xét duyệt
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- ① Tổng quan --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan kết quả</h2>
                    <p>Năm chỉ số nhanh về kết quả của khóa học này.</p>
                </div>
            </div>
        </header>
        <div class="row g-3">
            <div class="col-md col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-users"></i></div><div class="aps-text"><strong>{{ $summary['student_count'] }}</strong><small>Học viên</small></div></div></div>
            <div class="col-md col-6"><div class="apx-stat tone-success"><div class="aps-icon"><i class="fas fa-circle-check"></i></div><div class="aps-text"><strong>{{ $summary['course_result_count'] }}</strong><small>Đã có điểm</small></div></div></div>
            <div class="col-md col-6"><div class="apx-stat tone-info"><div class="aps-icon"><i class="fas fa-chart-line"></i></div><div class="aps-text"><strong>{{ $summary['average_score'] !== null ? number_format((float) $summary['average_score'], 2) : '—' }}</strong><small>Điểm TB chung</small></div></div></div>
            <div class="col-md col-6"><div class="apx-stat tone-warning"><div class="aps-icon"><i class="fas fa-hourglass-half"></i></div><div class="aps-text"><strong>{{ $summary['pending_approval_count'] ?? 0 }}</strong><small>Chờ duyệt</small></div></div></div>
            <div class="col-md col-6"><div class="apx-stat tone-violet"><div class="aps-icon"><i class="fas fa-archive"></i></div><div class="aps-text"><strong>{{ $summary['archived_count'] ?? 0 }}</strong><small>Đã lưu hồ sơ</small></div></div></div>
        </div>
    </section>

    {{-- ② Toolbar (sticky) + danh sách HV compact --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách kết quả học viên</h2>
                    <p>Bấm vào dòng để xem chi tiết module &amp; bài kiểm tra. Hỗ trợ tìm/lọc/sắp xếp/phân trang.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill">
                    <strong>{{ $rangeFrom }}–{{ $rangeTo }}</strong> / {{ $totalRows }}
                    @if($hasFilter)
                        <span style="color:#b45309;">· đang lọc</span>
                    @endif
                </span>
            </div>
        </header>

        {{-- Toolbar --}}
        <div class="kqs-toolbar">
            <form method="GET" class="kqs-toolbar-form">
                <div class="kqs-input-icon kqs-search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Tìm tên hoặc email...">
                </div>
                <select name="trang_thai" class="form-select kqs-select">
                    <option value="">Trạng thái: Tất cả</option>
                    <option value="dat"        @selected($statusFilter === 'dat')>Đạt</option>
                    <option value="khong_dat"  @selected($statusFilter === 'khong_dat')>Chưa đạt</option>
                    <option value="dang_hoc"   @selected($statusFilter === 'dang_hoc')>Đang học</option>
                </select>
                <select name="duyet" class="form-select kqs-select">
                    <option value="">Module: Tất cả</option>
                    <option value="cho_duyet" @selected($approvalFilter === 'cho_duyet')>Có chờ duyệt</option>
                    <option value="da_duyet"  @selected($approvalFilter === 'da_duyet')>Đã duyệt hết</option>
                </select>
                <select name="sort" class="form-select kqs-select">
                    <option value="name"          @selected($sort === 'name')>Sắp xếp: A→Z</option>
                    <option value="tongket_desc"  @selected($sort === 'tongket_desc')>Tổng kết cao→thấp</option>
                    <option value="tongket_asc"   @selected($sort === 'tongket_asc')>Tổng kết thấp→cao</option>
                    <option value="pending"       @selected($sort === 'pending')>Chờ duyệt nhiều nhất</option>
                </select>
                <button type="submit" class="kqs-btn-filter">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                <a href="{{ route('admin.ket-qua.show', $khoa_hoc->id) }}" class="kqs-btn-reset" title="Xóa lọc">
                    <i class="fas fa-rotate-left"></i>
                </a>
                <button type="button" class="kqs-btn-collapse-all" id="kqsCollapseAll" title="Thu gọn tất cả">
                    <i class="fas fa-compress"></i>
                </button>
            </form>
        </div>

        @if($pageRows->isEmpty())
            <div class="kqs-empty">
                <div class="kqs-empty-icon"><i class="fas fa-user-slash"></i></div>
                <h5>{{ $hasFilter ? 'Không tìm thấy học viên khớp bộ lọc' : 'Chưa có học viên trong khóa' }}</h5>
                <p>{{ $hasFilter ? 'Thử bỏ bộ lọc hoặc đổi từ khóa.' : 'Khi học viên được ghi danh, kết quả sẽ xuất hiện ở đây.' }}</p>
            </div>
        @else
            <div class="kqs-list">
                {{-- Header row --}}
                <div class="kqs-row kqs-row-header">
                    <div class="kqs-col-stt">#</div>
                    <div class="kqs-col-name">Học viên</div>
                    <div class="kqs-col-score">Chuyên cần</div>
                    <div class="kqs-col-score">Kiểm tra</div>
                    <div class="kqs-col-score">Tổng kết</div>
                    <div class="kqs-col-status">Trạng thái</div>
                    <div class="kqs-col-action"></div>
                </div>

                @foreach($pageRows as $row)
                    @php
                        $student       = $row['student'];
                        $courseResult  = $row['course_result'];
                        $moduleResults = $row['module_results'];
                        $examResults   = $row['exam_results'];
                        $attemptsByExam = $row['attempts_by_exam'] ?? collect();
                        $modulePending = collect($moduleResults)->where('trang_thai_duyet', 'cho_duyet')->count();

                        $rowIndex = $rangeFrom + $loop->index;
                        $studentId = $student?->ma_nguoi_dung ?? $loop->index;
                        $idColor = ($student?->ma_nguoi_dung ?? $loop->index) % 6;
                        $gradients = [
                            'linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)',
                            'linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)',
                            'linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)',
                        ];
                        $initialHV = mb_strtoupper(mb_substr(trim($student?->ho_ten ?? '?'), 0, 1, 'UTF-8'), 'UTF-8');

                        $statusClass = match($courseResult?->trang_thai) {
                            'dat'        => 'is-success',
                            'khong_dat'  => 'is-danger',
                            'dang_hoc'   => 'is-info',
                            default      => 'is-secondary',
                        };
                        $statusLabel = match($courseResult?->trang_thai) {
                            'dat'        => 'ĐẠT',
                            'khong_dat'  => 'CHƯA ĐẠT',
                            'dang_hoc'   => 'ĐANG HỌC',
                            default      => 'CHƯA CÓ',
                        };

                        $finalScore = $courseResult?->diem_giang_vien_chot ?? $courseResult?->diem_tong_ket;
                        $finalScoreClass = '';
                        if ($finalScore !== null) {
                            $finalScoreClass = $finalScore >= 5 ? 'is-pass' : 'is-fail';
                        }
                    @endphp

                    <div class="kqs-row {{ $modulePending > 0 ? 'has-pending' : '' }}" data-row-id="row-{{ $studentId }}">
                        <div class="kqs-col-stt">{{ $rowIndex }}</div>
                        <div class="kqs-col-name">
                            <button type="button" class="kqs-row-toggle" onclick="kqsToggleRow('row-{{ $studentId }}')">
                                <div class="kqs-avatar" style="background: {{ $gradients[$idColor] }};">{{ $initialHV }}</div>
                                <div class="kqs-name-block">
                                    <div class="kqs-name">{{ $student?->ho_ten ?? '—' }}</div>
                                    <div class="kqs-email">{{ $student?->email ?? '—' }}</div>
                                </div>
                                <i class="fas fa-chevron-down kqs-row-arrow"></i>
                            </button>
                        </div>
                        <div class="kqs-col-score">
                            {{ $courseResult?->diem_diem_danh !== null ? number_format((float) $courseResult->diem_diem_danh, 2) : '—' }}
                        </div>
                        <div class="kqs-col-score">
                            {{ $courseResult?->diem_kiem_tra !== null ? number_format((float) $courseResult->diem_kiem_tra, 2) : '—' }}
                        </div>
                        <div class="kqs-col-score kqs-col-final {{ $finalScoreClass }}">
                            {{ $finalScore !== null ? number_format((float) $finalScore, 2) : '—' }}
                            @if($courseResult?->diem_giang_vien_chot !== null)
                                <small><i class="fas fa-lock"></i></small>
                            @endif
                        </div>
                        <div class="kqs-col-status">
                            <span class="kqs-status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if($modulePending > 0)
                                <span class="kqs-pending-pill"><i class="fas fa-hourglass-half"></i> {{ $modulePending }}</span>
                            @endif
                        </div>
                        <div class="kqs-col-action">
                            <button type="button" class="kqs-quick-btn" onclick="kqsToggleRow('row-{{ $studentId }}')">
                                Chi tiết <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Expanded detail panel --}}
                    <div class="kqs-detail" id="detail-row-{{ $studentId }}">
                        <div class="kqs-detail-grid">
                            {{-- Modules --}}
                            <div class="kqs-detail-block">
                                <div class="kqs-detail-block-head">
                                    <i class="fas fa-layer-group"></i>
                                    <span>Module ({{ $moduleResults->count() }})</span>
                                    @if($modulePending > 0)
                                        <span class="kqs-detail-badge is-warning">{{ $modulePending }} chờ duyệt</span>
                                    @endif
                                </div>
                                <div class="kqs-detail-block-body">
                                    @forelse($row['module_breakdowns'] as $mb)
                                        @php
                                            $mResult = $mb['result'];
                                            $bd = $mb['breakdown'];
                                            $sm = $bd['summary'];
                                            $isPending = $mResult->trang_thai_duyet === 'cho_duyet';
                                            $finalModule = $mResult->diem_giang_vien_chot ?? $mResult->diem_tong_ket;
                                        @endphp
                                        <div class="kqs-module-block {{ $isPending ? 'is-pending' : '' }}">
                                            <div class="kqs-module-head">
                                                <div class="kqs-module-info">
                                                    <strong>{{ $mResult->moduleHoc?->ten_module ?? 'Module' }}</strong>
                                                    @if($mResult->aggregation_strategy_used)
                                                        <span class="kqs-strategy-tag"><i class="fas fa-cogs"></i> {{ $mResult->aggregation_strategy_used }}</span>
                                                    @endif
                                                </div>
                                                <div class="kqs-module-status">
                                                    <span class="kqs-status-pill {{ $mResult->da_chot ? 'is-success' : 'is-secondary' }}">
                                                        <i class="fas {{ $mResult->da_chot ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                                        {{ $mResult->da_chot ? 'Đã chốt' : 'Chưa chốt' }}
                                                    </span>
                                                    <div class="kqs-module-score">{{ number_format($finalModule ?: 0, 2) }}</div>
                                                </div>
                                            </div>

                                            <div class="kqs-mini-grid">
                                                <div class="kqs-mini-cell">
                                                    <small>Quá trình (A)</small>
                                                    <strong>{{ number_format($sm['process_score'] ?: 0, 2) }}</strong>
                                                </div>
                                                <div class="kqs-mini-cell">
                                                    <small>Điểm thi (B)</small>
                                                    <strong>{{ number_format($sm['module_exam_score'] ?: 0, 2) }}</strong>
                                                </div>
                                                <div class="kqs-mini-cell">
                                                    <small>Chuyên cần</small>
                                                    <strong>{{ $bd['attendance']['so_buoi_tham_du'] }}/{{ $bd['attendance']['tong_so_buoi'] }}</strong>
                                                </div>
                                            </div>

                                            @if($isPending)
                                                <div class="kqs-approval-block">
                                                    <div class="kqs-approval-title"><i class="fas fa-gavel"></i> Phê duyệt module</div>
                                                    <form method="POST" action="{{ route('admin.ket-qua.approve', $mResult->id) }}" class="kqs-approval-form">
                                                        @csrf
                                                        <input type="text" name="ghi_chu_duyet" class="form-control" placeholder="Ghi chú duyệt (không bắt buộc)">
                                                        <button class="kqs-btn-approve" type="submit"><i class="fas fa-check"></i> Duyệt</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.ket-qua.reject', $mResult->id) }}" class="kqs-approval-form">
                                                        @csrf
                                                        <input type="text" name="ghi_chu_duyet" class="form-control" placeholder="Lý do trả về (không bắt buộc)">
                                                        <button class="kqs-btn-reject" type="submit"><i class="fas fa-rotate-left"></i> Trả về</button>
                                                    </form>
                                                </div>
                                            @else
                                                <div class="kqs-module-foot">
                                                    <span class="kqs-tag-soft"><i class="fas fa-flag"></i> {{ $mResult->trang_thai_duyet_label }}</span>
                                                    @if($mResult->luu_ho_so_luc)
                                                        <span class="kqs-tag-soft is-success"><i class="fas fa-archive"></i> Lưu hồ sơ {{ $mResult->luu_ho_so_luc->format('d/m/Y H:i') }}</span>
                                                    @endif
                                                    @if($mResult->chotBoi)
                                                        <span class="kqs-tag-soft"><i class="fas fa-user-check"></i> {{ $mResult->chotBoi->ho_ten }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="kqs-drill-empty"><i class="fas fa-circle-info"></i> Chưa có kết quả module.</div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Exams --}}
                            <div class="kqs-detail-block">
                                <div class="kqs-detail-block-head">
                                    <i class="fas fa-file-pen"></i>
                                    <span>Bài kiểm tra ({{ $examResults->count() }})</span>
                                </div>
                                <div class="kqs-detail-block-body">
                                    @forelse($examResults as $examResult)
                                        @php $attempts = $attemptsByExam[$examResult->bai_kiem_tra_id] ?? collect(); @endphp
                                        <div class="kqs-exam-block">
                                            <div class="kqs-exam-head">
                                                <strong>{{ $examResult->baiKiemTra?->tieu_de ?? 'Bài kiểm tra' }}</strong>
                                                <div class="kqs-exam-final">
                                                    <small>Chính thức</small>
                                                    <span>{{ $examResult->diem_kiem_tra !== null ? number_format((float) $examResult->diem_kiem_tra, 2) : '—' }}</span>
                                                </div>
                                            </div>
                                            @if($attempts->isNotEmpty())
                                                <div class="kqs-attempts">
                                                    @foreach($attempts as $attempt)
                                                        @php $isOfficial = (int) $attempt->id === (int) $examResult->source_attempt_id; @endphp
                                                        <div class="kqs-attempt {{ $isOfficial ? 'is-official' : '' }}">
                                                            <span class="kqs-attempt-no">Lần {{ $attempt->lan_lam_thu }}</span>
                                                            <span class="kqs-attempt-score">{{ $attempt->diem_so !== null ? number_format((float) $attempt->diem_so, 2) : 'Chờ chấm' }}</span>
                                                            @if($isOfficial)
                                                                <span class="kqs-attempt-tag"><i class="fas fa-star"></i> Chính thức</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="kqs-drill-empty"><i class="fas fa-circle-info"></i> Chưa có kết quả bài kiểm tra.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($totalPages > 1)
                <div class="kqs-pagination">
                    <a href="{{ $page > 1 ? route('admin.ket-qua.show', array_merge(['khoaHocId' => $khoa_hoc->id], $queryBase, ['page' => $page - 1])) : '#' }}"
                       class="kqs-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}">
                        <i class="fas fa-chevron-left"></i> Trước
                    </a>
                    @php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        if ($start > 1) echo '<span class="kqs-page-ellipsis">…</span>';
                    @endphp
                    @for($p = $start; $p <= $end; $p++)
                        @if($p === $page)
                            <span class="kqs-page-btn is-active">{{ $p }}</span>
                        @else
                            <a href="{{ route('admin.ket-qua.show', array_merge(['khoaHocId' => $khoa_hoc->id], $queryBase, ['page' => $p])) }}"
                               class="kqs-page-btn">{{ $p }}</a>
                        @endif
                    @endfor
                    @if($end < $totalPages)<span class="kqs-page-ellipsis">…</span>@endif
                    <a href="{{ $page < $totalPages ? route('admin.ket-qua.show', array_merge(['khoaHocId' => $khoa_hoc->id], $queryBase, ['page' => $page + 1])) : '#' }}"
                       class="kqs-page-btn {{ $page >= $totalPages ? 'is-disabled' : '' }}">
                        Sau <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            @endif
        @endif
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .kqs-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .kqs-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .kqs-page .apx-section-title h2 i { color: #dc2626; }
    .kqs-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .kqs-page .apx-meta-pill strong { color: #b91c1c; }

    .kqs-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .kqs-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .kqs-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; border-radius: 999px; }
    .kqs-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .kqs-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: kqsPulse 1.6s ease-out infinite; }
    @keyframes kqsPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.kqs-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.kqs-welcome p i { color: #fef3c7; margin-right: 4px; }
    .kqs-sep { opacity: 0.5; }
    .kqs-cta-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    /* Toolbar — sticky */
    .kqs-toolbar {
        position: sticky;
        top: 12px;
        z-index: 5;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
        margin-bottom: 12px;
        box-shadow: 0 4px 14px rgba(15,23,42,0.06);
    }
    .kqs-toolbar-form {
        display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
        margin: 0;
    }
    .kqs-search-input { flex: 2 1 280px; }
    .kqs-input-icon { position: relative; }
    .kqs-input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem; pointer-events: none; }
    .kqs-input-icon input { padding-left: 34px; }
    .kqs-select { flex: 1 1 160px; min-width: 140px; }
    .kqs-btn-filter, .kqs-btn-reset, .kqs-btn-collapse-all {
        flex-shrink: 0;
        padding: 8px 14px;
        font-size: 0.84rem;
        font-weight: 800;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.18s ease;
        white-space: nowrap;
    }
    .kqs-btn-filter {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff; border: 0;
    }
    .kqs-btn-filter:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .kqs-btn-reset, .kqs-btn-collapse-all {
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .kqs-btn-reset:hover, .kqs-btn-collapse-all:hover {
        background: #fef2f2; color: #dc2626; border-color: #fecaca;
    }

    /* Compact list */
    .kqs-list {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }
    .kqs-row {
        display: grid;
        grid-template-columns: 50px minmax(220px, 2.4fr) 92px 92px 110px 150px 110px;
        gap: 10px;
        align-items: center;
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s ease;
    }
    .kqs-row:hover { background: #fafafa; }
    .kqs-row.kqs-row-header {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem;
        font-weight: 800;
        color: #7f1d1d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-bottom: 1px solid #fecaca;
        padding: 12px 14px;
    }
    .kqs-row.has-pending {
        border-left: 3px solid #f59e0b;
        padding-left: 11px;
        background: linear-gradient(90deg, #fffbeb 0%, #ffffff 35%);
    }

    .kqs-col-stt {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 800;
        text-align: center;
    }
    .kqs-row-toggle {
        display: flex; align-items: center; gap: 10px;
        background: transparent; border: 0; padding: 0;
        text-align: left;
        cursor: pointer;
        width: 100%;
        font-family: inherit;
        font-size: inherit;
        color: inherit;
    }
    .kqs-avatar {
        flex-shrink: 0;
        width: 36px; height: 36px;
        border-radius: 50%;
        color: #fff;
        font-weight: 800;
        font-size: 0.92rem;
        display: grid; place-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .kqs-name-block { flex: 1; min-width: 0; }
    .kqs-name {
        font-size: 0.88rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .kqs-email {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .kqs-row-arrow {
        color: #cbd5e1;
        font-size: 0.78rem;
        transition: transform 0.2s ease;
    }
    .kqs-row.is-open .kqs-row-arrow { transform: rotate(180deg); color: #dc2626; }

    .kqs-col-score {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }
    .kqs-col-final {
        font-size: 1.05rem;
        font-weight: 900;
        position: relative;
    }
    .kqs-col-final.is-pass { color: #16a34a; }
    .kqs-col-final.is-fail { color: #dc2626; }
    .kqs-col-final small {
        font-size: 0.66rem;
        margin-left: 4px;
        opacity: 0.8;
    }

    .kqs-col-status {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    .kqs-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        font-size: 0.66rem; font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .kqs-status-pill i { font-size: 0.6rem; }
    .kqs-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .kqs-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .kqs-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .kqs-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .kqs-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
    .kqs-pending-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-size: 0.62rem; font-weight: 800;
        border-radius: 999px;
        animation: kqsPulse 1.6s ease-out infinite;
    }
    .kqs-pending-pill i { font-size: 0.55rem; }

    .kqs-col-action { text-align: right; }
    .kqs-quick-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .kqs-quick-btn:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .kqs-quick-btn i { font-size: 0.7rem; transition: transform 0.2s ease; }
    .kqs-row.is-open + .kqs-detail .kqs-quick-btn i { transform: rotate(180deg); }

    /* Expanded detail */
    .kqs-detail {
        display: none;
        padding: 14px 18px;
        background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%);
        border-bottom: 1px solid #f1f5f9;
    }
    .kqs-detail.is-open { display: block; }
    .kqs-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    @media (max-width: 991.98px) {
        .kqs-detail-grid { grid-template-columns: 1fr; }
    }
    .kqs-detail-block {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    .kqs-detail-block-head {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        border-bottom: 1px solid #fecaca;
        font-size: 0.84rem;
        font-weight: 800;
        color: #0f172a;
    }
    .kqs-detail-block-head i { color: #dc2626; font-size: 0.85rem; }
    .kqs-detail-badge {
        margin-left: auto;
        padding: 2px 10px;
        font-size: 0.66rem;
        font-weight: 800;
        border-radius: 999px;
    }
    .kqs-detail-badge.is-warning { background: #fef3c7; color: #b45309; }
    .kqs-detail-block-body { padding: 12px; }

    /* Module block */
    .kqs-module-block {
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 8px;
    }
    .kqs-module-block:last-child { margin-bottom: 0; }
    .kqs-module-block.is-pending { border-left: 3px solid #f59e0b; background: #fffbeb; }
    .kqs-module-head {
        display: flex; gap: 8px; flex-wrap: wrap;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .kqs-module-info strong {
        display: block;
        font-size: 0.86rem; font-weight: 800; color: #0f172a;
    }
    .kqs-strategy-tag {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 1px 8px;
        background: #ede9fe; color: #7c3aed;
        font-size: 0.62rem; font-weight: 800;
        border-radius: 999px;
        margin-top: 3px;
    }
    .kqs-strategy-tag i { font-size: 0.55rem; }
    .kqs-module-status { text-align: right; }
    .kqs-module-status .kqs-status-pill { margin-bottom: 3px; }
    .kqs-module-score {
        font-size: 1.1rem; font-weight: 900;
        color: #1d4ed8; line-height: 1;
    }

    .kqs-mini-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
        margin-bottom: 8px;
    }
    .kqs-mini-cell {
        padding: 6px 8px;
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 6px;
        text-align: center;
    }
    .kqs-mini-cell small {
        display: block;
        font-size: 0.62rem; color: #64748b;
        font-weight: 700; text-transform: uppercase;
    }
    .kqs-mini-cell strong {
        display: block;
        font-size: 0.86rem; font-weight: 800;
        color: #0f172a;
    }

    .kqs-approval-block {
        margin-top: 8px;
        padding: 10px 12px;
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-radius: 8px;
    }
    .kqs-approval-title {
        font-size: 0.72rem; font-weight: 800; color: #92400e;
        text-transform: uppercase; letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .kqs-approval-title i { color: #f59e0b; margin-right: 5px; }
    .kqs-approval-form {
        display: flex; gap: 6px; margin: 0 0 6px;
    }
    .kqs-approval-form:last-child { margin-bottom: 0; }
    .kqs-approval-form .form-control { flex: 1; font-size: 0.8rem; padding: 5px 10px; border-radius: 6px; }
    .kqs-btn-approve, .kqs-btn-reject {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 12px; font-size: 0.78rem; font-weight: 800;
        border: 0; border-radius: 6px; cursor: pointer; white-space: nowrap;
        transition: all 0.18s ease;
    }
    .kqs-btn-approve { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; }
    .kqs-btn-approve:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(22,163,74,0.3); }
    .kqs-btn-reject { background: #fff; color: #dc2626; border: 1px solid #fecaca; }
    .kqs-btn-reject:hover { background: #dc2626; color: #fff; }

    .kqs-module-foot {
        display: flex; gap: 5px; flex-wrap: wrap;
    }
    .kqs-tag-soft {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px;
        background: #f1f5f9; color: #475569;
        font-size: 0.66rem; font-weight: 700;
        border-radius: 999px;
    }
    .kqs-tag-soft.is-success { background: #dcfce7; color: #166534; }
    .kqs-tag-soft i { font-size: 0.58rem; }

    /* Exam */
    .kqs-exam-block {
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 8px;
    }
    .kqs-exam-block:last-child { margin-bottom: 0; }
    .kqs-exam-head {
        display: flex; justify-content: space-between; gap: 10px;
        align-items: center; flex-wrap: wrap; margin-bottom: 6px;
    }
    .kqs-exam-head strong {
        font-size: 0.86rem; font-weight: 800; color: #0f172a;
        flex: 1; min-width: 180px;
    }
    .kqs-exam-final small {
        display: block;
        font-size: 0.62rem; color: #64748b;
        font-weight: 700; text-transform: uppercase;
    }
    .kqs-exam-final span {
        display: block;
        font-size: 1rem; font-weight: 900; color: #1d4ed8; line-height: 1.1;
    }
    .kqs-attempts { display: flex; flex-wrap: wrap; gap: 4px; }
    .kqs-attempt {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 8px;
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 6px;
        font-size: 0.72rem;
    }
    .kqs-attempt-no { color: #64748b; font-weight: 700; }
    .kqs-attempt-score { color: #0f172a; font-weight: 800; }
    .kqs-attempt.is-official { background: #fef3c7; border-color: #fde68a; }
    .kqs-attempt-tag {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 1px 6px;
        background: #f59e0b; color: #fff;
        font-size: 0.58rem; font-weight: 800; border-radius: 999px;
    }
    .kqs-attempt-tag i { font-size: 0.5rem; }

    .kqs-drill-empty {
        padding: 12px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.8rem;
    }
    .kqs-drill-empty i { color: #1d4ed8; margin-right: 5px; }

    /* Pagination */
    .kqs-pagination {
        display: flex; gap: 4px; flex-wrap: wrap;
        justify-content: center;
        padding: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-top: 0;
        border-radius: 0 0 14px 14px;
        margin-top: -1px;
    }
    .kqs-page-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.84rem; font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.18s ease;
        min-width: 36px;
        justify-content: center;
    }
    .kqs-page-btn:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .kqs-page-btn.is-active {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border-color: #dc2626;
        color: #fff;
        box-shadow: 0 4px 12px rgba(220,38,38,0.25);
    }
    .kqs-page-btn.is-disabled {
        opacity: 0.4; pointer-events: none;
    }
    .kqs-page-ellipsis { color: #94a3b8; font-weight: 800; padding: 0 6px; align-self: center; }

    /* Empty */
    .kqs-empty { padding: 60px 30px; text-align: center; background: #fff; border: 1px dashed #fecaca; border-radius: 14px; }
    .kqs-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .kqs-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .kqs-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }

    /* Responsive */
    @media (max-width: 991.98px) {
        .kqs-row {
            grid-template-columns: 40px 1fr 80px 80px 90px;
            grid-template-areas:
                "stt name name name action"
                "stt scores scores scores scores"
                "stt status status status status";
            gap: 6px 10px;
        }
        .kqs-row-header { display: none; }
        .kqs-col-stt { grid-area: stt; align-self: start; }
        .kqs-col-name { grid-area: name; }
        .kqs-col-action { grid-area: action; }
        .kqs-col-score:nth-of-type(3) { grid-area: scores; display: none; }
        .kqs-col-status { grid-area: status; flex-direction: row; align-items: center; }
    }
</style>

<script>
function kqsToggleRow(rowId) {
    const row    = document.querySelector('[data-row-id="' + rowId + '"]');
    const detail = document.getElementById('detail-' + rowId);
    if (!row || !detail) return;

    const isOpen = detail.classList.contains('is-open');
    if (isOpen) {
        detail.classList.remove('is-open');
        row.classList.remove('is-open');
    } else {
        detail.classList.add('is-open');
        row.classList.add('is-open');
    }
}

document.getElementById('kqsCollapseAll')?.addEventListener('click', function () {
    document.querySelectorAll('.kqs-detail.is-open').forEach(d => d.classList.remove('is-open'));
    document.querySelectorAll('.kqs-row.is-open').forEach(r => r.classList.remove('is-open'));
});
</script>
@endsection
