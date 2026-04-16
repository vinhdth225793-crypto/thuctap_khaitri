@php
    $upcomingLessons = collect($dashboardData['cacBuoiSapToi'] ?? [])->take(3);
    $activeEnrollments = collect($dashboardData['ghiDanhKhoaHoc'] ?? [])->where('trang_thai', 'dang_hoc');
    $avgProgress = round((float) ($activeEnrollments->avg('tien_do_hoc_tap') ?: 0));
    $exams = collect($dashboardData['baiKiemTraCanChuY'] ?? [])->take(2);
@endphp

<section class="role-panel" aria-label="Khu học tập nhanh cho học viên">
    <div class="role-panel-heading">
        <span class="eyebrow">Khu học tập</span>
        <h2>Chào {{ auth()->user()->ho_ten }}, tiếp tục lộ trình hôm nay</h2>
        <p>Nắm lịch học, tiến độ và bài kiểm tra cần chú ý trong một nơi.</p>
    </div>

    <div class="student-dashboard-grid">
        <div class="learning-strip">
            <div class="metric-block">
                <strong>{{ $avgProgress }}%</strong>
                <span>Tiến độ trung bình</span>
            </div>
            <div class="metric-block">
                <strong>{{ $activeEnrollments->count() }}</strong>
                <span>Khóa đang học</span>
            </div>
            <div class="metric-block">
                <strong>{{ $exams->count() }}</strong>
                <span>Bài cần chú ý</span>
            </div>
        </div>

        <div class="timeline-panel">
            <div class="panel-title">
                <i class="fas fa-calendar-day"></i>
                <span>Buổi học sắp tới</span>
            </div>
            @forelse($upcomingLessons as $lich)
                <a class="timeline-row" href="{{ route('hoc-vien.buoi-hoc.show', $lich->id) }}">
                    <time>
                        <strong>{{ optional($lich->ngay_hoc)->format('d') }}</strong>
                        <span>{{ optional($lich->ngay_hoc)->format('m/Y') }}</span>
                    </time>
                    <span>
                        <strong>{{ $lich->khoaHoc->ten_khoa_hoc ?? 'Khóa học' }}</strong>
                        <small>{{ $lich->moduleHoc->ten_module ?? 'Buổi học' }} - {{ $lich->gio_bat_dau_formatted ?? '' }}</small>
                    </span>
                </a>
            @empty
                <div class="empty-mini">Bạn chưa có lịch học sắp tới.</div>
            @endforelse
        </div>

        <div class="timeline-panel">
            <div class="panel-title">
                <i class="fas fa-clipboard-check"></i>
                <span>Bài kiểm tra</span>
            </div>
            @forelse($exams as $exam)
                <a class="timeline-row compact" href="{{ route('hoc-vien.bai-kiem-tra.show', $exam->id) }}">
                    <span>
                        <strong>{{ $exam->tieu_de }}</strong>
                        <small>{{ $exam->access_status_label ?? 'Sẵn sàng' }}</small>
                    </span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            @empty
                <div class="empty-mini">Chưa có bài kiểm tra cần xử lý.</div>
            @endforelse
        </div>
    </div>

    <div class="role-actions">
        <a href="{{ route('hoc-vien.dashboard') }}" class="btn-main">Mở bảng học tập</a>
        <a href="{{ route('hoc-vien.khoa-hoc-cua-toi') }}" class="btn-soft">Khóa học của tôi</a>
        <a href="{{ route('hoc-vien.khoa-hoc-tham-gia') }}" class="btn-soft">Tìm khóa mới</a>
    </div>
</section>
