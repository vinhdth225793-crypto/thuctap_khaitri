<aside class="sidebar">
    @php
        $hasBaiKiemTraRoute = Route::has('hoc-vien.bai-kiem-tra');
        $hasKetQuaRoute = Route::has('hoc-vien.ket-qua');
    @endphp

    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-user-graduate"></i>
            <span>Hoc Vien</span>
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
                <span>Khoa hoc cua toi</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('hoc-vien.hoat-dong-tien-do') }}" class="nav-link {{ request()->routeIs('hoc-vien.hoat-dong-tien-do') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Hoat dong &amp; tien do</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="nav-link {{ request()->routeIs('hoc-vien.khoa-hoc-tham-gia') ? 'active' : '' }}">
                <i class="fas fa-user-plus"></i>
                <span>Xin vao lop</span>
            </a>
        </div>

        <div class="nav-item">
            @if($hasBaiKiemTraRoute)
                <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="nav-link {{ request()->routeIs('hoc-vien.bai-kiem-tra*') ? 'active' : '' }}">
                    <i class="fas fa-tasks"></i>
                    <span>Bai kiem tra</span>
                </a>
            @else
                <span class="nav-link opacity-75" aria-disabled="true">
                    <i class="fas fa-tasks"></i>
                    <span>Bai kiem tra</span>
                    <span class="badge bg-warning text-dark ms-auto">Sap mo</span>
                </span>
            @endif
        </div>

        <div class="nav-item">
            @if($hasKetQuaRoute)
                <a href="{{ route('hoc-vien.ket-qua') }}" class="nav-link {{ request()->routeIs('hoc-vien.ket-qua') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Ket qua hoc tap</span>
                </a>
            @else
                <span class="nav-link opacity-75" aria-disabled="true">
                    <i class="fas fa-chart-bar"></i>
                    <span>Ket qua hoc tap</span>
                    <span class="badge bg-warning text-dark ms-auto">Sap mo</span>
                </span>
            @endif
        </div>

        <div class="nav-item">
            <a href="{{ route('home') }}" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Trang chu</span>
            </a>
        </div>

        <div class="nav-item mt-4">
            <a href="{{ route('hoc-vien.profile') }}" class="nav-link {{ request()->routeIs('hoc-vien.profile') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Ho so ca nhan</span>
            </a>
        </div>

        <div class="nav-item">
            <form method="POST" action="{{ route('dang-xuat') }}" id="logout-form">
                @csrf
                <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Dang xuat</span>
                </a>
            </form>
        </div>
    </nav>
</aside>
