@extends('layouts.app')

@section('title', 'Chi tiết module: ' . $moduleHoc->ten_module)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.module-hoc.index') }}">Module học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $moduleHoc->ma_module }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <h3 class="fw-bold mb-0">{{ $moduleHoc->ten_module }}</h3>
                <span class="badge bg-{{ $moduleHoc->trang_thai ? 'success' : 'secondary' }} ms-3 px-3 shadow-xs">
                    {{ $moduleHoc->trang_thai ? 'Hoạt động' : 'Tạm dừng' }}
                </span>
                <span class="badge bg-{{ $moduleHoc->trang_thai_hoc_tap_badge }}-soft text-{{ $moduleHoc->trang_thai_hoc_tap_badge }} border border-{{ $moduleHoc->trang_thai_hoc_tap_badge }} ms-2 px-3 shadow-xs">
                    {{ $moduleHoc->trang_thai_hoc_tap_label }}
                </span>
            </div>
            <div class="mt-2 text-muted">
                Mã module: <code class="fw-bold text-primary">{{ $moduleHoc->ma_module }}</code> 
                <span class="mx-2 text-muted opacity-50">|</span>
                Thứ tự: <span class="fw-bold text-dark">#{{ $moduleHoc->thu_tu_module }}</span>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.module-hoc.edit', $moduleHoc->id) }}" class="btn btn-warning text-white fw-bold shadow-sm">
                <i class="fas fa-edit me-1"></i> Sửa module
            </a>
            <form action="{{ route('admin.module-hoc.destroy', $moduleHoc->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Xóa module này?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger fw-bold shadow-sm">
                    <i class="fas fa-trash me-1"></i> Xóa
                </button>
            </form>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <!-- Cột trái: Thông tin Module & Phân công -->
        <div class="col-lg-8">
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-primary">
                        <i class="fas fa-info-circle me-2"></i> Nội dung bài học
                    </h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light">
                                <span class="smaller text-muted fw-bold d-block text-uppercase">Thời lượng dự kiến</span>
                                <span class="fs-5 fw-bold text-primary">
                                    @if($moduleHoc->thoi_luong_du_kien)
                                        @php
                                            $hours = floor($moduleHoc->thoi_luong_du_kien / 60);
                                            $mins = $moduleHoc->thoi_luong_du_kien % 60;
                                        @endphp
                                        <i class="far fa-clock me-1"></i> {{ $hours > 0 ? $hours.' giờ ' : '' }}{{ $mins.' phút' }}
                                    @else — @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light">
                                <span class="smaller text-muted fw-bold d-block text-uppercase">Ngày khởi tạo</span>
                                <span class="fs-5 fw-bold text-dark"><i class="far fa-calendar-alt me-1"></i> {{ $moduleHoc->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <span class="smaller text-muted fw-bold d-block text-uppercase">Buổi hợp lệ</span>
                                <span class="fs-4 fw-bold text-dark">{{ $moduleHoc->so_buoi_hop_le }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <span class="smaller text-muted fw-bold d-block text-uppercase">Đã hoàn thành</span>
                                <span class="fs-4 fw-bold text-success">{{ $moduleHoc->so_buoi_hoan_thanh }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <span class="smaller text-muted fw-bold d-block text-uppercase">Còn lại</span>
                                <span class="fs-4 fw-bold text-primary">{{ $moduleHoc->so_buoi_chua_hoan_thanh }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <span class="smaller text-muted fw-bold d-block text-uppercase">Tiến độ</span>
                                <span class="fs-4 fw-bold text-info">{{ $moduleHoc->tien_do_hoc_tap }}%</span>
                            </div>
                        </div>
                    </div>

                    <h6 class="smaller fw-bold text-muted text-uppercase mb-2">Mô tả chi tiết</h6>
                    <div class="text-dark lh-lg bg-light p-3 rounded border border-dashed mb-0">
                        {!! $moduleHoc->mo_ta ? nl2br(e($moduleHoc->mo_ta)) : '<span class="text-muted italic">Chưa có mô tả chi tiết.</span>' !!}
                    </div>
                </div>
            </div>

            {{-- SECTION: GIẢNG VIÊN PHỤ TRÁCH --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-success">
                        <i class="fas fa-user-tie me-2"></i> Phân công giảng viên
                    </h5>
                </div>
                <div class="vip-card-body p-4">
                    {{-- Form phân công --}}
                    <form action="{{ route('admin.module-hoc.assign', $moduleHoc->id) }}" method="POST" class="mb-4 bg-light p-3 rounded border shadow-xs">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark">Chọn giảng viên</label>
                                <select name="giang_vien_id" class="form-select vip-form-control shadow-sm" required>
                                    <option value="">-- Chọn giảng viên --</option>
                                    @foreach($giangViens as $gv)
                                        <option value="{{ $gv->id }}">
                                            {{ $gv->nguoiDung->ho_ten }} ({{ $gv->chuyen_nganh ?: 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark">Ghi chú (tùy chọn)</label>
                                <input type="text" name="ghi_chu" class="form-control vip-form-control shadow-sm" placeholder="VD: Dạy online, cần chuẩn bị tài liệu...">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                    <i class="fas fa-plus me-1"></i> Gán GV
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Bảng danh sách đã gán --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border mb-0">
                            <thead class="bg-light smaller text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">Giảng viên</th>
                                    <th class="text-center">Học vị</th>
                                    <th class="text-center">Ngày gán</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="pe-3 text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($moduleHoc->phanCongGiangViens as $pc)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $pc->giangVien->nguoiDung->ho_ten }}</div>
                                            <div class="smaller text-muted">{{ $pc->giangVien->chuyen_nganh }}</div>
                                        </td>
                                        <td class="text-center small">{{ $pc->giangVien->hoc_vi ?: '—' }}</td>
                                        <td class="text-center text-muted small">{{ $pc->ngay_phan_cong?->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            @php
                                                $status = $pc->trang_thai_label ?? [
                                                    'label' => 'Không xác định',
                                                    'color' => 'secondary',
                                                    'icon' => 'fa-question-circle',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $status['color'] }} shadow-xs smaller">
                                                <i class="fas {{ $status['icon'] }} me-1"></i> {{ $status['label'] }}
                                            </span>
                                        </td>
                                        <td class="pe-3 text-center">
                                            @if($pc->trang_thai === 'cho_xac_nhan')
                                                <form action="{{ route('admin.phan-cong.huy', $pc->id) }}" method="POST" onsubmit="return confirm('Hủy yêu cầu này?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-undo"></i> Hủy</button>
                                                </form>
                                            @else — @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small italic">Chưa có giảng viên nào được gán cho module này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SECTION: CÁC MODULE CÒN LẠI CỦA KHÓA HỌC (THEO YÊU CẦU) --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-dark">
                        <i class="fas fa-layer-group me-2"></i> Lộ trình bài học khác trong khóa
                    </h5>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="ps-4 text-center" width="60">STT</th>
                                    <th>Tên bài học (Module)</th>
                                    <th class="text-center">Tiến độ</th>
                                    <th class="text-center">Thời lượng</th>
                                    <th class="pe-4 text-center" width="120">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cacModuleKhac as $item)
                                    <tr>
                                        <td class="text-center ps-4 text-muted">#{{ $item->thu_tu_module }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->ten_module }}</div>
                                            <code class="smaller text-muted">{{ $item->ma_module }}</code>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <span class="badge bg-{{ $item->trang_thai_hoc_tap_badge }}-soft text-{{ $item->trang_thai_hoc_tap_badge }} border border-{{ $item->trang_thai_hoc_tap_badge }}">
                                                    {{ $item->trang_thai_hoc_tap_label }}
                                                </span>
                                                <span class="smaller text-muted">{{ $item->so_buoi_hoan_thanh }}/{{ $item->so_buoi_hop_le }} buổi</span>
                                            </div>
                                        </td>
                                        <td class="text-center text-muted">{{ $item->thoi_luong_du_kien }}p</td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('admin.module-hoc.show', $item->id) }}" class="btn btn-xs btn-outline-primary shadow-xs" title="Xem chi tiết">
                                                    <i class="fas fa-arrow-right"></i>
                                                </a>
                                                <a href="{{ route('admin.module-hoc.edit', $item->id) }}" class="btn btn-xs btn-outline-warning shadow-xs" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted italic">Khóa học này chỉ có duy nhất 1 module.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông tin Khóa học -->
        <div class="col-lg-4">
            <div class="vip-card mb-4 shadow-sm border-0 overflow-hidden">
                <div class="vip-card-header bg-primary text-white py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Khóa học sở hữu</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="text-center mb-3">
                        <div class="rounded border bg-light overflow-hidden mx-auto shadow-xs" style="width: 120px; height: 120px;">
                            <img src="{{ $moduleHoc->khoaHoc->hinh_anh ? asset($moduleHoc->khoaHoc->hinh_anh) : asset('images/default-course.svg') }}" 
                                 class="img-fluid w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="smaller text-muted fw-bold d-block text-uppercase">Tên khóa học</span>
                        <a href="{{ route('admin.khoa-hoc.show', $moduleHoc->khoa_hoc_id) }}" class="fw-bold text-primary text-decoration-none">
                            {{ $moduleHoc->khoaHoc->ten_khoa_hoc }}
                        </a>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <span class="smaller text-muted d-block text-uppercase">Mã KH</span>
                            <span class="small fw-bold text-dark">{{ $moduleHoc->khoaHoc->ma_khoa_hoc }}</span>
                        </div>
                        <div class="col-6">
                            <span class="smaller text-muted d-block text-uppercase">Cấp độ</span>
                            <span class="badge bg-info-soft text-info border border-info smaller">
                                {{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$moduleHoc->khoaHoc->cap_do] ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-3 rounded border bg-light mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="smaller text-muted text-uppercase fw-bold">Tiến độ khóa học</span>
                            <span class="badge bg-{{ $moduleHoc->khoaHoc->trang_thai_hoc_tap_badge }}-soft text-{{ $moduleHoc->khoaHoc->trang_thai_hoc_tap_badge }} border border-{{ $moduleHoc->khoaHoc->trang_thai_hoc_tap_badge }}">
                                {{ $moduleHoc->khoaHoc->trang_thai_hoc_tap_label }}
                            </span>
                        </div>
                        <div class="fw-bold text-dark">{{ $moduleHoc->khoaHoc->so_module_hoan_thanh }}/{{ $moduleHoc->khoaHoc->moduleHocs->count() }} module hoàn thành</div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $moduleHoc->khoaHoc->tien_do_hoc_tap }}%"></div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <span class="smaller text-muted d-block text-uppercase">Nhóm ngành</span>
                        <span class="small fw-bold text-dark">{{ $moduleHoc->khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}</span>
                    </div>
                    <hr class="my-3">
                    <div class="d-grid">
                        <a href="{{ route('admin.khoa-hoc.show', $moduleHoc->khoa_hoc_id) }}" class="btn btn-outline-primary btn-sm fw-bold shadow-xs">
                            <i class="fas fa-external-link-alt me-1"></i> Xem chi tiết khóa học
                        </a>
                    </div>
                </div>
            </div>

            <div class="vip-card shadow-sm border-0 bg-light">
                <div class="vip-card-body p-3 smaller">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ID Module:</span>
                        <span class="fw-bold">#{{ $moduleHoc->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Cập nhật cuối:</span>
                        <span class="fw-bold text-dark">{{ $moduleHoc->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .border-dashed { border-style: dashed !important; }
    .object-fit-cover { object-fit: cover; }
    .italic { font-style: italic; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .btn-xs { padding: 0.2rem 0.4rem; font-size: 0.75rem; border-radius: 4px; }
</style>
@endsection

