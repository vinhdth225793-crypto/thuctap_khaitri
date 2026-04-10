<header class="header">
    <div class="header-left d-flex align-items-center gap-3">
        <!-- Mobile Toggle -->
        <button class="btn d-md-none p-0 border-0" onclick="toggleSidebarMobile()">
            <i class="fas fa-bars fs-4"></i>
        </button>
        <!-- Desktop Toggle -->
        <button class="btn d-none d-md-flex align-items-center justify-content-center sidebar-toggle-btn" id="desktopSidebarToggle" onclick="toggleSidebarDesktop()" title="Thu gọn/Mở rộng menu">
            <div class="toggle-icon-wrapper">
                <i class="fas fa-bars-staggered" id="toggleIcon"></i>
            </div>
        </button>
        <h1 class="ms-2">@yield('title', 'Dashboard')</h1>
    </div>

<style>
    .sidebar-toggle-btn {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #ffffff;
        border: 1.5px solid #f1f5f9;
        color: #1e293b;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    
    .sidebar-toggle-btn:hover {
        background: #1d4ed8;
        color: #ffffff;
        border-color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.15);
    }

    .sidebar-toggle-btn:active {
        transform: scale(0.95);
    }

    .toggle-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .sidebar-toggle-btn i {
        font-size: 1.2rem;
        transition: transform 0.4s cubic-bezier(0.68, -0.6, 0.32, 1.6);
    }

    /* Khi sidebar bị thu gọn, icon sẽ xoay */
    .sidebar-collapsed .sidebar-toggle-btn i {
        transform: rotate(180deg);
    }
</style>
    
    <div class="header-right">
        <a href="{{ route('home') }}"
           class="btn btn-sm d-flex align-items-center gap-2"
           style="background:#eff6ff; color:#2563eb; border:1.5px solid #bfdbfe;
                  border-radius:8px; padding:6px 14px; font-weight:700; font-size:12px;
                  text-decoration:none; white-space:nowrap;"
           title="Xem trang chủ"
           onmouseover="this.style.background='#dbeafe'"
           onmouseout="this.style.background='#eff6ff'">
            <i class="fas fa-home"></i>
            <span class="d-none d-md-inline">Trang chủ</span>
        </a>
        <!-- Thông báo -->
        <div class="dropdown">
            <button class="btn position-relative" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-bell"></i>
                @if($headerNotificationCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $headerNotificationCount > 99 ? '99+' : $headerNotificationCount }}
                    </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 320px;">
                <h6 class="dropdown-header d-flex justify-content-between align-items-center py-3">
                    <span>Thông báo</span>
                    @if($headerNotificationCount > 0)
                        <span class="badge bg-danger rounded-pill">{{ $headerNotificationCount }} mới</span>
                    @endif
                </h6>
                <div class="dropdown-divider m-0"></div>
                <div style="max-height: 350px; overflow-y: auto;">
                    @forelse($headerRecentNotifications as $tb)
                        <a class="dropdown-item py-3 border-bottom {{ $tb->da_doc ? '' : 'bg-light' }}" href="{{ route('thong-bao.doc-mot', $tb->id) }}">
                            <div class="d-flex align-items-start gap-2">
                                <div class="mt-1">
                                    @if($tb->loai === 'phan_cong')
                                        <i class="fas fa-user-tie text-info"></i>
                                    @elseif($tb->loai === 'mo_lop')
                                        <i class="fas fa-rocket text-success"></i>
                                    @elseif($tb->loai === 'xac_nhan_gv')
                                        <i class="fas fa-check-circle text-primary"></i>
                                    @else
                                        <i class="fas fa-info-circle text-secondary"></i>
                                    @endif
                                </div>
                                <div class="flex-fill">
                                    <div class="small fw-bold text-dark text-wrap">{{ $tb->tieu_de }}</div>
                                    <div class="smaller text-muted mt-1">{{ $tb->created_at->diffForHumans() }}</div>
                                </div>
                                @if(!$tb->da_doc)
                                    <div class="rounded-circle bg-danger" style="width: 8px; height: 8px; margin-top: 5px;"></div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted small">Không có thông báo nào</div>
                    @endforelse
                </div>
                <div class="dropdown-divider m-0"></div>
                <a class="dropdown-item text-center py-2 fw-bold small text-primary" href="{{ route('thong-bao.index') }}">Xem tất cả thông báo</a>
            </div>
        </div>
        
        <!-- User Profile -->
        <div class="dropdown">
            <div class="user-profile" data-bs-toggle="dropdown">
                <div class="user-avatar" style="overflow:hidden; padding:0;">
                    @if(auth()->user()->anh_dai_dien)
                        <img src="{{ asset(auth()->user()->anh_dai_dien) }}"
                             alt="{{ auth()->user()->ho_ten }}"
                             style="width:100%; height:100%; object-fit:cover; display:block;">
                    @else
                        <span style="display:flex; align-items:center; justify-content:center;
                                     width:100%; height:100%; font-weight:700; font-size:15px;">
                            {{ strtoupper(mb_substr(auth()->user()->ho_ten, 0, 1)) }}
                        </span>
                    @endif
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
