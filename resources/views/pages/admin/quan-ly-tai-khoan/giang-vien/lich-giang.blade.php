@extends('layouts.app')

@section('title', 'Lich day giang vien')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.giang-vien.index') }}">Giang vien</a></li>
            <li class="breadcrumb-item active">{{ $teacher->nguoiDung->ho_ten }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-muted text-uppercase fw-bold mb-1">Teacher Schedule</div>
            <h4 class="fw-bold mb-1">Lich day cua {{ $teacher->nguoiDung->ho_ten }}</h4>
            <div class="text-muted">{{ $teacher->chuyen_nganh ?: 'Chua cap nhat chuyen nganh' }} <span class="mx-2">|</span> {{ $teacher->nguoiDung->email }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.giang-vien.index') }}" class="btn btn-outline-secondary">Danh sach giang vien</a>
            <a href="{{ route('admin.giang-vien-don-xin-nghi.index', ['giang_vien_id' => $teacher->id]) }}" class="btn btn-outline-warning fw-bold">Don xin nghi</a>
        </div>
    </div>

    @include('components.alert')

    @include('components.teacher-schedule-board', [
        'scheduleView' => $scheduleView,
    ])

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted text-uppercase fw-bold">Module da nhan</div><div class="display-6 fw-bold text-info">{{ $stats['assigned_modules'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted text-uppercase fw-bold">Buoi sap toi</div><div class="display-6 fw-bold text-primary">{{ $stats['upcoming_schedules'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted text-uppercase fw-bold">Don cho duyet</div><div class="display-6 fw-bold text-warning">{{ $stats['leave_requests_pending'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted text-uppercase fw-bold">Don da duyet</div><div class="display-6 fw-bold text-success">{{ $stats['leave_requests_approved'] }}</div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">Don xin nghi gan day</h5>
                    <div class="text-muted small">Admin co the mo danh sach day du de duyet hoac xu ly tiep.</div>
                </div>
                <div class="card-body pt-0">
                    @forelse($recentLeaveRequests as $item)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-bold text-dark">{{ $item->ngay_xin_nghi?->format('d/m/Y') }}</div>
                                    <div class="small text-muted">{{ $item->schedule_range_label }}</div>
                                </div>
                                <span class="badge bg-{{ $item->trang_thai_color }}">{{ $item->trang_thai_label }}</span>
                            </div>
                            <div class="small text-muted mb-2">{{ $item->moduleHoc?->ten_module ?: '-' }}</div>
                            <div class="small">{{ $item->ly_do }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">Chua co don xin nghi nao.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">Buoi hoc sap toi</h5>
                    <div class="text-muted small">Dung de doi chieu nhanh truoc khi doi lich hoac thay giang vien.</div>
                </div>
                <div class="card-body pt-0">
                    @forelse($upcomingSchedules as $schedule)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-bold text-dark">{{ $schedule->moduleHoc?->ten_module }}</div>
                            <div class="small text-muted mb-2">{{ $schedule->moduleHoc?->khoaHoc?->ten_khoa_hoc }}</div>
                            <div class="small text-muted mb-3">{{ $schedule->ngay_hoc?->format('d/m/Y') }} | {{ $schedule->schedule_range_label }}</div>
                            <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $schedule->khoa_hoc_id) }}" class="btn btn-sm btn-outline-primary">Mo planner</a>
                        </div>
                    @empty
                        <div class="text-muted small">Chua co buoi hoc tuong lai nao duoc gan cho giang vien nay.</div>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">Module da nhan</h5>
                    <div class="text-muted small">Day la cac module admin co the tiep tuc sap lich.</div>
                </div>
                <div class="card-body pt-0">
                    @forelse($acceptedAssignments as $assignment)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-bold text-dark">{{ $assignment->moduleHoc->ten_module ?? 'N/A' }}</div>
                            <div class="small text-muted mb-2">{{ $assignment->moduleHoc->khoaHoc->ten_khoa_hoc ?? 'Khong xac dinh' }}</div>
                            <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $assignment->khoa_hoc_id) }}" class="btn btn-sm btn-outline-success">Mo planner</a>
                        </div>
                    @empty
                        <div class="text-muted small">Giang vien nay chua co module nao o trang thai da nhan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
