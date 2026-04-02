<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ thống Quản lý')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- GSAP for advanced animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --info-color: #7209b7;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: #333;
            min-height: 100vh;
        }
        
        /* Layout Styles */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            color: #333;
            position: fixed;
            height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            box-shadow: 10px 0 40px rgba(0, 0, 0, 0.03);
            overflow-x: hidden;
        }
        
        .app-wrapper.sidebar-collapsed .sidebar {
            width: 85px;
        }

        .app-wrapper.sidebar-collapsed .main-content {
            margin-left: 85px;
        }

        /* Hide text elements in collapsed state */
        .app-wrapper.sidebar-collapsed .edu-brand-name,
        .app-wrapper.sidebar-collapsed .edu-tagline,
        .app-wrapper.sidebar-collapsed .edu-profile-card .overflow-hidden,
        .app-wrapper.sidebar-collapsed .edu-link-parent span,
        .app-wrapper.sidebar-collapsed .arrow-toggle,
        .app-wrapper.sidebar-collapsed .edu-submenu-container,
        .app-wrapper.sidebar-collapsed .edu-btn-logout span {
            display: none !important;
        }

        .app-wrapper.sidebar-collapsed .edu-logo-wrapper {
            width: 45px; height: 45px;
            margin: 0 auto;
        }

        .app-wrapper.sidebar-collapsed .sidebar-header {
            padding: 30px 10px;
        }

        .app-wrapper.sidebar-collapsed .edu-link-parent {
            justify-content: center;
            padding: 12px 0;
            margin: 0 10px 8px;
        }

        .app-wrapper.sidebar-collapsed .edu-icon-circle {
            margin-right: 0;
            width: 45px;
            height: 45px;
        }

        .app-wrapper.sidebar-collapsed .edu-profile-card {
            padding: 10px 0;
            background: transparent;
            border: none;
            justify-content: center;
        }

        .app-wrapper.sidebar-collapsed .edu-avatar-box {
            width: 45px;
            height: 45px;
            margin: 0 auto;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .header {
            height: var(--header-height);
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0;
            color: var(--dark-color);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .user-info h6 {
            margin-bottom: 0;
            font-weight: 600;
        }
        
        .user-info small {
            color: #6c757d;
        }
        
        /* Content Area */
        .content-wrapper {
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
        }
        
        /* Card Styles */
        .vip-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .vip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        
        .vip-card-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            background-color: transparent;
        }
        
        .vip-card-body {
            padding: 20px;
        }
        
        .vip-card-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0;
        }
        
        /* Button Styles */
        .vip-btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
        }
        
        .vip-btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .vip-btn-primary:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .vip-btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .vip-btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .vip-btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .vip-btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        /* Form Styles */
        .vip-form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .vip-form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        /* Alert Styles */
        .vip-alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }
        
        /* Auth Pages */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fb 0%, #e4edf5 100%);
            padding: 20px;
        }
        
        .auth-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .auth-header {
            text-align: center;
            padding: 40px 30px 20px;
        }
        
        .auth-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }
        
        .auth-title {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .auth-subtitle {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .auth-body {
            padding: 0 30px 30px;
        }
        
        .auth-footer {
            padding: 20px 30px;
            background-color: #f8f9fa;
            text-align: center;
            border-top: 1px solid #eee;
        }
        
        /* Animation */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-wrapper {
                padding: 20px;
            }
            
            .header {
                padding: 0 20px;
            }
        }
        
        /* Loading Button */
        .btn-loading {
            position: relative;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }

                /* Thêm vào phần style đã có */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, var(--secondary-color), var(--primary-color));
        }

        /* Sidebar Submenu Styles */
        .nav-submenu {
            display: flex;
            flex-direction: column;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin: 5px 10px;
            overflow: hidden;
        }

        .nav-submenu .nav-link {
            padding: 10px 20px 10px 40px;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
            border-radius: 0;
        }

        .nav-submenu .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: white;
        }

        .nav-submenu .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: white;
        }

        .nav-link[data-bs-toggle="collapse"]::after {
            display: none;
        }

        .nav-link[data-bs-toggle="collapse"] .fa-chevron-down {
            transition: transform 0.3s;
        }

        .nav-link[data-bs-toggle="collapse"]:not(.collapsed) .fa-chevron-down {
            transform: rotate(180deg);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @auth
        <!-- Layout có sidebar cho người dùng đã đăng nhập -->
        <div class="app-wrapper">
            <!-- Sidebar sẽ được include tùy theo vai trò -->
            @if(auth()->user()->vai_tro === 'admin')
                @include('components.sidebar-admin')
            @elseif(auth()->user()->vai_tro === 'giang_vien')
                @include('components.sidebar-giang-vien')
            @else
                @include('components.sidebar-hoc-vien')
            @endif
            
            <div class="main-content">
                <!-- Header -->
                @include('components.header')
                
                <!-- Main Content -->
                <main class="content-wrapper">
                    @yield('content')
                </main>
                
                <!-- Footer -->
                @include('components.footer')
            </div>
        </div>
    @else
        <!-- Layout đơn giản cho trang auth (chưa đăng nhập) -->
        @yield('content')
    @endauth
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (nếu cần) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Toggle sidebar trên mobile
        function toggleSidebarMobile() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }

        // Toggle sidebar trên desktop (Thu gọn/Mở rộng)
        function toggleSidebarDesktop() {
            const wrapper = document.querySelector('.app-wrapper');
            const icon = document.querySelector('#desktopSidebarToggle i');
            
            wrapper.classList.toggle('sidebar-collapsed');
            
            // Đổi icon và lưu trạng thái
            if (wrapper.classList.contains('sidebar-collapsed')) {
                localStorage.setItem('sidebar-state', 'collapsed');
            } else {
                localStorage.setItem('sidebar-state', 'expanded');
            }
        }
        
        // --- Xử lý giữ vị trí cuộn Sidebar ---
        const SIDEBAR_SCROLL_KEY = 'sidebar_scroll_pos';
        const WINDOW_SCROLL_KEY = 'window_scroll_pos';
        
        function saveSidebarScroll() {
            const sidebarNav = document.getElementById('sidebarScrollContainer');
            if (sidebarNav) {
                sessionStorage.setItem(SIDEBAR_SCROLL_KEY, sidebarNav.scrollTop);
            }
        }

        function restoreSidebarScroll() {
            const sidebarNav = document.getElementById('sidebarScrollContainer');
            const scrollPos = sessionStorage.getItem(SIDEBAR_SCROLL_KEY);
            if (sidebarNav && scrollPos) {
                sidebarNav.scrollTop = scrollPos;
            }
        }

        function saveWindowScroll() {
            sessionStorage.setItem(WINDOW_SCROLL_KEY, window.scrollY);
        }

        function restoreWindowScroll() {
            const scrollPos = sessionStorage.getItem(WINDOW_SCROLL_KEY);
            if (scrollPos) {
                window.scrollTo(0, parseInt(scrollPos));
                // Xóa sau khi khôi phục để tránh nhảy trang ngoài ý muốn khi F5 thủ công
                sessionStorage.removeItem(WINDOW_SCROLL_KEY);
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Khôi phục trạng thái thu gọn/mở rộng
            const sidebarState = localStorage.getItem('sidebar-state');
            const wrapper = document.querySelector('.app-wrapper');
            if (sidebarState === 'collapsed' && wrapper) {
                wrapper.classList.add('sidebar-collapsed');
            }

            // 2. Khôi phục vị trí cuộn (Cả sidebar và cửa sổ chính)
            restoreSidebarScroll();
            restoreWindowScroll();

            // 3. Lưu vị trí cuộn khi người dùng cuộn hoặc nhấn link
            const sidebarNav = document.getElementById('sidebarScrollContainer');
            if (sidebarNav) {
                sidebarNav.addEventListener('scroll', saveSidebarScroll);
                
                // Lưu khi nhấn vào bất kỳ link nào trong sidebar
                const links = sidebarNav.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', () => {
                        saveSidebarScroll();
                        saveWindowScroll();
                    });
                });

                // Ngăn chặn sidebar cuộn lên đầu khi nhấn vào nút collapse (menu con)
                const collapseButtons = sidebarNav.querySelectorAll('[data-bs-toggle="collapse"]');
                collapseButtons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        saveSidebarScroll();
                    });
                });
            }

            // Lưu vị trí cửa sổ khi submit bất kỳ form nào
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', saveWindowScroll);
            });

            // Xử lý loading button
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('btn-loading');
                        submitBtn.disabled = true;
                    }
                }, false);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>