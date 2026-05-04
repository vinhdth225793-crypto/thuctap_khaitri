@extends('layouts.app')

@section('title', 'Hồ sơ admin')

@section('content')
@php
    $avatarUrl = $user->anh_dai_dien ? asset('storage/' . $user->anh_dai_dien) : null;
    $initial = mb_strtoupper(mb_substr(trim($user->ho_ten), 0, 1, 'UTF-8'), 'UTF-8');

    // Đếm nhanh các con số quản trị
    $totalUsers   = \App\Models\NguoiDung::count();
    $totalCourses = \App\Models\KhoaHoc::count();
    $pendingApprovals = \App\Models\BaiKiemTra::where('trang_thai_duyet', 'cho_duyet')->count()
        + \App\Models\TaiNguyenBuoiHoc::where('trang_thai_duyet', 'cho_duyet')->count()
        + \App\Models\BaiGiang::where('trang_thai_duyet', 'cho_duyet')->count();
@endphp

<div class="container-fluid admin-page-x prof-page">
    <div class="apx-welcome prof-welcome">
        <div class="prof-avatar-lg">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $user->ho_ten }}">
            @else
                <span>{{ $initial ?: 'A' }}</span>
            @endif
        </div>
        <div class="apx-welcome-text">
            <div class="prof-tag-row">
                <span class="prof-loai-badge"><i class="fas fa-shield-halved"></i> HỒ SƠ QUẢN TRỊ</span>
                <span class="prof-status-badge is-success"><i class="fas fa-check-circle"></i> Quyền admin</span>
                <span class="prof-status-badge"><i class="fas fa-users"></i> {{ $totalUsers }} người dùng</span>
                @if($pendingApprovals > 0)
                    <span class="prof-status-badge is-warning"><i class="fas fa-hourglass-half"></i> {{ $pendingApprovals }} chờ duyệt</span>
                @endif
            </div>
            <h4>{{ $user->ho_ten }}</h4>
            <p>
                <span><i class="fas fa-envelope"></i> {{ $user->email }}</span>
                @if($user->so_dien_thoai)
                    <span class="prof-sep">·</span>
                    <span><i class="fas fa-phone"></i> {{ $user->so_dien_thoai }}</span>
                @endif
                <span class="prof-sep">·</span>
                <span><i class="fas fa-graduation-cap"></i> {{ $totalCourses }} khóa học đang quản lý</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.dashboard') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
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
                                <div class="prof-avatar-fallback" id="profAvatarPreview">{{ $initial ?: 'A' }}</div>
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
                                <p>Thông tin nhanh tài khoản admin.</p>
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
                            <span class="prof-pill is-danger"><i class="fas fa-shield-halved"></i> Admin</span>
                        </div>
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Trạng thái</span>
                            <span class="prof-pill is-success"><i class="fas fa-check"></i> Hoạt động</span>
                        </div>
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Tạo lúc</span>
                            <strong>{{ optional($user->created_at)->format('d/m/Y') }}</strong>
                        </div>
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Người dùng</span>
                            <strong class="text-primary">{{ $totalUsers }}</strong>
                        </div>
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Khóa học</span>
                            <strong class="text-primary">{{ $totalCourses }}</strong>
                        </div>
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Cần duyệt</span>
                            @if($pendingApprovals > 0)
                                <span class="prof-pill is-warning"><i class="fas fa-hourglass-half"></i> {{ $pendingApprovals }}</span>
                            @else
                                <span class="prof-pill is-success"><i class="fas fa-check"></i> 0</span>
                            @endif
                        </div>
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
                                <h2><i class="fas fa-key"></i> Đổi mật khẩu</h2>
                                <p>Để trống nếu bạn không muốn đổi mật khẩu. Khuyến nghị đổi định kỳ vì đây là tài khoản quản trị.</p>
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

                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">5</span>
                            <div>
                                <h2><i class="fas fa-link"></i> Liên kết quản trị</h2>
                                <p>Truy cập nhanh các trang quản lý.</p>
                            </div>
                        </div>
                    </header>

                    <div class="prof-form-card">
                        <div class="prof-quick-links">
                            <a href="{{ route('admin.tai-khoan.index') }}" class="prof-quick-link">
                                <i class="fas fa-users"></i>
                                <span>Quản lý tài khoản</span>
                            </a>
                            <a href="{{ route('admin.phe-duyet-tai-khoan.index') }}" class="prof-quick-link">
                                <i class="fas fa-user-check"></i>
                                <span>Phê duyệt tài khoản</span>
                            </a>
                            <a href="{{ route('admin.thu-vien.index') }}" class="prof-quick-link">
                                <i class="fas fa-folder-tree"></i>
                                <span>Thư viện hệ thống</span>
                            </a>
                            <a href="{{ route('admin.bai-giang.index') }}" class="prof-quick-link">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Phê duyệt bài giảng</span>
                            </a>
                            <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="prof-quick-link">
                                <i class="fas fa-shield-halved"></i>
                                <span>Phê duyệt đề thi</span>
                            </a>
                            <a href="{{ route('admin.xet-duyet-ket-qua.index') }}" class="prof-quick-link">
                                <i class="fas fa-stamp"></i>
                                <span>Xét duyệt kết quả</span>
                            </a>
                        </div>
                    </div>
                </section>

                <div class="prof-actions">
                    <button type="submit" class="prof-btn-save">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="prof-btn-cancel">
                        <i class="fas fa-times"></i> Hủy bỏ
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@include('components.profile-styles')

<style>
    /* Quick links cho admin */
    .prof-quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }
    .prof-quick-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        background: #fafafa;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.84rem;
        font-weight: 700;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .prof-quick-link:hover {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-color: #fecaca;
        color: #dc2626;
        transform: translateY(-1px);
    }
    .prof-quick-link i {
        flex-shrink: 0;
        width: 32px; height: 32px;
        border-radius: 8px;
        background: #eff6ff;
        color: #1d4ed8;
        display: grid; place-items: center;
        font-size: 0.86rem;
        transition: all 0.18s ease;
    }
    .prof-quick-link:hover i {
        background: #fef2f2;
        color: #dc2626;
    }
</style>

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
