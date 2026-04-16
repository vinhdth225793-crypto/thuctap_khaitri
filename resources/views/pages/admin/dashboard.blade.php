@extends('layouts.app')

@section('title', 'Dashboard - Quản trị viên')

@section('content')
@php
    $adminName = auth()->user()->ho_ten ?? 'Admin';
    $priorityTasks = collect($taskStats ?? [])->sortByDesc('count')->values();
    $hasWork = ($urgentTotal ?? 0) > 0;

    $overviewCards = [
        [
            'label' => 'Người dùng',
            'value' => $tongNguoiDung,
            'meta' => "{$tongHocVien} học viên, {$tongGiangVien} giảng viên",
            'icon' => 'fas fa-users',
            'tone' => 'primary',
            'route' => route('admin.tai-khoan.index'),
        ],
        [
            'label' => 'Khóa học',
            'value' => $stats['tong_khoa_hoc'],
            'meta' => "{$stats['khoa_hoc_hoat_dong']} đang hoạt động",
            'icon' => 'fas fa-graduation-cap',
            'tone' => 'success',
            'route' => route('admin.khoa-hoc.index'),
        ],
        [
            'label' => 'Module',
            'value' => $stats['tong_module'],
            'meta' => "{$stats['module_chua_co_gv']} module chưa có GV",
            'icon' => 'fas fa-layer-group',
            'tone' => 'warning',
            'route' => route('admin.module-hoc.index'),
        ],
        [
            'label' => 'Lịch hôm nay',
            'value' => $stats['lich_hoc_hom_nay'],
            'meta' => "{$stats['lich_hoc_sap_toi']} buổi sắp tới",
            'icon' => 'fas fa-calendar-day',
            'tone' => 'info',
            'route' => route('admin.khoa-hoc.index'),
        ],
    ];

    $workChartData = $priorityTasks
        ->map(fn ($task) => [
            'label' => $task['title'],
            'value' => (int) $task['count'],
        ])
        ->values();

    $roleChartData = collect([
        ['label' => 'Học viên', 'value' => (int) ($roleDistribution['hoc_vien'] ?? 0), 'color' => '#22c55e'],
        ['label' => 'Giảng viên', 'value' => (int) ($roleDistribution['giang_vien'] ?? 0), 'color' => '#f59e0b'],
        ['label' => 'Admin', 'value' => (int) ($roleDistribution['admin'] ?? 0), 'color' => '#ef4444'],
    ]);

    $registrationChartData = collect($registrationData ?? [])
        ->map(fn ($item, $date) => [
            'label' => \Carbon\Carbon::parse($date)->format('d/m'),
            'hoc_vien' => (int) ($item['hoc_vien'] ?? 0),
            'giang_vien' => (int) ($item['giang_vien'] ?? 0),
            'admin' => (int) ($item['admin'] ?? 0),
        ])
        ->values();

    $monthlyChartData = collect($monthlyActivity ?? [])
        ->map(fn ($item, $month) => [
            'label' => $month,
            'nguoi_dung' => (int) ($item['nguoi_dung'] ?? 0),
            'khoa_hoc' => (int) ($item['khoa_hoc'] ?? 0),
            'module' => (int) ($item['module'] ?? 0),
        ])
        ->values();
@endphp

<div class="admin-dashboard">
    <section class="admin-briefing-card">
        <div>
            <div class="dashboard-kicker">
                <i class="fas fa-home"></i>
                Admin / Dashboard
            </div>
            <h1>Chào {{ $adminName }}, hôm nay cần xử lý gì?</h1>
            <p>
                Tổng quan hệ thống, các việc đang chờ duyệt và lịch học trong ngày được gom lại để bạn xử lý nhanh ngay khi vào trang.
            </p>

            <div class="briefing-actions">
                <a href="{{ route('admin.phe-duyet-tai-khoan.index') }}" class="btn btn-light fw-bold">
                    <i class="fas fa-user-check me-2"></i>Duyệt tài khoản
                </a>
                <a href="{{ route('admin.giang-vien-don-xin-nghi.index') }}" class="btn btn-outline-light fw-bold">
                    <i class="fas fa-calendar-xmark me-2"></i>Đơn xin nghỉ
                </a>
                <a href="{{ route('admin.khoa-hoc.index') }}" class="btn btn-outline-light fw-bold">
                    <i class="fas fa-calendar-check me-2"></i>Sắp lịch
                </a>
            </div>
        </div>

        <div class="briefing-status {{ $hasWork ? 'needs-work' : 'clear' }}">
            <span>{{ now()->format('d/m/Y') }}</span>
            <strong>{{ number_format($urgentTotal ?? 0) }}</strong>
            <p>{{ $hasWork ? 'việc cần xem hôm nay' : 'hàng đợi đang sạch' }}</p>
        </div>
    </section>

    <section class="work-queue-grid">
        @foreach($priorityTasks as $task)
            <a href="{{ $task['route'] }}" class="work-card tone-{{ $task['tone'] }}">
                <div class="work-icon"><i class="{{ $task['icon'] }}"></i></div>
                <div>
                    <span>{{ $task['title'] }}</span>
                    <strong>{{ number_format($task['count']) }}</strong>
                    <p>{{ $task['description'] }}</p>
                </div>
            </a>
        @endforeach
    </section>

    <section class="overview-grid">
        @foreach($overviewCards as $card)
            <a href="{{ $card['route'] }}" class="overview-card tone-{{ $card['tone'] }}">
                <div>
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ number_format($card['value']) }}</strong>
                    <p>{{ $card['meta'] }}</p>
                </div>
                <i class="{{ $card['icon'] }}"></i>
            </a>
        @endforeach
    </section>

    <section class="dashboard-chart-grid">
        <div class="dashboard-panel chart-panel chart-wide">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Biểu đồ</span>
                    <h2>Đăng ký mới trong 7 ngày</h2>
                </div>
                <span class="chart-note">Học viên, giảng viên, admin</span>
            </div>
            <div class="chart-box chart-box-tall">
                <canvas id="registrationChart" aria-label="Biểu đồ đăng ký mới trong 7 ngày"></canvas>
            </div>
        </div>

        <div class="dashboard-panel chart-panel">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Nhân sự</span>
                    <h2>Phân bổ vai trò</h2>
                </div>
            </div>
            <div class="chart-box">
                <canvas id="roleChart" aria-label="Biểu đồ phân bổ vai trò"></canvas>
            </div>
            <div class="chart-legend">
                @foreach($roleChartData as $role)
                    <span><i style="background: {{ $role['color'] }}"></i>{{ $role['label'] }}: {{ number_format($role['value']) }}</span>
                @endforeach
            </div>
        </div>

        <div class="dashboard-panel chart-panel">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Hàng đợi</span>
                    <h2>Việc chờ xử lý</h2>
                </div>
            </div>
            <div class="chart-box">
                <canvas id="workloadChart" aria-label="Biểu đồ việc chờ xử lý"></canvas>
            </div>
        </div>

        <div class="dashboard-panel chart-panel chart-wide">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Đào tạo</span>
                    <h2>Hoạt động 6 tháng gần đây</h2>
                </div>
                <span class="chart-note">Người dùng, khóa học, module</span>
            </div>
            <div class="chart-box chart-box-tall">
                <canvas id="monthlyActivityChart" aria-label="Biểu đồ hoạt động đào tạo theo tháng"></canvas>
            </div>
        </div>
    </section>

    <section class="dashboard-main-grid">
        <div class="dashboard-panel">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Lịch vận hành</span>
                    <h2>Lịch học hôm nay</h2>
                </div>
                <a href="{{ route('admin.khoa-hoc.index') }}">Quản lý khóa học</a>
            </div>

            <div class="today-schedule-list">
                @forelse($todaysSchedules as $schedule)
                    <article class="schedule-row">
                        <time>
                            <strong>{{ $schedule->gio_bat_dau ? \Carbon\Carbon::parse($schedule->gio_bat_dau)->format('H:i') : '--:--' }}</strong>
                            <span>{{ $schedule->gio_ket_thuc ? \Carbon\Carbon::parse($schedule->gio_ket_thuc)->format('H:i') : '--:--' }}</span>
                        </time>
                        <div>
                            <h3>{{ $schedule->moduleHoc->ten_module ?? 'Buổi học' }}</h3>
                            <p>{{ $schedule->khoaHoc->ten_khoa_hoc ?? 'Khóa học' }}</p>
                            <small>
                                <i class="fas fa-user-tie me-1"></i>
                                {{ $schedule->giangVien?->nguoiDung?->ho_ten ?? 'Chưa gán giảng viên' }}
                                <span class="mx-1">•</span>
                                {{ $schedule->phong_hoc ?: ($schedule->hinh_thuc === 'online' ? 'Online' : 'Chưa có phòng') }}
                            </small>
                        </div>
                        <span class="status-pill">{{ $schedule->trang_thai_label ?? ucfirst(str_replace('_', ' ', $schedule->trang_thai)) }}</span>
                    </article>
                @empty
                    <div class="empty-dashboard">
                        <i class="fas fa-calendar-check"></i>
                        <strong>Hôm nay chưa có lịch học</strong>
                        <span>Bạn có thể tranh thủ xử lý các hàng đợi duyệt hoặc kiểm tra lịch sắp tới.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Ưu tiên</span>
                    <h2>Việc cần giải quyết</h2>
                </div>
                <a href="{{ route('admin.phan-cong.index') }}">Phân công</a>
            </div>

            <div class="action-list">
                <a href="{{ route('admin.module-hoc.index') }}" class="action-item">
                    <i class="fas fa-triangle-exclamation text-warning"></i>
                    <span>
                        <strong>{{ number_format($stats['module_chua_co_gv']) }} module chưa có giảng viên</strong>
                        <small>Cần phân công để lớp có thể vận hành.</small>
                    </span>
                </a>
                <a href="{{ route('admin.phan-cong.index') }}" class="action-item">
                    <i class="fas fa-user-clock text-danger"></i>
                    <span>
                        <strong>{{ number_format($stats['phan_cong_cho_xn']) }} phân công chờ xác nhận</strong>
                        <small>Theo dõi phản hồi từ giảng viên.</small>
                    </span>
                </a>
                <a href="{{ route('admin.diem-danh.index') }}" class="action-item">
                    <i class="fas fa-clipboard-user text-primary"></i>
                    <span>
                        <strong>Điểm danh giảng viên</strong>
                        <small>Kiểm tra buổi còn thiếu điểm danh hoặc báo cáo.</small>
                    </span>
                </a>
                <a href="{{ route('admin.settings.contact') }}" class="action-item">
                    <i class="fas fa-bullhorn text-info"></i>
                    <span>
                        <strong>Thông tin hiển thị trang chủ</strong>
                        <small>Cập nhật hotline, email, logo và thông báo.</small>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <section class="dashboard-main-grid">
        <div class="dashboard-panel">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Phê duyệt</span>
                    <h2>Tài khoản mới</h2>
                </div>
                <a href="{{ route('admin.phe-duyet-tai-khoan.index') }}">Xem tất cả</a>
            </div>

            <div class="compact-list">
                @forelse($pendingAccounts as $account)
                    <article class="compact-row">
                        <div class="avatar-dot">{{ mb_substr($account->ho_ten, 0, 1) }}</div>
                        <div>
                            <strong>{{ $account->ho_ten }}</strong>
                            <span>{{ $account->email }}</span>
                        </div>
                        <small>{{ $account->created_at?->diffForHumans() }}</small>
                    </article>
                @empty
                    <div class="empty-dashboard compact">Không có tài khoản nào đang chờ duyệt.</div>
                @endforelse
            </div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Giảng viên</span>
                    <h2>Đơn xin nghỉ</h2>
                </div>
                <a href="{{ route('admin.giang-vien-don-xin-nghi.index') }}">Xem tất cả</a>
            </div>

            <div class="compact-list">
                @forelse($pendingLeaveRequests as $leave)
                    <a href="{{ route('admin.giang-vien-don-xin-nghi.show', $leave->id) }}" class="compact-row link-row">
                        <div class="avatar-dot warn"><i class="fas fa-calendar-xmark"></i></div>
                        <div>
                            <strong>{{ $leave->giangVien?->nguoiDung?->ho_ten ?? 'Giảng viên' }}</strong>
                            <span>{{ optional($leave->ngay_xin_nghi)->format('d/m/Y') }} - {{ $leave->moduleHoc->ten_module ?? 'Module' }}</span>
                        </div>
                        <small>{{ $leave->created_at?->diffForHumans() }}</small>
                    </a>
                @empty
                    <div class="empty-dashboard compact">Không có đơn xin nghỉ chờ duyệt.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="dashboard-main-grid">
        <div class="dashboard-panel">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Học viên</span>
                    <h2>Yêu cầu đang chờ</h2>
                </div>
                <a href="{{ route('admin.yeu-cau-hoc-vien.index') }}">Xem tất cả</a>
            </div>

            <div class="compact-list">
                @forelse($pendingStudentRequests as $requestItem)
                    <article class="compact-row">
                        <div class="avatar-dot info"><i class="fas fa-user-plus"></i></div>
                        <div>
                            <strong>{{ $requestItem->nguoi_gui_ten }}</strong>
                            <span>{{ $requestItem->khoaHoc->ten_khoa_hoc ?? 'Khóa học' }} - {{ $requestItem->loai_label }}</span>
                        </div>
                        <small>{{ $requestItem->created_at?->diffForHumans() }}</small>
                    </article>
                @empty
                    <div class="empty-dashboard compact">Không có yêu cầu học viên đang chờ.</div>
                @endforelse
            </div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-head">
                <div>
                    <span class="dashboard-kicker">Đào tạo</span>
                    <h2>Module cần giảng viên</h2>
                </div>
                <a href="{{ route('admin.module-hoc.index') }}">Xem module</a>
            </div>

            <div class="compact-list">
                @forelse($moduleChuaCoGv as $module)
                    <a href="{{ route('admin.khoa-hoc.show', $module->khoa_hoc_id) }}" class="compact-row link-row">
                        <div class="avatar-dot danger"><i class="fas fa-layer-group"></i></div>
                        <div>
                            <strong>{{ $module->ten_module }}</strong>
                            <span>{{ $module->khoaHoc->ten_khoa_hoc ?? 'Khóa học' }}</span>
                        </div>
                        <small>Phân công</small>
                    </a>
                @empty
                    <div class="empty-dashboard compact">Tất cả module đang có giảng viên.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="panel-head">
            <div>
                <span class="dashboard-kicker">Tài khoản</span>
                <h2>Thành viên mới gia nhập</h2>
            </div>
            <a href="{{ route('admin.tai-khoan.index') }}">Quản lý tài khoản</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nguoiDungMoi as $user)
                        <tr>
                            <td>
                                <div class="user-mini">
                                    @if($user->anh_dai_dien)
                                        <img src="{{ asset('images/' . $user->anh_dai_dien) }}" alt="{{ $user->ho_ten }}">
                                    @else
                                        <span>{{ mb_substr($user->ho_ten, 0, 1) }}</span>
                                    @endif
                                    <strong>{{ $user->ho_ten }}</strong>
                                </div>
                            </td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                <span class="badge rounded-pill text-bg-light">{{ str_replace('_', ' ', $user->vai_tro) }}</span>
                            </td>
                            <td>
                                @if($user->trashed())
                                    <span class="badge rounded-pill text-bg-dark">Đã xóa</span>
                                @elseif($user->trang_thai)
                                    <span class="badge rounded-pill text-bg-success">Đang hoạt động</span>
                                @else
                                    <span class="badge rounded-pill text-bg-warning">Đang khóa</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.tai-khoan.edit', $user->ma_nguoi_dung) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                    Sửa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có tài khoản nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<style>
    .admin-dashboard {
        display: grid;
        gap: 22px;
    }

    .dashboard-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #4f63c7;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .admin-briefing-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px;
        gap: 24px;
        align-items: center;
        padding: 28px;
        border-radius: 8px;
        background: linear-gradient(135deg, #243cde, #4361ee);
        color: #fff;
        box-shadow: 0 18px 45px rgba(67, 97, 238, 0.24);
    }

    .admin-briefing-card .dashboard-kicker {
        color: #dfe6ff;
    }

    .admin-briefing-card h1 {
        max-width: 760px;
        margin: 10px 0;
        font-size: clamp(28px, 4vw, 42px);
        font-weight: 900;
        line-height: 1.12;
    }

    .admin-briefing-card p {
        max-width: 780px;
        margin: 0;
        color: rgba(255, 255, 255, 0.82);
        line-height: 1.7;
    }

    .briefing-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .briefing-status {
        min-height: 190px;
        display: grid;
        place-items: center;
        text-align: center;
        padding: 22px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .briefing-status span,
    .briefing-status p {
        color: rgba(255, 255, 255, 0.78);
        font-weight: 700;
    }

    .briefing-status strong {
        display: block;
        font-size: 56px;
        line-height: 1;
        font-weight: 900;
    }

    .work-queue-grid,
    .overview-grid,
    .dashboard-main-grid,
    .dashboard-chart-grid {
        display: grid;
        gap: 16px;
    }

    .work-queue-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .overview-grid,
    .dashboard-main-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-chart-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .work-card,
    .overview-card,
    .dashboard-panel {
        border: 1px solid #e3e8ff;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(67, 97, 238, 0.08);
    }

    .work-card,
    .overview-card {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px;
        text-decoration: none;
        color: #17203d;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .work-card:hover,
    .overview-card:hover,
    .compact-row.link-row:hover,
    .action-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 36px rgba(67, 97, 238, 0.14);
    }

    .work-card span,
    .overview-card span {
        display: block;
        color: #667091;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .work-card strong,
    .overview-card strong {
        display: block;
        margin: 5px 0;
        font-size: 32px;
        line-height: 1;
        font-weight: 900;
    }

    .work-card p,
    .overview-card p {
        margin: 0;
        color: #6a7289;
        line-height: 1.45;
    }

    .work-icon,
    .overview-card > i {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 8px;
        background: #eef2ff;
        color: #4361ee;
        font-size: 18px;
    }

    .tone-warning .work-icon,
    .tone-warning > i { background: #fff7df; color: #b7791f; }
    .tone-danger .work-icon,
    .tone-danger > i { background: #ffe8ec; color: #d92d50; }
    .tone-success .work-icon,
    .tone-success > i { background: #e7f8ee; color: #16864b; }
    .tone-info .work-icon,
    .tone-info > i { background: #e2f3ff; color: #0b7fab; }
    .tone-secondary .work-icon,
    .tone-secondary > i { background: #f1f3f8; color: #5f6780; }

    .dashboard-panel {
        padding: 20px;
        min-width: 0;
    }

    .chart-wide {
        grid-column: span 2;
    }

    .chart-panel {
        overflow: hidden;
    }

    .chart-box {
        position: relative;
        width: 100%;
        height: 260px;
        border: 1px solid #edf0ff;
        border-radius: 8px;
        background: linear-gradient(180deg, #fbfcff, #ffffff);
        overflow: hidden;
    }

    .chart-box-tall {
        height: 300px;
    }

    .chart-box canvas {
        display: block;
        width: 100%;
        height: 100%;
    }

    .chart-note {
        color: #69728a;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .chart-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .chart-legend span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 9px;
        border-radius: 8px;
        background: #f6f8ff;
        color: #52607d;
        font-size: 12px;
        font-weight: 800;
    }

    .chart-legend i {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        display: inline-block;
    }

    .panel-head {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .panel-head h2 {
        margin: 5px 0 0;
        font-size: 22px;
        font-weight: 900;
        color: #17203d;
    }

    .panel-head a {
        color: #4361ee;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .today-schedule-list,
    .action-list,
    .compact-list {
        display: grid;
        gap: 10px;
    }

    .schedule-row,
    .action-item,
    .compact-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px;
        border: 1px solid #edf0ff;
        border-radius: 8px;
        background: #fbfcff;
        color: #17203d;
        text-decoration: none;
    }

    .schedule-row time {
        width: 70px;
        flex: 0 0 70px;
        padding: 9px;
        border-radius: 8px;
        background: #eef2ff;
        text-align: center;
        color: #2f46c9;
    }

    .schedule-row time strong,
    .schedule-row time span {
        display: block;
    }

    .schedule-row h3,
    .schedule-row p {
        margin: 0;
    }

    .schedule-row h3 {
        font-size: 15px;
        font-weight: 900;
    }

    .schedule-row p,
    .schedule-row small,
    .compact-row span,
    .compact-row small,
    .action-item small {
        color: #69728a;
    }

    .status-pill {
        margin-left: auto;
        padding: 6px 9px;
        border-radius: 999px;
        background: #eef2ff;
        color: #2f46c9;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .action-item i {
        width: 34px;
        text-align: center;
        font-size: 18px;
    }

    .action-item strong,
    .action-item small,
    .compact-row strong,
    .compact-row span {
        display: block;
    }

    .avatar-dot {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 8px;
        background: #eef2ff;
        color: #2f46c9;
        font-weight: 900;
        text-transform: uppercase;
    }

    .avatar-dot.warn { background: #fff7df; color: #b7791f; }
    .avatar-dot.info { background: #e2f3ff; color: #0b7fab; }
    .avatar-dot.danger { background: #ffe8ec; color: #d92d50; }

    .compact-row small {
        margin-left: auto;
        text-align: right;
        white-space: nowrap;
    }

    .empty-dashboard {
        display: grid;
        gap: 7px;
        place-items: center;
        padding: 28px 16px;
        border: 1px dashed #ccd5ff;
        border-radius: 8px;
        background: #fbfcff;
        color: #68728d;
        text-align: center;
    }

    .empty-dashboard i {
        color: #4361ee;
        font-size: 24px;
    }

    .empty-dashboard.compact {
        display: block;
        padding: 18px;
    }

    .admin-table thead th {
        color: #667091;
        font-size: 12px;
        text-transform: uppercase;
        border-bottom: 1px solid #edf0ff;
    }

    .user-mini {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-mini img,
    .user-mini span {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        object-fit: cover;
    }

    .user-mini span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #2f46c9;
        font-weight: 900;
    }

    @media (max-width: 1200px) {
        .work-queue-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .admin-briefing-card,
        .overview-grid,
        .dashboard-main-grid,
        .dashboard-chart-grid {
            grid-template-columns: 1fr;
        }

        .chart-wide {
            grid-column: auto;
        }

        .briefing-status {
            min-height: auto;
        }
    }

    @media (max-width: 640px) {
        .admin-briefing-card,
        .dashboard-panel {
            padding: 18px;
        }

        .work-queue-grid {
            grid-template-columns: 1fr;
        }

        .panel-head,
        .schedule-row,
        .compact-row {
            align-items: flex-start;
        }

        .schedule-row,
        .compact-row {
            flex-wrap: wrap;
        }

        .compact-row small,
        .status-pill {
            margin-left: 0;
        }
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chartData = {
            workload: @json($workChartData),
            roles: @json($roleChartData),
            registration: @json($registrationChartData),
            monthly: @json($monthlyChartData),
        };

        const palette = {
            blue: '#4361ee',
            darkBlue: '#2f46c9',
            green: '#22c55e',
            yellow: '#f59e0b',
            red: '#ef4444',
            cyan: '#0ea5e9',
            muted: '#69728a',
            line: '#e3e8ff',
            text: '#17203d',
        };

        const setupCanvas = (canvas) => {
            const parent = canvas.parentElement;
            const rect = parent.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            const width = Math.max(260, rect.width);
            const height = Math.max(220, rect.height);
            canvas.width = Math.floor(width * dpr);
            canvas.height = Math.floor(height * dpr);
            canvas.style.width = `${width}px`;
            canvas.style.height = `${height}px`;
            const ctx = canvas.getContext('2d');
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, width, height);
            return { ctx, width, height };
        };

        const roundRect = (ctx, x, y, width, height, radius) => {
            const safeRadius = Math.min(radius, Math.abs(width) / 2, Math.abs(height) / 2);
            ctx.beginPath();
            ctx.moveTo(x + safeRadius, y);
            ctx.arcTo(x + width, y, x + width, y + height, safeRadius);
            ctx.arcTo(x + width, y + height, x, y + height, safeRadius);
            ctx.arcTo(x, y + height, x, y, safeRadius);
            ctx.arcTo(x, y, x + width, y, safeRadius);
            ctx.closePath();
        };

        const drawEmpty = (ctx, width, height, label = 'Chưa có dữ liệu') => {
            ctx.fillStyle = '#f6f8ff';
            roundRect(ctx, 16, 16, width - 32, height - 32, 8);
            ctx.fill();
            ctx.fillStyle = palette.muted;
            ctx.font = '700 14px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(label, width / 2, height / 2);
        };

        const truncate = (text, max = 16) => {
            const value = String(text || '');
            return value.length > max ? `${value.slice(0, max - 1)}...` : value;
        };

        const drawGrid = (ctx, box, maxValue) => {
            ctx.strokeStyle = palette.line;
            ctx.lineWidth = 1;
            ctx.fillStyle = palette.muted;
            ctx.font = '700 11px Inter, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';

            for (let i = 0; i <= 4; i += 1) {
                const y = box.top + (box.height / 4) * i;
                const value = Math.round(maxValue - (maxValue / 4) * i);
                ctx.beginPath();
                ctx.moveTo(box.left, y);
                ctx.lineTo(box.left + box.width, y);
                ctx.stroke();
                ctx.fillText(value, box.left - 8, y);
            }
        };

        const drawBarChart = (canvas, items, colors = [palette.blue]) => {
            const { ctx, width, height } = setupCanvas(canvas);
            const values = items.map((item) => Number(item.value || 0));
            const maxValue = Math.max(...values, 0);

            if (!items.length || maxValue === 0) {
                drawEmpty(ctx, width, height);
                return;
            }

            const box = { left: 42, top: 18, width: width - 58, height: height - 74 };
            drawGrid(ctx, box, maxValue);

            const slot = box.width / items.length;
            const barWidth = Math.max(18, Math.min(42, slot * 0.54));

            items.forEach((item, index) => {
                const value = Number(item.value || 0);
                const barHeight = (value / maxValue) * box.height;
                const x = box.left + index * slot + (slot - barWidth) / 2;
                const y = box.top + box.height - barHeight;
                const gradient = ctx.createLinearGradient(0, y, 0, box.top + box.height);
                gradient.addColorStop(0, colors[index % colors.length]);
                gradient.addColorStop(1, '#dfe6ff');

                ctx.fillStyle = gradient;
                roundRect(ctx, x, y, barWidth, barHeight, 7);
                ctx.fill();

                ctx.fillStyle = palette.text;
                ctx.font = '800 12px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                ctx.fillText(value, x + barWidth / 2, y - 5);

                ctx.fillStyle = palette.muted;
                ctx.font = '700 10px Inter, sans-serif';
                ctx.textBaseline = 'top';
                ctx.fillText(truncate(item.label, 13), x + barWidth / 2, box.top + box.height + 10);
            });
        };

        const drawDoughnutChart = (canvas, items) => {
            const { ctx, width, height } = setupCanvas(canvas);
            const total = items.reduce((sum, item) => sum + Number(item.value || 0), 0);

            if (!items.length || total === 0) {
                drawEmpty(ctx, width, height);
                return;
            }

            const centerX = width / 2;
            const centerY = height / 2;
            const radius = Math.min(width, height) * 0.34;
            const innerRadius = radius * 0.58;
            let start = -Math.PI / 2;

            items.forEach((item) => {
                const slice = (Number(item.value || 0) / total) * Math.PI * 2;
                ctx.beginPath();
                ctx.arc(centerX, centerY, radius, start, start + slice);
                ctx.arc(centerX, centerY, innerRadius, start + slice, start, true);
                ctx.closePath();
                ctx.fillStyle = item.color || palette.blue;
                ctx.fill();
                start += slice;
            });

            ctx.fillStyle = '#fff';
            ctx.beginPath();
            ctx.arc(centerX, centerY, innerRadius - 2, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = palette.text;
            ctx.font = '900 30px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(total, centerX, centerY - 7);

            ctx.fillStyle = palette.muted;
            ctx.font = '800 12px Inter, sans-serif';
            ctx.fillText('người dùng', centerX, centerY + 20);
        };

        const drawLineChart = (canvas, items) => {
            const { ctx, width, height } = setupCanvas(canvas);
            const series = [
                { key: 'hoc_vien', label: 'Học viên', color: palette.green },
                { key: 'giang_vien', label: 'Giảng viên', color: palette.yellow },
                { key: 'admin', label: 'Admin', color: palette.red },
            ];
            const maxValue = Math.max(
                ...items.flatMap((item) => series.map((serie) => Number(item[serie.key] || 0))),
                1
            );

            if (!items.length) {
                drawEmpty(ctx, width, height);
                return;
            }

            const box = { left: 42, top: 32, width: width - 58, height: height - 78 };
            drawGrid(ctx, box, maxValue);

            series.forEach((serie, serieIndex) => {
                const points = items.map((item, index) => {
                    const x = box.left + (items.length === 1 ? box.width / 2 : (box.width / (items.length - 1)) * index);
                    const y = box.top + box.height - (Number(item[serie.key] || 0) / maxValue) * box.height;
                    return { x, y };
                });

                ctx.strokeStyle = serie.color;
                ctx.lineWidth = 3;
                ctx.beginPath();
                points.forEach((point, index) => {
                    if (index === 0) {
                        ctx.moveTo(point.x, point.y);
                    } else {
                        ctx.lineTo(point.x, point.y);
                    }
                });
                ctx.stroke();

                points.forEach((point) => {
                    ctx.fillStyle = '#fff';
                    ctx.beginPath();
                    ctx.arc(point.x, point.y, 5, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = serie.color;
                    ctx.lineWidth = 2;
                    ctx.stroke();
                });

                ctx.fillStyle = serie.color;
                ctx.font = '800 11px Inter, sans-serif';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'top';
                ctx.fillText(serie.label, box.left + serieIndex * 86, 10);
            });

            ctx.fillStyle = palette.muted;
            ctx.font = '700 11px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            items.forEach((item, index) => {
                const x = box.left + (items.length === 1 ? box.width / 2 : (box.width / (items.length - 1)) * index);
                ctx.fillText(item.label, x, box.top + box.height + 12);
            });
        };

        const drawGroupedBarChart = (canvas, items) => {
            const { ctx, width, height } = setupCanvas(canvas);
            const series = [
                { key: 'nguoi_dung', label: 'Người dùng', color: palette.blue },
                { key: 'khoa_hoc', label: 'Khóa học', color: palette.green },
                { key: 'module', label: 'Module', color: palette.yellow },
            ];
            const maxValue = Math.max(
                ...items.flatMap((item) => series.map((serie) => Number(item[serie.key] || 0))),
                0
            );

            if (!items.length || maxValue === 0) {
                drawEmpty(ctx, width, height);
                return;
            }

            const box = { left: 42, top: 32, width: width - 58, height: height - 78 };
            drawGrid(ctx, box, maxValue);

            const groupSlot = box.width / items.length;
            const barWidth = Math.max(8, Math.min(18, groupSlot / 6));

            series.forEach((serie, serieIndex) => {
                ctx.fillStyle = serie.color;
                ctx.font = '800 11px Inter, sans-serif';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'top';
                ctx.fillText(serie.label, box.left + serieIndex * 96, 10);
            });

            items.forEach((item, index) => {
                const groupCenter = box.left + index * groupSlot + groupSlot / 2;
                series.forEach((serie, serieIndex) => {
                    const value = Number(item[serie.key] || 0);
                    const barHeight = (value / maxValue) * box.height;
                    const x = groupCenter + (serieIndex - 1) * (barWidth + 4) - barWidth / 2;
                    const y = box.top + box.height - barHeight;
                    ctx.fillStyle = serie.color;
                    roundRect(ctx, x, y, barWidth, barHeight, 5);
                    ctx.fill();
                });

                ctx.fillStyle = palette.muted;
                ctx.font = '700 11px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'top';
                ctx.fillText(item.label, groupCenter, box.top + box.height + 12);
            });
        };

        const drawAllCharts = () => {
            const workload = document.getElementById('workloadChart');
            const roles = document.getElementById('roleChart');
            const registration = document.getElementById('registrationChart');
            const monthly = document.getElementById('monthlyActivityChart');

            if (workload) {
                drawBarChart(workload, chartData.workload, [palette.blue, palette.cyan, palette.yellow, palette.green, palette.darkBlue, palette.red]);
            }

            if (roles) {
                drawDoughnutChart(roles, chartData.roles);
            }

            if (registration) {
                drawLineChart(registration, chartData.registration);
            }

            if (monthly) {
                drawGroupedBarChart(monthly, chartData.monthly);
            }
        };

        let resizeTimer;
        window.addEventListener('resize', () => {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(drawAllCharts, 150);
        });

        drawAllCharts();
    });
</script>
@endpush
@endsection
