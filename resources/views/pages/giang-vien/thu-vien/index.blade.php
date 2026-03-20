@extends('layouts.app')

@section('title', 'Thư viện tài nguyên của tôi')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0 fw-bold"><i class="fas fa-book-open me-2 text-primary"></i>Thư viện tài nguyên</h2>
            <p class="text-muted mb-0">Quản lý kho tài nguyên cá nhân của bạn</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('giang-vien.thu-vien.create') }}" class="btn btn-primary shadow-sm px-4 fw-bold">
                <i class="fas fa-plus me-2"></i>Thêm tài nguyên mới
            </a>
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
                            <th class="text-center">Loại</th>
                            <th class="text-center">Trạng thái duyệt</th>
                            <th class="text-center">Trạng thái xử lý</th>
                            <th class="text-center">Ngày tạo</th>
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
                                                    <i class="fas fa-file-download me-1"></i>{{ $tn->file_name }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
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
                                    @if($tn->ghi_chu_admin)
                                        <div class="smaller text-danger mt-1" title="{{ $tn->ghi_chu_admin }}">
                                            <i class="fas fa-info-circle me-1"></i>Có phản hồi
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($tn->loai_tai_nguyen === 'video')
                                        @php
                                            $xuLyColor = match($tn->trang_thai_xu_ly) {
                                                'san_sang' => 'success',
                                                'dang_xu_ly' => 'primary',
                                                'loi_xu_ly' => 'danger',
                                                default => 'secondary'
                                            };
                                            $xuLyLabel = match($tn->trang_thai_xu_ly) {
                                                'san_sang' => 'Sẵn sàng',
                                                'dang_xu_ly' => 'Đang xử lý',
                                                'loi_xu_ly' => 'Lỗi',
                                                default => 'Chờ xử lý'
                                            };
                                        @endphp
                                        <span class="badge rounded-pill border border-{{ $xuLyColor }} text-{{ $xuLyColor }}">{{ $xuLyLabel }}</span>
                                    @else
                                        <span class="text-muted smaller">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center smaller text-muted">
                                    {{ $tn->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-xs">
                                        <a href="{{ $tn->file_url }}" target="_blank" class="btn btn-sm btn-outline-info" title="Xem trước">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('giang-vien.thu-vien.edit', $tn->id) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($tn->trang_thai_duyet === 'nhap' || $tn->trang_thai_duyet === 'can_chinh_sua')
                                            <form action="{{ route('giang-vien.thu-vien.gui-duyet', $tn->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Gửi duyệt">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="if(confirm('Bạn có chắc muốn xóa tài nguyên này?')) document.getElementById('delete-form-{{ $tn->id }}').submit();"
                                                title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $tn->id }}" action="{{ route('giang-vien.thu-vien.destroy', $tn->id) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Thư viện của bạn chưa có tài nguyên nào.</p>
                                    <a href="{{ route('giang-vien.thu-vien.create') }}" class="btn btn-primary mt-3 btn-sm">Bắt đầu tải lên ngay</a>
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
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .text-primary { color: #0d6efd !important; }
    .text-success { color: #198754 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-info { color: #0dcaf0 !important; }
    .text-danger { color: #dc3545 !important; }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
</style>
@endsection
