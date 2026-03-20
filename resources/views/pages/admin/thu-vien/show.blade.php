@extends('layouts.app')

@section('title', 'Chi tiết tài nguyên')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.thu-vien.index') }}">Thư viện hệ thống</a></li>
                    <li class="breadcrumb-item active">Chi tiết tài nguyên</li>
                </ol>
            </nav>
            <h2 class="fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Phê duyệt tài nguyên</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            <div class="rounded-circle bg-{{ $taiNguyen->loai_color }}-soft p-4 d-inline-block text-{{ $taiNguyen->loai_color }}">
                                <i class="fas {{ $taiNguyen->loai_icon }} fa-3x"></i>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="fw-bold mb-1">{{ $taiNguyen->tieu_de }}</h3>
                                    <p class="text-muted mb-2">Loại: <span class="badge bg-light text-dark border">{{ $taiNguyen->loai_label }}</span> | Phạm vi: <span class="text-primary fw-bold">{{ $taiNguyen->pham_vi_su_dung }}</span></p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $taiNguyen->trang_thai_duyet === 'da_duyet' ? 'success' : 'warning' }} fs-6">
                                        {{ $taiNguyen->trang_thai_duyet }}
                                    </span>
                                </div>
                            </div>
                            <hr>
                            <div class="row smaller">
                                <div class="col-6 mb-2">
                                    <span class="text-muted d-block">Người tạo:</span>
                                    <span class="fw-bold text-dark">{{ $taiNguyen->nguoiTao->ho_ten ?? 'N/A' }} ({{ $taiNguyen->vai_tro_nguoi_tao }})</span>
                                </div>
                                <div class="col-6 mb-2">
                                    <span class="text-muted d-block">Ngày gửi duyệt:</span>
                                    <span class="fw-bold text-dark">{{ $taiNguyen->ngay_gui_duyet ? $taiNguyen->ngay_gui_duyet->format('d/m/Y H:i') : 'Chưa gửi' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-2">Mô tả:</h6>
                        <p class="mb-0 text-dark">{{ $taiNguyen->mo_ta ?: 'Không có mô tả.' }}</p>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Nội dung tệp tin / Liên kết:</h6>
                        <div class="p-4 border rounded bg-white text-center">
                            @if($taiNguyen->isVideo())
                                <div class="mb-3">
                                    <i class="fas fa-video fa-3x text-primary opacity-25"></i>
                                </div>
                                <a href="{{ $taiNguyen->file_url }}" target="_blank" class="btn btn-primary px-4 fw-bold">
                                    <i class="fas fa-play me-2"></i>Xem Video
                                </a>
                            @elseif($taiNguyen->isPdf())
                                <div class="mb-3">
                                    <i class="fas fa-file-pdf fa-3x text-danger opacity-25"></i>
                                </div>
                                <a href="{{ $taiNguyen->file_url }}" target="_blank" class="btn btn-danger px-4 fw-bold">
                                    <i class="fas fa-file-pdf me-2"></i>Xem PDF
                                </a>
                            @else
                                <div class="mb-3">
                                    <i class="fas {{ $taiNguyen->loai_icon }} fa-3x text-secondary opacity-25"></i>
                                </div>
                                <a href="{{ $taiNguyen->file_url }}" target="_blank" class="btn btn-outline-dark px-4 fw-bold">
                                    <i class="fas fa-external-link-alt me-2"></i>Mở tài nguyên
                                </a>
                            @endif
                            <div class="mt-3 smaller text-muted text-break">{{ $taiNguyen->file_url }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2 text-success"></i>Thực hiện phê duyệt</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.thu-vien.duyet', $taiNguyen->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quyết định <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check card p-2 border flex-fill">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="trang_thai_duyet" id="duyet_ok" value="da_duyet" {{ $taiNguyen->trang_thai_duyet === 'da_duyet' ? 'checked' : '' }} required>
                                    <label class="form-check-label fw-bold text-success" for="duyet_ok">
                                        <i class="fas fa-check-circle me-1"></i> Đồng ý duyệt
                                    </label>
                                </div>
                                <div class="form-check card p-2 border flex-fill">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="trang_thai_duyet" id="duyet_edit" value="can_chinh_sua" {{ $taiNguyen->trang_thai_duyet === 'can_chinh_sua' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-info" for="duyet_edit">
                                        <i class="fas fa-edit me-1"></i> Yêu cầu sửa
                                    </label>
                                </div>
                                <div class="form-check card p-2 border flex-fill">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="trang_thai_duyet" id="duyet_no" value="tu_choi" {{ $taiNguyen->trang_thai_duyet === 'tu_choi' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-danger" for="duyet_no">
                                        <i class="fas fa-times-circle me-1"></i> Từ chối
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú phản hồi cho giảng viên</label>
                            <textarea name="ghi_chu_admin" class="form-control" rows="4" placeholder="Nhập lý do nếu từ chối hoặc yêu cầu sửa đổi...">{{ old('ghi_chu_admin', $taiNguyen->ghi_chu_admin) }}</textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i>Cập nhật phê duyệt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .smaller { font-size: 0.85rem; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
</style>
@endsection
