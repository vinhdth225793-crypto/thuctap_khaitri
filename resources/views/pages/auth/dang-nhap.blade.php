@extends('layouts.app', ['title' => 'Đăng nhập'])

@section('content')
<x-auth-card 
    title="Đăng nhập" 
    subtitle="Chào mừng trở lại! Vui lòng đăng nhập vào tài khoản của bạn"
    logoIcon="fa-graduation-cap"
>
    <x-alert />
    
    <form method="POST" action="{{ route('xu-ly-dang-nhap') }}" class="needs-validation" novalidate>
        @csrf
        
        <x-form-input 
            type="email"
            name="email"
            label="Email"
            icon="fa-envelope"
            placeholder="Nhập email của bạn"
            required="true"
        />
        
        <x-form-input 
            type="password"
            name="mat_khau"
            label="Mật khẩu"
            icon="fa-lock"
            placeholder="Nhập mật khẩu"
            required="true"
            showTogglePassword="true"
        />
        
        <div class="mb-3 form-check d-flex justify-content-between align-items-center">
            <div>
                <input type="checkbox" class="form-check-input" id="ghi_nho" name="ghi_nho">
                <label class="form-check-label" for="ghi_nho">Ghi nhớ đăng nhập</label>
            </div>
            <a href="{{ url('/quen-mat-khau') }}" class="text-decoration-none">Quên mật khẩu?</a>
        </div>
        
        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn vip-btn vip-btn-primary btn-loading">
                <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập
            </button>
        </div>
        
        <div class="text-center">
            <p class="mb-0">Chưa có tài khoản? 
                <a href="{{ route('dang-ky') }}" class="text-decoration-none fw-semibold">Đăng ký ngay</a>
            </p>
        </div>
    </form>
</x-auth-card>

@push('scripts')
<script>
    // Xử lý toggle password cho tất cả các nút có data-toggle-password
    $('[data-toggle-password]').on('click', function() {
        const targetSelector = $(this).data('toggle-password');
        const passwordInput = $(targetSelector);
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
</script>
@endpush
@endsection