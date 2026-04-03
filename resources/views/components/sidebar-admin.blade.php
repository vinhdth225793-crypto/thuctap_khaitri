<aside class="sidebar edu-sidebar-fixed">
    <!-- Brand Identity -->
    <div class="sidebar-header border-0 mt-4 mb-4 px-4 text-center">
        <div class="edu-logo-wrapper mx-auto mb-2">
            <i class="fas fa-shield-halved"></i>
        </div>
        <h4 class="edu-brand-name">QUẢN TRỊ</h4>
        <div class="edu-tagline">SYSTEM CONTROL</div>
    </div>

    <!-- Admin Card -->
    <div class="px-3 mb-4">
        <div class="edu-profile-card">
            <div class="d-flex align-items-center gap-3">
                <div class="edu-avatar-box">
                    @if(auth()->user()->anh_dai_dien)
                        <img src="{{ asset(auth()->user()->anh_dai_dien) }}" class="rounded-circle">
                    @else
                        <div class="edu-avatar-initials bg-danger">{{ strtoupper(mb_substr(auth()->user()->ho_ten, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="overflow-hidden">
                    <div class="edu-user-title text-truncate">{{ auth()->user()->ho_ten }}</div>
                    <div class="edu-user-status"><span class="dot-online"></span> Admin System</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Scrollable Area -->
    <nav class="sidebar-nav custom-scrollbar px-3 pb-5" id="sidebarScrollContainer">
        <!-- Dashboard -->
        <div class="nav-item mb-3">
            <a href="{{ route('admin.dashboard') }}" class="edu-link-parent {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-primary"><i class="fas fa-gauge-high"></i></div>
                <span class="fw-bold">Bảng điều khiển</span>
            </a>
        </div>

        <!-- Quản lý tài khoản -->
        <div class="nav-item mb-3">
            <a class="edu-link-parent {{ request()->routeIs('admin.hoc-vien.*', 'admin.giang-vien.*', 'admin.phe-duyet-tai-khoan.*', 'admin.giang-vien-don-xin-nghi.*') ? '' : 'collapsed' }}" 
               data-bs-toggle="collapse" data-bs-target="#accountGroup" role="button" 
               aria-expanded="{{ request()->routeIs('admin.hoc-vien.*', 'admin.giang-vien.*', 'admin.phe-duyet-tai-khoan.*', 'admin.giang-vien-don-xin-nghi.*') ? 'true' : 'false' }}">
                <div class="edu-icon-circle bg-soft-info"><i class="fas fa-users-gear"></i></div>
                <span class="fw-bold">Quản lý tài khoản</span>
                @if($pendingAccountsCount > 0)
                    <span class="badge bg-danger rounded-pill ms-2 d-inline-block" style="font-size: 10px;">{{ $pendingAccountsCount }}</span>
                @endif
                <i class="fas fa-chevron-right ms-auto arrow-toggle"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.hoc-vien.*', 'admin.giang-vien.*', 'admin.phe-duyet-tai-khoan.*', 'admin.giang-vien-don-xin-nghi.*') ? 'show' : '' }}" id="accountGroup">
                <div class="edu-submenu-container">
                    <a href="{{ route('admin.hoc-vien.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.hoc-vien.*') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate me-2"></i> Học viên
                    </a>
                    <a href="{{ route('admin.giang-vien.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.giang-vien.*') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-user me-2"></i> Giảng viên
                    </a>
                    <a href="{{ route('admin.phe-duyet-tai-khoan.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.phe-duyet-tai-khoan.*') ? 'active' : '' }}">
                        <i class="fas fa-user-check me-2"></i> Phê duyệt
                    </a>
                    <a href="{{ route('admin.giang-vien-don-xin-nghi.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.giang-vien-don-xin-nghi.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-minus me-2"></i> Đơn nghỉ GV
                    </a>
                </div>
            </div>
        </div>

        <!-- Quản lý khóa học -->
        <div class="nav-item mb-3">
            <a class="edu-link-parent {{ request()->routeIs('admin.mon-hoc.*', 'admin.khoa-hoc.*', 'admin.module-hoc.*') ? '' : 'collapsed' }}" 
               data-bs-toggle="collapse" data-bs-target="#courseGroup" role="button"
               aria-expanded="{{ request()->routeIs('admin.mon-hoc.*', 'admin.khoa-hoc.*', 'admin.module-hoc.*') ? 'true' : 'false' }}">
                <div class="edu-icon-circle bg-soft-warning"><i class="fas fa-layer-group"></i></div>
                <span class="fw-bold">Chương trình học</span>
                <i class="fas fa-chevron-right ms-auto arrow-toggle"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.mon-hoc.*', 'admin.khoa-hoc.*', 'admin.module-hoc.*') ? 'show' : '' }}" id="courseGroup">
                <div class="edu-submenu-container">
                    <a href="{{ route('admin.mon-hoc.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.mon-hoc.*') ? 'active' : '' }}">
                        <i class="fas fa-tags me-2"></i> Nhóm ngành
                    </a>
                    <a href="{{ route('admin.khoa-hoc.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.khoa-hoc.*') ? 'active' : '' }}">
                        <i class="fas fa-book me-2"></i> Khóa học
                    </a>
                    <a href="{{ route('admin.module-hoc.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.module-hoc.*') ? 'active' : '' }}">
                        <i class="fas fa-cubes me-2"></i> Module học
                    </a>
                </div>
            </div>
        </div>

        <div class="nav-item mb-3">
            <a href="{{ route('admin.diem-danh.index') }}" class="edu-link-parent {{ request()->routeIs('admin.diem-danh.*') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-primary"><i class="fas fa-user-check"></i></div>
                <span class="fw-bold">Điểm danh</span>
            </a>
        </div>

        <!-- Nội dung & Khảo thí -->
        <div class="nav-item mb-3">
            <a class="edu-link-parent {{ request()->routeIs('admin.thu-vien.*', 'admin.bai-giang.*', 'admin.kiem-tra-online.*') ? '' : 'collapsed' }}" 
               data-bs-toggle="collapse" data-bs-target="#contentGroup" role="button"
               aria-expanded="{{ request()->routeIs('admin.thu-vien.*', 'admin.bai-giang.*', 'admin.kiem-tra-online.*') ? 'true' : 'false' }}">
                <div class="edu-icon-circle bg-soft-danger"><i class="fas fa-vial-circle-check"></i></div>
                <span class="fw-bold">Nội dung & Khảo thí</span>
                <i class="fas fa-chevron-right ms-auto arrow-toggle"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.thu-vien.*', 'admin.bai-giang.*', 'admin.kiem-tra-online.*') ? 'show' : '' }}" id="contentGroup">
                <div class="edu-submenu-container">
                    <a href="{{ route('admin.thu-vien.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.thu-vien.*') ? 'active' : '' }}">
                        <i class="fas fa-folder-tree me-2"></i> Thư viện số
                    </a>
                    <a href="{{ route('admin.bai-giang.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.bai-giang.*') ? 'active' : '' }}">
                        <i class="fas fa-check-double me-2"></i> Duyệt bài giảng
                    </a>
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.kiem-tra-online.cau-hoi.*') ? 'active' : '' }}">
                        <i class="fas fa-database me-2"></i> Ngân hàng câu hỏi
                    </a>
                    <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.kiem-tra-online.phe-duyet.*') ? 'active' : '' }}">
                        <i class="fas fa-stamp me-2"></i> Phê duyệt đề thi
                    </a>
                </div>
            </div>
        </div>

        <!-- Cài đặt hệ thống -->
        <div class="nav-item mb-3">
            <a class="edu-link-parent {{ request()->routeIs('admin.settings*', 'admin.yeu-cau-hoc-vien.*') ? '' : 'collapsed' }}" 
               data-bs-toggle="collapse" data-bs-target="#settingGroup" role="button"
               aria-expanded="{{ request()->routeIs('admin.settings*', 'admin.yeu-cau-hoc-vien.*') ? 'true' : 'false' }}">
                <div class="edu-icon-circle bg-soft-dark"><i class="fas fa-gears"></i></div>
                <span class="fw-bold">Hệ thống</span>
                <i class="fas fa-chevron-right ms-auto arrow-toggle"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.settings*', 'admin.yeu-cau-hoc-vien.*') ? 'show' : '' }}" id="settingGroup">
                <div class="edu-submenu-container">
                    <a href="{{ route('admin.settings') }}" class="edu-submenu-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <i class="fas fa-sliders me-2"></i> Cấu hình chung
                    </a>
                    <a href="{{ route('admin.yeu-cau-hoc-vien.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.yeu-cau-hoc-vien.*') ? 'active' : '' }}">
                        <i class="fas fa-comment-dots me-2"></i> Yêu cầu học viên
                    </a>
                    <a href="{{ route('admin.settings.banners.index') }}" class="edu-submenu-item {{ request()->routeIs('admin.settings.banners.*') ? 'active' : '' }}">
                        <i class="fas fa-images me-2"></i> Quản lý Banner
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom -->
        <div class="mt-5 pt-4 border-top border-light">
            <a href="{{ route('profile') }}" class="edu-link-parent mb-2 {{ request()->routeIs('profile') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-secondary"><i class="fas fa-id-card"></i></div>
                <span class="fw-bold">Hồ sơ cá nhân</span>
            </a>
            <a href="{{ route('home') }}" class="edu-link-parent mb-4">
                <div class="edu-icon-circle bg-soft-dark"><i class="fas fa-house"></i></div>
                <span class="fw-bold">Về trang chủ</span>
            </a>
            
            <form action="{{ route('dang-xuat') }}" method="POST">
                @csrf
                <button type="submit" class="edu-btn-logout d-flex align-items-center justify-content-center py-3">
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

    /* Styles copied from instructor sidebar for consistency */
    .edu-logo-wrapper { width: 60px; height: 60px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2rem; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2); }
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
    .edu-link-parent:hover { background: #f1f5f9; color: #0f172a !important; }
    .edu-link-parent.active { background: #f1f5f9; color: #0f172a !important; font-weight: 700; }
    .edu-icon-circle { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 14px; font-size: 1.2rem; }
    .edu-link-parent.active .edu-icon-circle { background: #0f172a !important; color: #fff !important; }
    .edu-submenu-container { margin-left: 35px; margin-top: 6px; padding-left: 18px; border-left: 2px solid #f1f5f9; }
    .edu-submenu-item { display: flex; align-items: center; padding: 10px 14px; color: #64748b !important; text-decoration: none !important; font-size: 0.95rem; font-weight: 500; border-radius: 12px; margin-bottom: 4px; transition: all 0.2s; }
    .edu-submenu-item:hover { color: #0f172a !important; background: #f8fafc; }
    .edu-submenu-item.active { color: #0f172a !important; font-weight: 700; background: #f1f5f9; }
    .arrow-toggle { font-size: 0.85rem; opacity: 0.5; transition: transform 0.3s; }
    .edu-link-parent:not(.collapsed) .arrow-toggle { transform: rotate(90deg); opacity: 1; color: #0f172a; }
    .edu-btn-logout { width: 100%; border: none; padding: 14px; background: #0f172a; color: #fff; border-radius: 16px; font-weight: 800; font-size: 0.9rem; letter-spacing: 1.5px; transition: all 0.3s; }
    .edu-btn-logout:hover { background: #ef4444; }
    .bg-soft-primary { background: #eef2ff; color: #6366f1; }
    .bg-soft-info { background: #e0f2fe; color: #0ea5e9; }
    .bg-soft-warning { background: #fef3c7; color: #f59e0b; }
    .bg-soft-danger { background: #fee2e2; color: #ef4444; }
    .bg-soft-secondary { background: #f1f5f9; color: #64748b; }
    .bg-soft-dark { background: #f1f5f9; color: #0f172a; }
    .custom-scrollbar { overflow-y: auto; overflow-x: hidden; flex-grow: 1; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>




