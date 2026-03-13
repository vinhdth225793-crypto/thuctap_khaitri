@extends('layouts.app')

@section('title', 'Quản lý khóa học')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <i class="fas fa-home me-1"></i> Admin > Quản lý Khóa học
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0">Quản lý khóa học</h3>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.khoa-hoc.create', ['loai'=>'mau']) }}" class="btn btn-outline-info me-2 fw-bold">
                <i class="fas fa-copy me-1"></i> Tạo khóa học mẫu
            </a>
            <a href="{{ route('admin.khoa-hoc.create', ['loai'=>'truc_tiep']) }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="fas fa-bolt me-1"></i> Tạo khóa học trực tiếp
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 small fw-bold">Khóa học mẫu</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['tong_mau'] }}</h3>
                        </div>
                        <i class="fas fa-copy fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 small fw-bold">Chờ mở</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['cho_mo'] }}</h3>
                        </div>
                        <i class="fas fa-pause fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 small fw-bold">Đang hoạt động</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['hoat_dong'] }}</h3>
                        </div>
                        <i class="fas fa-play fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 small fw-bold">Sẵn sàng khai giảng</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['san_sang'] }}</h3>
                        </div>
                        <i class="fas fa-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.khoa-hoc.index') }}" class="d-flex gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="input-group" style="max-width: 400px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 vip-form-control" 
                           placeholder="Tìm theo tên, mã khóa học..." value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-secondary px-4 fw-bold">Tìm kiếm</button>
                @if($search)
                    <a href="{{ route('admin.khoa-hoc.index', ['tab'=>$tab]) }}" class="btn btn-outline-secondary px-3">Xóa lọc</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs border-bottom-0 mb-0" id="khoaHocTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab==='mau' ? 'active fw-bold border-bottom-0' : 'text-muted' }}" 
               href="{{ request()->fullUrlWithQuery(['tab'=>'mau','mau_page'=>1]) }}" style="{{ $tab==='mau' ? 'border-top: 3px solid #0dcaf0 !important;' : '' }}">
                <i class="fas fa-copy me-1"></i> Khóa học đã tạo sẵn
                <span class="badge bg-info ms-1">{{ $stats['tong_mau'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab==='hoat_dong' ? 'active fw-bold border-bottom-0' : 'text-muted' }}" 
               href="{{ request()->fullUrlWithQuery(['tab'=>'hoat_dong','hd_page'=>1]) }}" style="{{ $tab==='hoat_dong' ? 'border-top: 3px solid #0d6efd !important;' : '' }}">
                <i class="fas fa-play me-1"></i> Đang hoạt động
                <span class="badge bg-primary ms-1">{{ $stats['hoat_dong'] }}</span>
            </a>
        </li>
    </ul>

    <div class="vip-card border-top-0 shadow-sm" style="border-top-left-radius: 0;">
        <div class="vip-card-body p-0">
            @if($tab === 'mau')
                <!-- Tab Khóa học mẫu -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="smaller">
                                <th class="ps-4 text-center" width="60">STT</th>
                                <th width="120">Mã KH</th>
                                <th>Tên khóa học mẫu</th>
                                <th>Môn học</th>
                                <th class="text-center">Cấp độ</th>
                                <th class="text-center">Modules</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Ngày tạo</th>
                                <th class="pe-4 text-center" width="220">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($khoaHocMau as $kh)
                                <tr>
                                    <td class="text-center ps-4 text-muted small">{{ ($khoaHocMau->currentPage() - 1) * $khoaHocMau->perPage() + $loop->iteration }}</td>
                                    <td><code class="fw-bold text-primary">{{ $kh->ma_khoa_hoc }}</code></td>
                                    <td><span class="fw-bold text-dark">{{ $kh->ten_khoa_hoc }}</span></td>
                                    <td><small class="fw-bold text-info">{{ $kh->monHoc->ten_mon_hoc ?? 'N/A' }}</small></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border smaller">{{ ucfirst($kh->cap_do) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill px-2">{{ $kh->tong_so_module }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $kh->trang_thai_van_hanh_label['color'] }} px-2">
                                            <i class="fas {{ $kh->trang_thai_van_hanh_label['icon'] }} me-1 small"></i>
                                            {{ $kh->trang_thai_van_hanh_label['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-center small text-muted">{{ $kh->created_at->format('d/m/Y') }}</td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}" class="btn btn-sm btn-info text-white shadow-sm" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($kh->trang_thai_van_hanh === 'cho_mo')
                                                <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}#section-kich-hoat" class="btn btn-sm btn-success shadow-sm" title="Tạo lớp học từ template này">
                                                    <i class="fas fa-rocket"></i>
                                                </a>
                                                <a href="{{ route('admin.khoa-hoc.edit', $kh->id) }}" class="btn btn-sm btn-warning text-white" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" title="Xóa" onclick="confirmDelete({{ $kh->id }}, '{{ $kh->ten_khoa_hoc }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i> Chưa có khóa học mẫu nào.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $khoaHocMau->links('pagination::bootstrap-5') }}
                </div>
            @else
                <!-- Tab Đang hoạt động -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="smaller">
                                <th class="ps-4 text-center" width="60">STT</th>
                                <th width="120">Mã KH</th>
                                <th>Tên lớp học</th>
                                <th>Môn học</th>
                                <th class="text-center">Loại</th>
                                <th class="text-center">GV xác nhận</th>
                                <th class="text-center">Ngày khai giảng</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4 text-center" width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($khoaHocHoatDong as $kh)
                                <tr>
                                    <td class="text-center ps-4 text-muted small">{{ ($khoaHocHoatDong->currentPage() - 1) * $khoaHocHoatDong->perPage() + $loop->iteration }}</td>
                                    <td><code class="fw-bold text-primary">{{ $kh->ma_khoa_hoc }}</code></td>
                                    <td><span class="fw-bold text-dark">{{ $kh->ten_khoa_hoc }}</span></td>
                                    <td><small class="fw-bold text-info">{{ $kh->monHoc->ten_mon_hoc ?? 'N/A' }}</small></td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $kh->loai_label['color'] }} smaller">{{ $kh->loai_label['label'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php $t = $kh->tong_module ?? 0; $d = $kh->module_da_nhan ?? 0; @endphp
                                        <span class="badge bg-{{ $d >= $t && $t > 0 ? 'success' : 'warning' }} shadow-sm px-2">
                                            {{ $d }}/{{ $t }} module
                                        </span>
                                    </td>
                                    <td class="text-center small fw-bold text-dark">
                                        {{ $kh->ngay_khai_giang ? $kh->ngay_khai_giang->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $kh->trang_thai_van_hanh_label['color'] }} px-2">
                                            <i class="fas {{ $kh->trang_thai_van_hanh_label['icon'] }} me-1 small"></i>
                                            {{ $kh->trang_thai_van_hanh_label['label'] }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}" class="btn btn-sm btn-info text-white shadow-sm" title="Xem chi tiết & Quản lý">
                                            <i class="fas fa-cog"></i> Quản lý
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-play-circle fa-2x mb-2 d-block opacity-25"></i> Hiện không có lớp học nào đang hoạt động.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $khoaHocHoatDong->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>Bạn có chắc chắn muốn xóa khóa học <strong id="deleteCourseName" class="text-danger"></strong> không?</p>
                <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> Lưu ý: Mọi module và dữ liệu liên quan sẽ bị xóa vĩnh viễn.</p>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 text-white fw-bold shadow-sm">Đồng ý Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteCourseName').textContent = name;
        document.getElementById('deleteForm').action = `/admin/khoa-hoc/${id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<style>
    .smaller { font-size: 0.75rem; }
    .nav-tabs .nav-link { color: #6c757d; border: 1px solid transparent; border-bottom: 1px solid #dee2e6; transition: all 0.2s; }
    .nav-tabs .nav-link.active { background-color: #fff; border-color: #dee2e6 #dee2e6 #fff; color: #333; }
    .nav-tabs .nav-link:hover:not(.active) { background-color: #f8f9fa; border-color: #eee #eee #dee2e6; }
</style>
@endsection
