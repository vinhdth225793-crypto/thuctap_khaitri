@extends('layouts.app')

@section('title', 'Tham gia khóa học')

@section('content')
<div class="container-fluid admin-page-x ktg-page">
    <div class="apx-welcome ktg-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-user-plus"></i></div>
        <div class="apx-welcome-text">
            <div class="ktg-tag-row">
                <span class="ktg-loai-badge"><i class="fas fa-globe"></i> THAM GIA KHÓA HỌC</span>
                <span class="ktg-status-badge"><i class="fas fa-layer-group"></i> {{ $stats['co_the_tham_gia'] }} khóa</span>
                @if($stats['dang_cho_duyet'] > 0)
                    <span class="ktg-pending-badge"><i class="fas fa-hourglass-half"></i> {{ $stats['dang_cho_duyet'] }} chờ duyệt</span>
                @endif
            </div>
            <h4>Khóa học đang mở để gửi yêu cầu</h4>
            <p>
                <span><i class="fas fa-paper-plane"></i> {{ $stats['da_gui'] }} đã gửi yêu cầu</span>
                <span class="ktg-sep">·</span>
                <span><i class="fas fa-graduation-cap"></i> Gửi đơn xin vào lớp để admin xét duyệt</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('hoc-vien.dashboard') }}" class="apx-view-toggle"><i class="fas fa-arrow-left"></i> <span>Dashboard</span></a>
            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn btn-light text-primary fw-bold shadow-sm ktg-create-btn">
                <i class="fas fa-book-open me-1"></i> Khóa của tôi
            </a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">1</span><div><h2><i class="fas fa-chart-pie"></i> Tổng quan</h2><p>Ba chỉ số nhanh về việc đăng ký khóa học.</p></div></div>
        </header>
        <div class="row g-3">
            <div class="col-md-4 col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-globe"></i></div><div class="aps-text"><strong>{{ $stats['co_the_tham_gia'] }}</strong><small>Có thể tham gia</small></div></div></div>
            <div class="col-md-4 col-6"><div class="apx-stat tone-warning"><div class="aps-icon"><i class="fas fa-hourglass-half"></i></div><div class="aps-text"><strong>{{ $stats['dang_cho_duyet'] }}</strong><small>Đang chờ duyệt</small></div></div></div>
            <div class="col-md-4 col-6"><div class="apx-stat tone-info"><div class="aps-icon"><i class="fas fa-paper-plane"></i></div><div class="aps-text"><strong>{{ $stats['da_gui'] }}</strong><small>Đã gửi yêu cầu</small></div></div></div>
        </div>
    </section>

    <div class="row g-4">
        {{-- Main: Khóa học mở --}}
        <div class="col-lg-8">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title"><span class="apx-section-num">2</span><div><h2><i class="fas fa-list"></i> Danh sách khóa học mở</h2><p>Bấm "Gửi yêu cầu" để xin vào lớp.</p></div></div>
                    <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ $khoaHocs->total() }}</strong> khóa</span></div>
                </header>

                @if($khoaHocs->isEmpty())
                    <div class="ktg-empty">
                        <div class="ktg-empty-icon"><i class="fas fa-inbox"></i></div>
                        <h5>Chưa có khóa học nào đang mở</h5>
                        <p>Bạn có thể quay lại sau hoặc liên hệ admin để được hỗ trợ ghi danh.</p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($khoaHocs as $khoaHoc)
                            @php
                                $dangCho = in_array($khoaHoc->id, $dangChoDuyetIds);
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
                            <div class="col-xl-6">
                                <div class="ktg-card">
                                    <div class="ktg-cover">
                                        @if($khoaHoc->hinh_anh)
                                            <img src="{{ asset($khoaHoc->hinh_anh) }}" alt="{{ $khoaHoc->ten_khoa_hoc }}">
                                        @else
                                            <div class="ktg-cover-fallback" style="background: {{ $gradients[$idColor] }};">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                        @endif
                                        <span class="ktg-status-pill bg-{{ $khoaHoc->badge_trang_thai }}">{{ $khoaHoc->label_trang_thai_van_hanh }}</span>
                                        @if($dangCho)
                                            <span class="ktg-pending-tag"><i class="fas fa-hourglass-half"></i> Đã gửi</span>
                                        @endif
                                    </div>
                                    <div class="ktg-body">
                                        <span class="ktg-soft-tag"><i class="fas fa-tag"></i> {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'Chưa phân nhóm' }}</span>
                                        <h5 class="ktg-title">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                                        <div class="ktg-code"><i class="fas fa-fingerprint"></i> {{ $khoaHoc->ma_khoa_hoc }}</div>
                                        <p class="ktg-desc">{{ $khoaHoc->mo_ta_ngan ?: 'Khóa học đang mở cho học viên gửi yêu cầu tham gia.' }}</p>

                                        <div class="ktg-info-list">
                                            <div class="ktg-info-row">
                                                <span><i class="far fa-calendar-alt"></i> Khai giảng</span>
                                                <strong>{{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?: '—' }}</strong>
                                            </div>
                                            <div class="ktg-info-row">
                                                <span><i class="fas fa-layer-group"></i> Module</span>
                                                <strong>{{ $khoaHoc->module_hocs_count }}</strong>
                                            </div>
                                            <div class="ktg-info-row">
                                                <span><i class="fas fa-users"></i> Học viên</span>
                                                <strong>{{ $khoaHoc->hoc_vien_dang_hoc_count }}</strong>
                                            </div>
                                        </div>

                                        <div class="ktg-actions">
                                            @if($dangCho)
                                                <button type="button" class="ktg-btn-disabled" disabled>
                                                    <i class="fas fa-clock"></i> Đã gửi yêu cầu
                                                </button>
                                            @else
                                                <button type="button" class="ktg-btn-primary" data-bs-toggle="modal" data-bs-target="#modalXinThamGia{{ $khoaHoc->id }}">
                                                    <i class="fas fa-paper-plane"></i> Gửi yêu cầu tham gia
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @unless($dangCho)
                                <div class="modal fade" id="modalXinThamGia{{ $khoaHoc->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0">
                                            <div class="modal-header ktg-modal-head">
                                                <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i> Gửi yêu cầu tham gia</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('hoc-vien.khoa-hoc.gui-yeu-cau-tham-gia', $khoaHoc->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="ktg-flabel">Khóa học</label>
                                                        <input type="text" class="form-control" value="{{ $khoaHoc->ten_khoa_hoc }}" readonly>
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="ktg-flabel">Lý do xin tham gia <span class="text-danger">*</span></label>
                                                        <textarea name="ly_do" class="form-control" rows="4" placeholder="Ví dụ: Em muốn tham gia để bổ sung kiến thức và theo học cùng lớp..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="ktg-btn-primary" style="padding: 9px 20px;"><i class="fas fa-paper-plane"></i> Gửi yêu cầu</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endunless
                        @endforeach
                    </div>

                    @if($khoaHocs->hasPages())
                        <div class="ktg-pagination">{{ $khoaHocs->links('pagination::bootstrap-5') }}</div>
                    @endif
                @endif
            </section>
        </div>

        {{-- Sidebar: Lịch sử yêu cầu --}}
        <div class="col-lg-4">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title"><span class="apx-section-num">3</span><div><h2><i class="fas fa-clock-rotate-left"></i> Lịch sử yêu cầu</h2><p>Các yêu cầu tham gia đã gửi.</p></div></div>
                </header>
                <div class="ktg-history-card">
                    @forelse($yeuCauDaGui as $yeuCau)
                        <div class="ktg-history-item">
                            <div class="ktg-history-head">
                                <div class="ktg-history-title">{{ $yeuCau->khoaHoc->ten_khoa_hoc ?? 'Khóa học không còn tồn tại' }}</div>
                                <span class="badge bg-{{ $yeuCau->trang_thai_badge }} px-2 py-1">{{ $yeuCau->trang_thai_label }}</span>
                            </div>
                            <div class="ktg-history-time"><i class="far fa-clock"></i> {{ $yeuCau->created_at->format('d/m/Y H:i') }}</div>
                            <div class="ktg-history-reason"><strong>Lý do:</strong> {{ $yeuCau->ly_do }}</div>
                            @if($yeuCau->phan_hoi_admin)
                                <div class="ktg-history-feedback">
                                    <i class="fas fa-comment-dots"></i>
                                    <strong>Phản hồi admin:</strong> {{ $yeuCau->phan_hoi_admin }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="ktg-history-empty">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có yêu cầu nào</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .ktg-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .ktg-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .ktg-page .apx-section-title h2 i { color: #dc2626; }
    .ktg-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .ktg-page .apx-meta-pill strong { color: #b91c1c; }

    .ktg-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .ktg-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .ktg-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .ktg-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .ktg-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: ktgPulse 1.6s ease-out infinite; }
    @keyframes ktgPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.ktg-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.ktg-welcome p i { color: #fef3c7; margin-right: 4px; }
    .ktg-sep { opacity: 0.5; }
    .ktg-create-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    .ktg-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; height: 100%; display: flex; flex-direction: column; transition: all 0.25s ease; }
    .ktg-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(15,23,42,0.1); border-color: #fecaca; }
    .ktg-cover { position: relative; height: 170px; overflow: hidden; background: #f1f5f9; }
    .ktg-cover img { width: 100%; height: 100%; object-fit: cover; }
    .ktg-cover-fallback { width: 100%; height: 100%; display: grid; place-items: center; color: #fff; font-size: 3rem; }
    .ktg-status-pill { position: absolute; top: 12px; right: 12px; padding: 5px 14px; font-size: 0.72rem; font-weight: 800; border-radius: 999px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); color: #fff; }
    .ktg-pending-tag { position: absolute; top: 12px; left: 12px; padding: 5px 12px; background: #fef3c7; color: #b45309; font-size: 0.7rem; font-weight: 800; border-radius: 999px; }

    .ktg-body { padding: 16px 18px; flex: 1; display: flex; flex-direction: column; }
    .ktg-soft-tag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: #fef2f2; color: #dc2626; font-size: 0.7rem; font-weight: 800; border-radius: 999px; margin-bottom: 8px; align-self: flex-start; }
    .ktg-soft-tag i { font-size: 0.62rem; }

    .ktg-title { font-size: 1rem; font-weight: 800; color: #0f172a; line-height: 1.4; margin: 0 0 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.8em; }
    .ktg-code { font-size: 0.74rem; color: #64748b; font-weight: 600; margin-bottom: 8px; }
    .ktg-code i { color: #dc2626; margin-right: 4px; font-size: 0.66rem; }
    .ktg-desc { font-size: 0.82rem; color: #64748b; line-height: 1.5; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    .ktg-info-list { background: #fafafa; border: 1px solid #f1f5f9; border-radius: 10px; padding: 10px 12px; margin-bottom: 14px; }
    .ktg-info-row { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 4px 0; font-size: 0.78rem; }
    .ktg-info-row span { color: #64748b; font-weight: 600; }
    .ktg-info-row span i { color: #1d4ed8; margin-right: 5px; font-size: 0.7rem; }
    .ktg-info-row strong { color: #0f172a; font-weight: 800; }

    .ktg-actions { margin-top: auto; }
    .ktg-btn-primary { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #fff; font-size: 0.86rem; font-weight: 800; border-radius: 10px; border: 0; cursor: pointer; box-shadow: 0 4px 12px rgba(220,38,38,0.22); transition: all 0.2s ease; width: 100%; }
    .ktg-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(220,38,38,0.32); color: #fff; }
    .ktg-btn-disabled { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; background: #f1f5f9; color: #94a3b8; font-size: 0.86rem; font-weight: 800; border-radius: 10px; border: 0; cursor: not-allowed; width: 100%; }

    .ktg-modal-head { background: linear-gradient(135deg, #1d4ed8 0%, #4361ee 100%); color: #fff; border: 0; padding: 16px 20px; }
    .ktg-modal-head .btn-close { filter: invert(1) grayscale(100%); opacity: 0.9; }
    .ktg-flabel { display: block; font-weight: 800; color: #7f1d1d; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }

    .ktg-pagination { padding: 18px; display: flex; justify-content: center; }
    .ktg-pagination nav { margin: 0; }

    .ktg-empty { padding: 60px 30px; text-align: center; background: #fff; border: 1px dashed #fecaca; border-radius: 14px; }
    .ktg-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .ktg-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .ktg-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 440px; margin: 0 auto; line-height: 1.55; }

    .ktg-history-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; max-height: 600px; overflow-y: auto; }
    .ktg-history-item { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; }
    .ktg-history-item:last-child { border-bottom: 0; }
    .ktg-history-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 6px; }
    .ktg-history-title { font-size: 0.86rem; font-weight: 800; color: #0f172a; line-height: 1.3; }
    .ktg-history-time { font-size: 0.72rem; color: #94a3b8; font-weight: 600; margin-bottom: 6px; }
    .ktg-history-time i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }
    .ktg-history-reason { font-size: 0.78rem; color: #475569; line-height: 1.5; }
    .ktg-history-reason strong { color: #0f172a; font-weight: 700; }
    .ktg-history-feedback { margin-top: 8px; padding: 8px 10px; background: #eff6ff; border-left: 3px solid #1d4ed8; border-radius: 6px; font-size: 0.76rem; color: #1e293b; line-height: 1.45; }
    .ktg-history-feedback i { color: #1d4ed8; margin-right: 4px; }
    .ktg-history-empty { padding: 50px 20px; text-align: center; color: #94a3b8; }
    .ktg-history-empty i { font-size: 2rem; opacity: 0.4; display: block; margin-bottom: 10px; }
    .ktg-history-empty p { font-size: 0.86rem; margin: 0; }
</style>
@endsection
