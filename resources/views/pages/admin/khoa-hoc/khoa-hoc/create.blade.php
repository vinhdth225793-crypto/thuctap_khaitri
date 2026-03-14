@extends('layouts.app')

@section('title', $loai === 'mau' ? 'Tạo khóa học mẫu' : 'Tạo khóa học trực tiếp')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item">Quản lý đào tạo</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $loai === 'mau' ? 'Tạo khóa học mẫu' : 'Tạo khóa học trực tiếp' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            @if($loai === 'mau')
                <h4 class="fw-bold"><i class="fas fa-copy me-2 text-info"></i> Tạo khóa học mẫu <span class="badge bg-info ms-2 fs-6 shadow-sm">Template</span></h4>
                <p class="text-muted small">Chuẩn bị sẵn nội dung khóa học. Giảng viên và lịch dạy sẽ được thiết lập sau khi kích hoạt thành lớp học.</p>
            @else
                <h4 class="fw-bold"><i class="fas fa-bolt me-2 text-primary"></i> Tạo khóa học trực tiếp <span class="badge bg-primary ms-2 fs-6 shadow-sm">Học ngay</span></h4>
                <p class="text-muted small">Tạo lớp học với giảng viên và ngày khai giảng ngay lập tức.</p>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.khoa-hoc.store') }}" method="POST" enctype="multipart/form-data" id="mainForm">
        @csrf
        <input type="hidden" name="loai" value="{{ $loai }}">

        <div class="row">
            <!-- Cột trái: Thông tin chính -->
            <div class="col-lg-8">
                <div class="vip-card mb-4">
                    <div class="vip-card-header">
                        <h5 class="vip-card-title small fw-bold text-uppercase">1. Thông tin chung</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Môn học <span class="text-danger">*</span></label>
                                <select name="mon_hoc_id" class="form-select vip-form-control @error('mon_hoc_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn môn học --</option>
                                    @foreach($monHocs as $mh)
                                        <option value="{{ $mh->id }}" {{ old('mon_hoc_id', $preselectedMonHocId) == $mh->id ? 'selected' : '' }}>
                                            {{ $mh->ten_mon_hoc }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('mon_hoc_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tên khóa học <span class="text-danger">*</span></label>
                                <input type="text" name="ten_khoa_hoc" class="form-control vip-form-control @error('ten_khoa_hoc') is-invalid @enderror" value="{{ old('ten_khoa_hoc') }}" required placeholder="Ví dụ: Lập trình Python cơ bản">
                                @error('ten_khoa_hoc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small fw-bold d-block">Cấp độ</label>
                                <div class="d-flex gap-4">
                                    @foreach(['co_ban' => 'Cơ bản', 'trung_binh' => 'Trung bình', 'nang_cao' => 'Nâng cao'] as $val => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="cap_do" id="cd_{{ $val }}" value="{{ $val }}" {{ old('cap_do', 'co_ban') === $val ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="cd_{{ $val }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Mô tả ngắn</label>
                                <textarea name="mo_ta_ngan" class="form-control vip-form-control" rows="2" placeholder="Tóm tắt khóa học (tối đa 500 ký tự)">{{ old('mo_ta_ngan') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Nội dung chi tiết</label>
                                <textarea name="mo_ta_chi_tiet" class="form-control vip-form-control" rows="5" placeholder="Mục tiêu, lộ trình và yêu cầu khóa học...">{{ old('mo_ta_chi_tiet') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Lịch học (Chỉ hiện khi trực tiếp) -->
                <div class="{{ $loai === 'truc_tiep' ? '' : 'd-none' }}" id="section-lich-hoc">
                    <div class="vip-card mb-4 border-primary border-start border-4 shadow-sm">
                        <div class="vip-card-header">
                            <h5 class="vip-card-title small fw-bold text-primary">2. Lịch học & Khai giảng</h5>
                        </div>
                        <div class="vip-card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Ngày khai giảng <span class="text-danger">*</span></label>
                                    <input type="date" name="ngay_khai_giang" class="form-control vip-form-control @error('ngay_khai_giang') is-invalid @enderror" value="{{ old('ngay_khai_giang') }}" min="{{ date('Y-m-d') }}">
                                    @error('ngay_khai_giang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Ngày kết thúc dự kiến <span class="text-danger">*</span></label>
                                    <input type="date" name="ngay_ket_thuc_du_kien" class="form-control vip-form-control @error('ngay_ket_thuc_du_kien') is-invalid @enderror" value="{{ old('ngay_ket_thuc_du_kien') }}">
                                    @error('ngay_ket_thuc_du_kien') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Modules (Dynamic Table) -->
                <div class="vip-card mb-4 border-0 shadow-sm">
                    <div class="vip-card-header d-flex justify-content-between align-items-center">
                        <h5 class="vip-card-title small fw-bold text-uppercase">3. Cấu trúc Modules học tập</h5>
                        <span class="badge bg-dark" id="module-count">1 module</span>
                    </div>
                    <div class="vip-card-body p-0">
                        <!-- Suggest Copy from Template -->
                        <div class="p-3 bg-light border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-lightbulb text-warning fs-4"></i>
                                <div class="flex-fill">
                                    <label class="small fw-bold text-muted d-block mb-1">Copy cấu trúc từ khóa học mẫu có sẵn</label>
                                    <select class="form-select form-select-sm vip-form-control" id="copy-from-template" style="max-width: 450px;">
                                        <option value="">-- Chọn khóa học mẫu để lấy cấu trúc module --</option>
                                        @foreach($khoaHocMauCoSan as $mau)
                                            <option value="{{ $mau->id }}" data-modules="{{ $mau->moduleHocs->toJson() }}">
                                                [{{ $mau->ma_khoa_hoc }}] {{ $mau->ten_khoa_hoc }} ({{ $mau->tong_so_module }} module)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="module-table">
                                <thead class="bg-light smaller text-muted text-uppercase">
                                    <tr>
                                        <th class="text-center" width="50">#</th>
                                        <th>Tên module <span class="text-danger">*</span></th>
                                        <th width="110">TL (phút)</th>
                                        <th>Mô tả nhanh</th>
                                        @if($loai === 'truc_tiep')
                                            <th width="200">Giảng viên <span class="text-danger">*</span></th>
                                        @endif
                                        <th class="text-center" width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $modulesOld = old('modules', [['ten_module'=>'', 'thoi_luong_du_kien'=>'', 'mo_ta'=>'', 'giang_vien_id'=>'']]) @endphp
                                    @foreach($modulesOld as $i => $mod)
                                        <tr class="module-row" data-index="{{ $i }}">
                                            <td class="text-center fw-bold text-muted stt">{{ $i + 1 }}</td>
                                            <td>
                                                <input type="text" name="modules[{{ $i }}][ten_module]" class="form-control form-control-sm vip-form-control" value="{{ $mod['ten_module'] }}" required placeholder="Tên module">
                                            </td>
                                            <td>
                                                <input type="number" name="modules[{{ $i }}][thoi_luong_du_kien]" class="form-control form-control-sm vip-form-control text-center" value="{{ $mod['thoi_luong_du_kien'] }}" placeholder="90" min="1" max="600">
                                            </td>
                                            <td>
                                                <input type="text" name="modules[{{ $i }}][mo_ta]" class="form-control form-control-sm vip-form-control" value="{{ $mod['mo_ta'] }}" placeholder="...">
                                            </td>
                                            @if($loai === 'truc_tiep')
                                                <td>
                                                    <select name="modules[{{ $i }}][giang_vien_id]" class="form-select form-select-sm vip-form-control" required>
                                                        <option value="">-- Chọn GV --</option>
                                                        @foreach($giangViens as $gv)
                                                            <option value="{{ $gv->id }}" {{ ($mod['giang_vien_id'] ?? '') == $gv->id ? 'selected' : '' }}>{{ $gv->nguoiDung->ho_ten }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            @endif
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link text-danger p-0 btn-remove-row" title="Xóa"><i class="fas fa-times-circle"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 border-top bg-light">
                            <button type="button" id="btn-add-module" class="btn btn-outline-secondary btn-sm fw-bold">
                                <i class="fas fa-plus me-1"></i> Thêm dòng module mới
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Media & Submit -->
            <div class="col-lg-4">
                <div class="vip-card mb-4 shadow-sm">
                    <div class="vip-card-header">
                        <h5 class="vip-card-title small fw-bold text-uppercase">Ảnh đại diện</h5>
                    </div>
                    <div class="vip-card-body p-4 text-center">
                        <div class="bg-light rounded p-4 mb-3 border border-dashed text-muted" id="image-preview-placeholder">
                            <i class="fas fa-image fa-3x opacity-25"></i>
                            <p class="small mb-0 mt-2 italic">Chưa chọn tập tin</p>
                        </div>
                        <input type="file" name="hinh_anh" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text smaller italic mt-2">Dung lượng tối đa 2MB. Hỗ trợ: JPG, PNG.</div>
                    </div>
                </div>

                <div class="vip-card mb-4 shadow-sm">
                    <div class="vip-card-header">
                        <h5 class="vip-card-title small fw-bold text-uppercase">Ghi chú nội bộ</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <textarea name="ghi_chu_noi_bo" class="form-control vip-form-control" rows="4" placeholder="Chỉ dành cho quản trị viên..."></textarea>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    @if($loai === 'mau')
                        <button type="submit" class="btn btn-info py-2 fw-bold text-white shadow-sm border-0"><i class="fas fa-save me-2"></i>LƯU KHÓA HỌC MẪU</button>
                    @else
                        <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm border-0"><i class="fas fa-paper-plane me-2"></i>TẠO VÀ GỬI THÔNG BÁO GV</button>
                    @endif
                    <a href="{{ route('admin.khoa-hoc.index', ['tab'=> $loai==='mau' ? 'mau' : 'hoat_dong']) }}" class="btn btn-outline-secondary py-2 fw-bold">HỦY BỎ</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#module-table tbody');
    const btnAdd = document.getElementById('btn-add-module');
    const countBadge = document.getElementById('module-count');
    const loai = "{{ $loai }}";

    function updateRenumbering() {
        const rows = tableBody.querySelectorAll('.module-row');
        rows.forEach((row, index) => {
            row.dataset.index = index;
            row.querySelector('.stt').textContent = index + 1;
            
            row.querySelectorAll('input, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/modules\[\d+\]/, `modules[${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
        countBadge.textContent = `${rows.length} module`;
    }

    // 1. Thêm row mới
    btnAdd.addEventListener('click', function() {
        const lastRow = tableBody.querySelector('.module-row:last-child');
        const newRow = lastRow.cloneNode(true);
        
        newRow.querySelectorAll('input').forEach(i => i.value = '');
        if (loai === 'truc_tiep') {
            newRow.querySelector('select').selectedIndex = 0;
        }
        
        tableBody.appendChild(newRow);
        updateRenumbering();
    });

    // 2. Xóa row
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-row')) {
            const rows = tableBody.querySelectorAll('.module-row');
            if (rows.length <= 1) {
                alert('Phải có ít nhất 1 module cho khóa học!');
                return;
            }
            e.target.closest('.module-row').remove();
            updateRenumbering();
        }
    });

    // 3. Copy từ template có sẵn
    document.getElementById('copy-from-template').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (!selectedOption.value) return;

        if (confirm("Thay thế danh sách module hiện tại bằng cấu trúc từ khóa học mẫu này?")) {
            const modulesData = JSON.parse(selectedOption.dataset.modules);
            tableBody.innerHTML = ''; 

            modulesData.forEach((mod, index) => {
                const row = document.createElement('tr');
                row.className = 'module-row';
                row.dataset.index = index;
                
                let gvCell = loai === 'truc_tiep' ? `
                    <td>
                        <select name="modules[${index}][giang_vien_id]" class="form-select form-select-sm vip-form-control" required>
                            <option value="">-- Chọn GV --</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->id }}">{{ $gv->nguoiDung->ho_ten }}</option>
                            @endforeach
                        </select>
                    </td>
                ` : '';

                row.innerHTML = `
                    <td class="text-center fw-bold text-muted stt">${index + 1}</td>
                    <td>
                        <input type="text" name="modules[${index}][ten_module]" class="form-control form-control-sm vip-form-control" value="${mod.ten_module}" required>
                    </td>
                    <td>
                        <input type="number" name="modules[${index}][thoi_luong_du_kien]" class="form-control form-control-sm vip-form-control text-center" value="${mod.thoi_luong_du_kien || ''}">
                    </td>
                    <td>
                        <input type="text" name="modules[${index}][mo_ta]" class="form-control form-control-sm vip-form-control" value="${mod.mo_ta || ''}">
                    </td>
                    ${gvCell}
                    <td class="text-center">
                        <button type="button" class="btn btn-link text-danger p-0 btn-remove-row"><i class="fas fa-times-circle"></i></button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            updateRenumbering();
        }
        this.selectedIndex = 0; 
    });
});
</script>

<style>
    .border-dashed { border-style: dashed !important; }
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
</style>
@endsection
