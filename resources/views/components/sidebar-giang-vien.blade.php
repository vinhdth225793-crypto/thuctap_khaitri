<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Giảng Viên</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="{{ route('giang-vien.dashboard') }}" class="nav-link {{ request()->routeIs('giang-vien.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('giang-vien.khoa-hoc') }}" class="nav-link {{ request()->routeIs('giang-vien.khoa-hoc') ? 'active' : '' }}">
                <i class="fas fa-chalkboard"></i>
                <span>Khóa học</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('giang-vien.tao-bai-giang') }}" class="nav-link {{ request()->routeIs('giang-vien.tao-bai-giang') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>Tạo bài giảng</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('giang-vien.tao-bai-kiem-tra') }}" class="nav-link {{ request()->routeIs('giang-vien.tao-bai-kiem-tra') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span>Tạo bài kiểm tra</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('giang-vien.cham-diem') }}" class="nav-link {{ request()->routeIs('giang-vien.cham-diem') ? 'active' : '' }}">
                <i class="fas fa-check-circle"></i>
                <span>Chấm điểm</span>
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
    </nav>
</aside>