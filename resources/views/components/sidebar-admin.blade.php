<aside class="sidebar edu-sidebar-fixed">
    {{-- Header: logo + brand --}}
    <div class="edu-sidebar-header">
        <div class="edu-logo-wrapper">
            <i class="fas fa-shield-halved"></i>
        </div>
        <div class="edu-brand-block">
            <h4 class="edu-brand-name">QUẢN TRỊ</h4>
            <div class="edu-tagline">SYSTEM CONTROL</div>
        </div>
    </div>

    {{-- Profile card --}}
    <div class="edu-profile-wrap">
        <div class="edu-profile-card">
            <div class="edu-avatar-box">
                @if(auth()->user()->anh_dai_dien)
                    <img src="{{ asset(auth()->user()->anh_dai_dien) }}" alt="">
                @else
                    <div class="edu-avatar-initials">{{ strtoupper(mb_substr(auth()->user()->ho_ten, 0, 1)) }}</div>
                @endif
            </div>
            <div class="edu-profile-info">
                <div class="edu-user-title">{{ auth()->user()->ho_ten }}</div>
                <div class="edu-user-status"><span class="dot-online"></span> Admin online</div>
            </div>
        </div>
    </div>

    @php
        $accountGroupOpen = request()->routeIs('admin.hoc-vien.*', 'admin.giang-vien.*');
        $structureGroupOpen = request()->routeIs('admin.nhom-nganh.*', 'admin.khoa-hoc.*', 'admin.module-hoc.*', 'admin.phan-cong.*');
        $opsGroupOpen = request()->routeIs('admin.diem-danh.*', 'admin.ket-qua.*');
        $questionBankActive = request()->routeIs('admin.kiem-tra-online.cau-hoi.*');
        $approvalGroupOpen = request()->routeIs(
            'admin.phe-duyet-tai-khoan.*',
            'admin.giang-vien-don-xin-nghi.*',
            'admin.thu-vien.*',
            'admin.bai-giang.*',
            'admin.kiem-tra-online.phe-duyet.*',
            'admin.xet-duyet-ket-qua.*',
            'admin.yeu-cau-hoc-vien.*'
        );
        $systemGroupOpen = request()->routeIs('admin.settings*');
        $approvalCounts = $pendingApprovalCounts ?? [
            'tai_khoan' => 0,
            'tai_nguyen' => 0,
            'bai_giang' => 0,
            'de_thi' => 0,
            'xet_duyet_ket_qua' => 0,
            'don_nghi' => 0,
            'yeu_cau_hoc_vien' => 0,
        ];
        $approvalTotal = $pendingApprovalTotal ?? array_sum($approvalCounts);
    @endphp

    {{-- Navigation --}}
    <nav class="sidebar-nav custom-scrollbar" id="sidebarScrollContainer" aria-label="Điều hướng quản trị">
        <x-sidebar-link route="admin.dashboard" icon="fas fa-gauge-high" tone="primary" tooltip="Bảng điều khiển">
            Bảng điều khiển
        </x-sidebar-link>

        <x-sidebar-section icon="fas fa-users">Người dùng</x-sidebar-section>

        <x-sidebar-group id="accountGroup" :open="$accountGroupOpen" icon="fas fa-users-gear" tone="info" tooltip="Tài khoản">
            Tài khoản
            <x-slot:items>
                <x-sidebar-submenu-item route="admin.hoc-vien.index" pattern="admin.hoc-vien.*" icon="fas fa-user-graduate">
                    Học viên
                </x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.giang-vien.index" pattern="admin.giang-vien.*" icon="fas fa-chalkboard-user">
                    Giảng viên
                </x-sidebar-submenu-item>
            </x-slot:items>
        </x-sidebar-group>

        <x-sidebar-section icon="fas fa-graduation-cap">Đào tạo</x-sidebar-section>

        <x-sidebar-group id="structureGroup" :open="$structureGroupOpen" icon="fas fa-layer-group" tone="warning" tooltip="Cấu trúc đào tạo">
            Cấu trúc đào tạo
            <x-slot:items>
                <x-sidebar-submenu-item route="admin.nhom-nganh.index" pattern="admin.nhom-nganh.*" icon="fas fa-tags">Nhóm ngành</x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.khoa-hoc.index" pattern="admin.khoa-hoc.*" icon="fas fa-book">Khóa học</x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.module-hoc.index" pattern="admin.module-hoc.*" icon="fas fa-cubes">Module học</x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.phan-cong.index" pattern="admin.phan-cong.*" icon="fas fa-people-arrows">Phân công GV</x-sidebar-submenu-item>
            </x-slot:items>
        </x-sidebar-group>

        <x-sidebar-group id="opsGroup" :open="$opsGroupOpen" icon="fas fa-chalkboard" tone="success" tooltip="Vận hành lớp">
            Vận hành lớp
            <x-slot:items>
                <x-sidebar-submenu-item route="admin.diem-danh.index" pattern="admin.diem-danh.*" icon="fas fa-user-check">Điểm danh</x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.ket-qua.index" pattern="admin.ket-qua.*" icon="fas fa-chart-line">Kết quả học tập</x-sidebar-submenu-item>
            </x-slot:items>
        </x-sidebar-group>

        <x-sidebar-link route="admin.kiem-tra-online.cau-hoi.index"
                        :pattern="'admin.kiem-tra-online.cau-hoi.*'"
                        icon="fas fa-database" tone="primary" tooltip="Ngân hàng câu hỏi">
            Ngân hàng câu hỏi
        </x-sidebar-link>

        <x-sidebar-section icon="fas fa-clock-rotate-left" :badge="$approvalTotal">Chờ xử lý</x-sidebar-section>

        <x-sidebar-group id="approvalGroup" :open="$approvalGroupOpen" icon="fas fa-stamp" tone="danger" tooltip="Phê duyệt"
                         :badge="$approvalTotal" :badgePulse="true">
            Phê duyệt
            <x-slot:items>
                <x-sidebar-submenu-item route="admin.phe-duyet-tai-khoan.index" pattern="admin.phe-duyet-tai-khoan.*"
                                        icon="fas fa-user-check" :badge="$approvalCounts['tai_khoan'] ?? 0">
                    Tài khoản
                </x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.bai-giang.index" pattern="admin.bai-giang.*"
                                        icon="fas fa-file-circle-check" :badge="$approvalCounts['bai_giang'] ?? 0">
                    Bài giảng
                </x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.thu-vien.index" pattern="admin.thu-vien.*"
                                        icon="fas fa-folder-tree" :badge="$approvalCounts['tai_nguyen'] ?? 0">
                    Tài nguyên thư viện
                </x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.kiem-tra-online.phe-duyet.index" pattern="admin.kiem-tra-online.phe-duyet.*"
                                        icon="fas fa-clipboard-check" :badge="$approvalCounts['de_thi'] ?? 0">
                    Đề thi
                </x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.xet-duyet-ket-qua.index" pattern="admin.xet-duyet-ket-qua.*"
                                        icon="fas fa-file-signature" :badge="$approvalCounts['xet_duyet_ket_qua'] ?? 0">
                    Xét duyệt kết quả
                </x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.giang-vien-don-xin-nghi.index" pattern="admin.giang-vien-don-xin-nghi.*"
                                        icon="fas fa-calendar-minus" :badge="$approvalCounts['don_nghi'] ?? 0">
                    Đơn nghỉ giảng viên
                </x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.yeu-cau-hoc-vien.index" pattern="admin.yeu-cau-hoc-vien.*"
                                        icon="fas fa-comment-dots" :badge="$approvalCounts['yeu_cau_hoc_vien'] ?? 0">
                    Yêu cầu học viên
                </x-sidebar-submenu-item>
            </x-slot:items>
        </x-sidebar-group>

        <x-sidebar-section icon="fas fa-gear">Hệ thống</x-sidebar-section>

        <x-sidebar-group id="systemGroup" :open="$systemGroupOpen" icon="fas fa-sliders" tone="secondary" tooltip="Cài đặt">
            Cài đặt
            <x-slot:items>
                <x-sidebar-submenu-item route="admin.settings" pattern="admin.settings" icon="fas fa-toolbox">Cấu hình chung</x-sidebar-submenu-item>
                <x-sidebar-submenu-item route="admin.settings.banners.index" pattern="admin.settings.banners.*" icon="fas fa-images">Banner trang chủ</x-sidebar-submenu-item>
            </x-slot:items>
        </x-sidebar-group>

        {{-- Footer: profile + home + logout --}}
        <div class="edu-sidebar-footer">
            <x-sidebar-link route="profile" icon="fas fa-id-card" tone="secondary" tooltip="Hồ sơ cá nhân" :mini="true">
                Hồ sơ cá nhân
            </x-sidebar-link>
            <x-sidebar-link route="home" icon="fas fa-house" tone="dark" tooltip="Về trang chủ" :mini="true">
                Về trang chủ
            </x-sidebar-link>

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
        --sb-brand-dark: #2f46c9;
        --sb-bg: #ffffff;
        --sb-text: #475569;
        --sb-text-strong: #0f172a;
        --sb-muted: #94a3b8;
        --sb-line: #f1f5f9;
        --sb-soft: #f8fafc;
        --sb-active-grad: linear-gradient(135deg, #4361ee 0%, #2f46c9 100%);
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
        background: var(--sb-active-grad);
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 1.4rem;
        box-shadow: 0 8px 18px rgba(67, 97, 238, 0.3);
    }

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

    .edu-profile-card:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
    }

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
        background: var(--sb-active-grad);
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
    .dot-online { width: 7px; height: 7px; background: #22c55e; border-radius: 50%; box-shadow: 0 0 0 0 rgba(34,197,94,0.7); animation: dotPulse 1.6s infinite; }

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

    .edu-section-badge {
        margin-left: auto;
        min-width: 20px;
        padding: 2px 6px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #fff;
        border-radius: 999px;
        font-size: 0.62rem;
        font-weight: 800;
        text-align: center;
        line-height: 1.2;
    }

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

    .bg-soft-primary   { background: #eef2ff; color: #4361ee; }
    .bg-soft-info      { background: #e0f2fe; color: #0ea5e9; }
    .bg-soft-warning   { background: #fef3c7; color: #d97706; }
    .bg-soft-success   { background: #dcfce7; color: #16a34a; }
    .bg-soft-danger    { background: #fee2e2; color: #dc2626; }
    .bg-soft-secondary { background: #f1f5f9; color: #475569; }
    .bg-soft-dark      { background: #e2e8f0; color: #1e293b; }

    .edu-link-label { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 600; }

    /* ===== Submenu ===== */
    .edu-submenu-container {
        margin: 6px 0 4px 18px;
        padding: 6px 0 6px 16px;
        border-left: 2px solid var(--sb-line);
    }

    .edu-submenu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        margin-bottom: 2px;
        color: #64748b !important;
        text-decoration: none !important;
        font-size: 0.85rem;
        font-weight: 500;
        border-radius: 10px;
        transition: all 0.18s ease;
        position: relative;
    }

    .edu-submenu-item i {
        flex-shrink: 0;
        width: 18px;
        text-align: center;
        font-size: 0.78rem;
        color: var(--sb-muted);
        transition: color 0.18s ease;
    }

    .edu-submenu-item span {
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .edu-submenu-item:hover {
        color: var(--sb-text-strong) !important;
        background: var(--sb-soft);
        transform: translateX(2px);
    }

    .edu-submenu-item:hover i { color: var(--sb-brand); }

    .edu-submenu-item.active {
        color: var(--sb-brand-dark) !important;
        font-weight: 700;
        background: rgba(67, 97, 238, 0.08);
    }

    .edu-submenu-item.active i { color: var(--sb-brand); }

    .edu-submenu-item.active::before {
        content: '';
        position: absolute;
        left: -17px;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        background: var(--sb-brand);
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.18);
    }

    /* ===== Badges ===== */
    .edu-menu-badge {
        margin-left: auto;
        min-width: 22px;
        padding: 2px 7px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #fff;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
        line-height: 1.3;
        text-align: center;
    }

    .edu-badge-pulse {
        animation: badgePulse 1.6s ease-out infinite;
    }

    @keyframes badgePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
        50% { box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
    }

    .edu-submenu-badge {
        margin-left: auto;
        min-width: 18px;
        padding: 1px 6px;
        background: #fee2e2;
        color: #dc2626;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 800;
        line-height: 1.4;
        text-align: center;
    }

    /* ===== Arrow toggle ===== */
    .arrow-toggle {
        margin-left: 8px;
        font-size: 0.72rem;
        opacity: 0.45;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease, color 0.25s ease;
    }

    .edu-link-parent:not(.collapsed) .arrow-toggle {
        transform: rotate(90deg);
        opacity: 1;
        color: var(--sb-brand);
    }

    /* ===== Footer ===== */
    .edu-sidebar-footer {
        margin-top: 18px;
        padding: 14px 0 8px;
        border-top: 1px solid var(--sb-line);
    }

    .edu-link-mini { padding: 8px 12px; font-size: 0.88rem; margin-bottom: 4px; }
    .edu-link-mini .edu-icon-circle { width: 32px; height: 32px; font-size: 0.85rem; }

    .edu-logout-form { margin-top: 8px; }

    .edu-btn-logout {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
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
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
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

    .sidebar-collapsed .edu-link-parent {
        justify-content: center;
        padding: 10px 0;
    }

    .sidebar-collapsed .edu-icon-circle { margin-right: 0; }

    .sidebar-collapsed .edu-link-label,
    .sidebar-collapsed .arrow-toggle,
    .sidebar-collapsed .edu-section-label,
    .sidebar-collapsed .edu-submenu-container,
    .sidebar-collapsed .edu-menu-badge {
        display: none !important;
    }

    /* Badge nhỏ floating khi collapsed (chỉ hiển thị dot) */
    .sidebar-collapsed .edu-link-parent {
        position: relative;
    }

    .sidebar-collapsed .edu-link-parent .edu-menu-badge {
        display: block !important;
        position: absolute;
        top: 2px;
        right: 8px;
        margin: 0;
        min-width: 18px;
        padding: 1px 5px;
        font-size: 0.6rem;
    }

    .sidebar-collapsed .edu-btn-logout { padding: 12px 0; }
    .sidebar-collapsed .edu-btn-logout .edu-link-label { display: none !important; }

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

    /* Override để indicator left bar không hiện khi collapsed */
    .sidebar-collapsed .edu-link-parent::before { display: none; }

    /* ============== Responsive ============== */
    @media (max-width: 991.98px) {
        .edu-sidebar-fixed { transform: translateX(-100%); }
        .edu-sidebar-fixed.active { transform: translateX(0); }
    }
</style>
