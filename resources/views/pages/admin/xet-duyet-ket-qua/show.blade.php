@extends('layouts.app', ['title' => 'Chi tiết phiếu xét duyệt #' . $phieu->id])

@section('content')
@php
    $canStartReview = $phieu->trang_thai === \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED;
    $canApprove = in_array($phieu->trang_thai, [
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
    ], true);
    $canFinalize = in_array($phieu->trang_thai, [
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_SUBMITTED,
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_REVIEWING,
        \App\Models\PhieuXetDuyetKetQua::TRANG_THAI_APPROVED,
    ], true);
    $firstDetail = $phieu->chiTiets->first();

    // Phân nhóm filter + sort + pagination
    $search = trim((string) request('q', ''));
    $resultFilter = request('ket_qua', '');
    $sort = request('sort', 'name');
    $page = max(1, (int) request('page', 1));
    $perPage = 25;

    $rows = $phieu->chiTiets;

    if ($search !== '') {
        $rows = $rows->filter(fn ($r) =>
            stripos($r->hocVien?->ho_ten ?? '', $search) !== false
            || stripos($r->hocVien?->email ?? '', $search) !== false
        );
    }
    if ($resultFilter !== '') {
        $rows = $rows->filter(fn ($r) => ($r->ket_qua ?? '') === $resultFilter);
    }
    $rows = match($sort) {
        'xetduyet_desc' => $rows->sortByDesc(fn ($r) => $r->diem_xet_duyet ?? -1),
        'xetduyet_asc'  => $rows->sortBy(fn ($r) => $r->diem_xet_duyet ?? 999),
        default         => $rows->sortBy(fn ($r) => mb_strtolower($r->hocVien?->ho_ten ?? 'zz', 'UTF-8')),
    };

    $totalRows = $rows->count();
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    $page = min($page, $totalPages);
    $pageRows = $rows->forPage($page, $perPage)->values();
    $rangeFrom = $totalRows === 0 ? 0 : ($page - 1) * $perPage + 1;
    $rangeTo = min($page * $perPage, $totalRows);

    $hasFilter = $search !== '' || $resultFilter !== '';
    $queryBase = array_filter([
        'q' => $search, 'ket_qua' => $resultFilter, 'sort' => $sort,
    ], fn ($v) => $v !== '' && $v !== null);

    // Stats: kết quả (đạt/không đạt) trên toàn phiếu
    $totalCount = $phieu->chiTiets->count();
    $countDat = $phieu->chiTiets->filter(fn ($r) => ($r->ket_qua ?? '') === 'dat')->count();
    $countKhongDat = $phieu->chiTiets->filter(fn ($r) => ($r->ket_qua ?? '') === 'khong_dat')->count();
    $avgScore = $phieu->chiTiets->whereNotNull('diem_xet_duyet')->avg('diem_xet_duyet');
@endphp

<div class="container-fluid admin-page-x pxd-page">
    <div class="apx-welcome pxd-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-stamp"></i></div>
        <div class="apx-welcome-text">
            <div class="pxd-tag-row">
                <span class="pxd-loai-badge"><i class="fas fa-file-circle-check"></i> PHIẾU XÉT DUYỆT #{{ $phieu->id }}</span>
                <span class="pxd-status-pill is-{{ $phieu->trang_thai_color }}">
                    <i class="fas fa-circle"></i> {{ $phieu->trang_thai_label }}
                </span>
                <span class="pxd-status-badge"><i class="fas fa-graduation-cap"></i> {{ $phieu->khoaHoc?->ma_khoa_hoc ?? '—' }}</span>
            </div>
            <h4>{{ $phieu->khoaHoc?->ten_khoa_hoc ?? 'Phiếu xét duyệt' }}</h4>
            <p>
                <span><i class="fas fa-user"></i> GV lập: {{ $phieu->nguoiLap?->ho_ten ?? '—' }}</span>
                <span class="pxd-sep">·</span>
                <span><i class="fas fa-cog"></i> {{ $phieu->phuong_an_label }}</span>
                @if($phieu->submitted_at)
                    <span class="pxd-sep">·</span>
                    <span><i class="far fa-clock"></i> Gửi {{ $phieu->submitted_at->format('d/m/Y H:i') }}</span>
                @endif
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.xet-duyet-ket-qua.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Danh sách</span>
            </a>
            @if($phieu->khoaHoc)
                <a href="{{ route('admin.ket-qua.show', $phieu->khoaHoc->id) }}" class="apx-view-toggle">
                    <i class="fas fa-chart-bar"></i> <span>Bảng điểm</span>
                </a>
            @endif
        </div>
    </div>

    @include('components.alert')

    {{-- ① Tổng quan phiếu --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-circle-info"></i> Thông tin phiếu &amp; tổng quan</h2>
                    <p>Cấu hình tỷ trọng, điểm đạt và phân bổ kết quả của toàn bộ học viên.</p>
                </div>
            </div>
        </header>

        <div class="row g-3 mb-3">
            <div class="col-md col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-users"></i></div>
                    <div class="aps-text"><strong>{{ $totalCount }}</strong><small>Học viên</small></div>
                </div>
            </div>
            <div class="col-md col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text"><strong>{{ $countDat }}</strong><small>Đạt</small></div>
                </div>
            </div>
            <div class="col-md col-6">
                <div class="apx-stat tone-danger">
                    <div class="aps-icon"><i class="fas fa-circle-xmark"></i></div>
                    <div class="aps-text"><strong>{{ $countKhongDat }}</strong><small>Chưa đạt</small></div>
                </div>
            </div>
            <div class="col-md col-6">
                <div class="apx-stat tone-info">
                    <div class="aps-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="aps-text"><strong>{{ $avgScore !== null ? number_format((float) $avgScore, 2) : '—' }}</strong><small>Điểm TB xét duyệt</small></div>
                </div>
            </div>
            <div class="col-md col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-bullseye"></i></div>
                    <div class="aps-text"><strong>{{ number_format((float) $phieu->diem_dat, 2) }}</strong><small>Điểm đạt</small></div>
                </div>
            </div>
        </div>

        <div class="pxd-info-card">
            <div class="pxd-info-grid">
                <div class="pxd-info-item">
                    <div class="pxd-info-icon" style="background: #eff6ff; color: #1d4ed8;"><i class="fas fa-percentage"></i></div>
                    <div>
                        <small>Tỷ trọng kiểm tra</small>
                        <strong>{{ number_format((float) $phieu->ty_trong_kiem_tra, 0) }}%</strong>
                    </div>
                </div>
                <div class="pxd-info-item">
                    <div class="pxd-info-icon" style="background: #dcfce7; color: #16a34a;"><i class="fas fa-user-check"></i></div>
                    <div>
                        <small>Tỷ trọng điểm danh</small>
                        <strong>{{ number_format((float) $phieu->ty_trong_diem_danh, 0) }}%</strong>
                    </div>
                </div>
                <div class="pxd-info-item">
                    <div class="pxd-info-icon" style="background: #fef3c7; color: #b45309;"><i class="fas fa-bullseye"></i></div>
                    <div>
                        <small>Điểm đạt tối thiểu</small>
                        <strong>{{ number_format((float) $phieu->diem_dat, 2) }}</strong>
                    </div>
                </div>
                <div class="pxd-info-item">
                    <div class="pxd-info-icon" style="background: #ede9fe; color: #7c3aed;"><i class="fas fa-cog"></i></div>
                    <div>
                        <small>Phương án</small>
                        <strong style="font-size: 0.92rem;">{{ $phieu->phuong_an_label }}</strong>
                    </div>
                </div>
            </div>

            @if($phieu->ghi_chu)
                <div class="pxd-note pxd-note-gv">
                    <div class="pxd-note-title"><i class="fas fa-comment-dots"></i> Ghi chú giảng viên</div>
                    <p>{{ $phieu->ghi_chu }}</p>
                </div>
            @endif

            @if($phieu->reject_reason)
                <div class="pxd-note pxd-note-reject">
                    <div class="pxd-note-title"><i class="fas fa-circle-xmark"></i> Lý do từ chối trước đó</div>
                    <p>{{ $phieu->reject_reason }}</p>
                </div>
            @endif

            @if($firstDetail && !empty($firstDetail->chi_tiet_bai_kiem_tra))
                <div class="pxd-exam-list">
                    <div class="pxd-note-title mb-2"><i class="fas fa-file-pen"></i> Bài kiểm tra được chọn</div>
                    <div class="pxd-exam-tags">
                        @foreach(($firstDetail->chi_tiet_bai_kiem_tra ?? []) as $examRow)
                            <span class="pxd-exam-tag">
                                <i class="fas fa-file-pen"></i> {{ $examRow['tieu_de'] ?? 'Bài kiểm tra' }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ② Hành động xử lý phiếu --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-gavel"></i> Hành động xử lý</h2>
                    <p>Chuyển trạng thái, duyệt, từ chối hoặc chốt chính thức phiếu.</p>
                </div>
            </div>
        </header>

        <div class="pxd-action-card">
            @if(! $canApprove && ! $canFinalize && ! $canStartReview)
                <div class="pxd-no-action">
                    <i class="fas fa-circle-check"></i>
                    <div>
                        <strong>Phiếu đã được xử lý xong</strong>
                        <small>Không còn thao tác nào để thực hiện ở trạng thái hiện tại.</small>
                    </div>
                </div>
            @else
                <div class="pxd-action-grid">
                    @if($canStartReview)
                        <form method="POST" action="{{ route('admin.xet-duyet-ket-qua.reviewing', $phieu) }}" class="pxd-action-block tone-info">
                            @csrf
                            <div class="pxd-action-info">
                                <i class="fas fa-eye"></i>
                                <div>
                                    <strong>Bắt đầu xem phiếu</strong>
                                    <small>Đánh dấu phiếu đang được admin xem xét</small>
                                </div>
                            </div>
                            <button type="submit" class="pxd-btn-primary tone-info">
                                <i class="fas fa-eye"></i> Đang xem
                            </button>
                        </form>
                    @endif

                    @if($canApprove)
                        <form method="POST" action="{{ route('admin.xet-duyet-ket-qua.approve', $phieu) }}" class="pxd-action-block tone-primary">
                            @csrf
                            <div class="pxd-action-info">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <strong>Duyệt phiếu</strong>
                                    <small>Đồng ý cho phiếu đi tiếp đến bước chốt hồ sơ</small>
                                </div>
                            </div>
                            <button type="submit" class="pxd-btn-primary tone-primary">
                                <i class="fas fa-check"></i> Duyệt phiếu
                            </button>
                        </form>
                    @endif

                    @if($canFinalize)
                        <form method="POST" action="{{ route('admin.xet-duyet-ket-qua.finalize', $phieu) }}"
                              class="pxd-action-block tone-success"
                              onsubmit="return confirm('Chốt chính thức và ghi vào hồ sơ học tập? Hành động này khó hoàn tác.');">
                            @csrf
                            <div class="pxd-action-info">
                                <i class="fas fa-trophy"></i>
                                <div>
                                    <strong>Chốt chính thức</strong>
                                    <small>Ghi kết quả vĩnh viễn vào hồ sơ học tập</small>
                                </div>
                            </div>
                            <textarea name="ghi_chu_duyet" rows="2" class="form-control mb-2 pxd-textarea"
                                      placeholder="Ghi chú chốt hồ sơ (không bắt buộc)..."></textarea>
                            <button type="submit" class="pxd-btn-primary tone-success">
                                <i class="fas fa-trophy"></i> Chốt kết quả chính thức
                            </button>
                        </form>
                    @endif

                    @if($canApprove)
                        <form method="POST" action="{{ route('admin.xet-duyet-ket-qua.reject', $phieu) }}" class="pxd-action-block tone-danger">
                            @csrf
                            <div class="pxd-action-info">
                                <i class="fas fa-circle-xmark"></i>
                                <div>
                                    <strong>Từ chối &amp; trả về</strong>
                                    <small>Đưa phiếu quay lại GV để chỉnh sửa</small>
                                </div>
                            </div>
                            <textarea name="reject_reason" rows="2" class="form-control mb-2 pxd-textarea"
                                      placeholder="Lý do từ chối (bắt buộc)..." required></textarea>
                            <button type="submit" class="pxd-btn-primary tone-danger">
                                <i class="fas fa-rotate-left"></i> Từ chối &amp; trả về
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- ③ Danh sách học viên (compact) --}}
    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div>
                    <h2><i class="fas fa-list"></i> Chi tiết học viên</h2>
                    <p>Bấm vào dòng để xem điểm chi tiết bài kiểm tra của từng học viên.</p>
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

        <div class="pxd-toolbar">
            <form method="GET" class="pxd-toolbar-form">
                <div class="pxd-input-icon pxd-search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Tìm tên hoặc email...">
                </div>
                <select name="ket_qua" class="form-select pxd-select">
                    <option value="">Kết quả: Tất cả</option>
                    <option value="dat"        @selected($resultFilter === 'dat')>Đạt</option>
                    <option value="khong_dat"  @selected($resultFilter === 'khong_dat')>Chưa đạt</option>
                </select>
                <select name="sort" class="form-select pxd-select">
                    <option value="name"           @selected($sort === 'name')>Sắp xếp: A→Z</option>
                    <option value="xetduyet_desc"  @selected($sort === 'xetduyet_desc')>Điểm cao→thấp</option>
                    <option value="xetduyet_asc"   @selected($sort === 'xetduyet_asc')>Điểm thấp→cao</option>
                </select>
                <button type="submit" class="pxd-btn-filter">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                <a href="{{ route('admin.xet-duyet-ket-qua.show', $phieu) }}" class="pxd-btn-reset" title="Xóa lọc">
                    <i class="fas fa-rotate-left"></i>
                </a>
                <button type="button" class="pxd-btn-collapse-all" id="pxdCollapseAll" title="Thu gọn tất cả">
                    <i class="fas fa-compress"></i>
                </button>
            </form>
        </div>

        @if($pageRows->isEmpty())
            <div class="pxd-empty">
                <div class="pxd-empty-icon"><i class="fas fa-user-slash"></i></div>
                <h5>{{ $hasFilter ? 'Không tìm thấy học viên khớp bộ lọc' : 'Phiếu chưa có chi tiết học viên' }}</h5>
                <p>{{ $hasFilter ? 'Thử bỏ bộ lọc.' : 'GV cần thêm chi tiết học viên trước khi gửi duyệt.' }}</p>
            </div>
        @else
            <div class="pxd-list">
                <div class="pxd-row pxd-row-header">
                    <div class="pxd-col-stt">#</div>
                    <div class="pxd-col-name">Học viên</div>
                    <div class="pxd-col-score">Chuyên cần</div>
                    <div class="pxd-col-score">Kiểm tra</div>
                    <div class="pxd-col-score">Xét duyệt</div>
                    <div class="pxd-col-result">Kết quả</div>
                    <div class="pxd-col-action"></div>
                </div>

                @foreach($pageRows as $detail)
                    @php
                        $rowIndex = $rangeFrom + $loop->index;
                        $hocVien = $detail->hocVien;
                        $studentId = $hocVien?->ma_nguoi_dung ?? $loop->index;
                        $idColor = ($hocVien?->ma_nguoi_dung ?? $loop->index) % 6;
                        $gradients = [
                            'linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)',
                            'linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)',
                            'linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)',
                        ];
                        $initialHV = mb_strtoupper(mb_substr(trim($hocVien?->ho_ten ?? '?'), 0, 1, 'UTF-8'), 'UTF-8');

                        $resultClass = match($detail->ket_qua) {
                            'dat'       => 'is-success',
                            'khong_dat' => 'is-danger',
                            default     => 'is-secondary',
                        };
                        $resultIcon = match($detail->ket_qua) {
                            'dat'       => 'fa-check-circle',
                            'khong_dat' => 'fa-times-circle',
                            default     => 'fa-circle-question',
                        };
                        $resultLabel = $detail->ket_qua_label ?? '—';

                        $finalScore = $detail->diem_xet_duyet;
                        $finalScoreClass = '';
                        if ($finalScore !== null) {
                            $finalScoreClass = $finalScore >= (float) $phieu->diem_dat ? 'is-pass' : 'is-fail';
                        }
                    @endphp

                    <div class="pxd-row" data-row-id="row-{{ $studentId }}">
                        <div class="pxd-col-stt">{{ $rowIndex }}</div>
                        <div class="pxd-col-name">
                            <button type="button" class="pxd-row-toggle" onclick="pxdToggleRow('row-{{ $studentId }}')">
                                <div class="pxd-avatar" style="background: {{ $gradients[$idColor] }};">{{ $initialHV }}</div>
                                <div class="pxd-name-block">
                                    <div class="pxd-name">{{ $hocVien?->ho_ten ?? '—' }}</div>
                                    <div class="pxd-email">{{ $hocVien?->email ?? '—' }}</div>
                                </div>
                                <i class="fas fa-chevron-down pxd-row-arrow"></i>
                            </button>
                        </div>
                        <div class="pxd-col-score">
                            <div>{{ $detail->diem_chuyen_can !== null ? number_format((float) $detail->diem_chuyen_can, 2) : '—' }}</div>
                            <small class="pxd-col-score-sub">{{ $detail->so_buoi_tham_du }}/{{ $detail->tong_so_buoi }}</small>
                        </div>
                        <div class="pxd-col-score">
                            {{ $detail->diem_kiem_tra !== null ? number_format((float) $detail->diem_kiem_tra, 2) : '—' }}
                        </div>
                        <div class="pxd-col-score pxd-col-final {{ $finalScoreClass }}">
                            {{ $finalScore !== null ? number_format((float) $finalScore, 2) : '—' }}
                        </div>
                        <div class="pxd-col-result">
                            <span class="pxd-result-pill {{ $resultClass }}">
                                <i class="fas {{ $resultIcon }}"></i> {{ $resultLabel }}
                            </span>
                        </div>
                        <div class="pxd-col-action">
                            <button type="button" class="pxd-quick-btn" onclick="pxdToggleRow('row-{{ $studentId }}')">
                                Chi tiết <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pxd-detail" id="detail-row-{{ $studentId }}">
                        <div class="pxd-detail-head"><i class="fas fa-file-pen"></i> Chi tiết bài kiểm tra</div>
                        <div class="pxd-detail-body">
                            @if(empty($detail->chi_tiet_bai_kiem_tra))
                                <div class="pxd-drill-empty"><i class="fas fa-circle-info"></i> Phiếu này không lưu chi tiết bài kiểm tra cho học viên.</div>
                            @else
                                <div class="pxd-exam-grid">
                                    @foreach(($detail->chi_tiet_bai_kiem_tra ?? []) as $examRow)
                                        @php
                                            $examScore = $examRow['diem'] ?? null;
                                            $examTitle = $examRow['tieu_de'] ?? 'Bài kiểm tra';
                                        @endphp
                                        <div class="pxd-exam-item">
                                            <div class="pxd-exam-info">
                                                <i class="fas fa-file-pen"></i>
                                                <span>{{ $examTitle }}</span>
                                            </div>
                                            <strong>{{ $examScore !== null ? number_format((float) $examScore, 2) : '—' }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($totalPages > 1)
                <div class="pxd-pagination">
                    <a href="{{ $page > 1 ? route('admin.xet-duyet-ket-qua.show', array_merge(['phieu' => $phieu->id], $queryBase, ['page' => $page - 1])) : '#' }}"
                       class="pxd-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}">
                        <i class="fas fa-chevron-left"></i> Trước
                    </a>
                    @php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        if ($start > 1) echo '<span class="pxd-page-ellipsis">…</span>';
                    @endphp
                    @for($p = $start; $p <= $end; $p++)
                        @if($p === $page)
                            <span class="pxd-page-btn is-active">{{ $p }}</span>
                        @else
                            <a href="{{ route('admin.xet-duyet-ket-qua.show', array_merge(['phieu' => $phieu->id], $queryBase, ['page' => $p])) }}"
                               class="pxd-page-btn">{{ $p }}</a>
                        @endif
                    @endfor
                    @if($end < $totalPages)<span class="pxd-page-ellipsis">…</span>@endif
                    <a href="{{ $page < $totalPages ? route('admin.xet-duyet-ket-qua.show', array_merge(['phieu' => $phieu->id], $queryBase, ['page' => $page + 1])) : '#' }}"
                       class="pxd-page-btn {{ $page >= $totalPages ? 'is-disabled' : '' }}">
                        Sau <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            @endif
        @endif
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .pxd-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .pxd-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .pxd-page .apx-section-title h2 i { color: #dc2626; }
    .pxd-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .pxd-page .apx-meta-pill strong { color: #b91c1c; }

    .pxd-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .pxd-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .pxd-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .pxd-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .pxd-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
    }
    .pxd-status-pill i { font-size: 0.5rem; }
    .pxd-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .pxd-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .pxd-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .pxd-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .pxd-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
    .pxd-status-pill.is-primary   { background: #eff6ff; color: #1d4ed8; }
    .apx-welcome.pxd-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.pxd-welcome p i { color: #fef3c7; margin-right: 4px; }
    .pxd-sep { opacity: 0.5; }

    /* Info card */
    .pxd-info-card {
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 14px; padding: 18px 20px;
    }
    .pxd-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }
    .pxd-info-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 14px;
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
    }
    .pxd-info-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: grid; place-items: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .pxd-info-item small {
        display: block; font-size: 0.7rem;
        color: #64748b; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .pxd-info-item strong {
        display: block; font-size: 1.15rem;
        font-weight: 900; color: #0f172a;
        line-height: 1.1; margin-top: 2px;
    }

    .pxd-note {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 10px;
        border-left: 4px solid;
    }
    .pxd-note p { margin: 0; font-size: 0.86rem; line-height: 1.55; }
    .pxd-note-gv { background: #eff6ff; border-color: #1d4ed8; color: #1e3a8a; }
    .pxd-note-gv .pxd-note-title { color: #1d4ed8; }
    .pxd-note-reject { background: #fef2f2; border-color: #dc2626; color: #7f1d1d; }
    .pxd-note-reject .pxd-note-title { color: #dc2626; }
    .pxd-note-title {
        font-size: 0.78rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.4px;
        margin-bottom: 4px;
    }
    .pxd-note-title i { margin-right: 5px; }

    .pxd-exam-list {
        margin-top: 14px;
        padding: 12px 14px;
        background: #fafafa;
        border: 1px dashed #e2e8f0;
        border-radius: 10px;
    }
    .pxd-exam-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .pxd-exam-tag {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        background: #eff6ff; color: #1d4ed8;
        font-size: 0.78rem; font-weight: 700;
        border-radius: 999px;
    }
    .pxd-exam-tag i { font-size: 0.66rem; }

    /* Action card */
    .pxd-action-card {
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 14px; padding: 18px 20px;
    }
    .pxd-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
    }
    .pxd-action-block {
        padding: 14px;
        border: 1.5px solid;
        border-radius: 12px;
        margin: 0;
    }
    .pxd-action-block.tone-info    { border-color: #cffafe; background: linear-gradient(180deg, #ecfeff 0%, #ffffff 100%); }
    .pxd-action-block.tone-primary { border-color: #bfdbfe; background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%); }
    .pxd-action-block.tone-success { border-color: #a7f3d0; background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%); }
    .pxd-action-block.tone-danger  { border-color: #fecaca; background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%); }

    .pxd-action-info {
        display: flex; gap: 10px; align-items: center;
        margin-bottom: 12px;
    }
    .pxd-action-info > i {
        flex-shrink: 0;
        width: 38px; height: 38px;
        border-radius: 10px;
        display: grid; place-items: center;
        font-size: 1rem;
    }
    .pxd-action-block.tone-info    .pxd-action-info i { background: #cffafe; color: #0e7490; }
    .pxd-action-block.tone-primary .pxd-action-info i { background: #dbeafe; color: #1d4ed8; }
    .pxd-action-block.tone-success .pxd-action-info i { background: #dcfce7; color: #16a34a; }
    .pxd-action-block.tone-danger  .pxd-action-info i { background: #fee2e2; color: #dc2626; }
    .pxd-action-info strong { display: block; font-size: 0.92rem; font-weight: 800; color: #0f172a; }
    .pxd-action-info small { display: block; font-size: 0.74rem; color: #64748b; line-height: 1.4; }

    .pxd-textarea {
        font-size: 0.84rem; padding: 8px 12px;
        border-radius: 8px; border: 1px solid #e2e8f0;
    }
    .pxd-textarea:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .pxd-btn-primary {
        display: flex; align-items: center; justify-content: center; gap: 7px;
        width: 100%;
        padding: 9px 14px;
        font-size: 0.86rem; font-weight: 800;
        border: 0; border-radius: 8px; cursor: pointer;
        color: #fff;
        transition: all 0.18s ease;
    }
    .pxd-btn-primary.tone-info    { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); box-shadow: 0 4px 12px rgba(14,165,233,0.22); }
    .pxd-btn-primary.tone-primary { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); box-shadow: 0 4px 12px rgba(29,78,216,0.22); }
    .pxd-btn-primary.tone-success { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); box-shadow: 0 4px 12px rgba(22,163,74,0.22); }
    .pxd-btn-primary.tone-danger  { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.22); }
    .pxd-btn-primary:hover { transform: translateY(-1px); }

    .pxd-no-action {
        display: flex; align-items: center; gap: 14px;
        padding: 16px 20px;
        background: #f0fdf4;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
    }
    .pxd-no-action i { font-size: 2rem; color: #16a34a; }
    .pxd-no-action strong { display: block; font-size: 0.94rem; font-weight: 800; color: #166534; }
    .pxd-no-action small { display: block; font-size: 0.82rem; color: #047857; }

    /* Toolbar */
    .pxd-toolbar {
        position: sticky; top: 12px; z-index: 5;
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 12px; padding: 10px 12px;
        margin-bottom: 12px;
        box-shadow: 0 4px 14px rgba(15,23,42,0.06);
    }
    .pxd-toolbar-form { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin: 0; }
    .pxd-search-input { flex: 2 1 280px; }
    .pxd-input-icon { position: relative; }
    .pxd-input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem; pointer-events: none; }
    .pxd-input-icon input { padding-left: 34px; }
    .pxd-select { flex: 1 1 160px; min-width: 140px; }
    .pxd-btn-filter, .pxd-btn-reset, .pxd-btn-collapse-all {
        flex-shrink: 0;
        padding: 8px 14px;
        font-size: 0.84rem; font-weight: 800;
        border-radius: 8px; cursor: pointer;
        transition: all 0.18s ease; white-space: nowrap;
    }
    .pxd-btn-filter { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #fff; border: 0; }
    .pxd-btn-filter:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .pxd-btn-reset, .pxd-btn-collapse-all { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
    .pxd-btn-reset:hover, .pxd-btn-collapse-all:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

    /* Compact list */
    .pxd-list {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }
    .pxd-row {
        display: grid;
        grid-template-columns: 50px minmax(220px, 2.4fr) 100px 92px 100px 130px 110px;
        gap: 10px;
        align-items: center;
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s ease;
    }
    .pxd-row:hover { background: #fafafa; }
    .pxd-row.pxd-row-header {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem; font-weight: 800;
        color: #7f1d1d; text-transform: uppercase;
        letter-spacing: 0.4px;
        border-bottom: 1px solid #fecaca;
        padding: 12px 14px;
    }

    .pxd-col-stt { font-size: 0.78rem; color: #94a3b8; font-weight: 800; text-align: center; }
    .pxd-row-toggle {
        display: flex; align-items: center; gap: 10px;
        background: transparent; border: 0; padding: 0;
        text-align: left; cursor: pointer;
        width: 100%;
        font-family: inherit; font-size: inherit; color: inherit;
    }
    .pxd-avatar {
        flex-shrink: 0;
        width: 36px; height: 36px;
        border-radius: 50%;
        color: #fff;
        font-weight: 800; font-size: 0.92rem;
        display: grid; place-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .pxd-name-block { flex: 1; min-width: 0; }
    .pxd-name {
        font-size: 0.88rem; font-weight: 800; color: #0f172a;
        line-height: 1.3;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .pxd-email {
        font-size: 0.74rem; color: #64748b; font-weight: 600;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .pxd-row-arrow { color: #cbd5e1; font-size: 0.78rem; transition: transform 0.2s ease; }
    .pxd-row.is-open .pxd-row-arrow { transform: rotate(180deg); color: #dc2626; }

    .pxd-col-score {
        font-size: 0.92rem; font-weight: 800;
        color: #0f172a; text-align: center;
        font-variant-numeric: tabular-nums;
    }
    .pxd-col-score-sub {
        display: block;
        font-size: 0.7rem; color: #94a3b8;
        font-weight: 600;
    }
    .pxd-col-final {
        font-size: 1.05rem; font-weight: 900;
    }
    .pxd-col-final.is-pass { color: #16a34a; }
    .pxd-col-final.is-fail { color: #dc2626; }

    .pxd-col-result { text-align: center; }
    .pxd-result-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .pxd-result-pill i { font-size: 0.6rem; }
    .pxd-result-pill.is-success   { background: #dcfce7; color: #166534; }
    .pxd-result-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .pxd-result-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .pxd-col-action { text-align: right; }
    .pxd-quick-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.78rem; font-weight: 700;
        border-radius: 8px; cursor: pointer;
        transition: all 0.18s ease;
    }
    .pxd-quick-btn:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .pxd-quick-btn i { font-size: 0.7rem; transition: transform 0.2s ease; }
    .pxd-row.is-open + .pxd-detail .pxd-quick-btn i { transform: rotate(180deg); }

    /* Detail */
    .pxd-detail {
        display: none;
        padding: 12px 18px 14px;
        background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%);
        border-bottom: 1px solid #f1f5f9;
    }
    .pxd-detail.is-open { display: block; }
    .pxd-detail-head {
        font-size: 0.84rem; font-weight: 800;
        color: #0f172a; margin-bottom: 8px;
    }
    .pxd-detail-head i { color: #dc2626; margin-right: 5px; }

    .pxd-exam-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 8px;
    }
    .pxd-exam-item {
        display: flex; justify-content: space-between; align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    .pxd-exam-info {
        display: flex; align-items: center; gap: 7px;
        font-size: 0.82rem; color: #475569; font-weight: 700;
        min-width: 0;
    }
    .pxd-exam-info i { color: #1d4ed8; font-size: 0.72rem; flex-shrink: 0; }
    .pxd-exam-info span {
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .pxd-exam-item strong {
        font-size: 1rem; font-weight: 900; color: #1d4ed8;
        flex-shrink: 0;
    }

    .pxd-drill-empty {
        padding: 16px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.85rem;
        background: #fff;
        border: 1px dashed #e2e8f0;
        border-radius: 8px;
    }
    .pxd-drill-empty i { color: #1d4ed8; margin-right: 5px; }

    /* Pagination */
    .pxd-pagination {
        display: flex; gap: 4px; flex-wrap: wrap; justify-content: center;
        padding: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-top: 0;
        border-radius: 0 0 14px 14px;
        margin-top: -1px;
    }
    .pxd-page-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 12px;
        background: #fff; border: 1px solid #e2e8f0;
        color: #475569; font-size: 0.84rem; font-weight: 700;
        border-radius: 8px; text-decoration: none;
        transition: all 0.18s ease;
        min-width: 36px; justify-content: center;
    }
    .pxd-page-btn:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .pxd-page-btn.is-active {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border-color: #dc2626; color: #fff;
        box-shadow: 0 4px 12px rgba(220,38,38,0.25);
    }
    .pxd-page-btn.is-disabled { opacity: 0.4; pointer-events: none; }
    .pxd-page-ellipsis { color: #94a3b8; font-weight: 800; padding: 0 6px; align-self: center; }

    /* Empty */
    .pxd-empty { padding: 60px 30px; text-align: center; background: #fff; border: 1px dashed #fecaca; border-radius: 14px; }
    .pxd-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .pxd-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .pxd-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }

    /* Responsive */
    @media (max-width: 991.98px) {
        .pxd-row {
            grid-template-columns: 40px 1fr 90px 90px;
            grid-template-areas:
                "stt name name action"
                "stt scores scores scores"
                "stt result result result";
            gap: 6px 10px;
        }
        .pxd-row-header { display: none; }
        .pxd-col-stt { grid-area: stt; align-self: start; }
        .pxd-col-name { grid-area: name; }
        .pxd-col-action { grid-area: action; }
        .pxd-col-score:nth-of-type(3),
        .pxd-col-score:nth-of-type(4) { display: none; }
        .pxd-col-final { display: block !important; grid-area: scores; text-align: left; }
        .pxd-col-result { grid-area: result; text-align: left; }
    }
</style>

<script>
function pxdToggleRow(rowId) {
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

document.getElementById('pxdCollapseAll')?.addEventListener('click', function () {
    document.querySelectorAll('.pxd-detail.is-open').forEach(d => d.classList.remove('is-open'));
    document.querySelectorAll('.pxd-row.is-open').forEach(r => r.classList.remove('is-open'));
});
</script>
@endsection
