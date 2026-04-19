@extends('layouts.app')

@section('title', 'Chi tiết bài giảng')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-10 mx-auto">
            <a href="{{ route('admin.bai-giang.index') }}" class="btn btn-link ps-0 text-decoration-none">&larr; Quay lại danh sách</a>
            <h2 class="fw-bold mb-0">Chi tiết bài giảng</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h3 class="fw-bold mb-3 text-primary">{{ $baiGiang->tieu_de }}</h3>
                            <div class="mb-3">
                                <span class="badge bg-light text-primary border text-capitalize">{{ str_replace('_', ' ', $baiGiang->loai_bai_giang) }}</span>
                                <span class="badge bg-light text-dark border ms-2">Thứ tự: {{ $baiGiang->thu_tu_hien_thi }}</span>
                            </div>
                            <p class="mb-4 text-dark">{{ $baiGiang->mo_ta ?: 'Không có mô tả.' }}</p>

                            <div class="row g-4 mb-4 border rounded p-3 bg-light">
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase fw-bold smaller">Khóa học</div>
                                    <div class="fw-bold text-dark">{{ $baiGiang->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase fw-bold smaller">Module</div>
                                    <div class="fw-bold text-dark">{{ $baiGiang->moduleHoc->ten_module ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase fw-bold smaller">Buổi học</div>
                                    <div class="fw-bold text-dark">{{ $baiGiang->lichHoc ? 'Buổi ' . $baiGiang->lichHoc->buoi_so : 'Chung cho module' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted text-uppercase fw-bold smaller">Người tạo</div>
                                    <div class="fw-bold text-dark">{{ $baiGiang->nguoiTao->ho_ten ?? 'N/A' }}</div>
                                </div>
                            </div>

                            @if($baiGiang->taiNguyenChinh)
                                <div class="mb-4">
                                    <h5 class="fw-bold mb-3"><i class="fas fa-file-alt me-2 text-primary"></i>Tài nguyên chính</h5>
                                    <div class="card border shadow-xs">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $baiGiang->taiNguyenChinh->tieu_de }}</div>
                                                    <div class="small text-muted">{{ $baiGiang->taiNguyenChinh->loai_label }}</div>
                                                </div>
                                                <a href="{{ $baiGiang->taiNguyenChinh->file_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary fw-bold">
                                                    MỞ TÀI NGUYÊN
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($baiGiang->taiNguyenPhu->isNotEmpty())
                                <div class="mb-4">
                                    <h5 class="fw-bold mb-3"><i class="fas fa-paperclip me-2 text-primary"></i>Tài nguyên phụ</h5>
                                    <div class="row g-3">
                                        @foreach($baiGiang->taiNguyenPhu as $taiNguyen)
                                            <div class="col-md-6">
                                                <div class="card border shadow-xs h-100">
                                                    <div class="card-body">
                                                        <div class="fw-bold text-dark mb-1">{{ $taiNguyen->tieu_de }}</div>
                                                        <div class="small text-muted mb-3">{{ $taiNguyen->loai_label }}</div>
                                                        <a href="{{ $taiNguyen->file_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary fw-bold w-100">
                                                            MỞ TÀI NGUYÊN
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($baiGiang->phongHocLive)
                                <div class="border-top pt-4 mt-4">
                                    <h5 class="fw-bold mb-4 text-danger"><i class="fas fa-video me-2"></i>Cấu hình phòng học live</h5>
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <div class="small text-muted text-uppercase fw-bold smaller">Nền tảng</div>
                                            <div class="fw-bold text-dark">{{ $baiGiang->phongHocLive->platform_label }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted text-uppercase fw-bold smaller">Người chủ trì (Moderator)</div>
                                            <div class="fw-bold text-dark">{{ $baiGiang->phongHocLive->moderator->ho_ten ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted text-uppercase fw-bold smaller">Trợ giảng</div>
                                            <div class="fw-bold text-dark">{{ $baiGiang->phongHocLive->troGiang->ho_ten ?? 'Không có' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted text-uppercase fw-bold smaller">Thời gian bắt đầu</div>
                                            <div class="fw-bold text-dark">{{ $baiGiang->phongHocLive->thoi_gian_bat_dau->format('d/m/Y H:i') }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted text-uppercase fw-bold smaller">Thời lượng</div>
                                            <div class="fw-bold text-dark">{{ $baiGiang->phongHocLive->thoi_luong_phut }} phút</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted text-uppercase fw-bold smaller">Trạng thái phòng</div>
                                            <div class="fw-bold text-primary">{{ $baiGiang->phongHocLive->timeline_trang_thai_label }}</div>
                                        </div>
                                    </div>

                                    <div class="card border-0 bg-light mb-4">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">Cấu hình nâng cao</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6 small"><i class="fas fa-check-circle text-success me-2"></i>Cho phép Chat: {{ $baiGiang->phongHocLive->cho_phep_chat ? 'Bật' : 'Tắt' }}</div>
                                                <div class="col-md-6 small"><i class="fas fa-check-circle text-success me-2"></i>Thảo luận: {{ $baiGiang->phongHocLive->cho_phep_thao_luan ? 'Bật' : 'Tắt' }}</div>
                                                <div class="col-md-6 small"><i class="fas fa-check-circle text-success me-2"></i>Chia sẻ màn hình: {{ $baiGiang->phongHocLive->cho_phep_chia_se_man_hinh ? 'Bật' : 'Tắt' }}</div>
                                                <div class="col-md-6 small"><i class="fas fa-check-circle text-success me-2"></i>Tắt mic khi vào: {{ $baiGiang->phongHocLive->tat_mic_khi_vao ? 'Có' : 'Không' }}</div>
                                                <div class="col-md-6 small"><i class="fas fa-check-circle text-success me-2"></i>Tắt camera khi vào: {{ $baiGiang->phongHocLive->tat_camera_khi_vao ? 'Có' : 'Không' }}</div>
                                                <div class="col-md-6 small"><i class="fas fa-check-circle text-success me-2"></i>Cho phép ghi hình: {{ $baiGiang->phongHocLive->cho_phep_ghi_hinh ? 'Có' : 'Không' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <a href="{{ route('admin.live-room.show', $baiGiang->id) }}" class="btn btn-primary fw-bold px-4">
                                        <i class="fas fa-eye me-2"></i>Vao phong voi vai tro giam sat
                                    </a>

                                    @if($baiGiang->phongHocLive->banGhis->isNotEmpty())
                                        <div class="mt-4">
                                            <h6 class="fw-bold mb-3"><i class="fas fa-history me-2"></i>Bản ghi buổi học</h6>
                                            @foreach($baiGiang->phongHocLive->banGhis as $recording)
                                                <div class="card border shadow-xs mb-2">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="fw-bold text-dark">{{ $recording->tieu_de }}</div>
                                                                <div class="small text-muted">{{ $recording->nguon_ban_ghi }}</div>
                                                            </div>
                                                            <a href="#" class="btn btn-sm btn-outline-danger fw-bold">XEM LẠI</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4"><i class="fas fa-stamp me-2 text-primary"></i>Phê duyệt bài giảng</h5>
                            <form action="{{ route('admin.bai-giang.duyet', $baiGiang->id) }}" method="POST" class="d-grid gap-3">
                                @csrf
                                <div>
                                    <label class="form-label small fw-bold text-muted">Trạng thái phê duyệt</label>
                                    <select name="trang_thai_duyet" class="form-select fw-bold">
                                        <option value="da_duyet" @selected($baiGiang->trang_thai_duyet === 'da_duyet') class="text-success">Đã duyệt</option>
                                        <option value="can_chinh_sua" @selected($baiGiang->trang_thai_duyet === 'can_chinh_sua') class="text-info">Cần chỉnh sửa</option>
                                        <option value="tu_choi" @selected($baiGiang->trang_thai_duyet === 'tu_choi') class="text-danger">Từ chối</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label small fw-bold text-muted">Ghi chú / Phản hồi</label>
                                    <textarea name="ghi_chu_admin" rows="4" class="form-control" placeholder="Nhập lý do từ chối hoặc yêu cầu chỉnh sửa...">{{ $baiGiang->ghi_chu_admin }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success fw-bold py-2 shadow-sm">CẬP NHẬT PHÊ DUYỆT</button>
                            </form>

                            @if($baiGiang->trang_thai_duyet === 'da_duyet')
                                <hr class="my-4">
                                <form action="{{ route('admin.bai-giang.cong-bo', $baiGiang->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $baiGiang->trang_thai_cong_bo === 'da_cong_bo' ? 'warning text-dark' : 'primary' }} w-100 fw-bold py-2 shadow-sm">
                                        {{ $baiGiang->trang_thai_cong_bo === 'da_cong_bo' ? 'ẨN BÀI GIẢNG' : 'CÔNG BỐ BÀI GIẢNG' }}
                                    </button>
                                </form>
                            @endif

                            <div class="mt-3">
                                <a href="{{ route('admin.bai-giang.edit', $baiGiang->id) }}" class="btn btn-outline-dark w-100 fw-bold py-2">
                                    <i class="fas fa-edit me-2"></i>CHỈNH SỬA BÀI GIẢNG
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
