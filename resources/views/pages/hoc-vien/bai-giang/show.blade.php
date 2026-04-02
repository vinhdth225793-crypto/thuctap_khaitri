@extends('layouts.app')

@section('title', $baiGiang->tieu_de)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-10 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.chi-tiet-khoa-hoc', $baiGiang->khoa_hoc_id) }}">{{ $baiGiang->khoaHoc->ten_khoa_hoc }}</a></li>
                    @if($baiGiang->lich_hoc_id)
                        <li class="breadcrumb-item"><a href="{{ route('hoc-vien.buoi-hoc.show', $baiGiang->lich_hoc_id) }}">Buổi {{ $baiGiang->lichHoc->buoi_so ?: '#' }}</a></li>
                    @endif
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
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge bg-primary-soft text-primary px-3 py-2 border-0">
                            <i class="fas fa-layer-group me-1"></i>{{ $baiGiang->moduleHoc->ten_module ?? 'Chưa gán module' }}
                        </span>
                        @if($baiGiang->lich_hoc_id)
                            <span class="badge bg-light text-dark border px-3 py-2">
                                <i class="fas fa-calendar-day me-1"></i>Buổi {{ $baiGiang->lichHoc->buoi_so ?: '#' }}
                            </span>
                        @endif
                        <span class="text-muted smaller align-self-center">
                            <i class="far fa-calendar-alt me-1"></i>Cập nhật: {{ $baiGiang->updated_at->format('d/m/Y') }}
                        </span>
                    </div>

                    <div class="lecture-description mb-5 text-dark" style="font-size: 1.05rem; line-height: 1.7;">
                        {!! nl2br(e($baiGiang->mo_ta ?: 'Bài giảng này chưa có mô tả chi tiết.')) !!}
                    </div>

                    @if($baiGiang->taiNguyenChinh)
                        <div class="main-resource-box mb-5">
                            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-star me-2 text-warning"></i>Nội dung chính</h5>
                            <div class="p-4 border rounded-4 bg-light">
                                @if($baiGiang->taiNguyenChinh->isVideo())
                                    <div class="ratio ratio-16x9 bg-black rounded shadow-sm overflow-hidden mb-3">
                                        <video controls class="w-100 h-100">
                                            <source src="{{ $baiGiang->taiNguyenChinh->file_url }}" type="{{ $baiGiang->taiNguyenChinh->mime_type }}">
                                        </video>
                                    </div>
                                @endif

                                <div class="d-flex flex-wrap gap-2">
                                    @if($baiGiang->taiNguyenChinh->file_url)
                                        <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary fw-bold px-4">
                                            <i class="fas fa-external-link-alt me-2"></i>Mở nội dung
                                        </a>
                                    @endif
                                    @if($baiGiang->taiNguyenChinh->is_downloadable)
                                        <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary fw-bold px-4">
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($baiGiang->taiNguyenPhu->isNotEmpty())
                        <div class="supplementary-resources">
                            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-paperclip me-2 text-info"></i>Tài liệu đính kèm</h5>
                            <div class="row g-3">
                                @foreach($baiGiang->taiNguyenPhu as $taiNguyen)
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between bg-white shadow-xs h-100">
                                            <div class="d-flex align-items-center overflow-hidden">
                                                <div class="bg-{{ $taiNguyen->loai_color }}-soft rounded-circle text-{{ $taiNguyen->loai_color }} d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                    <i class="fas {{ $taiNguyen->loai_icon }}"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="fw-bold text-dark text-truncate small">{{ $taiNguyen->tieu_de }}</div>
                                                    <div class="smaller text-muted">{{ $taiNguyen->loai_label }}</div>
                                                </div>
                                            </div>
                                            @if($taiNguyen->file_url)
                                                <a href="{{ $taiNguyen->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary ms-3">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-white border-0 p-4 border-top">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <a href="{{ $backUrl }}" class="btn btn-light fw-bold shadow-xs">
                            <i class="fas fa-arrow-left me-2"></i>{{ $baiGiang->lich_hoc_id ? 'Quay lại buổi học' : 'Quay lại khóa học' }}
                        </a>
                        <div class="text-muted smaller italic">
                            Bạn đang xem nội dung đã được phê duyệt để học viên truy cập.
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
