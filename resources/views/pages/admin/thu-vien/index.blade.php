@extends('layouts.app')

@section('title', 'Quản lý Thư viện tài nguyên')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0 fw-bold"><i class="fas fa-university me-2 text-primary"></i>Thư viện hệ thống</h2>
            <p class="text-muted mb-0">Duyệt và quản lý tài nguyên từ giảng viên</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.thu-vien.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label smaller fw-bold text-muted">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="nhap" {{ request('trang_thai_duyet') == 'nhap' ? 'selected' : '' }}>Nháp</option>
                        <option value="cho_duyet" {{ request('trang_thai_duyet') == 'cho_duyet' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="da_duyet" {{ request('trang_thai_duyet') == 'da_duyet' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="can_chinh_sua" {{ request('trang_thai_duyet') == 'can_chinh_sua' ? 'selected' : '' }}>Cần sửa</option>
                        <option value="tu_choi" {{ request('trang_thai_duyet') == 'tu_choi' ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label smaller fw-bold text-muted">Loại tài nguyên</label>
                    <select name="loai_tai_nguyen" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả loại</option>
                        <option value="video" {{ request('loai_tai_nguyen') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="pdf" {{ request('loai_tai_nguyen') == 'pdf' ? 'selected' : '' }}>PDF</option>
                        <option value="link_ngoai" {{ request('loai_tai_nguyen') == 'link_ngoai' ? 'selected' : '' }}>Link ngoài</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted smaller fw-bold">
                        <tr>
                            <th class="ps-4">Tài nguyên</th>
                            <th class="text-center">Người tạo</th>
                            <th class="text-center">Loại</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Ngày gửi</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taiNguyens as $tn)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-{{ $tn->loai_color }}-soft p-2 me-3 text-{{ $tn->loai_color }}">
                                            <i class="fas {{ $tn->loai_icon }} fa-lg"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $tn->tieu_de }}</div>
                                            <div class="smaller text-muted">
                                                @if($tn->is_external)
                                                    <i class="fas fa-link me-1"></i>Link ngoài
                                                @else
                                                    <i class="fas fa-file-download me-1"></i>{{ round($tn->file_size / 1024 / 1024, 2) }} MB
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center smaller">
                                    <div class="fw-bold text-dark">{{ $tn->nguoiTao->ho_ten ?? 'N/A' }}</div>
                                    <div class="text-muted">{{ $tn->vai_tro_nguoi_tao }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $tn->loai_color }}-soft text-{{ $tn->loai_color }} border border-{{ $tn->loai_color }}">
                                        {{ $tn->loai_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $duyetColor = match($tn->trang_thai_duyet) {
                                            'da_duyet' => 'success',
                                            'cho_duyet' => 'warning',
                                            'can_chinh_sua' => 'info',
                                            'tu_choi' => 'danger',
                                            default => 'secondary'
                                        };
                                        $duyetLabel = match($tn->trang_thai_duyet) {
                                            'da_duyet' => 'Đã duyệt',
                                            'cho_duyet' => 'Chờ duyệt',
                                            'can_chinh_sua' => 'Cần sửa',
                                            'tu_choi' => 'Từ chối',
                                            default => 'Nháp'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $duyetColor }}">{{ $duyetLabel }}</span>
                                </td>
                                <td class="text-center smaller text-muted">
                                    {{ $tn->ngay_gui_duyet ? $tn->ngay_gui_duyet->format('d/m/Y H:i') : '─' }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-xs">
                                        <a href="{{ route('admin.thu-vien.show', $tn->id) }}" class="btn btn-sm btn-outline-primary" title="Chi tiết & Duyệt">
                                            <i class="fas fa-search-plus me-1"></i> Xem
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="if(confirm('Bạn có chắc muốn xóa tài nguyên này khỏi hệ thống?')) document.getElementById('delete-form-{{ $tn->id }}').submit();"
                                                title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $tn->id }}" action="{{ route('admin.thu-vien.destroy', $tn->id) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <p class="mb-0">Không tìm thấy tài nguyên nào phù hợp.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($taiNguyens->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $taiNguyens->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .smaller { font-size: 0.75rem; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
</style>
@endsection
