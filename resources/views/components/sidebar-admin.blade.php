<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-graduation-cap"></i>
            <span>Hệ thống QL</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <!-- Quản lý tài khoản với submenu -->
        <div class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#collapseAccount" role="button" aria-expanded="{{ request()->routeIs('admin.quan-ly-hoc-vien', 'admin.quan-ly-giang-vien', 'admin.phe-duyet-tai-khoan.*') ? 'true' : 'false' }}" aria-controls="collapseAccount">
                <i class="fas fa-users"></i>
                <span>Quản lý tài khoản</span>
                <span style="display: flex; gap: 8px; align-items: center; margin-left: auto;">
                    @if($pendingAccountsCount > 0)
                        <i class="fas fa-exclamation-circle text-danger" style="font-size: 1rem;"></i>
                    @endif
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <div class="collapse {{ request()->routeIs('admin.quan-ly-hoc-vien', 'admin.quan-ly-giang-vien', 'admin.phe-duyet-tai-khoan.*') ? 'show' : '' }}" id="collapseAccount">
                <div class="nav-submenu">
                    <a href="{{ route('admin.hoc-vien.index') }}" class="nav-link {{ request()->routeIs('admin.hoc-vien.*', 'admin.quan-ly-hoc-vien') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i>
                        <span>Học viên</span>
                    </a>
                    <a href="{{ route('admin.giang-vien.index') }}" class="nav-link {{ request()->routeIs('admin.giang-vien.*', 'admin.quan-ly-giang-vien') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-user"></i>
                        <span>Giảng viên</span>
                    </a>
                    <a href="{{ route('admin.phe-duyet-tai-khoan.index') }}" class="nav-link {{ request()->routeIs('admin.phe-duyet-tai-khoan.*') ? 'active' : '' }}">
                        <i class="fas fa-user-check"></i>
                        <span>Phê duyệt tài khoản</span>
                        @if($pendingAccountsCount > 0)
                            <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingAccountsCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quản lý khóa học với submenu -->
        <div class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#collapseCourse" role="button" aria-expanded="{{ request()->routeIs('admin.mon-hoc.*', 'admin.khoa-hoc.*', 'admin.module-hoc.*', 'admin.quan-ly-khoa-hoc') ? 'true' : 'false' }}" aria-controls="collapseCourse">
                <i class="fas fa-book"></i>
                <span>Quản lý khóa học</span>
                <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.mon-hoc.*', 'admin.khoa-hoc.*', 'admin.module-hoc.*', 'admin.quan-ly-khoa-hoc') ? 'show' : '' }}" id="collapseCourse">
                <div class="nav-submenu">
                    <a href="{{ route('admin.mon-hoc.index') }}" class="nav-link {{ request()->routeIs('admin.mon-hoc.*') ? 'active' : '' }}">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Môn học</span>
                    </a>
                    <a href="{{ route('admin.khoa-hoc.index') }}" class="nav-link {{ request()->routeIs('admin.khoa-hoc.*') ? 'active' : '' }}">
                        <i class="fas fa-layer-group"></i>
                        <span>Khóa học</span>
                    </a>
                    <a href="{{ route('admin.module-hoc.index') }}" class="nav-link {{ request()->routeIs('admin.module-hoc.*') ? 'active' : '' }}">
                        <i class="fas fa-cube"></i>
                        <span>Module</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Cài đặt hệ thống dropdown -->
        <div class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#settingsMenu" role="button" aria-expanded="{{ request()->routeIs('admin.settings*') ? 'true' : 'false' }}" aria-controls="settingsMenu">
                <i class="fas fa-cog"></i>
                <span>Cài đặt hệ thống</span>
                <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.settings*') ? 'show' : '' }}" id="settingsMenu">
                <div class="nav-submenu">
                    <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tổng quan</span>
                    </a>
                    <a href="{{ route('admin.settings.contact') }}" class="nav-link {{ request()->routeIs('admin.settings.contact') ? 'active' : '' }}">
                        <i class="fas fa-phone"></i>
                        <span>Thông tin liên hệ</span>
                    </a>
                    <a href="{{ route('admin.settings.social') }}" class="nav-link {{ request()->routeIs('admin.settings.social') ? 'active' : '' }}">
                        <i class="fas fa-share-alt"></i>
                        <span>Mạng xã hội</span>
                    </a>
                    <a href="{{ route('admin.settings.instructors') }}" class="nav-link {{ request()->routeIs('admin.settings.instructors') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Giảng viên</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="nav-item mt-4">
            <a href="{{ route('profile') }}" class="nav-link">
                <i class="fas fa-user"></i>
                <span>Hồ sơ cá nhân</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ route('home', ['preview' => 1]) }}" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Trang chủ</span>
            </a>
        </div>
    </nav>
</aside>