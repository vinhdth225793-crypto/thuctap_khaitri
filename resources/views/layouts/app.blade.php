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
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .sidebar-logo i {
            font-size: 2rem;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        /* Badge Notification Styles */
        .nav-link .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
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
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
        
        // Loading button
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                    
                    // Thêm hiệu ứng loading cho button submit
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('btn-loading');
                        submitBtn.disabled = true;
                    }
                }, false);
            });
            
            // Auto remove loading after 3 seconds (for demo)
            setTimeout(() => {
                document.querySelectorAll('.btn-loading').forEach(btn => {
                    btn.classList.remove('btn-loading');
                    btn.disabled = false;
                });
            }, 3000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>