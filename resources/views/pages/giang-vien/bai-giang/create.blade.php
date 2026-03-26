@extends('layouts.app')

@section('title', 'Tạo bài giảng mới')

@section('content')
    @include('pages.shared.bai-giang.form', [
        'formAction' => route('giang-vien.bai-giang.store'),
        'method' => 'POST',
        'indexRoute' => route('giang-vien.bai-giang.index'),
        'getLichHocRoute' => route('giang-vien.bai-giang.get-lich-hoc'),
        'isAdmin' => false,
        'pageTitle' => 'Thiết kế bài giảng',
        'pageSubtitle' => 'Tạo bài giảng thường hoặc phòng học live trong luồng giảng viên.',
    ])
@endsection
