@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Hồ sơ cá nhân</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-user-edit fa-lg"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">Chỉnh sửa thông tin cá nhân</h3>
                    <div class="text-muted small mt-1">Cập nhật thông tin tài khoản và thông tin học tập của bạn.</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('hoc-vien.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Thông tin cơ bản -->
            <div class="col-lg-8">
                <div class="card vip-card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Thông tin cơ bản
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="ho_ten" class="form-label small fw-bold">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" name="ho_ten" id="ho_ten" class="form-control vip-form-control @error('ho_ten') is-invalid @enderror" value="{{ old('ho_ten', $user->ho_ten) }}" required>
                                @error('ho_ten')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control vip-form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="so_dien_thoai" class="form-label small fw-bold">Số điện thoại</label>
                                <input type="text" name="so_dien_thoai" id="so_dien_thoai" class="form-control vip-form-control @error('so_dien_thoai') is-invalid @enderror" value="{{ old('so_dien_thoai', $user->so_dien_thoai) }}">
                                @error('so_dien_thoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="ngay_sinh" class="form-label small fw-bold">Ngày sinh</label>
                                <input type="date" name="ngay_sinh" id="ngay_sinh" class="form-control vip-form-control @error('ngay_sinh') is-invalid @enderror" value="{{ old('ngay_sinh', optional($user->ngay_sinh)->format('Y-m-d')) }}">
                                @error('ngay_sinh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="dia_chi" class="form-label small fw-bold">Địa chỉ</label>
                                <textarea name="dia_chi" id="dia_chi" class="form-control vip-form-control @error('dia_chi') is-invalid @enderror" rows="3">{{ old('dia_chi', $user->dia_chi) }}</textarea>
                                @error('dia_chi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3 text-dark">
                            <i class="fas fa-user-graduate me-2 text-primary"></i>Thông tin học tập
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="lop" class="form-label small fw-bold">Lớp</label>
                                <input type="text" name="lop" id="lop" class="form-control vip-form-control @error('lop') is-invalid @enderror" value="{{ old('lop', optional($user->hocVien)->lop) }}">
                                @error('lop')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="nganh" class="form-label small fw-bold">Ngành</label>
                                <input type="text" name="nganh" id="nganh" class="form-control vip-form-control @error('nganh') is-invalid @enderror" value="{{ old('nganh', optional($user->hocVien)->nganh) }}">
                                @error('nganh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="diem_trung_binh" class="form-label small fw-bold">Điểm trung bình</label>
                                <input type="text" name="diem_trung_binh" id="diem_trung_binh" class="form-control vip-form-control @error('diem_trung_binh') is-invalid @enderror" value="{{ old('diem_trung_binh', optional($user->hocVien)->diem_trung_binh) }}">
                                @error('diem_trung_binh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card vip-card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-key me-2 text-primary"></i>Đổi mật khẩu
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="mat_khau" class="form-label small fw-bold">Mật khẩu mới</label>
                                <input type="password" name="mat_khau" id="mat_khau" class="form-control vip-form-control @error('mat_khau') is-invalid @enderror" placeholder="Để trống nếu không đổi">
                                @error('mat_khau')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="mat_khau_confirmation" class="form-label small fw-bold">Xác nhận mật khẩu</label>
                                <input type="password" name="mat_khau_confirmation" id="mat_khau_confirmation" class="form-control vip-form-control" placeholder="Nhập lại mật khẩu mới">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ảnh đại diện -->
            <div class="col-lg-4">
                <div class="card vip-card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 text-center">
                        <h5 class="fw-bold mb-0 text-dark">Ảnh đại diện</h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            @if($user->anh_dai_dien)
                                <img src="{{ asset('storage/'.$user->anh_dai_dien) }}" alt="avatar" class="rounded-circle shadow-sm" width="150" height="150" style="object-fit: cover;">
                                <div class="form-check justify-content-center d-flex mt-2">
                                    <input class="form-check-input me-2" type="checkbox" name="xoa_anh_dai_dien" value="1" id="xoa_anh">
                                    <label class="form-check-label small" for="xoa_anh">Xóa ảnh hiện tại</label>
                                </div>
                            @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 150px; height: 150px;">
                                    <i class="fas fa-user fa-4x text-muted opacity-50"></i>
                                </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="anh_dai_dien" class="form-label small fw-bold">Tải lên ảnh mới</label>
                            <input type="file" name="anh_dai_dien" id="anh_dai_dien" class="form-control form-control-sm @error('anh_dai_dien') is-invalid @enderror" accept="image/*">
                            @error('anh_dai_dien')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="small text-muted italic">Hỗ trợ định dạng: JPG, PNG, GIF. Tối đa 2MB.</div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-3 fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i>LƯU THAY ĐỔI
                    </button>
                    <a href="{{ route('hoc-vien.dashboard') }}" class="btn btn-outline-secondary py-2 fw-bold">
                        HỦY BỎ
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
</style>
@endsection
