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
                <input type="hidden" name="tiet_bat_dau" id="single-tiet-bat-dau" value="9">
                <input type="hidden" name="tiet_ket_thuc" id="single-tiet-ket-thuc" value="12">
                <input type="hidden" name="buoi_hoc" id="single-buoi-hoc" value="toi">
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
                            <input type="text" id="single-time-preview" class="form-control form-control-sm border-0 bg-light italic" readonly value="Ca tối | Tiết 9-12" placeholder="Thông tin ca học...">
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
<div class="modal fade shadow lh-auto-modal" id="modalSinhTuDong" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable lh-auto-dialog">
        <div class="modal-content border-0 lh-auto-content">
            <div class="modal-header bg-success text-white border-0 lh-auto-header">
                <div class="lh-auto-title-wrap">
                    <span class="lh-auto-header-icon"><i class="fas fa-magic"></i></span>
                    <div>
                        <span class="lh-auto-kicker">Planner tự động</span>
                        <h5 class="modal-title fw-bold mb-0">Sinh lịch tự động & Xem trước lộ trình</h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.lich-hoc.store-auto', $khoaHoc->id) }}" method="POST" id="auto-schedule-form">
                @csrf
                <input type="hidden" name="module_hoc_id" id="auto-module-id">
                <input type="hidden" name="tiet_bat_dau" id="auto-tiet-bat-dau" value="9">
                <input type="hidden" name="tiet_ket_thuc" id="auto-tiet-ket-thuc" value="12">
                <input type="hidden" name="buoi_hoc" id="auto-buoi-hoc" value="toi">

                <div class="modal-body p-0 lh-auto-body">
                    <div class="row g-0 lh-auto-layout">
                        <div class="col-lg-5 lh-auto-config">
                            <div class="lh-auto-scroll">
                                <section class="lh-auto-block lh-auto-module-block">
                                    <div class="lh-auto-block-head">
                                        <span class="lh-auto-step">01</span>
                                        <div>
                                            <label class="lh-auto-label mb-0">Module xử lý</label>
                                            <div id="auto-module-name" class="lh-auto-module-name"></div>
                                        </div>
                                    </div>

                                    <div class="lh-auto-metrics">
                                        <div class="lh-auto-metric">
                                            <span>Số buổi</span>
                                            <strong id="auto-so-buoi-text">0</strong>
                                        </div>
                                        <div class="lh-auto-metric">
                                            <span>Dự kiến xong</span>
                                            <strong id="auto-end-date-text">--/--</strong>
                                        </div>
                                    </div>
                                </section>

                                <section class="lh-auto-block">
                                    <div class="lh-auto-block-head">
                                        <span class="lh-auto-step">02</span>
                                        <div>
                                            <h6>Thông tin vận hành</h6>
                                            <p>Ngày bắt đầu, giảng viên và hình thức học cho toàn bộ lộ trình.</p>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label lh-auto-field-label">Ngày bắt đầu *</label>
                                            <input type="date" name="ngay_bat_dau" id="auto-start-date" class="form-control" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label lh-auto-field-label">Giảng viên *</label>
                                            <select name="giang_vien_id" id="auto-teacher-id" class="form-select" required></select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label lh-auto-field-label">Hình thức *</label>
                                            <select name="hinh_thuc" class="form-select" required>
                                                <option value="truc_tiep">Trực tiếp</option>
                                                <option value="online">Online</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label lh-auto-field-label">Phòng / Link</label>
                                            <input type="text" name="phong_hoc" class="form-control" placeholder="Phòng hoặc link học">
                                        </div>
                                    </div>
                                </section>

                                <section class="lh-auto-block">
                                    <div class="lh-auto-block-head align-items-start">
                                        <span class="lh-auto-step">03</span>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                                <div>
                                                    <h6>Thứ học trong tuần</h6>
                                                    <p>Ô màu xanh là ngày module đã từng có lịch, dùng để giữ nhịp học cũ.</p>
                                                </div>
                                                <div class="lh-day-tools">
                                                    <button type="button" class="btn btn-sm btn-outline-success" id="btnUseExistingDays">Theo lịch cũ</button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUseDefaultDays">Mẫu 2-4-6</button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUse357Days">Mẫu 3-5-7</button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearDays">Bỏ chọn</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="lh-lock-row">
                                        <div class="form-check form-switch small mb-0">
                                            <input class="form-check-input cursor-pointer" type="checkbox" id="lockThuSelection">
                                            <label class="form-check-label fw-bold cursor-pointer" for="lockThuSelection">Khóa chỉnh thứ học</label>
                                        </div>
                                    </div>

                                    <div class="lh-auto-days-grid" id="container-thu-auto">
                                        @foreach(\App\Models\LichHoc::$thuLabels as $val => $lbl)
                                            <div class="thu-check-item">
                                                <input type="checkbox" name="thu_trong_tuan[]" value="{{ $val }}" id="thu_auto_{{ $val }}" class="d-none" {{ in_array($val, [2, 4, 6]) ? 'checked' : '' }}>
                                                <label class="thu-label-box shadow-xs" for="thu_auto_{{ $val }}" data-thu="{{ $val }}" data-full-label="{{ $lbl }}" title="{{ $lbl }}">
                                                    <span class="thu-title">{{ $val === 8 ? 'CN' : 'T' . $val }}</span>
                                                    <span class="thu-state">Chưa chọn</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="lh-auto-legend">
                                        <span><span class="legend-box legend-existing"></span>Đã có lịch</span>
                                        <span><span class="legend-box bg-danger"></span>Bắt đầu</span>
                                        <span><span class="legend-box bg-success"></span>Cùng tuần</span>
                                        <span><span class="legend-box bg-primary"></span>Tuần sau</span>
                                    </div>
                                    <div id="auto-existing-days-note" class="lh-auto-note"></div>
                                </section>

                                <section class="lh-auto-block">
                                    <div class="lh-auto-block-head">
                                        <span class="lh-auto-step">04</span>
                                        <div>
                                            <h6>Ca học mặc định</h6>
                                            <p>Ca này sẽ áp dụng trước cho danh sách preview, sau đó vẫn chỉnh từng dòng được.</p>
                                        </div>
                                    </div>

                                    <div class="lh-session-tools">
                                        @foreach($sessionOptions as $session => $definition)
                                            <button type="button" class="btn btn-sm btn-outline-success schedule-session-btn" data-prefix="auto" data-session="{{ $session }}">
                                                {{ $definition['label'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <input type="text" id="auto-time-preview" class="form-control form-control-sm fw-bold" readonly value="Ca tối | Tiết 9-12" placeholder="Chọn ca học...">
                                    <input type="hidden" name="gio_bat_dau" id="auto-start-time" value="18:00">
                                    <input type="hidden" name="gio_ket_thuc" id="auto-end-time" value="20:45">
                                </section>

                                <button type="button" class="btn w-100 fw-bold lh-auto-preview-btn" id="btnPreviewAuto">
                                    <i class="fas fa-eye me-2"></i>Xem trước lộ trình
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-7 lh-auto-preview">
                            <div class="lh-auto-preview-scroll">
                                <div class="lh-auto-preview-head">
                                    <div>
                                        <span class="lh-auto-label">Bản xem trước</span>
                                        <h6>Danh sách buổi học dự kiến</h6>
                                    </div>
                                    <span class="lh-auto-preview-pill"><i class="fas fa-shield-halved"></i> Có kiểm tra trùng lịch</span>
                                </div>

                                <div id="auto-preview-container" class="d-none lh-auto-preview-live">
                                    <div class="table-responsive lh-auto-table-wrap">
                                        <table class="table table-sm table-hover align-middle mb-0" id="tablePreviewAuto">
                                            <thead class="sticky-top">
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
                                    <div id="auto-planning-panel" class="planning-panel p-3 mt-3" data-endpoint="{{ route('admin.khoa-hoc.lich-hoc.teacher-context', $khoaHoc->id) }}"></div>
                                </div>

                                <div id="auto-empty-preview" class="lh-auto-empty-preview">
                                    <span class="lh-auto-empty-icon"><i class="fas fa-calendar-alt"></i></span>
                                    <strong>Chưa có bản xem trước</strong>
                                    <p>Sau khi thiết lập thông tin bên trái, nhấn "Xem trước lộ trình" để kiểm tra danh sách buổi học.</p>
                                </div>
                            </div>

                            <div class="lh-auto-footer">
                                <button type="submit" class="btn btn-success w-100 fw-bold disabled lh-auto-save-btn" id="btnConfirmAutoSave">
                                    <i class="fas fa-check-circle me-2"></i>Xác nhận lưu lịch học
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
