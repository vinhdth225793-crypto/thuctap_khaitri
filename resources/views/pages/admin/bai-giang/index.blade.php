@extends('layouts.app')

@section('title', 'Phê duyệt Bài giảng')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0 fw-bold"><i class="fas fa-graduation-cap me-2 text-primary"></i>Phê duyệt Bài giảng</h2>
            <p class="text-muted mb-0">Quản lý nội dung học tập toàn hệ thống</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.bai-giang.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label smaller fw-bold text-muted">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="cho_duyet" {{ request('trang_thai_duyet') == 'cho_duyet' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="da_duyet" {{ request('trang_thai_duyet') == 'da_duyet' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="can_chinh_sua" {{ request('trang_thai_duyet') == 'can_chinh_sua' ? 'selected' : '' }}>Cần sửa</option>
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
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted smaller fw-bold">
                        <tr>
                            <th class="ps-4">Bài giảng</th>
                            <th class="text-center">Người tạo</th>
                            <th class="text-center">Khóa học / Module</th>
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
                                    <div class="smaller text-muted">{{ $bg->loai_bai_giang }}</div>
                                </td>
                                <td class="text-center smaller">
                                    <div class="fw-bold text-dark">{{ $bg->nguoiTao->ho_ten ?? 'N/A' }}</div>
                                </td>
                                <td class="text-center smaller">
                                    <div class="fw-bold text-dark">{{ $bg->moduleHoc->ten_module ?? 'N/A' }}</div>
                                    <div class="text-muted">{{ $bg->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</div>
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
                                        <span class="badge bg-success">Công bố</span>
                                    @else
                                        <span class="badge bg-secondary">Ẩn</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-xs">
                                        <a href="{{ route('admin.bai-giang.show', $bg->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-search-plus me-1"></i> Xem & Duyệt
                                        </a>
                                        @if($bg->trang_thai_duyet === 'da_duyet')
                                            <form action="{{ route('admin.bai-giang.cong-bo', $bg->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'warning' : 'success' }}">
                                                    {{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'Ẩn' : 'Công bố' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Không có bài giảng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
