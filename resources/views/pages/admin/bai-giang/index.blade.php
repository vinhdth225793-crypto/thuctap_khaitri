@extends('layouts.app')

@section('title', 'Phê duyệt bài giảng')

@section('content')
@php
    $baseQuery = \App\Models\BaiGiang::query();
    $stats = [
        'tong'      => (clone $baseQuery)->count(),
        'cho_duyet' => (clone $baseQuery)->where('trang_thai_duyet', 'cho_duyet')->count(),
        'da_duyet'  => (clone $baseQuery)->where('trang_thai_duyet', 'da_duyet')->count(),
        'cong_bo'   => (clone $baseQuery)->where('trang_thai_cong_bo', 'da_cong_bo')->count(),
    ];
    $hasFilter = request()->filled('trang_thai_duyet') || request()->filled('loai_bai_giang');
@endphp

<div class="container-fluid admin-page-x bgad-page">
    <div class="apx-welcome bgad-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="apx-welcome-text">
            <div class="bgad-tag-row">
                <span class="bgad-loai-badge"><i class="fas fa-shield-halved"></i> PHÊ DUYỆT BÀI GIẢNG</span>
                <span class="bgad-status-badge"><i class="fas fa-layer-group"></i> {{ $stats['tong'] }} bài</span>
                @if($stats['cho_duyet'] > 0)
                    <span class="bgad-pending-badge"><i class="fas fa-hourglass-half"></i> {{ $stats['cho_duyet'] }} chờ duyệt</span>
                @endif
            </div>
            <h4>Quản lý bài giảng &amp; phòng học live</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $stats['da_duyet'] }} đã duyệt</span>
                <span class="bgad-sep">·</span>
                <span><i class="fas fa-eye"></i> {{ $stats['cong_bo'] }} đang công bố</span>
                <span class="bgad-sep">·</span>
                <span><i class="fas fa-video"></i> Bao gồm bài thường và phòng học live</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle"><i class="fas fa-arrow-left"></i> <span>Dashboard</span></a>
            <a href="{{ route('admin.bai-giang.create') }}" class="btn btn-light text-primary fw-bold shadow-sm bgad-create-btn"><i class="fas fa-plus me-1"></i> Tạo bài giảng</a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">1</span><div><h2><i class="fas fa-chart-pie"></i> Tổng quan bài giảng</h2><p>Bốn chỉ số nhanh để admin theo dõi.</p></div></div>
        </header>
        <div class="row g-3">
            <div class="col-md-3 col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-chalkboard"></i></div><div class="aps-text"><strong>{{ $stats['tong'] }}</strong><small>Tổng bài giảng</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-warning"><div class="aps-icon"><i class="fas fa-hourglass-half"></i></div><div class="aps-text"><strong>{{ $stats['cho_duyet'] }}</strong><small>Chờ duyệt</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-success"><div class="aps-icon"><i class="fas fa-circle-check"></i></div><div class="aps-text"><strong>{{ $stats['da_duyet'] }}</strong><small>Đã duyệt</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-info"><div class="aps-icon"><i class="fas fa-eye"></i></div><div class="aps-text"><strong>{{ $stats['cong_bo'] }}</strong><small>Đang công bố</small></div></div></div>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">2</span><div><h2><i class="fas fa-filter"></i> Bộ lọc</h2><p>Lọc theo trạng thái duyệt và loại bài giảng.</p></div></div>
            @if($hasFilter)<div class="apx-section-meta"><span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;"><i class="fas fa-filter"></i> Đang lọc</span></div>@endif
        </header>
        <div class="bgad-filter-card">
            <form action="{{ route('admin.bai-giang.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="bgad-flabel">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả</option>
                        <option value="cho_duyet"     @selected(request('trang_thai_duyet')=='cho_duyet')>Chờ duyệt</option>
                        <option value="da_duyet"      @selected(request('trang_thai_duyet')=='da_duyet')>Đã duyệt</option>
                        <option value="can_chinh_sua" @selected(request('trang_thai_duyet')=='can_chinh_sua')>Cần chỉnh sửa</option>
                        <option value="tu_choi"       @selected(request('trang_thai_duyet')=='tu_choi')>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="bgad-flabel">Loại bài giảng</label>
                    <select name="loai_bai_giang" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả</option>
                        <option value="live"     @selected(request('loai_bai_giang')=='live')>Trực tuyến (Live)</option>
                        <option value="tai_lieu" @selected(request('loai_bai_giang')=='tai_lieu')>Tài liệu</option>
                        <option value="video"    @selected(request('loai_bai_giang')=='video')>Video</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('admin.bai-giang.index') }}" class="btn btn-outline-secondary fw-bold"><i class="fas fa-rotate-left me-1"></i> Đặt lại</a>
                </div>
            </form>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">3</span><div><h2><i class="fas fa-list"></i> Danh sách bài giảng</h2><p>Xem chi tiết, sửa và công bố cho học viên.</p></div></div>
            <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ $baiGiangs->total() }}</strong> bài</span></div>
        </header>

        <div class="bgad-table-wrap">
            @if($baiGiangs->isEmpty())
                <div class="bgad-empty">
                    <div class="bgad-empty-icon"><i class="fas fa-folder-open"></i></div>
                    <h5>Không có bài giảng nào cần xử lý</h5>
                    <p>Bài giảng sẽ xuất hiện khi giảng viên gửi lên hoặc khi admin tạo mới.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 bgad-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Bài giảng</th>
                                <th>Người tạo</th>
                                <th>Khóa / Module</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Công bố</th>
                                <th class="pe-4 text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($baiGiangs as $bg)
                                @php
                                    $statusLabels = ['cho_duyet'=>'Chờ duyệt','da_duyet'=>'Đã duyệt','can_chinh_sua'=>'Cần sửa','tu_choi'=>'Từ chối'];
                                    $statusClass  = ['cho_duyet'=>'is-warning','da_duyet'=>'is-success','can_chinh_sua'=>'is-info','tu_choi'=>'is-danger'];
                                    $statusIcon   = ['cho_duyet'=>'fa-hourglass-half','da_duyet'=>'fa-check-circle','can_chinh_sua'=>'fa-pen-to-square','tu_choi'=>'fa-times-circle'];
                                    $sClass = $statusClass[$bg->trang_thai_duyet] ?? 'is-secondary';
                                    $sIcon  = $statusIcon[$bg->trang_thai_duyet] ?? 'fa-pen';
                                    $sLabel = $statusLabels[$bg->trang_thai_duyet] ?? $bg->trang_thai_duyet;
                                    $loaiIcon = match($bg->loai_bai_giang){'live'=>'fa-video','tai_lieu','document'=>'fa-file-lines','video'=>'fa-play-circle',default=>'fa-book-open'};
                                    $idColor = $bg->id % 6;
                                    $gradients = ['linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)','linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)','linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)'];
                                    $tenND = $bg->nguoiTao->ho_ten ?? 'N/A';
                                    $initial = mb_strtoupper(mb_substr(trim($tenND), 0, 1, 'UTF-8'), 'UTF-8');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="bgad-thumb" style="background: {{ $gradients[$idColor] }};">
                                                <i class="fas {{ $loaiIcon }}"></i>
                                            </div>
                                            <div>
                                                <div class="bgad-name">{{ $bg->tieu_de }}</div>
                                                <div class="bgad-tags">
                                                    <span class="bgad-loai-pill"><i class="fas {{ $loaiIcon }}"></i> {{ str_replace('_', ' ', $bg->loai_bai_giang) }}</span>
                                                    @if($bg->isLive() && $bg->phongHocLive)
                                                        <span class="bgad-platform"><i class="fas fa-broadcast-tower"></i> {{ $bg->phongHocLive->platform_label }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bgad-avatar" style="background: {{ $gradients[(($bg->nguoi_tao_id ?? 0)) % 6] }};">{{ $initial ?: '?' }}</div>
                                            <div class="bgad-uploader">{{ $tenND }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="bgad-course">{{ $bg->moduleHoc->ten_module ?? 'N/A' }}</div>
                                        <div class="bgad-course-sub"><i class="fas fa-graduation-cap"></i> {{ $bg->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="bgad-status-pill {{ $sClass }}"><i class="fas {{ $sIcon }}"></i> {{ $sLabel }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($bg->trang_thai_cong_bo === 'da_cong_bo')
                                            <span class="bgad-status-pill is-success"><i class="fas fa-eye"></i> Đã công bố</span>
                                        @else
                                            <span class="bgad-status-pill is-secondary"><i class="fas fa-eye-slash"></i> Đang ẩn</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="bgad-actions">
                                            <a href="{{ route('admin.bai-giang.show', $bg->id) }}" class="bgad-action-btn" title="Xem"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('admin.bai-giang.edit', $bg->id) }}" class="bgad-action-btn dark" title="Sửa"><i class="fas fa-edit"></i></a>
                                            @if($bg->trang_thai_duyet === 'da_duyet')
                                                <form action="{{ route('admin.bai-giang.cong-bo', $bg->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="bgad-action-btn {{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'warning' : 'success' }}" title="{{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'Ẩn' : 'Công bố' }}">
                                                        <i class="fas {{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'fa-eye-slash' : 'fa-paper-plane' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($baiGiangs->hasPages())
                    <div class="bgad-pagination">{{ $baiGiangs->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .bgad-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .bgad-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .bgad-page .apx-section-title h2 i { color: #dc2626; }
    .bgad-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .bgad-page .apx-meta-pill strong { color: #b91c1c; }

    .bgad-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .bgad-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .bgad-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .bgad-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .bgad-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: bgadPulse 1.6s ease-out infinite; }
    @keyframes bgadPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.bgad-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.bgad-welcome p i { color: #fef3c7; margin-right: 4px; }
    .bgad-sep { opacity: 0.5; }
    .bgad-create-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    .bgad-filter-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; }
    .bgad-flabel { display: block; font-weight: 800; color: #7f1d1d; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }

    .bgad-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
    .bgad-table thead { background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%); font-size: 0.7rem; text-transform: uppercase; color: #7f1d1d; letter-spacing: 0.5px; }
    .bgad-table thead th { padding: 12px 10px; font-weight: 800; border-bottom: 1px solid #fecaca; }
    .bgad-table tbody td { padding: 14px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .bgad-table tbody tr:last-child td { border-bottom: 0; }
    .bgad-table tbody tr:hover { background: #fafafa; }

    .bgad-thumb { flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px; color: #fff; display: grid; place-items: center; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .bgad-name { font-size: 0.92rem; font-weight: 800; color: #0f172a; line-height: 1.3; margin-bottom: 5px; }
    .bgad-tags { display: flex; flex-wrap: wrap; gap: 5px; }
    .bgad-loai-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: #eff6ff; color: #1d4ed8; font-size: 0.7rem; font-weight: 800; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; }
    .bgad-loai-pill i { font-size: 0.6rem; }
    .bgad-platform { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: #ecfdf5; color: #047857; font-size: 0.7rem; font-weight: 700; border-radius: 999px; }
    .bgad-platform i { font-size: 0.6rem; }

    .bgad-avatar { flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; color: #fff; font-weight: 800; font-size: 0.82rem; display: grid; place-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .bgad-uploader { font-size: 0.84rem; font-weight: 700; color: #0f172a; }

    .bgad-course { font-size: 0.85rem; font-weight: 700; color: #0f172a; }
    .bgad-course-sub { font-size: 0.72rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .bgad-course-sub i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .bgad-status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; font-size: 0.72rem; font-weight: 800; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; }
    .bgad-status-pill i { font-size: 0.62rem; }
    .bgad-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .bgad-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .bgad-status-pill.is-info      { background: #cffafe; color: #0e7490; }
    .bgad-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .bgad-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .bgad-actions { display: inline-flex; gap: 4px; }
    .bgad-action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; color: #1d4ed8; display: grid; place-items: center; cursor: pointer; font-size: 0.78rem; text-decoration: none; transition: all 0.18s ease; padding: 0; }
    .bgad-action-btn:hover { border-color: #1d4ed8; background: #1d4ed8; color: #fff; transform: translateY(-1px); }
    .bgad-action-btn.dark { color: #1e293b; }
    .bgad-action-btn.dark:hover { background: #1e293b; border-color: #1e293b; color: #fff; }
    .bgad-action-btn.success { color: #16a34a; }
    .bgad-action-btn.success:hover { background: #16a34a; border-color: #16a34a; color: #fff; }
    .bgad-action-btn.warning { color: #d97706; }
    .bgad-action-btn.warning:hover { background: #d97706; border-color: #d97706; color: #fff; }

    .bgad-pagination { padding: 14px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; }
    .bgad-pagination nav { margin: 0; }

    .bgad-empty { padding: 60px 30px; text-align: center; }
    .bgad-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .bgad-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .bgad-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }
</style>
@endsection
