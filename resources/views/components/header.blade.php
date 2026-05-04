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
        <div class="dropdown hd-bell-wrap">
            <button class="hd-bell-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                @if($headerNotificationCount > 0)
                    <span class="hd-bell-count">{{ $headerNotificationCount > 99 ? '99+' : $headerNotificationCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end hd-bell-menu">
                <div class="hd-bell-head">
                    <div>
                        <h6><i class="fas fa-bell"></i> Thông báo</h6>
                        @if($headerNotificationCount > 0)
                            <small>{{ $headerNotificationCount }} thông báo chưa đọc</small>
                        @else
                            <small>Tất cả đã đọc</small>
                        @endif
                    </div>
                    @if($headerNotificationCount > 0)
                        <form action="{{ route('thong-bao.mark-all-read') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="hd-bell-mark" title="Đánh dấu tất cả đã đọc">
                                <i class="fas fa-check-double"></i>
                            </button>
                        </form>
                    @endif
                </div>
                <div class="hd-bell-list">
                    @forelse($headerRecentNotifications as $tb)
                        <a class="hd-bell-item {{ $tb->da_doc ? '' : 'is-unread' }} level-{{ $tb->level ?? 'info' }}" href="{{ route('thong-bao.read', $tb->id) }}">
                            <div class="hd-bell-icon">
                                <i class="{{ $tb->icon_class }}"></i>
                            </div>
                            <div class="hd-bell-info">
                                <div class="hd-bell-title">{{ $tb->tieu_de }}</div>
                                <div class="hd-bell-text">{{ \Illuminate\Support\Str::limit($tb->noi_dung, 80) }}</div>
                                <div class="hd-bell-time"><i class="far fa-clock"></i> {{ $tb->created_at->diffForHumans() }}</div>
                            </div>
                            @if(!$tb->da_doc)
                                <span class="hd-bell-dot"></span>
                            @endif
                        </a>
                    @empty
                        <div class="hd-bell-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>Không có thông báo nào</p>
                        </div>
                    @endforelse
                </div>
                <a class="hd-bell-foot" href="{{ route('thong-bao.index') }}">
                    <i class="fas fa-arrow-right"></i> Xem tất cả thông báo
                </a>
            </div>
        </div>

<style>
    .hd-bell-wrap { position: relative; }
    .hd-bell-btn {
        width: 42px; height: 42px;
        border-radius: 12px;
        background: #fff;
        border: 1.5px solid #f1f5f9;
        color: #1e293b;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }
    .hd-bell-btn:hover, .hd-bell-btn[aria-expanded="true"] {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .hd-bell-btn i { font-size: 1.05rem; }
    .hd-bell-count {
        position: absolute;
        top: -4px; right: -4px;
        min-width: 20px;
        padding: 2px 6px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.66rem;
        font-weight: 800;
        border-radius: 999px;
        box-shadow: 0 2px 6px rgba(220,38,38,0.4);
        animation: hdBellPulse 1.6s ease-out infinite;
    }
    @keyframes hdBellPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.5); }
        50%      { box-shadow: 0 0 0 5px rgba(220, 38, 38, 0); }
    }

    .hd-bell-menu {
        width: 380px;
        padding: 0;
        border: 0;
        border-radius: 14px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }
    .hd-bell-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        background: linear-gradient(135deg, #1d4ed8 0%, #4361ee 100%);
        color: #fff;
    }
    .hd-bell-head h6 {
        margin: 0 0 2px;
        font-size: 0.95rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .hd-bell-head h6 i { font-size: 0.86rem; }
    .hd-bell-head small {
        font-size: 0.74rem;
        opacity: 0.85;
        font-weight: 500;
    }
    .hd-bell-mark {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        cursor: pointer;
        display: grid; place-items: center;
        font-size: 0.78rem;
        transition: all 0.18s ease;
    }
    .hd-bell-mark:hover { background: rgba(255,255,255,0.35); }

    .hd-bell-list {
        max-height: 420px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }
    .hd-bell-list::-webkit-scrollbar { width: 6px; }
    .hd-bell-list::-webkit-scrollbar-track { background: #f1f5f9; }
    .hd-bell-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }

    .hd-bell-item {
        display: flex;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s ease;
        position: relative;
    }
    .hd-bell-item:last-child { border-bottom: 0; }
    .hd-bell-item:hover { background: #fafafa; color: inherit; }
    .hd-bell-item.is-unread { background: #fafafa; }
    .hd-bell-item.is-unread:hover { background: #f1f5f9; }

    .hd-bell-icon {
        flex-shrink: 0;
        width: 36px; height: 36px;
        border-radius: 10px;
        display: grid; place-items: center;
        font-size: 0.92rem;
    }
    .hd-bell-item.level-info    .hd-bell-icon { background: #dbeafe; color: #1d4ed8; }
    .hd-bell-item.level-success .hd-bell-icon { background: #dcfce7; color: #16a34a; }
    .hd-bell-item.level-warning .hd-bell-icon { background: #fef3c7; color: #b45309; }
    .hd-bell-item.level-danger  .hd-bell-icon { background: #fee2e2; color: #dc2626; }

    .hd-bell-info { flex: 1; min-width: 0; }
    .hd-bell-title {
        font-size: 0.84rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
        margin-bottom: 3px;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .hd-bell-text {
        font-size: 0.76rem;
        color: #64748b;
        line-height: 1.4;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .hd-bell-time {
        font-size: 0.7rem;
        color: #94a3b8;
        font-weight: 600;
    }
    .hd-bell-time i { color: #1d4ed8; margin-right: 3px; font-size: 0.62rem; }
    .hd-bell-dot {
        position: absolute;
        top: 14px; right: 12px;
        width: 8px; height: 8px;
        background: #dc2626;
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.18);
    }

    .hd-bell-empty {
        padding: 40px 20px;
        text-align: center;
        color: #94a3b8;
    }
    .hd-bell-empty i { font-size: 2rem; opacity: 0.4; display: block; margin-bottom: 8px; }
    .hd-bell-empty p { font-size: 0.84rem; margin: 0; }

    .hd-bell-foot {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 10px 14px;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #dc2626;
        font-size: 0.82rem;
        font-weight: 800;
        text-decoration: none;
        border-top: 1px solid #fecaca;
        transition: all 0.18s ease;
    }
    .hd-bell-foot:hover { background: #dc2626; color: #fff; }
    .hd-bell-foot i { font-size: 0.74rem; }
</style>
        
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
