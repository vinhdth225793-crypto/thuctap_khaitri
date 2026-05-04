@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng')

@section('content')
@php
    $avatarUrl = $nguoiDung->anh_dai_dien ? asset('storage/' . $nguoiDung->anh_dai_dien) : null;
    $initial = mb_strtoupper(mb_substr(trim($nguoiDung->ho_ten ?? '?'), 0, 1, 'UTF-8'), 'UTF-8');
    $roleClass = match($nguoiDung->vai_tro){'admin'=>'is-danger','giang_vien'=>'is-info',default=>'is-success'};
    $roleIcon  = match($nguoiDung->vai_tro){'admin'=>'fa-shield-halved','giang_vien'=>'fa-chalkboard-teacher',default=>'fa-user-graduate'};
    $roleLabel = match($nguoiDung->vai_tro){'admin'=>'Admin','giang_vien'=>'Giảng viên',default=>'Học viên'};
@endphp

<div class="container-fluid admin-page-x prof-page">
    <div class="apx-welcome prof-welcome">
        <div class="prof-avatar-lg">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $nguoiDung->ho_ten }}">
            @else
                <span>{{ $initial }}</span>
            @endif
        </div>
        <div class="apx-welcome-text">
            <div class="prof-tag-row">
                <span class="prof-loai-badge"><i class="fas fa-user-pen"></i> CHỈNH SỬA NGƯỜI DÙNG</span>
                <span class="prof-status-badge {{ $roleClass }}"><i class="fas {{ $roleIcon }}"></i> {{ $roleLabel }}</span>
                @if($nguoiDung->trang_thai)
                    <span class="prof-status-badge is-success"><i class="fas fa-check-circle"></i> Hoạt động</span>
                @else
                    <span class="prof-status-badge is-warning"><i class="fas fa-pause-circle"></i> Khóa</span>
                @endif
                @if($nguoiDung->trashed())
                    <span class="prof-status-badge is-danger"><i class="fas fa-trash"></i> Đã xóa</span>
                @endif
            </div>
            <h4>{{ $nguoiDung->ho_ten }}</h4>
            <p>
                <span><i class="fas fa-hashtag"></i> ID #{{ $nguoiDung->ma_nguoi_dung }}</span>
                <span class="prof-sep">·</span>
                <span><i class="fas fa-envelope"></i> {{ $nguoiDung->email }}</span>
                @if($nguoiDung->so_dien_thoai)
                    <span class="prof-sep">·</span>
                    <span><i class="fas fa-phone"></i> {{ $nguoiDung->so_dien_thoai }}</span>
                @endif
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

    <form action="{{ route('admin.tai-khoan.update', $nguoiDung->ma_nguoi_dung) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('pages.admin.quan-ly-tai-khoan.tai-khoan._form', ['user' => $nguoiDung])

        <div class="prof-actions">
            <button type="submit" class="prof-btn-save">
                <i class="fas fa-save"></i> Lưu thay đổi
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
