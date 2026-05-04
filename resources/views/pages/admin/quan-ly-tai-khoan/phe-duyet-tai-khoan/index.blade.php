@extends('layouts.app')

@section('title', 'Phê duyệt tài khoản')

@section('content')
@php
    $tongCho = \App\Models\TaiKhoanChoPheDuyet::where('trang_thai', 'cho_phe_duyet')->count();
    $homNay = \App\Models\TaiKhoanChoPheDuyet::where('trang_thai', 'cho_phe_duyet')->whereDate('created_at', today())->count();
    $tuanNay = \App\Models\TaiKhoanChoPheDuyet::where('trang_thai', 'cho_phe_duyet')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
    $hasFilter = request()->filled('search');
@endphp

<div class="container-fluid admin-page-x pdt-page">
    <div class="apx-welcome pdt-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-user-check"></i></div>
        <div class="apx-welcome-text">
            <div class="pdt-tag-row">
                <span class="pdt-loai-badge"><i class="fas fa-shield-halved"></i> PHÊ DUYỆT TÀI KHOẢN</span>
                <span class="pdt-status-badge"><i class="fas fa-layer-group"></i> {{ $tongCho }} chờ duyệt</span>
                @if($tongCho > 0)
                    <span class="pdt-pending-badge"><i class="fas fa-hourglass-half"></i> Cần xử lý</span>
                @endif
            </div>
            <h4>Tài khoản đăng ký mới</h4>
            <p>
                <span><i class="fas fa-calendar-day"></i> {{ $homNay }} đăng ký hôm nay</span>
                <span class="pdt-sep">·</span>
                <span><i class="fas fa-calendar-week"></i> {{ $tuanNay }} đăng ký tuần này</span>
                <span class="pdt-sep">·</span>
                <span><i class="fas fa-user-plus"></i> Phê duyệt để tạo tài khoản chính thức</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle"><i class="fas fa-arrow-left"></i> <span>Dashboard</span></a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">1</span><div><h2><i class="fas fa-chart-pie"></i> Tổng quan đăng ký</h2><p>Bốn chỉ số nhanh cho hàng đợi phê duyệt.</p></div></div>
        </header>
        <div class="row g-3">
            <div class="col-md-3 col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-users"></i></div><div class="aps-text"><strong>{{ $tongCho }}</strong><small>Tổng chờ duyệt</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-warning"><div class="aps-icon"><i class="fas fa-hourglass-half"></i></div><div class="aps-text"><strong>{{ $tongCho }}</strong><small>Cần xử lý</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-info"><div class="aps-icon"><i class="fas fa-calendar-day"></i></div><div class="aps-text"><strong>{{ $homNay }}</strong><small>Đăng ký hôm nay</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-success"><div class="aps-icon"><i class="fas fa-calendar-week"></i></div><div class="aps-text"><strong>{{ $tuanNay }}</strong><small>Đăng ký tuần này</small></div></div></div>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">2</span><div><h2><i class="fas fa-magnifying-glass"></i> Tìm kiếm</h2><p>Tìm theo tên, email hoặc số điện thoại.</p></div></div>
            @if($hasFilter)<div class="apx-section-meta"><span class="apx-meta-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;"><i class="fas fa-filter"></i> Đang lọc</span></div>@endif
        </header>
        <div class="pdt-filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-8">
                    <label class="pdt-flabel">Tìm kiếm</label>
                    <div class="pdt-input-icon">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên, email, số điện thoại...">
                    </div>
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold flex-fill"><i class="fas fa-search me-1"></i> Tìm kiếm</button>
                    <a href="{{ route('admin.phe-duyet-tai-khoan.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">3</span><div><h2><i class="fas fa-list"></i> Danh sách tài khoản chờ phê duyệt</h2><p>Phê duyệt để tạo tài khoản chính thức hoặc từ chối nếu không hợp lệ.</p></div></div>
            <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ $taiKhoanChoPheDuyet->total() }}</strong> tài khoản</span></div>
        </header>

        <div class="pdt-table-wrap">
            @if($taiKhoanChoPheDuyet->isEmpty())
                <div class="pdt-empty">
                    <div class="pdt-empty-icon"><i class="fas fa-circle-check"></i></div>
                    <h5>Tuyệt vời! Không có tài khoản nào chờ phê duyệt</h5>
                    <p>Tất cả đăng ký đã được xử lý. Tài khoản mới sẽ xuất hiện ở đây khi có người đăng ký.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 pdt-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Tài khoản</th>
                                <th>Liên lạc</th>
                                <th>Thông tin cá nhân</th>
                                <th class="text-center">Đăng ký lúc</th>
                                <th class="pe-4 text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taiKhoanChoPheDuyet as $tk)
                                @php
                                    $idColor = $tk->id % 6;
                                    $gradients = ['linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)','linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)','linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)'];
                                    $initial = mb_strtoupper(mb_substr(trim($tk->ho_ten), 0, 1, 'UTF-8'), 'UTF-8');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="pdt-avatar" style="background: {{ $gradients[$idColor] }};">{{ $initial ?: '?' }}</div>
                                            <div>
                                                <div class="pdt-name">{{ $tk->ho_ten }}</div>
                                                <div class="pdt-id"><i class="fas fa-hashtag"></i> ID: {{ $tk->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="pdt-line"><i class="fas fa-envelope"></i> {{ $tk->email }}</div>
                                        <div class="pdt-line-sub"><i class="fas fa-phone"></i> {{ $tk->so_dien_thoai ?: 'Chưa cập nhật' }}</div>
                                    </td>
                                    <td>
                                        <div class="pdt-line">
                                            <i class="fas fa-cake-candles"></i>
                                            {{ $tk->ngay_sinh ? $tk->ngay_sinh->format('d/m/Y') : 'Chưa cập nhật' }}
                                        </div>
                                        <div class="pdt-line-sub" title="{{ $tk->dia_chi ?? '' }}">
                                            <i class="fas fa-location-dot"></i>
                                            {{ $tk->dia_chi ? \Illuminate\Support\Str::limit($tk->dia_chi, 40) : 'Chưa cập nhật' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="pdt-time"><i class="far fa-calendar-alt"></i> {{ $tk->created_at->format('d/m/Y') }}</div>
                                        <div class="pdt-time-sub">{{ $tk->created_at->format('H:i') }} · {{ $tk->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="pdt-actions">
                                            <button class="pdt-btn-approve approve-btn" data-id="{{ $tk->id }}" data-name="{{ $tk->ho_ten }}" data-status="pending">
                                                <i class="fas fa-check"></i> <span class="btn-text">Duyệt</span>
                                            </button>
                                            <button class="pdt-btn-reject reject-btn" data-id="{{ $tk->id }}" data-name="{{ $tk->ho_ten }}">
                                                <i class="fas fa-xmark"></i> Từ chối
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($taiKhoanChoPheDuyet->hasPages())
                    <div class="pdt-pagination">{{ $taiKhoanChoPheDuyet->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </section>
</div>

{{-- Modal duyệt --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header pdt-modal-head success">
                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i> Xác nhận phê duyệt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                Bạn có chắc chắn muốn phê duyệt tài khoản <strong id="approveName" class="text-success"></strong>?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="pdt-btn-approve" id="confirmApprove" style="padding: 9px 22px;"><i class="fas fa-check"></i> Phê duyệt</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal từ chối --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header pdt-modal-head danger">
                <h5 class="modal-title fw-bold"><i class="fas fa-circle-xmark me-2"></i> Xác nhận từ chối</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                Bạn có chắc chắn muốn từ chối tài khoản <strong id="rejectName" class="text-danger"></strong>?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="pdt-btn-reject" id="confirmReject" style="padding: 9px 22px;"><i class="fas fa-xmark"></i> Từ chối</button>
            </div>
        </div>
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .pdt-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .pdt-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .pdt-page .apx-section-title h2 i { color: #dc2626; }
    .pdt-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .pdt-page .apx-meta-pill strong { color: #b91c1c; }

    .pdt-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .pdt-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .pdt-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .pdt-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .pdt-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: pdtPulse 1.6s ease-out infinite; }
    @keyframes pdtPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.pdt-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.pdt-welcome p i { color: #fef3c7; margin-right: 4px; }
    .pdt-sep { opacity: 0.5; }

    .pdt-filter-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; }
    .pdt-flabel { display: block; font-weight: 800; color: #7f1d1d; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }
    .pdt-input-icon { position: relative; }
    .pdt-input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem; pointer-events: none; }
    .pdt-input-icon input { padding-left: 34px; }

    .pdt-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
    .pdt-table thead { background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%); font-size: 0.7rem; text-transform: uppercase; color: #7f1d1d; letter-spacing: 0.5px; }
    .pdt-table thead th { padding: 12px 10px; font-weight: 800; border-bottom: 1px solid #fecaca; }
    .pdt-table tbody td { padding: 14px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .pdt-table tbody tr:last-child td { border-bottom: 0; }
    .pdt-table tbody tr:hover { background: #fafafa; }

    .pdt-avatar { flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; color: #fff; font-weight: 800; font-size: 1.05rem; display: grid; place-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .pdt-name { font-size: 0.92rem; font-weight: 800; color: #0f172a; line-height: 1.3; }
    .pdt-id { font-size: 0.72rem; color: #94a3b8; font-weight: 600; margin-top: 3px; }
    .pdt-id i { color: #1d4ed8; margin-right: 3px; font-size: 0.62rem; }

    .pdt-line { font-size: 0.82rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
    .pdt-line i { color: #1d4ed8; margin-right: 5px; font-size: 0.74rem; width: 14px; text-align: center; }
    .pdt-line-sub { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-top: 4px; line-height: 1.3; }
    .pdt-line-sub i { color: #94a3b8; margin-right: 5px; font-size: 0.66rem; width: 14px; text-align: center; }

    .pdt-time { font-size: 0.84rem; font-weight: 800; color: #0f172a; }
    .pdt-time i { color: #dc2626; margin-right: 4px; font-size: 0.74rem; }
    .pdt-time-sub { font-size: 0.7rem; color: #94a3b8; font-weight: 600; margin-top: 2px; }

    .pdt-actions { display: inline-flex; gap: 6px; }
    .pdt-btn-approve {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 14px;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: #fff; font-size: 0.78rem; font-weight: 800; border-radius: 10px;
        border: 0; cursor: pointer; box-shadow: 0 4px 12px rgba(22,163,74,0.22);
        transition: all 0.2s ease;
    }
    .pdt-btn-approve:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(22,163,74,0.32); color: #fff; }
    .pdt-btn-approve.btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 4px 12px rgba(245,158,11,0.22);
    }
    .pdt-btn-approve.btn-warning:hover { box-shadow: 0 8px 18px rgba(245,158,11,0.32); }
    .pdt-btn-reject {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 14px;
        background: #fff;
        color: #dc2626; border: 1px solid #fecaca;
        font-size: 0.78rem; font-weight: 800; border-radius: 10px;
        cursor: pointer; transition: all 0.2s ease;
    }
    .pdt-btn-reject:hover { background: #dc2626; color: #fff; border-color: #dc2626; transform: translateY(-1px); }

    .pdt-pagination { padding: 14px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; }
    .pdt-pagination nav { margin: 0; }

    .pdt-empty { padding: 60px 30px; text-align: center; }
    .pdt-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 50%; display: grid; place-items: center; color: #16a34a; font-size: 2rem; }
    .pdt-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .pdt-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }

    .pdt-modal-head { color: #fff; border: 0; padding: 16px 20px; }
    .pdt-modal-head.success { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); }
    .pdt-modal-head.danger  { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }
    .pdt-modal-head .btn-close { filter: invert(1) grayscale(100%); opacity: 0.9; }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    let currentId = null;

    $(document).on('click', '.approve-btn', function() {
        const $btn = $(this);
        const status = $btn.data('status');

        if (status === 'approved') {
            currentId = $btn.data('id');
            const name = $btn.data('name');
            if (confirm(`Bạn có chắc chắn muốn hủy phê duyệt tài khoản "${name}"?`)) {
                $.post(`{{ route('admin.phe-duyet-tai-khoan.undo', ':id') }}`.replace(':id', currentId), {
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $btn.data('status', 'pending').removeClass('btn-warning');
                        $btn.find('.btn-text').text('Duyệt');
                    } else {
                        toastr.error(response.message || 'Có lỗi xảy ra!');
                    }
                })
                .fail(function() { toastr.error('Có lỗi xảy ra!'); });
            }
        } else {
            currentId = $btn.data('id');
            $('#approveName').text($btn.data('name'));
            $('#approveModal').modal('show');
        }
    });

    $('#confirmApprove').on('click', function() {
        if (currentId) {
            $.post(`{{ route('admin.phe-duyet-tai-khoan.approve', ':id') }}`.replace(':id', currentId), {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    const $approveBtn = $('[data-id="' + currentId + '"].approve-btn');
                    $approveBtn.data('status', 'approved').addClass('btn-warning');
                    $approveBtn.find('.btn-text').text('Hủy');

                    setTimeout(function() {
                        $approveBtn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                            if ($('tbody tr').length === 0) {
                                location.reload();
                            }
                            if (response.redirect) { window.location.href = response.redirect; }
                        });
                    }, 1500);
                } else {
                    toastr.error('Có lỗi xảy ra!');
                }
            })
            .fail(function() { toastr.error('Có lỗi xảy ra!'); });

            $('#approveModal').modal('hide');
        }
    });

    $(document).on('click', '.reject-btn', function() {
        currentId = $(this).data('id');
        $('#rejectName').text($(this).data('name'));
        $('#rejectModal').modal('show');
    });

    $('#confirmReject').on('click', function() {
        if (currentId) {
            $.post(`{{ route('admin.phe-duyet-tai-khoan.reject', ':id') }}`.replace(':id', currentId), {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('tbody tr').each(function() {
                        if ($(this).find('.reject-btn').data('id') == currentId) {
                            $(this).fadeOut(300, function() {
                                $(this).remove();
                                if ($('tbody tr').length === 0) location.reload();
                            });
                        }
                    });
                } else {
                    toastr.error('Có lỗi xảy ra!');
                }
            })
            .fail(function() { toastr.error('Có lỗi xảy ra!'); });

            $('#rejectModal').modal('hide');
        }
    });
});
</script>
@endpush
@endsection
