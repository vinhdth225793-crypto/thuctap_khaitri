@extends('layouts.app')

@section('title', 'Quản lý lịch học — ' . $khoaHoc->ten_khoa_hoc)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}">{{ $khoaHoc->ma_khoa_hoc }}</a></li>
            <li class="breadcrumb-item active">Lịch học</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-calendar-alt me-2 text-info"></i>
                Quản lý lịch học — {{ $khoaHoc->ten_khoa_hoc }}
            </h4>
            <span class="badge bg-{{ $khoaHoc->badge_trang_thai }}">{{ $khoaHoc->label_trang_thai_van_hanh }}</span>
        </div>
        <div class="d-flex gap-2">
            <button id="btnBulkDelete" class="btn btn-danger btn-sm shadow-sm fw-bold d-none" onclick="submitBulkDelete()">
                <i class="fas fa-trash-alt me-1"></i> Xóa <span id="selectedCount">0</span> buổi đã chọn
            </button>
            <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay lại chi tiết
            </a>
        </div>
    </div>

    @include('components.alert')

    <!-- Thống kê tổng quát -->
    <div class="row g-3 mb-4">
        @php
            $tongBuoiQuyDinh = $khoaHoc->moduleHocs->sum('so_buoi');
            $tongBuoiDaLen = $khoaHoc->lichHocs->count();
            $conThieu = max(0, $tongBuoiQuyDinh - $tongBuoiDaLen);
            $moduleDuLich = $khoaHoc->moduleHocs->filter(fn($m) => $m->lichHocs->count() >= $m->so_buoi)->count();
            $moduleThieuLich = $khoaHoc->moduleHocs->count() - $moduleDuLich;
        @endphp
        <div class="col-md-3">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-info text-white">
                <div class="smaller text-uppercase fw-bold opacity-75">Đã lên lịch</div>
                <div class="fs-2 fw-bold">{{ $tongBuoiDaLen }}</div>
                <div class="small">buổi học thực tế</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vip-card p-3 text-center border-0 shadow-sm {{ $conThieu > 0 ? 'bg-warning' : 'bg-success text-white' }}">
                <div class="smaller text-uppercase fw-bold opacity-75">Cần bổ sung</div>
                <div class="fs-2 fw-bold">{{ $conThieu }}</div>
                <div class="small">buổi học còn thiếu</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-white border-start border-4 border-success">
                <div class="smaller text-muted text-uppercase fw-bold">Module đủ lịch</div>
                <div class="fs-2 fw-bold text-success">{{ $moduleDuLich }}</div>
                <div class="small text-muted">/ {{ $khoaHoc->moduleHocs->count() }} module</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vip-card p-3 text-center border-0 shadow-sm bg-white border-start border-4 border-danger">
                <div class="smaller text-muted text-uppercase fw-bold">Module thiếu lịch</div>
                <div class="fs-2 fw-bold text-danger">{{ $moduleThieuLich }}</div>
                <div class="small text-muted">chưa hoàn thiện lịch</div>
            </div>
        </div>
    </div>

    <!-- Form Xóa Hàng Loạt (Bao phủ bảng nhưng không lồng vào các form khác) -->
    <form id="bulkDeleteForm" action="{{ route('admin.khoa-hoc.lich-hoc.destroy-bulk', $khoaHoc->id) }}" method="POST">
        @csrf @method('DELETE')
    </form>

    <!-- Loop qua từng module -->
    @foreach($khoaHoc->moduleHocs as $index => $module)
        @php
            // Lấy ngày kết thúc của module đứng trước đó (nếu có)
            $prevModule = $index > 0 ? $khoaHoc->moduleHocs[$index - 1] : null;
            $minDate = $prevModule ? $prevModule->ngay_ket_thuc_thuc_te : date('Y-m-d');
        @endphp
        <div class="vip-card mb-4 border-0 shadow-sm">
            <div class="vip-card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div style="flex: 1; min-width: 250px;">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-1 text-primary">
                        <i class="fas fa-cube me-2"></i> Module {{ $module->thu_tu_module }}: {{ $module->ten_module }}
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="small text-muted">
                            Quy định: <strong class="text-dark">{{ $module->so_buoi }} buổi</strong> | 
                            Đã lên: <strong class="{{ $module->lichHocs->count() < $module->so_buoi ? 'text-danger' : 'text-success' }}">{{ $module->lichHocs->count() }} buổi</strong>
                        </div>
                        @if($module->so_buoi > 0)
                            <div class="progress" style="width: 100px; height: 6px;">
                                @php $percent = min(100, ($module->lichHocs->count() / $module->so_buoi) * 100); @endphp
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <!-- Form lưu số buổi -->
                    <div class="d-flex gap-1 align-items-center me-2 border-end pe-3">
                        <form action="{{ route('admin.khoa-hoc.lich-hoc.update-so-buoi', [$khoaHoc->id, $module->id]) }}" method="POST" class="d-flex gap-1 align-items-center">
                            @csrf
                            <input type="number" name="so_buoi" value="{{ $module->so_buoi }}" class="form-control form-control-sm text-center" style="width: 55px;" min="1">
                            <button type="submit" class="btn btn-sm btn-light border" title="Lưu số buổi">
                                <i class="fas fa-save text-primary"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-danger dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-trash-alt me-1"></i> Xóa
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <button type="button" class="dropdown-item text-danger small py-2" onclick="confirmDeleteModule('{{ $module->id }}', '{{ $module->ten_module }}')">
                                    <i class="fas fa-eraser me-2"></i> Xóa tất cả các buổi "Chờ"
                                </button>
                            </li>
                        </ul>
                    </div>

                    <button type="button" class="btn btn-sm btn-success fw-bold px-3 btn-auto-schedule" 
                            data-module-id="{{ $module->id }}" 
                            data-module-name="{{ $module->ten_module }}"
                            data-so-buoi="{{ $module->so_buoi }}"
                            data-khoa-hoc-end="{{ $khoaHoc->ngay_ket_thuc->format('Y-m-d') }}"
                            data-min-date="{{ $minDate }}">
                        <i class="fas fa-magic me-1"></i> Sinh lịch tự động
                    </button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3 btn-add-single" 
                            data-module-id="{{ $module->id }}" 
                            data-module-name="{{ $module->ten_module }}"
                            data-min-date="{{ $minDate }}">
                        <i class="fas fa-plus me-1"></i> Thêm buổi lẻ
                    </button>
                </div>
            </div>
            <div class="vip-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light smaller">
                            <tr>
                                <th class="ps-4" width="40">
                                    <input type="checkbox" class="form-check-input check-all-module" data-module="{{ $module->id }}">
                                </th>
                                <th width="80">Thứ tự</th>
                                <th>Ngày học</th>
                                <th>Thứ</th>
                                <th class="text-center">Thời gian</th>
                                <th>Phòng / Link</th>
                                <th>Giảng viên</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="pe-4 text-center" width="100">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($module->lichHocs as $index => $lich)
                                <tr class="{{ $lich->trang_thai === 'cho' ? 'row-selectable' : 'table-light opacity-75' }}">
                                    <td class="ps-4">
                                        @if($lich->trang_thai === 'cho')
                                            <!-- Chú ý: input checkbox này có thuộc tính form="bulkDeleteForm" để nó thuộc về form xóa hàng loạt nằm bên ngoài -->
                                            <input type="checkbox" name="ids[]" value="{{ $lich->id }}" form="bulkDeleteForm" class="form-check-input check-item module-{{ $module->id }}">
                                        @else
                                            <i class="fas fa-lock text-muted small" title="Không thể xóa buổi đã học/đang học"></i>
                                        @endif
                                    </td>
                                    <td class="text-muted">Buổi {{ $lich->buoi_so }}</td>
                                    <td class="fw-bold">{{ $lich->ngay_hoc->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $lich->thu_label }}</span></td>
                                    <td class="text-center">
                                        <code class="text-dark">{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</code>
                                    </td>
                                    <td>
                                        @if($lich->hinh_thuc === 'online')
                                            <span class="text-info"><i class="fas fa-globe me-1"></i> Online</span>
                                        @else
                                            <span class="text-dark"><i class="fas fa-door-open me-1"></i> {{ $lich->phong_hoc ?: 'Chưa gán' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lich->giangVien)
                                            <div class="small fw-bold text-truncate" style="max-width: 120px;">{{ $lich->giangVien->nguoiDung->ho_ten }}</div>
                                        @else
                                            <span class="text-muted italic smaller">Chưa gán</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $color = match($lich->trang_thai) {
                                                'cho' => 'secondary',
                                                'dang_hoc' => 'info',
                                                'hoan_thanh' => 'success',
                                                'huy' => 'danger',
                                                default => 'light'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $lich->trang_thai_label }}</span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.khoa-hoc.lich-hoc.edit', [$khoaHoc->id, $lich->id]) }}" class="btn btn-sm btn-outline-warning border-0"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="confirmDeleteSingle('{{ route('admin.khoa-hoc.lich-hoc.destroy', [$khoaHoc->id, $lich->id]) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted italic">Module này chưa có lịch học nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Hidden Form for Module Delete --}}
<form id="deleteModuleForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

{{-- Hidden Form for Single Delete --}}
<form id="deleteSingleForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

{{-- Các Modal (Sinh lịch, Thêm buổi lẻ) --}}
@include('pages.admin.lich-hoc.modals')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo Bootstrap Modals
    const modalSingle = new bootstrap.Modal(document.getElementById('modalThemBuoi'));
    const modalAuto   = new bootstrap.Modal(document.getElementById('modalSinhTuDong'));

    // Hàm hỗ trợ cộng 1 ngày
    function getNextDay(dateString) {
        if (!dateString) return "{{ date('Y-m-d') }}";
        const date = new Date(dateString);
        date.setDate(date.getDate() + 1);
        return date.toISOString().split('T')[0];
    }

    // Sự kiện mở Modal thêm buổi lẻ
    document.querySelectorAll('.btn-add-single').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('single-module-id').value = this.dataset.moduleId;
            document.getElementById('single-module-name').textContent = this.dataset.moduleName;
            
            const minDate = this.dataset.minDate;
            const inputNgay = document.querySelector('#modalThemBuoi input[name="ngay_hoc"]');
            if (inputNgay && minDate) {
                const nextDay = getNextDay(minDate);
                inputNgay.min = nextDay;
                inputNgay.value = nextDay;
                inputNgay.dispatchEvent(new Event('change'));
            }
            
            modalSingle.show();
        });
    });

    // Biến lưu trữ dữ liệu module đang thao tác
    let currentModuleSoBuoi = 0;
    let currentCourseEndDate = "";

    // Sự kiện mở Modal sinh lịch tự động
    document.querySelectorAll('.btn-auto-schedule').forEach(btn => {
        btn.addEventListener('click', function() {
            const moduleId = this.dataset.moduleId;
            document.getElementById('auto-module-id').value = moduleId;
            document.getElementById('auto-module-name').textContent = this.dataset.moduleName;
            
            // Lưu thông tin phục vụ tính toán
            currentModuleSoBuoi = parseInt(this.dataset.soBuoi) || 0;
            currentCourseEndDate = this.dataset.khoaHocEnd;
            
            document.getElementById('auto-so-buoi-text').textContent = currentModuleSoBuoi + " buổi";
            document.getElementById('auto-course-end-date').textContent = new Date(currentCourseEndDate).toLocaleDateString('vi-VN');

            const minDate = this.dataset.minDate;
            const inputNgayBatDau = document.querySelector('#modalSinhTuDong input[name="ngay_bat_dau"]');
            if (inputNgayBatDau && minDate) {
                const nextDay = getNextDay(minDate);
                inputNgayBatDau.min = nextDay;
                inputNgayBatDau.value = nextDay;
            }

            calculateExpectedEndDate();
            modalAuto.show();
        });
    });

    // --- LOGIC TÍNH TOÁN LỘ TRÌNH DỰ KIẾN ---
    function calculateExpectedEndDate() {
        const startDateVal = document.getElementById('auto-start-date').value;
        const selectedDays = Array.from(document.querySelectorAll('input[name="thu_trong_tuan[]"]:checked')).map(cb => parseInt(cb.value));
        const endDateDisplay = document.getElementById('auto-end-date-text');
        const warningBox = document.getElementById('auto-conflict-warning');

        if (!startDateVal || selectedDays.length === 0 || currentModuleSoBuoi <= 0) {
            endDateDisplay.textContent = "--/--/----";
            warningBox.classList.add('d-none');
            return;
        }

        let currentDate = new Date(startDateVal);
        let count = 0;
        let safetyLoop = 0;

        while (count < currentModuleSoBuoi && safetyLoop < 1000) {
            safetyLoop++;
            let day = currentDate.getDay(); // 0 (Sun) to 6 (Sat)
            let dbDay = (day === 0) ? 8 : (day + 1);

            if (selectedDays.includes(dbDay)) {
                count++;
                if (count === currentModuleSoBuoi) break;
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }

        const expectedEndDateStr = currentDate.toISOString().split('T')[0];
        endDateDisplay.textContent = currentDate.toLocaleDateString('vi-VN');

        // So sánh với ngày kết thúc khóa học
        if (expectedEndDateStr > currentCourseEndDate) {
            warningBox.classList.remove('d-none');
            endDateDisplay.classList.add('text-danger');
        } else {
            warningBox.classList.add('d-none');
            endDateDisplay.classList.remove('text-danger');
        }

        // Đồng thời cập nhật màu sắc các thứ
        updateThuColors();
    }

    // Lắng nghe sự kiện thay đổi để tính toán lại
    document.getElementById('auto-start-date').addEventListener('change', calculateExpectedEndDate);
    document.querySelectorAll('input[name="thu_trong_tuan[]"]').forEach(cb => {
        cb.addEventListener('change', calculateExpectedEndDate);
    });

    // --- LOGIC TÔ MÀU THỨ (MODAL SINH TỰ ĐỘNG) ---
    const ngayBatDauInput = document.querySelector('#modalSinhTuDong input[name="ngay_bat_dau"]');
    const thuLabels = document.querySelectorAll('#container-thu-auto .thu-label-box');

    function updateThuColors() {
        if (!ngayBatDauInput.value) return;

        const date = new Date(ngayBatDauInput.value);
        let carbonDay = date.getDay(); // 0 (Sun) to 6 (Sat)
        let startThu = (carbonDay === 0) ? 8 : (carbonDay + 1); // 2 to 8

        thuLabels.forEach(label => {
            let thuVal = parseInt(label.dataset.thu);
            label.classList.remove('bg-danger', 'bg-success', 'bg-warning', 'text-white', 'text-dark');

            if (thuVal === startThu) {
                label.classList.add('bg-danger', 'text-white');
            } else if (thuVal > startThu) {
                label.classList.add('bg-success', 'text-white');
            } else {
                label.classList.add('bg-warning', 'text-dark');
            }
        });
    }

    if (ngayBatDauInput) {
        ngayBatDauInput.addEventListener('change', updateThuColors);
        // Cập nhật ngay khi mở modal (vì có giá trị mặc định)
        document.querySelector('.btn-auto-schedule').addEventListener('click', () => {
            setTimeout(updateThuColors, 200); 
        });
    }
});

function confirmDeleteSingle(url) {
    if (confirm('Bạn chắc chắn muốn xóa buổi học này?')) {
        const form = document.getElementById('deleteSingleForm');
        form.action = url;
        form.submit();
    }
}

function confirmDeleteModule(moduleId, moduleName) {
    if (confirm(`Bạn chắc chắn muốn xóa TOÀN BỘ các buổi học đang ở trạng thái "Chờ" của module: ${moduleName}?`)) {
        const form = document.getElementById('deleteModuleForm');
        form.action = `{{ url('admin/khoa-hoc/'.$khoaHoc->id.'/lich-hoc/module') }}/${moduleId}`;
        form.submit();
    }
}

function submitBulkDelete() {
    if (confirm('Bạn chắc chắn muốn xóa toàn bộ các buổi học đã chọn?')) {
        const form = document.getElementById('bulkDeleteForm');
        form.submit();
    }
}
</script>
@endpush

<style>
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .row-selectable:hover { background-color: rgba(13, 110, 253, 0.02); }
    .italic { font-style: italic; }

    /* Style cho ô chọn Thứ - VIP Upgrade */
    .thu-label-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        border: 2px solid #edf2f7;
        cursor: pointer;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
        background-color: #fff;
        color: #4a5568;
        position: relative;
    }
    
    .thu-label-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #cbd5e0;
    }

    input.form-check-input:checked + .thu-label-box {
        border-color: #0d6efd;
        color: #0d6efd;
        background-color: #f0f7ff;
        transform: scale(1.05);
        z-index: 2;
    }

    input.form-check-input:checked + .thu-label-box::after {
        content: '\f058';
        font-family: "Font Awesome 6 Free";
        position: absolute;
        top: -8px;
        right: -8px;
        background: #fff;
        border-radius: 50%;
        font-size: 14px;
        color: #0d6efd;
    }

    /* Màu sắc theo logic tuần */
    .thu-label-box.bg-danger { background-color: #fff5f5 !important; color: #c53030 !important; border-color: #feb2b2 !important; }
    .thu-label-box.bg-success { background-color: #f0fff4 !important; color: #2f855a !important; border-color: #9ae6b4 !important; }
    .thu-label-box.bg-warning { background-color: #fffaf0 !important; color: #975a16 !important; border-color: #fbd38d !important; }

    /* Hiệu ứng nháy khi tự động thay đổi */
    @keyframes pulse-highlight {
        0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
        100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }
    .auto-filled {
        animation: pulse-highlight 1.5s infinite;
        border-color: #0d6efd !important;
    }

    .legend-box {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 4px;
        margin-right: 6px;
        vertical-align: middle;
    }
</style>
@endsection
