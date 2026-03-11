@extends('layouts.app', ['title' => 'Dashboard Giảng Viên'])

@section('content')
<div class="container-fluid">
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card vip-card border-0 shadow-sm overflow-hidden welcome-card">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2 fw-bold">Chào mừng, {{ auth()->user()->ho_ten }}! 👨‍🏫</h2>
                            <p class="text-muted mb-0">Hôm nay bạn có {{ rand(1, 3) }} lớp học và {{ rand(2, 6) }} bài tập cần chấm</p>
                            <div class="mt-3">
                                <span class="badge bg-warning me-2">Giảng viên</span>
                                <span class="badge bg-success">Đang giảng dạy</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="dashboard-illustration">
                                <i class="fas fa-chalkboard-teacher fa-4x text-warning"></i>
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
                title="Khóa học đang dạy"
                value="{{ rand(2, 5) }}"
                icon="fas fa-chalkboard"
                color="warning"
                trend="up"
                trendValue="+1"
                description="Tổng số khóa học"
            />
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <x-stat-card 
                title="Học viên"
                value="{{ rand(50, 200) }}"
                icon="fas fa-user-graduate"
                color="primary"
                trend="up"
                trendValue="+{{ rand(5, 15) }}"
                description="Tổng số học viên"
            />
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <x-stat-card 
                title="Bài tập cần chấm"
                value="{{ rand(10, 50) }}"
                icon="fas fa-tasks"
                color="danger"
                trend="down"
                trendValue="-{{ rand(2, 8) }}"
                description="Chưa chấm"
            />
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <x-stat-card 
                title="Đánh giá"
                value="{{ rand(4.5, 5.0) }}"
                icon="fas fa-star"
                color="success"
                trend="up"
                trendValue="+0.{{ rand(1, 3) }}"
                description="/5.0"
            />
        </div>
    </div>

    <!-- Teaching Overview -->
    <div class="row">
        <!-- Courses Overview -->
        <div class="col-lg-8 mb-4">
            <div class="card vip-card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Khóa học đang dạy</h5>
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn vip-btn vip-btn-warning btn-sm">
                        Quản lý khóa học
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Khóa học</th>
                                    <th>Học viên</th>
                                    <th>Thời lượng</th>
                                    <th>Tiến độ</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                    ['name' => 'Lập trình Web PHP', 'students' => 45, 'duration' => '12 tuần', 'progress' => 75, 'status' => 'Đang dạy'],
                                    ['name' => 'Thiết kế UI/UX', 'students' => 38, 'duration' => '10 tuần', 'progress' => 60, 'status' => 'Đang dạy'],
                                    ['name' => 'Cơ sở dữ liệu', 'students' => 52, 'duration' => '8 tuần', 'progress' => 100, 'status' => 'Đã kết thúc'],
                                    ['name' => 'Toán cao cấp', 'students' => 60, 'duration' => '14 tuần', 'progress' => 40, 'status' => 'Đang dạy'],
                                ] as $course)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="course-icon me-3">
                                                <i class="fas fa-book text-warning"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $course['name'] }}</h6>
                                                <small class="text-muted">Mã: COURSE{{ rand(1000, 9999) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $course['students'] }}</td>
                                    <td>{{ $course['duration'] }}</td>
                                    <td>
                                        <div class="progress-wrapper">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small>{{ $course['progress'] }}%</small>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-warning" 
                                                     style="width: {{ $course['progress'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $course['status'] == 'Đã kết thúc' ? 'bg-success' : 'bg-info' }}">
                                            {{ $course['status'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="#" class="btn vip-btn vip-btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('giang-vien.tao-bai-giang') }}" class="btn vip-btn vip-btn-outline-warning">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks & Quick Actions -->
        <div class="col-lg-4 mb-4">
            <!-- Tasks to Grade -->
            <div class="card vip-card mb-4">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Bài tập cần chấm</h5>
                </div>
                <div class="card-body">
                    <div class="task-list">
                        @foreach([
                            ['title' => 'Bài tập PHP Laravel', 'course' => 'Lập trình Web', 'students' => 12, 'due' => 'Hôm nay'],
                            ['title' => 'Thiết kế UI', 'course' => 'UI/UX Design', 'students' => 8, 'due' => 'Hôm qua'],
                            ['title' => 'Bài kiểm tra SQL', 'course' => 'Cơ sở dữ liệu', 'students' => 25, 'due' => '2 ngày'],
                            ['title' => 'Bài luận', 'course' => 'Tiếng Anh', 'students' => 5, 'due' => '3 ngày'],
                        ] as $task)
                        <div class="task-item d-flex align-items-center mb-3">
                            <div class="task-icon me-3">
                                <div class="assignment-icon bg-danger">
                                    <i class="fas fa-file-alt text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $task['title'] }}</h6>
                                <small class="text-muted">{{ $task['course'] }} • {{ $task['students'] }} bài cần chấm</small>
                            </div>
                            <a href="{{ route('giang-vien.cham-diem') }}" class="btn btn-sm vip-btn vip-btn-outline-warning">
                                <i class="fas fa-check"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('giang-vien.cham-diem') }}" class="btn vip-btn vip-btn-warning btn-sm">
                            Chấm bài ngay
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
                            <a href="{{ route('giang-vien.tao-bai-giang') }}" class="btn vip-btn vip-btn-outline-warning d-block p-3">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                <span>Tạo bài giảng</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('giang-vien.tao-bai-kiem-tra') }}" class="btn vip-btn vip-btn-outline-danger d-block p-3">
                                <i class="fas fa-tasks fa-2x mb-2"></i>
                                <span>Tạo bài kiểm tra</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn vip-btn vip-btn-outline-primary d-block p-3">
                                <i class="fas fa-chalkboard fa-2x mb-2"></i>
                                <span>Khóa học</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('profile') }}" class="btn vip-btn vip-btn-outline-success d-block p-3">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <span>Hồ sơ</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .welcome-card {
        background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        color: #333;
    }
    
    .assignment-icon {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .teacher-stats .stat-icon {
        font-size: 32px;
    }
</style>
@endpush
@endsection