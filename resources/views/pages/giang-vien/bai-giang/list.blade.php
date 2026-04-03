@extends('layouts.app')

@section('title', 'Quản lý Bài giảng')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0 fw-bold"><i class="fas fa-chalkboard me-2 text-primary"></i>Quản lý Bài giảng</h2>
            <p class="text-muted mb-0">Thiết kế bài giảng cho khóa học của bạn</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('giang-vien.bai-giang.create') }}" class="btn btn-primary shadow-sm px-4 fw-bold">
                <i class="fas fa-plus me-2"></i>Tạo bài giảng mới
            </a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted smaller fw-bold">
                        <tr>
                            <th class="ps-4">Bài giảng</th>
                            <th class="text-center">Khóa học / Module</th>
                            <th class="text-center">Loại</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Công bố</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($baiGiangs as $bg)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $bg->tieu_de }}</div>
                                    <div class="smaller text-muted">
                                        @if($bg->lichHoc)
                                            <i class="fas fa-calendar-day me-1"></i>Buổi {{ $bg->lichHoc->buoi_so }} ({{ $bg->lichHoc->ngay_hoc?->format('d/m/Y') }})
                                        @else
                                            <i class="fas fa-layer-group me-1"></i>Chung cho Module
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center smaller">
                                    <div class="fw-bold text-dark">{{ $bg->moduleHoc->ten_module ?? 'N/A' }}</div>
                                    <div class="text-muted">{{ $bg->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $bg->loai_bai_giang }}</span>
                                    @if($bg->isLive() && $bg->phongHocLive)
                                        <div class="small text-muted mt-1">{{ $bg->phongHocLive->platform_label }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $duyetColor = match($bg->trang_thai_duyet) {
                                            'da_duyet' => 'success',
                                            'cho_duyet' => 'warning',
                                            'can_chinh_sua' => 'info',
                                            'tu_choi' => 'danger',
                                            default => 'secondary'
                                        };
                                        $duyetLabel = match($bg->trang_thai_duyet) {
                                            'da_duyet' => 'Đã duyệt',
                                            'cho_duyet' => 'Chờ duyệt',
                                            'can_chinh_sua' => 'Cần sửa',
                                            'tu_choi' => 'Từ chối',
                                            default => 'Nháp'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $duyetColor }}">{{ $duyetLabel }}</span>
                                </td>
                                <td class="text-center">
                                    @if($bg->trang_thai_cong_bo === 'da_cong_bo')
                                        <span class="badge bg-success"><i class="fas fa-eye me-1"></i>Đã công bố</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-eye-slash me-1"></i>Ẩn</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-xs">
                                        <a href="{{ route('giang-vien.bai-giang.edit', $bg->id) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($bg->isLive() && $bg->phongHocLive)
                                            <a href="{{ route('giang-vien.live-room.show', $bg->id) }}" class="btn btn-sm btn-outline-dark" title="Phong hoc live">
                                                <i class="fas fa-video"></i>
                                            </a>
                                        @endif
                                        @if($bg->trang_thai_duyet === 'nhap' || $bg->trang_thai_duyet === 'can_chinh_sua')
                                            <form action="{{ route('giang-vien.bai-giang.gui-duyet', $bg->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Gửi duyệt">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="if(confirm('Bạn có chắc muốn xóa bài giảng này?')) document.getElementById('delete-form-{{ $bg->id }}').submit();"
                                                title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $bg->id }}" action="{{ route('giang-vien.bai-giang.destroy', $bg->id) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-chalkboard fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Bạn chưa có bài giảng nào.</p>
                                    <a href="{{ route('giang-vien.bai-giang.create') }}" class="btn btn-primary mt-3 btn-sm">Bắt đầu tạo ngay</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($baiGiangs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $baiGiangs->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .smaller { font-size: 0.75rem; }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
</style>
@endsection
