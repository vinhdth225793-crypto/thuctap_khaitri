@extends('layouts.app')

@section('title', 'Chi tiết khóa học: ' . $khoaHoc->ma_khoa_hoc)

@section('content')
@php
    $capDo = [
        'co_ban' => ['text' => 'Cơ bản', 'class' => 'success'],
        'trung_binh' => ['text' => 'Trung bình', 'class' => 'warning text-dark'],
        'nang_cao' => ['text' => 'Nâng cao', 'class' => 'danger'],
    ];
    $cd = $capDo[$khoaHoc->cap_do] ?? ['text' => 'N/A', 'class' => 'secondary'];
@endphp
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $khoaHoc->ma_khoa_hoc }}</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-0 me-3">{{ $khoaHoc->ten_khoa_hoc }}</h3>
                    <span class="badge bg-{{ $khoaHoc->loai_label['color'] }} me-2 px-3">{{ $khoaHoc->loai_label['label'] }}</span>
                    <span class="badge bg-{{ $khoaHoc->trang_thai_van_hanh_label['color'] }} px-3">
                        <i class="fas {{ $khoaHoc->trang_thai_van_hanh_label['icon'] }} me-1"></i>
                        {{ $khoaHoc->trang_thai_van_hanh_label['label'] }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    @if($khoaHoc->trang_thai_van_hanh === 'cho_mo')
                        <a href="{{ route('admin.khoa-hoc.edit', $khoaHoc->id) }}" class="btn btn-warning text-white fw-bold shadow-sm">
                            <i class="fas fa-edit me-1"></i> Sửa
                        </a>
                        <form action="{{ route('admin.khoa-hoc.destroy', $khoaHoc->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa khóa học này?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger fw-bold shadow-sm">
                                <i class="fas fa-trash me-1"></i> Xóa
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.khoa-hoc.index', ['tab' => $khoaHoc->loai === 'mau' ? 'mau' : 'hoat_dong']) }}" class="btn btn-outline-secondary fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Cột trái: Thông tin & Nội dung -->
        <div class="col-lg-8">
            <div class="vip-card mb-4">
                <div class="vip-card-header">
                    <h5 class="vip-card-title fw-bold">Thông tin chi tiết</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="mb-4">
                        <label class="small text-muted fw-bold d-block text-uppercase mb-1">Mô tả ngắn</label>
                        <p class="text-dark fw-bold border-start border-primary border-4 ps-3 py-1 bg-light">
                            {{ $khoaHoc->mo_ta_ngan ?: 'Chưa có mô tả ngắn' }}
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="small text-muted fw-bold d-block text-uppercase mb-2">Nội dung chương trình</label>
                        <div class="p-3 bg-light rounded border min-vh-20">
                            {!! nl2br(e($khoaHoc->mo_ta_chi_tiet)) ?: '<em class="text-muted small">Nội dung chi tiết đang được cập nhật...</em>' !!}
                        </div>
                    </div>

                    @if($khoaHoc->ghi_chu_noi_bo)
                        <div class="alert alert-light border shadow-sm">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2"><i class="fas fa-sticky-note me-1"></i> Ghi chú nội bộ (Chỉ Admin thấy)</h6>
                            <p class="mb-0 small">{{ $khoaHoc->ghi_chu_noi_bo }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Bar (Hiện khi đã kích hoạt hoặc trực tiếp) -->
            @if($khoaHoc->trang_thai_van_hanh !== 'cho_mo')
                <div class="vip-card mb-4 border-0 shadow-sm">
                    <div class="vip-card-body p-4 text-center">
                        <h6 class="fw-bold mb-3">Tình trạng tiếp nhận giảng dạy</h6>
                        <div class="progress mb-2" style="height: 25px; border-radius: 50px;">
                            @php $percent = $tongModule > 0 ? ($moduleCoGv / $tongModule * 100) : 0; @endphp
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $percent >= 100 ? 'success' : 'warning' }}" 
                                 role="progressbar" style="width: {{ $percent }}%" 
                                 aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                {{ round($percent) }}%
                            </div>
                        </div>
                        <p class="mb-0 small fw-bold text-{{ $percent >= 100 ? 'success' : 'muted' }}">
                            {{ $moduleCoGv }}/{{ $tongModule }} module đã có giảng viên xác nhận dạy.
                            @if($percent >= 100) <i class="fas fa-check-circle ms-1"></i> Tất cả giảng viên đã xác nhận! @endif
                        </p>
                    </div>
                </div>
            @endif

            <!-- Bảng danh sách Modules -->
            <div class="vip-card mb-4">
                <div class="vip-card-header d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title fw-bold mb-0">Chương trình học ({{ $tongModule }} modules)</h5>
                    <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $khoaHoc->id]) }}" class="btn btn-primary btn-sm px-3 fw-bold">
                        <i class="fas fa-plus me-1"></i> Thêm module
                    </a>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="text-center" width="50">#</th>
                                    <th>Tên Module</th>
                                    <th class="text-center">Thời lượng</th>
                                    <th>Giảng viên phụ trách</th>
                                    <th class="text-center">Trạng thái PC</th>
                                    <th class="text-center" width="80">Xem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($khoaHoc->moduleHocs as $index => $module)
                                    @php $pc = $module->phanCongGiangViens->first(); @endphp
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $module->ten_module }}</div>
                                            <code class="smaller">{{ $module->ma_module }}</code>
                                        </td>
                                        <td class="text-center small">
                                            @if($module->thoi_luong_du_kien)
                                                @php $h = intdiv($module->thoi_luong_du_kien, 60); $m = $module->thoi_luong_du_kien % 60; @endphp
                                                <span class="fw-bold">{{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}</span>
                                            @else — @endif
                                        </td>
                                        <td>
                                            @if($pc)
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset('images/default-avatar.svg') }}" class="rounded-circle me-2" width="24">
                                                    <span class="small fw-bold">{{ $pc->giangVien->nguoiDung->ho_ten ?? 'N/A' }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted small italic">Chưa phân công</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($pc)
                                                @php 
                                                    $st = [
                                                        'da_nhan' => ['c' => 'success', 't' => 'Đã xác nhận'],
                                                        'cho_xac_nhan' => ['c' => 'warning text-dark', 't' => 'Chờ XN'],
                                                        'tu_choi' => ['c' => 'danger', 't' => 'Từ chối']
                                                    ][$pc->trang_thai] ?? ['c' => 'secondary', 't' => $pc->trang_thai];
                                                @endphp
                                                <span class="badge bg-{{ $st['c'] }} smaller px-2 shadow-sm">{{ $st['t'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="btn btn-sm btn-outline-info p-1 px-2"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted italic small">Chưa có module nào được thiết lập.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Sidebar thông tin & Kích hoạt -->
        <div class="col-lg-4">
            <div class="vip-card mb-4 border-primary border-top border-4 shadow-sm">
                <div class="vip-card-body p-4">
                    <div class="text-center mb-4">
                        @if($khoaHoc->hinh_anh)
                            <img src="{{ asset($khoaHoc->hinh_anh) }}" class="img-fluid rounded shadow border mb-3 w-100" style="max-height: 180px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded p-5 mb-3 border text-muted">
                                <i class="fas fa-graduation-cap fa-4x opacity-25"></i>
                            </div>
                        @endif
                    </div>

                    <div class="list-group list-group-flush border rounded overflow-hidden shadow-sm small">
                        <div class="list-group-item d-flex justify-content-between p-3">
                            <span class="text-muted fw-bold">Mã khóa học:</span>
                            <span class="fw-bold text-primary">{{ $khoaHoc->ma_khoa_hoc }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between p-3">
                            <span class="text-muted fw-bold">Môn học:</span>
                            <span class="fw-bold text-info">{{ $khoaHoc->monHoc->ten_mon_hoc }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between p-3">
                            <span class="text-muted fw-bold">Cấp độ:</span>
                            <span class="badge bg-{{ $cd['class'] }}">{{ $cd['text'] }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between p-3">
                            <span class="text-muted fw-bold">Ngày khai giảng:</span>
                            <span class="fw-bold">{{ $khoaHoc->ngay_khai_giang ? $khoaHoc->ngay_khai_giang->format('d/m/Y') : '—' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between p-3">
                            <span class="text-muted fw-bold">Ngày kết thúc:</span>
                            <span class="fw-bold">{{ $khoaHoc->ngay_ket_thuc_du_kien ? $khoaHoc->ngay_ket_thuc_du_kien->format('d/m/Y') : '—' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between p-3 bg-light border-bottom-0">
                            <span class="text-muted fw-bold">Ngày tạo:</span>
                            <span class="text-muted">{{ $khoaHoc->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION KÍCH HOẠT (Chỉ cho khóa mẫu đang chờ mở) -->
            @if($khoaHoc->loai === 'mau' && $khoaHoc->trang_thai_van_hanh === 'cho_mo')
                <div class="card border-success shadow-sm mb-4" id="section-kich-hoat">
                    <div class="card-header bg-success text-white py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-rocket me-2"></i> KÍCH HOẠT LỚP HỌC THỰC TẾ</h6>
                    </div>
                    <div class="card-body p-4">
                        @if($tongModule === 0)
                            <div class="alert alert-warning small mb-0">
                                <i class="fas fa-exclamation-circle me-1"></i> Khóa học chưa có module. 
                                <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $khoaHoc->id]) }}" class="fw-bold">Thêm module ngay</a>
                            </div>
                        @else
                            <p class="text-muted small mb-4 italic">Điền thông tin lịch học và chọn giảng viên phụ trách cho từng phần để bắt đầu vận hành lớp học này.</p>
                            
                            <form action="{{ route('admin.khoa-hoc.kich-hoat-mau', $khoaHoc->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Ngày khai giảng *</label>
                                    <input type="date" name="ngay_khai_giang" class="form-control form-control-sm @error('ngay_khai_giang') is-invalid @enderror" value="{{ old('ngay_khai_giang') }}" min="{{ date('Y-m-d') }}" required>
                                    @error('ngay_khai_giang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Ngày kết thúc dự kiến *</label>
                                    <input type="date" name="ngay_ket_thuc_du_kien" class="form-control form-control-sm @error('ngay_ket_thuc_du_kien') is-invalid @enderror" value="{{ old('ngay_ket_thuc_du_kien') }}" required>
                                    @error('ngay_ket_thuc_du_kien') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <h6 class="fw-bold small text-muted text-uppercase mb-3 border-bottom pb-2">Phân công giảng viên</h6>
                                @foreach($khoaHoc->moduleHocs as $module)
                                    <div class="mb-3 p-2 bg-light rounded border-start border-primary border-3">
                                        <label class="small d-block mb-1 fw-bold text-dark">{{ $module->ten_module }}</label>
                                        <select name="giang_viens[{{ $module->id }}]" class="form-select form-select-sm" required>
                                            <option value="">-- Chọn giảng viên --</option>
                                            @foreach($giangViens as $gv)
                                                <option value="{{ $gv->id }}" {{ old("giang_viens.{$module->id}") == $gv->id ? 'selected' : '' }}>
                                                    {{ $gv->nguoiDung->ho_ten }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("giang_viens.{$module->id}") <div class="text-danger smaller italic mt-1">{{ $message }}</div> @enderror
                                    </div>
                                @endforeach

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-success fw-bold py-2 shadow-sm" onclick="return confirm('Xác nhận kích hoạt? Hệ thống sẽ tạo yêu cầu gửi tới giảng viên.')">
                                        <i class="fas fa-rocket me-2"></i> XÁC NHẬN KÍCH HOẠT
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function confirmDeleteKH() {
        if(confirm('CẢNH BÁO: Xóa khóa học sẽ xóa toàn bộ modules và phân công liên quan. Bạn chắc chắn muốn xóa?')) {
            // Submit hidden delete form or logic
        }
    }
</script>

<style>
    .min-vh-20 { min-height: 150px; }
    .opacity-90 { opacity: 0.95; }
    .smaller { font-size: 0.75rem; }
</style>
@endsection
