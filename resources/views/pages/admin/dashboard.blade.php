@extends('layouts.app')

@section('title', 'Dashboard - Quản trị viên')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Dashboard Quản trị viên</h3>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-home"></i> Về trang chủ
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Thống kê người dùng -->
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $tongNguoiDung }}</h3>
                                    <p>Tổng người dùng</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $tongHocVien }}</h3>
                                    <p>Học viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $tongGiangVien }}</h3>
                                    <p>Giảng viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $tongAdmin }}</h3>
                                    <p>Quản trị viên</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Người dùng mới -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title mb-0">10 Tài khoản mới nhất</h3>
                                    <a href="{{ route('admin.tai-khoan.index') }}" class="btn btn-sm btn-primary">Xem tất cả</a>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Ảnh</th>
                                                <th>Họ tên</th>
                                                <th>Email</th>
                                                <th>Vai trò</th>
                                                <th>Trạng thái</th>
                                                <th>Ngày tạo</th>
                                                <th>Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($nguoiDungMoi as $user)
                                            <tr>
                                                <td>
                                                    @if($user->anh_dai_dien)
                                                        <img src="{{ asset('images/'.$user->anh_dai_dien) }}" alt="Ảnh đại diện" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover; border: 1px solid #ddd;">
                                                    @else
                                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center text-muted" style="width: 32px; height: 32px; border: 1px solid #ddd; font-size: 12px;">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td><strong>{{ $user->ho_ten }}</strong></td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $user->vai_tro === 'admin' ? 'danger' : ($user->vai_tro === 'giang_vien' ? 'warning' : 'success') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $user->vai_tro)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($user->trashed())
                                                        <span class="badge badge-dark">Đã xóa</span>
                                                    @elseif($user->trang_thai)
                                                        <span class="badge badge-success">Hoạt động</span>
                                                    @else
                                                        <span class="badge badge-warning">Khóa</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.tai-khoan.edit', $user->ma_nguoi_dung) }}" class="btn btn-xs btn-warning" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">Chưa có tài khoản nào</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection