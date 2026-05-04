@extends('layouts.app', ['title' => 'Hậu kiểm bài làm'])

@section('content')
@php
    $hoTen = $baiLam->hocVien->ho_ten ?? 'Học viên';
    $email = $baiLam->hocVien->email ?? '';
    $idColor = ($baiLam->hocVien->ma_nguoi_dung ?? $baiLam->id) % 6;
    $gradients = [
        'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
        'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
        'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
        'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
        'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
        'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
    ];
    $initial = mb_strtoupper(mb_substr(trim($hoTen), 0, 1, 'UTF-8'), 'UTF-8');
    $viPham = (int) $baiLam->tong_so_vi_pham;
    $totalLogs    = $baiLam->giamSatLogs->count();
    $totalSnaps   = $baiLam->giamSatSnapshots->where('status', 'captured')->count();
    $allSnaps     = $baiLam->giamSatSnapshots->count();
@endphp

<div class="container-fluid admin-page-x atr-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome atr-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-magnifying-glass"></i></div>
        <div class="apx-welcome-text">
            <div class="atr-tag-row">
                <span class="atr-loai-badge">
                    <i class="fas fa-user-shield"></i> HẬU KIỂM BÀI LÀM
                </span>
                <span class="atr-status-pill bg-{{ $baiLam->trang_thai_giam_sat_color }}">
                    <i class="fas fa-shield-halved"></i> {{ $baiLam->trang_thai_giam_sat_label }}
                </span>
                @if($viPham > 0)
                    <span class="atr-violation-badge">
                        <i class="fas fa-flag"></i> {{ $viPham }} vi phạm
                    </span>
                @else
                    <span class="atr-clean-badge">
                        <i class="fas fa-check-circle"></i> Sạch
                    </span>
                @endif
                <span class="atr-attempt-badge">
                    <i class="fas fa-redo"></i> Lần làm {{ $baiLam->lan_lam_thu }}
                </span>
            </div>
            <h4>{{ $hoTen }}</h4>
            <p>
                <span><i class="fas fa-file-signature"></i> {{ $baiLam->baiKiemTra->tieu_de }}</span>
                <span class="atr-sep">·</span>
                <span><i class="fas fa-envelope"></i> {{ $email ?: '—' }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.kiem-tra-online.phe-duyet.show', $baiLam->baiKiemTra->id) }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Quay lại đề thi</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- Quick stats --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-primary">
                <div class="aps-icon"><i class="fas fa-clock-rotate-left"></i></div>
                <div class="aps-text">
                    <strong>{{ $totalLogs }}</strong>
                    <small>Log giám sát</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-danger">
                <div class="aps-icon"><i class="fas fa-flag"></i></div>
                <div class="aps-text">
                    <strong>{{ $viPham }}</strong>
                    <small>Số vi phạm</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-success">
                <div class="aps-icon"><i class="fas fa-camera"></i></div>
                <div class="aps-text">
                    <strong>{{ $totalSnaps }}</strong>
                    <small>Snapshot OK</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-info">
                <div class="aps-icon"><i class="fas fa-image"></i></div>
                <div class="aps-text">
                    <strong>{{ $allSnaps }}</strong>
                    <small>Tổng snapshot</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ===== ① Thông tin học viên ===== --}}
        <div class="col-lg-4">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">1</span>
                        <div>
                            <h2><i class="fas fa-user-graduate"></i> Thông tin học viên</h2>
                            <p>Hồ sơ và tóm tắt giám sát.</p>
                        </div>
                    </div>
                </header>

                <div class="atr-info-card">
                    <div class="atr-student">
                        <div class="atr-avatar" style="background: {{ $gradients[$idColor] }};">
                            {{ $initial ?: 'H' }}
                        </div>
                        <div class="atr-student-info">
                            <div class="atr-name">{{ $hoTen }}</div>
                            <div class="atr-email"><i class="fas fa-envelope"></i> {{ $email ?: '—' }}</div>
                        </div>
                    </div>

                    <div class="atr-info-row">
                        <span class="atr-info-label">Trạng thái giám sát</span>
                        <span class="badge bg-{{ $baiLam->trang_thai_giam_sat_color }} px-3 py-2">
                            {{ $baiLam->trang_thai_giam_sat_label }}
                        </span>
                    </div>
                    <div class="atr-info-row">
                        <span class="atr-info-label">Tổng vi phạm</span>
                        <span class="atr-violation {{ $viPham > 0 ? 'is-danger' : 'is-success' }}">
                            <i class="fas {{ $viPham > 0 ? 'fa-flag' : 'fa-check' }}"></i> {{ $viPham }}
                        </span>
                    </div>
                    <div class="atr-info-row">
                        <span class="atr-info-label">Lần làm</span>
                        <strong class="atr-info-value">#{{ $baiLam->lan_lam_thu }}</strong>
                    </div>
                    <div class="atr-info-row">
                        <span class="atr-info-label">Snapshot đã lưu</span>
                        <strong class="atr-info-value">{{ $totalSnaps }}</strong>
                    </div>
                    <div class="atr-info-row">
                        <span class="atr-info-label">Tổng log</span>
                        <strong class="atr-info-value">{{ $totalLogs }}</strong>
                    </div>
                    @if($baiLam->nguoiHauKiem)
                        <div class="atr-info-row">
                            <span class="atr-info-label">Người hậu kiểm</span>
                            <strong class="atr-info-value">{{ $baiLam->nguoiHauKiem->ho_ten ?? '—' }}</strong>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- ===== Right column ===== --}}
        <div class="col-lg-8">
            @if(!$baiLam->baiKiemTra->co_giam_sat)
                <div class="atr-no-watch">
                    <div class="atr-no-watch-icon"><i class="fas fa-shield"></i></div>
                    <h5>Bài làm này không áp dụng giám sát nâng cao</h5>
                    <p>Đề thi gốc đã được cấu hình ở chế độ thường nên không có dữ liệu vi phạm để hậu kiểm.</p>
                </div>
            @else
                {{-- ② Cập nhật hậu kiểm --}}
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">2</span>
                            <div>
                                <h2><i class="fas fa-pen-to-square"></i> Cập nhật trạng thái hậu kiểm</h2>
                                <p>Đặt trạng thái và ghi chú để giảng viên / admin tham khảo.</p>
                            </div>
                        </div>
                    </header>

                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.attempt.surveillance', $baiLam->id) }}"
                          method="POST" class="atr-form-card">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="atr-flabel">Trạng thái hậu kiểm</label>
                                <select name="trang_thai_giam_sat" class="form-select">
                                    @foreach($reviewStatusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('trang_thai_giam_sat', $baiLam->trang_thai_giam_sat) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="atr-flabel">Ghi chú hậu kiểm</label>
                                <textarea name="ghi_chu_giam_sat" rows="3" class="form-control"
                                          placeholder="Nhận định của bạn về hành vi của học viên...">{{ old('ghi_chu_giam_sat', $baiLam->ghi_chu_giam_sat) }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="atr-btn-update">
                                <i class="fas fa-save"></i> Cập nhật hậu kiểm
                            </button>
                        </div>
                    </form>
                </section>

                {{-- ③ Timeline vi phạm --}}
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">3</span>
                            <div>
                                <h2><i class="fas fa-clock-rotate-left"></i> Timeline vi phạm</h2>
                                <p>Toàn bộ sự kiện giám sát được hệ thống ghi nhận.</p>
                            </div>
                        </div>
                        <div class="apx-section-meta">
                            <span class="apx-meta-pill"><strong>{{ $totalLogs }}</strong> log</span>
                        </div>
                    </header>

                    <div class="atr-timeline">
                        @forelse($baiLam->giamSatLogs as $log)
                            <div class="atr-log-item {{ $log->la_vi_pham ? 'is-violation' : 'is-info' }}">
                                <div class="atr-log-marker">
                                    <i class="fas {{ $log->la_vi_pham ? 'fa-flag' : 'fa-info-circle' }}"></i>
                                </div>
                                <div class="atr-log-card">
                                    <div class="atr-log-head">
                                        <div class="atr-log-title">{{ $log->loai_su_kien_label }}</div>
                                        <span class="badge bg-{{ $log->badge_color }} px-2 py-1">
                                            {{ $log->la_vi_pham ? 'Vi phạm' : 'Log' }}
                                        </span>
                                    </div>
                                    @if($log->mo_ta)
                                        <div class="atr-log-desc">{{ $log->mo_ta }}</div>
                                    @endif
                                    <div class="atr-log-time">
                                        <i class="far fa-clock"></i>
                                        {{ $log->created_at?->format('d/m/Y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="atr-empty">
                                <div class="atr-empty-icon"><i class="fas fa-shield-check"></i></div>
                                <p>Chưa có log giám sát nào.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- ④ Snapshot camera --}}
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">4</span>
                            <div>
                                <h2><i class="fas fa-camera"></i> Snapshot camera</h2>
                                <p>Ảnh chụp định kỳ trong quá trình làm bài. Bấm để mở ảnh đầy đủ.</p>
                            </div>
                        </div>
                        <div class="apx-section-meta">
                            <span class="apx-meta-pill"><strong>{{ $allSnaps }}</strong> ảnh</span>
                        </div>
                    </header>

                    <div class="atr-snapshot-card">
                        @if($baiLam->giamSatSnapshots->isEmpty())
                            <div class="atr-empty">
                                <div class="atr-empty-icon"><i class="fas fa-image"></i></div>
                                <p>Chưa có snapshot nào.</p>
                            </div>
                        @else
                            <div class="atr-snapshot-grid">
                                @foreach($baiLam->giamSatSnapshots as $snapshot)
                                    <div class="atr-snap">
                                        @if($snapshot->file_url)
                                            <a href="{{ $snapshot->file_url }}" target="_blank" rel="noopener">
                                                <img src="{{ $snapshot->file_url }}" alt="Snapshot giám sát" class="atr-snap-img">
                                            </a>
                                        @else
                                            <div class="atr-snap-error">
                                                <i class="fas fa-triangle-exclamation"></i>
                                                Snapshot lỗi
                                            </div>
                                        @endif
                                        <div class="atr-snap-time">
                                            <i class="far fa-clock"></i>
                                            {{ $snapshot->captured_at?->format('d/m/Y H:i:s') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .atr-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .atr-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .atr-page .apx-section-title h2 i { color: #dc2626; }
    .atr-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .atr-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .atr-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .atr-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .atr-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem; font-weight: 800;
        letter-spacing: 1px; border-radius: 999px;
    }
    .atr-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
        color: #fff;
    }
    .atr-violation-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
        animation: atrPulse 1.6s ease-out infinite;
    }
    @keyframes atrPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.5); }
        50%      { box-shadow: 0 0 0 5px rgba(220, 38, 38, 0); }
    }
    .atr-clean-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
    }
    .atr-attempt-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff;
        font-size: 0.72rem; font-weight: 700;
        border-radius: 999px;
    }
    .apx-welcome.atr-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.atr-welcome p i { color: #fef3c7; margin-right: 4px; }
    .atr-sep { opacity: 0.5; }

    /* ===== Info card ===== */
    .atr-info-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .atr-student {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 14px;
        margin-bottom: 14px;
        border-bottom: 1px dashed #fecaca;
    }
    .atr-avatar {
        flex-shrink: 0;
        width: 56px; height: 56px;
        border-radius: 50%;
        color: #fff;
        font-weight: 800;
        font-size: 1.4rem;
        display: grid; place-items: center;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    }
    .atr-student-info { flex: 1; min-width: 0; }
    .atr-name {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
    }
    .atr-email {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 4px;
    }
    .atr-email i { color: #1d4ed8; margin-right: 5px; font-size: 0.7rem; }

    .atr-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        gap: 12px;
        border-bottom: 1px dashed #f1f5f9;
        flex-wrap: wrap;
    }
    .atr-info-row:last-child { border-bottom: 0; }
    .atr-info-label {
        font-size: 0.74rem;
        font-weight: 800;
        color: #7f1d1d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .atr-info-value {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
    }
    .atr-violation {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.78rem; font-weight: 800;
        border-radius: 999px;
    }
    .atr-violation.is-success { background: #dcfce7; color: #166534; }
    .atr-violation.is-danger  { background: #fee2e2; color: #b91c1c; }

    /* ===== Form card ===== */
    .atr-form-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .atr-flabel {
        display: block;
        font-weight: 800;
        color: #7f1d1d;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .atr-form-card textarea:focus,
    .atr-form-card select:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
    }
    .atr-btn-update {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 18px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.86rem; font-weight: 800;
        border-radius: 10px;
        border: 0;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .atr-btn-update:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.32);
    }

    /* ===== Timeline ===== */
    .atr-timeline {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .atr-log-item {
        display: grid;
        grid-template-columns: 36px 1fr;
        gap: 10px;
        position: relative;
    }
    .atr-log-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 17px; top: 36px; bottom: -12px;
        border-left: 2px dashed #e2e8f0;
    }
    .atr-log-marker {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: grid; place-items: center;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.86rem;
        flex-shrink: 0;
        z-index: 1;
    }
    .atr-log-item.is-violation .atr-log-marker {
        background: #fee2e2;
        color: #dc2626;
    }
    .atr-log-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
    }
    .atr-log-item.is-violation .atr-log-card {
        background: #fef2f2;
        border-color: #fecaca;
    }
    .atr-log-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 4px;
    }
    .atr-log-title {
        font-size: 0.88rem;
        font-weight: 800;
        color: #0f172a;
    }
    .atr-log-desc {
        font-size: 0.8rem;
        color: #475569;
        margin-bottom: 4px;
        line-height: 1.45;
    }
    .atr-log-time {
        font-size: 0.74rem;
        color: #94a3b8;
        font-weight: 600;
    }
    .atr-log-time i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }
    .atr-log-item.is-violation .atr-log-time i { color: #dc2626; }

    /* ===== Snapshots ===== */
    .atr-snapshot-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .atr-snapshot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
    }
    .atr-snap {
        position: relative;
    }
    .atr-snap a { display: block; }
    .atr-snap-img {
        width: 100%;
        aspect-ratio: 4/3;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .atr-snap-img:hover {
        border-color: #dc2626;
        transform: scale(1.02);
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.18);
    }
    .atr-snap-error {
        aspect-ratio: 4/3;
        display: grid; place-items: center;
        background: #fef2f2;
        border: 1px dashed #fecaca;
        border-radius: 10px;
        color: #b91c1c;
        font-size: 0.8rem;
        font-weight: 700;
        text-align: center;
        padding: 12px;
    }
    .atr-snap-error i { font-size: 1.2rem; margin-bottom: 6px; display: block; }
    .atr-snap-time {
        margin-top: 6px;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        text-align: center;
    }
    .atr-snap-time i { color: #1d4ed8; margin-right: 4px; font-size: 0.62rem; }

    /* ===== Empty / no-watch ===== */
    .atr-empty {
        padding: 40px 20px;
        text-align: center;
    }
    .atr-empty-icon {
        width: 72px; height: 72px;
        margin: 0 auto 12px;
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #16a34a;
        font-size: 1.6rem;
    }
    .atr-empty p { font-size: 0.86rem; color: #94a3b8; margin: 0; }

    .atr-no-watch {
        padding: 60px 30px;
        text-align: center;
        background: #fff;
        border: 1px dashed #e2e8f0;
        border-radius: 14px;
    }
    .atr-no-watch-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #64748b;
        font-size: 2rem;
    }
    .atr-no-watch h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .atr-no-watch p {
        font-size: 0.9rem; color: #94a3b8;
        max-width: 460px; margin: 0 auto;
        line-height: 1.55;
    }

    @media (max-width: 575.98px) {
        .atr-snapshot-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endsection
