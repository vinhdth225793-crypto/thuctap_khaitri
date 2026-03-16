@extends('layouts.app')

@section('title', 'Sửa buổi học')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $lichHoc->khoa_hoc_id) }}">{{ $lichHoc->khoaHoc->ma_khoa_hoc }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.lich-hoc.index', $lichHoc->khoa_hoc_id) }}">Lịch học</a></li>
            <li class="breadcrumb-item active">Sửa buổi {{ $lichHoc->buoi_so }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="vip-card shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">
                        <i class="fas fa-edit me-2 text-warning"></i> Cập nhật thông tin buổi học
                    </h5>
                </div>
                <div class="vip-card-body p-4">
                    <form action="{{ route('admin.khoa-hoc.lich-hoc.update', [$lichHoc->khoa_hoc_id, $lichHoc->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="smaller text-muted text-uppercase fw-bold mb-1">Module:</label>
                            <div class="fw-bold fs-5 text-dark border-bottom pb-2">{{ $lichHoc->moduleHoc->ten_module }} (Buổi {{ $lichHoc->buoi_so }})</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ngày học *</label>
                                <input type="date" name="ngay_hoc" class="form-control vip-form-control @error('ngay_hoc') is-invalid @enderror" 
                                       value="{{ old('ngay_hoc', $lichHoc->ngay_hoc->format('Y-m-d')) }}" required>
                                @error('ngay_hoc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Giờ bắt đầu *</label>
                                <input type="time" name="gio_bat_dau" class="form-control vip-form-control @error('gio_bat_dau') is-invalid @enderror" 
                                       value="{{ old('gio_bat_dau', \Carbon\Carbon::parse($lichHoc->gio_bat_dau)->format('H:i')) }}" required>
                                @error('gio_bat_dau') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Giờ kết thúc *</label>
                                <input type="time" name="gio_ket_thuc" class="form-control vip-form-control @error('gio_ket_thuc') is-invalid @enderror" 
                                       value="{{ old('gio_ket_thuc', \Carbon\Carbon::parse($lichHoc->gio_ket_thuc)->format('H:i')) }}" required>
                                @error('gio_ket_thuc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Thứ trong tuần *</label>
                                <select name="thu_trong_tuan" class="form-select vip-form-control" required>
                                    @foreach(\App\Models\LichHoc::$thuLabels as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('thu_trong_tuan', $lichHoc->thu_trong_tuan) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Trạng thái buổi học *</label>
                                <select name="trang_thai" class="form-select vip-form-control" required>
                                    <option value="cho"        {{ old('trang_thai', $lichHoc->trang_thai) == 'cho' ? 'selected' : '' }}>Chờ (Sắp tới)</option>
                                    <option value="dang_hoc"   {{ old('trang_thai', $lichHoc->trang_thai) == 'dang_hoc' ? 'selected' : '' }}>Đang học</option>
                                    <option value="hoan_thanh" {{ old('trang_thai', $lichHoc->trang_thai) == 'hoan_thanh' ? 'selected' : '' }}>Hoàn thành</option>
                                    <option value="huy"        {{ old('trang_thai', $lichHoc->trang_thai) == 'huy' ? 'selected' : '' }}>Đã hủy</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Hình thức *</label>
                                <select name="hinh_thuc" class="form-select vip-form-control" required id="edit-hinh-thuc">
                                    <option value="truc_tiep" {{ old('hinh_thuc', $lichHoc->hinh_thuc) == 'truc_tiep' ? 'selected' : '' }}>Trực tiếp (Tại trung tâm)</option>
                                    <option value="online"    {{ old('hinh_thuc', $lichHoc->hinh_thuc) == 'online' ? 'selected' : '' }}>Online (Qua link họp)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold" id="label-location">Phòng học / Link họp</label>
                                <input type="text" name="phong_hoc" class="form-control vip-form-control shadow-sm" 
                                       value="{{ old('phong_hoc', $lichHoc->hinh_thuc === 'online' ? $lichHoc->link_online : $lichHoc->phong_hoc) }}" placeholder="...">
                                <input type="hidden" name="link_online" id="input-link-online" value="{{ $lichHoc->link_online }}">
                                
                                <div class="mt-2" id="box-apply-all" style="display: {{ $lichHoc->hinh_thuc === 'online' ? 'block' : 'none' }};">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="apply_to_all_online" value="1" id="applyAllOnline">
                                        <label class="form-check-label small text-primary fw-bold" for="applyAllOnline">
                                            Áp dụng link này cho tất cả các buổi học Online của khóa học này
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Giảng viên dạy buổi này</label>
                                <select name="giang_vien_id" class="form-select vip-form-control">
                                    <option value="">-- Mặc định theo module --</option>
                                    @foreach($giangViens as $gv)
                                        <option value="{{ $gv->id }}" {{ old('giang_vien_id', $lichHoc->giang_vien_id) == $gv->id ? 'selected' : '' }}>
                                            {{ $gv->nguoiDung->ho_ten }} ({{ $gv->chuyen_nganh ?: 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Ghi chú cho buổi này</label>
                                <textarea name="ghi_chu" class="form-control vip-form-control" rows="3">{{ old('ghi_chu', $lichHoc->ghi_chu) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-warning px-5 fw-bold shadow-sm text-white border-0">
                                <i class="fas fa-save me-2"></i> CẬP NHẬT
                            </button>
                            <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $lichHoc->khoa_hoc_id) }}" class="btn btn-outline-secondary px-4 fw-bold">
                                HỦY BỎ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Logic đơn giản để đổi giá trị phong_hoc/link_online khi submit nếu cần (hoặc xử lý ở controller)
    // Tạm thời để nguyên form-control xử lý cho cả 2
</script>

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.1); border-color: #ffc107; }
</style>
@endsection
