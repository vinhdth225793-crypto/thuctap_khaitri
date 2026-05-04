@extends('layouts.app')

@section('title', 'Đơn xin nghỉ giảng viên')

@section('content')
@php
    $baseQuery = \App\Models\GiangVienDonXinNghi::query();
    $stats = [
        'tong'      => (clone $baseQuery)->count(),
        'cho_duyet' => (clone $baseQuery)->where('trang_thai', 'cho_duyet')->count(),
        'da_duyet'  => (clone $baseQuery)->where('trang_thai', 'da_duyet')->count(),
        'tu_choi'   => (clone $baseQuery)->where('trang_thai', 'tu_choi')->count(),
    ];
    $hasFilter = !empty($filters['trang_thai']) || !empty($filters['giang_vien_id']);
@endphp

<div class="container-fluid admin-page-x dxng-page">
    <div class="apx-welcome dxng-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-calendar-xmark"></i></div>
        <div class="apx-welcome-text">
            <div class="dxng-tag-row">
                <span class="dxng-loai-badge">
                    <i class="fas fa-clipboard-list"></i> ĐƠN XIN NGHỈ GIẢNG VIÊN
                </span>
                <span class="dxng-status-badge">
                    <i class="fas fa-layer-group"></i> {{ $stats['tong'] }} đơn
                </span>
                @if($stats['cho_duyet'] > 0)
                    <span class="dxng-pending-badge">
                        <i class="fas fa-hourglass-half"></i> {{ $stats['cho_duyet'] }} chờ duyệt
                    </span>
                @endif
            </div>
            <h4>Quản lý đơn xin nghỉ giảng viên</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $stats['da_duyet'] }} đã duyệt</span>
                <span class="dxng-sep">·</span>
                <span><i class="fas fa-times-circle"></i> {{ $stats['tu_choi'] }} từ chối</span>
                <span class="dxng-sep">·</span>
                <span><i class="fas fa-pen"></i> Duyệt và theo dõi buổi học cần xử lý sau khi duyệt</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan đơn</h2>
                    <p>Bốn chỉ số nhanh giúp admin nắm hàng đợi.</p>
                </div>
            </div>
        </header>
        <div class="row g-3">
            <div class="col-md-3 col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-clipboard-list"></i></div><div class="aps-text"><strong>{{ $stats['tong'] }}</strong><small>Tổng đơn</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-warning"><div class="aps-icon"><i class="fas fa-hourglass-half"></i></div><div class="aps-text"><strong>{{ $stats['cho_duyet'] }}</strong><small>Chờ duyệt</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-success"><div class="aps-icon"><i class="fas fa-circle-check"></i></div><div class="aps-text"><strong>{{ $stats['da_duyet'] }}</strong><small>Đã duyệt</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-danger"><div class="aps-icon"><i class="fas fa-times-circle"></i></div><div class="aps-text"><strong>{{ $stats['tu_choi'] }}</strong><small>Từ chối</small></div></div></div>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div><h2><i class="fas fa-filter"></i> Bộ lọc</h2><p>Lọc theo giảng viên hoặc trạng thái duyệt.</p></div>
            </div>
            @if($hasFilter)
                <div class="apx-section-meta"><span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;"><i class="fas fa-filter"></i> Đang lọc</span></div>
            @endif
        </header>
        <div class="dxng-filter-card">
            <form method="GET" action="{{ route('admin.giang-vien-don-xin-nghi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="dxng-flabel">Giảng viên</label>
                    <select name="giang_vien_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string)($filters['giang_vien_id']??'') === (string)$teacher->id)>{{ $teacher->nguoiDung?->ho_ten }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="dxng-flabel">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="cho_duyet" @selected(($filters['trang_thai']??null)==='cho_duyet')>Chờ duyệt</option>
                        <option value="da_duyet"  @selected(($filters['trang_thai']??null)==='da_duyet')>Đã duyệt</option>
                        <option value="tu_choi"   @selected(($filters['trang_thai']??null)==='tu_choi')>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold flex-fill"><i class="fas fa-search me-1"></i> Lọc</button>
                    <a href="{{ route('admin.giang-vien-don-xin-nghi.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">3</span>
                <div><h2><i class="fas fa-list"></i> Danh sách đơn</h2><p>Bấm "Mở chi tiết" để xem đơn và quyết định duyệt / từ chối.</p></div>
            </div>
            <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ $leaveRequests->total() }}</strong> đơn</span></div>
        </header>

        <div class="dxng-table-wrap">
            @if($leaveRequests->isEmpty())
                <div class="dxng-empty">
                    <div class="dxng-empty-icon"><i class="fas fa-inbox"></i></div>
                    <h5>Chưa có đơn xin nghỉ nào</h5>
                    <p>Đơn sẽ xuất hiện ở đây khi giảng viên gửi yêu cầu xin nghỉ buổi học.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 dxng-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Giảng viên</th>
                                <th>Ngày nghỉ</th>
                                <th>Khung nghỉ</th>
                                <th>Khóa / Module</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4 text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveRequests as $item)
                                @php
                                    $statusClass = match($item->trang_thai){'da_duyet'=>'is-success','cho_duyet'=>'is-warning','tu_choi'=>'is-danger',default=>'is-secondary'};
                                    $statusIcon  = match($item->trang_thai){'da_duyet'=>'fa-check-circle','cho_duyet'=>'fa-hourglass-half','tu_choi'=>'fa-times-circle',default=>'fa-circle-question'};
                                    $tenGV = $item->giangVien?->nguoiDung?->ho_ten ?? '?';
                                    $idColor = ($item->giang_vien_id ?? $item->id) % 6;
                                    $gradients = ['linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)','linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)','linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)'];
                                    $initial = mb_strtoupper(mb_substr(trim($tenGV), 0, 1, 'UTF-8'), 'UTF-8');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="dxng-avatar" style="background: {{ $gradients[$idColor] }};">{{ $initial ?: '?' }}</div>
                                            <div>
                                                <div class="dxng-name">{{ $tenGV }}</div>
                                                <div class="dxng-name-sub"><i class="fas fa-briefcase"></i> {{ $item->giangVien?->chuyen_nganh ?: 'Chưa cập nhật' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dxng-date"><i class="far fa-calendar-alt"></i> {{ $item->ngay_xin_nghi?->format('d/m/Y') }}</div>
                                        <div class="dxng-date-sub">{{ $item->ngay_xin_nghi?->isoFormat('dddd') ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="dxng-frame">{{ $item->schedule_range_label }}</div>
                                        <div class="dxng-frame-sub"><i class="fas fa-clock"></i> {{ $item->tiet_range_label }}</div>
                                    </td>
                                    <td>
                                        <div class="dxng-course"><i class="fas fa-graduation-cap"></i> {{ $item->khoaHoc?->ma_khoa_hoc ?: ($item->lichHoc?->khoaHoc?->ma_khoa_hoc ?? 'Không gắn buổi') }}</div>
                                        @if($item->moduleHoc?->ten_module || $item->lichHoc?->moduleHoc?->ten_module)
                                            <div class="dxng-module"><i class="fas fa-cube"></i> {{ $item->moduleHoc?->ten_module ?? $item->lichHoc?->moduleHoc?->ten_module }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="dxng-status-pill {{ $statusClass }}">
                                            <i class="fas {{ $statusIcon }}"></i> {{ $item->trang_thai_label }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('admin.giang-vien-don-xin-nghi.show', $item->id) }}" class="dxng-detail-btn">
                                            <i class="fas fa-magnifying-glass"></i> Mở chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($leaveRequests->hasPages())
                    <div class="dxng-pagination">{{ $leaveRequests->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .dxng-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .dxng-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .dxng-page .apx-section-title h2 i { color: #dc2626; }
    .dxng-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .dxng-page .apx-meta-pill strong { color: #b91c1c; }

    .dxng-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .dxng-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .dxng-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .dxng-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .dxng-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: dxngPulse 1.6s ease-out infinite; }
    @keyframes dxngPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.dxng-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.dxng-welcome p i { color: #fef3c7; margin-right: 4px; }
    .dxng-sep { opacity: 0.5; }

    .dxng-filter-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; }
    .dxng-flabel { display: block; font-weight: 800; color: #7f1d1d; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }

    .dxng-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
    .dxng-table thead { background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%); font-size: 0.7rem; text-transform: uppercase; color: #7f1d1d; letter-spacing: 0.5px; }
    .dxng-table thead th { padding: 12px 10px; font-weight: 800; border-bottom: 1px solid #fecaca; }
    .dxng-table tbody td { padding: 14px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .dxng-table tbody tr:last-child td { border-bottom: 0; }
    .dxng-table tbody tr:hover { background: #fafafa; }

    .dxng-avatar { flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; color: #fff; font-weight: 800; font-size: 0.92rem; display: grid; place-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .dxng-name { font-size: 0.88rem; font-weight: 800; color: #0f172a; }
    .dxng-name-sub { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .dxng-name-sub i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .dxng-date { font-size: 0.88rem; font-weight: 800; color: #0f172a; }
    .dxng-date i { color: #dc2626; margin-right: 5px; font-size: 0.74rem; }
    .dxng-date-sub { font-size: 0.72rem; color: #94a3b8; font-weight: 600; margin-top: 2px; text-transform: capitalize; }

    .dxng-frame { font-size: 0.84rem; font-weight: 700; color: #0f172a; }
    .dxng-frame-sub { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .dxng-frame-sub i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .dxng-course { font-size: 0.84rem; font-weight: 700; color: #0f172a; }
    .dxng-course i { color: #1d4ed8; margin-right: 5px; font-size: 0.74rem; }
    .dxng-module { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .dxng-module i { color: #16a34a; margin-right: 4px; font-size: 0.66rem; }

    .dxng-status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; font-size: 0.72rem; font-weight: 800; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; }
    .dxng-status-pill i { font-size: 0.62rem; }
    .dxng-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .dxng-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .dxng-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .dxng-status-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .dxng-detail-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #fff; font-size: 0.78rem; font-weight: 800; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 12px rgba(220,38,38,0.22); transition: all 0.2s ease; }
    .dxng-detail-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(220,38,38,0.32); color: #fff; }

    .dxng-pagination { padding: 14px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; }
    .dxng-pagination nav { margin: 0; }

    .dxng-empty { padding: 60px 30px; text-align: center; }
    .dxng-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .dxng-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .dxng-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }
</style>
@endsection
