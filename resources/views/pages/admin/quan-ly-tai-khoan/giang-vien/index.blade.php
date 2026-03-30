@extends('layouts.app')

@section('title', 'Quan ly giang vien')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quan ly tai khoan</li>
                    <li class="breadcrumb-item active" aria-current="page">Giang vien</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                Quan ly giang vien, lich day va don xin nghi
            </h4>
            <div class="text-muted mt-2">Admin co the theo doi buoi sap toi, don xin nghi cho duyet va mo nhanh thoi khoa bieu cua tung giang vien.</div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2 flex-wrap">
            <a href="{{ route('admin.giang-vien-don-xin-nghi.index') }}" class="btn btn-outline-warning fw-bold shadow-sm">
                <i class="fas fa-calendar-minus me-1"></i> Don xin nghi
            </a>
            <a href="{{ route('admin.tai-khoan.create', ['vai_tro' => 'giang_vien']) }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Them giang vien moi
            </a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Tong profile giang vien</div>
                    <div class="display-6 fw-bold text-dark">{{ $teacherSummary['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Co lich day sap toi</div>
                    <div class="display-6 fw-bold text-success">{{ $teacherSummary['with_upcoming_schedule'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Don xin nghi cho duyet</div>
                    <div class="display-6 fw-bold text-primary">{{ $teacherSummary['pending_leave_requests'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-bold">Giang vien can xu ly</div>
                    <div class="display-6 fw-bold text-warning">{{ $teacherSummary['teachers_with_pending_leave'] }}</div>
                    <div class="small text-muted">Dang co it nhat mot don cho duyet</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.giang-vien.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input
                            type="text"
                            name="search"
                            class="form-control border-start-0 vip-form-control"
                            placeholder="Ten, email hoac so dien thoai..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="trang_thai" class="form-select vip-form-control">
                        <option value="">-- Trang thai --</option>
                        <option value="active" {{ request('trang_thai') == 'active' ? 'selected' : '' }}>Hoat dong</option>
                        <option value="inactive" {{ request('trang_thai') == 'inactive' ? 'selected' : '' }}>Dang khoa</option>
                        <option value="deleted" {{ request('trang_thai') == 'deleted' ? 'selected' : '' }}>Da xoa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort_field" class="form-select vip-form-control">
                        <option value="created_at" {{ request('sort_field') == 'created_at' ? 'selected' : '' }}>Ngay tao</option>
                        <option value="ho_ten" {{ request('sort_field') == 'ho_ten' ? 'selected' : '' }}>Ho ten</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Loc du lieu</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.giang-vien.index') }}" class="btn btn-light w-100 fw-bold border">Dat lai</a>
                </div>
            </form>
        </div>
    </div>

    <div class="vip-card shadow-sm border-0">
        <div class="vip-card-header bg-white border-bottom py-3">
            <h5 class="vip-card-title small fw-bold text-uppercase mb-0">
                <i class="fas fa-list me-2"></i> Danh sach doi ngu giang vien
            </h5>
        </div>
        <div class="vip-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light smaller text-muted text-uppercase">
                        <tr>
                            <th class="ps-4 text-center" width="70">Ma</th>
                            <th width="260">Thong tin co ban</th>
                            <th>Chuyen mon</th>
                            <th class="text-center">So gio day</th>
                            <th width="230">Lich day / Don nghi</th>
                            <th class="text-center">Trang thai</th>
                            <th class="pe-4 text-center" width="220">Hanh dong</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($giangVien as $user)
                            @php
                                $teacherProfile = $user->giangVien;
                                $acceptedAssignmentCount = $teacherProfile?->phan_cong_da_nhan_count ?? 0;
                                $upcomingScheduleCount = $teacherProfile?->buoi_day_tuong_lai_count ?? 0;
                                $pendingLeaveRequestCount = $teacherProfile?->don_xin_nghi_cho_duyet_count ?? 0;
                                $totalLeaveRequestCount = $teacherProfile?->tong_don_xin_nghi_count ?? 0;
                            @endphp
                            <tr>
                                <td class="text-center ps-4 text-muted small">#{{ $user->ma_nguoi_dung }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-wrapper me-3">
                                            @if($user->anh_dai_dien)
                                                <img src="{{ asset('images/' . $user->anh_dai_dien) }}" class="rounded-circle shadow-xs" width="45" height="45" style="object-fit: cover;">
                                            @else
                                                <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">
                                                    {{ substr($user->ho_ten, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-0">{{ $user->ho_ten }}</div>
                                            <div class="smaller text-muted"><i class="far fa-envelope me-1"></i>{{ $user->email }}</div>
                                            <div class="smaller text-muted"><i class="fas fa-phone-alt me-1"></i>{{ $user->so_dien_thoai ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($teacherProfile)
                                        <div class="small fw-bold text-secondary">{{ $teacherProfile->chuyen_nganh ?? 'Chua cap nhat' }}</div>
                                        <div class="smaller text-muted mt-1"><i class="fas fa-user-graduate me-1"></i>{{ $teacherProfile->hoc_vi ?? 'N/A' }}</div>
                                    @else
                                        <span class="badge bg-light text-muted border smaller">Chua co profile</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-primary">{{ $teacherProfile?->so_gio_day ?? 0 }}h</span>
                                </td>
                                <td>
                                    @if($teacherProfile)
                                        <div class="small fw-bold text-dark">{{ $upcomingScheduleCount }} buoi sap toi</div>
                                        <div class="smaller text-muted">Don cho duyet {{ $pendingLeaveRequestCount }} | Tong don {{ $totalLeaveRequestCount }}</div>
                                        <div class="smaller text-muted">Module da nhan {{ $acceptedAssignmentCount }}</div>
                                        @if($pendingLeaveRequestCount > 0)
                                            <span class="badge bg-warning text-dark border smaller mt-1">Can xu ly don nghi</span>
                                        @elseif($upcomingScheduleCount > 0)
                                            <span class="badge bg-success-soft text-success border border-success smaller mt-1">Dang co lich day</span>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-muted border smaller">Chua co du lieu</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($user->trashed())
                                        <span class="badge bg-danger-soft text-danger px-3 border border-danger smaller">Da xoa</span>
                                    @elseif($user->trang_thai)
                                        <span class="badge bg-success-soft text-success px-3 border border-success smaller">Hoat dong</span>
                                    @else
                                        <span class="badge bg-warning-soft text-warning px-3 border border-warning smaller">Dang khoa</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        @if($teacherProfile)
                                            <a href="{{ route('admin.giang-vien.lich-giang.show', $teacherProfile->id) }}" class="btn btn-sm btn-outline-primary action-btn" title="Xem lich day">
                                                <i class="fas fa-calendar-week"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.tai-khoan.show', $user->ma_nguoi_dung) }}" class="btn btn-sm btn-outline-info action-btn" title="Xem chi tiet">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.tai-khoan.edit', $user->ma_nguoi_dung) }}" class="btn btn-sm btn-outline-warning action-btn" title="Chinh sua">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary action-btn toggle-status"
                                            data-id="{{ $user->ma_nguoi_dung }}"
                                            data-name="{{ $user->ho_ten }}"
                                            data-status="{{ $user->trang_thai ? 1 : 0 }}"
                                            title="{{ $user->trang_thai ? 'Khoa tai khoan' : 'Mo khoa' }}"
                                        >
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                        @unless($user->trashed())
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger action-btn delete-user"
                                                data-id="{{ $user->ma_nguoi_dung }}"
                                                data-name="{{ $user->ho_ten }}"
                                                title="Xoa"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Khong tim thay giang vien nao phu hop.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <div class="text-muted smaller">
                    Hien thi {{ $giangVien->firstItem() }} - {{ $giangVien->lastItem() }} trong tong so {{ $giangVien->total() }} giang vien
                </div>
                <div>
                    {{ $giangVien->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .smaller { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .vip-form-control:focus { box-shadow: none; border-color: #0d6efd; }
    .avatar-wrapper img { transition: transform 0.2s; }
    .avatar-wrapper img:hover { transform: scale(1.1); }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const status = this.dataset.status === '1';
            const action = status ? 'KHOA' : 'MO KHOA';

            if (confirm(`Ban chac chan muon ${action} tai khoan cua giang vien ${name}?`)) {
                fetch(`/admin/tai-khoan/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert(data.message);
                });
            }
        });
    });

    document.querySelectorAll('.delete-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            if (confirm(`Ban chac chan muon XOA giang vien ${name}? Tai khoan se chuyen vao thung rac.`)) {
                fetch(`/admin/tai-khoan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert(data.message);
                });
            }
        });
    });
});
</script>
@endpush
@endsection
