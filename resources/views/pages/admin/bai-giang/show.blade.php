@extends('layouts.app')

@section('title', 'Chi tiết bài giảng')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-10 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.bai-giang.index') }}">Bài giảng</a></li>
                    <li class="breadcrumb-item active">Chi tiết</li>
                </ol>
            </nav>
            <h2 class="fw-bold"><i class="fas fa-search me-2 text-primary"></i>Phê duyệt bài giảng</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="row">
                <!-- Content -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h3 class="fw-bold mb-3">{{ $baiGiang->tieu_de }}</h3>
                            <div class="mb-4 text-dark" style="white-space: pre-line;">
                                {{ $baiGiang->mo_ta ?: 'Không có mô tả.' }}
                            </div>

                            @if($baiGiang->taiNguyenChinh)
                                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-star me-2"></i>Tài nguyên chính:</h6>
                                <div class="p-3 border rounded bg-light mb-4">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas {{ $baiGiang->taiNguyenChinh->loai_icon }} fa-2x text-{{ $baiGiang->taiNguyenChinh->loai_color }} me-3"></i>
                                            <div>
                                                <div class="fw-bold">{{ $baiGiang->taiNguyenChinh->tieu_de }}</div>
                                                <div class="smaller text-muted">{{ $baiGiang->taiNguyenChinh->loai_label }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary px-3">
                                            Xem tài nguyên
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($baiGiang->taiNguyenPhu->isNotEmpty())
                                <h6 class="fw-bold text-muted mb-3"><i class="fas fa-paperclip me-2"></i>Tài nguyên phụ:</h6>
                                <div class="row g-2">
                                    @foreach($baiGiang->taiNguyenPhu as $tnp)
                                        <div class="col-md-6">
                                            <div class="p-2 border rounded d-flex align-items-center justify-content-between bg-white">
                                                <div class="d-flex align-items-center overflow-hidden">
                                                    <i class="fas {{ $tnp->loai_icon }} text-{{ $tnp->loai_color }} me-2"></i>
                                                    <span class="text-truncate smaller fw-bold">{{ $tnp->tieu_de }}</span>
                                                </div>
                                                <a href="{{ $tnp->file_url }}" target="_blank" class="ms-2">
                                                    <i class="fas fa-external-link-alt smaller"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Approval Sidebar -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-info"></i>Thông tin bài giảng</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <span class="text-muted smaller d-block">Giảng viên:</span>
                                <span class="fw-bold text-dark">{{ $baiGiang->nguoiTao->ho_ten ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted smaller d-block">Khóa học:</span>
                                <span class="text-dark">{{ $baiGiang->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted smaller d-block">Module:</span>
                                <span class="text-dark">{{ $baiGiang->moduleHoc->ten_module ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted smaller d-block">Vị trí:</span>
                                <span class="badge bg-light text-dark border">
                                    {{ $baiGiang->lichHoc ? 'Buổi ' . $baiGiang->lichHoc->buoi_so : 'Chung cho Module' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-check-double me-2 text-success"></i>Duyệt nội dung</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.bai-giang.duyet', $baiGiang->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold smaller">Quyết định:</label>
                                    <select name="trang_thai_duyet" class="form-select mb-3">
                                        <option value="da_duyet" {{ $baiGiang->trang_thai_duyet === 'da_duyet' ? 'selected' : '' }}>Đồng ý duyệt</option>
                                        <option value="can_chinh_sua" {{ $baiGiang->trang_thai_duyet === 'can_chinh_sua' ? 'selected' : '' }}>Yêu cầu sửa</option>
                                        <option value="tu_choi" {{ $baiGiang->trang_thai_duyet === 'tu_choi' ? 'selected' : '' }}>Từ chối</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold smaller">Phản hồi:</label>
                                    <textarea name="ghi_chu_admin" class="form-control" rows="3">{{ $baiGiang->ghi_chu_admin }}</textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success fw-bold">Cập nhật phê duyệt</button>
                                </div>
                            </form>

                            @if($baiGiang->trang_thai_duyet === 'da_duyet')
                                <hr>
                                <form action="{{ route('admin.bai-giang.cong-bo', $baiGiang->id) }}" method="POST">
                                    @csrf
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-{{ $baiGiang->trang_thai_cong_bo === 'da_cong_bo' ? 'warning' : 'primary' }} fw-bold">
                                            <i class="fas fa-eye me-2"></i>
                                            {{ $baiGiang->trang_thai_cong_bo === 'da_cong_bo' ? 'Ẩn khỏi học viên' : 'Công bố ngay' }}
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
