@extends('layouts.app', ['title' => 'Bài kiểm tra của học viên'])

@section('content')
<div class="container-fluid admin-page-x bkthv-page">
    <div class="apx-welcome bkthv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-clipboard-check"></i></div>
        <div class="apx-welcome-text">
            <div class="bkthv-tag-row">
                <span class="bkthv-loai-badge"><i class="fas fa-file-signature"></i> BÀI KIỂM TRA CỦA TÔI</span>
                <span class="bkthv-status-badge"><i class="fas fa-layer-group"></i> {{ $stats['tong'] }} bài</span>
                @if($stats['dang_mo'] > 0)
                    <span class="bkthv-active-badge"><i class="fas fa-circle-play"></i> {{ $stats['dang_mo'] }} đang mở</span>
                @endif
                @if(($stats['cho_cham'] ?? 0) > 0)
                    <span class="bkthv-pending-badge"><i class="fas fa-hourglass-half"></i> {{ $stats['cho_cham'] }} chờ chấm</span>
                @endif
            </div>
            <h4>Bài kiểm tra của tôi</h4>
            <p>
                <span><i class="fas fa-clock"></i> {{ $stats['sap_mo'] }} sắp mở</span>
                <span class="bkthv-sep">·</span>
                <span><i class="fas fa-paper-plane"></i> {{ $stats['da_nop'] }} đã nộp</span>
                <span class="bkthv-sep">·</span>
                <span><i class="fas fa-graduation-cap"></i> Thuộc các khóa học bạn đang tham gia</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('hoc-vien.dashboard') }}" class="apx-view-toggle"><i class="fas fa-arrow-left"></i> <span>Dashboard</span></a>
            <a href="{{ route('hoc-vien.ket-qua') }}" class="btn btn-light text-primary fw-bold shadow-sm bkthv-create-btn">
                <i class="fas fa-chart-column me-1"></i> Bảng điểm
            </a>
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">1</span><div><h2><i class="fas fa-chart-pie"></i> Tổng quan bài kiểm tra</h2><p>Bốn chỉ số nhanh về các bài kiểm tra của bạn.</p></div></div>
            <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ $stats['tong'] }}</strong> bài</span></div>
        </header>
        <div class="row g-3">
            <div class="col-md-3 col-6"><div class="apx-stat tone-primary"><div class="aps-icon"><i class="fas fa-file-signature"></i></div><div class="aps-text"><strong>{{ $stats['tong'] }}</strong><small>Tổng bài kiểm tra</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-success"><div class="aps-icon"><i class="fas fa-circle-play"></i></div><div class="aps-text"><strong>{{ $stats['dang_mo'] }}</strong><small>Đang mở</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-warning"><div class="aps-icon"><i class="fas fa-clock"></i></div><div class="aps-text"><strong>{{ $stats['sap_mo'] }}</strong><small>Sắp mở</small></div></div></div>
            <div class="col-md-3 col-6"><div class="apx-stat tone-info"><div class="aps-icon"><i class="fas fa-paper-plane"></i></div><div class="aps-text"><strong>{{ $stats['da_nop'] }}</strong><small>Đã nộp</small></div></div></div>
        </div>
    </section>

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title"><span class="apx-section-num">2</span><div><h2><i class="fas fa-list"></i> Danh sách bài kiểm tra</h2><p>Bài đang mở hiển thị đầu danh sách. Bấm "Bắt đầu" để vào bài.</p></div></div>
            <div class="apx-section-meta"><span class="apx-meta-pill"><strong>{{ count($baiKiemTras) }}</strong> bài</span></div>
        </header>

        @if(count($baiKiemTras) === 0)
            <div class="bkthv-empty">
                <div class="bkthv-empty-icon"><i class="fas fa-file-circle-question"></i></div>
                <h5>Chưa có bài kiểm tra nào</h5>
                <p>Khi giảng viên tạo bài kiểm tra cho khóa học bạn tham gia, danh sách sẽ xuất hiện tại đây.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($baiKiemTras as $bkt)
                    @php
                        $baiLam = $bkt->baiLams->first();
                        $activeBaiLam = $bkt->baiLams->firstWhere('trang_thai', 'dang_lam');
                        $attemptsUsed = $bkt->baiLams->count();
                        $remainingAttempts = max(0, (int) $bkt->so_lan_duoc_lam - $attemptsUsed);
                        $canStartNewAttempt = $bkt->can_student_start && !$activeBaiLam && $remainingAttempts > 0;
                        $accessClass = match($bkt->access_status_color){'success'=>'is-success','warning'=>'is-warning','danger'=>'is-danger',default=>'is-secondary'};
                        $accessIcon  = match($bkt->access_status_color){'success'=>'fa-circle-check','warning'=>'fa-clock','danger'=>'fa-circle-xmark',default=>'fa-circle-info'};
                        $cardTone = '';
                        if ($bkt->loai_noi_dung === 'trac_nghiem') $cardTone = 'tone-tn';
                        elseif ($bkt->loai_noi_dung === 'tu_luan') $cardTone = 'tone-tl';
                        $idColor = $bkt->id % 6;
                        $gradients = ['linear-gradient(135deg,#4361ee,#2f46c9)','linear-gradient(135deg,#16a34a,#15803d)','linear-gradient(135deg,#d97706,#b45309)','linear-gradient(135deg,#0ea5e9,#0369a1)','linear-gradient(135deg,#7c3aed,#6d28d9)','linear-gradient(135deg,#db2777,#be185d)'];
                    @endphp
                    <div class="col-xl-4 col-lg-6">
                        <div class="bkthv-card {{ $cardTone }}">
                            <div class="bkthv-card-head">
                                <div class="bkthv-thumb" style="background: {{ $gradients[$idColor] }};">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <div class="bkthv-card-head-text">
                                    <h5 class="bkthv-title">{{ $bkt->tieu_de }}</h5>
                                    <div class="bkthv-course"><i class="fas fa-graduation-cap"></i> {{ $bkt->khoaHoc->ten_khoa_hoc ?? 'Chưa xác định' }}</div>
                                </div>
                            </div>

                            <div class="bkthv-tags">
                                <span class="bkthv-status-pill {{ $accessClass }}"><i class="fas {{ $accessIcon }}"></i> {{ $bkt->access_status_label }}</span>
                                <span class="bkthv-soft-tag"><i class="fas fa-tag"></i> {{ $bkt->pham_vi_label }}</span>
                                <span class="bkthv-soft-tag is-info"><i class="fas fa-clock"></i> {{ $bkt->thoi_gian_lam_bai }} phút</span>
                                @if($bkt->co_giam_sat)
                                    <span class="bkthv-soft-tag is-warn"><i class="fas fa-shield-halved"></i> Giám sát</span>
                                @endif
                                @if($baiLam)
                                    <span class="badge bg-{{ $baiLam->trang_thai_color }} px-2 py-1">{{ $baiLam->trang_thai_label }}</span>
                                @endif
                            </div>

                            <div class="bkthv-info-list">
                                <div class="bkthv-info-row">
                                    <span><i class="fas fa-cube"></i> Module</span>
                                    <strong>{{ $bkt->moduleHoc->ten_module ?? '—' }}</strong>
                                </div>
                                @if($bkt->lichHoc)
                                    <div class="bkthv-info-row">
                                        <span><i class="fas fa-calendar-day"></i> Buổi {{ $bkt->lichHoc->buoi_so ?: '#' }}</span>
                                        <strong>{{ optional($bkt->lichHoc->ngay_hoc)->format('d/m/Y') ?: '—' }}</strong>
                                    </div>
                                @endif
                                <div class="bkthv-info-row">
                                    <span><i class="fas fa-door-open text-success"></i> Mở</span>
                                    <strong>{{ $bkt->ngay_mo ? $bkt->ngay_mo->format('d/m H:i') : 'Mở ngay' }}</strong>
                                </div>
                                <div class="bkthv-info-row">
                                    <span><i class="fas fa-door-closed text-danger"></i> Đóng</span>
                                    <strong>{{ $bkt->ngay_dong ? $bkt->ngay_dong->format('d/m H:i') : '—' }}</strong>
                                </div>
                                <div class="bkthv-info-row">
                                    <span><i class="fas fa-rotate-right"></i> Lượt</span>
                                    <strong>
                                        {{ $attemptsUsed }}/{{ (int) $bkt->so_lan_duoc_lam }}
                                        @if($remainingAttempts > 0)
                                            <span class="text-success">(+{{ $remainingAttempts }})</span>
                                        @else
                                            <span class="text-muted">(hết)</span>
                                        @endif
                                    </strong>
                                </div>
                            </div>

                            @if($bkt->mo_ta)
                                <p class="bkthv-desc">{{ \Illuminate\Support\Str::limit($bkt->mo_ta, 110) }}</p>
                            @endif

                            <div class="bkthv-actions">
                                <a href="{{ route('hoc-vien.bai-kiem-tra.show', $bkt->id) }}" class="bkthv-btn-secondary">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>

                                @if($activeBaiLam)
                                    <a href="{{ route('hoc-vien.bai-kiem-tra.show', $bkt->id) }}" class="bkthv-btn-primary">
                                        <i class="fas fa-play"></i> Tiếp tục
                                    </a>
                                @elseif($canStartNewAttempt && $bkt->co_giam_sat)
                                    <a href="{{ route('hoc-vien.bai-kiem-tra.precheck', $bkt->id) }}" class="bkthv-btn-warning">
                                        <i class="fas fa-shield-halved"></i> {{ $baiLam ? 'Pre-check làm lại' : 'Pre-check' }}
                                    </a>
                                @elseif($canStartNewAttempt)
                                    <form action="{{ route('hoc-vien.bai-kiem-tra.bat-dau', $bkt->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="bkthv-btn-primary">
                                            <i class="fas fa-play"></i> {{ $baiLam ? 'Làm lại' : 'Bắt đầu' }}
                                        </button>
                                    </form>
                                @elseif($baiLam && $baiLam->is_submitted)
                                    <span class="bkthv-btn-success-static"><i class="fas fa-check"></i> Đã nộp</span>
                                @else
                                    <span class="bkthv-btn-disabled"><i class="fas fa-lock"></i> {{ $bkt->access_status_label }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .bkthv-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .bkthv-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .bkthv-page .apx-section-title h2 i { color: #dc2626; }
    .bkthv-page .apx-meta-pill { border-color: #fecaca; color: #dc2626; }
    .bkthv-page .apx-meta-pill strong { color: #b91c1c; }

    .bkthv-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .bkthv-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .bkthv-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .bkthv-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .bkthv-active-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: #dcfce7; color: #166534; font-size: 0.72rem; font-weight: 800; border-radius: 999px; }
    .bkthv-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: bkthvPulse 1.6s ease-out infinite; }
    @keyframes bkthvPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.bkthv-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.bkthv-welcome p i { color: #fef3c7; margin-right: 4px; }
    .bkthv-sep { opacity: 0.5; }
    .bkthv-create-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    .bkthv-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: all 0.25s ease;
    }
    .bkthv-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 28px rgba(15,23,42,0.08);
        border-color: #fecaca;
    }
    .bkthv-card.tone-tn { background: linear-gradient(180deg, #ffffff 0%, #fffdf0 100%); border-color: #fde68a; }
    .bkthv-card.tone-tn:hover { border-color: #f59e0b; }
    .bkthv-card.tone-tl { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; }
    .bkthv-card.tone-tl:hover { border-color: #dc2626; }

    .bkthv-card-head { display: flex; align-items: flex-start; gap: 12px; }
    .bkthv-thumb { flex-shrink: 0; width: 48px; height: 48px; border-radius: 12px; color: #fff; display: grid; place-items: center; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .bkthv-card-head-text { flex: 1; min-width: 0; }
    .bkthv-title { font-size: 0.96rem; font-weight: 800; color: #0f172a; line-height: 1.35; margin: 0 0 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .bkthv-course { font-size: 0.76rem; color: #64748b; font-weight: 600; }
    .bkthv-course i { color: #dc2626; margin-right: 4px; font-size: 0.66rem; }

    .bkthv-tags { display: flex; flex-wrap: wrap; gap: 5px; }
    .bkthv-status-pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; font-size: 0.68rem; font-weight: 800; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; }
    .bkthv-status-pill i { font-size: 0.6rem; }
    .bkthv-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .bkthv-status-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .bkthv-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .bkthv-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
    .bkthv-soft-tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; background: #f1f5f9; color: #475569; font-size: 0.68rem; font-weight: 700; border-radius: 999px; }
    .bkthv-soft-tag.is-info { background: #eff6ff; color: #1d4ed8; }
    .bkthv-soft-tag.is-warn { background: #fef3c7; color: #b45309; }
    .bkthv-soft-tag i { font-size: 0.6rem; }

    .bkthv-info-list { background: #fafafa; border: 1px solid #f1f5f9; border-radius: 10px; padding: 8px 12px; }
    .bkthv-info-row { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 4px 0; font-size: 0.78rem; }
    .bkthv-info-row + .bkthv-info-row { border-top: 1px dashed #f1f5f9; }
    .bkthv-info-row span { color: #64748b; font-weight: 600; }
    .bkthv-info-row span i { color: #1d4ed8; margin-right: 5px; font-size: 0.7rem; width: 14px; text-align: center; }
    .bkthv-info-row strong { color: #0f172a; font-weight: 800; }

    .bkthv-desc { font-size: 0.78rem; color: #64748b; line-height: 1.5; margin: 0; padding: 8px 10px; background: #fff; border: 1px dashed #fecaca; border-radius: 8px; }

    .bkthv-actions { display: flex; gap: 6px; flex-wrap: wrap; margin-top: auto; }
    .bkthv-btn-secondary { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #fff; color: #475569; border: 1px solid #e2e8f0; font-size: 0.8rem; font-weight: 700; border-radius: 10px; text-decoration: none; transition: all 0.18s ease; }
    .bkthv-btn-secondary:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .bkthv-btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #fff; font-size: 0.8rem; font-weight: 800; border-radius: 10px; text-decoration: none; border: 0; cursor: pointer; box-shadow: 0 4px 12px rgba(220,38,38,0.22); transition: all 0.2s ease; }
    .bkthv-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(220,38,38,0.32); color: #fff; }
    .bkthv-btn-warning { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; font-size: 0.8rem; font-weight: 800; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 12px rgba(245,158,11,0.22); transition: all 0.2s ease; }
    .bkthv-btn-warning:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(245,158,11,0.32); color: #fff; }
    .bkthv-btn-success-static { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #dcfce7; color: #166534; font-size: 0.8rem; font-weight: 800; border-radius: 10px; }
    .bkthv-btn-disabled { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #f1f5f9; color: #94a3b8; font-size: 0.8rem; font-weight: 700; border-radius: 10px; }

    .bkthv-empty { padding: 60px 30px; text-align: center; background: #fff; border: 1px dashed #fecaca; border-radius: 14px; }
    .bkthv-empty-icon { width: 88px; height: 88px; margin: 0 auto 18px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: grid; place-items: center; color: #dc2626; font-size: 2rem; }
    .bkthv-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .bkthv-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }
</style>
@endsection
