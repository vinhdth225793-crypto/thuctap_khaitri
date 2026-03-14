@extends('layouts.app')

@section('title', 'Thông báo của bạn')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold"><i class="fas fa-bell me-2 text-warning"></i> Thông báo</h3>
            <p class="text-muted small">Xem các cập nhật mới nhất từ hệ thống.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="vip-card">
        <div class="vip-card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($thongBaos as $tb)
                    <a href="{{ route('thong-bao.doc-mot', $tb->id) }}"
                       class="list-group-item list-group-item-action p-4 border-start border-4 {{ $tb->da_doc ? 'border-light' : 'border-warning bg-light-warning' }}">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold text-dark">
                                @if(!$tb->da_doc)
                                    <span class="badge bg-danger me-2" style="font-size:10px">MỚI</span>
                                @endif
                                <i class="fas {{ $tb->loai === 'phan_cong' ? 'fa-tasks text-primary' : ($tb->loai === 'xac_nhan_gv' ? 'fa-check-circle text-success' : 'fa-info-circle text-info') }} me-2"></i>
                                {{ $tb->tieu_de }}
                            </h6>
                            <small class="text-muted fw-bold"><i class="far fa-clock me-1"></i> {{ $tb->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-0 text-muted small" style="white-space: pre-line">{{ $tb->noi_dung }}</p>
                    </a>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-bell-slash fa-4x mb-3 opacity-25"></i>
                        <h5 class="fw-bold">Bạn chưa có thông báo nào</h5>
                        <p class="small">Các thông báo về phân công, mở lớp sẽ xuất hiện tại đây.</p>
                    </div>
                @endforelse
            </div>
        </div>
        @if($thongBaos->hasPages())
            <div class="vip-card-footer p-3 border-top d-flex justify-content-center">
                {{ $thongBaos->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .bg-light-warning { background-color: rgba(255, 193, 7, 0.05); }
    .list-group-item-action:hover { background-color: #f8f9fa; }
    .border-dashed { border-style: dashed !important; }
</style>
@endsection
