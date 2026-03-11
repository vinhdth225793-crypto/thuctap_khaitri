@extends('layouts.app')

@section('title', 'Hồ sơ giảng viên')

@section('content')
<div class="container">
    <h1>Chỉnh sửa thông tin cá nhân</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('giang-vien.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="ho_ten">Họ tên</label>
            <input type="text" name="ho_ten" id="ho_ten" class="form-control" value="{{ old('ho_ten', $user->ho_ten) }}">
            @error('ho_ten')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}">
            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="form-group">
            <label for="so_dien_thoai">Số điện thoại</label>
            <input type="text" name="so_dien_thoai" id="so_dien_thoai" class="form-control" value="{{ old('so_dien_thoai', $user->so_dien_thoai) }}">
            @error('so_dien_thoai')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="form-group">
            <label for="ngay_sinh">Ngày sinh</label>
            <input type="date" name="ngay_sinh" id="ngay_sinh" class="form-control" value="{{ old('ngay_sinh', optional($user->ngay_sinh)->format('Y-m-d')) }}">
            @error('ngay_sinh')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="form-group">
            <label for="dia_chi">Địa chỉ</label>
            <textarea name="dia_chi" id="dia_chi" class="form-control">{{ old('dia_chi', $user->dia_chi) }}</textarea>
            @error('dia_chi')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="form-group">
            <label for="anh_dai_dien">Ảnh đại diện</label><br>
            @if($user->anh_dai_dien)
                <img src="{{ asset('storage/'.$user->anh_dai_dien) }}" alt="avatar" width="120">
                <br>
                <input type="checkbox" name="xoa_anh_dai_dien" value="1"> Xóa ảnh
            @endif
            <input type="file" name="anh_dai_dien" id="anh_dai_dien" class="form-control-file">
            @error('anh_dai_dien')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="form-group">
            <label for="mat_khau">Mật khẩu mới (để trống nếu không đổi)</label>
            <input type="password" name="mat_khau" id="mat_khau" class="form-control">
            @error('mat_khau')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
        <div class="form-group">
            <label for="mat_khau_confirmation">Xác nhận mật khẩu</label>
            <input type="password" name="mat_khau_confirmation" id="mat_khau_confirmation" class="form-control">
        </div>

        <hr>
        <h3>Thông tin giảng viên</h3>

        <div class="form-group">
            <label for="chuyen_nganh">Chuyên ngành</label>
            <input type="text" name="chuyen_nganh" id="chuyen_nganh" class="form-control" value="{{ old('chuyen_nganh', optional($user->giangVien)->chuyen_nganh) }}">
            @error('chuyen_nganh')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
        <div class="form-group">
            <label for="hoc_vi">Học vị</label>
            <input type="text" name="hoc_vi" id="hoc_vi" class="form-control" value="{{ old('hoc_vi', optional($user->giangVien)->hoc_vi) }}">
            @error('hoc_vi')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
        <div class="form-group">
            <label for="so_gio_day">Số giờ dạy</label>
            <input type="text" name="so_gio_day" id="so_gio_day" class="form-control" value="{{ old('so_gio_day', optional($user->giangVien)->so_gio_day) }}">
            @error('so_gio_day')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
    </form>
</div>
@endsection
