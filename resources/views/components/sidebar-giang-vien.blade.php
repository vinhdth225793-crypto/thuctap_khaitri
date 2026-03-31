<aside class="sidebar shadow-sm">
    <div class="sidebar-header bg-primary text-white">
        <div class="sidebar-logo">
            <i class="fas fa-chalkboard-teacher me-2"></i>
            <span>GIANG VIEN</span>
        </div>
    </div>

    <nav class="sidebar-nav py-3">
        <div class="nav-label px-4 smaller text-muted text-uppercase fw-bold mb-2">Trung tam dieu hanh</div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.dashboard') }}" class="nav-link {{ request()->routeIs('giang-vien.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Bang dieu khien</span>
            </a>
        </div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.khoa-hoc') }}" class="nav-link {{ request()->routeIs('giang-vien.khoa-hoc*') ? 'active' : '' }}">
                <i class="fas fa-book-reader"></i>
                <span>Lo trinh giang day</span>
            </a>
        </div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.lich-giang.index') }}" class="nav-link {{ request()->routeIs('giang-vien.lich-giang*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Lich day cua toi</span>
            </a>
        </div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="nav-link {{ request()->routeIs('giang-vien.don-xin-nghi*') ? 'active' : '' }}">
                <i class="fas fa-paper-plane"></i>
                <span>Don xin nghi</span>
            </a>
        </div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.bai-giang.index') }}" class="nav-link {{ request()->routeIs('giang-vien.bai-giang*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard"></i>
                <span>Bai giang hoc tap</span>
            </a>
        </div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.thu-vien.index') }}" class="nav-link {{ request()->routeIs('giang-vien.thu-vien*') ? 'active' : '' }}">
                <i class="fas fa-book-open"></i>
                <span>Thu vien tai nguyen</span>
            </a>
        </div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="nav-link {{ request()->routeIs('giang-vien.bai-kiem-tra*', 'giang-vien.tao-bai-kiem-tra') ? 'active' : '' }}">
                <i class="fas fa-file-signature"></i>
                <span>Tao va cau hinh de</span>
            </a>
        </div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.cham-diem.index') }}" class="nav-link {{ request()->routeIs('giang-vien.cham-diem*') ? 'active' : '' }}">
                <i class="fas fa-marker"></i>
                <span>Cham diem tu luan</span>
            </a>
        </div>

        <div class="nav-label px-4 smaller text-muted text-uppercase fw-bold mb-2 mt-4">Tien ich va he thong</div>

        <div class="nav-item mb-1">
            <a href="{{ route('giang-vien.profile') }}" class="nav-link {{ request()->routeIs('giang-vien.profile') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i>
                <span>Ho so giang vien</span>
            </a>
        </div>

        <div class="nav-item mb-1">
            <a href="{{ route('home') }}" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Ve trang chu</span>
            </a>
        </div>

        <div class="nav-item mt-5 border-top pt-3">
            <form action="{{ route('dang-xuat') }}" method="POST" class="px-3">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 btn-sm fw-bold">
                    <i class="fas fa-sign-out-alt me-1"></i> Dang xuat
                </button>
            </form>
        </div>
    </nav>
</aside>

<style>
    .sidebar { width: 260px; height: 100vh; position: fixed; left: 0; top: 0; background: #fff; z-index: 1000; }
    .sidebar-header { height: 70px; display: flex; align-items: center; justify-content: center; font-weight: 800; letter-spacing: 1px; }
    .sidebar-nav .nav-link { display: flex; align-items: center; padding: 0.8rem 1.5rem; color: #4a5568; text-decoration: none; transition: all 0.3s; border-left: 4px solid transparent; }
    .sidebar-nav .nav-link i { width: 25px; font-size: 1.1rem; margin-right: 10px; }
    .sidebar-nav .nav-link:hover { background: #f7fafc; color: #3182ce; }
    .sidebar-nav .nav-link.active { background: #ebf8ff; color: #3182ce; border-left-color: #3182ce; font-weight: bold; }
    .nav-label { font-size: 0.65rem; letter-spacing: 0.05rem; }
</style>
