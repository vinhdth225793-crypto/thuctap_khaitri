@extends('layouts.app')

@section('title', 'Chỉnh sửa tài nguyên')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.thu-vien.index') }}">Thư viện</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa</li>
                </ol>
            </nav>
            <h2 class="fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Chỉnh sửa tài nguyên</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('giang-vien.thu-vien.update', $taiNguyen->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề tài nguyên <span class="text-danger">*</span></label>
                            <input type="text" name="tieu_de" class="form-control @error('tieu_de') is-invalid @enderror" 
                                   value="{{ old('tieu_de', $taiNguyen->tieu_de) }}" required>
                            @error('tieu_de') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Loại tài nguyên <span class="text-danger">*</span></label>
                                <select name="loai_tai_nguyen" class="form-select @error('loai_tai_nguyen') is-invalid @enderror" required>
                                    @foreach(['video' => 'Video bài giảng', 'pdf' => 'Tài liệu PDF', 'word' => 'File Word', 'powerpoint' => 'File PowerPoint', 'excel' => 'File Excel', 'image' => 'Hình ảnh', 'archive' => 'File nén', 'link_ngoai' => 'Liên kết ngoài', 'tai_lieu_khac' => 'Tài liệu khác'] as $key => $label)
                                        <option value="{{ $key }}" {{ old('loai_tai_nguyen', $taiNguyen->loai_tai_nguyen) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('loai_tai_nguyen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phạm vi sử dụng <span class="text-danger">*</span></label>
                                <select name="pham_vi_su_dung" class="form-select @error('pham_vi_su_dung') is-invalid @enderror" required>
                                    <option value="ca_nhan" {{ old('pham_vi_su_dung', $taiNguyen->pham_vi_su_dung) == 'ca_nhan' ? 'selected' : '' }}>Cá nhân (Chỉ bạn thấy)</option>
                                    <option value="khoa_hoc" {{ old('pham_vi_su_dung', $taiNguyen->pham_vi_su_dung) == 'khoa_hoc' ? 'selected' : '' }}>Trong khóa học</option>
                                    <option value="cong_khai" {{ old('pham_vi_su_dung', $taiNguyen->pham_vi_su_dung) == 'cong_khai' ? 'selected' : '' }}>Công khai (Hệ thống)</option>
                                </select>
                                @error('pham_vi_su_dung') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả</label>
                            <textarea name="mo_ta" class="form-control" rows="3">{{ old('mo_ta', $taiNguyen->mo_ta) }}</textarea>
                        </div>

                        <div class="mb-3 card bg-light border-0">
                            <div class="card-body">
                                <label class="form-label fw-bold">Tài liệu hiện tại</label>
                                <div class="mb-3">
                                    @if($taiNguyen->is_external)
                                        <div class="d-flex align-items-center p-2 bg-white rounded border">
                                            <i class="fas fa-link me-2 text-info"></i>
                                            <a href="{{ $taiNguyen->link_ngoai }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 90%;">{{ $taiNguyen->link_ngoai }}</a>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center p-2 bg-white rounded border">
                                            <i class="fas {{ $taiNguyen->loai_icon }} me-2 text-{{ $taiNguyen->loai_color }}"></i>
                                            <span class="text-truncate d-inline-block" style="max-width: 90%;">{{ $taiNguyen->file_name }}</span>
                                        </div>
                                    @endif
                                </div>

                                <label class="form-label fw-bold">Thay đổi tệp tin (Để trống nếu giữ nguyên)</label>
                                <input type="file" name="file_dinh_kem" class="form-control @error('file_dinh_kem') is-invalid @enderror">
                                @error('file_dinh_kem') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                <div class="text-center my-2 fw-bold text-muted">─ HOẶC ─</div>

                                <label class="form-label fw-bold">Cập nhật liên kết ngoài (URL)</label>
                                <input type="url" name="link_ngoai" class="form-control @error('link_ngoai') is-invalid @enderror" 
                                       value="{{ old('link_ngoai', $taiNguyen->link_ngoai) }}">
                                @error('link_ngoai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('giang-vien.thu-vien.index') }}" class="btn btn-light px-4">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">Cập nhật tài nguyên</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
