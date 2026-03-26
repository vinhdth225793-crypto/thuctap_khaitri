@extends('layouts.app')

@section('title', 'Admin cập nhật bài giảng')

@section('content')
    @include('pages.shared.bai-giang.form', [
        'formAction' => route('admin.bai-giang.update', $baiGiang->id),
        'method' => 'PUT',
        'indexRoute' => route('admin.bai-giang.index'),
        'getLichHocRoute' => route('admin.bai-giang.get-lich-hoc'),
        'isAdmin' => true,
        'pageTitle' => 'Chỉnh sửa bài giảng (Admin)',
        'pageSubtitle' => 'Cập nhật nội dung hoặc cấu hình phòng học trực tuyến.',
    ])
@endsection
