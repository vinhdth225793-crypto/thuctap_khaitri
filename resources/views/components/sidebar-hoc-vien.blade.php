<aside class="sidebar edu-sidebar-fixed">
    @php
        $hasBaiKiemTraRoute = Route::has('hoc-vien.bai-kiem-tra');
        $hasKetQuaRoute = Route::has('hoc-vien.ket-qua');
    @endphp

    <!-- Brand Identity -->
    <div class="sidebar-header border-0 mt-4 mb-4 px-4 text-center">
        <div class="edu-logo-wrapper mx-auto mb-2 bg-learner">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <h4 class="edu-brand-name">HỌC VIÊN</h4>
        <div class="edu-tagline">LEARNING HUB</div>
    </div>

    <!-- Student Card -->
    <div class="px-3 mb-4">
        <div class="edu-profile-card">
            <div class="d-flex align-items-center gap-3">
                <div class="edu-avatar-box">
                    @if(auth()->user()->anh_dai_dien)
                        <img src="{{ asset(auth()->user()->anh_dai_dien) }}" class="rounded-circle">
                    @else
                        <div class="edu-avatar-initials bg-learner">{{ strtoupper(mb_substr(auth()->user()->ho_ten, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="overflow-hidden">
                    <div class="edu-user-title text-truncate">{{ auth()->user()->ho_ten }}</div>
                    <div class="edu-user-status"><span class="dot-online"></span> Đang học tập</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Scrollable Area -->
    <nav class="sidebar-nav custom-scrollbar px-3 pb-5" id="sidebarScrollContainer">
        <!-- Dashboard -->
        <div class="nav-item mb-3">
            <a href="{{ route('hoc-vien.dashboard') }}" class="edu-link-parent {{ request()->routeIs('hoc-vien.dashboard') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-learner"><i class="fas fa-house-chimney-user"></i></div>
                <span class="fw-bold">Bảng điều khiển</span>
            </a>
        </div>

        <!-- Khóa học -->
        <div class="nav-item mb-3">
            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="edu-link-parent {{ request()->routeIs('hoc-vien.khoa-hoc-cua-toi') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-info"><i class="fas fa-book-open-reader"></i></div>
                <span class="fw-bold">Khóa học của tôi</span>
            </a>
        </div>

        <!-- Tiến độ -->
        <div class="nav-item mb-3">
            <a href="{{ route('hoc-vien.hoat-dong-tien-do') }}" class="edu-link-parent {{ request()->routeIs('hoc-vien.hoat-dong-tien-do') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-success"><i class="fas fa-chart-line"></i></div>
                <span class="fw-bold">Hoạt động & Tiến độ</span>
            </a>
        </div>

        <!-- Đăng ký lớp -->
        <div class="nav-item mb-3">
            <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="edu-link-parent {{ request()->routeIs('hoc-vien.khoa-hoc-tham-gia') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-warning"><i class="fas fa-user-plus"></i></div>
                <span class="fw-bold">Xin vào lớp</span>
            </a>
        </div>

        <!-- Bài kiểm tra -->
        <div class="nav-item mb-3">
            @if($hasBaiKiemTraRoute)
                <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="edu-link-parent {{ request()->routeIs('hoc-vien.bai-kiem-tra*') ? 'active' : '' }}">
                    <div class="edu-icon-circle bg-soft-danger"><i class="fas fa-file-signature"></i></div>
                    <span class="fw-bold">Bài kiểm tra</span>
                </a>
            @else
                <div class="edu-link-parent opacity-50" style="cursor: not-allowed;">
                    <div class="edu-icon-circle bg-soft-secondary"><i class="fas fa-lock"></i></div>
                    <span class="fw-bold">Bài kiểm tra</span>
                    <span class="badge bg-warning text-dark ms-auto arrow-toggle" style="font-size: 8px;">SẮP MỞ</span>
                </div>
            @endif
        </div>

        <!-- Kết quả -->
        <div class="nav-item mb-3">
            @if($hasKetQuaRoute)
                <a href="{{ route('hoc-vien.ket-qua') }}" class="edu-link-parent {{ request()->routeIs('hoc-vien.ket-qua') ? 'active' : '' }}">
                    <div class="edu-icon-circle bg-soft-primary"><i class="fas fa-square-poll-vertical"></i></div>
                    <span class="fw-bold">Kết quả học tập</span>
                </a>
            @else
                <div class="edu-link-parent opacity-50" style="cursor: not-allowed;">
                    <div class="edu-icon-circle bg-soft-secondary"><i class="fas fa-lock"></i></div>
                    <span class="fw-bold">Kết quả học tập</span>
                    <span class="badge bg-warning text-dark ms-auto arrow-toggle" style="font-size: 8px;">SẮP MỞ</span>
                </div>
            @endif
        </div>

        <!-- Bottom -->
        <div class="mt-5 pt-4 border-top border-light">
            <a href="{{ route('hoc-vien.profile') }}" class="edu-link-parent mb-2 {{ request()->routeIs('hoc-vien.profile') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-secondary"><i class="fas fa-id-card-clip"></i></div>
                <span class="fw-bold">Hồ sơ cá nhân</span>
            </a>
            <a href="{{ route('home') }}" class="edu-link-parent mb-4">
                <div class="edu-icon-circle bg-soft-dark"><i class="fas fa-house"></i></div>
                <span class="fw-bold">Về trang chủ</span>
            </a>
            
            <form action="{{ route('dang-xuat') }}" method="POST">
                @csrf
                <button type="submit" class="edu-btn-logout d-flex align-items-center justify-content-center py-3 bg-learner-dark">
                    <i class="fas fa-power-off fs-5"></i>
                    <span class="ms-2 fw-800">ĐĂNG XUẤT</span>
                </button>
            </form>
        </div>
    </nav>
</aside>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700;800&display=swap');

    .edu-sidebar-fixed {
        background: #ffffff !important;
        width: 280px;
        height: 100vh;
        position: fixed;
        left: 0; top: 0;
        z-index: 1050;
        box-shadow: 10px 0 40px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        font-family: 'Lexend', sans-serif;
        border-right: 1px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Collapse support */
    .sidebar-collapsed .edu-sidebar-fixed { width: 85px !important; }
    .sidebar-collapsed .edu-sidebar-fixed .sidebar-header { padding-left: 0 !important; padding-right: 0 !important; }
    .sidebar-collapsed .edu-sidebar-fixed .edu-logo-wrapper { width: 45px; height: 45px; font-size: 1.2rem; }
    .sidebar-collapsed .edu-sidebar-fixed .edu-profile-card { padding: 10px 5px; display: flex; justify-content: center; background: transparent; border: none; }
    .sidebar-collapsed .edu-sidebar-fixed .edu-avatar-box { margin: 0 auto; width: 42px; height: 42px; }
    .sidebar-collapsed .edu-sidebar-fixed .edu-link-parent { justify-content: center; padding: 12px 0; }
    .sidebar-collapsed .edu-sidebar-fixed .edu-icon-circle { margin-right: 0; }
    .sidebar-collapsed .edu-sidebar-fixed .edu-btn-logout { padding: 14px 0; }
    .sidebar-collapsed .edu-sidebar-fixed .edu-brand-name, 
    .sidebar-collapsed .edu-sidebar-fixed .edu-tagline,
    .sidebar-collapsed .edu-sidebar-fixed .edu-user-title,
    .sidebar-collapsed .edu-sidebar-fixed .edu-user-status,
    .sidebar-collapsed .edu-sidebar-fixed span,
    .sidebar-collapsed .edu-sidebar-fixed .arrow-toggle,
    .sidebar-collapsed .edu-sidebar-fixed .edu-submenu-container { display: none !important; }

    /* Custom Student Styles */
    .bg-learner { background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%) !important; }
    .bg-soft-learner { background: #eef2ff; color: #4361ee; }
    .bg-learner-dark { background: #3a0ca3 !important; }
    .bg-learner-dark:hover { background: #ef4444 !important; }

    /* Base components */
    .edu-logo-wrapper { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2rem; box-shadow: 0 8px 20px rgba(67, 97, 238, 0.2); }
    .edu-brand-name { font-weight: 800; color: #0f172a; letter-spacing: 1px; margin-bottom: 0; font-size: 1.5rem; }
    .edu-tagline { font-size: 0.75rem; font-weight: 700; color: #94a3b8; letter-spacing: 2.5px; }
    .edu-profile-card { background: #f8fafc; border: 1px solid #f1f5f9; padding: 15px; border-radius: 20px; }
    .edu-avatar-box { width: 48px; height: 48px; border-radius: 14px; overflow: hidden; background: #fff; }
    .edu-avatar-box img { width: 100%; height: 100%; object-fit: cover; }
    .edu-avatar-initials { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 1.2rem; }
    .edu-user-title { font-weight: 700; color: #1e293b; font-size: 1rem; }
    .edu-user-status { font-size: 0.75rem; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .dot-online { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; display: inline-block; box-shadow: 0 0 8px #22c55e; }
    .edu-link-parent { display: flex; align-items: center; padding: 12px 16px; color: #475569 !important; text-decoration: none !important; border-radius: 16px; font-size: 1.05rem; transition: all 0.2s; }
    .edu-link-parent:hover { background: #f1f5f9; color: #4361ee !important; }
    .edu-link-parent.active { background: #eff6ff; color: #4361ee !important; font-weight: 700; }
    .edu-icon-circle { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 14px; font-size: 1.2rem; }
    .edu-link-parent.active .edu-icon-circle { background: #4361ee !important; color: #fff !important; }
    .edu-btn-logout { width: 100%; border: none; padding: 14px; color: #fff; border-radius: 16px; font-weight: 800; font-size: 0.9rem; letter-spacing: 1.5px; transition: all 0.3s; }
    .bg-soft-primary { background: #eef2ff; color: #6366f1; }
    .bg-soft-info { background: #e0f2fe; color: #0ea5e9; }
    .bg-soft-success { background: #f0fdf4; color: #22c55e; }
    .bg-soft-warning { background: #fef3c7; color: #f59e0b; }
    .bg-soft-danger { background: #fee2e2; color: #ef4444; }
    .bg-soft-secondary { background: #f1f5f9; color: #64748b; }
    .bg-soft-dark { background: #f1f5f9; color: #0f172a; }
    .arrow-toggle { font-size: 0.85rem; opacity: 0.5; }
    .custom-scrollbar { overflow-y: auto; overflow-x: hidden; flex-grow: 1; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
