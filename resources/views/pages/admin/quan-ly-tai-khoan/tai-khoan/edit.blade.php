@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng')

@section('content')
<div class="container">
    <h1>Chỉnh sửa người dùng</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.tai-khoan.update', $nguoiDung->ma_nguoi_dung) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('pages.admin.quan-ly-tai-khoan.tai-khoan._form', ['user' => $nguoiDung])
        <button class="btn btn-primary">Lưu</button>
    </form>
</div>
@endsection