@php
    use App\Support\Scheduling\TeachingPeriodCatalog;
    $sessionOptions = TeachingPeriodCatalog::sessions();
    $periodDefinitions = TeachingPeriodCatalog::periods();
@endphp

{{-- Modal thêm buổi học lẻ --}}
<div class="modal fade shadow" id="modalThemBuoi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold mb-0"><i class="fas fa-plus me-2"></i>Thêm buổi học lẻ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.lich-hoc.store', $khoaHoc->id) }}" method="POST" id="single-schedule-form">
                @csrf
                <input type="hidden" name="module_hoc_id" id="single-module-id">
                <input type="hidden" name="tiet_bat_dau" id="single-tiet-bat-dau">
                <input type="hidden" name="tiet_ket_thuc" id="single-tiet-ket-thuc">
                <input type="hidden" name="buoi_hoc" id="single-buoi-hoc">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold d-block mb-1">Module xử lý</label>
                        <div id="single-module-name" class="fw-bold fs-5 text-dark border-bottom pb-2"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Ngày học *</label>
                            <input type="date" name="ngay_hoc" id="single-date" class="form-control border-0 bg-light shadow-sm" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Giảng viên *</label>
                            <select name="giang_vien_id" id="single-teacher-id" class="form-select border-0 bg-light shadow-sm" required>
                                <option value="">-- Chọn giảng viên --</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Chọn ca học</label>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach($sessionOptions as $session => $definition)
                                    <button type="button" class="btn btn-sm btn-outline-primary schedule-session-btn" data-prefix="single" data-session="{{ $session }}">
                                        {{ $definition['label'] }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="text" id="single-time-preview" class="form-control form-control-sm border-0 bg-light italic" readonly placeholder="Thông tin ca học...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hình thức</label>
                            <select name="hinh_thuc" class="form-select border-0 bg-light shadow-sm">
                                <option value="truc_tiep">Trực tiếp</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phòng / Link</label>
                            <input type="text" name="phong_hoc" class="form-control border-0 bg-light shadow-sm" placeholder="Địa điểm học">
                        </div>
                    </div>
                    <div id="single-planning-panel" class="planning-panel border rounded-3 p-3 bg-light mt-4"></div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow">Lưu buổi học</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal sinh lịch tự động --}}
<div class="modal fade shadow" id="modalSinhTuDong" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-success rounded-circle p-2 me-3 shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-magic"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0">Sinh lịch tự động & Xem trước lộ trình</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.lich-hoc.store-auto', $khoaHoc->id) }}" method="POST" id="auto-schedule-form">
                @csrf
                <input type="hidden" name="module_hoc_id" id="auto-module-id">
                <input type="hidden" name="tiet_bat_dau" id="auto-tiet-bat-dau">
                <input type="hidden" name="tiet_ket_thuc" id="auto-tiet-ket-thuc">
                <input type="hidden" name="buoi_hoc" id="auto-buoi-hoc">
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <!-- Cột trái: Cấu hình (Có cuộn riêng) -->
                        <div class="col-lg-5 border-end bg-light p-4" style="max-height: 75vh; overflow-y: auto;">
                            <div class="mb-4">
                                <label class="small text-muted fw-bold d-block mb-1 text-uppercase">Module xử lý</label>
                                <div id="auto-module-name" class="fw-bold fs-5 text-dark border-bottom pb-2"></div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="p-2 bg-white rounded border text-center shadow-xs">
                                        <span class="smaller text-muted d-block fw-bold">SỐ BUỔI</span>
                                        <span id="auto-so-buoi-text" class="fw-bold text-primary fs-4">0</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-white rounded border text-center shadow-xs">
                                        <span class="smaller text-muted d-block fw-bold">DỰ KIẾN XONG</span>
                                        <span id="auto-end-date-text" class="fw-bold text-dark fs-5">--/--</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Ngày bắt đầu lộ trình *</label>
                                <input type="date" name="ngay_bat_dau" id="auto-start-date" class="form-control border-0 shadow-sm" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Giảng viên phụ trách *</label>
                                <select name="giang_vien_id" id="auto-teacher-id" class="form-select border-0 shadow-sm" required>
                                    <!-- JS sẽ đổ dữ liệu vào đây -->
                                </select>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Hình thức học *</label>
                                    <select name="hinh_thuc" class="form-select border-0 shadow-sm" required>
                                        <option value="truc_tiep">Trực tiếp</option>
                                        <option value="online">Online</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Phòng / Link</label>
                                    <input type="text" name="phong_hoc" class="form-control border-0 shadow-sm" placeholder="Phòng hoặc Link">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2 flex-wrap">
                                    <div>
                                        <label class="form-label small fw-bold mb-0">Chọn các Thứ học trong tuần *</label>
                                        <div class="small text-muted mt-1">Những ô đã có lịch trong module sẽ được tô xanh để bạn phân biệt nhanh.</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-outline-success" id="btnUseExistingDays">Theo lịch cũ</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUseDefaultDays">Mẫu 2-4-6</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUse357Days">Mẫu 3-5-7</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearDays">Bỏ chọn hết</button>
                                        <div class="form-check form-switch small ms-sm-2">
                                            <input class="form-check-input cursor-pointer" type="checkbox" id="lockThuSelection">
                                            <label class="form-check-label fw-bold text-muted cursor-pointer" for="lockThuSelection">KHÓA CHỈNH</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2" id="container-thu-auto">
                                    @foreach(\App\Models\LichHoc::$thuLabels as $val => $lbl)
                                        <div class="thu-check-item">
                                            <input type="checkbox" name="thu_trong_tuan[]" value="{{ $val }}" id="thu_auto_{{ $val }}" class="d-none" {{ in_array($val, [2, 4, 6]) ? 'checked' : '' }}>
                                            <label class="thu-label-box shadow-xs" for="thu_auto_{{ $val }}" data-thu="{{ $val }}" data-full-label="{{ $lbl }}" title="{{ $lbl }}">
                                                <span class="thu-title">{{ $val === 8 ? 'CN' : 'T' . $val }}</span>
                                                <span class="thu-state">Chua chon</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 d-flex gap-3 smaller fw-bold">
                                    <span><span class="legend-box legend-existing"></span>Đã có lịch</span>
                                    <span><span class="legend-box bg-danger"></span>Bắt đầu</span>
                                    <span><span class="legend-box bg-success"></span>Cùng tuần</span>
                                    <span><span class="legend-box bg-primary"></span>Tuần sau</span>
                                </div>
                                <div id="auto-existing-days-note" class="small text-muted mt-2"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold d-block">Chọn ca học mặc định</label>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    @foreach($sessionOptions as $session => $definition)
                                        <button type="button" class="btn btn-sm btn-outline-success schedule-session-btn" data-prefix="auto" data-session="{{ $session }}">
                                            {{ $definition['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                                <input type="text" id="auto-time-preview" class="form-control form-control-sm border-0 bg-white shadow-xs fw-bold text-success mb-2" readonly placeholder="Chọn ca học...">
                                <input type="hidden" name="gio_bat_dau" id="auto-start-time">
                                <input type="hidden" name="gio_ket_thuc" id="auto-end-time">
                            </div>

                            <button type="button" class="btn btn-dark w-100 py-3 fw-bold shadow mt-2" id="btnPreviewAuto">
                                <i class="fas fa-eye me-2"></i>XEM TRƯỚC LỘ TRÌNH
                            </button>
                        </div>

                        <!-- Cột phải: Xem trước (Có cuộn riêng) -->
                        <div class="col-lg-7 d-flex flex-column bg-white" style="max-height: 75vh;">
                            <div class="flex-grow-1 p-4 overflow-auto">
                                <div id="auto-preview-container" class="d-none">
                                    <h6 class="fw-bold mb-3 text-uppercase smaller text-muted border-bottom pb-2">Danh sách buổi học dự kiến</h6>
                                    <div class="table-responsive bg-white rounded shadow-sm border">
                                        <table class="table table-sm table-hover align-middle mb-0" id="tablePreviewAuto">
                                            <thead class="bg-light sticky-top">
                                                <tr>
                                                    <th class="text-center py-2" width="50">#</th>
                                                    <th>Ngày học</th>
                                                    <th class="text-center">Thứ</th>
                                                    <th>Ca học / Tiết</th>
                                                </tr>
                                            </thead>
                                            <tbody id="auto-preview-body"></tbody>
                                        </table>
                                    </div>
                                    <div id="auto-planning-panel" class="planning-panel border rounded-3 p-3 bg-light mt-4" data-endpoint="{{ route('admin.khoa-hoc.lich-hoc.teacher-context', $khoaHoc->id) }}"></div>
                                </div>

                                <div id="auto-empty-preview" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                    <i class="fas fa-calendar-alt fs-1 opacity-25 mb-3"></i>
                                    <p class="fw-bold">Chưa có bản xem trước</p>
                                    <p class="small italic text-center px-4 text-muted">Sau khi thiết lập thông tin bên trái, nhấn "Xem trước lộ trình" để kiểm tra danh sách buổi học.</p>
                                </div>
                            </div>

                            <div class="p-4 border-top bg-light mt-auto">
                                <button type="submit" class="btn btn-success w-100 py-3 fw-bold shadow disabled" id="btnConfirmAutoSave">
                                    <i class="fas fa-check-circle me-2"></i>XÁC NHẬN LƯU LỊCH HỌC
                                </button>
                                <button type="button" class="btn btn-link text-muted w-100 mt-2 smaller text-decoration-none" data-bs-dismiss="modal">Hủy bỏ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
