@extends('layouts.app')

@section('title', 'Thêm tài nguyên mới')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.thu-vien.index') }}">Thư viện</a></li>
                    <li class="breadcrumb-item active">Thêm mới</li>
                </ol>
            </nav>
            <h2 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Thêm tài nguyên mới</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('giang-vien.thu-vien.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề tài nguyên <span class="text-danger">*</span></label>
                            <input type="text" name="tieu_de" class="form-control @error('tieu_de') is-invalid @enderror" 
                                   value="{{ old('tieu_de') }}" placeholder="VD: Bài giảng chương 1, Tài liệu tham khảo..." required>
                            @error('tieu_de') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Loại tài nguyên <span class="text-danger">*</span></label>
                                <select name="loai_tai_nguyen" class="form-select @error('loai_tai_nguyen') is-invalid @enderror" required>
                                    <option value="video">Video bài giảng</option>
                                    <option value="pdf">Tài liệu PDF</option>
                                    <option value="word">File Word</option>
                                    <option value="powerpoint">File PowerPoint</option>
                                    <option value="excel">File Excel</option>
                                    <option value="image">Hình ảnh</option>
                                    <option value="archive">File nén (Zip/Rar)</option>
                                    <option value="link_ngoai">Liên kết ngoài</option>
                                    <option value="tai_lieu_khac">Tài liệu khác</option>
                                </select>
                                @error('loai_tai_nguyen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phạm vi sử dụng <span class="text-danger">*</span></label>
                                <select name="pham_vi_su_dung" class="form-select @error('pham_vi_su_dung') is-invalid @enderror" required>
                                    <option value="ca_nhan">Cá nhân (Chỉ bạn thấy)</option>
                                    <option value="khoa_hoc">Trong khóa học (Dùng cho các lớp bạn dạy)</option>
                                    <option value="cong_khai">Công khai (Toàn hệ thống có thể dùng)</option>
                                </select>
                                @error('pham_vi_su_dung') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả</label>
                            <textarea name="mo_ta" class="form-control" rows="3" placeholder="Mô tả ngắn gọn về nội dung tài nguyên...">{{ old('mo_ta') }}</textarea>
                        </div>

                        <div class="mb-3 card bg-light border-0">
                            <div class="card-body">
                                <label class="form-label fw-bold">Tải lên tệp tin</label>
                                <input type="file" name="file_dinh_kem" class="form-control @error('file_dinh_kem') is-invalid @enderror">
                                <div class="form-text mt-2">Dung lượng tối đa: 50MB. Hỗ trợ nhiều định dạng tệp tin.</div>
                                @error('file_dinh_kem') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                <div class="text-center my-2 fw-bold text-muted">─ HOẶC ─</div>

                                <label class="form-label fw-bold">Liên kết ngoài (URL)</label>
                                <input type="url" name="link_ngoai" class="form-control @error('link_ngoai') is-invalid @enderror" 
                                       value="{{ old('link_ngoai') }}" placeholder="https://example.com/document...">
                                @error('link_ngoai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('giang-vien.thu-vien.index') }}" class="btn btn-light px-4">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">Lưu vào thư viện</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
