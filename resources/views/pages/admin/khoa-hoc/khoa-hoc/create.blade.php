@extends('layouts.app')

@section('title', 'Thêm khóa học mới')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('admin.khoa-hoc.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="vip-card">
                <div class="vip-card-header">
                    <h5 class="vip-card-title">
                        <i class="fas fa-plus"></i> Thêm khóa học mới
                    </h5>
                </div>
                <div class="vip-card-body">
                    <form action="{{ route('admin.khoa-hoc.store') }}" method="POST" enctype="multipart/form-data" id="khoaHocForm">
                        @csrf

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
                                        <option value="{{ $monHoc->id }}" {{ old('mon_hoc_id') == $monHoc->id ? 'selected' : '' }}>
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
                                    <option value="co_ban" {{ old('cap_do') == 'co_ban' ? 'selected' : '' }}>Cơ bản</option>
                                    <option value="trung_binh" {{ old('cap_do') == 'trung_binh' ? 'selected' : '' }}>Trung bình</option>
                                    <option value="nang_cao" {{ old('cap_do') == 'nang_cao' ? 'selected' : '' }}>Nâng cao</option>
                                </select>
                                @error('cap_do')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="ten_khoa_hoc" class="form-label">
                                    <i class="fas fa-graduation-cap"></i> Tên khóa học <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="ten_khoa_hoc"
                                    id="ten_khoa_hoc"
                                    class="form-control vip-form-control @error('ten_khoa_hoc') is-invalid @enderror"
                                    placeholder="Nhập tên khóa học"
                                    value="{{ old('ten_khoa_hoc') }}"
                                    required
                                >
                                @error('ten_khoa_hoc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                >{{ old('mo_ta_ngan') }}</textarea>
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
                                >{{ old('mo_ta_chi_tiet') }}</textarea>
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
                                <div class="form-text">Chấp nhận: JPG, PNG, GIF. Kích thước tối đa: 2MB</div>
                                @error('hinh_anh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Modules -->
                        <h6 class="mb-3 text-primary">
                            <i class="fas fa-list"></i> Modules học tập
                        </h6>
                        <div id="modules-container">
                            <div class="module-item border rounded p-3 mb-3" data-module="1">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Tên module 1 <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            name="modules[0][ten_module]"
                                            class="form-control vip-form-control"
                                            placeholder="Nhập tên module"
                                            required
                                        >
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Thời lượng (giờ)</label>
                                        <input
                                            type="number"
                                            name="modules[0][thoi_luong_du_kien]"
                                            class="form-control vip-form-control"
                                            placeholder="0"
                                            min="1"
                                        >
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-module" style="display: none;">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <label class="form-label">Mô tả module</label>
                                        <textarea
                                            name="modules[0][mo_ta]"
                                            class="form-control vip-form-control"
                                            rows="2"
                                            placeholder="Mô tả nội dung module"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-primary" id="add-module">
                                <i class="fas fa-plus"></i> Thêm module
                            </button>
                        </div>

                        <!-- Phân công giảng viên cho từng module -->
                        <h6 class="mb-3 text-primary">
                            <i class="fas fa-users"></i> Phân công giảng viên dạy các module
                        </h6>
                        
                        <!-- Hiển thị danh sách giảng viên -->
                        <div class="row mb-4" id="lecturers-list">
                            @foreach($giangViens as $giangVien)
                                <div class="col-md-3 mb-3">
                                    <div class="card lecturer-card cursor-pointer" data-lecturer-id="{{ $giangVien->id }}" data-lecturer-name="{{ $giangVien->nguoiDung->ho_ten ?? 'N/A' }}" style="cursor: pointer; transition: all 0.3s;">
                                        <div class="card-body text-center">
                                            <img src="{{ $giangVien->avatar_url ? asset($giangVien->avatar_url) : asset('images/default-avatar.svg') }}" 
                                                 alt="Avatar" class="rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                                            <h6 class="card-title mb-1">{{ $giangVien->nguoiDung->ho_ten ?? 'N/A' }}</h6>
                                            @if($giangVien->hoc_vi)
                                                <small class="text-muted d-block">{{ $giangVien->hoc_vi }}</small>
                                            @endif
                                            @if($giangVien->chuyen_nganh)
                                                <small class="text-info d-block">{{ $giangVien->chuyen_nganh }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Modal chọn module cho giảng viên -->
                        <div class="modal fade" id="lectureModulesModal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Chọn modules cho giảng viên: <strong id="selected-lecturer-name"></strong></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="modules-checkboxes">
                                            <!-- Module checkboxes sẽ được tạo động bởi JavaScript -->
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                        <button type="button" class="btn btn-primary" id="save-lecturer-modules">
                                            <i class="fas fa-save"></i> Lưu phân công
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hiển thị danh sách phân công đã chọn -->
                        <h6 class="mb-3 mt-4 text-success">
                            <i class="fas fa-check-circle"></i> Danh sách phân công
                        </h6>
                        <div id="assignments-summary" class="mb-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Chọn giảng viên ở trên để phân công các module
                            </div>
                        </div>

                        <!-- Hidden input để lưu dữ liệu phân công -->
                        <input type="hidden" id="lecturer-modules-data" name="lecturer_modules" value="{}">

                        <!-- Hiển thị các module hiện có để tham khảo -->
                        @if($existingModules->count() > 0)
                        <div class="mt-4">
                            <h6 class="text-info">
                                <i class="fas fa-info-circle"></i> Tham khảo các module hiện có
                            </h6>
                            <div class="accordion" id="existingModulesAccordion">
                                @foreach($existingModules as $moduleName => $modules)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#module-{{ md5($moduleName) }}">
                                                {{ $moduleName }} ({{ $modules->count() }} khóa học)
                                            </button>
                                        </h2>
                                        <div id="module-{{ md5($moduleName) }}" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                @foreach($modules as $existingModule)
                                                    <div class="border rounded p-2 mb-2">
                                                        <strong>{{ $existingModule->khoaHoc->ten_khoa_hoc }}</strong>
                                                        <br><small class="text-muted">{{ $existingModule->khoaHoc->monHoc->ten_mon_hoc }}</small>
                                                        @if($existingModule->phanCongGiangViens->count() > 0)
                                                            <br><small>Giảng viên: {{ $existingModule->phanCongGiangViens->first()->giangVien->nguoiDung->ho_ten ?? 'N/A' }}</small>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Submit buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.khoa-hoc.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Tạo khóa học
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    let moduleCount = 1;

    // Tạo assignment cho module đầu tiên
    updateModuleAssignments();

    // Thêm module mới
    document.getElementById('add-module').addEventListener('click', function() {
        moduleCount++;
        const container = document.getElementById('modules-container');

        const moduleHtml = `
            <div class="module-item border rounded p-3 mb-3" data-module="${moduleCount}">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Tên module ${moduleCount} <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="modules[${moduleCount - 1}][ten_module]"
                            class="form-control vip-form-control"
                            placeholder="Nhập tên module"
                            required
                        >
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Thời lượng (giờ)</label>
                        <input
                            type="number"
                            name="modules[${moduleCount - 1}][thoi_luong_du_kien]"
                            class="form-control vip-form-control"
                            placeholder="0"
                            min="1"
                        >
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-module">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="form-label">Mô tả module</label>
                        <textarea
                            name="modules[${moduleCount - 1}][mo_ta]"
                            class="form-control vip-form-control"
                            rows="2"
                            placeholder="Mô tả nội dung module"
                        ></textarea>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', moduleHtml);

        // Hiển thị nút xóa cho module đầu tiên nếu có nhiều hơn 1 module
        if (moduleCount > 1) {
            document.querySelector('.remove-module').style.display = 'block';
        }

        // Cập nhật assignments
        updateModuleAssignments();
    });

    // Xóa module
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-module') || e.target.closest('.remove-module')) {
            e.preventDefault();
            const moduleItem = e.target.closest('.module-item');
            moduleItem.remove();
            moduleCount--;

            // Ẩn nút xóa nếu chỉ còn 1 module
            if (moduleCount <= 1) {
                const removeButtons = document.querySelectorAll('.remove-module');
                removeButtons.forEach(btn => btn.style.display = 'none');
            }

            // Cập nhật lại tên và index
            updateModuleNames();
            updateModuleAssignments();
        }
    });

    function updateModuleNames() {
        const moduleItems = document.querySelectorAll('.module-item');
        moduleItems.forEach((item, index) => {
            const moduleNumber = index + 1;
            item.setAttribute('data-module', moduleNumber);

            const label = item.querySelector('label');
            if (label) {
                label.textContent = `Tên module ${moduleNumber} *`;
            }

            // Cập nhật name attributes
            const inputs = item.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, `[${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
    }

    function updateModuleAssignments() {
        // Không cần cập nhật gì ở đây vì chúng ta sử dụng phương pháp mới qua modal
    }

    // Lưu trữ nhân các module được phân công
    let lecturerModules = {};

    // Xử lý khi click vào card giảng viên
    document.addEventListener('click', function(e) {
        const lecturerCard = e.target.closest('.lecturer-card');
        if (lecturerCard) {
            const lecturerId = lecturerCard.getAttribute('data-lecturer-id');
            const lecturerName = lecturerCard.getAttribute('data-lecturer-name');
            const modalElement = document.getElementById('lectureModulesModal');
            const modal = new bootstrap.Modal(modalElement);

            // Cập nhật tiêu đề modal
            document.getElementById('selected-lecturer-name').textContent = lecturerName;

            // Tạo danh sách checkbox cho các module
            createModuleCheckboxes(lecturerId);

            // Lưu lecturer ID hiện tại để después dùng
            modalElement.dataset.currentLecturerId = lecturerId;

            // Hiển thị modal
            modal.show();
        }
    });

    // Tạo danh sách checkbox cho các module
    function createModuleCheckboxes(lecturerId) {
        const modulesContainer = document.getElementById('modules-checkboxes');
        const moduleItems = document.querySelectorAll('.module-item');
        
        let checkboxesHtml = '';

        if (moduleItems.length === 0) {
            modulesContainer.innerHTML = '<div class="alert alert-warning">Vui lòng tạo ít nhất một module trước</div>';
            return;
        }

        moduleItems.forEach((item, index) => {
            const tenModuleInput = item.querySelector('input[name*="ten_module"]');
            const tenModule = tenModuleInput ? tenModuleInput.value || `Module ${index + 1}` : `Module ${index + 1}`;
            const isChecked = lecturerModules[lecturerId] && lecturerModules[lecturerId].includes(index) ? 'checked' : '';

            checkboxesHtml += `
                <div class="form-check mb-2">
                    <input 
                        class="form-check-input module-checkbox" 
                        type="checkbox" 
                        value="${index}" 
                        id="module_${index}" 
                        ${isChecked}
                    >
                    <label class="form-check-label" for="module_${index}">
                        <strong>${tenModule}</strong>
                    </label>
                </div>
            `;
        });

        modulesContainer.innerHTML = checkboxesHtml;
    }

    // Xử lý khi lưu phân công giảng viên cho module
    document.getElementById('save-lecturer-modules').addEventListener('click', function() {
        const modalElement = document.getElementById('lectureModulesModal');
        const lecturerId = modalElement.dataset.currentLecturerId;
        const checkboxes = document.querySelectorAll('.module-checkbox:checked');
        const selectedModules = Array.from(checkboxes).map(cb => parseInt(cb.value));

        // Lưu lựa chọn
        if (selectedModules.length > 0) {
            lecturerModules[lecturerId] = selectedModules;
        } else {
            delete lecturerModules[lecturerId];
        }

        // Cập nhật hidden input
        document.getElementById('lecturer-modules-data').value = JSON.stringify(lecturerModules);

        // Cập nhật hiển thị summary
        updateAssignmentsSummary();

        // Đóng modal
        const modal = bootstrap.Modal.getInstance(modalElement);
        modal.hide();
    });

    // Cập nhật hiển thị danh sách phân công
    function updateAssignmentsSummary() {
        const summaryContainer = document.getElementById('assignments-summary');
        let summaryHtml = '';

        if (Object.keys(lecturerModules).length === 0) {
            summaryHtml = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Chọn giảng viên ở trên để phân công các module</div>';
        } else {
            summaryHtml = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Giảng viên</th><th>Modules</th><th>Thao tác</th></tr></thead><tbody>';

            // Tìm giảng viên từ cards
            const lecturerCards = document.querySelectorAll('.lecturer-card');
            lecturerCards.forEach(card => {
                const lecturerId = card.getAttribute('data-lecturer-id');
                const lecturerName = card.getAttribute('data-lecturer-name');

                if (lecturerModules[lecturerId]) {
                    const moduleItems = document.querySelectorAll('.module-item');
                    const moduleNames = lecturerModules[lecturerId].map(index => {
                        const tenModuleInput = moduleItems[index]?.querySelector('input[name*="ten_module"]');
                        return tenModuleInput ? tenModuleInput.value || `Module ${index + 1}` : `Module ${index + 1}`;
                    }).join(', ');

                    summaryHtml += `
                        <tr>
                            <td><strong>${lecturerName}</strong></td>
                            <td>${moduleNames}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary edit-assignment" data-lecturer-id="${lecturerId}">
                                    <i class="fas fa-edit"></i> Sửa
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-assignment" data-lecturer-id="${lecturerId}">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                    `;
                }
            });

            summaryHtml += '</tbody></table></div>';
        }

        summaryContainer.innerHTML = summaryHtml;

        // Thêm event listeners cho nút Sửa và Xóa
        attachSummaryEventListeners();
    }

    function attachSummaryEventListeners() {
        document.querySelectorAll('.edit-assignment').forEach(btn => {
            btn.addEventListener('click', function() {
                const lecturerId = this.getAttribute('data-lecturer-id');
                const lecturerCard = document.querySelector(`.lecturer-card[data-lecturer-id="${lecturerId}"]`);
                lecturerCard.click(); // Mở modal
            });
        });

        document.querySelectorAll('.delete-assignment').forEach(btn => {
            btn.addEventListener('click', function() {
                const lecturerId = this.getAttribute('data-lecturer-id');
                delete lecturerModules[lecturerId];
                updateAssignmentsSummary();
            });
        });
    }

    // Đảm bảo dữ liệu được gửi khi form submit
    document.getElementById('khoaHocForm').addEventListener('submit', function(e) {
        //Cập nhật hidden input trước khi submit
        document.getElementById('lecturer-modules-data').value = JSON.stringify(lecturerModules);
    });
});
</script>
@endsection