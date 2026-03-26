@extends('layouts.app')

@section('title', 'Cập nhật bài giảng')

@section('content')
    @include('pages.shared.bai-giang.form', [
        'formAction' => route('giang-vien.bai-giang.update', $baiGiang->id),
        'method' => 'PUT',
        'indexRoute' => route('giang-vien.bai-giang.index'),
        'getLichHocRoute' => route('giang-vien.bai-giang.get-lich-hoc'),
        'isAdmin' => false,
        'pageTitle' => 'Chỉnh sửa bài giảng',
        'pageSubtitle' => 'Cập nhật nội dung hoặc cấu hình phòng học trực tuyến.',
    ])
@endsection
