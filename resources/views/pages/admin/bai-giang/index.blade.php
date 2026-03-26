@extends('layouts.app')

@section('title', 'Phê duyệt bài giảng')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0 fw-bold"><i class="fas fa-graduation-cap me-2 text-primary"></i>Phê duyệt bài giảng</h2>
            <p class="text-muted mb-0">Quản lý bài giảng thường và phòng học live.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.bai-giang.create') }}" class="btn btn-primary shadow-sm px-4 fw-bold">
                <i class="fas fa-plus me-2"></i>Tạo bài giảng
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.bai-giang.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label smaller fw-bold text-muted">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả</option>
                        <option value="cho_duyet" @selected(request('trang_thai_duyet') == 'cho_duyet')>Chờ duyệt</option>
                        <option value="da_duyet" @selected(request('trang_thai_duyet') == 'da_duyet')>Đã duyệt</option>
                        <option value="can_chinh_sua" @selected(request('trang_thai_duyet') == 'can_chinh_sua')>Cần chỉnh sửa</option>
                        <option value="tu_choi" @selected(request('trang_thai_duyet') == 'tu_choi')>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label smaller fw-bold text-muted">Loại bài giảng</label>
                    <select name="loai_bai_giang" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả</option>
                        <option value="live" @selected(request('loai_bai_giang') == 'live')>Trực tuyến (Live)</option>
                        <option value="tai_lieu" @selected(request('loai_bai_giang') == 'tai_lieu')>Tài liệu</option>
                        <option value="video" @selected(request('loai_bai_giang') == 'video')>Video</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
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
                                    <div class="smaller text-muted text-capitalize">{{ str_replace('_', ' ', $bg->loai_bai_giang) }}</div>
                                    @if($bg->isLive() && $bg->phongHocLive)
                                        <div class="smaller text-muted">{{ $bg->phongHocLive->platform_label }}</div>
                                    @endif
                                </td>
                                <td class="text-center smaller">{{ $bg->nguoiTao->ho_ten ?? 'N/A' }}</td>
                                <td class="text-center smaller">
                                    <div class="fw-bold text-dark">{{ $bg->moduleHoc->ten_module ?? 'N/A' }}</div>
                                    <div class="text-muted">{{ $bg->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusLabels = [
                                            'cho_duyet' => 'Chờ duyệt',
                                            'da_duyet' => 'Đã duyệt',
                                            'can_chinh_sua' => 'Cần chỉnh sửa',
                                            'tu_choi' => 'Từ chối'
                                        ];
                                        $statusColors = [
                                            'cho_duyet' => 'warning text-dark',
                                            'da_duyet' => 'success',
                                            'can_chinh_sua' => 'info',
                                            'tu_choi' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$bg->trang_thai_duyet] ?? 'secondary' }} shadow-xs px-2 py-1">
                                        {{ $statusLabels[$bg->trang_thai_duyet] ?? $bg->trang_thai_duyet }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'success' : 'secondary' }} shadow-xs px-2 py-1">
                                        {{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'Đã công bố' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-xs">
                                        <a href="{{ route('admin.bai-giang.show', $bg->id) }}" class="btn btn-sm btn-outline-primary fw-bold">XEM</a>
                                        <a href="{{ route('admin.bai-giang.edit', $bg->id) }}" class="btn btn-sm btn-outline-dark fw-bold">SỬA</a>
                                        @if($bg->trang_thai_duyet === 'da_duyet')
                                            <form action="{{ route('admin.bai-giang.cong-bo', $bg->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'warning' : 'success' }} fw-bold">
                                                    {{ $bg->trang_thai_cong_bo === 'da_cong_bo' ? 'ẨN' : 'CÔNG BỐ' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Không có bài giảng nào cần xử lý.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
