@extends('layouts.app')

@section('title', 'Chỉnh sửa khóa học')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div>
                    <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-header">
                    <h5 class="vip-card-title">
                        <i class="fas fa-edit"></i> Chỉnh sửa khóa học: {{ $khoaHoc->ten_khoa_hoc }}
                    </h5>
                </div>
                <div class="vip-card-body">
                    <form action="{{ route('admin.khoa-hoc.update', $khoaHoc->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Thông tin cơ bản -->
                        <h6 class="mb-3 text-primary">
                            <i class="fas fa-info-circle"></i> Thông tin cơ bản
                        </h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="mon_hoc_id" class="form-label">
                                    <i class="fas fa-book"></i> Môn học <span class="text-danger">*</span>
                                </label>
                                <select name="mon_hoc_id" id="mon_hoc_id" class="form-select vip-form-control @error('mon_hoc_id') is-invalid @enderror" required>
                                    <option value="">Chọn môn học</option>
                                    @foreach($monHocs as $monHoc)
                                        <option value="{{ $monHoc->id }}" {{ $khoaHoc->mon_hoc_id == $monHoc->id ? 'selected' : '' }}>
                                            {{ $monHoc->ten_mon_hoc }} ({{ $monHoc->ma_mon_hoc }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('mon_hoc_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="cap_do" class="form-label">
                                    <i class="fas fa-chart-line"></i> Cấp độ <span class="text-danger">*</span>
                                </label>
                                <select name="cap_do" id="cap_do" class="form-select vip-form-control @error('cap_do') is-invalid @enderror" required>
                                    <option value="">Chọn cấp độ</option>
                                    <option value="co_ban" {{ $khoaHoc->cap_do == 'co_ban' ? 'selected' : '' }}>Cơ bản</option>
                                    <option value="trung_binh" {{ $khoaHoc->cap_do == 'trung_binh' ? 'selected' : '' }}>Trung bình</option>
                                    <option value="nang_cao" {{ $khoaHoc->cap_do == 'nang_cao' ? 'selected' : '' }}>Nâng cao</option>
                                </select>
                                @error('cap_do')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label for="ten_khoa_hoc" class="form-label">
                                    <i class="fas fa-graduation-cap"></i> Tên khóa học <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="ten_khoa_hoc"
                                    id="ten_khoa_hoc"
                                    class="form-control vip-form-control @error('ten_khoa_hoc') is-invalid @enderror"
                                    placeholder="Nhập tên khóa học"
                                    value="{{ $khoaHoc->ten_khoa_hoc }}"
                                    required
                                >
                                @error('ten_khoa_hoc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="trang_thai" class="form-label">
                                    <i class="fas fa-toggle-on"></i> Trạng thái
                                </label>
                                <select name="trang_thai" id="trang_thai" class="form-select vip-form-control">
                                    <option value="1" {{ $khoaHoc->trang_thai ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="0" {{ !$khoaHoc->trang_thai ? 'selected' : '' }}>Tạm dừng</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="mo_ta_ngan" class="form-label">
                                    <i class="fas fa-align-left"></i> Mô tả ngắn
                                </label>
                                <textarea
                                    name="mo_ta_ngan"
                                    id="mo_ta_ngan"
                                    class="form-control vip-form-control @error('mo_ta_ngan') is-invalid @enderror"
                                    rows="2"
                                    placeholder="Mô tả ngắn gọn về khóa học"
                                >{{ $khoaHoc->mo_ta_ngan }}</textarea>
                                @error('mo_ta_ngan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="mo_ta_chi_tiet" class="form-label">
                                    <i class="fas fa-file-alt"></i> Mô tả chi tiết
                                </label>
                                <textarea
                                    name="mo_ta_chi_tiet"
                                    id="mo_ta_chi_tiet"
                                    class="form-control vip-form-control @error('mo_ta_chi_tiet') is-invalid @enderror"
                                    rows="4"
                                    placeholder="Mô tả chi tiết về khóa học, nội dung, mục tiêu..."
                                >{{ $khoaHoc->mo_ta_chi_tiet }}</textarea>
                                @error('mo_ta_chi_tiet')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="hinh_anh" class="form-label">
                                    <i class="fas fa-image"></i> Hình ảnh khóa học
                                </label>
                                <input
                                    type="file"
                                    name="hinh_anh"
                                    id="hinh_anh"
                                    class="form-control vip-form-control @error('hinh_anh') is-invalid @enderror"
                                    accept="image/*"
                                >
                                <div class="form-text">
                                    Chấp nhận: JPG, PNG, GIF. Kích thước tối đa: 2MB
                                    @if($khoaHoc->hinh_anh)
                                        <br><small class="text-muted">Để trống nếu không muốn thay đổi hình ảnh hiện tại</small>
                                    @endif
                                </div>
                                @if($khoaHoc->hinh_anh)
                                    <div class="mt-2">
                                        <img src="{{ asset($khoaHoc->hinh_anh) }}" alt="Hình ảnh hiện tại" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                @endif
                                @error('hinh_anh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Thông tin modules hiện tại -->
                        <h6 class="mb-3 text-primary">
                            <i class="fas fa-list"></i> Modules hiện tại
                        </h6>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> Việc chỉnh sửa modules và phân công giảng viên sẽ được triển khai trong phiên bản nâng cao.
                            Hiện tại chỉ có thể chỉnh sửa thông tin cơ bản của khóa học.
                        </div>

                        @if($khoaHoc->moduleHocs->count() > 0)
                            <div class="row mb-4">
                                @foreach($khoaHoc->moduleHocs as $module)
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="text-primary mb-2">
                                                <i class="fas fa-bookmark"></i> {{ $module->ten_module }}
                                            </h6>
                                            <p class="text-muted small mb-1">Mã: {{ $module->ma_module }}</p>
                                            @if($module->thoi_luong_du_kien)
                                                <p class="small text-info mb-1">
                                                    <i class="fas fa-clock"></i>
                                                    @php
                                                        $h = intdiv($module->thoi_luong_du_kien, 60);
                                                        $m = $module->thoi_luong_du_kien % 60;
                                                    @endphp
                                                    {{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'p' : '' }}
                                                    <small class="text-muted">({{ $module->thoi_luong_du_kien }} phút)</small>
                                                </p>
                                            @endif
                                            @php
                                                $giangVienModule = $module->phanCongGiangViens->where('trang_thai', 'da_nhan')->first();
                                            @endphp
                                            @if($giangVienModule)
                                                <p class="small mb-1">
                                                    <strong>Giảng viên:</strong> {{ $giangVienModule->giangVien->nguoiDung->ho_ten ?? 'N/A' }}
                                                </p>
                                            @endif
                                            <div>
                                                @if($module->trang_thai)
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-danger">Tạm dừng</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Submit buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection