@extends('layouts.app', ['title' => 'Đăng ký tài khoản'])

@section('content')
<x-auth-card 
    title="Đăng ký tài khoản" 
    subtitle="Tạo tài khoản mới để bắt đầu sử dụng hệ thống"
    logoIcon="fa-user-plus"
>
    <x-alert />
    
    <form method="POST" action="{{ route('xu-ly-dang-ky') }}" class="needs-validation" novalidate>
        @csrf
        
        <div class="row">
            <div class="col-md-6">
                <x-form-input 
                    type="text"
                    name="ho_ten"
                    label="Họ và tên"
                    icon="fa-user"
                    placeholder="Nguyễn Văn A"
                    required="true"
                />
            </div>
            <div class="col-md-6">
                <x-form-input 
                    type="email"
                    name="email"
                    label="Email"
                    icon="fa-envelope"
                    placeholder="example@email.com"
                    required="true"
                />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <x-form-input 
                    type="password"
                    name="mat_khau"
                    label="Mật khẩu"
                    icon="fa-lock"
                    placeholder="Mật khẩu (tối thiểu 8 ký tự)"
                    required="true"
                />
            </div>
            <div class="col-md-6">
                <x-form-input 
                    type="password"
                    name="mat_khau_confirmation"
                    label="Xác nhận mật khẩu"
                    icon="fa-lock"
                    placeholder="Nhập lại mật khẩu"
                    required="true"
                />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <x-form-input 
                    type="tel"
                    name="so_dien_thoai"
                    label="Số điện thoại"
                    icon="fa-phone"
                    placeholder="0912345678"
                />
            </div>
            <div class="col-md-6">
                <x-form-input 
                    type="date"
                    name="ngay_sinh"
                    label="Ngày sinh"
                    icon="fa-calendar"
                />
            </div>
        </div>
        
        <x-form-input 
            type="text"
            name="dia_chi"
            label="Địa chỉ"
            icon="fa-home"
            placeholder="Số nhà, đường, thành phố"
        />
        
        <div class="mb-3">
            <label class="form-label">Vai trò *</label>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="vai_tro" id="hoc_vien" 
                               value="hoc_vien" checked>
                        <label class="form-check-label" for="hoc_vien">
                            <i class="fas fa-user-graduate me-1"></i> Học viên
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="vai_tro" id="giang_vien" 
                               value="giang_vien">
                        <label class="form-check-label" for="giang_vien">
                            <i class="fas fa-chalkboard-teacher me-1"></i> Giảng viên
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="dong_y_dieu_khoan" required>
            <label class="form-check-label" for="dong_y_dieu_khoan">
                Tôi đồng ý với <a href="#" class="text-decoration-none">Điều khoản dịch vụ</a> và 
                <a href="#" class="text-decoration-none">Chính sách bảo mật</a>
            </label>
            <div class="invalid-feedback">
                Bạn phải đồng ý với điều khoản dịch vụ.
            </div>
        </div>
        
        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn vip-btn vip-btn-primary btn-loading">
                <i class="fas fa-user-plus me-2"></i> Đăng ký tài khoản
            </button>
        </div>
        
        <div class="text-center">
            <p class="mb-0">Đã có tài khoản? 
                <a href="{{ route('dang-nhap') }}" class="text-decoration-none fw-semibold">Đăng nhập ngay</a>
            </p>
        </div>
    </form>
</x-auth-card>

@push('scripts')
<script>
    // Validate mật khẩu khớp
    $('#mat_khau_confirmation').on('keyup', function() {
        const password = $('#mat_khau').val();
        const confirmPassword = $(this).val();
        
        if (password !== confirmPassword) {
            $(this).addClass('is-invalid');
            $(this).removeClass('is-valid');
        } else {
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
        }
    });

    // Hiển thị thông báo khi chọn vai trò giảng viên
    $('input[name="vai_tro"]').on('change', function() {
        const selectedRole = $(this).val();
        const alertDiv = $('#role-alert');
        
        if (selectedRole === 'giang_vien') {
            if (alertDiv.length === 0) {
                // Tạo alert mới
                const alert = `
                    <div id="role-alert" class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        Tạo tài khoản giảng viên cần thời gian phê duyệt từ trung tâm.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                $('.needs-validation').prepend(alert);
            }
        } else {
            // Ẩn alert nếu chọn học viên
            if (alertDiv.length > 0) {
                alertDiv.remove();
            }
        }
    });
</script>
@endpush
@endsection