@extends('layouts.app')

@section('title', 'Tạo bài giảng mới')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-10 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.bai-giang.index') }}">Bài giảng</a></li>
                    <li class="breadcrumb-item active">Tạo mới</li>
                </ol>
            </nav>
            <h2 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Thiết kế bài giảng</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <form action="{{ route('giang-vien.bai-giang.store') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- Main Info -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tiêu đề bài giảng <span class="text-danger">*</span></label>
                                    <input type="text" name="tieu_de" class="form-control @error('tieu_de') is-invalid @enderror" 
                                           value="{{ old('tieu_de') }}" placeholder="VD: Bài giảng về lập trình PHP cơ bản..." required>
                                    @error('tieu_de') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mô tả bài giảng</label>
                                    <textarea name="mo_ta" class="form-control" rows="5" placeholder="Nội dung tóm tắt của bài giảng...">{{ old('mo_ta') }}</textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Loại bài giảng <span class="text-danger">*</span></label>
                                        <select name="loai_bai_giang" class="form-select @error('loai_bai_giang') is-invalid @enderror" required>
                                            <option value="video">Video bài giảng</option>
                                            <option value="tai_lieu">Tài liệu học tập</option>
                                            <option value="bai_doc">Bài đọc (Text)</option>
                                            <option value="bai_tap">Bài tập về nhà</option>
                                            <option value="hon_hop" selected>Hỗn hợp</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Thứ tự hiển thị</label>
                                        <input type="number" name="thu_tu_hien_thi" class="form-control" value="{{ old('thu_tu_hien_thi', 0) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resource Selection -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-link me-2 text-info"></i>Liên kết tài nguyên từ Thư viện</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary"><i class="fas fa-star me-1"></i>Tài nguyên chính</label>
                                    <select name="tai_nguyen_chinh_id" class="form-select select2">
                                        <option value="">-- Chọn tài nguyên chính (Video, PDF...) --</option>
                                        @foreach($thuVien as $tn)
                                            <option value="{{ $tn->id }}">{{ $tn->tieu_de }} ({{ $tn->loai_label }})</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Tài nguyên chính sẽ được hiển thị nổi bật trong bài giảng.</div>
                                </div>

                                <div>
                                    <label class="form-label fw-bold text-muted">Tài nguyên phụ (Đính kèm)</label>
                                    <div class="row g-2">
                                        @forelse($thuVien as $tn)
                                            <div class="col-md-6">
                                                <div class="form-check card p-2 border-0 bg-light shadow-xs mb-0 h-100">
                                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="tai_nguyen_phu_ids[]" 
                                                           value="{{ $tn->id }}" id="tn_phu_{{ $tn->id }}">
                                                    <label class="form-check-label d-flex align-items-center" for="tn_phu_{{ $tn->id }}">
                                                        <i class="fas {{ $tn->loai_icon }} me-2 text-{{ $tn->loai_color }}"></i>
                                                        <span class="text-truncate" style="max-width: 80%;">{{ $tn->tieu_de }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12 text-center py-3 text-muted smaller">
                                                Bạn chưa có tài nguyên đã duyệt nào trong thư viện.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Context -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Vị trí bài giảng</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold smaller text-muted text-uppercase">Phân công Module <span class="text-danger">*</span></label>
                                    <select name="phan_cong_id" id="phan_cong_id" class="form-select @error('phan_cong_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn Module --</option>
                                        @foreach($phanCongs as $pc)
                                            <option value="{{ $pc->id }}">{{ $pc->moduleHoc->ten_module }} ({{ $pc->khoaHoc->ten_khoa_hoc }})</option>
                                        @endforeach
                                    </select>
                                    @error('phan_cong_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold smaller text-muted text-uppercase">Buổi học (Tùy chọn)</label>
                                    <select name="lich_hoc_id" id="lich_hoc_id" class="form-select">
                                        <option value="">-- Chọn buổi học --</option>
                                    </select>
                                    <div class="form-text">Gắn bài giảng vào một buổi cụ thể hoặc để chung cho Module.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold smaller text-muted text-uppercase">Thời điểm mở bài giảng</label>
                                    <input type="datetime-local" name="thoi_diem_mo" class="form-control">
                                    <div class="form-text">Để trống nếu muốn mở ngay lập tức sau khi công bố.</div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm bg-primary text-white p-3">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-white fw-bold text-primary py-3">
                                    <i class="fas fa-save me-2"></i>Lưu bài giảng
                                </button>
                                <a href="{{ route('giang-vien.bai-giang.index') }}" class="btn btn-link text-white text-decoration-none">Quay lại danh sách</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phanCongSelect = document.getElementById('phan_cong_id');
    const lichHocSelect = document.getElementById('lich_hoc_id');

    phanCongSelect.addEventListener('change', function() {
        const phanCongId = this.value;
        lichHocSelect.innerHTML = '<option value="">-- Chọn buổi học --</option>';

        if (phanCongId) {
            fetch(`{{ route('giang-vien.bai-giang.get-lich-hoc') }}?phan_cong_id=${phanCongId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = `Buổi ${item.buoi_so} (${item.ngay_hoc || 'Chưa xếp lịch'})`;
                        lichHocSelect.appendChild(option);
                    });
                });
        }
    });
});
</script>

<style>
    .smaller { font-size: 0.75rem; }
    .btn-white { background-color: white; border-color: white; }
    .bg-light { background-color: #f8f9fa !important; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
</style>
@endsection
