@extends('layouts.app')

@section('title', 'Thông tin người dùng')

@section('content')
<div class="container">
    <h1>Chi tiết người dùng</h1>

    <p><strong>Họ tên:</strong> {{ $nguoiDung->ho_ten }}</p>
    <p><strong>Email:</strong> {{ $nguoiDung->email }}</p>
    <p><strong>Vai trò:</strong> 
        @php
            $vaiTroMap = [
                'admin' => 'Quản trị viên',
                'giang_vien' => 'Giảng viên',
                'hoc_vien' => 'Học viên'
            ];
        @endphp
        <span class="badge bg-light text-dark border">{{ $vaiTroMap[$nguoiDung->vai_tro] ?? $nguoiDung->vai_tro }}</span>
    </p>
    <p><strong>Số điện thoại:</strong> {{ $nguoiDung->so_dien_thoai }}</p>
    <p><strong>Ngày sinh:</strong> {{ optional($nguoiDung->ngay_sinh)->format('d/m/Y') }}</p>
    <p><strong>Địa chỉ:</strong> {{ $nguoiDung->dia_chi }}</p>
    <p><strong>Trạng thái:</strong> {{ $nguoiDung->trang_thai ? 'Hoạt động' : 'Khóa' }}</p>
    <p><strong>Ngày tạo:</strong> {{ $nguoiDung->created_at }}</p>
    <p><strong>Đã xóa lúc:</strong> {{ $nguoiDung->deleted_at }}</p>

    <a href="{{ route('admin.tai-khoan.edit', $nguoiDung->ma_nguoi_dung) }}" class="btn btn-warning">Chỉnh sửa</a>
    <a href="{{ route('admin.tai-khoan.index') }}" class="btn btn-secondary">Quay lại</a>
</div>
@endsection