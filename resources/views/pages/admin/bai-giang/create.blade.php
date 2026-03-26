@extends('layouts.app')

@section('title', 'Admin tạo bài giảng')

@section('content')
    @include('pages.shared.bai-giang.form', [
        'formAction' => route('admin.bai-giang.store'),
        'method' => 'POST',
        'indexRoute' => route('admin.bai-giang.index'),
        'getLichHocRoute' => route('admin.bai-giang.get-lich-hoc'),
        'isAdmin' => true,
        'pageTitle' => 'Tạo bài giảng (Admin)',
        'pageSubtitle' => 'Quản trị viên trực tiếp thiết kế bài giảng hoặc cấu hình phòng họp live.',
    ])
@endsection
