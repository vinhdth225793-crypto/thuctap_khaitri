<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-user-graduate"></i>
            <span>Học Viên</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="{{ route('hoc-vien.dashboard') }}" class="nav-link {{ request()->routeIs('hoc-vien.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="nav-link {{ request()->routeIs('hoc-vien.khoa-hoc-cua-toi') ? 'active' : '' }}">
                <i class="fas fa-book"></i>
                <span>Khóa học của tôi</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="nav-link {{ request()->routeIs('hoc-vien.bai-kiem-tra') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span>Bài kiểm tra</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('hoc-vien.ket-qua') }}" class="nav-link {{ request()->routeIs('hoc-vien.ket-qua') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Kết quả học tập</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('home') }}" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Trang chủ</span>
            </a>
        </div>
        
        <div class="nav-item mt-4">
            <a href="{{ route('profile') }}" class="nav-link">
                <i class="fas fa-user"></i>
                <span>Hồ sơ cá nhân</span>
            </a>
        </div>
        
        <div class="nav-item">
            <form method="POST" action="{{ route('dang-xuat') }}" id="logout-form">
                @csrf
                <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </a>
            </form>
        </div>
    </nav>
</aside>