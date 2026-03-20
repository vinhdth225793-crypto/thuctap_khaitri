@extends('layouts.app')

@section('title', $baiGiang->tieu_de)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-10 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $baiGiang->khoa_hoc_id) }}">{{ $baiGiang->khoaHoc->ten_khoa_hoc }}</a></li>
                    <li class="breadcrumb-item active">{{ $baiGiang->tieu_de }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="fw-bold mb-3 text-dark">{{ $baiGiang->tieu_de }}</h2>
                    <div class="d-flex align-items-center mb-4">
                        <span class="badge bg-primary-soft text-primary px-3 py-2 border-0 me-3">
                            <i class="fas fa-layer-group me-1"></i> Module: {{ $baiGiang->moduleHoc->ten_module }}
                        </span>
                        <span class="text-muted smaller">
                            <i class="far fa-calendar-alt me-1"></i> Cập nhật: {{ $baiGiang->updated_at->format('d/m/Y') }}
                        </span>
                    </div>

                    <div class="lecture-description mb-5 text-dark" style="font-size: 1.1rem; line-height: 1.6;">
                        {!! nl2br(e($baiGiang->mo_ta)) !!}
                    </div>

                    @if($baiGiang->taiNguyenChinh)
                        <div class="main-resource-box mb-5">
                            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-star me-2 text-warning"></i>Nội dung bài học chính:</h5>
                            <div class="p-4 border rounded-4 bg-light">
                                @if($baiGiang->taiNguyenChinh->isVideo())
                                    <div class="ratio ratio-16x9 bg-black rounded shadow-sm overflow-hidden mb-3">
                                        {{-- Placeholder for video player --}}
                                        <video controls class="w-100 h-100">
                                            <source src="{{ $baiGiang->taiNguyenChinh->file_url }}" type="{{ $baiGiang->taiNguyenChinh->mime_type }}">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                    <div class="text-center">
                                        <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" class="btn btn-primary fw-bold px-4">
                                            <i class="fas fa-external-link-alt me-2"></i>Mở video trong tab mới
                                        </a>
                                    </div>
                                @elseif($baiGiang->taiNguyenChinh->isPdf())
                                    <div class="text-center py-5 bg-white border rounded shadow-xs mb-3">
                                        <i class="fas fa-file-pdf fa-4x text-danger opacity-25 mb-3"></i>
                                        <h6 class="fw-bold">{{ $baiGiang->taiNguyenChinh->file_name }}</h6>
                                        <p class="text-muted small">Tài liệu định dạng PDF</p>
                                        <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" class="btn btn-danger fw-bold px-4">
                                            <i class="fas fa-eye me-2"></i>Xem tài liệu ngay
                                        </a>
                                    </div>
                                @else
                                    <div class="text-center py-5 bg-white border rounded shadow-xs">
                                        <i class="fas {{ $baiGiang->taiNguyenChinh->loai_icon }} fa-4x text-secondary opacity-25 mb-3"></i>
                                        <h6 class="fw-bold">{{ $baiGiang->taiNguyenChinh->tieu_de }}</h6>
                                        <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" class="btn btn-dark fw-bold px-4 mt-2">
                                            <i class="fas fa-download me-2"></i>Tải xuống / Truy cập
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($baiGiang->taiNguyenPhu->isNotEmpty())
                        <div class="supplementary-resources">
                            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-paperclip me-2 text-info"></i>Tài liệu đính kèm:</h5>
                            <div class="row g-3">
                                @foreach($baiGiang->taiNguyenPhu as $tnp)
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between bg-white shadow-xs h-100">
                                            <div class="d-flex align-items-center overflow-hidden">
                                                <div class="bg-{{ $tnp->loai_color }}-soft rounded-circle text-{{ $tnp->loai_color }} d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                    <i class="fas {{ $tnp->loai_icon }}"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="fw-bold text-dark text-truncate small">{{ $tnp->tieu_de }}</div>
                                                    <div class="smaller text-muted">{{ $tnp->loai_label }}</div>
                                                </div>
                                            </div>
                                            <a href="{{ $tnp->file_url }}" target="_blank" class="btn btn-sm btn-outline-secondary ms-3">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-white border-0 p-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $baiGiang->khoa_hoc_id) }}" class="btn btn-light fw-bold shadow-xs">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại khóa học
                        </a>
                        <div class="text-muted smaller italic">
                            Bạn đang xem nội dung đã được phê duyệt bởi Ban đào tạo.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .rounded-4 { border-radius: 1rem !important; }
</style>
@endsection
