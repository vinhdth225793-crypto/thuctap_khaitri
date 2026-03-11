@extends('layouts.app')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="container">
    <h1>Danh sách người dùng</h1>

    <a href="{{ route('admin.tai-khoan.create') }}" class="btn btn-success mb-3">Thêm người dùng</a>

    <form method="GET" class="form-inline mb-3">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Tìm kiếm">
        <select name="vai_tro" class="form-control mr-2">
            <option value="all">Tất cả vai trò</option>
            <option value="admin" {{ request('vai_tro')=='admin'?'selected':'' }}>Admin</option>
            <option value="giang_vien" {{ request('vai_tro')=='giang_vien'?'selected':'' }}>Giảng viên</option>
            <option value="hoc_vien" {{ request('vai_tro')=='hoc_vien'?'selected':'' }}>Học viên</option>
        </select>
        <select name="trang_thai" class="form-control mr-2">
            <option value="">Trạng thái</option>
            <option value="active" {{ request('trang_thai')=='active'?'selected':'' }}>Hoạt động</option>
            <option value="inactive" {{ request('trang_thai')=='inactive'?'selected':'' }}>Khóa</option>
            <option value="deleted" {{ request('trang_thai')=='deleted'?'selected':'' }}>Đã xóa</option>
        </select>
        <button class="btn btn-primary">Lọc</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Ảnh</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nguoiDung as $user)
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
                <td>{{ $user->ho_ten }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$user->vai_tro)) }}</td>
                <td>{{ $user->trang_thai ? 'Hoạt động' : 'Khóa' }} {{ $user->trashed() ? '(đã xóa)' : '' }}</td>
                <td>
                    <a href="{{ route('admin.tai-khoan.show',$user->ma_nguoi_dung) }}" class="btn btn-sm btn-info">Xem</a>
                    <a href="{{ route('admin.tai-khoan.edit',$user->ma_nguoi_dung) }}" class="btn btn-sm btn-warning">Sửa</a>
                    <button type="button" class="btn btn-sm btn-secondary toggle-status" data-id="{{ $user->ma_nguoi_dung }}" data-status="{{ $user->trang_thai }}">{{ $user->trang_thai?'Khóa':'Mở' }}</button>
                    @if($user->trashed())
                        <button type="button" class="btn btn-sm btn-success restore-user" data-id="{{ $user->ma_nguoi_dung }}">Khôi phục</button>
                        <button type="button" class="btn btn-sm btn-danger delete-user" data-id="{{ $user->ma_nguoi_dung }}" data-permanent="1">Xóa vĩnh viễn</button>
                    @else
                        <button type="button" class="btn btn-sm btn-danger delete-user" data-id="{{ $user->ma_nguoi_dung }}" data-permanent="0">Xóa</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $nguoiDung->links() }}
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '{{ route("admin.tai-khoan.index") }}';
    
    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const currentStatus = this.dataset.status === '1';
            const action = currentStatus ? 'khóa' : 'mở';
            
            if (confirm(`Bạn chắc chắn muốn ${action} tài khoản này?`)) {
                fetch(`${baseUrl}/${id}/toggle`, {
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
            const isPermanent = this.dataset.permanent === '1';
            const message = isPermanent ? 'Xóa vĩnh viễn tài khoản này?' : 'Bạn chắc chắn muốn xóa tài khoản này?';
            
            if (confirm(message)) {
                const url = isPermanent ? `${baseUrl}/${id}/force` : `${baseUrl}/${id}`;
                const method = 'DELETE';
                
                fetch(url, {
                    method: method,
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
                fetch(`${baseUrl}/${id}/restore`, {
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