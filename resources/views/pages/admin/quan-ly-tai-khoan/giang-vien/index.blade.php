@extends('layouts.app')

@section('title', 'Quản lý giảng viên')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Danh sách giảng viên</h1>
        <a href="{{ route('admin.tai-khoan.create') }}" class="btn btn-success">+ Thêm giảng viên</a>
    </div>

    <!-- Bộ lọc và sắp xếp -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm kiếm theo tên, email, số điện thoại">
                </div>
                
                <div class="form-group mr-3 mb-2">
                    <select name="trang_thai" class="form-control">
                        <option value="">-- Trạng thái --</option>
                        <option value="active" {{ request('trang_thai')=='active'?'selected':'' }}>Hoạt động</option>
                        <option value="inactive" {{ request('trang_thai')=='inactive'?'selected':'' }}>Khóa</option>
                        <option value="deleted" {{ request('trang_thai')=='deleted'?'selected':'' }}>Đã xóa</option>
                    </select>
                </div>

                <div class="form-group mr-3 mb-2">
                    <select name="sort_field" class="form-control">
                        <option value="created_at" {{ request('sort_field')=='created_at'?'selected':'' }}>Ngày tạo</option>
                        <option value="ho_ten" {{ request('sort_field')=='ho_ten'?'selected':'' }}>Họ tên (A-Z)</option>
                        <option value="email" {{ request('sort_field')=='email'?'selected':'' }}>Email (A-Z)</option>
                        <option value="trang_thai" {{ request('sort_field')=='trang_thai'?'selected':'' }}>Trạng thái</option>
                    </select>
                </div>

                <div class="form-group mr-3 mb-2">
                    <select name="sort_direction" class="form-control">
                        <option value="desc" {{ request('sort_direction')=='desc'?'selected':'' }}>↓ Giảm dần</option>
                        <option value="asc" {{ request('sort_direction')=='asc'?'selected':'' }}>↑ Tăng dần</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Lọc & Sắp xếp
                </button>
                <a href="{{ route('admin.giang-vien.index') }}" class="btn btn-secondary mb-2 ml-2">
                    <i class="fas fa-redo"></i> Đặt lại
                </a>
            </form>
        </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Ảnh</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($giangVien as $user)
                <tr>
                    <td>{{ $user->ma_nguoi_dung }}</td>
                    <td>
                        @if($user->anh_dai_dien)
                            <img src="{{ asset('images/'.$user->anh_dai_dien) }}" alt="Ảnh đại diện" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;">
                        @else
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px; border: 1px solid #ddd;">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </td>
                    <td><strong>{{ $user->ho_ten }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->so_dien_thoai ?? '-' }}</td>
                    <td>
                        @if($user->trashed())
                            <span class="badge badge-danger">Đã xóa</span>
                        @elseif($user->trang_thai)
                            <span class="badge badge-success">Hoạt động</span>
                        @else
                            <span class="badge badge-warning">Khóa</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.tai-khoan.edit', $user->ma_nguoi_dung) }}" class="btn btn-sm btn-warning" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-info toggle-status" data-id="{{ $user->ma_nguoi_dung }}" data-status="{{ $user->trang_thai }}" title="{{ $user->trang_thai?'Khóa':'Mở' }}">
                            <i class="fas fa-lock{{ $user->trang_thai ? '' : '-open' }}"></i>
                        </button>
                        @if($user->trashed())
                            <button type="button" class="btn btn-sm btn-success restore-user" data-id="{{ $user->ma_nguoi_dung }}" title="Khôi phục">
                                <i class="fas fa-undo"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-danger delete-user" data-id="{{ $user->ma_nguoi_dung }}" data-permanent="0" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <p class="text-muted mb-0">Không có giảng viên nào</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Tổng cộng: <strong>{{ $giangVien->total() }}</strong> giảng viên
        </div>
        {{ $giangVien->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '{{ route("admin.giang-vien.index") }}';
    
    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const currentStatus = this.dataset.status === '1';
            const action = currentStatus ? 'khóa' : 'mở';
            
            if (confirm(`Bạn chắc chắn muốn ${action} tài khoản này?`)) {
                fetch(`{{ route('admin.tai-khoan.index') }}/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra: ' + error.message);
                });
            }
        });
    });

    // Delete user
    document.querySelectorAll('.delete-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            
            if (confirm('Bạn chắc chắn muốn xóa tài khoản này?')) {
                fetch(`{{ route('admin.tai-khoan.index') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra: ' + error.message);
                });
            }
        });
    });

    // Restore user
    document.querySelectorAll('.restore-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            
            if (confirm('Khôi phục tài khoản này?')) {
                fetch(`{{ route('admin.tai-khoan.index') }}/${id}/restore`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra: ' + error.message);
                });
            }
        });
    });
});
</script>
@endsection
