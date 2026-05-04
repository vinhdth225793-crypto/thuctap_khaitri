<aside class="sidebar edu-sidebar-fixed">
    @php
        $hasBaiKiemTraRoute = Route::has('hoc-vien.bai-kiem-tra');
        $hasKetQuaRoute = Route::has('hoc-vien.ket-qua');
    @endphp

    {{-- Header: logo + brand --}}
    <div class="edu-sidebar-header">
        <div class="edu-logo-wrapper bg-learner">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="edu-brand-block">
            <h4 class="edu-brand-name">HỌC VIÊN</h4>
            <div class="edu-tagline">LEARNING HUB</div>
        </div>
    </div>

    {{-- Profile card --}}
    <div class="edu-profile-wrap">
        <div class="edu-profile-card">
            <div class="edu-avatar-box">
                @if(auth()->user()->anh_dai_dien)
                    <img src="{{ asset(auth()->user()->anh_dai_dien) }}" alt="">
                @else
                    <div class="edu-avatar-initials bg-learner">{{ strtoupper(mb_substr(auth()->user()->ho_ten, 0, 1)) }}</div>
                @endif
            </div>
            <div class="edu-profile-info">
                <div class="edu-user-title">{{ auth()->user()->ho_ten }}</div>
                <div class="edu-user-status"><span class="dot-online"></span> Đang học tập</div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav custom-scrollbar" id="sidebarScrollContainer" aria-label="Điều hướng học viên">
        <x-sidebar-link route="hoc-vien.dashboard" icon="fas fa-house-chimney-user" tone="learner" tooltip="Bảng điều khiển">
            Bảng điều khiển
        </x-sidebar-link>

        <x-sidebar-section icon="fas fa-book-bookmark">Học tập</x-sidebar-section>

        <x-sidebar-link route="hoc-vien.khoa-hoc-cua-toi" icon="fas fa-book-open-reader" tone="info" tooltip="Khóa học của tôi">
            Khóa học của tôi
        </x-sidebar-link>

        <x-sidebar-link route="hoc-vien.hoat-dong-tien-do" icon="fas fa-chart-line" tone="success" tooltip="Hoạt động & tiến độ">
            Hoạt động & tiến độ
        </x-sidebar-link>

        <div class="nav-item">
            @if($hasBaiKiemTraRoute)
                <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="edu-link-parent {{ request()->routeIs('hoc-vien.bai-kiem-tra*') ? 'active' : '' }}" data-tooltip="Bài kiểm tra">
                    <div class="edu-icon-circle bg-soft-danger"><i class="fas fa-file-signature"></i></div>
                    <span class="edu-link-label">Bài kiểm tra</span>
                </a>
            @else
                <div class="edu-link-parent edu-link-disabled" data-tooltip="Sắp mở">
                    <div class="edu-icon-circle bg-soft-secondary"><i class="fas fa-lock"></i></div>
                    <span class="edu-link-label">Bài kiểm tra</span>
                    <span class="edu-soon-badge">SẮP MỞ</span>
                </div>
            @endif
        </div>

        <div class="nav-item">
            @if($hasKetQuaRoute)
                <a href="{{ route('hoc-vien.ket-qua') }}" class="edu-link-parent {{ request()->routeIs('hoc-vien.ket-qua') ? 'active' : '' }}" data-tooltip="Kết quả học tập">
                    <div class="edu-icon-circle bg-soft-primary"><i class="fas fa-square-poll-vertical"></i></div>
                    <span class="edu-link-label">Kết quả học tập</span>
                </a>
            @else
                <div class="edu-link-parent edu-link-disabled" data-tooltip="Sắp mở">
                    <div class="edu-icon-circle bg-soft-secondary"><i class="fas fa-lock"></i></div>
                    <span class="edu-link-label">Kết quả học tập</span>
                    <span class="edu-soon-badge">SẮP MỞ</span>
                </div>
            @endif
        </div>

        <x-sidebar-section icon="fas fa-compass">Khám phá</x-sidebar-section>

        <x-sidebar-link route="hoc-vien.khoa-hoc-tham-gia" icon="fas fa-user-plus" tone="warning" tooltip="Xin vào lớp mới">
            Xin vào lớp mới
        </x-sidebar-link>

        <x-sidebar-section icon="fas fa-circle-user">Tài khoản</x-sidebar-section>

        <x-sidebar-link route="hoc-vien.profile" icon="fas fa-id-card-clip" tone="secondary" tooltip="Hồ sơ cá nhân" :mini="true">
            Hồ sơ cá nhân
        </x-sidebar-link>

        <x-sidebar-link route="home" icon="fas fa-house" tone="dark" tooltip="Về trang chủ" :mini="true">
            Về trang chủ
        </x-sidebar-link>

        {{-- Footer logout --}}
        <div class="edu-sidebar-footer">
            <form action="{{ route('dang-xuat') }}" method="POST" class="edu-logout-form">
                @csrf
                <button type="submit" class="edu-btn-logout" data-tooltip="Đăng xuất">
                    <i class="fas fa-power-off"></i>
                    <span class="edu-link-label">ĐĂNG XUẤT</span>
                </button>
            </form>
        </div>
    </nav>
</aside>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&display=swap');

    :root {
        --sb-brand: #4361ee;
        --sb-brand-dark: #3a0ca3;
        --sb-bg: #ffffff;
        --sb-text: #475569;
        --sb-text-strong: #0f172a;
        --sb-muted: #94a3b8;
        --sb-line: #f1f5f9;
        --sb-soft: #f8fafc;
        --sb-active-grad: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
    }

    .edu-sidebar-fixed {
        background: var(--sb-bg) !important;
        width: 280px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1050;
        box-shadow: 4px 0 24px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        font-family: 'Lexend', sans-serif;
        border-right: 1px solid var(--sb-line);
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ===== Header ===== */
    .edu-sidebar-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--sb-line);
    }

    .edu-logo-wrapper {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 1.4rem;
        box-shadow: 0 8px 18px rgba(67, 97, 238, 0.3);
    }

    .bg-learner { background: var(--sb-active-grad) !important; }

    .edu-brand-block { line-height: 1.2; min-width: 0; }
    .edu-brand-name { font-weight: 800; color: var(--sb-text-strong); font-size: 1.05rem; letter-spacing: 0.6px; margin: 0; }
    .edu-tagline { font-size: 0.65rem; font-weight: 700; color: var(--sb-muted); letter-spacing: 1.5px; margin-top: 2px; }

    /* ===== Profile card ===== */
    .edu-profile-wrap { padding: 14px 16px; }

    .edu-profile-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--sb-soft);
        border: 1px solid var(--sb-line);
        border-radius: 14px;
        transition: all 0.2s ease;
    }

    .edu-profile-card:hover { background: #f1f5f9; border-color: #e2e8f0; }

    .edu-avatar-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        flex-shrink: 0;
    }

    .edu-avatar-box img { width: 100%; height: 100%; object-fit: cover; }

    .edu-avatar-initials {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
        color: #fff;
        font-weight: 800;
        font-size: 1.1rem;
    }

    .edu-profile-info { min-width: 0; flex: 1; }
    .edu-user-title {
        font-weight: 700;
        color: var(--sb-text-strong);
        font-size: 0.92rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .edu-user-status { font-size: 0.7rem; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 5px; margin-top: 2px; }
    .dot-online {
        width: 7px; height: 7px;
        background: #22c55e;
        border-radius: 50%;
        animation: dotPulse 1.6s infinite;
    }

    @keyframes dotPulse {
        0% { box-shadow: 0 0 0 0 rgba(34,197,94,0.7); }
        70% { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }

    /* ===== Navigation ===== */
    .sidebar-nav {
        flex: 1;
        padding: 8px 14px 18px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .nav-item { margin-bottom: 4px; }

    /* ===== Section labels ===== */
    .edu-section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 16px 12px 8px;
        font-size: 0.68rem;
        font-weight: 800;
        color: var(--sb-muted);
        text-transform: uppercase;
        letter-spacing: 1.2px;
        border-top: 1px dashed var(--sb-line);
        margin-top: 8px;
    }

    .edu-section-label:first-of-type { border-top: 0; margin-top: 12px; }

    .edu-section-label i { font-size: 0.72rem; color: var(--sb-brand); }

    /* ===== Parent links ===== */
    .edu-link-parent {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        color: var(--sb-text) !important;
        text-decoration: none !important;
        border-radius: 12px;
        font-size: 0.92rem;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        border: 0;
        background: transparent;
        width: 100%;
    }

    .edu-link-parent::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 0;
        background: var(--sb-brand);
        border-radius: 0 3px 3px 0;
        transition: height 0.2s ease;
    }

    .edu-link-parent:hover {
        background: var(--sb-soft);
        color: var(--sb-text-strong) !important;
        transform: translateX(2px);
    }

    .edu-link-parent:hover::before { height: 60%; }

    .edu-link-parent.active {
        background: rgba(67, 97, 238, 0.08);
        color: var(--sb-brand-dark) !important;
        font-weight: 700;
    }

    .edu-link-parent.active::before { height: 70%; }

    .edu-link-parent.active .edu-icon-circle {
        background: var(--sb-active-grad) !important;
        color: #fff !important;
        box-shadow: 0 8px 16px rgba(67, 97, 238, 0.3);
    }

    .edu-link-disabled {
        opacity: 0.55;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ===== Icon circle ===== */
    .edu-icon-circle {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        margin-right: 12px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .edu-link-parent:hover .edu-icon-circle { transform: scale(1.04); }

    .bg-soft-learner   { background: #eef2ff; color: #4361ee; }
    .bg-soft-primary   { background: #eef2ff; color: #4361ee; }
    .bg-soft-info      { background: #e0f2fe; color: #0ea5e9; }
    .bg-soft-warning   { background: #fef3c7; color: #d97706; }
    .bg-soft-success   { background: #dcfce7; color: #16a34a; }
    .bg-soft-danger    { background: #fee2e2; color: #dc2626; }
    .bg-soft-secondary { background: #f1f5f9; color: #475569; }
    .bg-soft-dark      { background: #e2e8f0; color: #1e293b; }

    .edu-link-label {
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
    }

    /* ===== Soon badge ===== */
    .edu-soon-badge {
        margin-left: auto;
        padding: 2px 8px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.8px;
        border-radius: 999px;
    }

    .edu-link-mini { padding: 8px 12px; font-size: 0.88rem; }
    .edu-link-mini .edu-icon-circle { width: 32px; height: 32px; font-size: 0.85rem; }

    /* ===== Footer logout ===== */
    .edu-sidebar-footer {
        margin-top: 18px;
        padding-top: 14px;
        border-top: 1px solid var(--sb-line);
    }

    .edu-logout-form { margin: 0; }

    .edu-btn-logout {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #fff;
        border: 0;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.82rem;
        letter-spacing: 1.2px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .edu-btn-logout::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        opacity: 0;
        transition: opacity 0.25s ease;
        z-index: 0;
    }

    .edu-btn-logout > * { position: relative; z-index: 1; }

    .edu-btn-logout:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(239, 68, 68, 0.35);
    }

    .edu-btn-logout:hover::before { opacity: 1; }

    .edu-btn-logout i { font-size: 1rem; }

    /* ===== Scrollbar ===== */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* ============== COLLAPSED STATE ============== */
    .sidebar-collapsed .edu-sidebar-fixed { width: 78px !important; }
    .sidebar-collapsed .edu-sidebar-header { padding: 18px 12px; justify-content: center; }
    .sidebar-collapsed .edu-brand-block { display: none; }
    .sidebar-collapsed .edu-profile-wrap { padding: 10px 12px; }
    .sidebar-collapsed .edu-profile-card { padding: 8px; justify-content: center; background: transparent; border: 0; }
    .sidebar-collapsed .edu-profile-info { display: none; }
    .sidebar-collapsed .edu-avatar-box { width: 40px; height: 40px; }
    .sidebar-collapsed .sidebar-nav { padding: 8px; }
    .sidebar-collapsed .nav-item { margin-bottom: 6px; }
    .sidebar-collapsed .edu-link-parent { justify-content: center; padding: 10px 0; }
    .sidebar-collapsed .edu-icon-circle { margin-right: 0; }
    .sidebar-collapsed .edu-link-label,
    .sidebar-collapsed .edu-section-label,
    .sidebar-collapsed .edu-soon-badge {
        display: none !important;
    }
    .sidebar-collapsed .edu-btn-logout { padding: 12px 0; }
    .sidebar-collapsed .edu-link-parent::before { display: none; }

    /* ===== Tooltip when collapsed ===== */
    .sidebar-collapsed .edu-link-parent[data-tooltip]::after,
    .sidebar-collapsed .edu-btn-logout[data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        left: calc(100% + 14px);
        top: 50%;
        transform: translateY(-50%) translateX(-6px);
        padding: 6px 12px;
        background: #0f172a;
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 8px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 1100;
        pointer-events: none;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
    }

    .sidebar-collapsed .edu-link-parent[data-tooltip]::before,
    .sidebar-collapsed .edu-btn-logout[data-tooltip]::before {
        content: '';
        position: absolute;
        left: calc(100% + 8px);
        top: 50%;
        transform: translateY(-50%) translateX(-6px);
        border: 6px solid transparent;
        border-right-color: #0f172a;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 1100;
        pointer-events: none;
    }

    .sidebar-collapsed .edu-link-parent[data-tooltip]:hover::after,
    .sidebar-collapsed .edu-link-parent[data-tooltip]:hover::before,
    .sidebar-collapsed .edu-btn-logout[data-tooltip]:hover::after,
    .sidebar-collapsed .edu-btn-logout[data-tooltip]:hover::before {
        opacity: 1;
        visibility: visible;
        transform: translateY(-50%) translateX(0);
    }

    /* ============== Responsive ============== */
    @media (max-width: 991.98px) {
        .edu-sidebar-fixed { transform: translateX(-100%); }
        .edu-sidebar-fixed.active { transform: translateX(0); }
    }
</style>
