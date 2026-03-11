<header class="header">
    <div class="header-left">
        <button class="btn d-md-none" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h1>@yield('title', 'Dashboard')</h1>
    </div>
    
    <div class="header-right">
        <!-- Thông báo -->
        <div class="dropdown">
            <button class="btn position-relative" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    3
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <h6 class="dropdown-header">Thông báo</h6>
                <a class="dropdown-item" href="#">
                    <div class="d-flex w-100 justify-content-between">
                        <small>Có bài tập mới</small>
                        <small>5 phút trước</small>
                    </div>
                </a>
                <a class="dropdown-item" href="#">
                    <div class="d-flex w-100 justify-content-between">
                        <small>Thông báo hệ thống</small>
                        <small>1 giờ trước</small>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center" href="#">Xem tất cả</a>
            </div>
        </div>
        
        <!-- User Profile -->
        <div class="dropdown">
            <div class="user-profile" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->ho_ten, 0, 1)) }}
                </div>
                <div class="user-info d-none d-md-block">
                    <h6>{{ auth()->user()->ho_ten }}</h6>
                    <small>
                        @if(auth()->user()->vai_tro === 'admin')
                            Quản trị viên
                        @elseif(auth()->user()->vai_tro === 'giang_vien')
                            Giảng viên
                        @else
                            Học viên
                        @endif
                    </small>
                </div>
            </div>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('profile') }}">
                    <i class="fas fa-user me-2"></i> Hồ sơ
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cog me-2"></i> Cài đặt
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('dang-xuat') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>