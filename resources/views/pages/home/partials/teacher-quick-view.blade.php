@php
    $todayLessons = collect($dashboardData['lichDayHomNay'] ?? [])->take(3);
    $teacher = $dashboardData['giangVien'] ?? null;
@endphp

<section class="role-panel" aria-label="Bảng điều khiển nhanh cho giảng viên">
    <div class="role-panel-heading">
        <span class="eyebrow">Giảng viên</span>
        <h2>Xin chào {{ auth()->user()->ho_ten }}, lịch dạy hôm nay đã sẵn sàng</h2>
        <p>Theo dõi buổi dạy, phân công mới và tổng giờ giảng trong một nơi.</p>
    </div>

    <div class="teacher-dashboard-grid">
        <div class="timeline-panel wide">
            <div class="panel-title">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Lịch giảng hôm nay</span>
            </div>
            @forelse($todayLessons as $lich)
                <a class="timeline-row" href="{{ route('giang-vien.khoa-hoc.show', ['id' => $lich->khoa_hoc_id, 'focus_lich_hoc_id' => $lich->id]) }}">
                    <time>
                        <strong>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }}</strong>
                        <span>{{ $lich->hinh_thuc === 'online' ? 'Online' : 'Trực tiếp' }}</span>
                    </time>
                    <span>
                        <strong>{{ $lich->khoaHoc->ten_khoa_hoc ?? 'Khóa học' }}</strong>
                        <small>{{ $lich->moduleHoc->ten_module ?? 'Module' }}</small>
                    </span>
                </a>
            @empty
                <div class="empty-mini">Hôm nay bạn chưa có lịch giảng.</div>
            @endforelse
        </div>

        <div class="learning-strip vertical">
            <div class="metric-block">
                <strong>{{ number_format($dashboardData['phanCongChoXN'] ?? 0) }}</strong>
                <span>Phân công chờ xác nhận</span>
            </div>
            <div class="metric-block">
                <strong>{{ number_format((int) ($teacher->so_gio_day ?? 0)) }}</strong>
                <span>Giờ giảng tích lũy</span>
            </div>
        </div>
    </div>

    <div class="role-actions">
        <a href="{{ route('giang-vien.dashboard') }}" class="btn-main">Mở bảng giảng dạy</a>
        <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn-soft">Khóa đang phụ trách</a>
        <a href="{{ route('giang-vien.lich-giang.index') }}" class="btn-soft">Lịch giảng</a>
    </div>
</section>
