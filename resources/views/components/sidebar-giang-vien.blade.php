<aside class="sidebar edu-sidebar-fixed">
    <!-- Brand Identity -->
    <div class="sidebar-header border-0 mt-4 mb-4 px-4 text-center">
        <div class="edu-logo-wrapper mx-auto mb-2">
            <i class="fas fa-university"></i>
        </div>
        <h4 class="edu-brand-name">KHẢI TRÍ</h4>
        <div class="edu-tagline">ACADEMIC PRO</div>
    </div>

    <!-- Instructor Card -->
    <div class="px-3 mb-4">
        <div class="edu-profile-card">
            <div class="d-flex align-items-center gap-3">
                <div class="edu-avatar-box">
                    @if(auth()->user()->anh_dai_dien)
                        <img src="{{ asset(auth()->user()->anh_dai_dien) }}" class="rounded-circle">
                    @else
                        <div class="edu-avatar-initials">{{ strtoupper(mb_substr(auth()->user()->ho_ten, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="overflow-hidden">
                    <div class="edu-user-title text-truncate">{{ auth()->user()->ho_ten }}</div>
                    <div class="edu-user-status"><span class="dot-online"></span> Trực tuyến</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Scrollable Area -->
    <nav class="sidebar-nav custom-scrollbar px-3 pb-5" id="sidebarScrollContainer">
        <!-- Dashboard Item -->
        <div class="nav-item mb-3">
            <a href="{{ route('giang-vien.dashboard') }}" class="edu-link-parent {{ request()->routeIs('giang-vien.dashboard') ? 'active' : '' }}">
                <div class="edu-icon-circle bg-soft-primary"><i class="fas fa-chart-pie"></i></div>
                <span class="fw-bold">Bảng điều khiển</span>
            </a>
        </div>

        <!-- Nhóm 1: Quản lý giảng dạy -->
        <div class="nav-item mb-3">
            <a class="edu-link-parent {{ request()->routeIs('giang-vien.khoa-hoc*', 'giang-vien.lich-giang*', 'giang-vien.don-xin-nghi*') ? '' : 'collapsed' }}" 
               data-bs-toggle="collapse" data-bs-target="#teachingGroup" role="button" 
               aria-expanded="{{ request()->routeIs('giang-vien.khoa-hoc*', 'giang-vien.lich-giang*', 'giang-vien.don-xin-nghi*') ? 'true' : 'false' }}">
                <div class="edu-icon-circle bg-soft-info"><i class="fas fa-user-tie"></i></div>
                <span class="fw-bold">Quản lý đào tạo</span>
                <i class="fas fa-chevron-right ms-auto arrow-toggle"></i>
            </a>
            <div class="collapse {{ request()->routeIs('giang-vien.khoa-hoc*', 'giang-vien.lich-giang*', 'giang-vien.don-xin-nghi*') ? 'show' : '' }}" id="teachingGroup">
                <div class="edu-submenu-container">
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="edu-submenu-item {{ request()->routeIs('giang-vien.khoa-hoc*') ? 'active' : '' }}">
                        <i class="fas fa-book-reader me-2"></i> Lộ trình giảng dạy
                    </a>
                    <a href="{{ route('giang-vien.lich-giang.index') }}" class="edu-submenu-item {{ request()->routeIs('giang-vien.lich-giang*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt me-2"></i> Lịch dạy của tôi
                    </a>
                    <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="edu-submenu-item {{ request()->routeIs('giang-vien.don-xin-nghi*') ? 'active' : '' }}">
                        <i class="fas fa-file-signature me-2"></i> Đơn xin nghỉ
                    </a>
                </div>
            </div>
        </div>

        <!-- Nhóm 2: Học liệu -->
        <div class="nav-item mb-3">
            <a class="edu-link-parent {{ request()->routeIs('giang-vien.bai-giang*', 'giang-vien.thu-vien*') ? '' : 'collapsed' }}" 
               data-bs-toggle="collapse" data-bs-target="#resourceGroup" role="button"
               aria-expanded="{{ request()->routeIs('giang-vien.bai-giang*', 'giang-vien.thu-vien*') ? 'true' : 'false' }}">
                <div class="edu-icon-circle bg-soft-warning"><i class="fas fa-folder-open"></i></div>
                <span class="fw-bold">Học liệu số</span>
                <i class="fas fa-chevron-right ms-auto arrow-toggle"></i>
            </a>
            <div class="collapse {{ request()->routeIs('giang-vien.bai-giang*', 'giang-vien.thu-vien*') ? 'show' : '' }}" id="resourceGroup">
                <div class="edu-submenu-container">
                    <a href="{{ route('giang-vien.bai-giang.index') }}" class="edu-submenu-item {{ request()->routeIs('giang-vien.bai-giang*') ? 'active' : '' }}">
                        <i class="fas fa-play-circle me-2"></i> Bài giảng học tập
                    </a>
                    <a href="{{ route('giang-vien.thu-vien.index') }}" class="edu-submenu-item {{ request()->routeIs('giang-vien.thu-vien*') ? 'active' : '' }}">
                        <i class="fas fa-photo-video me-2"></i> Thư viện tài nguyên
                    </a>
                </div>
            </div>
        </div>

        <!-- Nhóm 3: Đánh giá -->
        <div class="nav-item mb-3">
            <a class="edu-link-parent {{ request()->routeIs('giang-vien.bai-kiem-tra*', 'giang-vien.cham-diem*', 'giang-vien.diem-kiem-tra*') ? '' : 'collapsed' }}"
               data-bs-toggle="collapse" data-bs-target="#examGroup" role="button"
               aria-expanded="{{ request()->routeIs('giang-vien.bai-kiem-tra*', 'giang-vien.cham-diem*', 'giang-vien.diem-kiem-tra*') ? 'true' : 'false' }}">
                <div class="edu-icon-circle bg-soft-danger"><i class="fas fa-pen-fancy"></i></div>
                <span class="fw-bold">Khảo thí & Điểm</span>
                <i class="fas fa-chevron-right ms-auto arrow-toggle"></i>
            </a>
            <div class="collapse {{ request()->routeIs('giang-vien.bai-kiem-tra*', 'giang-vien.cham-diem*', 'giang-vien.diem-kiem-tra*') ? 'show' : '' }}" id="examGroup">
                <div class="edu-submenu-container">
                    <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="edu-submenu-item {{ request()->routeIs('giang-vien.bai-kiem-tra*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list me-2"></i> Thiết lập đề thi
                    </a>
                    <a href="{{ route('giang-vien.diem-kiem-tra.index') }}" class="edu-submenu-item {{ request()->routeIs('giang-vien.diem-kiem-tra*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line me-2"></i> Bảng điểm bài kiểm tra
                    </a>
                    <a href="{{ route('giang-vien.cham-diem.index') }}" class="edu-submenu-item {{ request()->routeIs('giang-vien.cham-diem*') ? 'active' : '' }}">
                        <i class="fas fa-user-edit me-2"></i> Chấm điểm tự luận
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom section -->
        <div class="mt-5 pt-4 border-top border-light">
            <div class="nav-item mb-3">
                <a href="{{ route('giang-vien.profile') }}" class="edu-link-parent {{ request()->routeIs('giang-vien.profile') ? 'active' : '' }}">
                    <div class="edu-icon-circle bg-soft-secondary"><i class="fas fa-id-card"></i></div>
                    <span class="fw-bold">Hồ sơ cá nhân</span>
                </a>
            </div>
            <div class="nav-item mb-4">
                <a href="{{ route('home') }}" class="edu-link-parent">
                    <div class="edu-icon-circle bg-soft-dark"><i class="fas fa-external-link-alt"></i></div>
                    <span class="fw-bold">Về trang chủ</span>
                </a>
            </div>
            
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
    .sidebar-collapsed .edu-sidebar-fixed {
        width: 85px !important;
    }

    .sidebar-collapsed .edu-sidebar-fixed .sidebar-header {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .sidebar-collapsed .edu-sidebar-fixed .edu-logo-wrapper {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
    }

    .sidebar-collapsed .edu-sidebar-fixed .edu-profile-card {
        padding: 10px 5px;
        display: flex;
        justify-content: center;
        background: transparent;
        border: none;
    }

    .sidebar-collapsed .edu-sidebar-fixed .edu-avatar-box {
        margin: 0 auto;
        width: 42px;
        height: 42px;
    }

    .sidebar-collapsed .edu-sidebar-fixed .edu-link-parent {
        justify-content: center;
        padding: 12px 0;
    }

    .sidebar-collapsed .edu-sidebar-fixed .edu-icon-circle {
        margin-right: 0;
    }

    .sidebar-collapsed .edu-sidebar-fixed .edu-btn-logout {
        padding: 14px 0;
    }

    /* Brand Style */
    .edu-logo-wrapper {
        width: 60px; height: 60px; /* To hơn */
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 2rem; /* Icon to hơn */
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
    }
    .edu-brand-name { font-weight: 800; color: #0f172a; letter-spacing: 1px; margin-bottom: 0; font-size: 1.5rem; }
    .edu-tagline { font-size: 0.75rem; font-weight: 700; color: #94a3b8; letter-spacing: 2.5px; }

    /* Profile Card */
    .edu-profile-card {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        padding: 15px; /* Rộng hơn */
        border-radius: 20px;
    }
    .edu-avatar-box { width: 48px; height: 48px; border-radius: 14px; overflow: hidden; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .edu-avatar-box img { width: 100%; height: 100%; object-fit: cover; }
    .edu-avatar-initials { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #3b82f6; color: #fff; font-weight: 700; font-size: 1.2rem; }
    .edu-user-title { font-weight: 700; color: #1e293b; font-size: 1rem; } /* Chữ to hơn */
    .edu-user-status { font-size: 0.75rem; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .dot-online { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; display: inline-block; box-shadow: 0 0 8px #22c55e; }

    /* Nav Links & Parent Group */
    .edu-link-parent {
        display: flex; align-items: center;
        padding: 12px 16px; /* Padding lớn hơn */
        color: #475569 !important;
        text-decoration: none !important;
        border-radius: 16px;
        font-size: 1.05rem; /* Chữ menu chính to hơn */
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .edu-link-parent:hover { background: #f1f5f9; color: #1d4ed8 !important; }
    .edu-link-parent.active { background: #eff6ff; color: #1d4ed8 !important; }
    
    .edu-icon-circle {
        width: 42px; height: 42px; border-radius: 12px; /* Vòng tròn icon to hơn */
        display: flex; align-items: center; justify-content: center;
        margin-right: 14px; font-size: 1.2rem; transition: 0.3s; /* Icon to hơn */
    }
    .edu-link-parent.active .edu-icon-circle { background: #1d4ed8 !important; color: #fff !important; box-shadow: 0 4px 10px rgba(29, 78, 216, 0.3); }

    /* Submenu items */
    .edu-submenu-container {
        margin-left: 35px;
        margin-top: 6px;
        padding-left: 18px;
        border-left: 2px solid #f1f5f9;
    }
    .edu-submenu-item {
        display: flex; align-items: center;
        padding: 10px 14px;
        color: #64748b !important;
        text-decoration: none !important;
        font-size: 0.95rem; /* Chữ menu con to hơn */
        font-weight: 500;
        border-radius: 12px;
        margin-bottom: 4px;
        transition: all 0.2s;
    }
    .edu-submenu-item:hover { color: #1d4ed8 !important; background: #f8fafc; }
    .edu-submenu-item.active { color: #1d4ed8 !important; font-weight: 700; background: #eff6ff; }

    /* Arrow Icon Animation */
    .arrow-toggle { font-size: 0.85rem; opacity: 0.5; transition: transform 0.3s; }
    .edu-link-parent:not(.collapsed) .arrow-toggle { transform: rotate(90deg); opacity: 1; color: #1d4ed8; }

    /* Logout Button */
    .edu-btn-logout {
        width: 100%; border: none; padding: 14px;
        background: #0f172a; color: #fff;
        border-radius: 16px; font-weight: 800;
        font-size: 0.9rem; letter-spacing: 1.5px;
        transition: all 0.3s;
    }
    .edu-btn-logout:hover { background: #ef4444; box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3); transform: translateY(-2px); }

    /* Helper Backgrounds */
    .bg-soft-primary { background: #eef2ff; color: #6366f1; }
    .bg-soft-info { background: #e0f2fe; color: #0ea5e9; }
    .bg-soft-warning { background: #fef3c7; color: #f59e0b; }
    .bg-soft-danger { background: #fee2e2; color: #ef4444; }
    .bg-soft-secondary { background: #f1f5f9; color: #64748b; }
    .bg-soft-dark { background: #f1f5f9; color: #0f172a; }

    /* Custom Scrollbar */
    .custom-scrollbar { overflow-y: auto; overflow-x: hidden; flex-grow: 1; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
