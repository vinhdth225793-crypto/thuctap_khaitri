@extends('layouts.app', ['title' => 'Dashboard Học Viên'])

@section('content')
<div class="container-fluid">
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card vip-card border-0 shadow-sm overflow-hidden welcome-card">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2 fw-bold">Chào mừng trở lại, {{ auth()->user()->ho_ten }}! 👋</h2>
                            <p class="text-muted mb-0">Hôm nay bạn có {{ rand(2, 5) }} bài tập cần hoàn thành</p>
                            <div class="mt-3">
                                <span class="badge bg-primary me-2">Học viên</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="dashboard-illustration">
                                <i class="fas fa-user-graduate fa-4x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <x-stat-card 
                title="Khóa học đang học"
                value="{{ rand(3, 8) }}"
                icon="fas fa-book"
                color="primary"
                trend="up"
                trendValue="+2"
                description="Tổng số khóa học"
            />
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <x-stat-card 
                title="Bài tập chưa làm"
                value="{{ rand(1, 5) }}"
                icon="fas fa-tasks"
                color="warning"
                trend="down"
                trendValue="-3"
                description="Cần hoàn thành"
            />
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <x-stat-card 
                title="Điểm trung bình"
                value="{{ rand(75, 95) }}"
                icon="fas fa-star"
                color="success"
                trend="up"
                trendValue="+5%"
                description="Tổng điểm"
            />
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <x-stat-card 
                title="Ngày học liên tiếp"
                value="{{ rand(5, 30) }}"
                icon="fas fa-calendar-check"
                color="info"
                trend="up"
                trendValue="+{{ rand(1, 5) }}"
                description="Streak"
            />
        </div>
    </div>

    <!-- Progress & Courses -->
    <div class="row">
        <!-- Progress Overview -->
        <div class="col-lg-8 mb-4">
            <div class="card vip-card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Tiến độ học tập</h5>
                    <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn vip-btn vip-btn-primary btn-sm">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Khóa học</th>
                                    <th>Giảng viên</th>
                                    <th>Tiến độ</th>
                                    <th>Điểm</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                    ['name' => 'Lập trình Web PHP', 'teacher' => 'Nguyễn Văn A', 'progress' => 85, 'score' => 92, 'status' => 'Đang học'],
                                    ['name' => 'Thiết kế UI/UX', 'teacher' => 'Trần Thị B', 'progress' => 60, 'score' => 88, 'status' => 'Đang học'],
                                    ['name' => 'Cơ sở dữ liệu', 'teacher' => 'Phạm Văn C', 'progress' => 100, 'score' => 95, 'status' => 'Hoàn thành'],
                                    ['name' => 'Toán cao cấp', 'teacher' => 'Lê Thị D', 'progress' => 45, 'score' => 78, 'status' => 'Đang học'],
                                    ['name' => 'Tiếng Anh chuyên ngành', 'teacher' => 'Hoàng Văn E', 'progress' => 75, 'score' => 85, 'status' => 'Đang học'],
                                ] as $course)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="course-icon me-3">
                                                <i class="fas fa-book text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $course['name'] }}</h6>
                                                <small class="text-muted">Bắt đầu: 15/01/2024</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $course['teacher'] }}</td>
                                    <td>
                                        <div class="progress-wrapper">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small>{{ $course['progress'] }}%</small>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-primary" 
                                                     style="width: {{ $course['progress'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $course['score'] }}/100</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $course['status'] == 'Hoàn thành' ? 'bg-success' : 'bg-info' }}">
                                            {{ $course['status'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks & Quick Actions -->
        <div class="col-lg-4 mb-4">
            <!-- Upcoming Tasks -->
            <div class="card vip-card mb-4">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Bài tập sắp đến hạn</h5>
                </div>
                <div class="card-body">
                    <div class="task-list">
                        @foreach([
                            ['title' => 'Bài tập PHP Laravel', 'course' => 'Lập trình Web', 'due' => 'Hôm nay', 'priority' => 'high'],
                            ['title' => 'Thiết kế Landing Page', 'course' => 'UI/UX Design', 'due' => '2 ngày', 'priority' => 'medium'],
                            ['title' => 'Bài kiểm tra SQL', 'course' => 'Cơ sở dữ liệu', 'due' => '3 ngày', 'priority' => 'low'],
                            ['title' => 'Bài luận tiếng Anh', 'course' => 'Tiếng Anh', 'due' => '5 ngày', 'priority' => 'low'],
                        ] as $task)
                        <div class="task-item d-flex align-items-center mb-3">
                            <div class="task-icon me-3">
                                <i class="fas fa-circle text-{{ $task['priority'] == 'high' ? 'danger' : ($task['priority'] == 'medium' ? 'warning' : 'success') }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $task['title'] }}</h6>
                                <small class="text-muted">{{ $task['course'] }} • Hạn: {{ $task['due'] }}</small>
                            </div>
                            <button class="btn btn-sm vip-btn vip-btn-outline-primary">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="btn vip-btn vip-btn-primary btn-sm">
                            Xem tất cả bài tập
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Thao tác nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn vip-btn vip-btn-outline-primary d-block p-3">
                                <i class="fas fa-book fa-2x mb-2"></i>
                                <span>Khóa học của tôi</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="btn vip-btn vip-btn-outline-success d-block p-3">
                                <i class="fas fa-tasks fa-2x mb-2"></i>
                                <span>Bài kiểm tra</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('hoc-vien.ket-qua') }}" class="btn vip-btn vip-btn-outline-info d-block p-3">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <span>Kết quả học tập</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('profile') }}" class="btn vip-btn vip-btn-outline-warning d-block p-3">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <span>Hồ sơ cá nhân</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Hoạt động gần đây</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach([
                            ['icon' => 'fa-check-circle', 'color' => 'success', 'title' => 'Hoàn thành bài kiểm tra', 'desc' => 'Bài kiểm tra PHP Laravel', 'time' => '2 giờ trước'],
                            ['icon' => 'fa-comment', 'color' => 'info', 'title' => 'Bình luận mới', 'desc' => 'Trong bài "Database Design"', 'time' => '4 giờ trước'],
                            ['icon' => 'fa-video', 'color' => 'primary', 'title' => 'Xem video bài giảng', 'desc' => 'Chương 5: Advanced Queries', 'time' => 'Hôm qua'],
                            ['icon' => 'fa-file-upload', 'color' => 'warning', 'title' => 'Nộp bài tập', 'desc' => 'Bài tập thiết kế UI', 'time' => '2 ngày trước'],
                            ['icon' => 'fa-trophy', 'color' => 'danger', 'title' => 'Nhận huy hiệu', 'desc' => 'Huy hiệu "Xuất sắc"', 'time' => '3 ngày trước'],
                        ] as $activity)
                        <div class="timeline-item d-flex mb-3">
                            <div class="timeline-icon me-3">
                                <div class="icon-wrapper bg-{{ $activity['color'] }}">
                                    <i class="fas {{ $activity['icon'] }} text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $activity['title'] }}</h6>
                                <p class="mb-1 text-muted">{{ $activity['desc'] }}</p>
                                <small class="text-muted">{{ $activity['time'] }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Dashboard Custom Styles */
    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .welcome-card .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
    }
    
    .dashboard-illustration {
        animation: float 3s ease-in-out infinite;
    }
    
    .course-icon {
        width: 40px;
        height: 40px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .task-item {
        padding: 10px;
        border-radius: 10px;
        transition: all 0.3s;
    }
    
    .task-item:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }
    
    .task-icon {
        width: 30px;
        text-align: center;
    }
    
    .timeline-icon .icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary { background-color: #667eea !important; }
    .bg-success { background-color: #43e97b !important; }
    .bg-info { background-color: #4facfe !important; }
    .bg-warning { background-color: #fa709a !important; }
    .bg-danger { background-color: #f72585 !important; }
    
    .progress-wrapper {
        min-width: 150px;
    }
    
    .btn-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* Table styling */
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transform: translateY(-2px);
        transition: all 0.3s;
    }
    
    .table th {
        font-weight: 600;
        color: #495057;
        border-top: none;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Animate progress bars on scroll
        const progressBars = document.querySelectorAll('.progress-bar');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 300);
                }
            });
        });
        
        progressBars.forEach(bar => observer.observe(bar));
        
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('vi-VN', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
            const dateString = now.toLocaleDateString('vi-VN', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const timeElement = document.getElementById('current-time');
            const dateElement = document.getElementById('current-date');
            
            if (timeElement) timeElement.textContent = timeString;
            if (dateElement) dateElement.textContent = dateString;
        }
        
        updateTime();
        setInterval(updateTime, 60000);
    });
</script>
@endpush
@endsection