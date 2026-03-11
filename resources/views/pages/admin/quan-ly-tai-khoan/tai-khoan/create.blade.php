@extends('layouts.app')

@section('title', 'Thêm người dùng mới')

@section('content')
<div class="container">
    <h1>Thêm người dùng mới</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.tai-khoan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('pages.admin.quan-ly-tai-khoan.tai-khoan._form', ['user' => new \App\Models\NguoiDung])
        <button class="btn btn-primary">Lưu</button>
    </form>
</div>
@endsection