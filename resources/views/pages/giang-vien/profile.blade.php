@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
@php
    $giangVien = $user->giangVien;
    $avatarUrl = $user->anh_dai_dien ? asset('storage/' . $user->anh_dai_dien) : null;
    $initial = mb_strtoupper(mb_substr(trim($user->ho_ten), 0, 1, 'UTF-8'), 'UTF-8');
@endphp

<div class="container-fluid admin-page-x prof-page">
    <div class="apx-welcome prof-welcome">
        <div class="prof-avatar-lg">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $user->ho_ten }}">
            @else
                <span>{{ $initial ?: 'GV' }}</span>
            @endif
        </div>
        <div class="apx-welcome-text">
            <div class="prof-tag-row">
                <span class="prof-loai-badge"><i class="fas fa-chalkboard-teacher"></i> HỒ SƠ GIẢNG VIÊN</span>
                @if($user->trang_thai)
                    <span class="prof-status-badge is-success"><i class="fas fa-check-circle"></i> Tài khoản hoạt động</span>
                @else
                    <span class="prof-status-badge is-warning"><i class="fas fa-pause-circle"></i> Tạm khóa</span>
                @endif
                @if($giangVien?->hoc_vi)
                    <span class="prof-status-badge"><i class="fas fa-graduation-cap"></i> {{ $giangVien->hoc_vi }}</span>
                @endif
            </div>
            <h4>{{ $user->ho_ten }}</h4>
            <p>
                <span><i class="fas fa-envelope"></i> {{ $user->email }}</span>
                @if($user->so_dien_thoai)
                    <span class="prof-sep">·</span>
                    <span><i class="fas fa-phone"></i> {{ $user->so_dien_thoai }}</span>
                @endif
                @if($giangVien?->chuyen_nganh)
                    <span class="prof-sep">·</span>
                    <span><i class="fas fa-briefcase"></i> {{ $giangVien->chuyen_nganh }}</span>
                @endif
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    <form action="{{ route('giang-vien.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-lg-4">
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">1</span>
                            <div>
                                <h2><i class="fas fa-camera"></i> Ảnh đại diện</h2>
                                <p>Tải lên ảnh để mọi nơi đều hiển thị đẹp.</p>
                            </div>
                        </div>
                    </header>

                    <div class="prof-avatar-card">
                        <div class="prof-avatar-preview">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="avatar" id="profAvatarPreview">
                            @else
                                <div class="prof-avatar-fallback" id="profAvatarPreview">{{ $initial ?: 'GV' }}</div>
                            @endif
                        </div>
                        @if($user->anh_dai_dien)
                            <label class="prof-remove-row">
                                <input type="checkbox" name="xoa_anh_dai_dien" value="1">
                                <span><i class="fas fa-trash"></i> Xóa ảnh hiện tại</span>
                            </label>
                        @endif
                        <label for="anh_dai_dien" class="prof-upload-btn">
                            <i class="fas fa-upload"></i> Tải ảnh mới
                            <input type="file" name="anh_dai_dien" id="anh_dai_dien" accept="image/*" hidden onchange="previewProfAvatar(this)">
                        </label>
                        @error('anh_dai_dien')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        <div class="prof-upload-hint">
                            <i class="fas fa-circle-info"></i> JPG, PNG, GIF — tối đa 2 MB
                        </div>
                    </div>
                </section>

                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">2</span>
                            <div>
                                <h2><i class="fas fa-id-badge"></i> Tóm tắt</h2>
                                <p>Thông tin nhanh tài khoản giảng viên.</p>
                            </div>
                        </div>
                    </header>

                    <div class="prof-meta-card">
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Mã NV</span>
                            <strong>#{{ $user->ma_nguoi_dung }}</strong>
                        </div>
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Vai trò</span>
                            <span class="prof-pill is-info"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</span>
                        </div>
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Trạng thái</span>
                            @if($user->trang_thai)
                                <span class="prof-pill is-success"><i class="fas fa-check"></i> Hoạt động</span>
                            @else
                                <span class="prof-pill is-warning"><i class="fas fa-pause"></i> Tạm khóa</span>
                            @endif
                        </div>
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Tạo lúc</span>
                            <strong>{{ optional($user->created_at)->format('d/m/Y') }}</strong>
                        </div>
                        @if($giangVien?->so_gio_day !== null)
                            <div class="prof-meta-row">
                                <span class="prof-meta-label">Số giờ dạy</span>
                                <strong class="text-primary">{{ $giangVien->so_gio_day }} giờ</strong>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="col-lg-8">
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">3</span>
                            <div>
                                <h2><i class="fas fa-user-pen"></i> Thông tin cơ bản</h2>
                                <p>Họ tên, email, số điện thoại — dùng cho mọi giao tiếp.</p>
                            </div>
                        </div>
                    </header>

                    <div class="prof-form-card">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="prof-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" name="ho_ten" class="form-control prof-input @error('ho_ten') is-invalid @enderror" value="{{ old('ho_ten', $user->ho_ten) }}" required>
                                @error('ho_ten')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="prof-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control prof-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="prof-label">Số điện thoại</label>
                                <input type="text" name="so_dien_thoai" class="form-control prof-input @error('so_dien_thoai') is-invalid @enderror" value="{{ old('so_dien_thoai', $user->so_dien_thoai) }}">
                                @error('so_dien_thoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="prof-label">Ngày sinh</label>
                                <input type="date" name="ngay_sinh" class="form-control prof-input @error('ngay_sinh') is-invalid @enderror" value="{{ old('ngay_sinh', optional($user->ngay_sinh)->format('Y-m-d')) }}">
                                @error('ngay_sinh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="prof-label">Địa chỉ</label>
                                <textarea name="dia_chi" rows="3" class="form-control prof-input @error('dia_chi') is-invalid @enderror">{{ old('dia_chi', $user->dia_chi) }}</textarea>
                                @error('dia_chi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">4</span>
                            <div>
                                <h2><i class="fas fa-briefcase"></i> Thông tin giảng dạy</h2>
                                <p>Chuyên ngành, học vị, số giờ dạy.</p>
                            </div>
                        </div>
                    </header>

                    <div class="prof-form-card">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="prof-label">Chuyên ngành</label>
                                <input type="text" name="chuyen_nganh" class="form-control prof-input @error('chuyen_nganh') is-invalid @enderror" value="{{ old('chuyen_nganh', $giangVien?->chuyen_nganh) }}">
                                @error('chuyen_nganh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="prof-label">Học vị</label>
                                <input type="text" name="hoc_vi" class="form-control prof-input @error('hoc_vi') is-invalid @enderror" value="{{ old('hoc_vi', $giangVien?->hoc_vi) }}" placeholder="VD: Thạc sĩ, Tiến sĩ">
                                @error('hoc_vi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="prof-label">Số giờ dạy</label>
                                <input type="text" name="so_gio_day" class="form-control prof-input" value="{{ old('so_gio_day', $giangVien?->so_gio_day) }}" readonly>
                                <small class="text-muted">Hệ thống tự cập nhật theo lịch giảng.</small>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">5</span>
                            <div>
                                <h2><i class="fas fa-key"></i> Đổi mật khẩu</h2>
                                <p>Để trống nếu bạn không muốn đổi mật khẩu.</p>
                            </div>
                        </div>
                    </header>

                    <div class="prof-form-card">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="prof-label">Mật khẩu mới</label>
                                <input type="password" name="mat_khau" class="form-control prof-input @error('mat_khau') is-invalid @enderror" placeholder="Tối thiểu 8 ký tự">
                                @error('mat_khau')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="prof-label">Xác nhận mật khẩu</label>
                                <input type="password" name="mat_khau_confirmation" class="form-control prof-input" placeholder="Nhập lại mật khẩu mới">
                            </div>
                        </div>
                    </div>
                </section>

                <div class="prof-actions">
                    <button type="submit" class="prof-btn-save">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                    <a href="{{ route('giang-vien.dashboard') }}" class="prof-btn-cancel">
                        <i class="fas fa-times"></i> Hủy bỏ
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@include('components.profile-styles')

<script>
function previewProfAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const old = document.getElementById('profAvatarPreview');
        const wrap = old.parentElement;
        wrap.innerHTML = '<img src="' + e.target.result + '" alt="avatar" id="profAvatarPreview">';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endsection
