@extends('layouts.app')

@section('title', 'Quản lý yêu cầu thay đổi học viên')

@section('content')
@php
    $tongYC = $yeuCaus->count();
    $choDuyet = $yeuCaus->where('trang_thai', 'cho_duyet')->count();
    $daDuyet  = $yeuCaus->where('trang_thai', 'da_duyet')->count();
    $tuChoi   = $yeuCaus->where('trang_thai', 'tu_choi')->count();
@endphp

<div class="container-fluid admin-page-x ychv-page">
    <div class="apx-welcome ychv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-user-edit"></i></div>
        <div class="apx-welcome-text">
            <div class="ychv-tag-row">
                <span class="ychv-loai-badge">
                    <i class="fas fa-user-plus"></i> YÊU CẦU HỌC VIÊN
                </span>
                <span class="ychv-status-badge">
                    <i class="fas fa-layer-group"></i> {{ $tongYC }} yêu cầu
                </span>
                @if($choDuyet > 0)
                    <span class="ychv-pending-badge">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $choDuyet }} chờ duyệt
                    </span>
                @endif
            </div>
            <h4>Yêu cầu liên quan đến học viên</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $daDuyet }} đã duyệt</span>
                <span class="ychv-sep">·</span>
                <span><i class="fas fa-times-circle"></i> {{ $tuChoi }} từ chối</span>
                <span class="ychv-sep">·</span>
                <span><i class="fas fa-pen"></i> Yêu cầu thêm/xin vào lớp/thay đổi học viên</span>
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
                    <h2><i class="fas fa-chart-pie"></i> Tổng quan yêu cầu</h2>
                    <p>Bốn chỉ số nhanh giúp admin nắm hàng đợi.</p>
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-primary">
                    <div class="aps-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="aps-text"><strong>{{ $tongYC }}</strong><small>Tổng yêu cầu</small></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-warning">
                    <div class="aps-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="aps-text"><strong>{{ $choDuyet }}</strong><small>Chờ admin duyệt</small></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-success">
                    <div class="aps-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="aps-text"><strong>{{ $daDuyet }}</strong><small>Đã duyệt</small></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="apx-stat tone-danger">
                    <div class="aps-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="aps-text"><strong>{{ $tuChoi }}</strong><small>Từ chối</small></div>
                </div>
            </div>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">2</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách yêu cầu</h2>
                    <p>Yêu cầu chờ xử lý hiển thị đầu danh sách. Bấm "Xử lý" để duyệt/từ chối.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <span class="apx-meta-pill"><strong>{{ $tongYC }}</strong> yêu cầu</span>
            </div>
        </header>

        <div class="ychv-table-wrap">
            @if($yeuCaus->isEmpty())
                <div class="ychv-empty">
                    <div class="ychv-empty-icon"><i class="fas fa-inbox"></i></div>
                    <h5>Hiện chưa có yêu cầu nào</h5>
                    <p>Yêu cầu sẽ xuất hiện ở đây khi giảng viên hoặc học viên gửi đơn cần admin xử lý.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 ychv-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Thời gian</th>
                                <th>Người gửi</th>
                                <th>Khóa học</th>
                                <th>Loại</th>
                                <th>Nội dung yêu cầu</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4 text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($yeuCaus as $yc)
                                @php
                                    $loaiClass = $yc->loai_yeu_cau === 'them' ? 'is-success' : ($yc->loai_yeu_cau === 'xoa' ? 'is-danger' : 'is-warning');
                                    $loaiIcon  = $yc->loai_yeu_cau === 'them' ? 'fa-user-plus' : ($yc->loai_yeu_cau === 'xoa' ? 'fa-user-minus' : 'fa-user-pen');
                                    $idColor = $yc->id % 6;
                                    $gradients = [
                                        'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                                        'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                                        'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                                        'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                                        'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                                        'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                                    ];
                                    $tenGui = $yc->nguoi_gui_ten ?? '?';
                                    $initialGui = mb_strtoupper(mb_substr(trim($tenGui), 0, 1, 'UTF-8'), 'UTF-8');
                                    $data = is_array($yc->du_lieu_yeu_cau) ? $yc->du_lieu_yeu_cau : json_decode($yc->du_lieu_yeu_cau, true);
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="ychv-time"><i class="far fa-calendar-alt"></i> {{ $yc->created_at->format('d/m/Y') }}</div>
                                        <div class="ychv-time-sub">{{ $yc->created_at->format('H:i') }} · {{ $yc->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="ychv-avatar" style="background: {{ $gradients[$idColor] }};">
                                                {{ $initialGui ?: '?' }}
                                            </div>
                                            <div>
                                                <span class="ychv-source-tag"><i class="fas fa-tag"></i> {{ $yc->nguon_yeu_cau_label }}</span>
                                                <div class="ychv-name">{{ $tenGui }}</div>
                                                <div class="ychv-name-sub">{{ $yc->nguoi_gui_mo_ta }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ychv-course" title="{{ $yc->khoaHoc->ten_khoa_hoc }}">{{ $yc->khoaHoc->ten_khoa_hoc }}</div>
                                        <div class="ychv-course-sub"><i class="fas fa-graduation-cap"></i> {{ $yc->khoaHoc->ma_khoa_hoc }}</div>
                                    </td>
                                    <td>
                                        <span class="ychv-type-pill {{ $loaiClass }}">
                                            <i class="fas {{ $loaiIcon }}"></i> {{ $yc->loai_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="ychv-content">
                                            @if($yc->hoc_vien_id)
                                                <strong>Học viên:</strong> {{ $data['ten'] ?? $yc->hocVienNguoiDung?->ho_ten ?? 'N/A' }}
                                                <div class="ychv-content-sub"><i class="fas fa-envelope"></i> {{ $data['email'] ?? $yc->hocVienNguoiDung?->email ?? 'N/A' }}</div>
                                            @elseif($yc->loai_yeu_cau === 'them')
                                                <strong>HV:</strong> {{ $data['ten'] ?? 'N/A' }}
                                                <div class="ychv-content-sub"><i class="fas fa-envelope"></i> {{ $data['email'] ?? 'N/A' }}</div>
                                            @else
                                                <strong>Mã HV:</strong> #{{ $data['id'] ?? 'N/A' }}
                                            @endif
                                            <div class="ychv-reason">
                                                <i class="fas fa-comment-dots"></i> Lý do: {{ $yc->ly_do }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $yc->trang_thai_badge }} px-3 py-2">
                                            {{ $yc->trang_thai_label }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if($yc->trang_thai === 'cho_duyet')
                                            <button class="ychv-process-btn" data-bs-toggle="modal" data-bs-target="#modalApprove{{ $yc->id }}">
                                                <i class="fas fa-gavel"></i> Xử lý
                                            </button>

                                            <div class="modal fade" id="modalApprove{{ $yc->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content border-0">
                                                        <div class="modal-header ychv-modal-head">
                                                            <h5 class="modal-title fw-bold"><i class="fas fa-gavel me-2"></i> Xử lý yêu cầu #{{ $yc->id }}</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.yeu-cau-hoc-vien.xac-nhan', $yc->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body p-4">
                                                                <div class="mb-3">
                                                                    <label class="ychv-flabel">Hành động *</label>
                                                                    <select name="hanh_dong" class="form-select" required>
                                                                        <option value="da_duyet">Chấp nhận yêu cầu</option>
                                                                        <option value="tu_choi">Từ chối yêu cầu</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-0">
                                                                    <label class="ychv-flabel">Phản hồi của Admin</label>
                                                                    <textarea name="phan_hoi" class="form-control" rows="3" placeholder="Nhập lý do từ chối hoặc lời nhắn phản hồi..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0 justify-content-end gap-2">
                                                                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                                                                <button type="submit" class="ychv-confirm-btn">
                                                                    <i class="fas fa-check"></i> Xác nhận
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="ychv-handled">
                                                <i class="fas fa-user-check"></i> {{ $yc->admin->ho_ten ?? 'Admin' }}<br>
                                                <small><i class="far fa-clock"></i> {{ $yc->thoi_gian_duyet?->format('d/m H:i') }}</small>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .ychv-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .ychv-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25); }
    .ychv-page .apx-section-title h2 i { color: #dc2626; }
    .ychv-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .ychv-page .apx-meta-pill strong { color: #b91c1c; }

    .ychv-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important; }
    .ychv-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .ychv-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .ychv-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .ychv-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: ychvPulse 1.6s ease-out infinite; }
    @keyframes ychvPulse { 0%,100%{box-shadow: 0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow: 0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.ychv-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.ychv-welcome p i { color: #fef3c7; margin-right: 4px; }
    .ychv-sep { opacity: 0.5; }

    .ychv-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
    .ychv-table thead { background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%); font-size: 0.7rem; text-transform: uppercase; color: #7f1d1d; letter-spacing: 0.5px; }
    .ychv-table thead th { padding: 12px 10px; font-weight: 800; border-bottom: 1px solid #fecaca; }
    .ychv-table tbody td { padding: 14px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .ychv-table tbody tr:last-child td { border-bottom: 0; }
    .ychv-table tbody tr:hover { background: #fafafa; }

    .ychv-time { font-size: 0.86rem; font-weight: 800; color: #0f172a; }
    .ychv-time i { color: #dc2626; margin-right: 5px; font-size: 0.74rem; }
    .ychv-time-sub { font-size: 0.72rem; color: #94a3b8; font-weight: 600; margin-top: 2px; }

    .ychv-avatar { flex-shrink: 0; width: 38px; height: 38px; border-radius: 50%; color: #fff; font-weight: 800; font-size: 0.9rem; display: grid; place-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .ychv-source-tag { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: #f1f5f9; color: #475569; font-size: 0.66rem; font-weight: 700; border-radius: 999px; margin-bottom: 3px; }
    .ychv-source-tag i { font-size: 0.55rem; opacity: 0.7; }
    .ychv-name { font-size: 0.86rem; font-weight: 800; color: #0f172a; }
    .ychv-name-sub { font-size: 0.7rem; color: #94a3b8; font-family: monospace; margin-top: 2px; }

    .ychv-course { font-size: 0.84rem; font-weight: 700; color: #0f172a; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ychv-course-sub { font-size: 0.72rem; color: #64748b; font-weight: 600; margin-top: 3px; }
    .ychv-course-sub i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .ychv-type-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; font-size: 0.72rem; font-weight: 800; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; }
    .ychv-type-pill.is-success { background: #dcfce7; color: #166534; }
    .ychv-type-pill.is-warning { background: #fef3c7; color: #b45309; }
    .ychv-type-pill.is-danger  { background: #fee2e2; color: #b91c1c; }
    .ychv-type-pill i { font-size: 0.62rem; }

    .ychv-content { font-size: 0.82rem; color: #1e293b; max-width: 280px; }
    .ychv-content strong { color: #0f172a; font-weight: 800; }
    .ychv-content-sub { font-size: 0.72rem; color: #64748b; margin-top: 3px; }
    .ychv-content-sub i { color: #1d4ed8; margin-right: 4px; font-size: 0.62rem; }
    .ychv-reason { font-size: 0.74rem; color: #94a3b8; font-style: italic; margin-top: 5px; }
    .ychv-reason i { color: #f59e0b; margin-right: 4px; font-size: 0.66rem; }

    .ychv-process-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #fff; font-size: 0.8rem; font-weight: 800; border-radius: 10px; border: 0; box-shadow: 0 4px 12px rgba(220,38,38,0.22); cursor: pointer; transition: all 0.2s ease; }
    .ychv-process-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(220,38,38,0.32); }
    .ychv-handled { font-size: 0.78rem; color: #475569; font-weight: 600; text-align: right; }
    .ychv-handled i { color: #16a34a; margin-right: 4px; font-size: 0.74rem; }
    .ychv-handled small { color: #94a3b8; font-size: 0.7rem; display: inline-flex; align-items: center; gap: 3px; margin-top: 2px; }

    .ychv-modal-head { background: linear-gradient(135deg, #1d4ed8 0%, #4361ee 100%); color: #fff; border: 0; padding: 16px 20px; }
    .ychv-modal-head .btn-close { filter: invert(1) grayscale(100%); opacity: 0.9; }
    .ychv-flabel { display: block; font-weight: 800; color: #7f1d1d; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }
    .ychv-confirm-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 20px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; font-size: 0.86rem; font-weight: 800; border-radius: 10px; border: 0; cursor: pointer; box-shadow: 0 4px 12px rgba(22,163,74,0.22); transition: all 0.2s ease; }
    .ychv-confirm-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(22,163,74,0.32); }

    .ychv-empty { padding: 60px 30px; text-align: center; }
    .ychv-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .ychv-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .ychv-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }
</style>
@endsection
