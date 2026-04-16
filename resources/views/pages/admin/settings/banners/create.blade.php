@extends('layouts.app')

@section('title', 'Thêm Banner Mới')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.settings.banners.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Thêm Banner Mới</h4>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            @include('pages.admin.settings.banners._form', [
                'banner' => null, 
                'action' => route('admin.settings.banners.store'), 
                'method' => 'POST',
                'suggestedOrder' => $suggestedOrder
            ])
        </div>
    </div>
</div>
@endsection
