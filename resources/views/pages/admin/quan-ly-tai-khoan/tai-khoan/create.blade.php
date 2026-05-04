@extends('layouts.app')

@section('title', 'Thêm người dùng mới')

@section('content')
<div class="container-fluid admin-page-x prof-page">
    <div class="apx-welcome prof-welcome">
        <div class="prof-avatar-lg">
            <span><i class="fas fa-user-plus"></i></span>
        </div>
        <div class="apx-welcome-text">
            <div class="prof-tag-row">
                <span class="prof-loai-badge"><i class="fas fa-user-plus"></i> THÊM NGƯỜI DÙNG MỚI</span>
                <span class="prof-status-badge"><i class="fas fa-shield-halved"></i> Tạo bởi admin</span>
            </div>
            <h4>Tạo tài khoản người dùng</h4>
            <p>
                <span><i class="fas fa-circle-info"></i> Tài khoản tạo bởi admin sẽ tự động kích hoạt ngay sau khi lưu</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.tai-khoan.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Danh sách</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <strong><i class="fas fa-circle-exclamation me-1"></i> Có lỗi xảy ra:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.tai-khoan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('pages.admin.quan-ly-tai-khoan.tai-khoan._form', ['user' => new \App\Models\NguoiDung])

        <div class="prof-actions">
            <button type="submit" class="prof-btn-save">
                <i class="fas fa-plus"></i> Tạo tài khoản
            </button>
            <a href="{{ route('admin.tai-khoan.index') }}" class="prof-btn-cancel">
                <i class="fas fa-times"></i> Hủy bỏ
            </a>
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
