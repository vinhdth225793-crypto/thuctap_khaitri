@extends('layouts.app')

@section('title', 'Quản lý giảng viên')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quản lý tài khoản</li>
                    <li class="breadcrumb-item active" aria-current="page">Giảng viên</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                Quản lý giảng viên
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.tai-khoan.create', ['vai_tro' => 'giang_vien']) }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Thêm giảng viên mới
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @include('components.alert')

    <!-- Search & Filter -->
    <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.giang-vien.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control" 
                               placeholder="Tên, email hoặc số điện thoại..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="trang_thai" class="form-select vip-form-control">
                        <option value="">-- Trạng thái --</option>
                        <option value="active" {{ request('trang_thai') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('trang_thai') == 'inactive' ? 'selected' : '' }}>Đang khóa</option>
                        <option value="deleted" {{ request('trang_thai') == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort_field" class="form-select vip-form-control">
                        <option value="created_at" {{ request('sort_field') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                        <option value="ho_ten" {{ request('sort_field') == 'ho_ten' ? 'selected' : '' }}>Họ tên</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Lọc dữ liệu</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.giang-vien.index') }}" class="btn btn-light w-100 fw-bold border">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="vip-card shadow-sm border-0">
        <div class="vip-card-header bg-white border-bottom py-3">
            <h5 class="vip-card-title small fw-bold text-uppercase mb-0">
                <i class="fas fa-list me-2"></i> Danh sách đội ngũ giảng viên
            </h5>
        </div>
        <div class="vip-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light smaller text-muted text-uppercase">
                        <tr>
                            <th class="ps-4 text-center" width="60">Mã</th>
                            <th width="250">Thông tin cơ bản</th>
                            <th>Chuyên môn</th>
                            <th class="text-center">Số giờ dạy</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="pe-4 text-center" width="180">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($giangVien as $user)
                            <tr>
                                <td class="text-center ps-4 text-muted small">#{{ $user->ma_nguoi_dung }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-wrapper me-3">
                                            @if($user->anh_dai_dien)
                                                <img src="{{ asset('images/'.$user->anh_dai_dien) }}" class="rounded-circle shadow-xs" width="45" height="45" style="object-fit: cover;">
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
                                    @if($user->giangVien)
                                        <div class="small fw-bold text-secondary">{{ $user->giangVien->chuyen_nganh ?? 'Chưa cập nhật' }}</div>
                                        <div class="smaller text-muted mt-1"><i class="fas fa-user-graduate me-1"></i>{{ $user->giangVien->hoc_vi ?? 'N/A' }}</div>
                                    @else
                                        <span class="badge bg-light text-muted border smaller">Chưa có profile</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-primary">{{ $user->giangVien->so_gio_day ?? 0 }}h</span>
                                </td>
                                <td class="text-center">
                                    @if($user->trashed())
                                        <span class="badge bg-danger-soft text-danger px-3 border border-danger smaller">Đã xóa</span>
                                    @elseif($user->trang_thai)
                                        <span class="badge bg-success-soft text-success px-3 border border-success smaller">Hoạt động</span>
                                    @else
                                        <span class="badge bg-warning-soft text-warning px-3 border border-warning smaller">Đang khóa</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.tai-khoan.show', $user->ma_nguoi_dung) }}" 
                                           class="btn btn-sm btn-outline-info action-btn" 
                                           title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.tai-khoan.edit', $user->ma_nguoi_dung) }}" 
                                           class="btn btn-sm btn-outline-warning action-btn" 
                                           title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary action-btn toggle-status" 
                                                data-id="{{ $user->ma_nguoi_dung }}" 
                                                data-name="{{ $user->ho_ten }}"
                                                data-status="{{ $user->trang_thai ? 1 : 0 }}"
                                                title="{{ $user->trang_thai ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                        @if($user->trashed())
                                            <button type="button" class="btn btn-sm btn-outline-success action-btn restore-user" 
                                                    data-id="{{ $user->ma_nguoi_dung }}" 
                                                    data-name="{{ $user->ho_ten }}"
                                                    title="Khôi phục">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-danger action-btn delete-user" 
                                                    data-id="{{ $user->ma_nguoi_dung }}" 
                                                    data-name="{{ $user->ho_ten }}"
                                                    title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Không tìm thấy giảng viên nào phù hợp.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <div class="text-muted smaller">
                    Hiển thị {{ $giangVien->firstItem() }} - {{ $giangVien->lastItem() }} trong tổng số {{ $giangVien->total() }} giảng viên
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

    // Toggle Status
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const status = this.dataset.status === '1';
            const action = status ? 'KHÓA' : 'MỞ KHÓA';
            
            if (confirm(`Bạn chắc chắn muốn ${action} tài khoản của giảng viên ${name}?`)) {
                fetch(`/admin/tai-khoan/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) location.reload();
                    else alert(data.message);
                });
            }
        });
    });

    // Delete User
    document.querySelectorAll('.delete-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            if (confirm(`Bạn chắc chắn muốn XÓA giảng viên ${name}? Tài khoản sẽ chuyển vào thùng rác.`)) {
                fetch(`/admin/tai-khoan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) location.reload();
                    else alert(data.message);
                });
            }
        });
    });

    // Restore User
    document.querySelectorAll('.restore-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            if (confirm(`Khôi phục tài khoản cho giảng viên ${name}?`)) {
                fetch(`/admin/tai-khoan/${id}/restore`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) location.reload();
                    else alert(data.message);
                });
            }
        });
    });
});
</script>
@endpush
@endsection
