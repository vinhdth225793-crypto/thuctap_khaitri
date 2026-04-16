<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="row">
        <!-- Cột trái: Upload & Preview -->
        <div class="col-md-5 text-center mb-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Hình ảnh Banner <span class="text-danger">*</span></label>
                <div class="border rounded p-2 bg-light mb-2" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                    <img id="preview-image" src="{{ $banner && $banner->duong_dan_anh ? asset($banner->duong_dan_anh) : asset('images/default-banner.jpg') }}" 
                         alt="Preview" class="img-fluid rounded shadow-sm" style="max-height: 250px; object-fit: cover;">
                </div>
                <input type="file" name="anh_banner" id="anh_banner" class="form-control @error('anh_banner') is-invalid @enderror" accept="image/*">
                @error('anh_banner')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted mt-2 d-block">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</small>
            </div>
        </div>

        <!-- Cột phải: Thông tin chi tiết -->
        <div class="col-md-7">
            <div class="mb-3">
                <label for="tieu_de" class="form-label fw-bold">Tiêu đề <span class="text-danger">*</span></label>
                <input type="text" name="tieu_de" id="tieu_de" class="form-control @error('tieu_de') is-invalid @enderror" 
                       value="{{ old('tieu_de', $banner ? $banner->tieu_de : '') }}" placeholder="Nhập tiêu đề banner">
                @error('tieu_de')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="mo_ta" class="form-label fw-bold">Mô tả ngắn</label>
                <div class="editor-container">
                    <textarea name="mo_ta" id="mo_ta" class="form-control @error('mo_ta') is-invalid @enderror" 
                              placeholder="Nhập mô tả banner">{{ old('mo_ta', $banner ? $banner->mo_ta : '') }}</textarea>
                </div>
                @error('mo_ta')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <style>
                    .ck-editor__editable {
                        min-height: 150px;
                    }
                </style>
            </div>

            <div class="mb-3">
                <label for="link" class="form-label fw-bold">Liên kết (Link)</label>
                <input type="text" name="link" id="link" class="form-control @error('link') is-invalid @enderror" 
                       value="{{ old('link', $banner ? $banner->link : '') }}" placeholder="https://...">
                @error('link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="thu_tu" class="form-label fw-bold">Thứ tự hiển thị</label>
                        <input type="number" name="thu_tu" id="thu_tu" class="form-control @error('thu_tu') is-invalid @enderror" 
                               value="{{ old('thu_tu', $banner ? $banner->thu_tu : ($suggestedOrder ?? 0)) }}" min="0">
                        @error('thu_tu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">
                            Nhập <strong>0</strong> để dùng làm ảnh thẻ đầu trang chủ. Nhập <strong>1 trở đi</strong> để đưa vào banner slide bên dưới.
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Trạng thái</label>
                        <input type="hidden" name="trang_thai" value="0">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="trang_thai" id="trang_thai" value="1" 
                                   {{ old('trang_thai', $banner ? $banner->trang_thai : true) ? 'checked' : '' }} style="width: 3em; height: 1.5em;">
                            <label class="form-check-label ms-2 mt-1" for="trang_thai">Bật / Ẩn</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 border-top pt-3 text-end">
                <a href="{{ route('admin.settings.banners.index') }}" class="btn btn-outline-secondary px-4 me-2">Hủy</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>{{ $banner ? 'Cập nhật' : 'Lưu Banner' }}
                </button>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // Preview ảnh
    document.getElementById('anh_banner').onchange = function (evt) {
        const [file] = this.files
        if (file) {
            document.getElementById('preview-image').src = URL.createObjectURL(file)
        }
    }

    // Khởi tạo CKEditor
    ClassicEditor
        .create(document.querySelector('#mo_ta'), {
            toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'],
            language: 'vi'
        })
        .catch(error => {
            console.error(error);
        });
</script>
