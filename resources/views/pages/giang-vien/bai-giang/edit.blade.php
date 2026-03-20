@extends('layouts.app')

@section('title', 'Chỉnh sửa bài giảng')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-10 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.bai-giang.index') }}">Bài giảng</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa</li>
                </ol>
            </nav>
            <h2 class="fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Cập nhật bài giảng</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <form action="{{ route('giang-vien.bai-giang.update', $baiGiang->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <!-- Main Info -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tiêu đề bài giảng <span class="text-danger">*</span></label>
                                    <input type="text" name="tieu_de" class="form-control @error('tieu_de') is-invalid @enderror" 
                                           value="{{ old('tieu_de', $baiGiang->tieu_de) }}" required>
                                    @error('tieu_de') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mô tả bài giảng</label>
                                    <textarea name="mo_ta" class="form-control" rows="5">{{ old('mo_ta', $baiGiang->mo_ta) }}</textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Loại bài giảng <span class="text-danger">*</span></label>
                                        <select name="loai_bai_giang" class="form-select @error('loai_bai_giang') is-invalid @enderror" required>
                                            @foreach(['video' => 'Video bài giảng', 'tai_lieu' => 'Tài liệu học tập', 'bai_doc' => 'Bài đọc (Text)', 'bai_tap' => 'Bài tập về nhà', 'hon_hop' => 'Hỗn hợp'] as $key => $label)
                                                <option value="{{ $key }}" {{ old('loai_bai_giang', $baiGiang->loai_bai_giang) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Thứ tự hiển thị</label>
                                        <input type="number" name="thu_tu_hien_thi" class="form-control" value="{{ old('thu_tu_hien_thi', $baiGiang->thu_tu_hien_thi) }}">
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
                                    <select name="tai_nguyen_chinh_id" class="form-select">
                                        <option value="">-- Chọn tài nguyên chính --</option>
                                        @foreach($thuVien as $tn)
                                            <option value="{{ $tn->id }}" {{ $baiGiang->tai_nguyen_chinh_id == $tn->id ? 'selected' : '' }}>{{ $tn->tieu_de }} ({{ $tn->loai_label }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label fw-bold text-muted">Tài nguyên phụ (Đính kèm)</label>
                                    <div class="row g-2">
                                        @php $phuIds = $baiGiang->taiNguyenPhu->pluck('id')->toArray(); @endphp
                                        @foreach($thuVien as $tn)
                                            <div class="col-md-6">
                                                <div class="form-check card p-2 border-0 bg-light shadow-xs mb-0 h-100">
                                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="tai_nguyen_phu_ids[]" 
                                                           value="{{ $tn->id }}" id="tn_phu_{{ $tn->id }}"
                                                           {{ in_array($tn->id, $phuIds) ? 'checked' : '' }}>
                                                    <label class="form-check-label d-flex align-items-center" for="tn_phu_{{ $tn->id }}">
                                                        <i class="fas {{ $tn->loai_icon }} me-2 text-{{ $tn->loai_color }}"></i>
                                                        <span class="text-truncate" style="max-width: 80%;">{{ $tn->tieu_de }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
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
                                    <select name="phan_cong_id" id="phan_cong_id" class="form-select" required>
                                        @foreach($phanCongs as $pc)
                                            @php 
                                                $isSelected = ($baiGiang->khoa_hoc_id == $pc->khoa_hoc_id && $baiGiang->module_hoc_id == $pc->module_hoc_id);
                                            @endphp
                                            <option value="{{ $pc->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                {{ $pc->moduleHoc->ten_module }} ({{ $pc->khoaHoc->ten_khoa_hoc }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold smaller text-muted text-uppercase">Buổi học (Tùy chọn)</label>
                                    <select name="lich_hoc_id" id="lich_hoc_id" class="form-select">
                                        <option value="">-- Chọn buổi học --</option>
                                        <!-- Will be filled by JS or initial load -->
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold smaller text-muted text-uppercase">Thời điểm mở bài giảng</label>
                                    <input type="datetime-local" name="thoi_diem_mo" class="form-control" 
                                           value="{{ $baiGiang->thoi_diem_mo ? $baiGiang->thoi_diem_mo->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm bg-primary text-white p-3">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-white fw-bold text-primary py-3">
                                    <i class="fas fa-save me-2"></i>Cập nhật bài giảng
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
    const initialLichHocId = "{{ $baiGiang->lich_hoc_id }}";

    function loadLichHoc(phanCongId, selectedId = null) {
        if (!phanCongId) return;
        
        fetch(`{{ route('giang-vien.bai-giang.get-lich-hoc') }}?phan_cong_id=${phanCongId}`)
            .then(response => response.json())
            .then(data => {
                lichHocSelect.innerHTML = '<option value="">-- Chọn buổi học --</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `Buổi ${item.buoi_so} (${item.ngay_hoc || 'Chưa xếp lịch'})`;
                    if (selectedId && item.id == selectedId) {
                        option.selected = true;
                    }
                    lichHocSelect.appendChild(option);
                });
            });
    }

    // Initial load
    loadLichHoc(phanCongSelect.value, initialLichHocId);

    phanCongSelect.addEventListener('change', function() {
        loadLichHoc(this.value);
    });
});
</script>
@endsection
